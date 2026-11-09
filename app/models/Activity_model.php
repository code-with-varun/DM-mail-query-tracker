<?php
/**
 * Activity & Sub-Activity Master Model
 */

class Activity_model extends Model {
    public function __construct() {
        parent::__construct();
        $this->ensureDefaultCheckerColumn();
        $this->ensureEnabledQueuesColumn();
        $this->ensurePointWeightColumn();
        $this->ensureDivisionTeamLeaderColumn();
    }

    public function ensureDivisionTeamLeaderColumn(): void {
        try {
            $cols = $this->fetchAll("SHOW COLUMNS FROM divisions LIKE 'team_leader_id'");
            if (empty($cols)) {
                $this->db->exec("ALTER TABLE `divisions` ADD COLUMN `team_leader_id` INT NULL AFTER `project_id`");
            }
        } catch (Exception $e) {}
    }

    public function ensureDefaultCheckerColumn(): void {
        try {
            $cols = $this->fetchAll("SHOW COLUMNS FROM sub_activities LIKE 'default_checker_id'");
            if (empty($cols)) {
                $this->db->exec("ALTER TABLE `sub_activities` ADD COLUMN `default_checker_id` INT NULL AFTER `default_user_id`");
            }
        } catch (Exception $e) {}
    }

    public function ensureEnabledQueuesColumn(): void {
        try {
            $cols = $this->fetchAll("SHOW COLUMNS FROM sub_activities LIKE 'enabled_queues'");
            if (empty($cols)) {
                $this->db->exec("ALTER TABLE `sub_activities` ADD COLUMN `enabled_queues` TEXT NULL AFTER `default_occurrence_day`");
            }
        } catch (Exception $e) {}
    }

    public function ensurePointWeightColumn(): void {
        try {
            $cols = $this->fetchAll("SHOW COLUMNS FROM sub_activities LIKE 'point_weight'");
            if (empty($cols)) {
                $this->db->exec("ALTER TABLE `sub_activities` ADD COLUMN `point_weight` DECIMAL(6,2) DEFAULT 1.00 AFTER `default_tat_hours`");
            }
        } catch (Exception $e) {}
    }

    public function getActivities(): array {
        return $this->fetchAll("
            SELECT a.*, d.division_name, d.code as division_code 
            FROM activities a 
            LEFT JOIN divisions d ON a.division_id = d.id 
            WHERE a.status = 'Active' 
            ORDER BY a.activity_name ASC
        ");
    }

    public function getActivitiesByDivision(?int $divisionId = null): array {
        if ($divisionId && $divisionId > 0) {
            return $this->fetchAll("
                SELECT a.*, d.division_name, d.code as division_code 
                FROM activities a 
                LEFT JOIN divisions d ON a.division_id = d.id 
                WHERE (a.division_id = ? OR a.division_id IS NULL) AND a.status = 'Active' 
                ORDER BY a.activity_name ASC
            ", [$divisionId]);
        }
        return $this->getActivities();
    }

    public function getSubActivitiesByActivity(int $activityId): array {
        return $this->fetchAll("
            SELECT sa.*, d.division_name, d.code as division_code, 
                   u.full_name as default_user_name, u.user_code as default_user_code,
                   uc.full_name as default_checker_name, uc.user_code as default_checker_code 
            FROM sub_activities sa 
            LEFT JOIN divisions d ON sa.division_id = d.id 
            LEFT JOIN users u ON sa.default_user_id = u.id 
            LEFT JOIN users uc ON sa.default_checker_id = uc.id 
            WHERE sa.activity_id = ? AND sa.status = 'Active' 
            ORDER BY sa.sub_activity_name ASC
        ", [$activityId]);
    }

    public function getAllSubActivitiesWithHierarchy(): array {
        $subActs = $this->fetchAll("
            SELECT sa.*, w.workflow_name, a.activity_name, d.division_name, d.code as division_code, 
                   u.full_name as default_user_name, u.user_code as default_user_code,
                   uc.full_name as default_checker_name, uc.user_code as default_checker_code
            FROM sub_activities sa
            JOIN activities a ON sa.activity_id = a.id
            LEFT JOIN workflows w ON sa.workflow_id = w.id
            LEFT JOIN divisions d ON COALESCE(sa.division_id, a.division_id) = d.id
            LEFT JOIN users u ON sa.default_user_id = u.id
            LEFT JOIN users uc ON sa.default_checker_id = uc.id
            WHERE sa.status = 'Active'
            ORDER BY a.activity_name ASC, sa.sub_activity_name ASC
        ");

        $allSkills = $this->fetchAll("
            SELECT usa.sub_activity_id, usa.user_id, usa.role_type, u.full_name, u.user_code
            FROM user_sub_activities usa
            JOIN users u ON usa.user_id = u.id
            WHERE u.status = 'Active'
            ORDER BY u.full_name ASC
        ");

        $makersMap = [];
        $checkersMap = [];
        foreach ($allSkills as $sk) {
            $saId = $sk['sub_activity_id'];
            if (in_array($sk['role_type'], ['Maker', 'Both'])) {
                $makersMap[$saId][] = $sk;
            }
            if (in_array($sk['role_type'], ['Checker', 'Both'])) {
                $checkersMap[$saId][] = $sk;
            }
        }

        foreach ($subActs as &$sa) {
            $sa['mapped_makers'] = $makersMap[$sa['id']] ?? [];
            $sa['mapped_checkers'] = $checkersMap[$sa['id']] ?? [];
        }

        return $subActs;
    }

    public function getSubActivityDetailByName(string $subActivityName): ?array {
        return $this->fetchOne("
            SELECT sa.*, a.activity_name, d.id as division_id, d.division_name, d.code as division_code, 
                   u.id as default_user_id, u.full_name as default_user_name, u.user_code as default_user_code,
                   uc.id as default_checker_id, uc.full_name as default_checker_name, uc.user_code as default_checker_code
            FROM sub_activities sa
            JOIN activities a ON sa.activity_id = a.id
            LEFT JOIN divisions d ON COALESCE(sa.division_id, a.division_id) = d.id
            LEFT JOIN users u ON sa.default_user_id = u.id
            LEFT JOIN users uc ON sa.default_checker_id = uc.id
            WHERE sa.sub_activity_name = ? AND sa.status = 'Active'
            LIMIT 1
        ", [trim($subActivityName)]);
    }

    public function getDivisions(): array {
        return $this->fetchAll("
            SELECT d.*, p.project_name, p.code as project_code,
                   u.full_name as tl_name, u.user_code as tl_code
            FROM divisions d 
            LEFT JOIN projects p ON d.project_id = p.id 
            LEFT JOIN users u ON d.team_leader_id = u.id
            WHERE d.status = 'Active' 
            ORDER BY d.id DESC
        ");
    }

    public function getAllActivitiesWithSub(): array {
        $activities = $this->fetchAll("
            SELECT a.*, d.division_name, d.code as division_code 
            FROM activities a 
            LEFT JOIN divisions d ON a.division_id = d.id 
            ORDER BY a.id DESC
        ");
        foreach ($activities as &$act) {
            $act['sub_activities'] = $this->fetchAll("
                SELECT sa.*, d.division_name, d.code as division_code, 
                       u.full_name as default_user_name, u.user_code as default_user_code,
                       uc.full_name as default_checker_name, uc.user_code as default_checker_code 
                FROM sub_activities sa 
                LEFT JOIN divisions d ON sa.division_id = d.id 
                LEFT JOIN users u ON sa.default_user_id = u.id 
                LEFT JOIN users uc ON sa.default_checker_id = uc.id 
                WHERE sa.activity_id = ? 
                ORDER BY sa.id ASC
            ", [$act['id']]);
        }
        return $activities;
    }

    public function createActivity(string $name, string $desc, ?int $divisionId = null): int {
        return $this->insert('activities', [
            'activity_name' => $name,
            'description' => $desc,
            'division_id' => $divisionId,
            'status' => 'Active',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function createSubActivity(int $activityId, string $name, int $tatHours, ?int $divisionId = null, ?int $defaultUserId = null, ?int $occurrenceDay = null, ?int $defaultCheckerId = null, ?string $enabledQueues = null, ?int $workflowId = null): int {
        $id = $this->insert('sub_activities', [
            'activity_id' => $activityId,
            'sub_activity_name' => $name,
            'division_id' => $divisionId,
            'default_tat_hours' => $tatHours,
            'default_user_id' => $defaultUserId,
            'default_checker_id' => $defaultCheckerId,
            'default_occurrence_day' => $occurrenceDay,
            'enabled_queues' => $enabledQueues,
            'workflow_id' => $workflowId,
            'status' => 'Active',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $this->syncSubActivityToSkillMatrix($id, $defaultUserId, $defaultCheckerId);
        return $id;
    }

    public function updateSubActivity(int $id, int $activityId, string $name, int $tatHours, ?int $divisionId = null, ?int $defaultUserId = null, ?int $occurrenceDay = null, ?int $defaultCheckerId = null, ?string $enabledQueues = null, ?int $workflowId = null): bool {
        $res = $this->update('sub_activities', [
            'activity_id' => $activityId,
            'sub_activity_name' => $name,
            'division_id' => $divisionId,
            'default_tat_hours' => $tatHours,
            'default_user_id' => $defaultUserId,
            'default_checker_id' => $defaultCheckerId,
            'default_occurrence_day' => $occurrenceDay,
            'enabled_queues' => $enabledQueues,
            'workflow_id' => $workflowId
        ], "id = ?", [$id]);
        $this->syncSubActivityToSkillMatrix($id, $defaultUserId, $defaultCheckerId);
        return $res;
    }

    public function syncSubActivityToSkillMatrix(int $subActId, ?int $makerId = null, ?int $checkerId = null): void {
        if ($makerId && $makerId > 0) {
            $existing = $this->fetchOne("SELECT id, role_type FROM user_sub_activities WHERE user_id = ? AND sub_activity_id = ?", [$makerId, $subActId]);
            if ($existing) {
                if ($existing['role_type'] === 'Checker' && $makerId == $checkerId) {
                    $this->update('user_sub_activities', ['role_type' => 'Both'], "id = ?", [$existing['id']]);
                }
            } else {
                $role = ($makerId == $checkerId) ? 'Both' : 'Maker';
                $this->insert('user_sub_activities', [
                    'user_id' => $makerId,
                    'sub_activity_id' => $subActId,
                    'role_type' => $role,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        if ($checkerId && $checkerId > 0 && $checkerId != $makerId) {
            $existing = $this->fetchOne("SELECT id, role_type FROM user_sub_activities WHERE user_id = ? AND sub_activity_id = ?", [$checkerId, $subActId]);
            if ($existing) {
                if ($existing['role_type'] === 'Maker') {
                    $this->update('user_sub_activities', ['role_type' => 'Both'], "id = ?", [$existing['id']]);
                }
            } else {
                $this->insert('user_sub_activities', [
                    'user_id' => $checkerId,
                    'sub_activity_id' => $subActId,
                    'role_type' => 'Checker',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
    }

    public function createDivision(string $name, string $code, ?int $projectId = null, ?int $teamLeaderId = null): int {
        return $this->insert('divisions', [
            'division_name' => $name,
            'code' => strtoupper($code),
            'project_id' => $projectId,
            'team_leader_id' => $teamLeaderId,
            'status' => 'Active',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getDivisionByCodeOrName(string $val): ?array {
        $clean = trim($val);
        if (empty($clean)) return null;
        return $this->fetchOne("SELECT * FROM divisions WHERE UPPER(code) = ? OR LOWER(division_name) = ? LIMIT 1", [strtoupper($clean), strtolower($clean)]);
    }

    public function getActivityByName(string $name): ?array {
        $clean = trim($name);
        if (empty($clean)) return null;
        return $this->fetchOne("SELECT * FROM activities WHERE LOWER(activity_name) = ? LIMIT 1", [strtolower($clean)]);
    }

    public function getSubActivityByName(string $name): ?array {
        $clean = trim($name);
        if (empty($clean)) return null;
        return $this->fetchOne("SELECT * FROM sub_activities WHERE LOWER(sub_activity_name) = ? LIMIT 1", [strtolower($clean)]);
    }
}
