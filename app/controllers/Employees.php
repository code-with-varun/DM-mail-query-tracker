<?php
/**
 * Employee & User Management Controller
 */

class Employees extends Controller {
    public function index() {
        $this->requireAuth();
        $this->requireRole([1, 2]); // Super Admin or Admin

        $userModel = $this->model('User_model');
        $roleId = Session::get('role_id');
        $userId = Session::get('user_id');

        if ($roleId == 1) {
            $users = $userModel->getAllUsers();
        } else {
            $users = $userModel->getTeamEmployees($userId);
        }

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

            $userCode = sanitize($_POST['user_code'] ?? '');
            $fullName = sanitize($_POST['full_name'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? 'ChangeMe@123';
            $targetRoleId = (int)($_POST['role_id'] ?? 3);

            // Admins can only create Employees (role_id 3)
            if (Session::get('role_id') == 2) {
                $targetRoleId = 3;
                $managerId = Session::get('user_id');
            } else {
                $managerId = !empty($_POST['manager_id']) ? (int)$_POST['manager_id'] : null;
            }

            if (empty($userCode) || empty($fullName) || empty($email)) {
                Session::setFlash('danger', 'Employee Code, Full Name, and Email are required.');
                redirect('employees');
            }

            $userModel = $this->model('User_model');
            if ($userModel->getByEmailOrUsername($email) || $userModel->getByEmailOrUsername($userCode)) {
                Session::setFlash('danger', 'User with this Email or Employee Code already exists.');
                redirect('employees');
            }

            $userModel->createUser([
                'user_code' => $userCode,
                'full_name' => $fullName,
                'email' => $email,
                'mobile' => sanitize($_POST['mobile'] ?? ''),
                'password' => $password,
                'role_id' => $targetRoleId,
                'department' => sanitize($_POST['department'] ?? 'Operations'),
                'manager_id' => $managerId,
                'status' => 'Active'
            ]);

            Session::setFlash('success', 'User account created successfully.');
            redirect('employees');
        }
    }
}
