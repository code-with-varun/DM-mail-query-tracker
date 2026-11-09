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

    public function getAllActiveUsers(): array {
        $sql = "SELECT u.*, r.role_name, m.full_name as manager_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.role_id 
                LEFT JOIN users m ON u.manager_id = m.id 
                WHERE u.status = 'Active'
                ORDER BY u.full_name ASC";
        return $this->fetchAll($sql);
    }

    /**
     * Get all downline user IDs (recursive reporting tree)
     * - Super Admin (Role 1): Sees everyone
     * - Senior Management (Role 5), Manager (Role 3), Team Lead (Role 2): Direct and indirect reports
     * - Employee (Role 4): Sees only self
     */
    public function getDownlineUserIds(int $userId, int $roleId, bool $includeSelf = false): array {
        if ($roleId == 1) {
            $sql = $includeSelf ? "SELECT id FROM users ORDER BY id ASC" : "SELECT id FROM users WHERE id != ? ORDER BY id ASC";
            $params = $includeSelf ? [] : [$userId];
            $all = $this->fetchAll($sql, $params);
            return array_map(function($r) { return (int)$r['id']; }, $all);
        }

        if ($roleId == 5) {
            $sql = $includeSelf 
                ? "SELECT id FROM users WHERE role_id != 1 ORDER BY id ASC" 
                : "SELECT id FROM users WHERE role_id != 1 AND id != ? ORDER BY id ASC";
            $params = $includeSelf ? [] : [$userId];
            $all = $this->fetchAll($sql, $params);
            return array_map(function($r) { return (int)$r['id']; }, $all);
        }

        $downlineIds = $includeSelf ? [$userId] : [];
        $queue = [$userId];

        while (!empty($queue)) {
            $currentId = array_shift($queue);
            $reports = $this->fetchAll("SELECT id FROM users WHERE manager_id = ?", [$currentId]);
            foreach ($reports as $r) {
                $repId = (int)$r['id'];
                if (!in_array($repId, $downlineIds, true) && ($includeSelf || $repId !== $userId)) {
                    $downlineIds[] = $repId;
                    $queue[] = $repId;
                }
            }
        }

        return $downlineIds;
    }

    /**
     * Get full user details for all downline users
     */
    public function getDownlineUsers(int $userId, int $roleId, bool $includeSelf = false): array {
        $ids = $this->getDownlineUserIds($userId, $roleId, $includeSelf);
        if (empty($ids)) {
            return [];
        }
        $inClause = implode(',', array_map('intval', $ids));
        $sql = "SELECT u.*, r.role_name, m.full_name as manager_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.role_id 
                LEFT JOIN users m ON u.manager_id = m.id 
                WHERE u.id IN ({$inClause})
                ORDER BY u.role_id ASC, u.id ASC";
        return $this->fetchAll($sql);
    }

    /**
     * Get eligible managers within user's downline scope for assignment
     */
    public function getDownlineManagers(int $userId, int $roleId): array {
        if ($roleId == 1) {
            return $this->getAdmins();
        }
        $ids = $this->getDownlineUserIds($userId, $roleId, true);
        if (empty($ids)) {
            return [];
        }
        $inClause = implode(',', array_map('intval', $ids));
        $sql = "SELECT u.*, r.role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.role_id 
                WHERE u.id IN ({$inClause}) AND (u.role_id IN (1, 2, 3, 5) OR u.id = ?) AND u.status = 'Active' 
                ORDER BY u.full_name ASC";
        return $this->fetchAll($sql, [$userId]);
    }

    /**
     * Get active employees/workers within downline scope (for slicers, analytics, scorecards)
     */
    public function getDownlineEmployees(int $userId, int $roleId): array {
        if ($roleId == 1) {
            return $this->getEmployees();
        }
        $ids = $this->getDownlineUserIds($userId, $roleId, true);
        if (empty($ids)) {
            return [];
        }
        $inClause = implode(',', array_map('intval', $ids));
        $sql = "SELECT u.*, r.role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.role_id 
                WHERE u.id IN ({$inClause}) AND u.status = 'Active' 
                ORDER BY u.full_name ASC";
        return $this->fetchAll($sql);
    }

    public function getTeamEmployees(int $managerId): array {
        $user = $this->getById($managerId);
        $roleId = $user ? (int)$user['role_id'] : 3;
        return $this->getDownlineEmployees($managerId, $roleId);
    }

    public function getAdmins(): array {
        $sql = "SELECT u.*, r.role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.role_id 
                WHERE u.role_id IN (1, 2, 3, 5) AND u.status = 'Active' 
                ORDER BY u.full_name ASC";
        return $this->fetchAll($sql);
    }

    public function getEmployees(): array {
        $sql = "SELECT u.*, r.role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.role_id 
                WHERE u.status = 'Active' AND u.role_id IN (2, 3, 4) 
                ORDER BY u.full_name ASC";
        return $this->fetchAll($sql);
    }

    public function getUsersByRole(int $roleId): array {
        $sql = "SELECT u.*, r.role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.role_id 
                WHERE u.role_id = ? AND u.status = 'Active' 
                ORDER BY u.full_name ASC";
        return $this->fetchAll($sql, [$roleId]);
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

    public function getUserSkills(int $userId): array {
        return $this->fetchAll("
            SELECT usa.*, sa.sub_activity_name, a.activity_name 
            FROM user_sub_activities usa 
            JOIN sub_activities sa ON usa.sub_activity_id = sa.id 
            JOIN activities a ON sa.activity_id = a.id 
            WHERE usa.user_id = ? 
            ORDER BY a.activity_name ASC, sa.sub_activity_name ASC
        ", [$userId]);
    }

    public function setUserSkills(int $userId, array $skills): void {
        // Track previous default roles for sync check
        $asDefaultMaker = $this->fetchAll("SELECT id FROM sub_activities WHERE default_user_id = ?", [$userId]);
        $asDefaultChecker = $this->fetchAll("SELECT id FROM sub_activities WHERE default_checker_id = ?", [$userId]);

        $this->delete('user_sub_activities', 'user_id = ?', [$userId]);
        $selectedSkills = [];

        foreach ($skills as $key => $skill) {
            $subActId = 0;
            if (isset($skill['sub_activity_id'])) {
                $subActId = (int)$skill['sub_activity_id'];
            } elseif (is_numeric($key)) {
                $subActId = (int)$key;
            }

            $isSelected = !empty($skill['selected']) || (isset($skill['selected']) && $skill['selected'] == '1');
            if ($subActId > 0 && $isSelected) {
                $roleType = $skill['role_type'] ?? 'Maker';
                if (!in_array($roleType, ['Maker', 'Checker', 'Both'])) {
                    $roleType = 'Maker';
                }
                $this->insert('user_sub_activities', [
                    'user_id' => $userId,
                    'sub_activity_id' => $subActId,
                    'role_type' => $roleType,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $selectedSkills[$subActId] = $roleType;
            }
        }

        // Bi-directional Sync: Update sub_activities table defaults when skills change
        foreach ($asDefaultMaker as $sa) {
            $saId = $sa['id'];
            $role = $selectedSkills[$saId] ?? null;
            if (!$role || !in_array($role, ['Maker', 'Both'])) {
                $otherMaker = $this->fetchOne("SELECT user_id FROM user_sub_activities WHERE sub_activity_id = ? AND role_type IN ('Maker', 'Both') AND user_id != ? LIMIT 1", [$saId, $userId]);
                $this->update('sub_activities', ['default_user_id' => $otherMaker['user_id'] ?? null], "id = ?", [$saId]);
            }
        }

        foreach ($asDefaultChecker as $sa) {
            $saId = $sa['id'];
            $role = $selectedSkills[$saId] ?? null;
            if (!$role || !in_array($role, ['Checker', 'Both'])) {
                $otherChecker = $this->fetchOne("SELECT user_id FROM user_sub_activities WHERE sub_activity_id = ? AND role_type IN ('Checker', 'Both') AND user_id != ? LIMIT 1", [$saId, $userId]);
                $this->update('sub_activities', ['default_checker_id' => $otherChecker['user_id'] ?? null], "id = ?", [$saId]);
            }
        }

        foreach ($selectedSkills as $saId => $role) {
            $subAct = $this->fetchOne("SELECT id, default_user_id, default_checker_id FROM sub_activities WHERE id = ?", [$saId]);
            if ($subAct) {
                $upd = [];
                if (empty($subAct['default_user_id']) && in_array($role, ['Maker', 'Both'])) {
                    $upd['default_user_id'] = $userId;
                }
                if (empty($subAct['default_checker_id']) && in_array($role, ['Checker', 'Both'])) {
                    $upd['default_checker_id'] = $userId;
                }
                if (!empty($upd)) {
                    $this->update('sub_activities', $upd, "id = ?", [$saId]);
                }
            }
        }
    }

    public function getCheckersForSubActivity(int $subActivityId): array {
        return $this->fetchAll("
            SELECT u.* 
            FROM users u 
            JOIN user_sub_activities usa ON u.id = usa.user_id 
            WHERE usa.sub_activity_id = ? AND usa.role_type IN ('Checker', 'Both') AND u.status = 'Active' 
            ORDER BY u.full_name ASC
        ", [$subActivityId]);
    }

    public function getMakersForSubActivity(int $subActivityId): array {
        return $this->fetchAll("
            SELECT u.* 
            FROM users u 
            JOIN user_sub_activities usa ON u.id = usa.user_id 
            WHERE usa.sub_activity_id = ? AND usa.role_type IN ('Maker', 'Both') AND u.status = 'Active' 
            ORDER BY u.full_name ASC
        ", [$subActivityId]);
    }

    public function getUserSkillsDetailed(int $userId): array {
        return $this->fetchAll("
            SELECT usa.*, 
                   sa.sub_activity_name, 
                   a.activity_name, 
                   d.division_name,
                   utp.is_learned, 
                   utp.acknowledged_at
            FROM user_sub_activities usa 
            JOIN sub_activities sa ON usa.sub_activity_id = sa.id 
            JOIN activities a ON sa.activity_id = a.id 
            LEFT JOIN divisions d ON a.division_id = d.id 
            LEFT JOIN user_training_progress utp ON utp.user_id = usa.user_id AND utp.sub_activity_id = sa.id
            WHERE usa.user_id = ? 
            ORDER BY d.division_name ASC, a.activity_name ASC, sa.sub_activity_name ASC
        ", [$userId]);
    }

    public function getUserStats(int $userId): array {
        $makerCount = 0;
        $checkerCount = 0;
        $creatorCount = 0;
        $deliveredCount = 0;
        $bucketCount = 0;
        $ktCount = 0;
        $certCount = 0;

        try {
            $makerCount = (int)($this->fetchOne("SELECT COUNT(*) as cnt FROM tickets WHERE allocated_to = ?", [$userId])['cnt'] ?? 0);
            $checkerCount = (int)($this->fetchOne("SELECT COUNT(*) as cnt FROM tickets WHERE checker_id = ?", [$userId])['cnt'] ?? 0);
            $creatorCount = (int)($this->fetchOne("SELECT COUNT(*) as cnt FROM tickets WHERE created_by = ?", [$userId])['cnt'] ?? 0);
            $deliveredCount = (int)($this->fetchOne("SELECT COUNT(*) as cnt FROM tickets WHERE (allocated_to = ? OR checker_id = ?) AND (status IN ('Completed', 'Closed') OR stage = 'Delivered')", [$userId, $userId])['cnt'] ?? 0);
            $bucketCount = (int)($this->fetchOne("SELECT COUNT(*) as cnt FROM tickets WHERE (allocated_to = ? AND stage = 'Maker Queue') OR (checker_id = ? AND stage = 'Checker Queue')", [$userId, $userId])['cnt'] ?? 0);
        } catch (Exception $e) {}

        try {
            $ktCount = (int)($this->fetchOne("SELECT COUNT(*) as cnt FROM user_training_progress WHERE user_id = ? AND (is_learned = 1 OR acknowledged_at IS NOT NULL)", [$userId])['cnt'] ?? 0);
        } catch (Exception $e) {}

        try {
            $certCount = (int)($this->fetchOne("SELECT COUNT(*) as cnt FROM pkt_test_attempts WHERE user_id = ? AND passed = 1", [$userId])['cnt'] ?? 0);
        } catch (Exception $e) {}
        
        return [
            'maker_count' => $makerCount,
            'checker_count' => $checkerCount,
            'creator_count' => $creatorCount,
            'delivered_count' => $deliveredCount,
            'bucket_count' => $bucketCount,
            'kt_count' => $ktCount,
            'cert_count' => $certCount,
            'total_handled' => ($makerCount + $checkerCount)
        ];
    }

    public function getUserAuditTrail(int $userId, int $limit = 20): array {
        return $this->fetchAll("
            SELECT * 
            FROM audit_logs 
            WHERE user_id = ? 
            ORDER BY id DESC 
            LIMIT ?
        ", [$userId, $limit]);
    }

    public function getUserByCodeOrEmail(string $val): ?array {
        $clean = strtolower(trim($val));
        if (empty($clean)) return null;
        return $this->fetchOne("SELECT * FROM users WHERE LOWER(user_code) = ? OR LOWER(email) = ? LIMIT 1", [$clean, $clean]);
    }
}

