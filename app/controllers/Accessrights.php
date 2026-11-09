<?php
/**
 * Access Rights & Permissions Controller (Super Admin Only)
 * Allows Super Admin to configure page-wise & module-wise access rights for roles and users
 */

class Accessrights extends Controller {

    public function index() {
        $this->requireAuth();
        $this->requireRole([1]);

        $permModel = $this->model('Permission_model');
        $userModel = $this->model('User_model');

        // Ensure default permissions are initialized in DB
        $permModel->ensureInitialized();

        $catalog = $permModel->getModuleCatalog();
        $roles = $this->fetchAllRoles();
        $allUsers = $userModel->getAllUsers();

        // Mode: 'role' (default) or 'user'
        $mode = sanitize($_GET['mode'] ?? 'role');
        if (!in_array($mode, ['role', 'user'])) {
            $mode = 'role';
        }

        // Default role selection to Senior Management (5) or query param
        $selectedRoleId = !empty($_GET['role_id']) ? (int)$_GET['role_id'] : 5;
        $selectedUserId = !empty($_GET['user_id']) ? (int)$_GET['user_id'] : ($allUsers[0]['id'] ?? 1);

        $activePermissions = [];
        $selectedRole = null;
        $selectedUser = null;
        $hasCustomOverrides = false;

        if ($mode === 'role') {
            foreach ($roles as $r) {
                if ((int)$r['role_id'] === $selectedRoleId) {
                    $selectedRole = $r;
                    break;
                }
            }
            if (!$selectedRole) {
                $selectedRole = $roles[0] ?? ['role_id' => 1, 'role_name' => 'Super Admin'];
                $selectedRoleId = (int)$selectedRole['role_id'];
            }
            $activePermissions = $permModel->getRolePermissions($selectedRoleId);
        } else {
            foreach ($allUsers as $u) {
                if ((int)$u['id'] === $selectedUserId) {
                    $selectedUser = $u;
                    break;
                }
            }
            if (!$selectedUser && !empty($allUsers)) {
                $selectedUser = $allUsers[0];
                $selectedUserId = (int)$selectedUser['id'];
            }

            $userRoleId = (int)($selectedUser['role_id'] ?? 4);
            $activePermissions = $permModel->getEffectiveUserPermissions($selectedUserId, $userRoleId);
            $userOverrides = $permModel->getUserOverrides($selectedUserId);
            $hasCustomOverrides = !empty($userOverrides);
        }

        $this->render('settings/access_rights', [
            'title' => 'Page & Module Access Rights Control',
            'catalog' => $catalog,
            'roles' => $roles,
            'allUsers' => $allUsers,
            'mode' => $mode,
            'selectedRoleId' => $selectedRoleId,
            'selectedRole' => $selectedRole,
            'selectedUserId' => $selectedUserId,
            'selectedUser' => $selectedUser,
            'activePermissions' => $activePermissions,
            'hasCustomOverrides' => $hasCustomOverrides
        ]);
    }

    /**
     * Save updated permissions
     */
    public function save() {
        $this->requireAuth();
        $this->requireRole([1]);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('access-rights');
        }

        if (!Session::verifyCsrf()) {
            Session::setFlash('danger', 'Invalid security token.');
            redirect('access-rights');
        }

        $permModel = $this->model('Permission_model');
        $mode = sanitize($_POST['mode'] ?? 'role');
        
        // Support both full CRUD structure (POST['crud']) and legacy (POST['pages'])
        $crudData = $_POST['crud'] ?? [];
        if (empty($crudData) && !empty($_POST['pages']) && is_array($_POST['pages'])) {
            foreach ($_POST['pages'] as $pk) {
                $crudData[$pk] = ['view' => 1, 'create' => 0, 'edit' => 0, 'delete' => 0];
            }
        }

        // Clear session map just in case any legacy session had it
        Session::remove('user_permissions_map');

        if ($mode === 'role') {
            $roleId = (int)($_POST['role_id'] ?? 0);
            if ($roleId <= 0) {
                Session::setFlash('danger', 'Invalid role selected.');
                redirect('access-rights');
            }

            $permModel->saveRolePermissions($roleId, $crudData);
            Session::setFlash('success', 'Access rights and CRUD permissions for role successfully updated. Changes take effect immediately across all active user sessions.');
            redirect("access-rights?mode=role&role_id={$roleId}");
        } elseif ($mode === 'user') {
            $userId = (int)($_POST['user_id'] ?? 0);
            if ($userId <= 0) {
                Session::setFlash('danger', 'Invalid user selected.');
                redirect('access-rights');
            }

            $resetToRole = !empty($_POST['reset_to_role']);
            $permModel->saveUserPermissions($userId, $crudData, $resetToRole);

            if ($resetToRole) {
                Session::setFlash('success', 'User access rights and CRUD permissions reset to standard role defaults.');
            } else {
                Session::setFlash('success', 'Custom user-level access rights and CRUD permissions saved successfully.');
            }

            redirect("access-rights?mode=user&user_id={$userId}");
        }

        redirect('access-rights');
    }

    /**
     * Reset role permissions to factory defaults
     */
    public function reset_defaults() {
        $this->requireAuth();
        $this->requireRole([1]);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('access-rights');
        }

        if (!Session::verifyCsrf()) {
            Session::setFlash('danger', 'Invalid security token.');
            redirect('access-rights');
        }

        $roleId = (int)($_POST['role_id'] ?? 0);
        $permModel = $this->model('Permission_model');

        // Clear session map
        Session::remove('user_permissions_map');

        if ($roleId > 0) {
            $defaults = $permModel->getDefaultPermissionsByRole($roleId);
            $permModel->saveRolePermissions($roleId, $defaults);
            Session::setFlash('success', "Role access rights and CRUD settings have been successfully reset to factory defaults.");
            redirect("access-rights?mode=role&role_id={$roleId}");
        } else {
            $permModel->seedDefaultRolePermissions(true);
            Session::setFlash('success', "All 5 roles have been reset to factory default access rights and CRUD settings.");
            redirect('access-rights');
        }
    }

    /**
     * Fetch all roles from database
     */
    private function fetchAllRoles(): array {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT role_id, role_name, description FROM roles ORDER BY role_id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
