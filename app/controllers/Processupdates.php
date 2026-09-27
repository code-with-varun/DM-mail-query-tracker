<?php
/**
 * Process Updates Tracker Controller
 * Preserves Outlook process update mails, policy changes & billing guidelines
 */

class Processupdates extends Controller {

    public function index() {
        $this->requireAuth();

        $puModel = $this->model('Process_update_model');
        $activityModel = $this->model('Activity_model');

        $filters = [
            'search' => sanitize($_GET['search'] ?? ''),
            'division_id' => !empty($_GET['division_id']) ? (int)$_GET['division_id'] : null,
            'activity_id' => !empty($_GET['activity_id']) ? (int)$_GET['activity_id'] : null,
            'sub_activity_id' => !empty($_GET['sub_activity_id']) ? (int)$_GET['sub_activity_id'] : null,
            'billing_month' => sanitize($_GET['billing_month'] ?? '')
        ];

        $updates = $puModel->getAllUpdates($filters);

        $this->render('tracker/process_updates', [
            'title' => 'Process Updates Tracker',
            'updates' => $updates,
            'filters' => $filters,
            'divisions' => $activityModel->getDivisions(),
            'activities' => $activityModel->getActivities(),
            'subActivities' => $activityModel->getAllSubActivitiesWithHierarchy()
        ]);
    }

    public function store() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('tracker/process-updates');
            }

            $puModel = $this->model('Process_update_model');

            $title = sanitize($_POST['title'] ?? '');
            $divisionId = !empty($_POST['division_id']) ? (int)$_POST['division_id'] : null;
            $activityId = !empty($_POST['activity_id']) ? (int)$_POST['activity_id'] : null;
            $subActivityId = !empty($_POST['sub_activity_id']) ? (int)$_POST['sub_activity_id'] : null;
            $billingMonth = sanitize($_POST['billing_month'] ?? date('Y-m'));
            $notes = sanitize($_POST['notes'] ?? '');

            if (empty($title)) {
                Session::setFlash('danger', 'Process update title is required.');
                redirect('tracker/process-updates');
            }

            // Handle Mail File Upload (Outlook file or standard upload)
            $fileKey = isset($_FILES['email_file']) ? 'email_file' : (isset($_FILES['emailfile']) ? 'emailfile' : null);
            $fileName = null;
            $filePath = null;
            $fileExt = null;
            $fileSize = null;

            if ($fileKey && !empty($_FILES[$fileKey]['name']) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/uploads/process_updates/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $originalName = basename($_FILES[$fileKey]['name']);
                $fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                $safeName = date('Ymd_His') . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
                $targetPath = $uploadDir . $safeName;

                if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $targetPath)) {
                    $fileName = $originalName;
                    $filePath = 'public/uploads/process_updates/' . $safeName;
                    $fileSize = (int)$_FILES[$fileKey]['size'];
                }
            }

            $puData = [
                'title' => $title,
                'division_id' => $divisionId,
                'activity_id' => $activityId,
                'sub_activity_id' => $subActivityId,
                'billing_month' => $billingMonth,
                'notes' => $notes,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_ext' => $fileExt,
                'file_size' => $fileSize,
                'created_by' => Session::get('user_id')
            ];

            if ($puModel->createUpdate($puData)) {
                Session::setFlash('success', 'Process update email preserved successfully!');
            } else {
                Session::setFlash('danger', 'Failed to save process update entry.');
            }

            redirect('tracker/process-updates');
        }
    }

    public function preview(int $id) {
        $this->requireAuth();
        header('Content-Type: application/json');

        $puModel = $this->model('Process_update_model');
        $record = $puModel->getById($id);

        if (!$record) {
            echo json_encode(['success' => false, 'message' => 'Record not found']);
            exit;
        }

        $content = '';
        if (!empty($record['file_path'])) {
            $fullPath = __DIR__ . '/../../' . $record['file_path'];
            if (file_exists($fullPath)) {
                $ext = strtolower($record['file_ext'] ?? '');
                if (in_array($ext, ['txt', 'html', 'htm', 'eml', 'log'])) {
                    $raw = file_get_contents($fullPath);
                    $content = htmlspecialchars(substr($raw, 0, 10000));
                }
            }
        }

        echo json_encode([
            'success' => true,
            'data' => $record,
            'file_content' => $content
        ]);
        exit;
    }

    public function download(int $id) {
        $this->requireAuth();

        $puModel = $this->model('Process_update_model');
        $record = $puModel->getById($id);

        if (!$record || empty($record['file_path'])) {
            Session::setFlash('danger', 'File not found for download.');
            redirect('tracker/process-updates');
        }

        $fullPath = __DIR__ . '/../../' . $record['file_path'];
        if (!file_exists($fullPath)) {
            Session::setFlash('danger', 'Attachment file does not exist on disk.');
            redirect('tracker/process-updates');
        }

        $downloadName = $record['file_name'] ?? basename($fullPath);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    public function delete(int $id) {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('tracker/process-updates');
            }

            $puModel = $this->model('Process_update_model');
            if ($puModel->deleteUpdate($id)) {
                Session::setFlash('success', 'Process update record deleted successfully.');
            } else {
                Session::setFlash('danger', 'Failed to delete process update record.');
            }
            redirect('tracker/process-updates');
        }
    }
}
