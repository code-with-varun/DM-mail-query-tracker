<?php
/**
 * Input & Output/Delivery Tracker Model
 */

class Tracker_model extends Model {
    public function getInputLogs(): array {
        $sql = "SELECT it.*, u.full_name as assigned_user_name, c.full_name as creator_name
                FROM input_tracker it
                LEFT JOIN users u ON it.assigned_to = u.id
                LEFT JOIN users c ON it.created_by = c.id
                ORDER BY it.id DESC";
        return $this->fetchAll($sql);
    }

    public function createInputLog(array $data): int {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert('input_tracker', $data);
    }

    public function getDeliveryLogs(): array {
        $sql = "SELECT dt.*, t.ticket_number, c.full_name as creator_name
                FROM delivery_tracker dt
                LEFT JOIN tickets t ON dt.ticket_id = t.id
                LEFT JOIN users c ON dt.created_by = c.id
                ORDER BY dt.id DESC";
        return $this->fetchAll($sql);
    }

    public function generateDeliveryNumber(): string {
        $year = date('Y');
        $prefix = "DEL-{$year}-";
        $last = $this->fetchOne("SELECT delivery_number FROM delivery_tracker WHERE delivery_number LIKE ? ORDER BY id DESC LIMIT 1", ["{$prefix}%"]);
        if ($last) {
            $lastNum = (int) substr($last['delivery_number'], -6);
            $nextNum = str_pad((string)($lastNum + 1), 6, '0', STR_PAD_LEFT);
        } else {
            $nextNum = "000001";
        }
        return $prefix . $nextNum;
    }

    public function createDeliveryLog(array $data): int {
        if (empty($data['delivery_number'])) {
            $data['delivery_number'] = $this->generateDeliveryNumber();
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert('delivery_tracker', $data);
    }
}
