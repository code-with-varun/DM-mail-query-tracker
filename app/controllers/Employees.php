<?php
/**
 * Employee & User Management Controller
 */

class Employees extends Controller {
    public function index() {
        $this->requireAuth();
        $this->requireRole([1, 2]);

        $userModel = $this->model('User_model');
        $users = $userModel->getAllUsers();
        $admins = $userModel->getAdmins();

        $this->render('employees/index', [
            'title' => 'Employee & User Management',
            'users' => $users,
            'admins' => $admins
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
                Session::setFlash('success', 'User account updated successfully.');
            } else {
                $userData['password'] = sanitize($_POST['password'] ?? 'ChangeMe@123');
                $userModel->createUser($userData);
                Session::setFlash('success', 'User account created successfully.');
            }

            redirect('employees');
        }
    }
}
