<?php
/**
 * Process Lifecycle Tracker Model
 */

class Lifecycle_model extends Model {

    public function __construct() {
        parent::__construct();
        $this->ensureTablesExist();
    }

    private function ensureTablesExist(): void {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `ticket_lifecycle_tracker` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `ticket_id` INT NULL,
                  `billing_month` VARCHAR(20) NOT NULL,
                  `ticket_number` VARCHAR(50) NOT NULL,
                  `ticket_type` ENUM('File Preparation', 'Rerun', 'Query Ticket', 'Task Ticket') DEFAULT 'File Preparation',
                  `division_id` INT NULL,
                  `activity_id` INT NOT NULL,
                  `sub_activity_id` INT NOT NULL,
                  `division_name` VARCHAR(100) NULL,
                  `activity_name` VARCHAR(100) NULL,
                  `sub_activity_name` VARCHAR(100) NULL,
                  `maker_id` INT NULL,
                  `checker_id` INT NULL,
                  `current_stage` ENUM(
                    'Input Received',
                    'Maker Queue',
                    'Checker Queue',
                    'Error Log',
                    'Maker Checker Rework',
                    'Delivery',
                    'Acknowledgement',
                    'Approval Queue',
                    'Uploader Queue',
                    'Dump Sharing',
                    'Consol Vendor Tracker',
                    'Closed'
                  ) DEFAULT 'Input Received',
                  
                  `input_received_at` DATETIME NULL,
                  `input_received_attachment` VARCHAR(255) NULL,
                  
                  `maker_queue_at` DATETIME NULL,
                  `maker_completed_at` DATETIME NULL,
                  `maker_attachment` VARCHAR(255) NULL,
                  
                  `checker_queue_at` DATETIME NULL,
                  `checker_completed_at` DATETIME NULL,
                  `checker_attachment` VARCHAR(255) NULL,
                  
                  `error_log_at` DATETIME NULL,
                  `error_description` TEXT NULL,
                  
                  `rework_loop_count` INT DEFAULT 0,
                  `rework_last_at` DATETIME NULL,
                  
                  `delivery_at` DATETIME NULL,
                  `delivery_attachment` VARCHAR(255) NULL,
                  
                  `acknowledgement_at` DATETIME NULL,
                  `acknowledgement_attachment` VARCHAR(255) NULL,
                  
                  `approval_queue_at` DATETIME NULL,
                  `approved_at` DATETIME NULL,
                  `approval_attachment` VARCHAR(255) NULL,
                  
                  `uploader_queue_at` DATETIME NULL,
                  `uploaded_at` DATETIME NULL,
                  `uploader_attachment` VARCHAR(255) NULL,
                  
                  `dump_sharing_at` DATETIME NULL,
                  `dump_attachment` VARCHAR(255) NULL,
                  
                  `consol_vendor_at` DATETIME NULL,
                  `consol_vendor_remarks` TEXT NULL,
                  
                  `closed_at` DATETIME NULL,
                  `closed_by` INT NULL,
                  `remarks` TEXT NULL,
                  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                  UNIQUE KEY `idx_subact_month` (`sub_activity_id`, `billing_month`, `ticket_number`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `ticket_lifecycle_history` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `lifecycle_id` INT NOT NULL,
                  `ticket_id` INT NULL,
                  `stage_name` VARCHAR(100) NOT NULL,
                  `performed_by` INT NOT NULL,
                  `performed_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                  `attachment_path` VARCHAR(255) NULL,
                  `attachment_name` VARCHAR(255) NULL,
                  `remarks` TEXT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        } catch (Exception $e) {
            // Table exists
        }
    }

    public function generateItemNumber(string $prefix = 'LFC'): string {
        $year = date('Y');
        $code = "{$prefix}-{$year}-";
        $last = $this->fetchOne("SELECT ticket_number FROM ticket_lifecycle_tracker WHERE ticket_number LIKE ? ORDER BY id DESC LIMIT 1", ["{$code}%"]);
        if ($last) {
            $lastNum = (int)substr($last['ticket_number'], -5);
            $nextNum = str_pad((string)($lastNum + 1), 5, '0', STR_PAD_LEFT);
        } else {
            $nextNum = "00001";
        }
        return $code . $nextNum;
    }

    public function getLifecycleItems(string $billingMonth, array $filters = []): array {
        $sql = "SELECT l.*, 
                       u_m.full_name as maker_name, u_m.user_code as maker_code,
                       u_c.full_name as checker_name, u_c.user_code as checker_code,
                       u_cls.full_name as closed_by_name
                FROM ticket_lifecycle_tracker l
                LEFT JOIN users u_m ON l.maker_id = u_m.id
                LEFT JOIN users u_c ON l.checker_id = u_c.id
                LEFT JOIN users u_cls ON l.closed_by = u_cls.id
                WHERE l.billing_month = ?";
        $params = [$billingMonth];

        if (!empty($filters['ticket_type'])) {
            $sql .= " AND l.ticket_type = ?";
            $params[] = $filters['ticket_type'];
        }
        if (!empty($filters['stage'])) {
            $sql .= " AND l.current_stage = ?";
            $params[] = $filters['stage'];
        }
        if (!empty($filters['sub_activity_id'])) {
            $sql .= " AND l.sub_activity_id = ?";
            $params[] = $filters['sub_activity_id'];
        }
        if (!empty($filters['search'])) {
            $searchTerm = "%" . $filters['search'] . "%";
            $sql .= " AND (l.sub_activity_name LIKE ? OR l.activity_name LIKE ? OR l.ticket_number LIKE ? OR u_m.full_name LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY l.id DESC";
        return $this->fetchAll($sql, $params);
    }

    public function getById(int $id): ?array {
        $sql = "SELECT l.*, 
                       u_m.full_name as maker_name, u_m.user_code as maker_code,
                       u_c.full_name as checker_name, u_c.user_code as checker_code,
                       u_cls.full_name as closed_by_name
                FROM ticket_lifecycle_tracker l
                LEFT JOIN users u_m ON l.maker_id = u_m.id
                LEFT JOIN users u_c ON l.checker_id = u_c.id
                LEFT JOIN users u_cls ON l.closed_by = u_cls.id
                WHERE l.id = ? LIMIT 1";
        return $this->fetchOne($sql, [$id]);
    }

    public function getHistory(int $lifecycleId): array {
        $sql = "SELECT h.*, u.full_name as user_name, u.role_id 
                FROM ticket_lifecycle_history h
                JOIN users u ON h.performed_by = u.id
                WHERE h.lifecycle_id = ?
                ORDER BY h.id DESC";
        return $this->fetchAll($sql, [$lifecycleId]);
    }

    /**
     * Copy-paste list of active subtasks & Maker-Checker details into Lifecycle Tracker for a selected billing month
     */
    public function populateFromActiveSubtasks(string $billingMonth, int $createdBy): int {
        $sql = "SELECT sa.id as sub_activity_id, sa.sub_activity_name, sa.default_user_id,
                       a.id as activity_id, a.activity_name,
                       d.id as division_id, d.division_name
                FROM sub_activities sa
                JOIN activities a ON sa.activity_id = a.id
                LEFT JOIN divisions d ON sa.division_id = d.id
                WHERE sa.status = 'Active'
                ORDER BY d.division_name ASC, a.activity_name ASC, sa.sub_activity_name ASC";
        
        $subActs = $this->fetchAll($sql);
        $count = 0;

        foreach ($subActs as $sa) {
            $subId = $sa['sub_activity_id'];
            $existing = $this->fetchOne("SELECT id FROM ticket_lifecycle_tracker WHERE sub_activity_id = ? AND billing_month = ?", [$subId, $billingMonth]);
            if ($existing) continue;

            // Lookup assigned Maker & Checker for this sub_activity
            $maker = $this->fetchOne("SELECT user_id FROM user_sub_activities WHERE sub_activity_id = ? AND role_type IN ('Maker', 'Both') LIMIT 1", [$subId]);
            $checker = $this->fetchOne("SELECT user_id FROM user_sub_activities WHERE sub_activity_id = ? AND role_type IN ('Checker', 'Both') LIMIT 1", [$subId]);

            $makerId = $maker['user_id'] ?? ($sa['default_user_id'] ?? null);
            $checkerId = $checker['user_id'] ?? null;

            $itemNumber = $this->generateItemNumber('LFC');

            $this->insert('ticket_lifecycle_tracker', [
                'billing_month' => $billingMonth,
                'ticket_number' => $itemNumber,
                'ticket_type' => 'File Preparation',
                'division_id' => $sa['division_id'],
                'activity_id' => $sa['activity_id'],
                'sub_activity_id' => $subId,
                'division_name' => $sa['division_name'] ?? 'General Operations',
                'activity_name' => $sa['activity_name'],
                'sub_activity_name' => $sa['sub_activity_name'],
                'maker_id' => $makerId,
                'checker_id' => $checkerId,
                'current_stage' => 'Input Received',
                'input_received_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $count++;
        }

        return $count;
    }

    public function createLifecycleItem(array $data): int {
        if (empty($data['ticket_number'])) {
            $data['ticket_number'] = $this->generateItemNumber('LFC');
        }
        $data['input_received_at'] = date('Y-m-d H:i:s');
        $data['created_at'] = date('Y-m-d H:i:s');
        
        $id = $this->insert('ticket_lifecycle_tracker', $data);

        $this->insert('ticket_lifecycle_history', [
            'lifecycle_id' => $id,
            'ticket_id' => $data['ticket_id'] ?? null,
            'stage_name' => 'Input Received',
            'performed_by' => $data['created_by'] ?? 1,
            'performed_at' => date('Y-m-d H:i:s'),
            'remarks' => 'Lifecycle item logged'
        ]);

        return $id;
    }

    public function updateStage(int $id, string $newStage, array $extraData, int $userId): bool {
        $item = $this->getById($id);
        if (!$item) return false;

        $update = ['current_stage' => $newStage];
        $now = date('Y-m-d H:i:s');

        // Stage specific field mapping
        switch ($newStage) {
            case 'Input Received':
                $update['input_received_at'] = $now;
                if (!empty($extraData['attachment'])) $update['input_received_attachment'] = $extraData['attachment'];
                break;
            case 'Maker Queue':
                $update['maker_queue_at'] = $now;
                if (!empty($extraData['attachment'])) $update['maker_attachment'] = $extraData['attachment'];
                break;
            case 'Checker Queue':
                $update['maker_completed_at'] = $now;
                $update['checker_queue_at'] = $now;
                if (!empty($extraData['attachment'])) $update['checker_attachment'] = $extraData['attachment'];
                break;
            case 'Error Log':
                $update['error_log_at'] = $now;
                if (!empty($extraData['error_description'])) $update['error_description'] = sanitize($extraData['error_description']);
                break;
            case 'Maker Checker Rework':
                $update['rework_loop_count'] = ((int)($item['rework_loop_count'] ?? 0)) + 1;
                $update['rework_last_at'] = $now;
                break;
            case 'Delivery':
                $update['checker_completed_at'] = $now;
                $update['delivery_at'] = $now;
                if (!empty($extraData['attachment'])) $update['delivery_attachment'] = $extraData['attachment'];
                break;
            case 'Acknowledgement':
                $update['acknowledgement_at'] = $now;
                if (!empty($extraData['attachment'])) $update['acknowledgement_attachment'] = $extraData['attachment'];
                break;
            case 'Approval Queue':
                $update['approval_queue_at'] = $now;
                if (!empty($extraData['attachment'])) $update['approval_attachment'] = $extraData['attachment'];
                break;
            case 'Uploader Queue':
                $update['approved_at'] = $now;
                $update['uploader_queue_at'] = $now;
                if (!empty($extraData['attachment'])) $update['uploader_attachment'] = $extraData['attachment'];
                break;
            case 'Dump Sharing':
                $update['uploaded_at'] = $now;
                $update['dump_sharing_at'] = $now;
                if (!empty($extraData['attachment'])) $update['dump_attachment'] = $extraData['attachment'];
                break;
            case 'Consol Vendor Tracker':
                $update['consol_vendor_at'] = $now;
                if (!empty($extraData['remarks'])) $update['consol_vendor_remarks'] = sanitize($extraData['remarks']);
                break;
            case 'Closed':
                $update['closed_at'] = $now;
                $update['closed_by'] = $userId;
                break;
        }

        if (!empty($extraData['remarks'])) {
            $update['remarks'] = sanitize($extraData['remarks']);
        }

        $this->update('ticket_lifecycle_tracker', $update, "id = ?", [$id]);

        $this->insert('ticket_lifecycle_history', [
            'lifecycle_id' => $id,
            'ticket_id' => $item['ticket_id'],
            'stage_name' => $newStage,
            'performed_by' => $userId,
            'performed_at' => $now,
            'attachment_path' => $extraData['attachment'] ?? null,
            'attachment_name' => $extraData['attachment_name'] ?? null,
            'remarks' => sanitize($extraData['remarks'] ?? "Moved to {$newStage}")
        ]);

        return true;
    }

    public function updateUsers(int $id, ?int $makerId, ?int $checkerId): bool {
        $update = [];
        if ($makerId !== null) $update['maker_id'] = $makerId;
        if ($checkerId !== null) $update['checker_id'] = $checkerId;
        if (empty($update)) return false;

        return $this->update('ticket_lifecycle_tracker', $update, "id = ?", [$id]);
    }
}
