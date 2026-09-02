<?php
/**
 * Audit Log Model
 */

class Audit_model extends Model {
    public function getLogs(int $limit = 500): array {
        $sql = "SELECT a.*, u.full_name, u.user_code 
                FROM audit_logs a 
                LEFT JOIN users u ON a.user_id = u.id 
                ORDER BY a.id DESC LIMIT ?";
        return $this->fetchAll($sql, [$limit]);
    }
}
