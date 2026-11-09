<?php
/**
 * Ticket Category Master Model
 */

class Category_model extends Model {

    public function __construct() {
        parent::__construct();
        $this->ensureWorkflowIdColumn();
        $this->ensureDefaultCategoriesExist();
        $this->cleanupLegacyCategories();
    }

    public function ensureWorkflowIdColumn(): void {
        try {
            $cols = $this->fetchAll("SHOW COLUMNS FROM ticket_categories LIKE 'workflow_id'");
            if (empty($cols)) {
                $this->db->exec("ALTER TABLE `ticket_categories` ADD COLUMN `workflow_id` INT NULL DEFAULT NULL AFTER `status`");
            }
        } catch (Exception $e) {}
    }

    public function ensureDefaultCategoriesExist(): void {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `ticket_categories` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `category_name` VARCHAR(100) NOT NULL,
                  `category_slug` VARCHAR(100) NOT NULL UNIQUE,
                  `workflow_id` INT NULL DEFAULT NULL,
                  `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
                  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            $defaults = [
                'Query',
                'Rerun',
                'Input',
                'Penalty',
                'Hold Release',
                'Grid',
                'Checklist',
                'File Preparation',
                'Allocation Exclude',
                'Process Updates'
            ];

            foreach ($defaults as $name) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
                $existing = $this->fetchOne("SELECT id FROM ticket_categories WHERE LOWER(category_name) = ? OR category_slug = ?", [strtolower($name), $slug]);
                if (!$existing) {
                    $this->insert('ticket_categories', [
                        'category_name' => $name,
                        'category_slug' => $slug,
                        'status' => 'Active',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        } catch (Exception $e) {}
    }

    public function cleanupLegacyCategories(): void {
        try {
            // Delete obsolete 'Unclassified' / 'General Query' categories
            $this->db->exec("DELETE FROM ticket_categories WHERE LOWER(category_name) LIKE '%unclassified%' OR LOWER(category_name) = 'general query'");
            
            // Remove duplicate categories keeping the lowest ID
            $this->db->exec("
                DELETE c1 FROM ticket_categories c1
                INNER JOIN ticket_categories c2 
                ON LOWER(c1.category_name) = LOWER(c2.category_name) 
                AND c1.id > c2.id
            ");
        } catch (Exception $e) {}
    }

    public function getCategories(): array {
        return $this->fetchAll("
            SELECT tc.*, w.workflow_name, w.is_default as workflow_is_default
            FROM ticket_categories tc
            LEFT JOIN workflows w ON tc.workflow_id = w.id
            WHERE tc.status = 'Active' 
            ORDER BY tc.category_name ASC
        ");
    }

    public function getAllCategories(): array {
        return $this->fetchAll("
            SELECT tc.*, w.workflow_name, w.is_default as workflow_is_default
            FROM ticket_categories tc
            LEFT JOIN workflows w ON tc.workflow_id = w.id
            ORDER BY tc.category_name ASC
        ");
    }

    public function getById(int $id): ?array {
        return $this->fetchOne("
            SELECT tc.*, w.workflow_name 
            FROM ticket_categories tc
            LEFT JOIN workflows w ON tc.workflow_id = w.id
            WHERE tc.id = ? LIMIT 1
        ", [$id]);
    }

    public function createCategory(string $name, string $status = 'Active', ?int $workflowId = null): int {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $catId = $this->insert('ticket_categories', [
            'category_name' => trim($name),
            'category_slug' => $slug,
            'workflow_id' => $workflowId ?: null,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if ($workflowId && $workflowId > 0) {
            try {
                // Clear any existing category mapping for this workflow
                $this->update('ticket_categories', ['workflow_id' => null], "workflow_id = ? AND id != ?", [$workflowId, $catId]);
                $this->update('workflows', ['category_id' => $catId], "id = ?", [$workflowId]);
            } catch (Exception $e) {}
        }

        return $catId;
    }

    public function updateCategory(int $id, string $name, string $status = 'Active', ?int $workflowId = null): bool {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

        // Unlink previous workflow pointing to this category
        try {
            $this->update('workflows', ['category_id' => null], "category_id = ?", [$id]);
        } catch (Exception $e) {}

        $res = $this->update('ticket_categories', [
            'category_name' => trim($name),
            'category_slug' => $slug,
            'workflow_id' => $workflowId ?: null,
            'status' => $status
        ], "id = ?", [$id]);

        if ($workflowId && $workflowId > 0) {
            try {
                // Unlink any other category mapped to this workflow
                $this->update('ticket_categories', ['workflow_id' => null], "workflow_id = ? AND id != ?", [$workflowId, $id]);
                $this->update('workflows', ['category_id' => $id], "id = ?", [$workflowId]);
            } catch (Exception $e) {}
        }

        return (bool)$res;
    }

    public function deleteCategory(int $id): bool {
        try {
            $this->update('workflows', ['category_id' => null], "category_id = ?", [$id]);
            $this->update('tickets', ['category_id' => null], "category_id = ?", [$id]);
            return $this->delete('ticket_categories', "id = ?", [$id]) > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}
