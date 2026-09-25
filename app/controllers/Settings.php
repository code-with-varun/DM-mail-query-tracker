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
}
