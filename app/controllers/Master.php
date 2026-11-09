<?php
/**
 * Master Data Controller (Activities, Sub-Activities, Divisions)
 */

class Master extends Controller {
    public function activities() {
        $this->requireAuth();
        $this->requireRole([1]);

        $activityModel = $this->model('Activity_model');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('master/activities');
            }

            $formType = $_POST['form_type'] ?? '';
            $action = $_POST['action'] ?? 'save';
            $id = (int)($_POST['id'] ?? 0);

            if ($action === 'delete' && $id > 0) {
                if ($formType === 'activity') {
                    $activityModel->delete('activities', "id = ?", [$id]);
                    $activityModel->delete('sub_activities', "activity_id = ?", [$id]);
                    Session::setFlash('success', 'Parent activity deleted.');
                } elseif ($formType === 'sub_activity') {
                    $activityModel->delete('sub_activities', "id = ?", [$id]);
                    Session::setFlash('success', 'Sub-activity deleted.');
                }
                redirect('master/activities');
            }

            if ($formType === 'activity') {
                $name = sanitize($_POST['activity_name'] ?? '');
                $desc = sanitize($_POST['description'] ?? '');
                $divId = !empty($_POST['division_id']) ? (int)$_POST['division_id'] : null;

                if (!empty($name)) {
                    if ($id > 0) {
                        $activityModel->update('activities', ['activity_name' => $name, 'description' => $desc, 'division_id' => $divId], "id = ?", [$id]);
                        Session::setFlash('success', 'Activity updated successfully.');
                    } else {
                        $activityModel->createActivity($name, $desc, $divId);
                        Session::setFlash('success', 'Activity created successfully.');
                    }
                }
            } elseif ($formType === 'sub_activity') {
                $actId = (int)($_POST['activity_id'] ?? 0);
                $subName = sanitize($_POST['sub_activity_name'] ?? '');
                $tat = (int)($_POST['default_tat_hours'] ?? 24);
                $divId = !empty($_POST['division_id']) ? (int)$_POST['division_id'] : null;
                $defaultUserId = !empty($_POST['default_user_id']) ? (int)$_POST['default_user_id'] : null;
                $defaultCheckerId = !empty($_POST['default_checker_id']) ? (int)$_POST['default_checker_id'] : null;

                if ($actId && !empty($subName)) {
                    if ($id > 0) {
                        $activityModel->updateSubActivity($id, $actId, $subName, $tat, $divId, $defaultUserId, null, $defaultCheckerId);
                        Session::setFlash('success', 'Sub-activity updated successfully.');
                    } else {
                        $activityModel->createSubActivity($actId, $subName, $tat, $divId, $defaultUserId, null, $defaultCheckerId);
                        Session::setFlash('success', 'Sub-activity created successfully.');
                    }
                }
            }
            redirect('master/activities');
        }

        $userModel = $this->model('User_model');
        $activities = $activityModel->getAllActivitiesWithSub();
        $allSubActivities = $activityModel->getAllSubActivitiesWithHierarchy();
        $divisions = $activityModel->getDivisions();
        $employees = $userModel->getEmployees();

        $this->render('master/activities', [
            'title' => 'Activities Master',
            'activities' => $activities,
            'allSubActivities' => $allSubActivities,
            'divisions' => $divisions,
            'employees' => $employees
        ]);
    }

    public function projects() {
        $this->requireAuth();
        $this->requireRole([1]); // Super Admin only

        $projectModel = $this->model('Project_model');
        $userModel = $this->model('User_model');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('master/projects');
            }

            $action = $_POST['action'] ?? 'save';
            $id = (int)($_POST['id'] ?? 0);

            if ($action === 'delete' && $id > 0) {
                if (!is_super_admin() && !is_manager()) {
                    Session::setFlash('danger', 'Unauthorized action.');
                    redirect('master/projects');
                }
                $projectModel->deleteProject($id);
                Session::remove('active_project_mode');
                Session::setFlash('success', 'Project / Team deleted successfully.');
                redirect('master/projects');
            }

            $name = sanitize($_POST['project_name'] ?? '');
            $code = sanitize($_POST['code'] ?? '');
            $description = sanitize($_POST['description'] ?? '');
            $status = sanitize($_POST['status'] ?? 'Active');
            $opType = sanitize($_POST['operation_type'] ?? 'production_count');
            if (!in_array($opType, ['production_count', 'ticket_workflow', 'hybrid'])) {
                $opType = 'production_count';
            }

            if (!empty($name) && !empty($code)) {
                $data = [
                    'project_name' => $name,
                    'code' => strtoupper($code),
                    'description' => $description,
                    'status' => $status,
                    'operation_type' => $opType
                ];

                if ($id > 0) {
                    $projectModel->updateProject($id, $data);
                    Session::setFlash('success', 'Project / Team updated successfully.');
                } else {
                    $data['created_by'] = (int)$_SESSION['user_id'];
                    $id = $projectModel->createProject($data);
                    Session::setFlash('success', 'Project / Team created successfully.');
                }

                // Handle team member mappings
                $mappedUsers = isset($_POST['mapped_users']) && is_array($_POST['mapped_users']) ? array_map('intval', $_POST['mapped_users']) : [];
                $projectModel->setProjectUsers($id, $mappedUsers);

                // If created by manager, ensure current manager is mapped to this project
                if (is_manager()) {
                    $userProjects = $projectModel->getUserProjectIds($_SESSION['user_id']);
                    if (!in_array($id, $userProjects)) {
                        $userProjects[] = $id;
                        $projectModel->setUserProjects($_SESSION['user_id'], $userProjects);
                    }
                }
            }
            Session::remove('active_project_mode');
            redirect('master/projects');
        }

        $allProjects = $projectModel->getProjects();
        if (!is_super_admin()) {
            // Scope projects for Manager
            $userPids = $projectModel->getUserProjectIds($_SESSION['user_id']);
            $projects = array_filter($allProjects, function($p) use ($userPids) {
                return in_array($p['id'], $userPids);
            });
        } else {
            $projects = $allProjects;
        }

        foreach ($projects as &$p) {
            $p['mapped_user_ids'] = $projectModel->getProjectUserIds($p['id']);
        }
        unset($p);

        $users = $userModel->getAllUsers();

        $this->render('master/projects', [
            'title' => 'Project & Team Master',
            'projects' => array_values($projects),
            'users' => $users
        ]);
    }

    public function divisions() {
        $this->requireAuth();
        $this->requireRole([1]);

        $activityModel = $this->model('Activity_model');
        $projectModel = $this->model('Project_model');
        $userModel = $this->model('User_model');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('master/divisions');
            }

            $action = $_POST['action'] ?? 'save';
            $id = (int)($_POST['id'] ?? 0);

            if ($action === 'delete' && $id > 0) {
                $activityModel->delete('divisions', "id = ?", [$id]);
                Session::setFlash('success', 'Division deleted successfully.');
                redirect('master/divisions');
            }

            $name = sanitize($_POST['division_name'] ?? '');
            $code = sanitize($_POST['code'] ?? '');
            $projectId = !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null;
            $teamLeaderId = !empty($_POST['team_leader_id']) ? (int)$_POST['team_leader_id'] : null;

            if (!empty($name) && !empty($code)) {
                if ($id > 0) {
                    $activityModel->update('divisions', [
                        'division_name' => $name, 
                        'code' => $code, 
                        'project_id' => $projectId,
                        'team_leader_id' => $teamLeaderId
                    ], "id = ?", [$id]);
                    Session::setFlash('success', 'Division updated successfully.');
                } else {
                    $activityModel->createDivision($name, $code, $projectId, $teamLeaderId);
                    Session::setFlash('success', 'Division created successfully.');
                }
            }
            redirect('master/divisions');
        }

        $teamLeaders = $userModel->fetchAll("SELECT id, full_name, user_code FROM users WHERE role_id IN (2, 3) AND status = 'Active' ORDER BY full_name ASC");

        $this->render('master/divisions', [
            'title' => 'Divisions Master',
            'divisions' => $activityModel->getDivisions(),
            'projects' => $projectModel->getProjects(),
            'teamLeaders' => $teamLeaders
        ]);
    }

    public function subactivities() {
        $this->requireAuth();
        $this->requireRole([1]);

        $activityModel = $this->model('Activity_model');
        $userModel = $this->model('User_model');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('master/subactivities');
            }

            $action = $_POST['action'] ?? 'save';
            $id = (int)($_POST['id'] ?? 0);

            if ($action === 'delete' && $id > 0) {
                $activityModel->delete('sub_activities', "id = ?", [$id]);
                Session::setFlash('success', 'Sub-activity deleted successfully.');
                redirect('master/subactivities');
            }

            $actId = (int)($_POST['activity_id'] ?? 0);
            $subName = sanitize($_POST['sub_activity_name'] ?? '');
            $tat = (int)($_POST['default_tat_hours'] ?? 24);
            $divId = !empty($_POST['division_id']) ? (int)$_POST['division_id'] : null;
            $defaultUserId = !empty($_POST['default_user_id']) ? (int)$_POST['default_user_id'] : null;
            $defaultCheckerId = !empty($_POST['default_checker_id']) ? (int)$_POST['default_checker_id'] : null;
            $defaultOccurrenceDay = !empty($_POST['default_occurrence_day']) ? (int)$_POST['default_occurrence_day'] : null;
            $workflowId = !empty($_POST['workflow_id']) ? (int)$_POST['workflow_id'] : null;

            if ($actId && !empty($subName)) {
                if ($id > 0) {
                    $activityModel->updateSubActivity($id, $actId, $subName, $tat, $divId, $defaultUserId, $defaultOccurrenceDay, $defaultCheckerId, null, $workflowId);
                    Session::setFlash('success', 'Sub-activity updated successfully.');
                } else {
                    $activityModel->createSubActivity($actId, $subName, $tat, $divId, $defaultUserId, $defaultOccurrenceDay, $defaultCheckerId, null, $workflowId);
                    Session::setFlash('success', 'Sub-activity created successfully.');
                }
            }
            redirect('master/subactivities');
        }

        $allSubActivities = $activityModel->getAllSubActivitiesWithHierarchy();
        $activities = $activityModel->getActivities();
        $divisions = $activityModel->getDivisions();
        $employees = $userModel->getEmployees();

        $wfModel = $this->model('Workflow_model');
        $workflows = $wfModel->getAllWorkflows();

        $this->render('master/subactivities', [
            'title' => 'Sub-activities Master',
            'subActivities' => $allSubActivities,
            'activities' => $activities,
            'divisions' => $divisions,
            'employees' => $employees,
            'workflows' => $workflows
        ]);
    }

    public function categories() {
        $this->requireAuth();
        $this->requireRole([1]);

        $categoryModel = $this->model('Category_model');
        $wfModel = $this->model('Workflow_model');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('master/categories');
            }

            $action = $_POST['action'] ?? 'save';
            $id = (int)($_POST['id'] ?? 0);

            if ($action === 'delete' && $id > 0) {
                $categoryModel->deleteCategory($id);
                Session::setFlash('success', 'Category deleted successfully.');
                redirect('master/categories');
            }

            $name = sanitize($_POST['category_name'] ?? '');
            $status = sanitize($_POST['status'] ?? 'Active');
            $workflowId = !empty($_POST['workflow_id']) ? (int)$_POST['workflow_id'] : null;

            if (!empty($name)) {
                if ($id > 0) {
                    $categoryModel->updateCategory($id, $name, $status, $workflowId);
                    Session::setFlash('success', 'Category updated successfully.');
                } else {
                    $categoryModel->createCategory($name, $status, $workflowId);
                    Session::setFlash('success', 'Category created successfully.');
                }
            }
            redirect('master/categories');
        }

        $categories = $categoryModel->getAllCategories();
        $workflows = $wfModel->getAllWorkflows();

        $this->render('master/categories', [
            'title' => 'Ticket Categories Master',
            'categories' => $categories,
            'workflows' => $workflows
        ]);
    }

    /**
     * Export Activities CSV Reference
     */
    public function export_activities() {
        $this->requireAuth();
        $this->requireRole([1, 2]);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=MQT_Activities_Master.csv');

        $output = fopen('php://output', 'w');
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['Activity ID', 'Activity Name', 'Description', 'Status']);
        $activities = $this->model('Activity_model')->getActivities();
        foreach ($activities as $act) {
            fputcsv($output, [$act['id'], $act['activity_name'], $act['description'] ?? '', 'Active']);
        }
        fclose($output);
        exit();
    }

    /**
     * Export Sub-Activities CSV Reference
     */
    public function export_sub_activities() {
        $this->requireAuth();
        $this->requireRole([1, 2]);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=MQT_Sub_Activities_Master.csv');

        $output = fopen('php://output', 'w');
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['Sub Activity ID', 'Sub Activity Name', 'Parent Activity Name', 'Default SLA TAT (Hours)', 'Default Maker Name', 'Default Checker Name']);
        $subActs = $this->model('Activity_model')->getAllSubActivitiesWithHierarchy();
        foreach ($subActs as $sa) {
            fputcsv($output, [
                $sa['id'], 
                $sa['sub_activity_name'], 
                $sa['activity_name'], 
                $sa['default_tat_hours'],
                $sa['default_user_name'] ?? 'Unassigned',
                $sa['default_checker_name'] ?? 'Unassigned'
            ]);
        }
        fclose($output);
        exit();
    }

    /**
     * Export Divisions CSV Reference
     */
    public function export_divisions() {
        $this->requireAuth();
        $this->requireRole([1, 2]);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=MQT_Divisions_Master.csv');

        $output = fopen('php://output', 'w');
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['Division ID', 'Division Code', 'Division Name', 'Status']);
        $divisions = $this->model('Activity_model')->getDivisions();
        foreach ($divisions as $div) {
            fputcsv($output, [$div['id'], $div['code'], $div['division_name'], $div['status']]);
        }
        fclose($output);
        exit();
    }

    /**
     * Export Single Combined Master CSV (Sub Activity + Activity + Division + Default Employee)
     */
    public function export_all() {
        $this->requireAuth();
        $this->requireRole([1, 2]);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=MQT_Complete_Hierarchy_Master.csv');

        $output = fopen('php://output', 'w');
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Single Combined Master Header
        fputcsv($output, [
            'Sub Activity Name',
            'Parent Activity Name',
            'Division Code',
            'Division Name',
            'Default Maker Code',
            'Default Maker Name',
            'Default Checker Code',
            'Default Checker Name',
            'Default SLA TAT (Hours)'
        ]);

        $subActs = $this->model('Activity_model')->getAllSubActivitiesWithHierarchy();
        foreach ($subActs as $sa) {
            fputcsv($output, [
                $sa['sub_activity_name'],
                $sa['activity_name'],
                $sa['division_code'] ?? '',
                $sa['division_name'] ?? '',
                $sa['default_user_code'] ?? '',
                $sa['default_user_name'] ?? '',
                $sa['default_checker_code'] ?? '',
                $sa['default_checker_name'] ?? '',
                $sa['default_tat_hours']
            ]);
        }

        fclose($output);
        exit();
    }

    /**
     * Download Divisions CSV Import Template
     */
    public function download_template_divisions() {
        $this->requireAuth();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=MQT_Divisions_Import_Template.csv');

        $output = fopen('php://output', 'w');
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, ['Division Code', 'Division Name', 'Status']);
        fputcsv($output, ['DIV-OPS', 'Operations Division', 'Active']);
        fputcsv($output, ['DIV-FIN', 'Finance Division', 'Active']);
        fclose($output);
        exit();
    }

    /**
     * Bulk Import Divisions CSV
     */
    public function import_divisions() {
        $this->requireAuth();
        $this->requireRole([1]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('master/divisions');
            }

            if (empty($_FILES['import_file']['tmp_name'])) {
                Session::setFlash('danger', 'Please select a CSV file to upload.');
                redirect('master/divisions');
            }

            $handle = fopen($_FILES['import_file']['tmp_name'], 'r');
            if (!$handle) {
                Session::setFlash('danger', 'Failed to read uploaded file.');
                redirect('master/divisions');
            }

            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $header = fgetcsv($handle);
            $activityModel = $this->model('Activity_model');
            $created = 0;
            $updated = 0;
            $skipped = 0;

            while (($row = fgetcsv($handle)) !== false) {
                if (empty(array_filter($row))) continue;

                $code = strtoupper(sanitize($row[0] ?? ''));
                $name = sanitize($row[1] ?? '');
                $status = sanitize($row[2] ?? 'Active');

                if (empty($code) || empty($name)) {
                    $skipped++;
                    continue;
                }

                $existing = $activityModel->getDivisionByCodeOrName($code) ?: $activityModel->getDivisionByCodeOrName($name);
                if ($existing) {
                    $activityModel->update('divisions', ['division_name' => $name, 'code' => $code, 'status' => $status], "id = ?", [$existing['id']]);
                    $updated++;
                } else {
                    $activityModel->createDivision($name, $code);
                    $created++;
                }
            }

            fclose($handle);
            Session::setFlash('success', "Divisions Import completed: {$created} created, {$updated} updated, {$skipped} skipped.");
        }

        redirect('master/divisions');
    }

    /**
     * Download Activities CSV Import Template
     */
    public function download_template_activities() {
        $this->requireAuth();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=MQT_Activities_Import_Template.csv');

        $output = fopen('php://output', 'w');
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, ['Activity Name', 'Description', 'Division Code']);
        fputcsv($output, ['File Preparation', 'Main calculation and prep activity', 'DIV-OPS']);
        fputcsv($output, ['Billing Reconciliation', 'Monthly billing reconciliation process', 'DIV-FIN']);
        fclose($output);
        exit();
    }

    /**
     * Bulk Import Activities CSV
     */
    public function import_activities() {
        $this->requireAuth();
        $this->requireRole([1]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('master/activities');
            }

            if (empty($_FILES['import_file']['tmp_name'])) {
                Session::setFlash('danger', 'Please select a CSV file to upload.');
                redirect('master/activities');
            }

            $handle = fopen($_FILES['import_file']['tmp_name'], 'r');
            if (!$handle) {
                Session::setFlash('danger', 'Failed to read uploaded file.');
                redirect('master/activities');
            }

            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $header = fgetcsv($handle);
            $activityModel = $this->model('Activity_model');
            $created = 0;
            $updated = 0;
            $skipped = 0;

            while (($row = fgetcsv($handle)) !== false) {
                if (empty(array_filter($row))) continue;

                $name = sanitize($row[0] ?? '');
                $desc = sanitize($row[1] ?? '');
                $divCode = sanitize($row[2] ?? '');

                if (empty($name)) {
                    $skipped++;
                    continue;
                }

                $divId = null;
                if (!empty($divCode)) {
                    $divObj = $activityModel->getDivisionByCodeOrName($divCode);
                    if ($divObj) $divId = (int)$divObj['id'];
                }

                $existing = $activityModel->getActivityByName($name);
                if ($existing) {
                    $activityModel->update('activities', ['description' => $desc, 'division_id' => $divId], "id = ?", [$existing['id']]);
                    $updated++;
                } else {
                    $activityModel->createActivity($name, $desc, $divId);
                    $created++;
                }
            }

            fclose($handle);
            Session::setFlash('success', "Activities Import completed: {$created} created, {$updated} updated, {$skipped} skipped.");
        }

        redirect('master/activities');
    }

    /**
     * Download Sub-Activities CSV Import Template
     */
    public function download_template_sub_activities() {
        $this->requireAuth();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=MQT_Sub_Activities_Import_Template.csv');

        $output = fopen('php://output', 'w');
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, ['Sub Activity Name', 'Parent Activity Name', 'Division Code', 'Default SLA TAT (Hours)', 'Default Maker Code', 'Default Checker Code', 'Occurrence Day']);
        fputcsv($output, ['CCOD Billing Prep', 'File Preparation', 'DIV-OPS', '24', 'EMP001', 'EMP002', '5']);
        fputcsv($output, ['Vendor Payout Calc', 'Billing Reconciliation', 'DIV-FIN', '48', 'EMP003', 'EMP002', '10']);
        fclose($output);
        exit();
    }

    /**
     * Bulk Import Sub-Activities CSV
     */
    public function import_sub_activities() {
        $this->requireAuth();
        $this->requireRole([1]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('master/subactivities');
            }

            if (empty($_FILES['import_file']['tmp_name'])) {
                Session::setFlash('danger', 'Please select a CSV file to upload.');
                redirect('master/subactivities');
            }

            $handle = fopen($_FILES['import_file']['tmp_name'], 'r');
            if (!$handle) {
                Session::setFlash('danger', 'Failed to read uploaded file.');
                redirect('master/subactivities');
            }

            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $header = fgetcsv($handle);
            $activityModel = $this->model('Activity_model');
            $userModel = $this->model('User_model');
            $created = 0;
            $updated = 0;
            $skipped = 0;

            while (($row = fgetcsv($handle)) !== false) {
                if (empty(array_filter($row))) continue;

                $subName = sanitize($row[0] ?? '');
                $parentActName = sanitize($row[1] ?? '');
                $divCode = sanitize($row[2] ?? '');
                $tat = (int)($row[3] ?? 24);
                $makerCode = sanitize($row[4] ?? '');
                $checkerCode = sanitize($row[5] ?? '');
                $occDay = !empty($row[6]) ? (int)$row[6] : null;

                if (empty($subName) || empty($parentActName)) {
                    $skipped++;
                    continue;
                }

                // Resolve Parent Activity
                $parentAct = $activityModel->getActivityByName($parentActName);
                if (!$parentAct) {
                    $divId = null;
                    if (!empty($divCode)) {
                        $divObj = $activityModel->getDivisionByCodeOrName($divCode);
                        if ($divObj) $divId = (int)$divObj['id'];
                    }
                    $actId = $activityModel->createActivity($parentActName, "Auto-created via Sub-Activity import", $divId);
                } else {
                    $actId = (int)$parentAct['id'];
                }

                // Resolve Division
                $divId = null;
                if (!empty($divCode)) {
                    $divObj = $activityModel->getDivisionByCodeOrName($divCode);
                    if ($divObj) $divId = (int)$divObj['id'];
                }

                // Resolve Maker User
                $makerId = null;
                if (!empty($makerCode)) {
                    $makerObj = $userModel->getUserByCodeOrEmail($makerCode);
                    if ($makerObj) $makerId = (int)$makerObj['id'];
                }

                // Resolve Checker User
                $checkerId = null;
                if (!empty($checkerCode)) {
                    $checkerObj = $userModel->getUserByCodeOrEmail($checkerCode);
                    if ($checkerObj) $checkerId = (int)$checkerObj['id'];
                }

                $existing = $activityModel->getSubActivityByName($subName);
                if ($existing) {
                    $activityModel->updateSubActivity($existing['id'], $actId, $subName, $tat, $divId, $makerId, $occDay, $checkerId);
                    $updated++;
                } else {
                    $activityModel->createSubActivity($actId, $subName, $tat, $divId, $makerId, $occDay, $checkerId);
                    $created++;
                }
            }

            fclose($handle);
            Session::setFlash('success', "Sub-Activities Import completed: {$created} created, {$updated} updated, {$skipped} skipped.");
        }

        redirect('master/subactivities');
    }

    /**
     * Organizational Hierarchy Tree View
     */
    public function hierarchy() {
        $this->requireAuth();
        $this->requireRole([1, 2, 3, 4]);

        $projectModel = $this->model('Project_model');
        $tree = $projectModel->getOrganizationalTree();

        $this->render('master/hierarchy', [
            'title' => 'Organizational Hierarchy Tree',
            'tree' => $tree
        ]);
    }
}
