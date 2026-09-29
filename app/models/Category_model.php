<?php
/**
 * Ticket Category Master Model
 */

class Category_model extends Model {

    public function __construct() {
        parent::__construct();
        $this->ensureDefaultCategoriesExist();
    }

    public function ensureDefaultCategoriesExist(): void {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `ticket_categories` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `category_name` VARCHAR(100) NOT NULL,
                  `category_slug` VARCHAR(100) NOT NULL UNIQUE,
                  `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
                  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            $defaults = [
                'General Query / Unclassified',
                'Allocation Exclude',
                'Checklist',
                'Grid',
                'Hold Release',
                'Input',
                'Penalty',
                'Query',
                'Rerun'
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

    public function getCategories(): array {
        return $this->fetchAll("SELECT * FROM ticket_categories WHERE status = 'Active' ORDER BY category_name ASC");
    }

    public function getAllCategories(): array {
        return $this->fetchAll("SELECT * FROM ticket_categories ORDER BY id DESC");
    }

    public function getById(int $id): ?array {
        return $this->fetchOne("SELECT * FROM ticket_categories WHERE id = ?", [$id]);
    }

    public function createCategory(string $name, string $status = 'Active'): int {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        return $this->insert('ticket_categories', [
            'category_name' => trim($name),
            'category_slug' => $slug,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function updateCategory(int $id, string $name, string $status = 'Active'): bool {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        return $this->update('ticket_categories', [
            'category_name' => trim($name),
            'category_slug' => $slug,
            'status' => $status
        ], "id = ?", [$id]);
    }

    public function deleteCategory(int $id): bool {
        return $this->delete('ticket_categories', "id = ?", [$id]);
    }
}
