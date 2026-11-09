<?php
/**
 * Tracker Controller (Input & Delivery Trackers)
 */

class Tracker extends Controller {
    public function input() {
        $this->requireAuth();
        $trackerModel = $this->model('Tracker_model');
        $userModel = $this->model('User_model');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('tracker/input');
            }

            $source = sanitize($_POST['source'] ?? '');
            $receivedFrom = sanitize($_POST['received_from'] ?? '');
            $docRef = sanitize($_POST['document_reference'] ?? '');

            if (empty($source) || empty($receivedFrom) || empty($docRef)) {
                Session::setFlash('danger', 'Source, Received From, and Document Reference are mandatory.');
                redirect('tracker/input');
            }

            $trackerModel->createInputLog([
                'source' => $source,
                'received_date' => date('Y-m-d H:i:s', strtotime($_POST['received_date'] ?? 'now')),
                'received_from' => $receivedFrom,
                'document_reference' => $docRef,
                'assigned_to' => !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null,
                'status' => sanitize($_POST['status'] ?? 'Received'),
                'remarks' => sanitize($_POST['remarks'] ?? ''),
                'created_by' => Session::get('user_id')
            ]);

            Session::setFlash('success', 'Input item logged successfully.');
            redirect('tracker/input');
        }

        $logs = $trackerModel->getInputLogs();
        $this->render('tracker/input', [
            'title' => 'Input Tracker',
            'logs' => $logs,
            'users' => $userModel->getEmployees()
        ]);
    }

    public function delivery() {
        $this->requireAuth();
        $trackerModel = $this->model('Tracker_model');
        $ticketModel = $this->model('Ticket_model');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('tracker/delivery');
            }

            $deliveredTo = sanitize($_POST['delivered_to'] ?? '');
            $mode = sanitize($_POST['delivery_mode'] ?? 'Email');

            if (empty($deliveredTo)) {
                Session::setFlash('danger', 'Delivered To field is required.');
                redirect('tracker/delivery');
            }

            $attachmentPath = null;
            if (!empty($_FILES['attachment']['name'])) {
                $uploadDir = __DIR__ . '/../../public/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $fileName = time() . '_del_' . basename($_FILES['attachment']['name']);
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadDir . $fileName)) {
                    $attachmentPath = 'public/uploads/' . $fileName;
                }
            }

            $trackerModel->createDeliveryLog([
                'ticket_id' => !empty($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : null,
                'delivered_to' => $deliveredTo,
                'delivery_date' => date('Y-m-d H:i:s', strtotime($_POST['delivery_date'] ?? 'now')),
                'delivery_mode' => $mode,
                'ack_received' => sanitize($_POST['ack_received'] ?? 'Pending'),
                'remarks' => sanitize($_POST['remarks'] ?? ''),
                'attachment_path' => $attachmentPath,
                'created_by' => Session::get('user_id')
            ]);

            Session::setFlash('success', 'Output / Delivery log created successfully.');
            redirect('tracker/delivery');
        }

        $logs = $trackerModel->getDeliveryLogs();
        $this->render('tracker/delivery', [
            'title' => 'Output / Delivery Tracker',
            'logs' => $logs,
            'tickets' => $ticketModel->getTickets([], Session::get('user_id'), Session::get('role_id'))
        ]);
    }

    public function query() {
        $this->requireAuth();
        $ticketModel = $this->model('Ticket_model');
        $userModel = $this->model('User_model');
        $activityModel = $this->model('Activity_model');

        $rawStatus = $_GET['status'] ?? [];
        $rawActivity = $_GET['activity_id'] ?? [];
        $rawAssignee = $_GET['allocated_to'] ?? [];

        $filters = [
            'search' => sanitize($_GET['search'] ?? ''),
            'status' => is_array($rawStatus) ? array_map('sanitize', $rawStatus) : (empty($rawStatus) ? [] : [sanitize($rawStatus)]),
            'activity_id' => is_array($rawActivity) ? array_map('intval', $rawActivity) : (!empty($rawActivity) ? [(int)$rawActivity] : []),
            'allocated_to' => is_array($rawAssignee) ? array_map('intval', $rawAssignee) : (!empty($rawAssignee) ? [(int)$rawAssignee] : [])
        ];

        $userId = Session::get('user_id');
        $roleId = Session::get('role_id');

        $tickets = $ticketModel->getQueryTickets($filters, $userId, $roleId);
        $stats = $ticketModel->getQueryTrackerStats($filters, $userId, $roleId);

        $this->render('tracker/query', [
            'title' => 'Query Tracker',
            'tickets' => $tickets,
            'stats' => $stats,
            'filters' => $filters,
            'users' => $userModel->getEmployees(),
            'activities' => $activityModel->getActivities()
        ]);
    }

    public function timesheet() {
        $this->requireAuth();
        $trackerModel = $this->model('Tracker_model');
        $userModel = $this->model('User_model');
        $projectModel = $this->model('Project_model');
        $activityModel = $this->model('Activity_model');

        $loggedUserId = (int)$_SESSION['user_id'];
        $loggedRoleId = (int)$_SESSION['role_id'];

        $filters = [];
        if (!is_super_admin() && !is_senior_management() && !is_manager() && !is_team_lead()) {
            $filters['user_id'] = $loggedUserId;
        } elseif (!empty($_GET['user_id'])) {
            $reqUserId = (int)$_GET['user_id'];
            $downlineIds = $userModel->getDownlineUserIds($loggedUserId, $loggedRoleId);
            if (in_array($reqUserId, $downlineIds)) {
                $filters['user_id'] = $reqUserId;
            } else {
                $filters['user_id'] = $loggedUserId;
            }
        }

        if (!empty($_GET['start_date'])) $filters['start_date'] = sanitize($_GET['start_date']);
        if (!empty($_GET['end_date'])) $filters['end_date'] = sanitize($_GET['end_date']);
        if (!empty($_GET['project_id'])) $filters['project_id'] = (int)$_GET['project_id'];

        $logs = $trackerModel->getTimesheetLogs($filters);
        $employees = $userModel->getDownlineEmployees($loggedUserId, $loggedRoleId);
        $projects = $projectModel->getProjects();
        $activities = $activityModel->getActivities();

        $this->render('tracker/timesheet', [
            'title' => 'Daily Timesheet & Effort Tracker',
            'logs' => $logs,
            'employees' => $employees,
            'projects' => $projects,
            'activities' => $activities,
            'filters' => $filters
        ]);
    }

    public function store_timesheet() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('tracker/timesheet');
            }

            $trackerModel = $this->model('Tracker_model');
            $data = [
                'user_id' => !empty($_POST['user_id']) ? (int)$_POST['user_id'] : (int)$_SESSION['user_id'],
                'project_id' => !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null,
                'activity_id' => !empty($_POST['activity_id']) ? (int)$_POST['activity_id'] : null,
                'sub_activity_id' => !empty($_POST['sub_activity_id']) ? (int)$_POST['sub_activity_id'] : null,
                'log_date' => sanitize($_POST['log_date'] ?? date('Y-m-d')),
                'hours_spent' => (float)($_POST['hours_spent'] ?? 8.0),
                'task_description' => sanitize($_POST['task_description'] ?? 'Operations processing and transaction delivery'),
                'status' => 'Submitted'
            ];

            $trackerModel->createTimesheetLog($data);
            Session::setFlash('success', 'Timesheet effort logged successfully.');
        }

        redirect('tracker/timesheet');
    }

    public function delete_timesheet($id = null) {
        $this->requireAuth();
        $id = (int)$id;
        if ($id > 0) {
            $trackerModel = $this->model('Tracker_model');
            $trackerModel->deleteTimesheetLog($id);
            Session::setFlash('success', 'Timesheet record removed.');
        }
        redirect('tracker/timesheet');
    }
}

