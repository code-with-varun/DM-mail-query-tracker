<?php
/**
 * Ticket Category Master Model
 */

class Category_model extends Model {
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
