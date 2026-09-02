<?php
/**
 * Tasks Controller (Internal Task Tickets)
 */

class Tasks extends Controller {
    public function index() {
        $this->requireAuth();
        $taskModel = $this->model('Task_model');
        $tasks = $taskModel->getTasks(Session::get('user_id'), Session::get('role_id'));

        $this->render('tasks/index', [
            'title' => 'Internal Task Tickets',
            'tasks' => $tasks
        ]);
    }

    public function create() {
        $this->requireAuth();
        $activityModel = $this->model('Activity_model');
        $userModel = $this->model('User_model');
        $taskModel = $this->model('Task_model');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('tasks/create');
            }

            $title = sanitize($_POST['task_title'] ?? '');
            $assignedTo = (int)($_POST['assigned_to'] ?? 0);
            $dueDate = sanitize($_POST['due_date'] ?? date('Y-m-d H:i'));

            if (empty($title) || !$assignedTo) {
                Session::setFlash('danger', 'Task title and assigned employee are required.');
                redirect('tasks/create');
            }

            $activityId = !empty($_POST['activity_id']) ? (int)$_POST['activity_id'] : 3;
            $subActivityId = !empty($_POST['sub_activity_id']) ? (int)$_POST['sub_activity_id'] : 7;

            $ticketData = [
                'ticket_type' => 'Task Ticket',
                'received_datetime' => date('Y-m-d H:i:s'),
                'from_address' => 'INTERNAL_TASK',
                'subject' => "[Task] " . $title,
                'activity_id' => $activityId,
                'sub_activity_id' => $subActivityId,
                'status' => 'Assigned',
                'priority' => sanitize($_POST['priority'] ?? 'Medium'),
                'tat_datetime' => date('Y-m-d H:i:s', strtotime($dueDate)),
                'allocated_to' => $assignedTo,
                'remarks' => sanitize($_POST['description'] ?? ''),
                'created_by' => Session::get('user_id')
            ];

            $taskData = [
                'task_title' => $title,
                'description' => sanitize($_POST['description'] ?? ''),
                'priority' => sanitize($_POST['priority'] ?? 'Medium'),
                'due_date' => date('Y-m-d H:i:s', strtotime($dueDate)),
                'status' => 'Pending'
            ];

            $ticketId = $taskModel->createTask($ticketData, $taskData);

            Session::setFlash('success', 'Task ticket created successfully!');
            redirect('tickets/view/' . $ticketId);
        }

        $this->render('tasks/create', [
            'title' => 'Create Task Ticket',
            'activities' => $activityModel->getActivities(),
            'users' => $userModel->getEmployees()
        ]);
    }
}
