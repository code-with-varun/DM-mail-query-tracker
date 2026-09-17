<?php
/**
 * Error Tracker Model
 * Handles CRUD and Bulk Import/Export for Error Tracker
 */

class Error_tracker_model extends Model {
    public function getErrors(?array $filters = null): array {
        $sql = "
            SELECT et.*, 
                   um.full_name as maker_name, um.user_code as maker_code,
                   uc.full_name as checker_name, uc.user_code as checker_code,
                   ucreator.full_name as creator_name
            FROM error_tracker et
            LEFT JOIN users um ON et.maker_id = um.id
            LEFT JOIN users uc ON et.checker_id = uc.id
            LEFT JOIN users ucreator ON et.created_by = ucreator.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['error_type']) && in_array($filters['error_type'], ['Internal', 'External'])) {
            $sql .= " AND et.error_type = ?";
            $params[] = $filters['error_type'];
        }

        if (!empty($filters['billing_month'])) {
            $sql .= " AND et.billing_month = ?";
            $params[] = $filters['billing_month'];
        }

        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $sql .= " AND (et.error_observation LIKE ? OR et.error_description LIKE ? OR et.resolution_solution LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY et.id DESC";

        return $this->fetchAll($sql, $params);
    }

    public function getErrorById(int $id): ?array {
        return $this->fetchOne("
            SELECT et.*, 
                   um.full_name as maker_name, um.user_code as maker_code,
                   uc.full_name as checker_name, uc.user_code as checker_code,
                   ucreator.full_name as creator_name
            FROM error_tracker et
            LEFT JOIN users um ON et.maker_id = um.id
            LEFT JOIN users uc ON et.checker_id = uc.id
            LEFT JOIN users ucreator ON et.created_by = ucreator.id
            WHERE et.id = ?
        ", [$id]);
    }

    public function createError(array $data): int {
        $insertData = [
            'billing_month' => $data['billing_month'],
            'checking_month' => $data['checking_month'],
            'error_observation' => $data['error_observation'],
            'error_description' => $data['error_description'] ?? null,
            'resolution_solution' => $data['resolution_solution'] ?? null,
            'error_type' => $data['error_type'] ?? 'Internal',
            'maker_id' => !empty($data['maker_id']) ? (int)$data['maker_id'] : null,
            'checker_id' => !empty($data['checker_id']) ? (int)$data['checker_id'] : null,
            'created_by' => $data['created_by'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        $id = $this->insert('error_tracker', $insertData);
        $this->logAudit('Error Tracker', 'CREATE_ERROR', null, $insertData);

        return $id;
    }

    public function updateError(int $id, array $data): bool {
        $oldData = $this->getErrorById($id);
        if (!$oldData) return false;

        $updateData = [
            'billing_month' => $data['billing_month'],
            'checking_month' => $data['checking_month'],
            'error_observation' => $data['error_observation'],
            'error_description' => $data['error_description'] ?? null,
            'resolution_solution' => $data['resolution_solution'] ?? null,
            'error_type' => $data['error_type'] ?? 'Internal',
            'maker_id' => !empty($data['maker_id']) ? (int)$data['maker_id'] : null,
            'checker_id' => !empty($data['checker_id']) ? (int)$data['checker_id'] : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->update('error_tracker', $updateData, 'id = ?', [$id]);
        $this->logAudit('Error Tracker', 'UPDATE_ERROR', $oldData, $updateData);

        return true;
    }

    public function deleteError(int $id): bool {
        $oldData = $this->getErrorById($id);
        if (!$oldData) return false;

        $this->delete('error_tracker', 'id = ?', [$id]);
        $this->logAudit('Error Tracker', 'DELETE_ERROR', $oldData, null);

        return true;
    }

    public function getStats(): array {
        $stats = [
            'total' => 0,
            'internal' => 0,
            'external' => 0,
            'resolved' => 0
        ];

        $rows = $this->fetchAll("
            SELECT error_type, 
                   COUNT(*) as cnt,
                   SUM(CASE WHEN resolution_solution IS NOT NULL AND TRIM(resolution_solution) != '' THEN 1 ELSE 0 END) as resolved_cnt
            FROM error_tracker 
            GROUP BY error_type
        ");

        foreach ($rows as $row) {
            $type = $row['error_type'];
            $count = (int)$row['cnt'];
            $stats['total'] += $count;
            $stats['resolved'] += (int)$row['resolved_cnt'];

            if ($type === 'Internal') {
                $stats['internal'] = $count;
            } elseif ($type === 'External') {
                $stats['external'] = $count;
            }
        }

        return $stats;
    }
}
