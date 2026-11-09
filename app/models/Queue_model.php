<?php
/**
 * Process Queues Master Model
 */

class Queue_model extends Model {

    public function __construct() {
        parent::__construct();
        $this->ensureTableExists();
        $this->seedDefaultQueues();
        $this->cleanAndSyncDefaultIcons();
    }

    public function ensureTableExists(): void {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `process_queues` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `queue_name` VARCHAR(255) NOT NULL,
                  `queue_code` VARCHAR(50) NOT NULL,
                  `assigned_role` ENUM('Maker', 'Checker', 'Both', 'Admin') DEFAULT 'Maker',
                  `badge_color` VARCHAR(50) DEFAULT '#2563eb',
                  `icon` VARCHAR(50) DEFAULT 'fa-list-alt',
                  `description` TEXT NULL,
                  `display_order` INT NOT NULL DEFAULT 1,
                  `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
                  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
            
            $cols = $this->fetchAll("SHOW COLUMNS FROM process_queues LIKE 'icon'");
            if (empty($cols)) {
                $this->db->exec("ALTER TABLE `process_queues` ADD COLUMN `icon` VARCHAR(50) DEFAULT 'fa-list-alt'");
            }
        } catch (Exception $e) {}
    }

    public function cleanAndSyncDefaultIcons(): void {
        try {
            $queues = $this->fetchAll("SELECT * FROM process_queues");
            $iconMap = [
                'Input'        => 'fa-inbox',
                'Maker'        => 'fa-pencil-ruler',
                'Checker'      => 'fa-clipboard-check',
                'ErrorLog'     => 'fa-exclamation-triangle',
                'Rework'       => 'fa-sync',
                'Delivery'     => 'fa-paper-plane',
                'Ack'          => 'fa-envelope-open-text',
                'Approval'     => 'fa-user-shield',
                'Uploader'     => 'fa-cloud-upload-alt',
                'DumpShare'    => 'fa-database',
                'ConsolVendor' => 'fa-truck-moving',
                'Closed'       => 'fa-check-double'
            ];

            foreach ($queues as $q) {
                $cleanName = preg_replace('/^\d+[\.\)]\s*/', '', $q['queue_name']);
                $cleanDesc = !empty($q['description']) ? preg_replace('/^\d+[\.\)]\s*/', '', $q['description']) : '';
                $icon = !empty($q['icon']) && $q['icon'] !== 'fa-list-alt' ? $q['icon'] : ($iconMap[$q['queue_code']] ?? 'fa-tasks');

                $this->update('process_queues', [
                    'queue_name' => $cleanName,
                    'description' => $cleanDesc,
                    'icon' => $icon
                ], "id = ?", [$q['id']]);
            }
        } catch (Exception $e) {}
    }

    public function seedDefaultQueues(): void {
        try {
            $count = $this->fetchOne("SELECT COUNT(*) as cnt FROM process_queues")['cnt'] ?? 0;
            if ($count > 0) return;

            $defaultQueues = [
                ['name' => 'Input Recv (Client / System)', 'code' => 'Input',       'role' => 'Maker',   'color' => '#0284c7', 'icon' => 'fa-inbox',             'order' => 1,  'desc' => 'Ingestion of client input file or system email trigger.'],
                ['name' => 'Maker Phase (File Prep)',      'code' => 'Maker',       'role' => 'Maker',   'color' => '#2563eb', 'icon' => 'fa-pencil-ruler',      'order' => 2,  'desc' => 'Primary processing, preparation, and calculation phase.'],
                ['name' => 'Quality Checker Audit',        'code' => 'Checker',     'role' => 'Checker', 'color' => '#d97706', 'icon' => 'fa-clipboard-check',    'order' => 3,  'desc' => 'Quality verification and audit by checker.'],
                ['name' => 'Error Log (Audit Discrepancy)','code' => 'ErrorLog',    'role' => 'Checker', 'color' => '#dc2626', 'icon' => 'fa-exclamation-triangle', 'order' => 4,  'desc' => 'Audit discrepancy logging and exception queue.'],
                ['name' => 'MC Rework Loop',               'code' => 'Rework',      'role' => 'Maker',   'color' => '#9333ea', 'icon' => 'fa-sync',                 'order' => 5,  'desc' => 'Maker rework loop for rejected or corrected items.'],
                ['name' => 'Delivery Phase (File Revert)', 'code' => 'Delivery',    'role' => 'Maker',   'color' => '#0891b2', 'icon' => 'fa-paper-plane',       'order' => 6,  'desc' => 'Final file delivery or mail response.'],
                ['name' => 'Ack Recv (Client Confirmation)','code' => 'Ack',        'role' => 'Both',    'color' => '#16a34a', 'icon' => 'fa-envelope-open-text','order' => 7,  'desc' => 'Client delivery confirmation & acknowledgment.'],
                ['name' => 'Approval (Business Head)',     'code' => 'Approval',    'role' => 'Admin',   'color' => '#475569', 'icon' => 'fa-user-shield',       'order' => 8,  'desc' => 'Business head sign-off approval queue.'],
                ['name' => 'Claims Uploader (Maker)',       'code' => 'Uploader',    'role' => 'Maker',   'color' => '#4f46e5', 'icon' => 'fa-cloud-upload-alt',  'order' => 9,  'desc' => 'Uploading claims data to portal.'],
                ['name' => 'Dump Share (Finance Team)',   'code' => 'DumpShare',   'role' => 'Both',    'color' => '#0d9488', 'icon' => 'fa-database',          'order' => 10, 'desc' => 'Sharing output dump with Finance.'],
                ['name' => 'Consol Vendor Prep',          'code' => 'ConsolVendor','role' => 'Maker',   'color' => '#db2777', 'icon' => 'fa-truck-moving',      'order' => 11, 'desc' => 'Consolidated vendor preparation queue.'],
                ['name' => 'Ticket Closed',               'code' => 'Closed',      'role' => 'Both',    'color' => '#059669', 'icon' => 'fa-check-double',      'order' => 12, 'desc' => 'Final stage marking ticket completion.']
            ];

            foreach ($defaultQueues as $dq) {
                $this->insert('process_queues', [
                    'queue_name' => $dq['name'],
                    'queue_code' => $dq['code'],
                    'assigned_role' => $dq['role'],
                    'badge_color' => $dq['color'],
                    'icon' => $dq['icon'],
                    'display_order' => $dq['order'],
                    'description' => $dq['desc'],
                    'status' => 'Active',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        } catch (Exception $e) {}
    }

    public function getAllQueues(): array {
        return $this->fetchAll("SELECT * FROM process_queues ORDER BY display_order ASC, id ASC");
    }

    public function getActiveQueues(): array {
        return $this->fetchAll("SELECT * FROM process_queues WHERE status = 'Active' ORDER BY display_order ASC, id ASC");
    }

    public function getQueueById(int $id): ?array {
        return $this->fetchOne("SELECT * FROM process_queues WHERE id = ? LIMIT 1", [$id]);
    }

    public function saveQueue(array $data): int {
        $cleanName = preg_replace('/^\d+[\.\)]\s*/', '', trim($data['queue_name']));
        $cleanDesc = !empty($data['description']) ? preg_replace('/^\d+[\.\)]\s*/', '', sanitize($data['description'])) : '';

        $qData = [
            'queue_name' => $cleanName,
            'queue_code' => trim($data['queue_code']),
            'assigned_role' => $data['assigned_role'] ?? 'Maker',
            'badge_color' => $data['badge_color'] ?? '#2563eb',
            'icon' => $data['icon'] ?? 'fa-list-alt',
            'display_order' => (int)($data['display_order'] ?? 1),
            'description' => $cleanDesc,
            'status' => $data['status'] ?? 'Active',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (!empty($data['queue_id'])) {
            $id = (int)$data['queue_id'];
            $this->update('process_queues', $qData, "id = ?", [$id]);
            return $id;
        } else {
            $qData['created_at'] = date('Y-m-d H:i:s');
            return $this->insert('process_queues', $qData);
        }
    }

    public function deleteQueue(int $id): bool {
        return $this->delete('process_queues', "id = ?", [$id]);
    }
}
