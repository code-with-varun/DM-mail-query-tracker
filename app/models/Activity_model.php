<?php
/**
 * Activity & Sub-Activity Master Model
 */

class Activity_model extends Model {
    public function getActivities(): array {
        return $this->fetchAll("SELECT * FROM activities WHERE status = 'Active' ORDER BY activity_name ASC");
    }

    public function getSubActivitiesByActivity(int $activityId): array {
        return $this->fetchAll("SELECT * FROM sub_activities WHERE activity_id = ? AND status = 'Active' ORDER BY sub_activity_name ASC", [$activityId]);
    }

    public function getDivisions(): array {
        return $this->fetchAll("SELECT * FROM divisions WHERE status = 'Active' ORDER BY division_name ASC");
    }

    public function getAllActivitiesWithSub(): array {
        $activities = $this->fetchAll("SELECT * FROM activities ORDER BY id DESC");
        foreach ($activities as &$act) {
            $act['sub_activities'] = $this->fetchAll("SELECT * FROM sub_activities WHERE activity_id = ?", [$act['id']]);
        }
        return $activities;
    }

    public function createActivity(string $name, string $desc): int {
        return $this->insert('activities', [
            'activity_name' => $name,
            'description' => $desc,
            'status' => 'Active',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function createSubActivity(int $activityId, string $name, int $tatHours): int {
        return $this->insert('sub_activities', [
            'activity_id' => $activityId,
            'sub_activity_name' => $name,
            'default_tat_hours' => $tatHours,
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
