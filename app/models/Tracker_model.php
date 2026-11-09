<?php
/**
 * Input & Output/Delivery Tracker Model
 */

class Tracker_model extends Model {
    public function getInputLogs(): array {
        $sql = "SELECT it.*, u.full_name as assigned_user_name, c.full_name as creator_name
                FROM input_tracker it
                LEFT JOIN users u ON it.assigned_to = u.id
                LEFT JOIN users c ON it.created_by = c.id
                ORDER BY it.id DESC";
        return $this->fetchAll($sql);
    }

    public function createInputLog(array $data): int {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert('input_tracker', $data);
    }

    public function getDeliveryLogs(): array {
        $sql = "SELECT dt.*, t.ticket_number, c.full_name as creator_name
                FROM delivery_tracker dt
                LEFT JOIN tickets t ON dt.ticket_id = t.id
                LEFT JOIN users c ON dt.created_by = c.id
                ORDER BY dt.id DESC";
        return $this->fetchAll($sql);
    }

    public function generateDeliveryNumber(): string {
        $year = date('Y');
        $prefix = "DEL-{$year}-";
        $last = $this->fetchOne("SELECT delivery_number FROM delivery_tracker WHERE delivery_number LIKE ? ORDER BY id DESC LIMIT 1", ["{$prefix}%"]);
        if ($last) {
            $lastNum = (int) substr($last['delivery_number'], -6);
            $nextNum = str_pad((string)($lastNum + 1), 6, '0', STR_PAD_LEFT);
        } else {
            $nextNum = "000001";
        }
        return $prefix . $nextNum;
    }

    public function createDeliveryLog(array $data): int {
        if (empty($data['delivery_number'])) {
            $data['delivery_number'] = $this->generateDeliveryNumber();
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert('delivery_tracker', $data);
    }

    public function __construct() {
        parent::__construct();
        $this->ensureTimesheetTable();
    }

    public function ensureTimesheetTable(): void {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `timesheet_logs` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `user_id` INT NOT NULL,
                  `project_id` INT NULL,
                  `activity_id` INT NULL,
                  `sub_activity_id` INT NULL,
                  `log_date` DATE NOT NULL,
                  `hours_spent` DECIMAL(4,2) NOT NULL DEFAULT 8.00,
                  `task_description` TEXT NOT NULL,
                  `status` ENUM('Submitted', 'Approved', 'Rejected') DEFAULT 'Submitted',
                  `approved_by` INT NULL,
                  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        } catch (Exception $e) {}
    }

    public function getTimesheetLogs(array $filters = []): array {
        $sql = "SELECT ts.*, u.full_name, u.user_code, p.project_name, a.activity_name, sa.sub_activity_name
                FROM timesheet_logs ts
                JOIN users u ON ts.user_id = u.id
                LEFT JOIN projects p ON ts.project_id = p.id
                LEFT JOIN activities a ON ts.activity_id = a.id
                LEFT JOIN sub_activities sa ON ts.sub_activity_id = sa.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= " AND ts.user_id = ?";
            $params[] = (int)$filters['user_id'];
        }
        if (!empty($filters['start_date'])) {
            $sql .= " AND ts.log_date >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND ts.log_date <= ?";
            $params[] = $filters['end_date'];
        }
        if (!empty($filters['project_id'])) {
            $sql .= " AND ts.project_id = ?";
            $params[] = (int)$filters['project_id'];
        }

        $sql .= " ORDER BY ts.log_date DESC, ts.id DESC";
        return $this->fetchAll($sql, $params);
    }

    public function createTimesheetLog(array $data): int {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert('timesheet_logs', $data);
    }

    public function deleteTimesheetLog(int $id): bool {
        return $this->delete('timesheet_logs', "id = ?", [$id]);
    }
}
