<?php
/**
 * Audit Logs Controller
 */

class Audit extends Controller {
    public function index() {
        $this->requireAuth();
        $this->requireRole([1]); // Super Admin only

        $auditModel = $this->model('Audit_model');
        $logs = $auditModel->getLogs();

        $this->render('audit/index', [
            'title' => 'System Audit Logs',
            'logs' => $logs
        ]);
    }
}
