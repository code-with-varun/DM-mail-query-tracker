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
     * Download Multi-Sheet Excel Ticket Import Template
     */
    public function template() {
        $this->requireAuth();
        $this->requireRole([1, 2]);

        $activityModel = $this->model('Activity_model');
        $userModel = $this->model('User_model');

        $activities = $activityModel->getActivities();
        $subActivities = $activityModel->fetchAll("SELECT sa.*, a.activity_name FROM sub_activities sa JOIN activities a ON sa.activity_id = a.id");
        $divisions = $activityModel->getDivisions();
        $employees = $userModel->getEmployees();

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="MQT_Tickets_Import_Template.xls"');

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
        ?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheets"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheets"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Color="#000000"/>
  </Style>
  <Style ss:ID="Header">
   <Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#FFFFFF"/>
   <Interior ss:Color="#1E3A8A" ss:Pattern="Solid"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
  </Style>
  <Style ss:ID="MasterHeader">
   <Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#FFFFFF"/>
   <Interior ss:Color="#0F172A" ss:Pattern="Solid"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
  </Style>
  <Style ss:ID="SubHeader">
   <Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#1E293B"/>
   <Interior ss:Color="#E2E8F0" ss:Pattern="Solid"/>
  </Style>
 </Styles>

 <!-- SHEET 1: Ticket Import Dump -->
 <Worksheet ss:Name="Ticket Import Dump">
  <Table>
   <Row ss:Height="24" ss:StyleID="Header">
    <Cell><Data ss:Type="String">Ticket Type</Data></Cell>
    <Cell><Data ss:Type="String">Received Datetime</Data></Cell>
    <Cell><Data ss:Type="String">From Address</Data></Cell>
    <Cell><Data ss:Type="String">Subject</Data></Cell>
    <Cell><Data ss:Type="String">Activity Name</Data></Cell>
    <Cell><Data ss:Type="String">Sub Activity Name</Data></Cell>
    <Cell><Data ss:Type="String">Division Code</Data></Cell>
    <Cell><Data ss:Type="String">Priority</Data></Cell>
    <Cell><Data ss:Type="String">Allocated Employee Code</Data></Cell>
    <Cell><Data ss:Type="String">Agency Code</Data></Cell>
    <Cell><Data ss:Type="String">Manager Name</Data></Cell>
    <Cell><Data ss:Type="String">Remarks</Data></Cell>
   </Row>
   <Row ss:Height="20">
    <Cell><Data ss:Type="String">Query Ticket</Data></Cell>
    <Cell><Data ss:Type="String"><?= date('Y-m-d H:i:s') ?></Data></Cell>
    <Cell><Data ss:Type="String">vendor.billing@client.com</Data></Cell>
    <Cell><Data ss:Type="String">Sample Query Regarding Payment Delay</Data></Cell>
    <Cell><Data ss:Type="String">Agency Billing</Data></Cell>
    <Cell><Data ss:Type="String">Billing Query</Data></Cell>
    <Cell><Data ss:Type="String">DIV01</Data></Cell>
    <Cell><Data ss:Type="String">Medium</Data></Cell>
    <Cell><Data ss:Type="String">EMP002</Data></Cell>
    <Cell><Data ss:Type="String">AGC101</Data></Cell>
    <Cell><Data ss:Type="String">John Doe</Data></Cell>
    <Cell><Data ss:Type="String">Sample imported query ticket</Data></Cell>
   </Row>
   <Row ss:Height="20">
    <Cell><Data ss:Type="String">Task Ticket</Data></Cell>
    <Cell><Data ss:Type="String"><?= date('Y-m-d H:i:s') ?></Data></Cell>
    <Cell><Data ss:Type="String">INTERNAL_TASK</Data></Cell>
    <Cell><Data ss:Type="String">Sample Internal Compliance Task</Data></Cell>
    <Cell><Data ss:Type="String">Inhouse</Data></Cell>
    <Cell><Data ss:Type="String">Internal Support</Data></Cell>
    <Cell><Data ss:Type="String">DIV02</Data></Cell>
    <Cell><Data ss:Type="String">High</Data></Cell>
    <Cell><Data ss:Type="String">EMP003</Data></Cell>
    <Cell><Data ss:Type="String"></Data></Cell>
    <Cell><Data ss:Type="String"></Data></Cell>
    <Cell><Data ss:Type="String">Sample imported task ticket</Data></Cell>
   </Row>
  </Table>
 </Worksheet>

 <!-- SHEET 2: Master Reference Data -->
 <Worksheet ss:Name="Master Reference Data">
  <Table>
   <Row ss:Height="24" ss:StyleID="MasterHeader">
    <Cell><Data ss:Type="String">Master Category</Data></Cell>
    <Cell><Data ss:Type="String">Name / Code to Use</Data></Cell>
    <Cell><Data ss:Type="String">Parent Activity / Department</Data></Cell>
    <Cell><Data ss:Type="String">SLA TAT Hours / Email</Data></Cell>
   </Row>

   <!-- Activities Section -->
   <Row ss:StyleID="SubHeader">
    <Cell ss:MergeAcross="3"><Data ss:Type="String">--- 1. ACTIVITIES MASTER ---</Data></Cell>
   </Row>
   <?php foreach ($activities as $act): ?>
   <Row>
    <Cell><Data ss:Type="String">Activity</Data></Cell>
    <Cell><Data ss:Type="String"><?= htmlspecialchars($act['activity_name']) ?></Data></Cell>
    <Cell><Data ss:Type="String"><?= htmlspecialchars($act['description'] ?? 'Active') ?></Data></Cell>
    <Cell><Data ss:Type="String">ID: <?= $act['id'] ?></Data></Cell>
   </Row>
   <?php endforeach; ?>

   <!-- Sub-Activities Section -->
   <Row ss:StyleID="SubHeader">
    <Cell ss:MergeAcross="3"><Data ss:Type="String">--- 2. SUB-ACTIVITIES MASTER (SLAs) ---</Data></Cell>
   </Row>
   <?php foreach ($subActivities as $sa): ?>
   <Row>
    <Cell><Data ss:Type="String">Sub-Activity</Data></Cell>
    <Cell><Data ss:Type="String"><?= htmlspecialchars($sa['sub_activity_name']) ?></Data></Cell>
    <Cell><Data ss:Type="String"><?= htmlspecialchars($sa['activity_name']) ?></Data></Cell>
    <Cell><Data ss:Type="String"><?= $sa['default_tat_hours'] ?> Hours SLA</Data></Cell>
   </Row>
   <?php endforeach; ?>

   <!-- Divisions Section -->
   <Row ss:StyleID="SubHeader">
    <Cell ss:MergeAcross="3"><Data ss:Type="String">--- 3. DIVISIONS MASTER ---</Data></Cell>
   </Row>
   <?php foreach ($divisions as $div): ?>
   <Row>
    <Cell><Data ss:Type="String">Division</Data></Cell>
    <Cell><Data ss:Type="String"><?= htmlspecialchars($div['code']) ?></Data></Cell>
    <Cell><Data ss:Type="String"><?= htmlspecialchars($div['division_name']) ?></Data></Cell>
    <Cell><Data ss:Type="String">Active</Data></Cell>
   </Row>
   <?php endforeach; ?>

   <!-- Employees Section -->
   <Row ss:StyleID="SubHeader">
    <Cell ss:MergeAcross="3"><Data ss:Type="String">--- 4. EMPLOYEES / ASSIGNEES MASTER ---</Data></Cell>
   </Row>
   <?php foreach ($employees as $emp): ?>
   <Row>
    <Cell><Data ss:Type="String">Employee</Data></Cell>
    <Cell><Data ss:Type="String"><?= htmlspecialchars($emp['user_code']) ?></Data></Cell>
    <Cell><Data ss:Type="String"><?= htmlspecialchars($emp['full_name']) ?></Data></Cell>
    <Cell><Data ss:Type="String"><?= htmlspecialchars($emp['email']) ?></Data></Cell>
   </Row>
   <?php endforeach; ?>
  </Table>
 </Worksheet>
</Workbook>
        <?php
        exit();
    }

    /**
     * Dedicated Bulk Import Page View
     */
    public function import_view() {
        $this->requireAuth();
        $this->requireRole([1, 2]);

        $this->render('tickets/import', [
            'title' => 'Bulk Ticket Import'
        ]);
    }

    /**
     * Bulk Ticket Excel / CSV Import Engine
     */
    public function import() {
        $this->requireAuth();
        $this->requireRole([1, 2]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('tickets');
            }

            if (empty($_FILES['csv_file']['tmp_name'])) {
                Session::setFlash('danger', 'Please select a CSV or Excel file to upload.');
                redirect('tickets/import_view');
            }

            $filePath = $_FILES['csv_file']['tmp_name'];
            $content = file_get_contents($filePath);
            $rows = [];

            if (str_contains($content, '<Workbook') || str_contains($content, '<?xml')) {
                // Parse XML Spreadsheet 2003 format
                preg_match_all('/<Row[^>]*>(.*?)<\/Row>/is', $content, $rowMatches);
                foreach ($rowMatches[1] as $rIndex => $rXml) {
                    if ($rIndex === 0) continue; // Skip header row
                    preg_match_all('/<Data[^>]*>(.*?)<\/Data>/is', $rXml, $cellMatches);
                    if (!empty($cellMatches[1])) {
                        $cleanCells = array_map(function($val) {
                            return trim(html_entity_decode(strip_tags($val)));
                        }, $cellMatches[1]);
                        $rows[] = $cleanCells;
                    }
                }
            } else {
                // Parse Standard CSV file
                $handle = fopen($filePath, 'r');
                if ($handle) {
                    $bom = fread($handle, 3);
                    if ($bom !== "\xEF\xBB\xBF") rewind($handle);
                    fgetcsv($handle); // Skip header row
                    while (($data = fgetcsv($handle)) !== false) {
                        $rows[] = $data;
                    }
                    fclose($handle);
                }
            }

            if (empty($rows)) {
                Session::setFlash('danger', 'No data rows found in uploaded file.');
                redirect('tickets/import_view');
            }

            $ticketModel = $this->model('Ticket_model');
            $activityModel = $this->model('Activity_model');
            $userModel = $this->model('User_model');

            // Pre-cache lookup tables
            $activities = $activityModel->getActivities();
            $subActivities = $activityModel->fetchAll("SELECT * FROM sub_activities");
            $divisions = $activityModel->getDivisions();
            $employees = $userModel->fetchAll("SELECT id, user_code, email, full_name FROM users WHERE status = 'Active'");

            $successCount = 0;
            $errorCount = 0;

            foreach ($rows as $row) {
                if (count($row) < 3 || empty(array_filter($row))) continue;

                $ticketType = sanitize($row[0] ?? 'Query Ticket');
                $receivedDatetime = !empty($row[1]) ? date('Y-m-d H:i:s', strtotime($row[1])) : date('Y-m-d H:i:s');
                $fromAddress = sanitize($row[2] ?? 'N/A');
                $subject = sanitize($row[3] ?? '');
                $activityName = sanitize($row[4] ?? '');
                $subActivityName = sanitize($row[5] ?? '');
                $divisionCode = sanitize($row[6] ?? '');
                $priority = sanitize($row[7] ?? 'Medium');
                $empCode = sanitize($row[8] ?? '');
                $agencyCode = sanitize($row[9] ?? '');
                $managerName = sanitize($row[10] ?? '');
                $remarks = sanitize($row[11] ?? '');

                if (empty($subject)) {
                    $errorCount++;
                    continue;
                }

                // Resolve Activity ID
                $activityId = 1;
                foreach ($activities as $act) {
                    if (strcasecmp($act['activity_name'], $activityName) === 0) {
                        $activityId = $act['id'];
                        break;
                    }
                }

                // Resolve Sub-Activity ID & Default TAT
                $subActivityId = 1;
                $defaultTatHours = 24;
                foreach ($subActivities as $sa) {
                    if ($sa['activity_id'] == $activityId && strcasecmp($sa['sub_activity_name'], $subActivityName) === 0) {
                        $subActivityId = $sa['id'];
                        $defaultTatHours = (int)$sa['default_tat_hours'];
                        break;
                    }
                }

                // Resolve Division ID
                $divisionId = null;
                if (!empty($divisionCode)) {
                    foreach ($divisions as $div) {
                        if (strcasecmp($div['code'], $divisionCode) === 0 || strcasecmp($div['division_name'], $divisionCode) === 0) {
                            $divisionId = $div['id'];
                            break;
                        }
                    }
                }

                // Resolve Allocated Employee ID
                $allocatedTo = null;
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

            Session::setFlash('success', "Bulk Import Complete: Successfully imported {$successCount} tickets" . ($errorCount > 0 ? " ({$errorCount} rows skipped/failed)." : "."));
            redirect('tickets');
        }
    }
}
