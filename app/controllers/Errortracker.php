<?php
/**
 * Error Tracker Controller
 * Handles CRUD and Bulk Import/Export for Error Tracker
 */

class Errortracker extends Controller {
    public function index() {
        $this->requireAuth();
        
        $errorModel = $this->model('Error_tracker_model');
        $userModel = $this->model('User_model');

        $filters = [
            'search' => sanitize($_GET['search'] ?? ''),
            'error_type' => sanitize($_GET['error_type'] ?? ''),
            'billing_month' => sanitize($_GET['billing_month'] ?? '')
        ];

        $errors = $errorModel->getErrors($filters);
        $stats = $errorModel->getStats();
        $users = $userModel->getEmployees();

        $this->render('error_tracker/index', [
            'title' => 'Error Tracker',
            'errors' => $errors,
            'stats' => $stats,
            'users' => $users,
            'filters' => $filters
        ]);
    }

    public function store() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('error-tracker');
            }

            $billingInput = sanitize($_POST['billing_month'] ?? '');
            $checkingInput = sanitize($_POST['checking_month'] ?? '');
            $errorObservation = sanitize($_POST['error_observation'] ?? '');
            $errorDescription = sanitize($_POST['error_description'] ?? '');
            $resolutionSolution = sanitize($_POST['resolution_solution'] ?? '');
            $errorType = sanitize($_POST['error_type'] ?? 'Internal');
            $makerId = !empty($_POST['maker_id']) ? (int)$_POST['maker_id'] : null;
            $checkerId = !empty($_POST['checker_id']) ? (int)$_POST['checker_id'] : null;

            if (empty($errorObservation)) {
                Session::setFlash('danger', 'Error Observation is required.');
                redirect('error-tracker');
            }

            $billingMonth = $this->normalizeMonthDate($billingInput);
            $checkingMonth = $this->normalizeMonthDate($checkingInput);

            if (!in_array($errorType, ['Internal', 'External'])) {
                $errorType = 'Internal';
            }

            $errorModel = $this->model('Error_tracker_model');
            $errorModel->createError([
                'billing_month' => $billingMonth,
                'checking_month' => $checkingMonth,
                'error_observation' => $errorObservation,
                'error_description' => $errorDescription,
                'resolution_solution' => $resolutionSolution,
                'error_type' => $errorType,
                'maker_id' => $makerId,
                'checker_id' => $checkerId,
                'created_by' => Session::get('user_id')
            ]);

            Session::setFlash('success', 'New Error Tracker record created successfully.');
        }

        redirect('error-tracker');
    }

    public function update() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('error-tracker');
            }

            $id = (int)($_POST['id'] ?? 0);
            $billingInput = sanitize($_POST['billing_month'] ?? '');
            $checkingInput = sanitize($_POST['checking_month'] ?? '');
            $errorObservation = sanitize($_POST['error_observation'] ?? '');
            $errorDescription = sanitize($_POST['error_description'] ?? '');
            $resolutionSolution = sanitize($_POST['resolution_solution'] ?? '');
            $errorType = sanitize($_POST['error_type'] ?? 'Internal');
            $makerId = !empty($_POST['maker_id']) ? (int)$_POST['maker_id'] : null;
            $checkerId = !empty($_POST['checker_id']) ? (int)$_POST['checker_id'] : null;

            if (!$id || empty($errorObservation)) {
                Session::setFlash('danger', 'Record ID and Error Observation are required.');
                redirect('error-tracker');
            }

            $billingMonth = $this->normalizeMonthDate($billingInput);
            $checkingMonth = $this->normalizeMonthDate($checkingInput);

            if (!in_array($errorType, ['Internal', 'External'])) {
                $errorType = 'Internal';
            }

            $errorModel = $this->model('Error_tracker_model');
            $updated = $errorModel->updateError($id, [
                'billing_month' => $billingMonth,
                'checking_month' => $checkingMonth,
                'error_observation' => $errorObservation,
                'error_description' => $errorDescription,
                'resolution_solution' => $resolutionSolution,
                'error_type' => $errorType,
                'maker_id' => $makerId,
                'checker_id' => $checkerId
            ]);

            if ($updated) {
                Session::setFlash('success', 'Error Tracker record updated successfully.');
            } else {
                Session::setFlash('danger', 'Record not found or update failed.');
            }
        }

        redirect('error-tracker');
    }

    public function delete() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('error-tracker');
            }

            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $errorModel = $this->model('Error_tracker_model');
                $deleted = $errorModel->deleteError($id);

                if ($deleted) {
                    Session::setFlash('success', 'Error Tracker record deleted successfully.');
                } else {
                    Session::setFlash('danger', 'Record not found or delete failed.');
                }
            }
        }

        redirect('error-tracker');
    }

    public function export_csv() {
        $this->requireAuth();

        $errorModel = $this->model('Error_tracker_model');
        $errors = $errorModel->getErrors();

        $filename = "MQT_Error_Tracker_Export_" . date('Y-m-d_His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        fputcsv($output, [
            'ID',
            'Billing Month',
            'Checking Month',
            'Error Observation',
            'Error Description',
            'Resolution / Solution',
            'Error Type',
            'Maker Name',
            'Maker Code',
            'Checker Name',
            'Checker Code',
            'Created By',
            'Created At'
        ]);

        foreach ($errors as $row) {
            fputcsv($output, [
                $row['id'],
                date('M Y (01-m-Y)', strtotime($row['billing_month'])),
                date('M Y (01-m-Y)', strtotime($row['checking_month'])),
                $row['error_observation'],
                $row['error_description'],
                $row['resolution_solution'],
                $row['error_type'],
                $row['maker_name'] ?? 'N/A',
                $row['maker_code'] ?? 'N/A',
                $row['checker_name'] ?? 'N/A',
                $row['checker_code'] ?? 'N/A',
                $row['creator_name'] ?? 'System',
                $row['created_at']
            ]);
        }

        fclose($output);
        exit;
    }

    public function download_template() {
        $this->requireAuth();

        $filename = "Error_Tracker_Import_Template.csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        fputcsv($output, [
            'Billing Month (YYYY-MM-01)',
            'Checking Month (YYYY-MM-01)',
            'Error Observation',
            'Error Description',
            'Resolution / Solution',
            'Error Type (Internal/External)',
            'Maker Code or Name',
            'Checker Code or Name'
        ]);

        // Sample Rows
        fputcsv($output, [
            date('Y-m-01'),
            date('Y-m-01'),
            'Billing Data Discrepancy',
            'JULY-26 invoice amount mismatched by 2500',
            'Re-verified vendor rate sheet and corrected invoice entry',
            'Internal',
            'EMP002',
            'EMP001'
        ]);

        fputcsv($output, [
            date('Y-m-01'),
            date('Y-m-01'),
            'Delayed Vendor NDC Receipt',
            'NDC tracker submission delayed from external agency',
            'Escalated to vendor account manager and received confirmation',
            'External',
            'EMP002',
            'EMP001'
        ]);

        fclose($output);
        exit;
    }

    public function import() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('error-tracker');
            }

            if (empty($_FILES['import_file']['tmp_name'])) {
                Session::setFlash('danger', 'Please select a CSV file to import.');
                redirect('error-tracker');
            }

            $filePath = $_FILES['import_file']['tmp_name'];
            $handle = fopen($filePath, 'r');

            if (!$handle) {
                Session::setFlash('danger', 'Unable to read the uploaded CSV file.');
                redirect('error-tracker');
            }

            // Read header
            $header = fgetcsv($handle);
            $userModel = $this->model('User_model');
            $errorModel = $this->model('Error_tracker_model');
            $allUsers = $userModel->getEmployees();

            $importedCount = 0;
            $skippedCount = 0;

            while (($data = fgetcsv($handle)) !== false) {
                if (empty($data[0]) && empty($data[2])) {
                    continue; // skip empty rows
                }

                $billingRaw = trim($data[0] ?? '');
                $checkingRaw = trim($data[1] ?? '');
                $observation = trim($data[2] ?? '');
                $description = trim($data[3] ?? '');
                $resolution = trim($data[4] ?? '');
                $errorType = trim($data[5] ?? 'Internal');
                $makerVal = trim($data[6] ?? '');
                $checkerVal = trim($data[7] ?? '');

                if (empty($observation)) {
                    $skippedCount++;
                    continue;
                }

                $billingMonth = $this->normalizeMonthDate($billingRaw);
                $checkingMonth = $this->normalizeMonthDate($checkingRaw);

                if (!in_array($errorType, ['Internal', 'External'])) {
                    $errorType = 'Internal';
                }

                // Resolve Maker ID & Checker ID
                $makerId = $this->findUserId($allUsers, $makerVal);
                $checkerId = $this->findUserId($allUsers, $checkerVal);

                $errorModel->createError([
                    'billing_month' => $billingMonth,
                    'checking_month' => $checkingMonth,
                    'error_observation' => $observation,
                    'error_description' => $description,
                    'resolution_solution' => $resolution,
                    'error_type' => $errorType,
                    'maker_id' => $makerId,
                    'checker_id' => $checkerId,
                    'created_by' => Session::get('user_id')
                ]);

                $importedCount++;
            }

            fclose($handle);

            Session::setFlash('success', "Bulk Import Complete! Successfully imported {$importedCount} error records." . ($skippedCount > 0 ? " ({$skippedCount} invalid rows skipped)" : ""));
        }

        redirect('error-tracker');
    }

    /**
     * Helper to normalize month input to YYYY-MM-01
     */
    private function normalizeMonthDate(string $input): string {
        $input = trim($input);
        if (empty($input)) {
            return date('Y-m-01');
        }

        // If YYYY-MM or YYYY-MM-DD
        if (preg_match('/^(\d{4})-(\d{1,2})(?:-(\d{1,2}))?$/', $input, $matches)) {
            $yyyy = $matches[1];
            $mm = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            return "{$yyyy}-{$mm}-01";
        }

        // Try strtotime
        $time = strtotime($input);
        if ($time !== false) {
            return date('Y-m-01', $time);
        }

        return date('Y-m-01');
    }

    /**
     * Helper to lookup user ID by code or name
     */
    private function findUserId(array $users, string $query): ?int {
        $query = strtolower(trim($query));
        if (empty($query)) return null;

        foreach ($users as $u) {
            if (strtolower($u['user_code']) === $query || strtolower($u['full_name']) === $query || strtolower($u['email']) === $query) {
                return (int)$u['id'];
            }
        }
        return null;
    }
}
