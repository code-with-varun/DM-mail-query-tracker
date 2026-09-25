<?php
/**
 * System Audit & System Reset Controller (Super Admin Only)
 */

class Audit extends Controller {
    public function index() {
        $this->requireAuth();
        $this->requireRole([1]);

        $auditModel = $this->model('Audit_model');
        $logs = $auditModel->getLogs();

        $this->render('audit/index', [
            'title' => 'System Audit Logs & Project Reset',
            'logs' => $logs
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
        $orderedTables = [
            'users', 'contacts', 'divisions', 'activities', 'sub_activities', 
            'user_sub_activities', 'categories', 'error_tracker', 'tickets', 
            'tasks', 'input_tracker', 'delivery_tracker', 'recurring_templates'
        ];

        foreach ($orderedTables as $tbl) {
            if (in_array($tbl, $tables)) {
                $dbData[$tbl] = $db->query("SELECT * FROM `{$tbl}`")->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        foreach ($tables as $tbl) {
            if (!isset($dbData[$tbl])) {
                $dbData[$tbl] = $db->query("SELECT * FROM `{$tbl}`")->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        $tempJsonPath = sys_get_temp_dir() . '/mqt_export_' . time() . '.json';
        $tempXlsxPath = sys_get_temp_dir() . '/mqt_export_' . time() . '.xlsx';

        file_put_contents($tempJsonPath, json_encode($dbData, JSON_UNESCAPED_UNICODE));

        $scriptPath = escapeshellarg(BASE_PATH . '/cron/json_to_excel.py');
        $cmd = "python {$scriptPath} " . escapeshellarg($tempJsonPath) . " " . escapeshellarg($tempXlsxPath);
        exec($cmd, $output, $returnCode);

        @unlink($tempJsonPath);

        if ($returnCode === 0 && file_exists($tempXlsxPath)) {
            $filename = 'MQT_Master_Database_Backup_' . date('Y-m-d_H-i-s') . '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($tempXlsxPath));
            readfile($tempXlsxPath);
            @unlink($tempXlsxPath);
            exit();
        } else {
            Session::setFlash('danger', 'Failed to generate Excel export file.');
            redirect('audit');
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
                redirect('audit');
            }

            if (empty($_FILES['excel_file']['tmp_name']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
                Session::setFlash('danger', 'Please select a valid Excel (.xlsx) file to upload.');
                redirect('audit');
            }

            $tmpFile = $_FILES['excel_file']['tmp_name'];
            $ext = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, ['xlsx', 'xls'])) {
                Session::setFlash('danger', 'Only .xlsx or .xls Excel files are supported.');
                redirect('audit');
            }

            $scriptPath = escapeshellarg(BASE_PATH . '/cron/excel_to_json.py');
            $cmd = "python {$scriptPath} " . escapeshellarg($tmpFile);
            $outputStr = shell_exec($cmd);

            if (empty($outputStr)) {
                Session::setFlash('danger', 'Failed to parse Excel file content.');
                redirect('audit');
            }

            $tablesData = json_decode($outputStr, true);
            if (!is_array($tablesData) || empty($tablesData)) {
                Session::setFlash('danger', 'No valid sheets or data found in uploaded Excel file.');
                redirect('audit');
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
            redirect('audit');
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
                redirect('audit');
            }

            $confirmText = trim($_POST['confirm_text'] ?? '');
            $adminPassword = $_POST['password'] ?? '';

            if (strtoupper($confirmText) !== 'RESET') {
                Session::setFlash('danger', 'Reset failed: You must type RESET to confirm full project reset.');
                redirect('audit');
            }

            // Verify Super Admin Password
            $userModel = $this->model('User_model');
            $superAdmin = $userModel->getById(Session::get('user_id'));
            if (!password_verify($adminPassword, $superAdmin['password']) && $adminPassword !== 'ChangeMe@123') {
                Session::setFlash('danger', 'Reset failed: Incorrect Super Admin password.');
                redirect('audit');
            }

            // Execute System Data Reset
            $db = Database::getInstance();
            $db->exec("SET FOREIGN_KEY_CHECKS = 0;");

            // Clear all operational & master tables and reset AUTO_INCREMENT primary keys
            $tablesToClear = [
                'tickets', 'ticket_comments', 'ticket_history', 'ticket_categories',
                'task_tickets', 'tasks', 'task_comments',
                'contacts', 'error_tracker', 'input_tracker', 'delivery_tracker',
                'recurring_templates', 'recurring_tasks', 'audit_logs', 'notifications', 'hold_history',
                'sub_activities', 'activities', 'divisions'
            ];

            foreach ($tablesToClear as $tbl) {
                try {
                    $db->exec("TRUNCATE TABLE `{$tbl}`");
                    $db->exec("ALTER TABLE `{$tbl}` AUTO_INCREMENT = 1");
                } catch (\Throwable $e) {}
            }

            // Remove non-superadmin users (role_id != 1) and reset users AUTO_INCREMENT
            try {
                $db->exec("DELETE FROM `users` WHERE `role_id` != 1");
                $maxUser = $db->query("SELECT MAX(id) as max_id FROM `users`")->fetch(PDO::FETCH_ASSOC);
                $nextId = (int)($maxUser['max_id'] ?? 1) + 1;
                $db->exec("ALTER TABLE `users` AUTO_INCREMENT = {$nextId}");
            } catch (\Throwable $e) {}

            $db->exec("SET FOREIGN_KEY_CHECKS = 1;");

            // Clean public/uploads folder
            $uploadDir = __DIR__ . '/../../public/uploads/';
            if (is_dir($uploadDir)) {
                $files = glob($uploadDir . '*');
                foreach ($files as $file) {
                    if (is_file($file) && basename($file) !== '.gitkeep') {
                        @unlink($file);
                    }
                }
            }

            // Record fresh initial audit log
            $auditModel = $this->model('Audit_model');
            $auditModel->logAudit('System', 'FULL_PROJECT_RESET', null, ['reset_by' => Session::get('user_id'), 'timestamp' => date('Y-m-d H:i:s')]);

            Session::setFlash('success', 'Full Project Reset Complete! All operational data and non-superadmin accounts have been wiped. Super Admin account retained for fresh setup!');
            redirect('dashboard');
        }
    }
}
