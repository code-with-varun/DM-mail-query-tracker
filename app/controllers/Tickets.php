<?php
/**
 * Tickets Controller (Mail Query & Task Tickets)
 */

class Tickets extends Controller {
    public function index() {
        $this->requireAuth();
        $ticketModel = $this->model('Ticket_model');
        $activityModel = $this->model('Activity_model');
        $userModel = $this->model('User_model');

        $filters = [
            'status' => sanitize($_GET['status'] ?? ''),
            'ticket_type' => sanitize($_GET['type'] ?? ''),
            'activity_id' => sanitize($_GET['activity_id'] ?? ''),
            'allocated_to' => sanitize($_GET['allocated_to'] ?? ''),
            'search' => sanitize($_GET['search'] ?? '')
        ];

        $tickets = $ticketModel->getTickets($filters, Session::get('user_id'), Session::get('role_id'));

        $this->render('tickets/index', [
            'title' => 'Mail Query & Task Tickets',
            'tickets' => $tickets,
            'filters' => $filters,
            'activities' => $activityModel->getActivities(),
            'users' => $userModel->getEmployees()
        ]);
    }

    public function create() {
        $this->requireAuth();
        $activityModel = $this->model('Activity_model');
        $userModel = $this->model('User_model');
        $ticketModel = $this->model('Ticket_model');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('tickets/create');
            }

            $activityId = (int)($_POST['activity_id'] ?? 0);
            $subActivityId = (int)($_POST['sub_activity_id'] ?? 0);
            $receivedDatetime = sanitize($_POST['received_datetime'] ?? date('Y-m-d H:i'));
            $fromAddress = sanitize($_POST['from_address'] ?? '');
            $subject = sanitize($_POST['subject'] ?? '');

            if (!$activityId || !$subActivityId || empty($fromAddress) || empty($subject)) {
                Session::setFlash('danger', 'Please fill in all mandatory fields.');
                redirect('tickets/create');
            }

            // Sub Activity TAT hours calculation
            $subActs = $activityModel->getSubActivitiesByActivity($activityId);
            $defaultHours = 24;
            foreach ($subActs as $sa) {
                if ($sa['id'] == $subActivityId) {
                    $defaultHours = (int)$sa['default_tat_hours'];
                    break;
                }
            }

            $tatDatetime = !empty($_POST['tat_datetime']) 
                ? date('Y-m-d H:i:s', strtotime($_POST['tat_datetime']))
                : date('Y-m-d H:i:s', strtotime("+{$defaultHours} hours"));

            $ticketData = [
                'ticket_type' => sanitize($_POST['ticket_type'] ?? 'Query Ticket'),
                'received_datetime' => date('Y-m-d H:i:s', strtotime($receivedDatetime)),
                'from_address' => $fromAddress,
                'subject' => $subject,
                'division_id' => !empty($_POST['division_id']) ? (int)$_POST['division_id'] : null,
                'activity_id' => $activityId,
                'sub_activity_id' => $subActivityId,
                'status' => !empty($_POST['allocated_to']) ? 'Assigned' : 'New',
                'priority' => sanitize($_POST['priority'] ?? 'Medium'),
                'tat_datetime' => $tatDatetime,
                'allocated_to' => !empty($_POST['allocated_to']) ? (int)$_POST['allocated_to'] : null,
                'agency_code' => sanitize($_POST['agency_code'] ?? ''),
                'manager_name' => sanitize($_POST['manager_name'] ?? ''),
                'remarks' => sanitize($_POST['remarks'] ?? ''),
                'created_by' => Session::get('user_id')
            ];

            $ticketId = $ticketModel->createTicket($ticketData);

            // Handle Attachment Upload if present
            if (!empty($_FILES['attachment']['name'])) {
                $uploadDir = __DIR__ . '/../../public/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                
                $fileName = time() . '_' . basename($_FILES['attachment']['name']);
                $targetFile = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetFile)) {
                    $ticketModel->insert('ticket_attachments', [
                        'ticket_id' => $ticketId,
                        'file_name' => $_FILES['attachment']['name'],
                        'file_path' => 'public/uploads/' . $fileName,
                        'file_size' => $_FILES['attachment']['size'],
                        'uploaded_by' => Session::get('user_id'),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            Session::setFlash('success', 'Ticket created successfully!');
            redirect('tickets/view/' . $ticketId);
        }

        $this->render('tickets/create', [
            'title' => 'Create New Ticket',
            'activities' => $activityModel->getActivities(),
            'divisions' => $activityModel->getDivisions(),
            'users' => $userModel->getEmployees()
        ]);
    }

    public function view(int $id) {
        $this->requireAuth();
        $ticketModel = $this->model('Ticket_model');
        $ticket = $ticketModel->getTicketById($id);

        if (!$ticket) {
            Session::setFlash('danger', 'Ticket not found.');
            redirect('tickets');
        }

        $comments = $ticketModel->getComments($id);
        $holdHistory = $ticketModel->getHoldHistory($id);
        $attachments = $ticketModel->fetchAll("SELECT * FROM ticket_attachments WHERE ticket_id = ?", [$id]);
        $userModel = $this->model('User_model');

        $this->render('tickets/view', [
            'title' => 'Ticket ' . $ticket['ticket_number'],
            'ticket' => $ticket,
            'comments' => $comments,
            'holdHistory' => $holdHistory,
            'attachments' => $attachments,
            'users' => $userModel->getEmployees()
        ]);
    }

    public function update_status() {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('tickets');
            }

            $ticketId = (int)($_POST['ticket_id'] ?? 0);
            $status = sanitize($_POST['status'] ?? '');
            $remarks = sanitize($_POST['remarks'] ?? '');

            $ticketModel = $this->model('Ticket_model');
            if ($ticketModel->updateStatus($ticketId, $status, $remarks, Session::get('user_id'))) {
                Session::setFlash('success', 'Ticket status updated successfully.');
            } else {
                Session::setFlash('danger', 'Failed to update ticket status.');
            }
            redirect('tickets/view/' . $ticketId);
        }
    }
}
