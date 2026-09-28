<?php
/**
 * Training, PKT LMS, Practice Files & Certification Controller
 */

class Training extends Controller {

    public function __construct() {
        $this->requireAuth();
    }

    /**
     * Training Plan & Knowledge Transfer (KT) Page
     */
    public function plan() {
        $user = current_user();
        $trainingModel = $this->model('Training_model');
        $activityModel = $this->model('Activity_model');

        $isAdmin = (is_super_admin() || is_admin());
        $activityCards = $trainingModel->getGroupedTrainingPlan($user['id'], $isAdmin);
        $subActivities = $activityModel->getAllSubActivitiesWithHierarchy();

        $this->render('training/plan', [
            'title' => 'Training Plan & KT Materials',
            'activityCards' => $activityCards,
            'subActivities' => $subActivities
        ]);
    }

    /**
     * Add KT Module (Admin / Super Admin / Senior Executive)
     */
    public function add_kt_module() {
        $this->requireRole([1, 2]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('training/plan');
            }

            $subActivityId = (int)($_POST['sub_activity_id'] ?? 0);
            $title = sanitize($_POST['title'] ?? '');
            $description = sanitize($_POST['description'] ?? '');

            if ($subActivityId <= 0 || empty($title)) {
                Session::setFlash('danger', 'Please select Sub Activity and enter Module Title.');
                redirect('training/plan');
            }

            $filePath = null;
            $fileName = null;

            if (isset($_FILES['kt_document']) && $_FILES['kt_document']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = FCPATH . 'uploads/kt_documents/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileTmp = $_FILES['kt_document']['tmp_name'];
                $origName = basename($_FILES['kt_document']['name']);
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                $newFileName = 'KT_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $targetFile = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmp, $targetFile)) {
                    $filePath = 'uploads/kt_documents/' . $newFileName;
                    $fileName = $origName;
                }
            }

            $trainingModel = $this->model('Training_model');
            $trainingModel->insert('training_plans', [
                'sub_activity_id' => $subActivityId,
                'title' => $title,
                'description' => $description,
                'kt_document_path' => $filePath,
                'kt_document_name' => $fileName,
                'created_by' => current_user()['id'],
                'created_at' => date('Y-m-d H:i:s')
            ]);

            Session::setFlash('success', 'KT Training Material added successfully!');
            redirect('training/plan');
        }
    }

    /**
     * Toggle Knowledge Learned Checklist per Sub-Activity
     */
    public function toggle_kt() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $subActivityId = (int)($_POST['sub_activity_id'] ?? 0);
            $isLearned = !empty($_POST['is_learned']);
            $user = current_user();

            $trainingModel = $this->model('Training_model');
            $trainingModel->toggleSubActivityProgress($user['id'], $subActivityId, $isLearned);

            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit();
            }

            Session::setFlash('success', 'Training progress updated.');
            redirect('training/plan');
        }
    }

    /**
     * Download KT File
     */
    public function download_kt(int $id) {
        $trainingModel = $this->model('Training_model');
        $plan = $trainingModel->fetchOne("SELECT * FROM training_plans WHERE id = ?", [$id]);

        if (!$plan || empty($plan['kt_document_path'])) {
            Session::setFlash('danger', 'Document file not found.');
            redirect('training/plan');
        }

        $fullPath = FCPATH . $plan['kt_document_path'];
        if (file_exists($fullPath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . ($plan['kt_document_name'] ?? basename($fullPath)) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($fullPath));
            readfile($fullPath);
            exit();
        }

        Session::setFlash('danger', 'File does not exist on server.');
        redirect('training/plan');
    }

    // ----------------------------------------------------
    // PKT TEST CENTER
    // ----------------------------------------------------
    public function pkt() {
        $user = current_user();
        $trainingModel = $this->model('Training_model');
        $tests = $trainingModel->getPublishedTestsForUser($user['id']);

        $this->render('training/pkt', [
            'title' => 'PKT (Process Knowledge Test) Center',
            'tests' => $tests
        ]);
    }

    /**
     * Take PKT Test Interface
     */
    public function take_test(int $testId) {
        $user = current_user();
        $trainingModel = $this->model('Training_model');

        $test = $trainingModel->getTestById($testId);
        if (!$test || $test['status'] !== 'Published') {
            Session::setFlash('danger', 'Test is not available or closed.');
            redirect('training/pkt');
        }

        $questions = $trainingModel->getRandomQuestionsForSubActivity($test['sub_activity_id'], $test['total_questions']);
        if (empty($questions)) {
            Session::setFlash('warning', 'No questions found in question bank for this activity test yet.');
            redirect('training/pkt');
        }

        $this->render('training/take_test', [
            'title' => 'Attending Test: ' . $test['test_title'],
            'test' => $test,
            'questions' => $questions
        ]);
    }

    /**
     * Submit PKT Test Answers & Store Scores
     */
    public function submit_test() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('training/pkt');
            }

            $user = current_user();
            $testId = (int)($_POST['test_id'] ?? 0);
            $answers = $_POST['answers'] ?? [];

            $trainingModel = $this->model('Training_model');
            $result = $trainingModel->submitTestAttempt($user['id'], $testId, $answers);

            if (!$result['success']) {
                Session::setFlash('danger', $result['message']);
                redirect('training/pkt');
            }

            if ($result['passed']) {
                Session::setFlash('success', "Congratulations! You PASSED the PKT Test with a score of {$result['score']}%. Practice File phase is now unlocked!");
            } else {
                Session::setFlash('warning', "You scored {$result['score']}%. Required passing score is {$result['passing_score']}%. Please review KT materials and try again.");
            }

            redirect('training/pkt');
        }
    }

    // ----------------------------------------------------
    // PKT QUESTION BANK & TEST MANAGER (ADMIN / SUPER ADMIN)
    // ----------------------------------------------------
    public function manage_pkt() {
        $this->requireRole([1, 2]);

        $trainingModel = $this->model('Training_model');
        $activityModel = $this->model('Activity_model');

        $questions = $trainingModel->getQuestionBank();
        $tests = $trainingModel->getAllTests();
        $subActivities = $activityModel->getAllSubActivitiesWithHierarchy();

        $this->render('training/manage_pkt', [
            'title' => 'PKT Question Bank & Test Manager',
            'questions' => $questions,
            'tests' => $tests,
            'subActivities' => $subActivities
        ]);
    }

    /**
     * Add Single Question
     */
    public function add_question() {
        $this->requireRole([1, 2]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('training/manage_pkt');
            }

            $trainingModel = $this->model('Training_model');
            $trainingModel->addQuestion([
                'sub_activity_id' => (int)$_POST['sub_activity_id'],
                'question' => sanitize($_POST['question']),
                'option_a' => sanitize($_POST['option_a']),
                'option_b' => sanitize($_POST['option_b']),
                'option_c' => sanitize($_POST['option_c']),
                'option_d' => sanitize($_POST['option_d']),
                'correct_option' => strtoupper(trim($_POST['correct_option'])),
                'created_by' => current_user()['id'],
                'created_at' => date('Y-m-d H:i:s')
            ]);

            Session::setFlash('success', 'Question added to Question Bank.');
            redirect('training/manage_pkt');
        }
    }

    /**
     * Import Questions via Excel / CSV
     */
    public function import_questions() {
        $this->requireRole([1, 2]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('training/manage_pkt');
            }

            $subActivityId = (int)($_POST['sub_activity_id'] ?? 0);
            if ($subActivityId <= 0) {
                Session::setFlash('danger', 'Please select a target Sub-Activity for imported questions.');
                redirect('training/manage_pkt');
            }

            $fileTmp = $_FILES['excel_file']['tmp_name'];
            $fileExt = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));

            $imported = 0;
            $trainingModel = $this->model('Training_model');
            $questions = [];

            if (in_array($fileExt, ['csv', 'txt'])) {
                if (($handle = fopen($fileTmp, 'r')) !== FALSE) {
                    $header = fgetcsv($handle, 1000, ",");
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        if (count($data) >= 6) {
                            $questions[] = [
                                'sub_activity_id' => $subActivityId,
                                'question' => $data[0] ?? '',
                                'option_a' => $data[1] ?? '',
                                'option_b' => $data[2] ?? '',
                                'option_c' => $data[3] ?? '',
                                'option_d' => $data[4] ?? '',
                                'correct_option' => $data[5] ?? 'A',
                                'created_by' => current_user()['id']
                            ];
                        }
                    }
                    fclose($handle);
                }
            } else {
                // Read via Python / Simple parser fallback if XLSX
                $pythonScript = FCPATH . 'cron/excel_to_json.py';
                $jsonOutput = tempnam(sys_get_temp_dir(), 'pkt_imp_') . '.json';
                $cmd = "python " . escapeshellarg($pythonScript) . " " . escapeshellarg($fileTmp) . " " . escapeshellarg($jsonOutput);
                exec($cmd);

                if (file_exists($jsonOutput)) {
                    $content = json_decode(file_get_contents($jsonOutput), true);
                    @unlink($jsonOutput);
                    if ($content) {
                        $rows = reset($content); // first sheet
                        foreach ($rows as $row) {
                            $vals = array_values($row);
                            if (count($vals) >= 6) {
                                $questions[] = [
                                    'sub_activity_id' => $subActivityId,
                                    'question' => $vals[0] ?? '',
                                    'option_a' => $vals[1] ?? '',
                                    'option_b' => $vals[2] ?? '',
                                    'option_c' => $vals[3] ?? '',
                                    'option_d' => $vals[4] ?? '',
                                    'correct_option' => $vals[5] ?? 'A',
                                    'created_by' => current_user()['id']
                                ];
                            }
                        }
                    }
                }
            }

            if (!empty($questions)) {
                $imported = $trainingModel->bulkImportQuestions($questions);
                Session::setFlash('success', "Successfully imported {$imported} PKT questions from file!");
            } else {
                Session::setFlash('danger', 'Could not parse questions. Please ensure columns: Question, Option A, Option B, Option C, Option D, Correct Answer.');
            }

            redirect('training/manage_pkt');
        }
    }

    /**
     * Download Question Import Excel Template
     */
    public function download_pkt_template() {
        $this->requireRole([1, 2]);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=PKT_Question_Bank_Import_Template.csv');

        $output = fopen('php://output', 'w');
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

        fputcsv($output, ['Question', 'Option A', 'Option B', 'Option C', 'Option D', 'Correct Answer (A/B/C/D)']);
        fputcsv($output, [
            'What is the standard SLA turnaround time for billing queries?',
            '12 Hours',
            '24 Hours',
            '48 Hours',
            '72 Hours',
            'B'
        ]);
        fputcsv($output, [
            'Which document is mandatory for vendor empanelment verification?',
            'GST Certificate & PAN',
            'Utility Bill Only',
            'Employee ID Card',
            'None of the above',
            'A'
        ]);

        fclose($output);
        exit();
    }

    /**
     * Create & Publish Test
     */
    public function create_test() {
        $this->requireRole([1, 2]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('training/manage_pkt');
            }

            $trainingModel = $this->model('Training_model');
            $trainingModel->createTest([
                'test_title' => sanitize($_POST['test_title']),
                'sub_activity_id' => (int)$_POST['sub_activity_id'],
                'total_questions' => (int)($_POST['total_questions'] ?? 5),
                'passing_score' => (int)($_POST['passing_score'] ?? 70),
                'time_limit_minutes' => (int)($_POST['time_limit_minutes'] ?? 20),
                'status' => 'Published',
                'created_by' => current_user()['id'],
                'created_at' => date('Y-m-d H:i:s')
            ]);

            Session::setFlash('success', 'PKT Test published successfully!');
            redirect('training/manage_pkt');
        }
    }

    // ----------------------------------------------------
    // PRACTICE FILES & CHECKER EVALUATION
    // ----------------------------------------------------
    public function practice() {
        $user = current_user();
        $trainingModel = $this->model('Training_model');
        $activityModel = $this->model('Activity_model');
        $userModel = $this->model('User_model');

        $myPracticeFiles = $trainingModel->getPracticeFilesForUser($user['id']);
        $checkerQueue = [];
        if (is_admin() || is_super_admin()) {
            $checkerQueue = $trainingModel->getPracticeFilesForChecker($user['id']);
        }

        $subActivities = $activityModel->getAllSubActivitiesWithHierarchy();
        $checkers = $userModel->getAdmins();

        $this->render('training/practice', [
            'title' => 'Practice Files & Checker Validation',
            'myPracticeFiles' => $myPracticeFiles,
            'checkerQueue' => $checkerQueue,
            'subActivities' => $subActivities,
            'checkers' => $checkers
        ]);
    }

    /**
     * Submit Practice File (Maker / Trainee)
     */
    public function submit_practice() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('training/practice');
            }

            $user = current_user();
            $subActivityId = (int)($_POST['sub_activity_id'] ?? 0);
            $taskTitle = sanitize($_POST['task_title'] ?? '');
            $remarks = sanitize($_POST['remarks'] ?? '');
            $checkerId = !empty($_POST['checker_id']) ? (int)$_POST['checker_id'] : null;

            if ($subActivityId <= 0 || empty($taskTitle)) {
                Session::setFlash('danger', 'Please select Sub-Activity and enter Practice Task Title.');
                redirect('training/practice');
            }

            $activityModel = $this->model('Activity_model');
            $saInfo = $activityModel->fetchOne("SELECT activity_id, division_id FROM sub_activities WHERE id = ?", [$subActivityId]);

            $filePath = null;
            $fileName = null;

            if (isset($_FILES['practice_file']) && $_FILES['practice_file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = FCPATH . 'uploads/practice_files/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileTmp = $_FILES['practice_file']['tmp_name'];
                $origName = basename($_FILES['practice_file']['name']);
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                $newFileName = 'PRACTICE_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $targetFile = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmp, $targetFile)) {
                    $filePath = 'uploads/practice_files/' . $newFileName;
                    $fileName = $origName;
                }
            }

            $trainingModel = $this->model('Training_model');
            $trainingModel->createPracticeFile([
                'user_id' => $user['id'],
                'division_id' => $saInfo['division_id'] ?? null,
                'activity_id' => $saInfo['activity_id'] ?? 1,
                'sub_activity_id' => $subActivityId,
                'task_title' => $taskTitle,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'remarks' => $remarks,
                'checker_id' => $checkerId
            ]);

            Session::setFlash('success', 'Practice file submitted to Checker queue successfully!');
            redirect('training/practice');
        }
    }

    /**
     * Validate Practice File (Checker Action with Error Logging)
     */
    public function validate_practice() {
        $this->requireRole([1, 2]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('training/practice');
            }

            $user = current_user();
            $practiceId = (int)($_POST['practice_id'] ?? 0);
            $action = $_POST['action'] ?? 'Approve';
            $errorObs = sanitize($_POST['error_observation'] ?? '');
            $errorDesc = sanitize($_POST['error_description'] ?? '');
            $errorType = sanitize($_POST['error_type'] ?? 'Internal');

            $trainingModel = $this->model('Training_model');
            $trainingModel->validatePracticeFile($practiceId, $user['id'], $action, $errorObs, $errorDesc, $errorType);

            if ($action === 'Approve') {
                Session::setFlash('success', 'Practice File APPROVED cleanly! Employee step completed.');
            } else {
                Session::setFlash('warning', 'Practice File REJECTED with Validation Errors logged into Error Tracker. Sent back for rework.');
            }

            redirect('training/practice');
        }
    }

    // ----------------------------------------------------
    // CERTIFICATIONS DASHBOARD & CERTIFICATES
    // ----------------------------------------------------
    public function certifications() {
        $user = current_user();
        $trainingModel = $this->model('Training_model');

        $myCertifications = $trainingModel->getUserCertifications($user['id']);
        $allCertifications = [];
        if (is_admin() || is_super_admin()) {
            $allCertifications = $trainingModel->getAllCertifications();
        }

        $this->render('training/certifications', [
            'title' => 'Employee Activity Certifications',
            'myCertifications' => $myCertifications,
            'allCertifications' => $allCertifications
        ]);
    }

    /**
     * View & Print Official Certificate of Competency
     */
    public function certificate(int $certId) {
        $trainingModel = $this->model('Training_model');
        $cert = $trainingModel->fetchOne("
            SELECT ac.*, sa.sub_activity_name, a.activity_name, d.division_name, u.full_name, u.user_code, u.department
            FROM activity_certifications ac
            JOIN sub_activities sa ON ac.sub_activity_id = sa.id
            JOIN activities a ON sa.activity_id = a.id
            LEFT JOIN divisions d ON sa.division_id = d.id
            JOIN users u ON ac.user_id = u.id
            WHERE ac.id = ? AND ac.status = 'Certified'
            LIMIT 1
        ", [$certId]);

        if (!$cert) {
            Session::setFlash('danger', 'Certificate not found or activity is not yet certified.');
            redirect('training/certifications');
        }

        $this->render('training/certificate_view', [
            'title' => 'Official Certificate of Competency - ' . $cert['full_name'],
            'cert' => $cert
        ]);
    }
}
