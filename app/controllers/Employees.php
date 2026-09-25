<?php
/**
 * Employee & User Management Controller
 */

class Employees extends Controller {
    public function index() {
        $this->requireAuth();
        $this->requireRole([1, 2]);

        $userModel = $this->model('User_model');
        $activityModel = $this->model('Activity_model');

        $users = $userModel->getAllUsers();
        $admins = $userModel->getAdmins();
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

        $this->render('employees/index', [
            'title' => 'Employee & User Management & Skill Matrix',
            'users' => $users,
            'admins' => $admins,
            'subActivities' => $subActivities,
            'userSkillsMap' => $userSkillsMap,
            'userFullSkillsMap' => $userFullSkillsMap
        ]);
    }

    public function create() {
        $this->requireAuth();
        $this->requireRole([1, 2]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('employees');
            }

            $userModel = $this->model('User_model');
            $id = (int)($_POST['id'] ?? 0);
            $action = $_POST['action'] ?? 'save';

            if ($action === 'delete' && $id > 0 && is_super_admin()) {
                $userModel->delete('users', "id = ?", [$id]);
                $userModel->delete('user_sub_activities', "user_id = ?", [$id]);
                Session::setFlash('success', 'User account deleted successfully.');
                redirect('employees');
            }

            $userData = [
                'user_code' => sanitize($_POST['user_code'] ?? ''),
                'full_name' => sanitize($_POST['full_name'] ?? ''),
                'email' => sanitize($_POST['email'] ?? ''),
                'mobile' => sanitize($_POST['mobile'] ?? ''),
                'role_id' => (int)($_POST['role_id'] ?? 3),
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
        $this->requireRole([1, 2]);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=MQT_Employees_Master.csv');

        $output = fopen('php://output', 'w');
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['User ID', 'Employee Code (Use this in Import)', 'Full Name', 'Email Address', 'Role', 'Department', 'Status']);
        $users = $this->model('User_model')->getAllUsers();
        foreach ($users as $u) {
            fputcsv($output, [$u['id'], $u['user_code'], $u['full_name'], $u['email'], $u['role_name'], $u['department'], $u['status']]);
        }
        fclose($output);
        exit();
    }
}
