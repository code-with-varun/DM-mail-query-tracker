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

    private function buildSlicerWhere(array $filters, ?int $userId = null, ?int $roleId = null, string $prefix = 't.'): array {
        $where = " WHERE 1=1";
        $params = [];

        if ($roleId == 3 && $userId) {
            $where .= " AND ({$prefix}allocated_to = ? OR {$prefix}created_by = ?)";
            $params[] = $userId;
            $params[] = $userId;
        } elseif ($roleId == 2 && $userId) {
            $where .= " AND ({$prefix}allocated_to IN (SELECT id FROM users WHERE manager_id = ? OR id = ?) OR {$prefix}created_by = ?)";
            $params[] = $userId;
            $params[] = $userId;
            $params[] = $userId;
        }

        if (!empty($filters['division_id'])) {
            $where .= " AND {$prefix}division_id = ?";
            $params[] = (int)$filters['division_id'];
        }
        if (!empty($filters['activity_id'])) {
            $where .= " AND {$prefix}activity_id = ?";
            $params[] = (int)$filters['activity_id'];
        }
        if (!empty($filters['allocated_to'])) {
            $where .= " AND {$prefix}allocated_to = ?";
            $params[] = (int)$filters['allocated_to'];
        }
        if (!empty($filters['priority'])) {
            $where .= " AND {$prefix}priority = ?";
            $params[] = $filters['priority'];
        }
        if (!empty($filters['start_date'])) {
            $where .= " AND DATE(COALESCE({$prefix}received_datetime, {$prefix}created_at)) >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $where .= " AND DATE(COALESCE({$prefix}received_datetime, {$prefix}created_at)) <= ?";
            $params[] = $filters['end_date'];
        }

        return [$where, $params];
    }

    public function getDashboardStats(?int $userId = null, ?int $roleId = null, array $filters = []): array {
        list($where, $params) = $this->buildSlicerWhere($filters, $userId, $roleId, '');

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

    public function getStatusBreakdown(?int $userId = null, ?int $roleId = null, array $filters = []): array {
        list($where, $params) = $this->buildSlicerWhere($filters, $userId, $roleId, '');
        $sql = "SELECT status, COUNT(*) as count FROM tickets {$where} GROUP BY status";
        return $this->fetchAll($sql, $params);
    }

    public function getDivisionBreakdown(?int $userId = null, ?int $roleId = null, array $filters = []): array {
        list($where, $params) = $this->buildSlicerWhere($filters, $userId, $roleId, 't.');
        $sql = "SELECT COALESCE(d.division_name, 'Unassigned') as division_name, COUNT(t.id) as count 
                FROM tickets t 
                LEFT JOIN divisions d ON t.division_id = d.id 
                {$where} GROUP BY d.division_name";
        return $this->fetchAll($sql, $params);
    }

    public function getMonthlyTrend(?int $userId = null, ?int $roleId = null, array $filters = []): array {
        list($where, $params) = $this->buildSlicerWhere($filters, $userId, $roleId, 't.');
        $sql = "SELECT DATE_FORMAT(t.created_at, '%b') as month_name, MONTH(t.created_at) as month_num, COUNT(t.id) as count 
                FROM tickets t 
                {$where} GROUP BY month_name, month_num ORDER BY month_num ASC";
        return $this->fetchAll($sql, $params);
    }

    public function getPriorityBreakdown(?int $userId = null, ?int $roleId = null, array $filters = []): array {
        list($where, $params) = $this->buildSlicerWhere($filters, $userId, $roleId, '');
        $sql = "SELECT priority, COUNT(*) as count FROM tickets {$where} GROUP BY priority";
        return $this->fetchAll($sql, $params);
    }

    public function getCategoryBreakdown(?int $userId = null, ?int $roleId = null, array $filters = []): array {
        list($where, $params) = $this->buildSlicerWhere($filters, $userId, $roleId, 't.');
        $sql = "SELECT COALESCE(c.category_name, 'General') as category_name, COUNT(t.id) as count 
                FROM tickets t 
                LEFT JOIN ticket_categories c ON t.category_id = c.id 
                {$where} GROUP BY c.category_name";
        return $this->fetchAll($sql, $params);
    }

    public function getEmployeeBreakdown(?int $userId = null, ?int $roleId = null, array $filters = []): array {
        list($where, $params) = $this->buildSlicerWhere($filters, $userId, $roleId, 't.');
        $sql = "SELECT COALESCE(u.full_name, 'Unassigned') as employee_name, COUNT(t.id) as count 
                FROM tickets t 
                LEFT JOIN users u ON t.allocated_to = u.id 
                {$where} GROUP BY u.full_name ORDER BY count DESC LIMIT 10";
        return $this->fetchAll($sql, $params);
    }

    public function getActivityBreakdown(?int $userId = null, ?int $roleId = null, array $filters = []): array {
        list($where, $params) = $this->buildSlicerWhere($filters, $userId, $roleId, 't.');
        $sql = "SELECT COALESCE(a.activity_name, 'General') as activity_name, COUNT(t.id) as count 
                FROM tickets t 
                LEFT JOIN activities a ON t.activity_id = a.id 
                {$where} GROUP BY a.activity_name ORDER BY count DESC LIMIT 10";
        return $this->fetchAll($sql, $params);
    }

    public function getTicketTypeBreakdown(?int $userId = null, ?int $roleId = null, array $filters = []): array {
        list($where, $params) = $this->buildSlicerWhere($filters, $userId, $roleId, 't.');
        $sql = "SELECT ticket_type, COUNT(t.id) as count 
                FROM tickets t 
                {$where} GROUP BY ticket_type";
        return $this->fetchAll($sql, $params);
    }

    public function getQualitySlaBreakdown(?int $userId = null, ?int $roleId = null, array $filters = []): array {
        list($where, $params) = $this->buildSlicerWhere($filters, $userId, $roleId, 't.');
        $now = date('Y-m-d H:i:s');
        
        $within = $this->fetchOne("SELECT COUNT(*) as count FROM tickets t {$where} AND status IN ('Completed', 'Closed') AND (replied_datetime IS NULL OR replied_datetime <= tat_datetime)", $params)['count'] ?? 0;
        $overdue = $this->fetchOne("SELECT COUNT(*) as count FROM tickets t {$where} AND status NOT IN ('Completed', 'Closed', 'Cancelled') AND tat_datetime < '{$now}'", $params)['count'] ?? 0;
        $onHold = $this->fetchOne("SELECT COUNT(*) as count FROM tickets t {$where} AND status = 'On Hold'", $params)['count'] ?? 0;
        $inTimeOpen = $this->fetchOne("SELECT COUNT(*) as count FROM tickets t {$where} AND status NOT IN ('Completed', 'Closed', 'Cancelled', 'On Hold') AND tat_datetime >= '{$now}'", $params)['count'] ?? 0;

        return [
            ['metric' => 'Within SLA', 'count' => $within],
            ['metric' => 'In-Time Open', 'count' => $inTimeOpen],
            ['metric' => 'On Hold', 'count' => $onHold],
            ['metric' => 'Overdue Breach', 'count' => $overdue]
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

    public function submitToChecker(int $ticketId, int $checkerId, string $remarks, int $userId): bool {
        $ticket = $this->getTicketById($ticketId);
        if (!$ticket) return false;

        $this->update('tickets', [
            'stage' => 'Checker Phase',
            'checker_id' => $checkerId,
            'updated_at' => date('Y-m-d H:i:s')
        ], "id = ?", [$ticketId]);

        $this->addComment($ticketId, $userId, "Maker completed work and submitted to Checker. Remarks: {$remarks}");
        $this->createNotification($checkerId, "Checker Review Requested", "Ticket {$ticket['ticket_number']} assigned to you for checker audit.", "/tickets/view/{$ticketId}");
        return true;
    }

    public function rejectByChecker(int $ticketId, string $errorCategory, string $errorDesc, string $errorType, string $solution, int $userId): bool {
        $ticket = $this->getTicketById($ticketId);
        if (!$ticket) return false;

        $this->update('tickets', [
            'stage' => 'Maker Phase',
            'status' => 'In Progress',
            'updated_at' => date('Y-m-d H:i:s')
        ], "id = ?", [$ticketId]);

        // Log to Error Tracker
        $this->insert('error_tracker', [
            'billing_month' => date('Y-m-01'),
            'checking_month' => date('Y-m-01'),
            'error_observation' => $errorCategory,
            'error_description' => $errorDesc,
            'resolution_solution' => $solution,
            'error_type' => in_array($errorType, ['Internal', 'External']) ? $errorType : 'Internal',
            'maker_id' => $ticket['allocated_to'],
            'checker_id' => $userId,
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->addComment($ticketId, $userId, "Checker Rejected File. Error: {$errorCategory} - {$errorDesc}. Retargeted to Maker Phase.");
        if ($ticket['allocated_to']) {
            $this->createNotification($ticket['allocated_to'], "Ticket Returned by Checker", "Checker identified error on ticket {$ticket['ticket_number']}. Please revise.", "/tickets/view/{$ticketId}");
        }
        return true;
    }

    public function approveByChecker(int $ticketId, string $remarks, int $userId): bool {
        $ticket = $this->getTicketById($ticketId);
        if (!$ticket) return false;

        $this->update('tickets', [
            'stage' => 'Delivery Phase',
            'updated_at' => date('Y-m-d H:i:s')
        ], "id = ?", [$ticketId]);

        $this->addComment($ticketId, $userId, "Checker Audit Approved. Ticket moved to Delivery Phase. Remarks: {$remarks}");
        if ($ticket['allocated_to']) {
            $this->createNotification($ticket['allocated_to'], "Checker Approved Ticket", "Ticket {$ticket['ticket_number']} approved by checker. Ready for delivery.", "/tickets/view/{$ticketId}");
        }
        return true;
    }

    public function completeDelivery(int $ticketId, ?string $attachmentPath, string $remarks, int $userId): bool {
        $ticket = $this->getTicketById($ticketId);
        if (!$ticket) return false;

        $this->update('tickets', [
            'stage' => 'Completed',
            'status' => 'Closed',
            'closure_attachment' => $attachmentPath,
            'replied_by' => $userId,
            'replied_datetime' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ], "id = ?", [$ticketId]);

        $delivNo = 'DEL-' . date('Ymd') . '-' . str_pad((string)$ticketId, 4, '0', STR_PAD_LEFT);
        $this->insert('delivery_tracker', [
            'delivery_number' => $delivNo,
            'ticket_id' => $ticketId,
            'delivered_to' => $ticket['from_address'] ?? 'N/A',
            'delivery_date' => date('Y-m-d H:i:s'),
            'delivery_mode' => 'Email',
            'ack_received' => 'Yes',
            'remarks' => $remarks ?: 'Delivered and closed.',
            'attachment_path' => $attachmentPath,
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->addComment($ticketId, $userId, "Ticket delivered and closed. Proof attached: " . ($attachmentPath ? basename($attachmentPath) : 'None') . ". Delivery Ref: {$delivNo}");
        return true;
    }

    public function syncToInputTracker(int $ticketId, int $userId): void {
        $ticket = $this->getTicketById($ticketId);
        if (!$ticket) return;

        $existing = $this->fetchOne("SELECT id FROM input_tracker WHERE document_reference = ?", [$ticket['ticket_number']]);
        if (!$existing) {
            $this->insert('input_tracker', [
                'source' => 'Mail Ticket',
                'received_date' => $ticket['received_datetime'] ?? date('Y-m-d H:i:s'),
                'received_from' => $ticket['from_address'] ?? 'N/A',
                'document_reference' => $ticket['ticket_number'],
                'assigned_to' => $ticket['allocated_to'],
                'status' => 'Received',
                'remarks' => $ticket['subject'] . ($ticket['remarks'] ? ' - ' . $ticket['remarks'] : ''),
                'created_by' => $userId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    public function rescheduleTicket(int $ticketId, string $scheduledDate, string $remarks, int $userId): bool {
        $ticket = $this->getTicketById($ticketId);
        if (!$ticket) return false;

        $this->update('tickets', [
            'scheduled_date' => $scheduledDate,
            'updated_at' => date('Y-m-d H:i:s')
        ], "id = ?", [$ticketId]);

        $this->addComment($ticketId, $userId, "Ticket scheduled date updated to {$scheduledDate}. Remarks: {$remarks}");
        return true;
    }

    public function reassignTicket(int $ticketId, int $newUserId, string $remarks, int $userId): bool {
        $ticket = $this->getTicketById($ticketId);
        if (!$ticket) return false;

        $this->update('tickets', [
            'allocated_to' => $newUserId,
            'status' => 'Assigned',
            'updated_at' => date('Y-m-d H:i:s')
        ], "id = ?", [$ticketId]);

        $this->addComment($ticketId, $userId, "Ticket reassigned to user ID {$newUserId}. Remarks: {$remarks}");
        $this->createNotification($newUserId, "Ticket Reassigned to You", "Ticket {$ticket['ticket_number']} has been reassigned to you.", "/tickets/view/{$ticketId}");
        return true;
    }

    public function getMyBucketTickets(int $userId, array $filters = []): array {
        $sql = "SELECT t.*, 
                       a.activity_name, sa.sub_activity_name,
                       u_alloc.full_name as allocated_user_name,
                       u_chk.full_name as checker_user_name
                FROM tickets t
                LEFT JOIN activities a ON t.activity_id = a.id
                LEFT JOIN sub_activities sa ON t.sub_activity_id = sa.id
                LEFT JOIN users u_alloc ON t.allocated_to = u_alloc.id
                LEFT JOIN users u_chk ON t.checker_id = u_chk.id
                WHERE (t.allocated_to = ? OR t.checker_id = ?) AND t.status NOT IN ('Closed', 'Cancelled')";
        $params = [$userId, $userId];

        if (!empty($filters['stage'])) {
            $sql .= " AND t.stage = ?";
            $params[] = $filters['stage'];
        }
        if (!empty($filters['search'])) {
            $searchTerm = "%" . $filters['search'] . "%";
            $sql .= " AND (t.ticket_number LIKE ? OR t.subject LIKE ? OR t.from_address LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY t.id DESC";
        return $this->fetchAll($sql, $params);
    }

    public function getRosterTickets(string $targetDate, ?int $userId = null, ?int $roleId = null): array {
        $sql = "SELECT t.*, 
                       a.activity_name, sa.sub_activity_name,
                       u_alloc.full_name as allocated_user_name
                FROM tickets t
                LEFT JOIN activities a ON t.activity_id = a.id
                LEFT JOIN sub_activities sa ON t.sub_activity_id = sa.id
                LEFT JOIN users u_alloc ON t.allocated_to = u_alloc.id
                WHERE 1=1";
        $params = [];

        // Role restriction
        if ($roleId == 3 && $userId) {
            $sql .= " AND (t.allocated_to = ? OR t.checker_id = ?)";
            $params[] = $userId;
            $params[] = $userId;
        }

        // Target Date or pending items scheduled on or before target date
        $sql .= " AND (t.scheduled_date = ? OR (t.scheduled_date < ? AND t.status NOT IN ('Completed', 'Closed', 'Cancelled')) OR (t.scheduled_date IS NULL AND DATE(t.created_at) <= ? AND t.status NOT IN ('Completed', 'Closed', 'Cancelled')))";
        $params[] = $targetDate;
        $params[] = $targetDate;
        $params[] = $targetDate;

        $sql .= " ORDER BY t.priority DESC, t.id DESC";
        return $this->fetchAll($sql, $params);
    }
}
