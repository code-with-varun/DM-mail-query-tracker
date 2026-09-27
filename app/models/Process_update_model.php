<?php
/**
 * Process Updates Tracker Model
 * Preserves Outlook process update mails, policy changes & billing guidelines
 */

class Process_update_model extends Model {
    
    public function getAllUpdates(array $filters = []): array {
        $sql = "SELECT pu.*, d.division_name, d.code as division_code, 
                       a.activity_name, sa.sub_activity_name, u.full_name as creator_name, u.user_code as creator_code
                FROM process_updates pu
                LEFT JOIN divisions d ON pu.division_id = d.id
                LEFT JOIN activities a ON pu.activity_id = a.id
                LEFT JOIN sub_activities sa ON pu.sub_activity_id = sa.id
                LEFT JOIN users u ON pu.created_by = u.id
                WHERE 1=1";
        
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (pu.title LIKE ? OR pu.notes LIKE ? OR pu.file_name LIKE ?)";
            $term = "%" . $filters['search'] . "%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['division_id'])) {
            $sql .= " AND pu.division_id = ?";
            $params[] = (int)$filters['division_id'];
        }

        if (!empty($filters['activity_id'])) {
            $sql .= " AND pu.activity_id = ?";
            $params[] = (int)$filters['activity_id'];
        }

        if (!empty($filters['sub_activity_id'])) {
            $sql .= " AND pu.sub_activity_id = ?";
            $params[] = (int)$filters['sub_activity_id'];
        }

        if (!empty($filters['billing_month'])) {
            $sql .= " AND pu.billing_month = ?";
            $params[] = $filters['billing_month'];
        }

        $sql .= " ORDER BY pu.billing_month DESC, pu.id DESC";

        return $this->fetchAll($sql, $params);
    }

    public function getById(int $id): ?array {
        $sql = "SELECT pu.*, d.division_name, d.code as division_code, 
                       a.activity_name, sa.sub_activity_name, u.full_name as creator_name, u.user_code as creator_code
                FROM process_updates pu
                LEFT JOIN divisions d ON pu.division_id = d.id
                LEFT JOIN activities a ON pu.activity_id = a.id
                LEFT JOIN sub_activities sa ON pu.sub_activity_id = sa.id
                LEFT JOIN users u ON pu.created_by = u.id
                WHERE pu.id = ?
                LIMIT 1";
        return $this->fetchOne($sql, [$id]);
    }

    public function createUpdate(array $data): int {
        $data['created_at'] = date('Y-m-d H:i:s');
        $id = $this->insert('process_updates', $data);
        $this->logAudit('Process Updates', 'Create Process Update Mail', null, ['id' => $id, 'title' => $data['title']]);
        return $id;
    }

    public function updateUpdate(int $id, array $data): bool {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $result = $this->update('process_updates', $data, "id = ?", [$id]);
        $this->logAudit('Process Updates', 'Update Process Update Mail', ['id' => $id], $data);
        return $result;
    }

    public function deleteUpdate(int $id): bool {
        $record = $this->getById($id);
        if ($record && !empty($record['file_path'])) {
            $fullPath = __DIR__ . '/../../' . $record['file_path'];
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }
        $res = $this->delete('process_updates', "id = ?", [$id]);
        $this->logAudit('Process Updates', 'Delete Process Update Mail', ['id' => $id], null);
        return $res;
    }
}
