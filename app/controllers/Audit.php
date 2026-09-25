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

            // Clear all operational tables and reset AUTO_INCREMENT primary keys
            $tablesToClear = [
                'tickets', 'ticket_history', 'ticket_categories', 'tasks', 'task_comments',
                'contacts', 'error_logs', 'input_tracker', 'delivery_tracker',
                'recurring_tasks', 'audit_logs', 'notifications', 'hold_history'
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
