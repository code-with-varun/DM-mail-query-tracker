<?php
/**
 * Activity & Sub-Activity Master Model
 */

class Activity_model extends Model {
    public function getActivities(): array {
        return $this->fetchAll("
            SELECT a.*, d.division_name, d.code as division_code 
            FROM activities a 
            LEFT JOIN divisions d ON a.division_id = d.id 
            WHERE a.status = 'Active' 
            ORDER BY a.activity_name ASC
        ");
    }

    public function getSubActivitiesByActivity(int $activityId): array {
        return $this->fetchAll("
            SELECT sa.*, d.division_name, d.code as division_code, u.full_name as default_user_name, u.user_code as default_user_code 
            FROM sub_activities sa 
            LEFT JOIN divisions d ON sa.division_id = d.id 
            LEFT JOIN users u ON sa.default_user_id = u.id 
            WHERE sa.activity_id = ? AND sa.status = 'Active' 
            ORDER BY sa.sub_activity_name ASC
        ", [$activityId]);
    }

    public function getAllSubActivitiesWithHierarchy(): array {
        return $this->fetchAll("
            SELECT sa.*, a.activity_name, d.division_name, d.code as division_code, u.full_name as default_user_name, u.user_code as default_user_code
            FROM sub_activities sa
            JOIN activities a ON sa.activity_id = a.id
            LEFT JOIN divisions d ON COALESCE(sa.division_id, a.division_id) = d.id
            LEFT JOIN users u ON sa.default_user_id = u.id
            WHERE sa.status = 'Active'
            ORDER BY a.activity_name ASC, sa.sub_activity_name ASC
        ");
    }

    public function getSubActivityDetailByName(string $subActivityName): ?array {
        return $this->fetchOne("
            SELECT sa.*, a.activity_name, d.id as division_id, d.division_name, d.code as division_code, u.id as default_user_id, u.full_name as default_user_name, u.user_code as default_user_code
            FROM sub_activities sa
            JOIN activities a ON sa.activity_id = a.id
            LEFT JOIN divisions d ON COALESCE(sa.division_id, a.division_id) = d.id
            LEFT JOIN users u ON sa.default_user_id = u.id
            WHERE sa.sub_activity_name = ? AND sa.status = 'Active'
            LIMIT 1
        ", [trim($subActivityName)]);
    }

    public function getDivisions(): array {
        return $this->fetchAll("SELECT * FROM divisions WHERE status = 'Active' ORDER BY division_name ASC");
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
                SELECT sa.*, d.division_name, d.code as division_code, u.full_name as default_user_name, u.user_code as default_user_code 
                FROM sub_activities sa 
                LEFT JOIN divisions d ON sa.division_id = d.id 
                LEFT JOIN users u ON sa.default_user_id = u.id 
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

    public function createSubActivity(int $activityId, string $name, int $tatHours, ?int $divisionId = null, ?int $defaultUserId = null): int {
        return $this->insert('sub_activities', [
            'activity_id' => $activityId,
            'sub_activity_name' => $name,
            'division_id' => $divisionId,
            'default_tat_hours' => $tatHours,
            'default_user_id' => $defaultUserId,
            'status' => 'Active',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function createDivision(string $name, string $code): int {
        return $this->insert('divisions', [
            'division_name' => $name,
            'code' => strtoupper($code),
            'status' => 'Active',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
