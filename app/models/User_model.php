<?php
/**
 * User Model
 */

class User_model extends Model {
    public function getByEmailOrUsername(string $loginInput): ?array {
        $input = strtolower(trim($loginInput));
        $prefix = explode('@', $input)[0];

        $sql = "SELECT u.*, r.role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.role_id 
                WHERE LOWER(u.email) = ? 
                   OR LOWER(u.user_code) = ? 
                   OR LOWER(u.email) = ? 
                   OR LOWER(u.user_code) = ?
                LIMIT 1";
        return $this->fetchOne($sql, [$input, $input, $prefix, $prefix]);
    }

    public function getById(int $id): ?array {
        $sql = "SELECT u.*, r.role_name, m.full_name as manager_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.role_id 
                LEFT JOIN users m ON u.manager_id = m.id 
                WHERE u.id = ? 
                LIMIT 1";
        return $this->fetchOne($sql, [$id]);
    }

    public function getAllUsers(): array {
        $sql = "SELECT u.*, r.role_name, m.full_name as manager_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.role_id 
                LEFT JOIN users m ON u.manager_id = m.id 
                ORDER BY u.id DESC";
        return $this->fetchAll($sql);
    }

    public function getTeamEmployees(int $managerId): array {
        $sql = "SELECT u.*, r.role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.role_id 
                WHERE u.manager_id = ? OR u.id = ? 
                ORDER BY u.full_name ASC";
        return $this->fetchAll($sql, [$managerId, $managerId]);
    }

    public function getAdmins(): array {
        $sql = "SELECT u.*, r.role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.role_id 
                WHERE u.role_id IN (1, 2) AND u.status = 'Active' 
                ORDER BY u.full_name ASC";
        return $this->fetchAll($sql);
    }

    public function getEmployees(): array {
        $sql = "SELECT u.*, r.role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.role_id 
                WHERE u.status = 'Active' AND u.role_id IN (2, 3) 
                ORDER BY u.full_name ASC";
        return $this->fetchAll($sql);
    }

    public function createUser(array $data): int {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $data['created_at'] = date('Y-m-d H:i:s');
        $id = $this->insert('users', $data);
        $this->logAudit('Users', 'Create User', null, ['id' => $id, 'email' => $data['email']]);
        return $id;
    }

    public function updateUser(int $id, array $data): int {
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } else {
            unset($data['password']);
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        $result = $this->update('users', $data, "id = ?", [$id]);
        $this->logAudit('Users', 'Update User', ['id' => $id], $data);
        return $result;
    }

    public function updateLastLogin(int $id): void {
        $this->update('users', ['last_login' => date('Y-m-d H:i:s')], "id = ?", [$id]);
    }
}
