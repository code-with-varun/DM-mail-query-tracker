<?php
/**
 * Ticket Controller
 */

class Tickets extends Controller {
    public function index() {
        $this->requireAuth();

        $ticketModel = $this->model('Ticket_model');
        $activityModel = $this->model('Activity_model');
        $userModel = $this->model('User_model');

        $filters = [
            'search' => sanitize($_GET['search'] ?? ''),
            'status' => sanitize($_GET['status'] ?? ''),
            'activity_id' => !empty($_GET['activity_id']) ? (int)$_GET['activity_id'] : null,
            'allocated_to' => !empty($_GET['allocated_to']) ? (int)$_GET['allocated_to'] : null,
            'date_from' => sanitize($_GET['date_from'] ?? ''),
            'date_to' => sanitize($_GET['date_to'] ?? '')
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

            // Sub Activity TAT hours & default employee mapping lookup
            $subActs = $activityModel->getSubActivitiesByActivity($activityId);
            $defaultHours = 24;
            $defaultUserId = null;
            foreach ($subActs as $sa) {
                if ($sa['id'] == $subActivityId) {
                    $defaultHours = (int)$sa['default_tat_hours'];
                    $defaultUserId = !empty($sa['default_user_id']) ? (int)$sa['default_user_id'] : null;
                    break;
                }
            }

            $allocatedTo = !empty($_POST['allocated_to']) ? (int)$_POST['allocated_to'] : $defaultUserId;

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
                'status' => $allocatedTo ? 'Assigned' : 'New',
                'priority' => sanitize($_POST['priority'] ?? 'Medium'),
                'tat_datetime' => $tatDatetime,
                'allocated_to' => $allocatedTo,
                'agency_code' => sanitize($_POST['agency_code'] ?? ''),
                'manager_name' => sanitize($_POST['manager_name'] ?? ''),
                'remarks' => sanitize($_POST['remarks'] ?? ''),
                'created_by' => Session::get('user_id')
            ];

            $ticketId = $ticketModel->createTicket($ticketData);

            // File Attachment Handling
            if (!empty($_FILES['attachment']['name'])) {
                $uploadDir = __DIR__ . '/../../public/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

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

    /**
     * Download Clean CSV Ticket Import Template (Simplified: No Activity/Division columns)
     */
    public function template() {
        $this->requireAuth();
        $this->requireRole([1, 2]);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=MQT_Ticket_Import_Template.csv');

        $output = fopen('php://output', 'w');
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Simplified CSV Header (Activity & Division automatically inferred from Sub Activity)
        fputcsv($output, [
            'Ticket Type',
            'Received Datetime',
            'From Address',
            'Subject',
            'Sub Activity Name',
            'Priority',
            'Allocated Employee Code',
            'Agency Code',
            'Manager Name',
            'Remarks'
        ]);

        // Sample Data Rows
        fputcsv($output, [
            'Query Ticket',
            date('Y-m-d H:i:s'),
            'vendor.billing@client.com',
            'Sample Query Regarding Payment Delay',
            'Billing Query',
            'Medium',
            '', // Auto-assigned from Sub-Activity Default Assignee (EMP002)
            'AGC101',
            'John Doe',
            'Sample imported query ticket'
        ]);

        fputcsv($output, [
            'Task Ticket',
            date('Y-m-d H:i:s'),
            'INTERNAL_TASK',
            'Sample Internal Compliance Task',
            'Internal Support',
            'High',
            '', // Auto-assigned
            '',
            '',
            'Sample imported task ticket'
        ]);

        fclose($output);
        exit();
    }

    /**
     * Unified Bulk Ticket CSV Import Engine
     */
    public function import() {
        $this->requireAuth();
        $this->requireRole([1, 2]);

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->render('tickets/import', [
                'title' => 'Bulk Ticket Import'
            ]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('tickets/import');
            }

            if (empty($_FILES['csv_file']['tmp_name'])) {
                Session::setFlash('danger', 'Please select a CSV file to upload.');
                redirect('tickets/import');
            }

            $filePath = $_FILES['csv_file']['tmp_name'];
            $handle = fopen($filePath, 'r');
            if (!$handle) {
                Session::setFlash('danger', 'Failed to read uploaded file.');
                redirect('tickets/import');
            }

            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $header = fgetcsv($handle);
            if (!$header || count($header) < 3) {
                Session::setFlash('danger', 'Invalid CSV format or missing headers.');
                fclose($handle);
                redirect('tickets/import');
            }

            $ticketModel = $this->model('Ticket_model');
            $activityModel = $this->model('Activity_model');
            $userModel = $this->model('User_model');

            $employees = $userModel->fetchAll("SELECT id, user_code, email, full_name FROM users WHERE status = 'Active'");

            $successCount = 0;
            $errorCount = 0;

            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 3 || empty(array_filter($row))) continue;

                $ticketType = sanitize($row[0] ?? 'Query Ticket');
                $receivedDatetime = !empty($row[1]) ? date('Y-m-d H:i:s', strtotime($row[1])) : date('Y-m-d H:i:s');
                $fromAddress = sanitize($row[2] ?? 'N/A');
                $subject = sanitize($row[3] ?? '');
                $subActivityName = sanitize($row[4] ?? '');
                $priority = sanitize($row[5] ?? 'Medium');
                $empCode = sanitize($row[6] ?? '');
                $agencyCode = sanitize($row[7] ?? '');
                $managerName = sanitize($row[8] ?? '');
                $remarks = sanitize($row[9] ?? '');

                if (empty($subject)) {
                    $errorCount++;
                    continue;
                }

                // Look up Sub Activity Detail for auto-population of Division, Activity, and Default Employee
                $subDetail = null;
                if (!empty($subActivityName)) {
                    $subDetail = $activityModel->getSubActivityDetailByName($subActivityName);
                }

                $activityId = $subDetail['activity_id'] ?? 1;
                $subActivityId = $subDetail['id'] ?? 1;
                $defaultTatHours = $subDetail['default_tat_hours'] ?? 24;
                $divisionId = $subDetail['division_id'] ?? null;
                $allocatedTo = $subDetail['default_user_id'] ?? null;

                // Optional Employee Override if specified in CSV
                if (!empty($empCode)) {
                    foreach ($employees as $emp) {
                        if (strcasecmp($emp['user_code'] ?? '', $empCode) === 0 || strcasecmp($emp['full_name'] ?? '', $empCode) === 0 || strcasecmp($emp['email'] ?? '', $empCode) === 0) {
                            $allocatedTo = $emp['id'];
                            break;
                        }
                    }
                }

                $tatDatetime = date('Y-m-d H:i:s', strtotime("+{$defaultTatHours} hours"));

                $ticketData = [
                    'ticket_type' => in_array($ticketType, ['Task Ticket', 'Task']) ? 'Task Ticket' : 'Query Ticket',
                    'received_datetime' => $receivedDatetime,
                    'from_address' => $fromAddress,
                    'subject' => $subject,
                    'division_id' => $divisionId,
                    'activity_id' => $activityId,
                    'sub_activity_id' => $subActivityId,
                    'status' => $allocatedTo ? 'Assigned' : 'New',
                    'priority' => in_array($priority, ['Low', 'Medium', 'High', 'Critical']) ? $priority : 'Medium',
                    'tat_datetime' => $tatDatetime,
                    'allocated_to' => $allocatedTo,
                    'agency_code' => $agencyCode,
                    'manager_name' => $managerName,
                    'remarks' => $remarks,
                    'created_by' => Session::get('user_id')
                ];

                try {
                    $ticketId = $ticketModel->createTicket($ticketData);
                    if ($ticketData['ticket_type'] === 'Task Ticket') {
                        $this->model('Task_model')->insert('task_tickets', [
                            'ticket_id' => $ticketId,
                            'task_title' => $subject,
                            'description' => $remarks,
                            'priority' => $ticketData['priority'],
                            'due_date' => $tatDatetime,
                            'status' => 'Pending',
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                }
            }

            fclose($handle);

            Session::setFlash('success', "Bulk Import Complete: Successfully imported {$successCount} tickets with auto-populated Divisions, Activities & Assignees" . ($errorCount > 0 ? " ({$errorCount} rows skipped/failed)." : "."));
            redirect('tickets');
        }
    }

    /**
     * Delete Ticket (Super Admin Only)
     */
    public function delete(int $id) {
        $this->requireAuth();
        $this->requireRole([1]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('tickets');
            }

            $ticketModel = $this->model('Ticket_model');
            $ticketModel->delete('tickets', "id = ?", [$id]);
            $ticketModel->delete('task_tickets', "ticket_id = ?", [$id]);
            $ticketModel->delete('ticket_comments', "ticket_id = ?", [$id]);
            $ticketModel->delete('ticket_hold_history', "ticket_id = ?", [$id]);
            $ticketModel->delete('ticket_attachments', "ticket_id = ?", [$id]);

            Session::setFlash('success', 'Ticket deleted successfully.');
            redirect('tickets');
        }
    }
}
