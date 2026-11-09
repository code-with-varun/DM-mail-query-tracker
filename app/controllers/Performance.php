<?php
/**
 * Performance & Scorecard Controller
 * Handles Count-Based & Workflow-Based Performance, Metric Weightages, Daily Production Logging, and Analytics
 */

class Performance extends Controller {

    public function index() {
        $this->requirePermission('performance_scorecard');

        $perfModel = $this->model('Performance_model');
        $userModel = $this->model('User_model');

        $userId = !empty($_GET['user_id']) ? (int)$_GET['user_id'] : $_SESSION['user_id'];
        $yearMonth = !empty($_GET['month']) ? sanitize($_GET['month']) : date('Y-m');

        $loggedUserId = (int)$_SESSION['user_id'];
        $loggedRoleId = (int)$_SESSION['role_id'];

        $downlineIds = $userModel->getDownlineUserIds($loggedUserId, $loggedRoleId);
        if (!in_array($userId, $downlineIds)) {
            $userId = $loggedUserId;
        }

        $scorecardData = $perfModel->getUserScorecard($userId, $yearMonth);
        $allUsers = $userModel->getDownlineEmployees($loggedUserId, $loggedRoleId);

        $awardModel = $this->model('Award_model');
        $userAwards = $awardModel->getEmployeeAwards($userId);

        $this->render('performance/scorecard', [
            'title' => 'Employee Performance Scorecard',
            'data' => $scorecardData,
            'allUsers' => $allUsers,
            'selectedUserId' => $userId,
            'selectedMonth' => $yearMonth,
            'userAwards' => $userAwards
        ]);
    }

    /**
     * Daily Production & Count-Based Log View & Entry
     */
    public function production() {
        $this->requireAuth();

        $perfModel = $this->model('Performance_model');
        $activityModel = $this->model('Activity_model');
        $projectModel = $this->model('Project_model');
        $userModel = $this->model('User_model');

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

        $logs = $perfModel->getProductionLogs($filters);
        $subActivities = $activityModel->getAllSubActivitiesWithHierarchy();
        $projects = $projectModel->getProjects();
        $employees = $userModel->getDownlineEmployees($loggedUserId, $loggedRoleId);

        $this->render('performance/production_log', [
            'title' => 'Daily Production & Count Logger',
            'logs' => $logs,
            'subActivities' => $subActivities,
            'projects' => $projects,
            'employees' => $employees,
            'filters' => $filters
        ]);
    }

    /**
     * Store Daily Production Log Entry
     */
    public function log_production() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('performance/production');
            }

            $perfModel = $this->model('Performance_model');
            $data = [
                'user_id' => !empty($_POST['user_id']) ? (int)$_POST['user_id'] : $_SESSION['user_id'],
                'project_id' => !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null,
                'sub_activity_id' => !empty($_POST['sub_activity_id']) ? (int)$_POST['sub_activity_id'] : null,
                'log_date' => sanitize($_POST['log_date'] ?? date('Y-m-d')),
                'processed_count' => (int)($_POST['processed_count'] ?? 0),
                'target_count' => (int)($_POST['target_count'] ?? 0),
                'worked_hours' => (float)($_POST['worked_hours'] ?? 8.0),
                'internal_errors' => (int)($_POST['internal_errors'] ?? 0),
                'external_errors' => (int)($_POST['external_errors'] ?? 0),
                'shift_status' => sanitize($_POST['shift_status'] ?? 'Present'),
                'remarks' => sanitize($_POST['remarks'] ?? '')
            ];

            $perfModel->saveDailyProductionLog($data);
            Session::setFlash('success', 'Daily production count logged & monthly scorecard updated successfully.');
        }

        redirect('performance/production');
    }

    /**
     * Team Metric Weightages & Scorecard Configurator
     */
    public function config() {
        $this->requireAuth();
        $this->requireRole([1, 2, 3]); // Super Admin, Senior Mgmt, Manager

        $perfModel = $this->model('Performance_model');
        $projectModel = $this->model('Project_model');

        $projectId = !empty($_GET['project_id']) ? (int)$_GET['project_id'] : null;
        $kpiConfigs = $perfModel->getTeamKpiConfig($projectId);
        $projects = $projectModel->getProjects();

        $this->render('performance/config', [
            'title' => 'Team Metric Weightage Configurator',
            'kpiConfigs' => $kpiConfigs,
            'projects' => $projects,
            'selectedProjectId' => $projectId
        ]);
    }

    /**
     * Save Team Metric Config
     */
    public function save_config() {
        $this->requireAuth();
        $this->requireRole([1, 2, 3]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('performance/config');
            }

            $perfModel = $this->model('Performance_model');
            $projectId = !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null;
            $rawKpis = $_POST['kpis'] ?? [];

            // Filter out empty rows
            $kpis = [];
            $totalPoints = 0;

            foreach ($rawKpis as $item) {
                $key = trim($item['kpi_key'] ?? '');
                $label = trim($item['label'] ?? '');
                if (empty($key) || empty($label)) continue;

                $weight = (int)($item['weight_points'] ?? 0);
                $totalPoints += $weight;
                $kpis[] = $item;
            }

            if (empty($kpis)) {
                Session::setFlash('danger', 'Please configure at least one performance metric line item.');
                redirect('performance/config' . ($projectId ? '?project_id=' . $projectId : ''));
            }

            if ($totalPoints !== 100) {
                Session::setFlash('warning', "Metric weights must sum up to exactly 100 Points. Current Total: {$totalPoints} Points.");
                redirect('performance/config' . ($projectId ? '?project_id=' . $projectId : ''));
            }

            $perfModel->saveTeamKpiConfig($projectId, $kpis);
            Session::setFlash('success', 'Team metric weightages & target benchmarks updated successfully.');
            redirect('performance/config' . ($projectId ? '?project_id=' . $projectId : ''));
        }

        redirect('performance/config');
    }

    /**
     * Multi-Horizon Performance Analytics (Daywise, Monthwise, Quarterwise, Yearwise)
     */
    public function analytics() {
        $this->requireAuth();
        $this->requirePermission('performance_analytics');

        $perfModel = $this->model('Performance_model');
        $projectModel = $this->model('Project_model');

        $horizon = sanitize($_GET['horizon'] ?? 'month');
        $projectId = !empty($_GET['project_id']) ? (int)$_GET['project_id'] : null;

        $analytics = $perfModel->getPerformanceAnalytics($horizon, ['project_id' => $projectId]);
        $projects = $projectModel->getProjects();

        $this->render('performance/analytics', [
            'title' => 'Performance Analytics & Multi-Horizon Trends',
            'analytics' => $analytics,
            'projects' => $projects,
            'selectedHorizon' => $horizon,
            'selectedProjectId' => $projectId
        ]);
    }

    /**
     * Export Performance Logs CSV
     */
    public function export_csv() {
        $this->requireAuth();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=EPMS_Production_Performance_Logs.csv');

        $output = fopen('php://output', 'w');
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['Log Date', 'Employee Code', 'Full Name', 'Project / Team', 'Sub Activity', 'Processed Count', 'Target Count', 'Worked Hours', 'Internal Errors', 'External Errors', 'Shift Status', 'Remarks']);

        $perfModel = $this->model('Performance_model');
        $logs = $perfModel->getProductionLogs();

        foreach ($logs as $l) {
            fputcsv($output, [
                $l['log_date'],
                $l['user_code'],
                $l['full_name'],
                $l['project_name'] ?? 'General Operations',
                $l['sub_activity_name'] ?? 'General Output',
                $l['processed_count'],
                $l['target_count'],
                $l['worked_hours'],
                $l['internal_errors'],
                $l['external_errors'],
                $l['shift_status'],
                $l['remarks']
            ]);
        }

        fclose($output);
        exit();
    }

    /**
     * Download Scorecard CSV/Excel Import Template
     */
    public function download_scorecard_template() {
        $this->requireAuth();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=MQT_Employee_Scorecard_Import_Template.csv');

        $output = fopen('php://output', 'w');
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, ['Emp Code', 'Employee Name', 'Month (YYYY-MM)', 'Total Working Days', 'No. of days Present', 'Processed Count', 'Target Count', 'Error Count (Internal)', 'Error Count (External)', 'PKT Score %']);
        fputcsv($output, ['172203', 'Rohan Bhujbal', date('Y-m'), '24', '24', '4041', '3500', '5', '1', '95']);
        fputcsv($output, ['175562', 'Siddhant Jadhav', date('Y-m'), '24', '22', '2713', '2500', '9', '2', '88']);
        fclose($output);
        exit();
    }

    /**
     * Bulk Import Monthly Scorecard CSV / Excel Data
     */
    public function import_scorecard() {
        $this->requireAuth();
        $this->requireRole([1, 2, 3]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('performance/scorecard');
            }

            if (empty($_FILES['import_file']['tmp_name'])) {
                Session::setFlash('danger', 'Please select a CSV or Excel file to upload.');
                redirect('performance/scorecard');
            }

            $handle = fopen($_FILES['import_file']['tmp_name'], 'r');
            if (!$handle) {
                Session::setFlash('danger', 'Failed to read uploaded file.');
                redirect('performance/scorecard');
            }

            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $header = fgetcsv($handle);
            $userModel = $this->model('User_model');
            $perfModel = $this->model('Performance_model');

            $updated = 0;
            $skipped = 0;

            while (($row = fgetcsv($handle)) !== false) {
                if (empty(array_filter($row))) continue;

                $empCode = trim($row[0] ?? $row[1] ?? '');
                $month = sanitize($row[2] ?? $row[8] ?? date('Y-m'));
                if (strlen($month) > 7) {
                    $month = date('Y-m', strtotime($month));
                }

                $user = $userModel->getByEmailOrUsername($empCode);
                if (!$user) {
                    $skipped++;
                    continue;
                }

                $userId = (int)$user['id'];
                $workingDays = (int)($row[3] ?? 24);
                $presentDays = (int)($row[4] ?? 24);
                $processedCount = (int)($row[5] ?? $row[12] ?? 0);
                $targetCount = (int)($row[6] ?? 1000);
                $intErrors = (int)($row[7] ?? $row[19] ?? 0);
                $extErrors = (int)($row[8] ?? $row[21] ?? 0);

                $logDate = $month . '-01';
                $perfModel->saveDailyProductionLog([
                    'user_id' => $userId,
                    'log_date' => $logDate,
                    'processed_count' => $processedCount,
                    'target_count' => $targetCount,
                    'internal_errors' => $intErrors,
                    'external_errors' => $extErrors,
                    'shift_status' => 'Present',
                    'remarks' => 'Imported via Scorecard Master CSV/Excel'
                ]);

                $perfModel->computeAndSaveMonthlyScorecard($userId, $month);
                $updated++;
            }

            fclose($handle);
            Session::setFlash('success', "Employee Scorecard Import completed: {$updated} employee records processed, {$skipped} skipped.");
        }

        redirect('performance/scorecard');
    }

    public function verify_scorecard() {
        $this->requireAuth();
        $this->requireRole([1, 2, 3, 5]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('performance/scorecard');
            }

            $scorecardId = (int)($_POST['scorecard_id'] ?? 0);
            $userId = (int)($_POST['user_id'] ?? $_SESSION['user_id']);
            $month = sanitize($_POST['month'] ?? date('Y-m'));

            if ($scorecardId > 0) {
                $perfModel = $this->model('Performance_model');
                $perfModel->verifyMonthlyScorecard($scorecardId, (int)$_SESSION['user_id']);
                Session::setFlash('success', 'Monthly scorecard verified by Team Lead successfully.');
            }
            redirect("performance/scorecard?user_id={$userId}&month={$month}");
        }
        redirect('performance/scorecard');
    }

    public function lock_scorecard() {
        $this->requireAuth();
        $this->requireRole([1, 3, 5]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('performance/scorecard');
            }

            $scorecardId = (int)($_POST['scorecard_id'] ?? 0);
            $userId = (int)($_POST['user_id'] ?? $_SESSION['user_id']);
            $month = sanitize($_POST['month'] ?? date('Y-m'));

            if ($scorecardId > 0) {
                $perfModel = $this->model('Performance_model');
                $perfModel->lockMonthlyScorecard($scorecardId, (int)$_SESSION['user_id']);
                Session::setFlash('success', 'Monthly scorecard frozen and locked by Management. Historical appraisal snapshot is now immutable.');
            }
            redirect("performance/scorecard?user_id={$userId}&month={$month}");
        }
        redirect('performance/scorecard');
    }

    public function update_notes() {
        $this->requireAuth();
        $this->requireRole([1, 2, 3, 5]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('performance/scorecard');
            }

            $scorecardId = (int)($_POST['scorecard_id'] ?? 0);
            $userId = (int)($_POST['user_id'] ?? $_SESSION['user_id']);
            $month = sanitize($_POST['month'] ?? date('Y-m'));
            $specialNote = sanitize($_POST['special_note'] ?? '');
            $recType = sanitize($_POST['recognition_type'] ?? '');
            $recDesc = sanitize($_POST['recognition_desc'] ?? '');

            if ($scorecardId > 0) {
                $perfModel = $this->model('Performance_model');
                $perfModel->updateScorecardNotes($scorecardId, $specialNote, $recType, $recDesc);
                Session::setFlash('success', 'Appraisal notes & recognition tags updated successfully.');
            }
            redirect("performance/scorecard?user_id={$userId}&month={$month}");
        }
        redirect('performance/scorecard');
    }

    /**
     * Gamified Contest & Grand Prix Championship Leaderboard
     */
    public function contest() {
        $this->requireAuth();
        if (!is_super_admin() && !is_senior_management()) {
            Session::setFlash('danger', 'Access restricted to Super Admin and Senior Management.');
            redirect('home');
        }
        $this->requirePermission('performance_contest');

        $contestModel = $this->model('Contest_model');
        $projectModel = $this->model('Project_model');

        $scope = sanitize($_GET['scope'] ?? 'teams');
        if (!in_array($scope, ['teams', 'individuals'])) {
            $scope = 'teams';
        }

        $timeframe = sanitize($_GET['timeframe'] ?? 'monthly');
        if (!in_array($timeframe, ['hourly', 'daily', 'monthly', 'quarterly'])) {
            $timeframe = 'monthly';
        }

        $top = !empty($_GET['top']) ? (int)$_GET['top'] : 10;
        if (!in_array($top, [3, 5, 10, 0])) {
            $top = 10;
        }

        $projectId = !empty($_GET['project_id']) ? (int)$_GET['project_id'] : null;

        $allProjects = $projectModel->getProjects();

        if ($scope === 'teams') {
            $standings = $contestModel->getTeamContestStandings($timeframe, $top);
        } else {
            $standings = $contestModel->getIndividualContestStandings($timeframe, $projectId, $top);
        }

        $slideshowData = $contestModel->getSlideshowData($projectId);

        $this->render('performance/contest', [
            'title' => 'Operations Grand Prix — Contest Management & Leaderboard',
            'scope' => $scope,
            'timeframe' => $timeframe,
            'top' => $top,
            'selectedProjectId' => $projectId,
            'allProjects' => $allProjects,
            'standings' => $standings,
            'slideshowData' => $slideshowData
        ]);
    }

    /**
     * Contest Data Telemetry Endpoint (for 1-Minute Live Auto-Refresh)
     */
    public function contest_data() {
        $this->requireAuth();
        if (!is_super_admin() && !is_senior_management()) {
            $this->json(['status' => 'error', 'message' => 'Unauthorized access'], 403);
            return;
        }

        $contestModel = $this->model('Contest_model');

        $scope = sanitize($_GET['scope'] ?? 'teams');
        if (!in_array($scope, ['teams', 'individuals'])) {
            $scope = 'teams';
        }

        $timeframe = sanitize($_GET['timeframe'] ?? 'monthly');
        if (!in_array($timeframe, ['hourly', 'daily', 'monthly', 'quarterly'])) {
            $timeframe = 'monthly';
        }

        $top = !empty($_GET['top']) ? (int)$_GET['top'] : 10;
        if (!in_array($top, [3, 5, 10, 0])) {
            $top = 10;
        }

        $projectId = !empty($_GET['project_id']) ? (int)$_GET['project_id'] : null;

        if ($scope === 'teams') {
            $standings = $contestModel->getTeamContestStandings($timeframe, $top);
        } else {
            $standings = $contestModel->getIndividualContestStandings($timeframe, $projectId, $top);
        }

        $slideshowData = $contestModel->getSlideshowData($projectId);

        $this->json([
            'status' => 'success',
            'scope' => $scope,
            'timeframe' => $timeframe,
            'top' => $top,
            'standings' => $standings,
            'slideshow' => $slideshowData,
            'synced_at' => date('H:i:s'),
            'timestamp' => time()
        ]);
    }

    /**
     * Dedicated Fullscreen Presentation Slideshow Mode
     */
    public function contest_slideshow() {
        $this->requireAuth();
        if (!is_super_admin() && !is_senior_management()) {
            Session::setFlash('danger', 'Access restricted to Super Admin and Senior Management.');
            redirect('home');
        }
        $this->requirePermission('performance_contest');

        $contestModel = $this->model('Contest_model');
        $projectId = !empty($_GET['project_id']) ? (int)$_GET['project_id'] : null;
        $slides = $contestModel->getSlideshowData($projectId);

        // Render standalone presentation without sidebar/navbar wrappers for clean TV signage
        $this->render('performance/contest_slideshow', [
            'title' => 'Grand Prix Championship — Live Executive Slideshow',
            'slides' => $slides
        ], false);
    }

    /**
     * Simulate an instant Overtake Event (for demonstration & sound testing)
     */
    public function simulate_overtake() {
        $this->requireAuth();
        if (!is_super_admin() && !is_senior_management()) {
            $this->json(['status' => 'error', 'message' => 'Unauthorized access'], 403);
            return;
        }
        $contestModel = $this->model('Contest_model');
        $result = $contestModel->simulateOvertakeEvent();
        $this->json($result);
    }

    /**
     * Datamatics Annual Achievers Awards (AAA) & DM-ISPARK Recognition Hub
     */
    public function awards() {
        $this->requireAuth();
        $this->requirePermission('performance_awards');

        $awardModel = $this->model('Award_model');
        $userModel = $this->model('User_model');
        $projectModel = $this->model('Project_model');

        $periodType = !empty($_GET['period_type']) ? sanitize($_GET['period_type']) : 'monthly';
        $periodKey = !empty($_GET['period_key']) ? sanitize($_GET['period_key']) : ($periodType === 'monthly' ? '2026-10' : ($periodType === 'quarterly' ? '2026-Q3' : ($periodType === 'annual' ? '2026-FY' : '10-Years')));
        $category = !empty($_GET['category']) ? sanitize($_GET['category']) : 'all';

        $distinctPeriods = $awardModel->getDistinctPeriods();
        $allAwards = $awardModel->getAllAwards($category === 'all' ? null : $category, null);
        $winners = $awardModel->getWinners($periodType, $periodKey);
        $totalAwardsConferred = count($awardModel->getWinners('all'));
        $allUsers = $userModel->getAllActiveUsers();
        $allProjects = $projectModel->getAll();

        // Automated contenders preview for the selected month/period
        $contenders = $awardModel->evaluateAutomatedContenders('monthly', $periodKey);

        $this->render('performance/awards', [
            'title' => 'Awards & Honours Hub (Datamatics AAA Framework)',
            'periodType' => $periodType,
            'periodKey' => $periodKey,
            'category' => $category,
            'distinctPeriods' => $distinctPeriods,
            'allAwards' => $allAwards,
            'winners' => $winners,
            'totalAwardsConferred' => $totalAwardsConferred,
            'allUsers' => $allUsers,
            'allProjects' => $allProjects,
            'contenders' => $contenders
        ]);
    }

    /**
     * API endpoint returning awards & winners
     */
    public function awards_data() {
        $this->requireAuth();
        $awardModel = $this->model('Award_model');

        $periodType = !empty($_GET['period_type']) ? sanitize($_GET['period_type']) : 'monthly';
        $periodKey = !empty($_GET['period_key']) ? sanitize($_GET['period_key']) : null;
        $category = !empty($_GET['category']) ? sanitize($_GET['category']) : null;

        $winners = $awardModel->getWinners($periodType, $periodKey);
        $awards = $awardModel->getAllAwards($category, $periodType);

        $this->json([
            'status' => 'success',
            'period_type' => $periodType,
            'period_key' => $periodKey,
            'winners' => $winners,
            'awards' => $awards
        ]);
    }

    /**
     * Evaluate Automated Contenders using 6-Factor Formula
     */
    public function evaluate_awards() {
        $this->requireAuth();
        if (!is_super_admin() && !is_senior_management() && !is_manager()) {
            $this->json(['status' => 'error', 'message' => 'Unauthorized access'], 403);
            return;
        }

        $periodKey = !empty($_GET['period_key']) ? sanitize($_GET['period_key']) : '2026-10';
        $awardModel = $this->model('Award_model');
        $contenders = $awardModel->evaluateAutomatedContenders('monthly', $periodKey);

        $this->json([
            'status' => 'success',
            'period_key' => $periodKey,
            'contenders' => $contenders
        ]);
    }

    /**
     * Bestow / Nominate an Official Award
     */
    public function bestow_award() {
        $this->requireAuth();
        if (!is_super_admin() && !is_senior_management() && !is_manager()) {
            Session::setFlash('danger', 'Unauthorized access.');
            redirect('performance/awards');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $awardModel = $this->model('Award_model');
            $userModel = $this->model('User_model');
            $projectModel = $this->model('Project_model');

            $awardId = (int)($_POST['award_id'] ?? 0);
            $userId = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
            $projectId = !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null;
            $recipientName = trim($_POST['recipient_name'] ?? '');
            $recipientRole = trim($_POST['recipient_role'] ?? '');

            if ($userId && empty($recipientName)) {
                $u = $userModel->getById($userId);
                if ($u) {
                    $recipientName = $u['full_name'];
                    $recipientRole = $u['department'] . ' (' . $u['user_code'] . ')';
                }
            } elseif ($projectId && empty($recipientName)) {
                $p = $projectModel->getById($projectId);
                if ($p) {
                    $recipientName = $p['project_name'];
                    $recipientRole = 'Operational Project Team';
                }
            }

            $award = $awardModel->getAwardById($awardId);
            if (!$award) {
                Session::setFlash('danger', 'Selected award not found.');
                redirect('performance/awards');
            }

            $data = [
                'award_id' => $awardId,
                'period_type' => $_POST['period_type'] ?? 'monthly',
                'period_key' => $_POST['period_key'] ?? date('Y-m'),
                'user_id' => $userId,
                'project_id' => $projectId,
                'recipient_name' => $recipientName,
                'recipient_role' => $recipientRole,
                'score' => (float)($_POST['score'] ?? 95.0),
                'rank_position' => (int)($_POST['rank_position'] ?? 1),
                'citation' => trim($_POST['citation'] ?? 'Outstanding operational performance benchmark.'),
                'productivity_score' => (float)($_POST['productivity_score'] ?? 95.0),
                'quality_score' => (float)($_POST['quality_score'] ?? 98.0),
                'sla_score' => (float)($_POST['sla_score'] ?? 99.0),
                'attendance_score' => (float)($_POST['attendance_score'] ?? 100.0),
                'innovation_score' => (float)($_POST['innovation_score'] ?? 90.0),
                'teamwork_score' => (float)($_POST['teamwork_score'] ?? 95.0),
                'display_in_slideshow' => 1
            ];

            $awardModel->bestowAward($data);
            Session::setFlash('success', "Award '{$award['award_name']}' successfully bestowed upon {$recipientName}!");
        }

        redirect('performance/awards');
    }

    /**
     * Delete / Revoke an Award Record
     */
    public function delete_award_winner() {
        $this->requireAuth();
        if (!is_super_admin() && !is_senior_management()) {
            Session::setFlash('danger', 'Unauthorized access.');
            redirect('performance/awards');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['winner_id'] ?? 0);
            $awardModel = $this->model('Award_model');
            $awardModel->deleteWinner($id);
            Session::setFlash('success', 'Award record revoked successfully.');
        }

        redirect('performance/awards');
    }

    /**
     * Fullscreen Dedicated Awards Slideshow
     */
    public function awards_slideshow() {
        $this->requireAuth();
        $this->requirePermission('performance_awards');

        $awardModel = $this->model('Award_model');
        $slides = $awardModel->getSlideshowAwards();

        $this->render('performance/awards_slideshow', [
            'title' => 'Datamatics Annual Achievers & Operational Champions — Live Slideshow',
            'slides' => $slides
        ], false);
    }
}

