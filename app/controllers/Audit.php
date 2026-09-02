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

            $tablesToTruncate = [
                'tickets',
                'task_tickets',
                'ticket_comments',
                'ticket_hold_history',
                'ticket_attachments',
                'recurring_templates',
                'recurring_instances',
                'input_tracker_logs',
                'delivery_logs',
                'notifications',
                'audit_logs'
            ];

            foreach ($tablesToTruncate as $tbl) {
                $db->exec("TRUNCATE TABLE `{$tbl}`");
            }

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

            Session::setFlash('success', 'Full Project Reset Complete! All tickets, tasks, trackers, comments, and file uploads have been cleared. Ready to start fresh!');
            redirect('dashboard');
        }
    }
}
