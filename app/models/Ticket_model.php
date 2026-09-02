<?php
/**
 * Ticket Model (Mail Query & Task Tickets)
 */

class Ticket_model extends Model {
    public function generateTicketNumber(): string {
        $year = date('Y');
        $prefix = "MQT-{$year}-";
        
        $sql = "SELECT ticket_number FROM tickets WHERE ticket_number LIKE ? ORDER BY id DESC LIMIT 1";
        $last = $this->fetchOne($sql, ["{$prefix}%"]);

        if ($last) {
            $lastNum = (int) substr($last['ticket_number'], -6);
            $nextNum = str_pad((string)($lastNum + 1), 6, '0', STR_PAD_LEFT);
        } else {
            $nextNum = "000001";
        }

        return $prefix . $nextNum;
    }

    public function createTicket(array $data): int {
        if (empty($data['ticket_number'])) {
            $data['ticket_number'] = $this->generateTicketNumber();
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $ticketId = $this->insert('tickets', $data);

        $this->logAudit('Tickets', 'Create Ticket', null, ['id' => $ticketId, 'ticket_number' => $data['ticket_number']]);

        // Trigger Notification if allocated
        if (!empty($data['allocated_to'])) {
            $this->createNotification($data['allocated_to'], "New Ticket Assigned", "You have been assigned ticket {$data['ticket_number']}", "/tickets/view/{$ticketId}");
        }

        return $ticketId;
    }

    public function getTicketById(int $id): ?array {
        $sql = "SELECT t.*, 
                       d.division_name, d.code as division_code,
                       a.activity_name, sa.sub_activity_name, sa.default_tat_hours,
                       u_alloc.full_name as allocated_user_name, u_alloc.email as allocated_user_email,
                       u_creator.full_name as creator_name,
                       u_replied.full_name as replied_user_name
                FROM tickets t
                LEFT JOIN divisions d ON t.division_id = d.id
                LEFT JOIN activities a ON t.activity_id = a.id
                LEFT JOIN sub_activities sa ON t.sub_activity_id = sa.id
                LEFT JOIN users u_alloc ON t.allocated_to = u_alloc.id
                LEFT JOIN users u_creator ON t.created_by = u_creator.id
                LEFT JOIN users u_replied ON t.replied_by = u_replied.id
                WHERE t.id = ? LIMIT 1";
        return $this->fetchOne($sql, [$id]);
    }

    public function getTickets(array $filters = [], ?int $userId = null, ?int $roleId = null): array {
        $sql = "SELECT t.*, 
                       a.activity_name, sa.sub_activity_name,
                       u_alloc.full_name as allocated_user_name,
                       u_creator.full_name as creator_name
                FROM tickets t
                LEFT JOIN activities a ON t.activity_id = a.id
                LEFT JOIN sub_activities sa ON t.sub_activity_id = sa.id
                LEFT JOIN users u_alloc ON t.allocated_to = u_alloc.id
                LEFT JOIN users u_creator ON t.created_by = u_creator.id
                WHERE 1=1";
        $params = [];

        // Role-based visibility
        if ($roleId == 3 && $userId) {
            // Employee sees assigned tickets or tickets created by them
            $sql .= " AND (t.allocated_to = ? OR t.created_by = ?)";
            $params[] = $userId;
            $params[] = $userId;
        } elseif ($roleId == 2 && $userId) {
            // Admin sees team tickets
            $sql .= " AND (t.allocated_to IN (SELECT id FROM users WHERE manager_id = ? OR id = ?) OR t.created_by = ?)";
            $params[] = $userId;
            $params[] = $userId;
            $params[] = $userId;
        }

        // Filters
        if (!empty($filters['status'])) {
            $sql .= " AND t.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['ticket_type'])) {
            $sql .= " AND t.ticket_type = ?";
            $params[] = $filters['ticket_type'];
        }
        if (!empty($filters['allocated_to'])) {
            $sql .= " AND t.allocated_to = ?";
            $params[] = $filters['allocated_to'];
        }
        if (!empty($filters['activity_id'])) {
            $sql .= " AND t.activity_id = ?";
            $params[] = $filters['activity_id'];
        }
        if (!empty($filters['search'])) {
            $searchTerm = "%" . $filters['search'] . "%";
            $sql .= " AND (t.ticket_number LIKE ? OR t.subject LIKE ? OR t.from_address LIKE ? OR t.agency_code LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY t.id DESC";
        return $this->fetchAll($sql, $params);
    }

    public function updateStatus(int $id, string $status, string $remarks, int $userId): bool {
        $oldTicket = $this->getTicketById($id);
        if (!$oldTicket) return false;

        $updateData = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($status === 'Completed' || $status === 'Closed') {
            $updateData['replied_by'] = $userId;
            $updateData['replied_datetime'] = date('Y-m-d H:i:s');
        }

        if (!empty($remarks)) {
            $updateData['remarks'] = $oldTicket['remarks'] ? $oldTicket['remarks'] . "\n[" . date('d M Y H:i') . "] " . $remarks : "[" . date('d M Y H:i') . "] " . $remarks;
        }

        $this->update('tickets', $updateData, "id = ?", [$id]);

        // Add Comment
        if (!empty($remarks)) {
            $this->addComment($id, $userId, "Status changed to {$status}. Remarks: {$remarks}");
        }

        $this->logAudit('Tickets', 'Update Status', ['status' => $oldTicket['status']], ['status' => $status]);

        // Notification
        if ($oldTicket['allocated_to']) {
            $this->createNotification($oldTicket['allocated_to'], "Ticket Updated", "Ticket {$oldTicket['ticket_number']} status changed to {$status}", "/tickets/view/{$id}");
        }

        return true;
    }

    public function addComment(int $ticketId, int $userId, string $comment): int {
        return $this->insert('ticket_comments', [
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'comment' => $comment,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getComments(int $ticketId): array {
        $sql = "SELECT c.*, u.full_name, u.user_code 
                FROM ticket_comments c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.ticket_id = ? 
                ORDER BY c.id ASC";
        return $this->fetchAll($sql, [$ticketId]);
    }

    public function holdTicket(int $ticketId, string $reason, ?string $expectedRelease, string $remarks, int $userId): bool {
        $this->update('tickets', ['status' => 'On Hold', 'pending_reason' => $reason], "id = ?", [$ticketId]);
        
        $this->insert('hold_history', [
            'ticket_id' => $ticketId,
            'hold_reason' => $reason,
            'hold_date' => date('Y-m-d H:i:s'),
            'expected_release_date' => $expectedRelease ?: null,
            'held_by' => $userId,
            'remarks' => $remarks,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->addComment($ticketId, $userId, "Ticket put ON HOLD. Reason: {$reason}");
        return true;
    }

    public function releaseTicket(int $ticketId, string $remarks, int $userId): bool {
        $this->update('tickets', ['status' => 'In Progress', 'pending_reason' => null], "id = ?", [$ticketId]);
        
        // Update last open hold entry
        $sql = "SELECT id FROM hold_history WHERE ticket_id = ? AND release_date IS NULL ORDER BY id DESC LIMIT 1";
        $hold = $this->fetchOne($sql, [$ticketId]);
        if ($hold) {
            $this->update('hold_history', [
                'release_date' => date('Y-m-d H:i:s'),
                'released_by' => $userId,
                'remarks' => $remarks
            ], "id = ?", [$hold['id']]);
        }

        $this->addComment($ticketId, $userId, "Ticket RELEASED from hold. Remarks: {$remarks}");
        return true;
    }

    public function getHoldHistory(int $ticketId): array {
        $sql = "SELECT h.*, u_hold.full_name as held_by_name, u_rel.full_name as released_by_name 
                FROM hold_history h 
                JOIN users u_hold ON h.held_by = u_hold.id 
                LEFT JOIN users u_rel ON h.released_by = u_rel.id 
                WHERE h.ticket_id = ? 
                ORDER BY h.id DESC";
        return $this->fetchAll($sql, [$ticketId]);
    }

    public function getDashboardStats(?int $userId = null, ?int $roleId = null): array {
        $where = " WHERE 1=1";
        $params = [];

        if ($roleId == 3 && $userId) {
            $where .= " AND (allocated_to = ? OR created_by = ?)";
            $params[] = $userId;
            $params[] = $userId;
        } elseif ($roleId == 2 && $userId) {
            $where .= " AND (allocated_to IN (SELECT id FROM users WHERE manager_id = ? OR id = ?) OR created_by = ?)";
            $params[] = $userId;
            $params[] = $userId;
            $params[] = $userId;
        }

        $total = $this->fetchOne("SELECT COUNT(*) as count FROM tickets {$where}", $params)['count'] ?? 0;
        $open = $this->fetchOne("SELECT COUNT(*) as count FROM tickets {$where} AND status NOT IN ('Completed', 'Closed', 'Cancelled')", $params)['count'] ?? 0;
        $closed = $this->fetchOne("SELECT COUNT(*) as count FROM tickets {$where} AND status IN ('Completed', 'Closed')", $params)['count'] ?? 0;
        $onHold = $this->fetchOne("SELECT COUNT(*) as count FROM tickets {$where} AND status = 'On Hold'", $params)['count'] ?? 0;

        // Overdue count
        $now = date('Y-m-d H:i:s');
        $overdueParams = array_merge($params, [$now]);
        $overdue = $this->fetchOne("SELECT COUNT(*) as count FROM tickets {$where} AND status NOT IN ('Completed', 'Closed', 'Cancelled') AND tat_datetime < ?", $overdueParams)['count'] ?? 0;

        return [
            'total' => $total,
            'open' => $open,
            'closed' => $closed,
            'on_hold' => $onHold,
            'overdue' => $overdue
        ];
    }

    public function createNotification(int $userId, string $title, string $message, ?string $link = null): int {
        return $this->insert('notifications', [
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
