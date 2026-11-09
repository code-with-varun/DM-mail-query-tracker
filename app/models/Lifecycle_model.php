<?php
/**
 * Process Lifecycle Tracker Model
 */

class Lifecycle_model extends Model {

    public function __construct() {
        parent::__construct();
        $this->ensureTablesExist();
        $this->convertDummyTicketsToReal();
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

    /**
     * Convert any existing dummy 'LFC-...' items to original tickets in tickets table
     */
    public function convertDummyTicketsToReal(): void {
        try {
            $dummyItems = $this->fetchAll("SELECT * FROM ticket_lifecycle_tracker WHERE ticket_id IS NULL OR ticket_number LIKE 'LFC-%'");
            if (empty($dummyItems)) return;

            require_once __DIR__ . '/Ticket_model.php';
            $tModel = new Ticket_model();

            foreach ($dummyItems as $item) {
                $ticketNum = $tModel->generateTicketNumber();
                $realTicketId = $tModel->createTicket([
                    'ticket_number' => $ticketNum,
                    'ticket_type' => $item['ticket_type'] ?: 'File Preparation',
                    'subject' => ($item['sub_activity_name'] ?: 'Lifecycle Task') . ' (' . $item['billing_month'] . ')',
                    'division_id' => $item['division_id'],
                    'activity_id' => $item['activity_id'],
                    'sub_activity_id' => $item['sub_activity_id'],
                    'allocated_to' => $item['maker_id'],
                    'checker_id' => $item['checker_id'],
                    'billing_month' => $item['billing_month'],
                    'stage' => ($item['current_stage'] === 'Input Received') ? 'Maker Phase' : ($item['current_stage'] === 'Closed' ? 'Closed' : 'Maker Phase'),
                    'status' => ($item['current_stage'] === 'Closed') ? 'Closed' : 'Assigned',
                    'created_at' => $item['created_at'] ?: date('Y-m-d H:i:s')
                ]);

                $this->update('ticket_lifecycle_tracker', [
                    'ticket_id' => $realTicketId,
                    'ticket_number' => $ticketNum
                ], "id = ?", [$item['id']]);

                $this->update('ticket_lifecycle_history', [
                    'ticket_id' => $realTicketId
                ], "lifecycle_id = ?", [$item['id']]);
            }
        } catch (Exception $e) {}
    }

    public function generateItemNumber(string $prefix = 'MQT'): string {
        require_once __DIR__ . '/Ticket_model.php';
        $tModel = new Ticket_model();
        return $tModel->generateTicketNumber();
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
     * Reset and sync Lifecycle Tracker data for a selected billing month with real File Preparation / Task tickets
     */
    public function resetAndSyncWithTickets(string $billingMonth, int $createdBy): int {
        // 1. Clear existing lifecycle tracker records for this month
        try {
            $this->db->prepare("DELETE FROM ticket_lifecycle_tracker WHERE billing_month = ?")->execute([$billingMonth]);
        } catch (Exception $e) {}

        require_once __DIR__ . '/Ticket_model.php';
        $tModel = new Ticket_model();

        // 2. Fetch all real tickets matching this billing month (or created in this month if billing_month was null)
        $sql = "SELECT t.*, 
                       d.division_name, a.activity_name, sa.sub_activity_name, sa.default_user_id, sa.default_checker_id
                FROM tickets t
                JOIN sub_activities sa ON t.sub_activity_id = sa.id
                JOIN activities a ON t.activity_id = a.id
                LEFT JOIN divisions d ON t.division_id = d.id
                WHERE (t.billing_month = ? OR (t.billing_month IS NULL AND DATE_FORMAT(t.created_at, '%Y-%m') = ?))
                ORDER BY t.id ASC";
        $tickets = $this->fetchAll($sql, [$billingMonth, $billingMonth]);

        $syncedSubIds = [];
        $count = 0;

        $stageMap = [
            'Input Received'        => 'Input Received',
            'Maker Phase'           => 'Maker Queue',
            'Maker Queue'           => 'Maker Queue',
            'In Progress'           => 'Maker Queue',
            'Assigned'              => 'Maker Queue',
            'Checker Phase'         => 'Checker Queue',
            'Checker Queue'         => 'Checker Queue',
            'Error Log'             => 'Error Log',
            'Maker Checker Rework'  => 'Maker Checker Rework',
            'Delivery Phase'        => 'Delivery',
            'Delivery'              => 'Delivery',
            'Acknowledgement'       => 'Acknowledgement',
            'Approval Queue'        => 'Approval Queue',
            'Uploader Queue'        => 'Uploader Queue',
            'Dump Sharing'          => 'Dump Sharing',
            'Consol Vendor Tracker' => 'Consol Vendor Tracker',
            'Completed'             => 'Closed',
            'Closed'                => 'Closed',
        ];

        foreach ($tickets as $t) {
            $syncedSubIds[] = $t['sub_activity_id'];
            $currentStage = $stageMap[$t['stage'] ?? ''] ?? ($stageMap[$t['status'] ?? ''] ?? 'Maker Queue');

            $this->insert('ticket_lifecycle_tracker', [
                'ticket_id' => $t['id'],
                'billing_month' => $billingMonth,
                'ticket_number' => $t['ticket_number'],
                'ticket_type' => $t['ticket_type'] ?: 'File Preparation',
                'division_id' => $t['division_id'],
                'activity_id' => $t['activity_id'],
                'sub_activity_id' => $t['sub_activity_id'],
                'division_name' => $t['division_name'] ?? 'General Operations',
                'activity_name' => $t['activity_name'],
                'sub_activity_name' => $t['sub_activity_name'],
                'maker_id' => $t['allocated_to'] ?: ($t['default_user_id'] ?? null),
                'checker_id' => $t['checker_id'] ?: ($t['default_checker_id'] ?? null),
                'current_stage' => $currentStage,
                'input_received_at' => $t['received_datetime'] ?: $t['created_at'],
                'maker_queue_at' => $t['created_at'],
                'created_at' => $t['created_at']
            ]);
            $count++;
        }

        // 3. For active sub-activities without a ticket yet for this month, create original tickets in tickets table
        $sqlSub = "SELECT sa.id as sub_activity_id, sa.sub_activity_name, sa.default_user_id, sa.default_checker_id,
                          a.id as activity_id, a.activity_name,
                          d.id as division_id, d.division_name
                   FROM sub_activities sa
                   JOIN activities a ON sa.activity_id = a.id
                   LEFT JOIN divisions d ON sa.division_id = d.id
                   WHERE sa.status = 'Active'
                   ORDER BY d.division_name ASC, a.activity_name ASC, sa.sub_activity_name ASC";
        
        $subActs = $this->fetchAll($sqlSub);

        foreach ($subActs as $sa) {
            $subId = $sa['sub_activity_id'];
            if (in_array($subId, $syncedSubIds)) continue;

            $maker = $this->fetchOne("SELECT user_id FROM user_sub_activities WHERE sub_activity_id = ? AND role_type IN ('Maker', 'Both') LIMIT 1", [$subId]);
            $checker = $this->fetchOne("SELECT user_id FROM user_sub_activities WHERE sub_activity_id = ? AND role_type IN ('Checker', 'Both') LIMIT 1", [$subId]);

            $makerId = $maker['user_id'] ?? ($sa['default_user_id'] ?? null);
            $checkerId = $checker['user_id'] ?? ($sa['default_checker_id'] ?? null);

            // Create Original Ticket in tickets table
            $ticketNum = $tModel->generateTicketNumber();
            $realTicketId = $tModel->createTicket([
                'ticket_number' => $ticketNum,
                'ticket_type' => 'File Preparation',
                'subject' => $sa['sub_activity_name'] . ' (' . $billingMonth . ')',
                'division_id' => $sa['division_id'],
                'activity_id' => $sa['activity_id'],
                'sub_activity_id' => $subId,
                'allocated_to' => $makerId,
                'checker_id' => $checkerId,
                'billing_month' => $billingMonth,
                'stage' => 'Maker Phase',
                'status' => 'Assigned',
                'created_by' => $createdBy,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $this->insert('ticket_lifecycle_tracker', [
                'ticket_id' => $realTicketId,
                'billing_month' => $billingMonth,
                'ticket_number' => $ticketNum,
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

    public function populateFromActiveSubtasks(string $billingMonth, int $createdBy): int {
        return $this->resetAndSyncWithTickets($billingMonth, $createdBy);
    }

    public function createLifecycleItem(array $data): int {
        require_once __DIR__ . '/Ticket_model.php';
        $tModel = new Ticket_model();

        if (empty($data['ticket_id'])) {
            $ticketNum = $tModel->generateTicketNumber();
            $realTicketId = $tModel->createTicket([
                'ticket_number' => $ticketNum,
                'ticket_type' => $data['ticket_type'] ?? 'File Preparation',
                'subject' => ($data['sub_activity_name'] ?? 'Lifecycle Task') . ' (' . ($data['billing_month'] ?? date('Y-m')) . ')',
                'division_id' => $data['division_id'] ?? null,
                'activity_id' => $data['activity_id'],
                'sub_activity_id' => $data['sub_activity_id'],
                'allocated_to' => $data['maker_id'] ?? null,
                'checker_id' => $data['checker_id'] ?? null,
                'billing_month' => $data['billing_month'] ?? date('Y-m'),
                'stage' => 'Maker Phase',
                'status' => 'Assigned',
                'created_by' => $data['created_by'] ?? 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $data['ticket_id'] = $realTicketId;
            $data['ticket_number'] = $ticketNum;
        }

        $data['input_received_at'] = date('Y-m-d H:i:s');
        $data['created_at'] = date('Y-m-d H:i:s');
        
        $id = $this->insert('ticket_lifecycle_tracker', $data);

        $this->insert('ticket_lifecycle_history', [
            'lifecycle_id' => $id,
            'ticket_id' => $data['ticket_id'],
            'stage_name' => 'Input Received',
            'performed_by' => $data['created_by'] ?? 1,
            'performed_at' => date('Y-m-d H:i:s'),
            'remarks' => 'Lifecycle item logged & ticket created'
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

        // Keep original ticket in tickets table 100% in sync
        if (!empty($item['ticket_id'])) {
            $ticketStageMap = [
                'Input Received'        => 'Input Received',
                'Maker Queue'           => 'Maker Phase',
                'Checker Queue'         => 'Checker Phase',
                'Error Log'             => 'Error Log',
                'Maker Checker Rework'  => 'Maker Checker Rework',
                'Delivery'              => 'Delivery Phase',
                'Acknowledgement'       => 'Acknowledgement',
                'Approval Queue'        => 'Approval Queue',
                'Uploader Queue'        => 'Uploader Queue',
                'Dump Sharing'          => 'Dump Sharing',
                'Consol Vendor Tracker' => 'Consol Vendor Tracker',
                'Closed'                => 'Closed'
            ];
            $tStage = $ticketStageMap[$newStage] ?? $newStage;
            $tStatus = ($newStage === 'Closed') ? 'Closed' : 'In Progress';

            $ticketUpdate = ['stage' => $tStage, 'status' => $tStatus];
            if ($newStage === 'Closed') {
                $ticketUpdate['closed_at'] = $now;
            }
            try {
                $this->update('tickets', $ticketUpdate, "id = ?", [$item['ticket_id']]);
            } catch (Exception $e) {}
        }

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

        $item = $this->getById($id);
        if ($item && !empty($item['ticket_id'])) {
            $tUpdate = [];
            if ($makerId !== null) $tUpdate['allocated_to'] = $makerId;
            if ($checkerId !== null) $tUpdate['checker_id'] = $checkerId;
            if (!empty($tUpdate)) {
                try {
                    $this->update('tickets', $tUpdate, "id = ?", [$item['ticket_id']]);
                } catch (Exception $e) {}
            }
        }

        return $this->update('ticket_lifecycle_tracker', $update, "id = ?", [$id]);
    }

    public function getActivityProgressReport(string $billingMonth): array {
        $sql = "SELECT 
                    a.id as activity_id,
                    COALESCE(a.activity_name, t.activity_name, 'General Activity') as activity_name,
                    COUNT(t.id) as total_tasks,
                    SUM(CASE WHEN t.current_stage IN ('Input Received', 'Maker Queue', 'Maker Checker Rework') THEN 1 ELSE 0 END) as maker_stage_count,
                    SUM(CASE WHEN t.current_stage IN ('Checker Queue', 'Error Log') THEN 1 ELSE 0 END) as checker_stage_count,
                    SUM(CASE WHEN t.current_stage IN ('Delivery', 'Acknowledgement', 'Approval Queue', 'Uploader Queue', 'Dump Sharing', 'Consol Vendor Tracker') THEN 1 ELSE 0 END) as delivery_stage_count,
                    SUM(CASE WHEN t.current_stage = 'Closed' THEN 1 ELSE 0 END) as closed_count
                FROM ticket_lifecycle_tracker t
                LEFT JOIN activities a ON t.activity_id = a.id
                WHERE t.billing_month = ?
                GROUP BY a.id, activity_name
                ORDER BY total_tasks DESC";
        $rows = $this->fetchAll($sql, [$billingMonth]);

        foreach ($rows as &$r) {
            $total = (int)$r['total_tasks'];
            $closed = (int)$r['closed_count'];
            $r['progress_percentage'] = $total > 0 ? round(($closed / $total) * 100, 1) : 0;
        }
        return $rows;
    }

    public function getMonthOverallProgress(string $billingMonth): array {
        $sql = "SELECT 
                    COUNT(*) as total_tasks,
                    SUM(CASE WHEN current_stage = 'Closed' THEN 1 ELSE 0 END) as closed_tasks,
                    SUM(CASE WHEN current_stage IN ('Input Received', 'Maker Queue', 'Maker Checker Rework') THEN 1 ELSE 0 END) as maker_stage_tasks,
                    SUM(CASE WHEN current_stage IN ('Checker Queue', 'Error Log') THEN 1 ELSE 0 END) as checker_stage_tasks,
                    SUM(CASE WHEN current_stage IN ('Delivery', 'Acknowledgement', 'Approval Queue', 'Uploader Queue', 'Dump Sharing', 'Consol Vendor Tracker') THEN 1 ELSE 0 END) as delivery_stage_tasks
                FROM ticket_lifecycle_tracker
                WHERE billing_month = ?";
        $row = $this->fetchOne($sql, [$billingMonth]);

        $total = (int)($row['total_tasks'] ?? 0);
        $closed = (int)($row['closed_tasks'] ?? 0);
        $percentage = $total > 0 ? round(($closed / $total) * 100, 1) : 0;

        return [
            'total' => $total,
            'closed' => $closed,
            'percentage' => $percentage,
            'maker_tasks' => (int)($row['maker_stage_tasks'] ?? 0),
            'checker_tasks' => (int)($row['checker_stage_tasks'] ?? 0),
            'delivery_tasks' => (int)($row['delivery_stage_tasks'] ?? 0),
        ];
    }
}
