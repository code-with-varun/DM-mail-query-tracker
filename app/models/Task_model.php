<?php
/**
 * Task Ticket Model
 */

require_once __DIR__ . '/Ticket_model.php';

class Task_model extends Model {
    public function createTask(array $ticketData, array $taskData): int {
        $ticketModel = new Ticket_model();
        $ticketData['ticket_type'] = 'Task Ticket';
        $ticketId = $ticketModel->createTicket($ticketData);

        $taskData['ticket_id'] = $ticketId;
        $taskData['created_at'] = date('Y-m-d H:i:s');
        $this->insert('task_tickets', $taskData);

        return $ticketId;
    }

    public function getTasks(?int $userId = null, ?int $roleId = null): array {
        $sql = "SELECT tt.*, t.ticket_number, t.allocated_to, t.created_by, t.status as ticket_status,
                       u_alloc.full_name as allocated_user_name,
                       u_creator.full_name as creator_name
                FROM task_tickets tt
                JOIN tickets t ON tt.ticket_id = t.id
                LEFT JOIN users u_alloc ON t.allocated_to = u_alloc.id
                LEFT JOIN users u_creator ON t.created_by = u_creator.id
                WHERE 1=1";
        $params = [];

        if ($roleId == 3 && $userId) {
            $sql .= " AND (t.allocated_to = ? OR t.created_by = ?)";
            $params[] = $userId;
            $params[] = $userId;
        } elseif ($roleId == 2 && $userId) {
            $sql .= " AND (t.allocated_to IN (SELECT id FROM users WHERE manager_id = ? OR id = ?) OR t.created_by = ?)";
            $params[] = $userId;
            $params[] = $userId;
            $params[] = $userId;
        }

        $sql .= " ORDER BY tt.id DESC";
        return $this->fetchAll($sql, $params);
    }
}
