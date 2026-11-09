<?php
/**
 * Workflow & Queue Management Model
 */

class Workflow_model extends Model {

    public function __construct() {
        parent::__construct();
        $this->ensureTablesExist();
        $this->seedDefaultWorkflows();
        $this->cleanWorkflowStageNames();
    }

    public function cleanWorkflowStageNames(): void {
        try {
            $stages = $this->fetchAll("SELECT id, stage_name, description FROM workflow_stages");
            foreach ($stages as $stg) {
                $cleanName = preg_replace('/^\d+[\.\)]\s*/', '', $stg['stage_name']);
                $cleanDesc = !empty($stg['description']) ? preg_replace('/^\d+[\.\)]\s*/', '', $stg['description']) : '';
                if ($cleanName !== $stg['stage_name'] || $cleanDesc !== $stg['description']) {
                    $this->update('workflow_stages', [
                        'stage_name' => $cleanName,
                        'description' => $cleanDesc
                    ], "id = ?", [$stg['id']]);
                }
            }
        } catch (Exception $e) {}
    }

    /**
     * Ensure database schema tables exist automatically
     */
    public function ensureTablesExist(): void {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `workflows` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `workflow_name` VARCHAR(255) NOT NULL,
                  `category_id` INT NULL DEFAULT NULL,
                  `sub_activity_id` INT NULL DEFAULT NULL,
                  `description` TEXT NULL,
                  `is_default` TINYINT(1) DEFAULT 0,
                  `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
                  `created_by` INT NOT NULL DEFAULT 1,
                  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `workflow_stages` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `workflow_id` INT NOT NULL,
                  `stage_order` INT NOT NULL DEFAULT 1,
                  `stage_name` VARCHAR(255) NOT NULL,
                  `queue_type` ENUM('Input', 'Maker', 'Checker', 'ErrorLog', 'Rework', 'Delivery', 'Ack', 'Approval', 'Uploader', 'DumpShare', 'ConsolVendor', 'Closed') DEFAULT 'Maker',
                  `description` TEXT NULL,
                  `assigned_role` ENUM('Maker', 'Checker', 'Both', 'Admin') DEFAULT 'Maker',
                  `pos_x` INT DEFAULT 100,
                  `pos_y` INT DEFAULT 100,
                  `connected_to` VARCHAR(255) NULL,
                  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                  INDEX `idx_wf_order` (`workflow_id`, `stage_order`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            try {
                $this->db->exec("ALTER TABLE `workflow_stages` ADD COLUMN `pos_x` INT DEFAULT 100 AFTER `assigned_role`");
            } catch (Exception $e) {}
            try {
                $this->db->exec("ALTER TABLE `workflow_stages` ADD COLUMN `pos_y` INT DEFAULT 100 AFTER `pos_x`");
            } catch (Exception $e) {}
            try {
                $this->db->exec("ALTER TABLE `workflow_stages` ADD COLUMN `connected_to` VARCHAR(255) NULL AFTER `pos_y`");
            } catch (Exception $e) {}

            try {
                $this->db->exec("ALTER TABLE `ticket_categories` ADD COLUMN `workflow_id` INT NULL DEFAULT NULL AFTER `status`");
            } catch (Exception $e) {}

            try {
                $this->db->exec("ALTER TABLE `sub_activities` ADD COLUMN `workflow_id` INT NULL DEFAULT NULL AFTER `default_checker_id`");
            } catch (Exception $e) {}

            try {
                $this->db->exec("ALTER TABLE `tickets` ADD COLUMN `workflow_id` INT NULL DEFAULT NULL AFTER `sub_activity_id`");
            } catch (Exception $e) {}

            try {
                $this->db->exec("ALTER TABLE `tickets` ADD COLUMN `current_stage_id` INT NULL DEFAULT NULL AFTER `workflow_id`");
            } catch (Exception $e) {}

        } catch (Exception $e) {}
    }

    /**
     * Seed Default Workflows if none exist
     */
    public function seedDefaultWorkflows(): void {
        try {
            $count = $this->fetchOne("SELECT COUNT(*) as cnt FROM workflows")['cnt'] ?? 0;
            if ($count > 0) return;

            // 1. Standard File Preparation Workflow (Full 12-Stage Pipeline)
            $wf1Id = $this->insert('workflows', [
                'workflow_name' => 'Standard File Preparation Workflow',
                'description' => 'Full 12-stage process queue for file preparation, calculation, quality audit, and vendor distribution.',
                'is_default' => 1,
                'status' => 'Active',
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $stages1 = [
                ['order' => 1, 'name' => '1. Input Recv (Client / System)', 'type' => 'Input', 'role' => 'Maker', 'desc' => 'Receiving raw client data file or system triggers.'],
                ['order' => 2, 'name' => '2. Maker Phase (File Preparation)', 'type' => 'Maker', 'role' => 'Maker', 'desc' => 'Main file preparation, formulas & data mapping.'],
                ['order' => 3, 'name' => '3. Checker Audit (Quality Audit)', 'type' => 'Checker', 'role' => 'Checker', 'desc' => 'Quality verification by designated Checker.'],
                ['order' => 4, 'name' => '4. Error Log (Audit Discrepancy)', 'type' => 'ErrorLog', 'role' => 'Checker', 'desc' => 'Audit discrepancy logging and exception queue.'],
                ['order' => 5, 'name' => '5. MC Rework (Rework Loop)', 'type' => 'Rework', 'role' => 'Maker', 'desc' => 'Maker rework loop for rejected/error items.'],
                ['order' => 6, 'name' => '6. Delivery Phase (File Delivery)', 'type' => 'Delivery', 'role' => 'Maker', 'desc' => 'Final file delivery to client / external portal.'],
                ['order' => 7, 'name' => '7. Ack Recv (Client Confirmation)', 'type' => 'Ack', 'role' => 'Both', 'desc' => 'Client delivery confirmation & acknowledgment.'],
                ['order' => 8, 'name' => '8. Approval (Business Head)', 'type' => 'Approval', 'role' => 'Admin', 'desc' => 'Management / Business Head sign-off approval.'],
                ['order' => 9, 'name' => '9. Uploader (Claims Uploading)', 'type' => 'Uploader', 'role' => 'Maker', 'desc' => 'Upload processed claims data to portal.'],
                ['order' => 10, 'name' => '10. Dump Share (Finance Team)', 'type' => 'DumpShare', 'role' => 'Both', 'desc' => 'Share final dump files with Finance team.'],
                ['order' => 11, 'name' => '11. Consol Vendor (Vendor Prep)', 'type' => 'ConsolVendor', 'role' => 'Maker', 'desc' => 'Consolidated vendor file compilation.'],
                ['order' => 12, 'name' => '12. Ticket Closed (Maker Completion)', 'type' => 'Closed', 'role' => 'Both', 'desc' => 'Process completed & ticket archived.']
            ];

            foreach ($stages1 as $st) {
                $this->insert('workflow_stages', [
                    'workflow_id' => $wf1Id,
                    'stage_order' => $st['order'],
                    'stage_name' => $st['name'],
                    'queue_type' => $st['type'],
                    'assigned_role' => $st['role'],
                    'description' => $st['desc'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // 2. Mail Query Response Workflow
            $wf2Id = $this->insert('workflows', [
                'workflow_name' => 'Mail Query Response Workflow',
                'description' => 'Fast-track queue workflow for mail queries, clarifications, and client communications.',
                'is_default' => 0,
                'status' => 'Active',
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $stages2 = [
                ['order' => 1, 'name' => '1. Input Recv (Query Received)', 'type' => 'Input', 'role' => 'Maker', 'desc' => 'Mail query ingested from inbox.'],
                ['order' => 2, 'name' => '2. Maker Analysis & Draft Revert', 'type' => 'Maker', 'role' => 'Maker', 'desc' => 'Analysis of query and drafting response.'],
                ['order' => 3, 'name' => '3. Checker Review & Sign-Off', 'type' => 'Checker', 'role' => 'Checker', 'desc' => 'Review drafted reply before sending.'],
                ['order' => 4, 'name' => '4. Mail Revert Sent (Delivery)', 'type' => 'Delivery', 'role' => 'Maker', 'desc' => 'Email response sent to requester.'],
                ['order' => 5, 'name' => '5. Query Ticket Closed', 'type' => 'Closed', 'role' => 'Both', 'desc' => 'Query resolved and closed.']
            ];

            foreach ($stages2 as $st) {
                $this->insert('workflow_stages', [
                    'workflow_id' => $wf2Id,
                    'stage_order' => $st['order'],
                    'stage_name' => $st['name'],
                    'queue_type' => $st['type'],
                    'assigned_role' => $st['role'],
                    'description' => $st['desc'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // 3. Rerun File Workflow
            $wf3Id = $this->insert('workflows', [
                'workflow_name' => 'Rerun File Workflow',
                'description' => 'Dedicated rework & re-execution workflow for file reruns, corrections, and updated inputs.',
                'is_default' => 0,
                'status' => 'Active',
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $stages3 = [
                ['order' => 1, 'name' => '1. Rerun Request Recv', 'type' => 'Input', 'role' => 'Maker', 'desc' => 'Rerun request or updated file received.'],
                ['order' => 2, 'name' => '2. Rerun Maker Re-Execution', 'type' => 'Rework', 'role' => 'Maker', 'desc' => 'Re-executing calculations with updated data.'],
                ['order' => 3, 'name' => '3. Quality Checker Verification', 'type' => 'Checker', 'role' => 'Checker', 'desc' => 'Checker verification of rerun outputs.'],
                ['order' => 4, 'name' => '4. Redelivery Phase', 'type' => 'Delivery', 'role' => 'Maker', 'desc' => 'Delivering updated rerun files.'],
                ['order' => 5, 'name' => '5. Client Ack & Confirmation', 'type' => 'Ack', 'role' => 'Both', 'desc' => 'Client confirmation of rerun accuracy.'],
                ['order' => 6, 'name' => '6. Rerun Ticket Closed', 'type' => 'Closed', 'role' => 'Both', 'desc' => 'Rerun completed.']
            ];

            foreach ($stages3 as $st) {
                $this->insert('workflow_stages', [
                    'workflow_id' => $wf3Id,
                    'stage_order' => $st['order'],
                    'stage_name' => $st['name'],
                    'queue_type' => $st['type'],
                    'assigned_role' => $st['role'],
                    'description' => $st['desc'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Map default workflows to Categories
            $queryCat = $this->fetchOne("SELECT id FROM ticket_categories WHERE LOWER(category_name) LIKE '%query%' LIMIT 1");
            if ($queryCat) {
                $this->update('ticket_categories', ['workflow_id' => $wf2Id], "id = ?", [$queryCat['id']]);
            }

            $rerunCat = $this->fetchOne("SELECT id FROM ticket_categories WHERE LOWER(category_name) LIKE '%rerun%' LIMIT 1");
            if ($rerunCat) {
                $this->update('ticket_categories', ['workflow_id' => $wf3Id], "id = ?", [$rerunCat['id']]);
            }

            $inputCat = $this->fetchOne("SELECT id FROM ticket_categories WHERE LOWER(category_name) LIKE '%input%' OR LOWER(category_name) LIKE '%grid%' LIMIT 1");
            if ($inputCat) {
                $this->update('ticket_categories', ['workflow_id' => $wf1Id], "id = ?", [$inputCat['id']]);
            }

        } catch (Exception $e) {}
    }

    public function getAllWorkflows(): array {
        $sql = "SELECT w.*, 
                       tc.category_name, 
                       sa.sub_activity_name, 
                       a.activity_name,
                       (SELECT COUNT(*) FROM workflow_stages WHERE workflow_id = w.id) as stage_count
                FROM workflows w
                LEFT JOIN ticket_categories tc ON w.category_id = tc.id
                LEFT JOIN sub_activities sa ON w.sub_activity_id = sa.id
                LEFT JOIN activities a ON sa.activity_id = a.id
                ORDER BY w.id DESC";
        return $this->fetchAll($sql);
    }

    public function getWorkflowById(int $id): ?array {
        $sql = "SELECT w.*, tc.category_name, sa.sub_activity_name 
                FROM workflows w
                LEFT JOIN ticket_categories tc ON w.category_id = tc.id
                LEFT JOIN sub_activities sa ON w.sub_activity_id = sa.id
                WHERE w.id = ? LIMIT 1";
        $wf = $this->fetchOne($sql, [$id]);
        if ($wf) {
            $wf['stages'] = $this->getWorkflowStages($id);
        }
        return $wf;
    }

    public function getWorkflowStages(int $workflowId): array {
        return $this->fetchAll("
            SELECT * 
            FROM workflow_stages 
            WHERE workflow_id = ? 
            ORDER BY stage_order ASC
        ", [$workflowId]);
    }

    public function saveWorkflow(array $data, array $stages): int {
        $wfData = [
            'workflow_name' => trim($data['workflow_name']),
            'category_id' => !empty($data['category_id']) ? (int)$data['category_id'] : null,
            'sub_activity_id' => !empty($data['sub_activity_id']) ? (int)$data['sub_activity_id'] : null,
            'description' => sanitize($data['description'] ?? ''),
            'is_default' => !empty($data['is_default']) ? 1 : 0,
            'status' => $data['status'] ?? 'Active',
            'created_by' => current_user()['id'] ?? 1,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (!empty($data['workflow_id'])) {
            $wfId = (int)$data['workflow_id'];
            $this->update('workflows', $wfData, "id = ?", [$wfId]);
            $this->delete('workflow_stages', "workflow_id = ?", [$wfId]);
        } else {
            $wfData['created_at'] = date('Y-m-d H:i:s');
            $wfId = $this->insert('workflows', $wfData);
        }

        // If marked as default, un-default other workflows
        if (!empty($wfData['is_default'])) {
            $this->update('workflows', ['is_default' => 0], "id != ?", [$wfId]);
        }

        // Save Stages
        $order = 1;
        foreach ($stages as $st) {
            if (empty($st['stage_name'])) continue;
            $this->insert('workflow_stages', [
                'workflow_id' => $wfId,
                'stage_order' => $order,
                'stage_name' => trim($st['stage_name']),
                'queue_type' => $st['queue_type'] ?? 'Maker',
                'assigned_role' => $st['assigned_role'] ?? 'Maker',
                'description' => sanitize($st['description'] ?? ''),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $order++;
        }

        // Sync category mapping
        $this->syncCategoryMapping($wfId, $wfData['category_id']);

        // Sync sub-activity mapping if selected
        if (!empty($wfData['sub_activity_id'])) {
            $this->update('sub_activities', ['workflow_id' => $wfId], "id = ?", [$wfData['sub_activity_id']]);
        }

        return $wfId;
    }

    public function syncCategoryMapping(int $wfId, ?int $categoryId): void {
        try {
            // Unlink old category mapping for this workflow
            $this->update('ticket_categories', ['workflow_id' => null], "workflow_id = ?", [$wfId]);

            if ($categoryId && $categoryId > 0) {
                // Unlink any other workflow mapped to this category
                $this->update('workflows', ['category_id' => null], "category_id = ? AND id != ?", [$categoryId, $wfId]);
                // Link category to this workflow
                $this->update('ticket_categories', ['workflow_id' => $wfId], "id = ?", [$categoryId]);
                $this->update('workflows', ['category_id' => $categoryId], "id = ?", [$wfId]);
            } else {
                $this->update('workflows', ['category_id' => null], "id = ?", [$wfId]);
            }
        } catch (Exception $e) {}
    }

    public function deleteWorkflow(int $id): bool {
        try {
            // Clear workflow references in other tables before deleting
            $this->update('ticket_categories', ['workflow_id' => null], "workflow_id = ?", [$id]);
            $this->update('sub_activities', ['workflow_id' => null], "workflow_id = ?", [$id]);
            $this->update('tickets', ['workflow_id' => null], "workflow_id = ?", [$id]);
            $this->update('workflows', ['category_id' => null], "id = ?", [$id]);

            // Delete workflow stages
            $this->delete('workflow_stages', "workflow_id = ?", [$id]);

            // Delete main workflow record
            return $this->delete('workflows', "id = ?", [$id]) > 0;
        } catch (Exception $e) {
            error_log("Error deleting workflow {$id}: " . $e->getMessage());
            return false;
        }
    }

    public function getWorkflowForTicket(?int $categoryId, ?int $subActivityId): ?array {
        // 1. Check Sub-Activity specific workflow override
        if ($subActivityId) {
            $sa = $this->fetchOne("SELECT workflow_id FROM sub_activities WHERE id = ?", [$subActivityId]);
            if (!empty($sa['workflow_id'])) {
                return $this->getWorkflowById((int)$sa['workflow_id']);
            }
        }

        // 2. Check Category workflow
        if ($categoryId) {
            $cat = $this->fetchOne("SELECT workflow_id FROM ticket_categories WHERE id = ?", [$categoryId]);
            if (!empty($cat['workflow_id'])) {
                return $this->getWorkflowById((int)$cat['workflow_id']);
            }
        }

        // 3. Fallback to default active workflow
        $default = $this->fetchOne("SELECT id FROM workflows WHERE is_default = 1 AND status = 'Active' LIMIT 1");
        if ($default) {
            return $this->getWorkflowById((int)$default['id']);
        }

        // 4. Fallback to first active workflow
        $first = $this->fetchOne("SELECT id FROM workflows WHERE status = 'Active' ORDER BY id ASC LIMIT 1");
        if ($first) {
            return $this->getWorkflowById((int)$first['id']);
        }

        return null;
    }

    public function saveCanvasWorkflow(int $wfId, array $wfData, array $nodes): bool {
        // Update main workflow details
        $upd = [
            'workflow_name' => trim($wfData['workflow_name']),
            'category_id' => !empty($wfData['category_id']) ? (int)$wfData['category_id'] : null,
            'sub_activity_id' => !empty($wfData['sub_activity_id']) ? (int)$wfData['sub_activity_id'] : null,
            'description' => sanitize($wfData['description'] ?? ''),
            'is_default' => !empty($wfData['is_default']) ? 1 : 0,
            'status' => $wfData['status'] ?? 'Active',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->update('workflows', $upd, "id = ?", [$wfId]);

        if (!empty($upd['is_default'])) {
            $this->update('workflows', ['is_default' => 0], "id != ?", [$wfId]);
        }

        // Replace stages
        $this->delete('workflow_stages', "workflow_id = ?", [$wfId]);

        $order = 1;
        foreach ($nodes as $n) {
            if (empty($n['stage_name'])) continue;
            $this->insert('workflow_stages', [
                'workflow_id' => $wfId,
                'stage_order' => $order,
                'stage_name' => trim($n['stage_name']),
                'queue_type' => $n['queue_type'] ?? 'Maker',
                'assigned_role' => $n['assigned_role'] ?? 'Maker',
                'description' => sanitize($n['description'] ?? ''),
                'pos_x' => (int)($n['pos_x'] ?? (50 + ($order * 240))),
                'pos_y' => (int)($n['pos_y'] ?? 150),
                'connected_to' => sanitize($n['connected_to'] ?? ''),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $order++;
        }

        // Sync category mapping
        $this->syncCategoryMapping($wfId, $upd['category_id']);

        // Sync sub-activity mapping
        if (!empty($upd['sub_activity_id'])) {
            $this->update('sub_activities', ['workflow_id' => $wfId], "id = ?", [$upd['sub_activity_id']]);
        }

        return true;
    }
}
