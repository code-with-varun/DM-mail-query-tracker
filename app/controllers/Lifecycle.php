<?php
/**
 * Process Lifecycle Tracker Controller
 */

class Lifecycle extends Controller {

    public function __construct() {
        $this->requireAuth();
    }

    public function index() {
        $lifecycleModel = $this->model('Lifecycle_model');
        $activityModel = $this->model('Activity_model');
        $userModel = $this->model('User_model');

        $billingMonth = !empty($_GET['month']) ? sanitize($_GET['month']) : date('Y-m');

        $filters = [
            'ticket_type' => !empty($_GET['type']) ? sanitize($_GET['type']) : null,
            'stage' => !empty($_GET['stage']) ? sanitize($_GET['stage']) : null,
            'search' => !empty($_GET['search']) ? sanitize($_GET['search']) : null,
        ];

        $items = $lifecycleModel->getLifecycleItems($billingMonth, $filters);

        $this->render('lifecycle/index', [
            'title' => 'Process Lifecycle & Monthwise Tracker',
            'billingMonth' => $billingMonth,
            'items' => $items,
            'filters' => $filters,
            'activities' => $activityModel->getActivities(),
            'users' => $userModel->getEmployees()
        ]);
    }

    public function populate_subtasks() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('lifecycle');
            }

            $month = !empty($_POST['billing_month']) ? sanitize($_POST['billing_month']) : date('Y-m');
            $lifecycleModel = $this->model('Lifecycle_model');

            $count = $lifecycleModel->populateFromActiveSubtasks($month, Session::get('user_id'));

            if ($count > 0) {
                Session::setFlash('success', "Successfully populated {$count} active sub-activities for {$month} into the Lifecycle Tracker!");
            } else {
                Session::setFlash('info', "All active sub-activities for {$month} are already present in the Lifecycle Tracker.");
            }

            redirect('lifecycle?month=' . urlencode($month));
        }
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('lifecycle');
            }

            $subActivityId = (int)($_POST['sub_activity_id'] ?? 0);
            $month = !empty($_POST['billing_month']) ? sanitize($_POST['billing_month']) : date('Y-m');

            if (!$subActivityId) {
                Session::setFlash('danger', 'Please select a Sub-Activity.');
                redirect('lifecycle');
            }

            $activityModel = $this->model('Activity_model');
            $subInfo = $activityModel->getSubActivityById($subActivityId);
            if (!$subInfo) {
                Session::setFlash('danger', 'Invalid Sub-Activity selected.');
                redirect('lifecycle');
            }

            $lifecycleModel = $this->model('Lifecycle_model');
            $lifecycleModel->createLifecycleItem([
                'billing_month' => $month,
                'ticket_type' => sanitize($_POST['ticket_type'] ?? 'File Preparation'),
                'division_id' => $subInfo['division_id'] ?? null,
                'activity_id' => $subInfo['activity_id'],
                'sub_activity_id' => $subActivityId,
                'division_name' => $subInfo['division_name'] ?? 'General Operations',
                'activity_name' => $subInfo['activity_name'],
                'sub_activity_name' => $subInfo['sub_activity_name'],
                'maker_id' => !empty($_POST['maker_id']) ? (int)$_POST['maker_id'] : null,
                'checker_id' => !empty($_POST['checker_id']) ? (int)$_POST['checker_id'] : null,
                'remarks' => sanitize($_POST['remarks'] ?? ''),
                'created_by' => Session::get('user_id')
            ]);

            Session::setFlash('success', 'Lifecycle item created successfully!');
            redirect('lifecycle?month=' . urlencode($month));
        }
    }

    public function update_stage() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('lifecycle');
            }

            $id = (int)($_POST['lifecycle_id'] ?? 0);
            $newStage = sanitize($_POST['new_stage'] ?? '');

            if (!$id || empty($newStage)) {
                Session::setFlash('danger', 'Invalid request parameters.');
                redirect('lifecycle');
            }

            $extraData = [
                'remarks' => sanitize($_POST['remarks'] ?? ''),
                'error_description' => sanitize($_POST['error_description'] ?? '')
            ];

            // Handle optional file attachment for this stage
            if (!empty($_FILES['stage_attachment']['name']) && $_FILES['stage_attachment']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['stage_attachment'];
                $uploadDir = BASE_PATH . '/public/uploads/lifecycle/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $newFileName = 'lfc_' . $id . '_' . time() . '.' . $ext;
                $targetFile = $uploadDir . $newFileName;

                if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                    $extraData['attachment'] = 'public/uploads/lifecycle/' . $newFileName;
                    $extraData['attachment_name'] = $file['name'];
                }
            }

            $lifecycleModel = $this->model('Lifecycle_model');
            $lifecycleModel->updateStage($id, $newStage, $extraData, Session::get('user_id'));

            Session::setFlash('success', "Ticket updated to stage: {$newStage}");
            
            $month = !empty($_POST['current_month']) ? $_POST['current_month'] : date('Y-m');
            redirect('lifecycle?month=' . urlencode($month));
        }
    }

    public function export_excel() {
        $month = !empty($_GET['month']) ? sanitize($_GET['month']) : date('Y-m');
        $lifecycleModel = $this->model('Lifecycle_model');
        $items = $lifecycleModel->getLifecycleItems($month);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Lifecycle_Tracker_' . $month . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, [
            'Ticket Number',
            'Billing Month',
            'Ticket Type',
            'Division',
            'Activity',
            'Sub Activity',
            'Maker',
            'Checker',
            'Current Stage',
            'Input Received At',
            'Maker Completed At',
            'Checker Completed At',
            'Error Log At',
            'Rework Loops Count',
            'Delivery At',
            'Acknowledgement At',
            'Approved At',
            'Uploaded At',
            'Dump Sharing At',
            'Consol Vendor At',
            'Closed At',
            'Remarks'
        ]);

        foreach ($items as $item) {
            fputcsv($output, [
                $item['ticket_number'],
                $item['billing_month'],
                $item['ticket_type'],
                $item['division_name'],
                $item['activity_name'],
                $item['sub_activity_name'],
                $item['maker_name'] ?? 'Unassigned',
                $item['checker_name'] ?? 'Unassigned',
                $item['current_stage'],
                $item['input_received_at'],
                $item['maker_completed_at'],
                $item['checker_completed_at'],
                $item['error_log_at'],
                $item['rework_loop_count'],
                $item['delivery_at'],
                $item['acknowledgement_at'],
                $item['approved_at'],
                $item['uploaded_at'],
                $item['dump_sharing_at'],
                $item['consol_vendor_at'],
                $item['closed_at'],
                $item['remarks']
            ]);
        }
        fclose($output);
        exit;
    }
}
