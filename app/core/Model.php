<?php
/**
 * Base Model Class
 * Provides Database operations with PDO Prepared Statements
 */

class Model {
    protected PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function query(string $sql, array $params = []): PDOStatement {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll(string $sql, array $params = []): array {
        return $this->query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchOne(string $sql, array $params = []): ?array {
        $result = $this->query($sql, $params)->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function insert(string $table, array $data): int {
        $fields = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));
        $sql = "INSERT INTO `{$table}` (`" . implode('`, `', $fields) . "`) VALUES ({$placeholders})";
        
        $this->query($sql, array_values($data));
        return (int) $this->db->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int {
        $setFields = [];
        $params = [];
        foreach ($data as $key => $value) {
            $setFields[] = "`{$key}` = ?";
            $params[] = $value;
        }
        $params = array_merge($params, $whereParams);
        $sql = "UPDATE `{$table}` SET " . implode(', ', $setFields) . " WHERE {$where}";
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function delete(string $table, string $where, array $params = []): int {
        $sql = "DELETE FROM `{$table}` WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function logAudit(string $module, string $action, ?array $oldValues = null, ?array $newValues = null): void {
        $userId = Session::get('user_id');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        
        $this->insert('audit_logs', [
            'user_id' => $userId,
            'module' => $module,
            'action' => $action,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $ip,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
