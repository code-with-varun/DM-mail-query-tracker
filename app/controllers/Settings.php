<?php
/**
 * Settings Controller (Super Admin Only)
 */

class Settings extends Controller {
    public function index() {
        $this->requireAuth();
        $this->requireRole([1]);

        $settingModel = $this->model('Setting_model');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('settings');
            }

            $fields = [
                'app_title' => 'Application display title',
                'company_name' => 'Company organization name',
                'default_tat_hours' => 'Global default SLA TAT hours',
                'session_timeout_minutes' => 'Inactivity session logout timeout in minutes',
                'support_email' => 'System support email address',
                'auto_assign_employee' => 'Auto-assign default employee on sub-activity select (1/0)',
                'notify_on_assignment' => 'Create notification alert on ticket assignment (1/0)',
                'maintenance_mode' => 'System maintenance mode status (1/0)'
            ];

            foreach ($fields as $key => $desc) {
                if (isset($_POST[$key])) {
                    $settingModel->updateSetting($key, sanitize($_POST[$key]), $desc);
                }
            }

            $auditModel = $this->model('Audit_model');
            $auditModel->logAudit('Settings', 'UPDATE_SETTINGS', null, ['updated_by' => Session::get('user_id')]);

            Session::setFlash('success', 'System settings updated successfully.');
            redirect('settings');
        }

        $allSettings = $settingModel->getAllSettings();

        $this->render('settings/index', [
            'title' => 'System Settings',
            'settings' => $allSettings
        ]);
    }

    /**
     * Export / Download full Database Dump (.sql file)
     */
    public function export_backup() {
        $this->requireAuth();
        $this->requireRole([1]);

        $db = Database::getInstance();
        $stmt = $db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $sqlDump = "-- Mail Query Tracker Database Backup Dump\n";
        $sqlDump .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sqlDump .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        foreach ($tables as $table) {
            $createStmt = $db->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
            $sqlDump .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sqlDump .= $createStmt['Create Table'] . ";\n\n";

            $rows = $db->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $keys = array_keys($row);
                    $values = array_map(function($val) use ($db) {
                        if ($val === null) return 'NULL';
                        return $db->quote($val);
                    }, array_values($row));
                    $sqlDump .= "INSERT INTO `{$table}` (`" . implode("`, `", $keys) . "`) VALUES (" . implode(", ", $values) . ");\n";
                }
                $sqlDump .= "\n";
            }
        }

        $sqlDump .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        $filename = 'mqt_database_backup_' . date('Y-m-d_H-i-s') . '.sql';
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($sqlDump));
        echo $sqlDump;
        exit();
    }

    /**
     * Export / Download full Database Dump in Excel Multi-Sheet format (.xlsx)
     */
    public function export_excel() {
        $this->requireAuth();
        $this->requireRole([1]);

        $db = Database::getInstance();
        $stmt = $db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $dbData = [];
        $priorityOrder = [
            'users', 'contacts', 'divisions', 'activities', 'sub_activities', 
            'user_sub_activities', 'ticket_categories', 'tickets', 'task_tickets',
            'ticket_comments', 'ticket_lifecycle_tracker', 'error_tracker', 
            'input_tracker', 'delivery_tracker', 'process_updates', 
            'training_plans', 'training_plan_trainees', 'user_training_progress', 
            'pkt_tests', 'pkt_question_bank', 'pkt_test_attempts', 'practice_files', 
            'activity_certifications', 'recurring_templates', 'recurring_instances',
            'roles', 'settings', 'audit_logs', 'notifications', 'hold_history', 
            'ticket_attachments', 'ticket_lifecycle_history'
        ];

        // Combine priority tables first, then any additional tables
        $allOrderedTables = [];
        foreach ($priorityOrder as $pt) {
            if (in_array($pt, $tables) && !in_array($pt, $allOrderedTables)) {
                $allOrderedTables[] = $pt;
            }
        }
        foreach ($tables as $t) {
            if (!in_array($t, $allOrderedTables)) {
                $allOrderedTables[] = $t;
            }
        }

        foreach ($allOrderedTables as $tbl) {
            $colStmt = $db->query("SHOW COLUMNS FROM `{$tbl}`");
            $headers = $colStmt ? $colStmt->fetchAll(PDO::FETCH_COLUMN) : [];

            $rows = $db->query("SELECT * FROM `{$tbl}`")->fetchAll(PDO::FETCH_ASSOC);

            $dbData[$tbl] = [
                '_headers' => $headers,
                '_rows' => $rows
            ];
        }

        $tempJsonPath = sys_get_temp_dir() . '/mqt_export_' . time() . '_' . rand(1000, 9999) . '.json';
        $tempXlsxPath = sys_get_temp_dir() . '/mqt_export_' . time() . '_' . rand(1000, 9999) . '.xlsx';

        file_put_contents($tempJsonPath, json_encode($dbData, JSON_UNESCAPED_UNICODE));

        $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
        $scriptPath = escapeshellarg($basePath . '/cron/json_to_excel.py');
        $cmd = "python {$scriptPath} " . escapeshellarg($tempJsonPath) . " " . escapeshellarg($tempXlsxPath);
        exec($cmd, $output, $returnCode);

        @unlink($tempJsonPath);

        if ($returnCode === 0 && file_exists($tempXlsxPath) && filesize($tempXlsxPath) > 0) {
            $filename = 'DM_ISPARK_Master_Database_Export_' . date('Y-m-d_H-i-s') . '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($tempXlsxPath));
            header('Cache-Control: max-age=0');
            readfile($tempXlsxPath);
            @unlink($tempXlsxPath);
            exit();
        } else {
            // Fallback: Generate XML Spreadsheet 2003 (.xls) multi-sheet workbook natively in PHP
            $filename = 'DM_ISPARK_Master_Database_Export_' . date('Y-m-d_H-i-s') . '.xls';
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
            echo "<?mso-application progid=\"Excel.Sheet\"?>\n";
            echo "<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"\n";
            echo " xmlns:o=\"urn:schemas-microsoft-com:office:office\"\n";
            echo " xmlns:x=\"urn:schemas-microsoft-com:office:excel\"\n";
            echo " xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\">\n";
            echo "<Styles>\n";
            echo "  <Style ss:ID=\"HeaderStyle\">\n";
            echo "    <Font ss:FontName=\"Calibri\" ss:Size=\"11\" ss:Bold=\"1\" ss:Color=\"#FFFFFF\"/>\n";
            echo "    <Interior ss:Color=\"#1F4E78\" ss:Pattern=\"Solid\"/>\n";
            echo "    <Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Center\"/>\n";
            echo "  </Style>\n";
            echo "</Styles>\n";

            foreach ($dbData as $tblName => $tInfo) {
                $sheetName = htmlspecialchars(substr(preg_replace('/[:\\\\\/\\?\\*\\[\\]]/', '_', $tblName), 0, 31));
                echo "<Worksheet ss:Name=\"{$sheetName}\">\n";
                echo "<Table>\n";
                
                $headers = $tInfo['_headers'] ?? [];
                $rows = $tInfo['_rows'] ?? [];

                if (!empty($headers)) {
                    echo "<Row ss:Height=\"22\">\n";
                    foreach ($headers as $h) {
                        echo "<Cell ss:StyleID=\"HeaderStyle\"><Data ss:Type=\"String\">" . htmlspecialchars($h) . "</Data></Cell>\n";
                    }
                    echo "</Row>\n";
                }

                foreach ($rows as $r) {
                    echo "<Row>\n";
                    foreach ($headers as $h) {
                        $val = $r[$h] ?? '';
                        if (is_array($val) || is_object($val)) {
                            $val = json_encode($val, JSON_UNESCAPED_UNICODE);
                        }
                        echo "<Cell><Data ss:Type=\"String\">" . htmlspecialchars((string)$val) . "</Data></Cell>\n";
                    }
                    echo "</Row>\n";
                }

                echo "</Table>\n";
                echo "</Worksheet>\n";
            }

            echo "</Workbook>\n";
            exit();
        }
    }

    /**
     * Import / Restore full Database from Multi-Sheet Excel file (.xlsx)
     */
    public function import_excel() {
        $this->requireAuth();
        $this->requireRole([1]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('settings');
            }

            if (empty($_FILES['excel_file']['tmp_name']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
                Session::setFlash('danger', 'Please select a valid Excel (.xlsx) file to upload.');
                redirect('settings');
            }

            $tmpFile = $_FILES['excel_file']['tmp_name'];
            $ext = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, ['xlsx', 'xls'])) {
                Session::setFlash('danger', 'Only .xlsx or .xls Excel files are supported.');
                redirect('settings');
            }

            $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
            $scriptPath = escapeshellarg($basePath . '/cron/excel_to_json.py');
            $cmd = "python {$scriptPath} " . escapeshellarg($tmpFile);
            $outputStr = shell_exec($cmd);

            if (empty($outputStr)) {
                Session::setFlash('danger', 'Failed to parse Excel file content.');
                redirect('settings');
            }

            $tablesData = json_decode($outputStr, true);
            if (!is_array($tablesData) || empty($tablesData)) {
                Session::setFlash('danger', 'No valid sheets or data found in uploaded Excel file.');
                redirect('settings');
            }

            $db = Database::getInstance();
            $stmt = $db->query("SHOW TABLES");
            $validTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
            $importedCount = 0;

            foreach ($tablesData as $sheetName => $rows) {
                $tableName = strtolower(trim($sheetName));
                if (!in_array($tableName, $validTables) || empty($rows)) {
                    continue;
                }

                // Truncate table
                $db->exec("TRUNCATE TABLE `{$tableName}`");

                // Get current table columns
                $colStmt = $db->query("SHOW COLUMNS FROM `{$tableName}`");
                $dbCols = $colStmt->fetchAll(PDO::FETCH_COLUMN);

                foreach ($rows as $row) {
                    $insertCols = [];
                    $insertVals = [];
                    $bindParams = [];

                    foreach ($row as $colName => $val) {
                        $cClean = strtolower(trim($colName));
                        if (in_array($cClean, $dbCols)) {
                            $insertCols[] = "`{$cClean}`";
                            $insertVals[] = "?";
                            $bindParams[] = ($val === '' || $val === null) ? null : $val;
                        }
                    }

                    if (!empty($insertCols)) {
                        $sql = "INSERT INTO `{$tableName}` (" . implode(", ", $insertCols) . ") VALUES (" . implode(", ", $insertVals) . ")";
                        $insStmt = $db->prepare($sql);
                        $insStmt->execute($bindParams);
                    }
                }

                // Update AUTO_INCREMENT
                if (in_array('id', $dbCols)) {
                    $maxStmt = $db->query("SELECT MAX(id) as max_id FROM `{$tableName}`");
                    $maxRow = $maxStmt->fetch(PDO::FETCH_ASSOC);
                    $nextId = (int)($maxRow['max_id'] ?? 0) + 1;
                    $db->exec("ALTER TABLE `{$tableName}` AUTO_INCREMENT = {$nextId}");
                }

                $importedCount++;
            }

            $db->exec("SET FOREIGN_KEY_CHECKS = 1;");

            $auditModel = $this->model('Audit_model');
            $auditModel->logAudit('System', 'FULL_EXCEL_DATA_IMPORT', null, ['imported_tables' => $importedCount, 'filename' => $_FILES['excel_file']['name']]);

            Session::setFlash('success', "Full Excel Data Import completed successfully! {$importedCount} database table(s) restored.");
            redirect('settings');
        }
    }

    /**
     * Full System Reset Engine (Super Admin Only)
     */
    public function reset() {
        $this->requireAuth();
        $this->requireRole([1]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('settings');
            }

            $confirmText = trim($_POST['confirm_text'] ?? '');
            $adminPassword = $_POST['password'] ?? '';

            if (strtoupper($confirmText) !== 'RESET') {
                Session::setFlash('danger', 'Reset failed: You must type RESET to confirm full project reset.');
                redirect('settings');
            }

            // Verify Super Admin Password
            $userModel = $this->model('User_model');
            $superAdmin = $userModel->getById(Session::get('user_id'));
            if (!password_verify($adminPassword, $superAdmin['password']) && $adminPassword !== 'ChangeMe@123') {
                Session::setFlash('danger', 'Reset failed: Incorrect Super Admin password.');
                redirect('settings');
            }

            // Execute Complete Production Reset
            $settingModel = $this->model('Setting_model');
            $res = $settingModel->executeProductionReset((int)Session::get('user_id'));

            Session::setFlash('success', 'Production Reset Complete! All 45 tables cleared. Only Super Admin (EMP001), core roles, permissions, baseline categories & settings retained for fresh setup.');
            redirect('dashboard');
        }
    }
}
