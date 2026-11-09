<?php
/**
 * Employee & User Management Controller
 */

class Employees extends Controller {
    public function index() {
        $this->requireAuth();
        $this->requireRole([1, 2, 3, 5]);

        $loggedUserId = (int)Session::get('user_id');
        $loggedRoleId = (int)Session::get('role_id');

        $userModel = $this->model('User_model');
        $activityModel = $this->model('Activity_model');

        $users = $userModel->getDownlineUsers($loggedUserId, $loggedRoleId, false);
        $admins = $userModel->getDownlineManagers($loggedUserId, $loggedRoleId);
        $subActivities = $activityModel->getAllSubActivitiesWithHierarchy();

        $userSkillsMap = [];
        $userFullSkillsMap = [];
        foreach ($users as $u) {
            $skills = $userModel->getUserSkills($u['id']);
            $mapped = [];
            foreach ($skills as $sk) {
                $mapped[$sk['sub_activity_id']] = $sk['role_type'];
            }
            $userSkillsMap[$u['id']] = $mapped;
            $userFullSkillsMap[$u['id']] = $skills;
        }

        $groupedSubActivities = [];
        foreach ($subActivities as $sa) {
            $divName = !empty($sa['division_name']) ? $sa['division_name'] : 'General Operations';
            $actName = $sa['activity_name'] ?? 'General Activities';
            $groupedSubActivities[$divName][$actName][] = $sa;
        }

        $this->render('employees/index', [
            'title' => 'Employee & User Management & Skill Matrix',
            'users' => $users,
            'admins' => $admins,
            'subActivities' => $subActivities,
            'groupedSubActivities' => $groupedSubActivities,
            'userSkillsMap' => $userSkillsMap,
            'userFullSkillsMap' => $userFullSkillsMap
        ]);
    }

    public function create() {
        $this->requireAuth();
        $this->requireRole([1, 2, 3, 5]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('employees');
            }

            $userModel = $this->model('User_model');
            $id = (int)($_POST['id'] ?? 0);
            $action = $_POST['action'] ?? 'save';
            $loggedUserId = (int)Session::get('user_id');
            $loggedRoleId = (int)Session::get('role_id');

            // Strictly prevent self-edit or self-delete in employee management
            if ($id > 0) {
                if ($id === $loggedUserId) {
                    Session::setFlash('danger', 'Self-edit is not permitted in Employee Management. Please use Profile settings.');
                    redirect('employees');
                }

                $downlineIds = $userModel->getDownlineUserIds($loggedUserId, $loggedRoleId, false);
                if (!in_array($id, $downlineIds)) {
                    Session::setFlash('danger', 'Unauthorized: You can only manage user accounts within your reporting downlines.');
                    redirect('employees');
                }
            }

            if ($action === 'delete' && $id > 0 && is_super_admin()) {
                $userModel->delete('users', "id = ?", [$id]);
                $userModel->delete('user_sub_activities', "user_id = ?", [$id]);
                Session::setFlash('success', 'User account deleted successfully.');
                redirect('employees');
            }

            if ($action === 'update_skills' && $id > 0) {
                $skillsData = $_POST['skills'] ?? [];
                $userModel->setUserSkills($id, $skillsData);
                Session::setFlash('success', 'Skill matrix updated successfully for user.');
                redirect('employees');
            }

            $userData = [
                'user_code' => sanitize($_POST['user_code'] ?? ''),
                'full_name' => sanitize($_POST['full_name'] ?? ''),
                'email' => sanitize($_POST['email'] ?? ''),
                'mobile' => sanitize($_POST['mobile'] ?? ''),
                'role_id' => (int)($_POST['role_id'] ?? 4),
                'designation' => sanitize($_POST['designation'] ?? ''),
                'department' => sanitize($_POST['department'] ?? 'Operations'),
                'manager_id' => !empty($_POST['manager_id']) ? (int)$_POST['manager_id'] : null,
                'status' => sanitize($_POST['status'] ?? 'Active')
            ];

            if ($id > 0 && (is_super_admin() || is_admin())) {
                if (!empty($_POST['password'])) {
                    $userData['password'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
                }
                $userModel->update('users', $userData, "id = ?", [$id]);
                $targetUserId = $id;
                Session::setFlash('success', 'User account & skill matrix updated successfully.');
            } else {
                $userData['password'] = sanitize($_POST['password'] ?? 'ChangeMe@123');
                $targetUserId = $userModel->createUser($userData);
                Session::setFlash('success', 'User account & skill matrix created successfully.');
            }

            // Save Sub-Activities & Maker-Checker Mapping
            if (isset($_POST['skills']) && is_array($_POST['skills'])) {
                $userModel->setUserSkills($targetUserId, $_POST['skills']);
            }

            redirect('employees');
        }
    }

    /**
     * Export Employees CSV Reference
     */
    public function export() {
        $this->requireAuth();
        $this->requireRole([1, 2, 3, 5]);

        $loggedUserId = (int)Session::get('user_id');
        $loggedRoleId = (int)Session::get('role_id');

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=MQT_Employees_Master.csv');

        $output = fopen('php://output', 'w');
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['User ID', 'Employee Code', 'Full Name', 'Designation', 'Role', 'Department', 'Reporting Manager', 'Status']);
        $users = $this->model('User_model')->getDownlineUsers($loggedUserId, $loggedRoleId, false);
        foreach ($users as $u) {
            fputcsv($output, [$u['id'], $u['user_code'], $u['full_name'], $u['designation'] ?? '', $u['role_name'], $u['department'], $u['manager_name'] ?? 'None / Super Admin', $u['status']]);
        }
        fclose($output);
        exit();
    }

    /**
     * Download Employees CSV Import Template
     */
    public function download_template() {
        $this->requireAuth();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=MQT_Employees_Import_Template.csv');

        $output = fopen('php://output', 'w');
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, ['Employee Code', 'Full Name', 'Email Address', 'Mobile', 'Designation', 'Role', 'Department', 'Password', 'Status']);
        fputcsv($output, ['EMP010', 'Ramesh Rao', 'ramesh@company.com', '9876543210', 'Process Associate', 'Employee', 'Operations', 'ChangeMe@123', 'Active']);
        fputcsv($output, ['EMP011', 'Sunita Sharma', 'sunita@company.com', '9876543211', 'Team Leader', 'Admin (TL)', 'Banking Operations', 'ChangeMe@123', 'Active']);
        fclose($output);
        exit();
    }

    /**
     * Bulk Import Employees / Users CSV
     */
    public function import() {
        $this->requireAuth();
        $this->requireRole([1, 2, 3, 5]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('employees');
            }

            if (empty($_FILES['import_file']['tmp_name'])) {
                Session::setFlash('danger', 'Please select a CSV file to upload.');
                redirect('employees');
            }

            $handle = fopen($_FILES['import_file']['tmp_name'], 'r');
            if (!$handle) {
                Session::setFlash('danger', 'Failed to read uploaded file.');
                redirect('employees');
            }

            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $header = fgetcsv($handle);
            $userModel = $this->model('User_model');
            $created = 0;
            $updated = 0;
            $skipped = 0;

            while (($row = fgetcsv($handle)) !== false) {
                if (empty(array_filter($row))) continue;

                $userCode = strtoupper(sanitize($row[0] ?? ''));
                $fullName = sanitize($row[1] ?? '');
                $email = sanitize($row[2] ?? '');
                $mobile = sanitize($row[3] ?? '');
                $designation = sanitize($row[4] ?? '');
                $roleStr = strtolower(trim($row[5] ?? ''));
                $dept = sanitize($row[6] ?? 'Operations');
                $pass = !empty($row[7]) ? trim($row[7]) : 'ChangeMe@123';
                $status = sanitize($row[8] ?? 'Active');

                if (empty($userCode) || empty($fullName) || empty($email)) {
                    $skipped++;
                    continue;
                }

                // Map Role (1: Super Admin, 2: Admin (TL), 3: Manager, 4: Employee, 5: Senior Management)
                $roleId = 4; // Default Employee
                if (strpos($roleStr, 'super') !== false) {
                    $roleId = 1;
                } elseif (strpos($roleStr, 'senior') !== false || strpos($roleStr, 'mgmt') !== false || strpos($roleStr, 'management') !== false) {
                    $roleId = 5;
                } elseif (strpos($roleStr, 'manager') !== false) {
                    $roleId = 3;
                } elseif (strpos($roleStr, 'tl') !== false || strpos($roleStr, 'lead') !== false || strpos($roleStr, 'admin') !== false) {
                    $roleId = 2;
                } elseif (strpos($roleStr, 'employee') !== false || strpos($roleStr, 'checker') !== false || strpos($roleStr, 'maker') !== false) {
                    $roleId = 4;
                }

                $existing = $userModel->getUserByCodeOrEmail($userCode) ?: $userModel->getUserByCodeOrEmail($email);
                
                $userData = [
                    'user_code' => $userCode,
                    'full_name' => $fullName,
                    'email' => $email,
                    'mobile' => $mobile,
                    'role_id' => $roleId,
                    'designation' => $designation,
                    'department' => $dept,
                    'status' => $status
                ];

                if ($existing) {
                    if (!empty($pass) && $pass !== 'ChangeMe@123') {
                        $userData['password'] = password_hash($pass, PASSWORD_BCRYPT);
                    }
                    $userModel->update('users', $userData, "id = ?", [$existing['id']]);
                    $updated++;
                } else {
                    $userData['password'] = $pass;
                    $userModel->createUser($userData);
                    $created++;
                }
            }

            fclose($handle);
            Session::setFlash('success', "Employees Import completed: {$created} created, {$updated} updated, {$skipped} skipped.");
        }

        redirect('employees');
    }

    /**
     * Dedicated Full Page Employee Skill Matrix & Competency Alignment Hub
     */
    public function skill_matrix(?int $userId = null) {
        $this->requireAuth();
        $this->requireRole([1, 2, 3, 4, 5]);

        $loggedUserId = (int)Session::get('user_id');
        $loggedRoleId = (int)Session::get('role_id');

        $userModel = $this->model('User_model');
        $activityModel = $this->model('Activity_model');

        $users = $userModel->getDownlineUsers($loggedUserId, $loggedRoleId, false);
        if (empty($users)) {
            Session::setFlash('warning', 'No reporting downlines found in system.');
            redirect('home');
        }

        // Determine active target user
        $targetUser = null;
        if ($userId && $userId > 0) {
            $downlineIds = $userModel->getDownlineUserIds($loggedUserId, $loggedRoleId, false);
            if (in_array($userId, $downlineIds)) {
                $targetUser = $userModel->getById($userId);
            }
        }

        if (!$targetUser) {
            $targetUser = $users[0];
        }

        $subActivities = $activityModel->getAllSubActivitiesWithHierarchy();
        $userSkills = $userModel->getUserSkills($targetUser['id']);

        $userSkillsMap = [];
        foreach ($userSkills as $sk) {
            $userSkillsMap[$sk['sub_activity_id']] = [
                'role_type' => $sk['role_type']
            ];
        }

        // Group sub-activities by Division -> Activity
        $groupedSubActivities = [];
        foreach ($subActivities as $sa) {
            $divName = !empty($sa['division_name']) ? $sa['division_name'] : 'General Operations';
            $actName = $sa['activity_name'] ?? 'General Activities';
            $groupedSubActivities[$divName][$actName][] = $sa;
        }

        // Summary Stats
        $totalSubActivities = count($subActivities);
        $assignedCount = count($userSkills);
        $makerCount = 0;
        $checkerCount = 0;
        $bothCount = 0;

        foreach ($userSkills as $sk) {
            if ($sk['role_type'] === 'Maker') $makerCount++;
            elseif ($sk['role_type'] === 'Checker') $checkerCount++;
            elseif ($sk['role_type'] === 'Both') $bothCount++;
        }

        $coveragePct = $totalSubActivities > 0 ? round(($assignedCount / $totalSubActivities) * 100, 1) : 0;

        $this->render('employees/skill_matrix', [
            'title' => 'Skill Matrix Alignment — ' . htmlspecialchars($targetUser['full_name']),
            'targetUser' => $targetUser,
            'users' => $users,
            'subActivities' => $subActivities,
            'groupedSubActivities' => $groupedSubActivities,
            'userSkills' => $userSkills,
            'userSkillsMap' => $userSkillsMap,
            'stats' => [
                'total' => $totalSubActivities,
                'assigned' => $assignedCount,
                'maker' => $makerCount,
                'checker' => $checkerCount,
                'both' => $bothCount,
                'coverage_pct' => $coveragePct
            ]
        ]);
    }

    /**
     * Save Skill Matrix & Role Alignments from Dedicated Page
     */
    public function save_skill_matrix() {
        $this->requireAuth();
        $this->requireRole([1, 2, 3, 4, 5]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('employees/skill-matrix');
            }

            $userModel = $this->model('User_model');
            $userId = (int)($_POST['user_id'] ?? 0);

            if ($userId <= 0) {
                Session::setFlash('danger', 'Invalid employee specified.');
                redirect('employees/skill-matrix');
            }

            $loggedUserId = (int)Session::get('user_id');
            $loggedRoleId = (int)Session::get('role_id');

            if ($userId === $loggedUserId) {
                Session::setFlash('danger', 'Self-edit is not permitted for skill matrix. You can only configure skills for reporting downlines.');
                redirect('employees/skill-matrix');
            }

            $downlineIds = $userModel->getDownlineUserIds($loggedUserId, $loggedRoleId, false);
            if (!in_array($userId, $downlineIds)) {
                Session::setFlash('danger', 'Unauthorized: Employee is outside your reporting scope.');
                redirect('employees/skill-matrix');
            }

            $skillsData = $_POST['skills'] ?? [];
            $userModel->setUserSkills($userId, $skillsData);

            $user = $userModel->getById($userId);
            $userName = $user ? $user['full_name'] : 'Employee';

            Session::setFlash('success', "Skill Matrix & Role Mapping updated successfully for {$userName}.");
            redirect('employees/skill-matrix/' . $userId);
        }

        redirect('employees/skill-matrix');
    }
}

