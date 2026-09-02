<?php
/**
 * Recurring Tasks Controller
 */

class Recurring extends Controller {
    public function index() {
        $this->requireAuth();
        $this->requireRole([1, 2]); // Super Admin or Admin

        $recurringModel = $this->model('Recurring_model');
        $activityModel = $this->model('Activity_model');
        $userModel = $this->model('User_model');

        $templates = $recurringModel->getTemplates(Session::get('user_id'), Session::get('role_id'));

        $this->render('recurring/index', [
            'title' => 'Recurring Task Templates',
            'templates' => $templates,
            'activities' => $activityModel->getActivities(),
            'users' => $userModel->getEmployees()
        ]);
    }

    public function create() {
        $this->requireAuth();
        $this->requireRole([1, 2]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('recurring');
            }

            $tmplName = sanitize($_POST['template_name'] ?? '');
            $assignedUser = (int)($_POST['assigned_user_id'] ?? 0);
            $activityId = (int)($_POST['activity_id'] ?? 0);
            $subActivityId = (int)($_POST['sub_activity_id'] ?? 0);

            if (empty($tmplName) || !$assignedUser || !$activityId || !$subActivityId) {
                Session::setFlash('danger', 'Please fill in all mandatory fields.');
                redirect('recurring');
            }

            $recurringModel = $this->model('Recurring_model');
            $recurringModel->createTemplate([
                'template_name' => $tmplName,
                'description' => sanitize($_POST['description'] ?? ''),
                'activity_id' => $activityId,
                'sub_activity_id' => $subActivityId,
                'assigned_user_id' => $assignedUser,
                'frequency' => sanitize($_POST['frequency'] ?? 'Monthly'),
                'due_day' => (int)($_POST['due_day'] ?? 5),
                'priority' => sanitize($_POST['priority'] ?? 'Medium'),
                'start_date' => sanitize($_POST['start_date'] ?? date('Y-m-d')),
                'end_date' => !empty($_POST['end_date']) ? sanitize($_POST['end_date']) : null,
                'is_active' => 1,
                'created_by' => Session::get('user_id')
            ]);

            Session::setFlash('success', 'Recurring Task Template created successfully!');
            redirect('recurring');
        }
    }

    public function trigger_scheduler() {
        $this->requireAuth();
        $this->requireRole([1, 2]);

        $recurringModel = $this->model('Recurring_model');
        $result = $recurringModel->processScheduler();

        Session::setFlash('success', "Scheduler ran successfully! {$result['generated_count']} recurring tickets generated.");
        redirect('recurring');
    }
}
