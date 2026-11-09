<?php
/**
 * Ticket Model (Mail Query & Task Tickets)
 */

class Ticket_model extends Model {

    public function __construct() {
        parent::__construct();
        try {
            $this->db->exec("ALTER TABLE tickets ADD COLUMN billing_month VARCHAR(20) NULL");
        } catch (Exception $e) {
            // Column already exists
        }
        try {
            $this->db->exec("ALTER TABLE tickets MODIFY COLUMN stage VARCHAR(100) NULL DEFAULT 'Maker Phase'");
        } catch (Exception $e) {}
        try {
            $cols = $this->fetchAll("SHOW COLUMNS FROM sub_activities LIKE 'enabled_queues'");
            if (empty($cols)) {
                $this->db->exec("ALTER TABLE `sub_activities` ADD COLUMN `enabled_queues` TEXT NULL");
            }
        } catch (Exception $e) {}
    }

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

    public function autoRolloverPendingTickets(): void {
        $today = date('Y-m-d');
        try {
            $sql = "UPDATE tickets 
                    SET scheduled_date = ? 
                    WHERE status NOT IN ('Completed', 'Closed', 'Cancelled') 
                    AND (scheduled_date IS NULL OR scheduled_date < ?)";
            $this->db->prepare($sql)->execute([$today, $today]);
        } catch (Exception $e) {
            // Silently ignore if table locked or execution issue
        }
    }

    public function createTicket(array $data): int {
        if (empty($data['ticket_number'])) {
            $data['ticket_number'] = $this->generateTicketNumber();
        }
        if (empty($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        if (empty($data['scheduled_date'])) {
            $data['scheduled_date'] = date('Y-m-d', strtotime($data['created_at']));
        }

        // Auto-determine Workflow based on Mail Category or Sub-Activity
        if (empty($data['workflow_id'])) {
            try {
                require_once __DIR__ . '/Workflow_model.php';
                $wfModel = new Workflow_model();
                $wf = $wfModel->getWorkflowForTicket($data['category_id'] ?? null, $data['sub_activity_id'] ?? null);
                if ($wf) {
                    $data['workflow_id'] = $wf['id'];
                    if (!empty($wf['stages'][0])) {
                        $data['current_stage_id'] = $wf['stages'][0]['id'];
                    }
                }
            } catch (Exception $e) {}
        }

        $ticketId = $this->insert('tickets', $data);

        $this->syncTicketToLifecycle($ticketId);

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
                       a.activity_name, sa.sub_activity_name, sa.default_tat_hours, sa.enabled_queues,
                       tc.category_name, tc.workflow_id as category_workflow_id,
                       u_alloc.full_name as allocated_user_name, u_alloc.email as allocated_user_email, u_alloc.user_code as allocated_user_code,
                       u_checker.full_name as checker_user_name, u_checker.email as checker_user_email, u_checker.user_code as checker_user_code,
                       u_creator.full_name as creator_name, u_creator.user_code as creator_user_code,
                       u_replied.full_name as replied_user_name, u_replied.user_code as replied_user_code
                FROM tickets t
                LEFT JOIN divisions d ON t.division_id = d.id
                LEFT JOIN activities a ON t.activity_id = a.id
                LEFT JOIN sub_activities sa ON t.sub_activity_id = sa.id
                LEFT JOIN ticket_categories tc ON t.category_id = tc.id
                LEFT JOIN users u_alloc ON t.allocated_to = u_alloc.id
                LEFT JOIN users u_checker ON t.checker_id = u_checker.id
                LEFT JOIN users u_creator ON t.created_by = u_creator.id
                LEFT JOIN users u_replied ON t.replied_by = u_replied.id
                WHERE t.id = ? LIMIT 1";
        return $this->fetchOne($sql, [$id]);
    }

    public function getTickets(array $filters = [], ?int $userId = null, ?int $roleId = null): array {
        $this->autoRolloverPendingTickets();

        $sql = "SELECT t.*, 
                       tc.category_name,
                       a.activity_name, sa.sub_activity_name,
                       u_alloc.full_name as allocated_user_name, u_alloc.user_code as allocated_user_code,
                       u_chk.full_name as checker_user_name, u_chk.user_code as checker_user_code,
                       u_creator.full_name as creator_name
                FROM tickets t
                LEFT JOIN ticket_categories tc ON t.category_id = tc.id
                LEFT JOIN activities a ON t.activity_id = a.id
                LEFT JOIN sub_activities sa ON t.sub_activity_id = sa.id
                LEFT JOIN users u_alloc ON t.allocated_to = u_alloc.id
                LEFT JOIN users u_chk ON t.checker_id = u_chk.id
                LEFT JOIN users u_creator ON t.created_by = u_creator.id
                WHERE 1=1";
        $params = [];

        // Role-based visibility
        if ($roleId == 5 && $userId) {
            // Employee sees assigned tickets (maker or checker) or tickets created by them
            $sql .= " AND (t.allocated_to = ? OR t.checker_id = ? OR t.created_by = ?)";
            $params[] = $userId;
            $params[] = $userId;
            $params[] = $userId;
        } elseif (($roleId == 3 || $roleId == 4) && $userId) {
            // Manager & Team Lead see team/project tickets
            $sql .= " AND (t.allocated_to IN (SELECT id FROM users WHERE manager_id = ? OR id = ?) OR t.checker_id = ? OR t.created_by = ?)";
            $params[] = $userId;
            $params[] = $userId;
            $params[] = $userId;
            $params[] = $userId;
        }

        // Filters
        if (!empty($filters['status'])) {
            $statuses = (array)$filters['status'];
            if (!empty($statuses)) {
                $placeholders = implode(',', array_fill(0, count($statuses), '?'));
                $sql .= " AND t.status IN ({$placeholders})";
                foreach ($statuses as $st) {
                    $params[] = $st;
                }
            }
        }
        if (!empty($filters['activity_id'])) {
            $acts = array_filter(array_map('intval', (array)$filters['activity_id']));
            if (!empty($acts)) {
                $placeholders = implode(',', array_fill(0, count($acts), '?'));
                $sql .= " AND t.activity_id IN ({$placeholders})";
                foreach ($acts as $aid) {
                    $params[] = $aid;
                }
            }
        }
        if (!empty($filters['allocated_to'])) {
            $users = array_filter(array_map('intval', (array)$filters['allocated_to']));
            if (!empty($users)) {
                $placeholders = implode(',', array_fill(0, count($users), '?'));
                $sql .= " AND t.allocated_to IN ({$placeholders})";
                foreach ($users as $uid) {
                    $params[] = $uid;
                }
            }
        }
        if (!empty($filters['ticket_type'])) {
            $sql .= " AND t.ticket_type = ?";
            $params[] = $filters['ticket_type'];
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

        if ($roleId == 4 && $userId) {
            // Employee: Personal workload only
            $where .= " AND ({$prefix}allocated_to = ? OR {$prefix}created_by = ? OR {$prefix}checker_id = ?)";
            $params[] = $userId;
            $params[] = $userId;
            $params[] = $userId;
        } elseif (($roleId == 2 || $roleId == 3) && $userId) {
            // Admin (TL) / Manager: Team level visibility
            $where .= " AND ({$prefix}allocated_to IN (SELECT id FROM users WHERE manager_id = ? OR id = ?) OR {$prefix}created_by = ? OR {$prefix}checker_id = ?)";
            $params[] = $userId;
            $params[] = $userId;
            $params[] = $userId;
            $params[] = $userId;
        }
        // Role 1 (Super Admin) & Role 5 (Senior Management): Full organization-wide analytics (no restriction)

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

    /**
     * Helper to resolve the next workflow stage for a ticket
     */
    public function getNextWorkflowStageForTicket(array $ticket, string $action = 'next'): array {
        $wfId = $ticket['workflow_id'] ?? null;
        if (!$wfId && !empty($ticket['category_id'])) {
            $cat = $this->fetchOne("SELECT workflow_id FROM ticket_categories WHERE id = ?", [$ticket['category_id']]);
            $wfId = $cat['workflow_id'] ?? null;
        }

        if ($wfId) {
            $stages = $this->fetchAll("SELECT * FROM workflow_stages WHERE workflow_id = ? ORDER BY stage_order ASC", [$wfId]);
            if (!empty($stages)) {
                $curIdx = 0;
                $found = false;

                if (!empty($ticket['current_stage_id'])) {
                    foreach ($stages as $i => $stg) {
                        if ((int)$stg['id'] === (int)$ticket['current_stage_id']) {
                            $curIdx = $i;
                            $found = true;
                            break;
                        }
                    }
                }

                if (!$found && !empty($ticket['stage'])) {
                    foreach ($stages as $i => $stg) {
                        if (strcasecmp(trim($stg['stage_name']), trim($ticket['stage'])) === 0) {
                            $curIdx = $i;
                            $found = true;
                            break;
                        }
                    }
                }

                $nextIdx = $curIdx;

                if ($action === 'checker') {
                    // Find first Checker stage at or after current index
                    for ($i = $curIdx + 1; $i < count($stages); $i++) {
                        if (($stages[$i]['queue_type'] ?? '') === 'Checker') {
                            $nextIdx = $i;
                            break;
                        }
                    }
                    if ($nextIdx === $curIdx && isset($stages[$curIdx + 1])) {
                        $nextIdx = $curIdx + 1;
                    }
                } elseif ($action === 'maker' || $action === 'rework') {
                    // Find Maker or Rework stage
                    for ($i = 0; $i < count($stages); $i++) {
                        if (in_array($stages[$i]['queue_type'] ?? '', ['Maker', 'Rework'])) {
                            $nextIdx = $i;
                            break;
                        }
                    }
                } elseif ($action === 'delivery') {
                    for ($i = $curIdx + 1; $i < count($stages); $i++) {
                        if (($stages[$i]['queue_type'] ?? '') === 'Delivery') {
                            $nextIdx = $i;
                            break;
                        }
                    }
                    if ($nextIdx === $curIdx && isset($stages[$curIdx + 1])) {
                        $nextIdx = $curIdx + 1;
                    }
                } else {
                    // Next consecutive stage
                    $nextIdx = min($curIdx + 1, count($stages) - 1);
                }

                $targetStage = $stages[$nextIdx];
                $isClosed = ($targetStage['queue_type'] ?? '') === 'Closed' || ($nextIdx === count($stages) - 1 && $action === 'next');

                return [
                    'stage_id'   => $targetStage['id'],
                    'stage_name' => $targetStage['stage_name'],
                    'queue_type' => $targetStage['queue_type'] ?? 'Maker',
                    'is_closed'  => $isClosed
                ];
            }
        }

        // Fallback to legacy master stage calculation
        $nextStageName = $this->getNextEnabledStage($ticket['stage'] ?? 'Maker Phase', $ticket['enabled_queues'] ?? null);
        return [
            'stage_id'   => null,
            'stage_name' => $nextStageName,
            'queue_type' => ($nextStageName === 'Closed') ? 'Closed' : 'Maker',
            'is_closed'  => ($nextStageName === 'Closed')
        ];
    }

    public function submitToChecker(int $ticketId, int $checkerId, string $remarks, int $userId): bool {
        $ticket = $this->getTicketById($ticketId);
        if (!$ticket) return false;

        $nextStg = $this->getNextWorkflowStageForTicket($ticket, 'checker');

        $this->update('tickets', [
            'stage' => $nextStg['stage_name'],
            'current_stage_id' => $nextStg['stage_id'],
            'checker_id' => $checkerId,
            'status' => 'In Progress',
            'updated_at' => date('Y-m-d H:i:s')
        ], "id = ?", [$ticketId]);

        $this->addComment($ticketId, $userId, "Maker completed work and submitted to Checker ({$nextStg['stage_name']}). Remarks: {$remarks}");
        $this->createNotification($checkerId, "Checker Review Requested", "Ticket {$ticket['ticket_number']} assigned to you for checker audit.", "/tickets/view/{$ticketId}");
        return true;
    }

    public function rejectByChecker(
        int $ticketId, 
        string $errorCategory, 
        string $errorDesc, 
        string $errorType, 
        string $solution, 
        int $userId,
        string $billingMonth = '',
        string $checkingMonth = '',
        ?int $makerId = null,
        ?int $checkerId = null
    ): bool {
        $ticket = $this->getTicketById($ticketId);
        if (!$ticket) return false;

        $nextStg = $this->getNextWorkflowStageForTicket($ticket, 'maker');

        $this->update('tickets', [
            'stage' => $nextStg['stage_name'],
            'current_stage_id' => $nextStg['stage_id'],
            'status' => 'In Progress',
            'updated_at' => date('Y-m-d H:i:s')
        ], "id = ?", [$ticketId]);

        $bMonth = !empty($billingMonth) ? date('Y-m-01', strtotime($billingMonth)) : date('Y-m-01');
        $cMonth = !empty($checkingMonth) ? date('Y-m-01', strtotime($checkingMonth)) : date('Y-m-01');
        $finalMaker = $makerId ?: $ticket['allocated_to'];
        $finalChecker = $checkerId ?: $userId;

        // Log to Error Tracker
        $this->insert('error_tracker', [
            'billing_month' => $bMonth,
            'checking_month' => $cMonth,
            'error_observation' => $errorCategory,
            'error_description' => $errorDesc,
            'resolution_solution' => $solution,
            'error_type' => in_array($errorType, ['Internal', 'External']) ? $errorType : 'Internal',
            'maker_id' => $finalMaker,
            'checker_id' => $finalChecker,
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->addComment($ticketId, $userId, "Checker Rejected File. Error: {$errorCategory} - {$errorDesc}. Retargeted to {$nextStg['stage_name']}.");
        if ($ticket['allocated_to']) {
            $this->createNotification($ticket['allocated_to'], "Ticket Returned by Checker", "Checker identified error on ticket {$ticket['ticket_number']}. Please revise.", "/tickets/view/{$ticketId}");
        }
        return true;
    }

    public function approveByChecker(int $ticketId, string $remarks, int $userId): bool {
        $ticket = $this->getTicketById($ticketId);
        if (!$ticket) return false;

        $nextStg = $this->getNextWorkflowStageForTicket($ticket, 'delivery');

        $this->update('tickets', [
            'stage' => $nextStg['stage_name'],
            'current_stage_id' => $nextStg['stage_id'],
            'status' => 'In Progress',
            'updated_at' => date('Y-m-d H:i:s')
        ], "id = ?", [$ticketId]);

        $this->addComment($ticketId, $userId, "Checker Audit Approved. Ticket moved to {$nextStg['stage_name']}. Remarks: {$remarks}");
        if ($ticket['allocated_to']) {
            $this->createNotification($ticket['allocated_to'], "Checker Approved Ticket", "Ticket {$ticket['ticket_number']} approved by checker. Ready for next phase.", "/tickets/view/{$ticketId}");
        }
        return true;
    }

    public function getNextEnabledStage(string $currentStage, ?string $enabledQueuesJson = null): string {
        $masterOrderedStages = [
            1  => 'Input Received',
            2  => 'Maker Queue',
            3  => 'Checker Queue',
            4  => 'Error Log',
            5  => 'Maker Checker Rework',
            6  => 'Delivery',
            7  => 'Acknowledgement',
            8  => 'Approval Queue',
            9  => 'Uploader Queue',
            10 => 'Dump Sharing',
            11 => 'Consol Vendor Tracker',
            12 => 'Closed'
        ];

        $stageMap = [
            'Input Received'        => 1,
            'Maker Phase'           => 2,
            'Maker Queue'           => 2,
            'Checker Phase'         => 3,
            'Checker Queue'         => 3,
            'Error Log'             => 4,
            'Maker Checker Rework'  => 5,
            'Delivery Phase'        => 6,
            'Delivery'              => 6,
            'Acknowledgement'       => 7,
            'Approval Queue'        => 8,
            'Uploader Queue'        => 9,
            'Dump Sharing'          => 10,
            'Consol Vendor Tracker' => 11,
            'Closed'                => 12,
            'Completed'             => 12,
        ];

        $currentIdx = $stageMap[$currentStage] ?? 2;
        $enabledList = !empty($enabledQueuesJson) ? json_decode($enabledQueuesJson, true) : null;

        for ($idx = $currentIdx + 1; $idx <= 12; $idx++) {
            $stageName = $masterOrderedStages[$idx];
            if ($enabledList === null || !is_array($enabledList) || in_array($stageName, $enabledList)) {
                return $stageName;
            }
        }
        return 'Closed';
    }

    public function completeDelivery(int $ticketId, ?string $attachmentPath, string $remarks, int $userId): bool {
        $ticket = $this->getTicketById($ticketId);
        if (!$ticket) return false;

        $nextStg = $this->getNextWorkflowStageForTicket($ticket, 'next');

        $updateData = [
            'stage' => $nextStg['stage_name'],
            'current_stage_id' => $nextStg['stage_id'],
            'closure_attachment' => $attachmentPath ?: ($ticket['closure_attachment'] ?? null),
            'replied_by' => $userId,
            'replied_datetime' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($nextStg['is_closed']) {
            $updateData['status'] = 'Closed';
        } else {
            $updateData['status'] = 'In Progress';
        }

        $this->update('tickets', $updateData, "id = ?", [$ticketId]);

        $delivNo = 'DEL-' . date('Ymd') . '-' . str_pad((string)$ticketId, 4, '0', STR_PAD_LEFT);
        $this->insert('delivery_tracker', [
            'delivery_number' => $delivNo,
            'ticket_id' => $ticketId,
            'delivered_to' => $ticket['from_address'] ?? 'N/A',
            'delivery_date' => date('Y-m-d H:i:s'),
            'delivery_mode' => 'Email',
            'ack_received' => 'Yes',
            'remarks' => $remarks ?: 'Delivered and logged in Delivery Tracker.',
            'attachment_path' => $attachmentPath,
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $statusMsg = ($nextStg['is_closed']) ? "Ticket delivered and closed. Delivery Ref: {$delivNo}" : "Ticket delivered and logged in Delivery Tracker (Ref: {$delivNo}). Progressed to next queue: [{$nextStg['stage_name']}].";
        $this->addComment($ticketId, $userId, $statusMsg);
        return true;
    }

    public function advanceToNextQueue(int $ticketId, string $currentStage, ?string $attachmentPath, string $remarks, int $userId): string {
        $ticket = $this->getTicketById($ticketId);
        if (!$ticket) return 'Closed';

        $nextStg = $this->getNextWorkflowStageForTicket($ticket, 'next');

        $updateData = [
            'stage' => $nextStg['stage_name'],
            'current_stage_id' => $nextStg['stage_id'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($attachmentPath) {
            $updateData['closure_attachment'] = $attachmentPath;
        }

        if ($nextStg['is_closed']) {
            $updateData['status'] = 'Closed';
            $updateData['replied_by'] = $userId;
            $updateData['replied_datetime'] = date('Y-m-d H:i:s');
        } else {
            $updateData['status'] = 'In Progress';
        }

        $this->update('tickets', $updateData, "id = ?", [$ticketId]);

        $statusMsg = ($nextStg['is_closed']) ? "Queue action [{$currentStage}] completed! Ticket fully closed & archived. Remarks: {$remarks}" : "Queue action [{$currentStage}] completed! Ticket progressed to next queue: [{$nextStg['stage_name']}]. Remarks: {$remarks}";
        $this->addComment($ticketId, $userId, $statusMsg);

        return $nextStg['stage_name'];
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
        $this->autoRolloverPendingTickets();

        $sql = "SELECT t.*, 
                       tc.category_name,
                       a.activity_name, sa.sub_activity_name,
                       u_alloc.full_name as allocated_user_name, u_alloc.user_code as allocated_user_code,
                       u_chk.full_name as checker_user_name, u_chk.user_code as checker_user_code,
                       u_creator.full_name as creator_name, u_creator.user_code as creator_user_code
                FROM tickets t
                LEFT JOIN ticket_categories tc ON t.category_id = tc.id
                LEFT JOIN activities a ON t.activity_id = a.id
                LEFT JOIN sub_activities sa ON t.sub_activity_id = sa.id
                LEFT JOIN users u_alloc ON t.allocated_to = u_alloc.id
                LEFT JOIN users u_chk ON t.checker_id = u_chk.id
                LEFT JOIN users u_creator ON t.created_by = u_creator.id
                WHERE (t.allocated_to = ? OR t.checker_id = ? OR t.created_by = ?)";
        $params = [$userId, $userId, $userId];

        // Multi-select status filter
        if (!empty($filters['status'])) {
            $statuses = (array)$filters['status'];
            $placeholders = implode(',', array_fill(0, count($statuses), '?'));
            $sql .= " AND t.status IN ({$placeholders})";
            foreach ($statuses as $st) {
                $params[] = $st;
            }
        } else {
            // Default active tickets if no status filter selected
            $sql .= " AND t.status NOT IN ('Closed', 'Cancelled')";
        }

        // Multi-select stage filter with stage alias expansion
        if (!empty($filters['stage'])) {
            $stages = (array)$filters['stage'];
            $stageAliasMap = [
                'Input Received'        => ['Input Received', '1'],
                'Maker Phase'           => ['Maker Phase', 'Maker Queue', 'In Progress', 'Assigned', 'New', ''],
                'Checker Phase'         => ['Checker Phase', 'Checker Queue', 'Error Log', 'Maker Checker Rework'],
                'Delivery Phase'        => ['Delivery Phase', 'Delivery'],
                'Acknowledgement'       => ['Acknowledgement'],
                'Approval Queue'        => ['Approval Queue'],
                'Uploader Queue'        => ['Uploader Queue'],
                'Dump Sharing'          => ['Dump Sharing'],
                'Consol Vendor Tracker' => ['Consol Vendor Tracker'],
                'Closed'                => ['Closed', 'Completed'],
            ];

            $expanded = [];
            foreach ($stages as $stg) {
                if (isset($stageAliasMap[$stg])) {
                    $expanded = array_merge($expanded, $stageAliasMap[$stg]);
                } else {
                    $expanded[] = $stg;
                }
            }
            $expanded = array_unique($expanded);
            $includeNull = in_array('Maker Phase', $stages) || count($stages) >= count($stageAliasMap);
            
            $placeholders = implode(',', array_fill(0, count($expanded), '?'));
            if ($includeNull) {
                $sql .= " AND (t.stage IN ({$placeholders}) OR t.stage IS NULL OR t.stage = '')";
            } else {
                $sql .= " AND t.stage IN ({$placeholders})";
            }
            foreach ($expanded as $stgVal) {
                $params[] = $stgVal;
            }
        }

        // Multi-select priority filter
        if (!empty($filters['priority'])) {
            $priorities = (array)$filters['priority'];
            $placeholders = implode(',', array_fill(0, count($priorities), '?'));
            $sql .= " AND t.priority IN ({$placeholders})";
            foreach ($priorities as $p) {
                $params[] = $p;
            }
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

    public function getRosterTickets(string $targetDate, ?int $userId = null, ?int $roleId = null, array $filters = []): array {
        $sql = "SELECT t.*, 
                       tc.category_name,
                       a.activity_name, sa.sub_activity_name,
                       u_alloc.full_name as allocated_user_name, u_alloc.user_code as allocated_user_code,
                       u_chk.full_name as checker_user_name, u_chk.user_code as checker_user_code
                FROM tickets t
                LEFT JOIN ticket_categories tc ON t.category_id = tc.id
                LEFT JOIN activities a ON t.activity_id = a.id
                LEFT JOIN sub_activities sa ON t.sub_activity_id = sa.id
                LEFT JOIN users u_alloc ON t.allocated_to = u_alloc.id
                LEFT JOIN users u_chk ON t.checker_id = u_chk.id
                WHERE 1=1";
        $params = [];

        // Role restriction
        if ($roleId == 3 && $userId) {
            $sql .= " AND (t.allocated_to = ? OR t.checker_id = ?)";
            $params[] = $userId;
            $params[] = $userId;
        }

        // Target Date logic (Includes closed/completed tasks on target date)
        if ($targetDate === date('Y-m-d')) {
            $sql .= " AND (t.scheduled_date = ? OR (t.scheduled_date < ? AND t.status NOT IN ('Completed', 'Closed', 'Cancelled')) OR (t.scheduled_date IS NULL AND DATE(t.created_at) <= ? AND t.status NOT IN ('Completed', 'Closed', 'Cancelled')) OR (DATE(t.updated_at) = ? AND t.status IN ('Completed', 'Closed')))";
            $params[] = $targetDate;
            $params[] = $targetDate;
            $params[] = $targetDate;
            $params[] = $targetDate;
        } else {
            $sql .= " AND (t.scheduled_date = ? OR DATE(t.updated_at) = ? OR (DATE(t.created_at) = ? AND t.status IN ('Completed', 'Closed')))";
            $params[] = $targetDate;
            $params[] = $targetDate;
            $params[] = $targetDate;
        }

        // Multi-select filters
        if (!empty($filters['status'])) {
            $statuses = (array)$filters['status'];
            if (!empty($statuses)) {
                $placeholders = implode(',', array_fill(0, count($statuses), '?'));
                $sql .= " AND t.status IN ({$placeholders})";
                foreach ($statuses as $st) {
                    $params[] = $st;
                }
            }
        }

        if (!empty($filters['stage'])) {
            $stages = (array)$filters['stage'];
            if (!empty($stages)) {
                $stageAliasMap = [
                    'Input Received'        => ['Input Received', '1'],
                    'Maker Phase'           => ['Maker Phase', 'Maker Queue', 'In Progress', 'Assigned', 'New', ''],
                    'Checker Phase'         => ['Checker Phase', 'Checker Queue', 'Error Log', 'Maker Checker Rework'],
                    'Delivery Phase'        => ['Delivery Phase', 'Delivery'],
                    'Acknowledgement'       => ['Acknowledgement'],
                    'Approval Queue'        => ['Approval Queue'],
                    'Uploader Queue'        => ['Uploader Queue'],
                    'Dump Sharing'          => ['Dump Sharing'],
                    'Consol Vendor Tracker' => ['Consol Vendor Tracker'],
                    'Closed'                => ['Closed', 'Completed'],
                ];

                $expanded = [];
                foreach ($stages as $stg) {
                    if (isset($stageAliasMap[$stg])) {
                        $expanded = array_merge($expanded, $stageAliasMap[$stg]);
                    } else {
                        $expanded[] = $stg;
                    }
                }
                $expanded = array_unique($expanded);
                $includeNull = in_array('Maker Phase', $stages);

                $placeholders = implode(',', array_fill(0, count($expanded), '?'));
                if ($includeNull) {
                    $sql .= " AND (t.stage IN ({$placeholders}) OR t.stage IS NULL OR t.stage = '')";
                } else {
                    $sql .= " AND t.stage IN ({$placeholders})";
                }
                foreach ($expanded as $stgVal) {
                    $params[] = $stgVal;
                }
            }
        }

        if (!empty($filters['priority'])) {
            $prios = (array)$filters['priority'];
            if (!empty($prios)) {
                $placeholders = implode(',', array_fill(0, count($prios), '?'));
                $sql .= " AND t.priority IN ({$placeholders})";
                foreach ($prios as $p) {
                    $params[] = $p;
                }
            }
        }

        if (!empty($filters['search'])) {
            $searchTerm = "%" . $filters['search'] . "%";
            $sql .= " AND (t.ticket_number LIKE ? OR t.subject LIKE ? OR t.from_address LIKE ? OR t.agency_code LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY t.priority DESC, t.id DESC";
        return $this->fetchAll($sql, $params);
    }

    public function getQueryTickets(array $filters = [], ?int $userId = null, ?int $roleId = null): array {
        $this->autoRolloverPendingTickets();

        $sql = "SELECT t.*, 
                       COALESCE(tc.category_name, 'Query') as category_name,
                       a.activity_name, sa.sub_activity_name,
                       u_alloc.full_name as allocated_user_name, u_alloc.user_code as allocated_user_code,
                       u_chk.full_name as checker_user_name, u_chk.user_code as checker_user_code,
                       u_creator.full_name as creator_name,
                       u_replied.full_name as replied_user_name
                FROM tickets t
                LEFT JOIN ticket_categories tc ON t.category_id = tc.id
                LEFT JOIN activities a ON t.activity_id = a.id
                LEFT JOIN sub_activities sa ON t.sub_activity_id = sa.id
                LEFT JOIN users u_alloc ON t.allocated_to = u_alloc.id
                LEFT JOIN users u_chk ON t.checker_id = u_chk.id
                LEFT JOIN users u_creator ON t.created_by = u_creator.id
                LEFT JOIN users u_replied ON t.replied_by = u_replied.id
                WHERE (t.category_id = 2 OR t.category_id = 9 OR t.category_id IS NULL OR t.category_id = 0 OR tc.category_name LIKE '%Query%')
                  AND (tc.category_name IS NULL OR tc.category_name NOT IN ('Penalty', 'Hold Release', 'Input', 'Checklist', 'Rerun', 'Allocation Exclude', 'Grid'))
                  AND (t.ticket_type = 'Query Ticket' OR t.ticket_type = 'Query' OR t.ticket_type IS NULL OR t.ticket_type NOT IN ('Task Ticket', 'Internal Task', 'File Preparation'))";

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

        if (!empty($filters['status'])) {
            $statuses = (array)$filters['status'];
            if (!empty($statuses)) {
                $placeholders = implode(',', array_fill(0, count($statuses), '?'));
                $sql .= " AND t.status IN ({$placeholders})";
                foreach ($statuses as $st) {
                    $params[] = $st;
                }
            }
        }
        if (!empty($filters['activity_id'])) {
            $acts = array_filter(array_map('intval', (array)$filters['activity_id']));
            if (!empty($acts)) {
                $placeholders = implode(',', array_fill(0, count($acts), '?'));
                $sql .= " AND t.activity_id IN ({$placeholders})";
                foreach ($acts as $aid) {
                    $params[] = $aid;
                }
            }
        }
        if (!empty($filters['allocated_to'])) {
            $users = array_filter(array_map('intval', (array)$filters['allocated_to']));
            if (!empty($users)) {
                $placeholders = implode(',', array_fill(0, count($users), '?'));
                $sql .= " AND t.allocated_to IN ({$placeholders})";
                foreach ($users as $uid) {
                    $params[] = $uid;
                }
            }
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

    public function getQueryTrackerStats(array $filters = [], ?int $userId = null, ?int $roleId = null): array {
        $tickets = $this->getQueryTickets($filters, $userId, $roleId);
        $total = count($tickets);
        $open = 0;
        $inProgress = 0;
        $pending = 0;
        $closed = 0;
        $overdue = 0;
        $now = date('Y-m-d H:i:s');

        foreach ($tickets as $t) {
            $status = $t['status'] ?? 'New';
            if ($status === 'New' || $status === 'Assigned') {
                $open++;
            } elseif ($status === 'In Progress') {
                $inProgress++;
            } elseif ($status === 'Pending' || $status === 'Waiting for Customer' || $status === 'Waiting for Internal Team' || $status === 'On Hold') {
                $pending++;
            } elseif ($status === 'Completed' || $status === 'Closed') {
                $closed++;
            }
            if (!in_array($status, ['Completed', 'Closed', 'Cancelled']) && !empty($t['tat_datetime']) && $t['tat_datetime'] < $now) {
                $overdue++;
            }
        }

        return [
            'total' => $total,
            'open' => $open,
            'in_progress' => $inProgress,
            'pending' => $pending,
            'closed' => $closed,
            'overdue' => $overdue
        ];
    }

    public function syncTicketToLifecycle(int $ticketId): void {
        try {
            $ticket = $this->getTicketById($ticketId);
            if (!$ticket || empty($ticket['sub_activity_id'])) return;

            $bMonth = !empty($ticket['billing_month']) ? $ticket['billing_month'] : date('Y-m', strtotime($ticket['created_at'] ?? 'now'));
            $ticketType = !empty($ticket['ticket_type']) ? $ticket['ticket_type'] : 'File Preparation';

            $stageMap = [
                'Input Received'        => 'Input Received',
                'Maker Phase'           => 'Maker Queue',
                'Maker Queue'           => 'Maker Queue',
                'In Progress'           => 'Maker Queue',
                'Assigned'              => 'Maker Queue',
                'Checker Phase'         => 'Checker Queue',
                'Checker Queue'         => 'Checker Queue',
                'Error Log'             => 'Error Log',
                'Maker Checker Rework'  => 'Maker Checker Rework',
                'Delivery Phase'        => 'Delivery',
                'Delivery'              => 'Delivery',
                'Acknowledgement'       => 'Acknowledgement',
                'Approval Queue'        => 'Approval Queue',
                'Uploader Queue'        => 'Uploader Queue',
                'Dump Sharing'          => 'Dump Sharing',
                'Consol Vendor Tracker' => 'Consol Vendor Tracker',
                'Completed'             => 'Closed',
                'Closed'                => 'Closed',
            ];

            $currentStage = $stageMap[$ticket['stage'] ?? ''] ?? ($stageMap[$ticket['status'] ?? ''] ?? 'Maker Queue');

            $lfc = $this->fetchOne(
                "SELECT id FROM ticket_lifecycle_tracker WHERE ticket_id = ? OR (sub_activity_id = ? AND billing_month = ? AND (ticket_id IS NULL OR ticket_id = 0)) LIMIT 1",
                [$ticketId, $ticket['sub_activity_id'], $bMonth]
            );

            $payload = [
                'ticket_id' => $ticketId,
                'ticket_number' => $ticket['ticket_number'],
                'billing_month' => $bMonth,
                'ticket_type' => $ticketType,
                'division_id' => $ticket['division_id'] ?? null,
                'activity_id' => $ticket['activity_id'],
                'sub_activity_id' => $ticket['sub_activity_id'],
                'division_name' => $ticket['division_name'] ?? 'General Operations',
                'activity_name' => $ticket['activity_name'] ?? '',
                'sub_activity_name' => $ticket['sub_activity_name'] ?? '',
                'maker_id' => $ticket['allocated_to'] ?? null,
                'checker_id' => $ticket['checker_id'] ?? null,
                'current_stage' => $currentStage,
                'remarks' => $ticket['remarks'] ?? null
            ];

            if ($currentStage === 'Closed') {
                $payload['closed_at'] = date('Y-m-d H:i:s');
            }

            if ($lfc) {
                $this->update('ticket_lifecycle_tracker', $payload, "id = ?", [$lfc['id']]);
            } else {
                $payload['input_received_at'] = $ticket['received_datetime'] ?? date('Y-m-d H:i:s');
                $payload['created_at'] = $ticket['created_at'] ?? date('Y-m-d H:i:s');
                $this->insert('ticket_lifecycle_tracker', $payload);
            }
        } catch (Exception $e) {
            // Silently catch sync error
        }
    }
}
