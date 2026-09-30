<?php
/**
 * Reports & Analytics Model
 */

class Report_model extends Model {
    public function getFilteredTickets(array $filters): array {
        $sql = "SELECT t.*, 
                       d.division_name,
                       a.activity_name, sa.sub_activity_name,
                       u_alloc.full_name as allocated_user_name,
                       u_creator.full_name as creator_name
                FROM tickets t
                LEFT JOIN divisions d ON t.division_id = d.id
                LEFT JOIN activities a ON t.activity_id = a.id
                LEFT JOIN sub_activities sa ON t.sub_activity_id = sa.id
                LEFT JOIN users u_alloc ON t.allocated_to = u_alloc.id
                LEFT JOIN users u_creator ON t.created_by = u_creator.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(t.created_at) >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(t.created_at) <= ?";
            $params[] = $filters['end_date'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND t.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['activity_id'])) {
            $sql .= " AND t.activity_id = ?";
            $params[] = $filters['activity_id'];
        }
        if (!empty($filters['allocated_to'])) {
            $sql .= " AND t.allocated_to = ?";
            $params[] = $filters['allocated_to'];
        }

        $sql .= " ORDER BY t.id DESC";
        return $this->fetchAll($sql, $params);
    }

    public function getEmployeePerformanceReport(): array {
        $sql = "SELECT u.id, u.full_name, u.user_code, u.department,
                       COUNT(t.id) as total_assigned,
                       SUM(CASE WHEN t.status IN ('Completed', 'Closed') THEN 1 ELSE 0 END) as completed_count,
                       SUM(CASE WHEN t.status NOT IN ('Completed', 'Closed', 'Cancelled') AND t.tat_datetime < NOW() THEN 1 ELSE 0 END) as overdue_count,
                       SUM(CASE WHEN t.status IN ('Completed', 'Closed') AND t.replied_datetime <= t.tat_datetime THEN 1 ELSE 0 END) as within_sla_count
                FROM users u
                LEFT JOIN tickets t ON u.id = t.allocated_to
                GROUP BY u.id, u.full_name, u.user_code, u.department
                ORDER BY completed_count DESC";
        return $this->fetchAll($sql);
    }

    public function getDaywiseProductionReport(array $filters): array {
        $startDate = !empty($filters['start_date']) ? $filters['start_date'] : date('Y-m-01');
        $endDate = !empty($filters['end_date']) ? $filters['end_date'] : date('Y-m-d');
        $employeeId = !empty($filters['allocated_to']) ? (int)$filters['allocated_to'] : null;
        $activityId = !empty($filters['activity_id']) ? (int)$filters['activity_id'] : null;

        $userWhere = "";
        $paramsInitial = [$startDate];
        if ($employeeId) {
            $userWhere .= " AND allocated_to = ?";
            $paramsInitial[] = $employeeId;
        }
        if ($activityId) {
            $userWhere .= " AND activity_id = ?";
            $paramsInitial[] = $activityId;
        }

        // Tickets created before start_date
        $sqlCreatedBefore = "SELECT COUNT(*) as cnt FROM tickets WHERE DATE(created_at) < ?" . $userWhere;
        $createdBefore = (int)($this->fetchOne($sqlCreatedBefore, $paramsInitial)['cnt'] ?? 0);

        // Tickets closed before start_date
        $paramsClosedBefore = [$startDate];
        if ($employeeId) $paramsClosedBefore[] = $employeeId;
        if ($activityId) $paramsClosedBefore[] = $activityId;
        
        $sqlClosedBefore = "SELECT COUNT(*) as cnt FROM tickets WHERE status IN ('Completed', 'Closed') AND DATE(COALESCE(replied_datetime, updated_at)) < ?" . $userWhere;
        $closedBefore = (int)($this->fetchOne($sqlClosedBefore, $paramsClosedBefore)['cnt'] ?? 0);

        $runningOpeningBalance = max(0, $createdBefore - $closedBefore);

        // Daily creation counts
        $paramsCreated = [$startDate, $endDate];
        if ($employeeId) $paramsCreated[] = $employeeId;
        if ($activityId) $paramsCreated[] = $activityId;

        $sqlDailyNew = "SELECT DATE(created_at) as dt, COUNT(*) as cnt 
                        FROM tickets 
                        WHERE DATE(created_at) >= ? AND DATE(created_at) <= ?" . $userWhere . " 
                        GROUP BY DATE(created_at)";
        $dailyNewRows = $this->fetchAll($sqlDailyNew, $paramsCreated);
        $dailyNewMap = [];
        foreach ($dailyNewRows as $r) {
            $dailyNewMap[$r['dt']] = (int)$r['cnt'];
        }

        // Daily closure counts
        $paramsClosed = [$startDate, $endDate];
        if ($employeeId) $paramsClosed[] = $employeeId;
        if ($activityId) $paramsClosed[] = $activityId;

        $sqlDailyClosed = "SELECT DATE(COALESCE(replied_datetime, updated_at)) as dt, COUNT(*) as cnt 
                           FROM tickets 
                           WHERE status IN ('Completed', 'Closed') 
                             AND DATE(COALESCE(replied_datetime, updated_at)) >= ? 
                             AND DATE(COALESCE(replied_datetime, updated_at)) <= ?" . $userWhere . " 
                           GROUP BY DATE(COALESCE(replied_datetime, updated_at))";
        $dailyClosedRows = $this->fetchAll($sqlDailyClosed, $paramsClosed);
        $dailyClosedMap = [];
        foreach ($dailyClosedRows as $r) {
            $dailyClosedMap[$r['dt']] = (int)$r['cnt'];
        }

        // Chronological computation for opening & closing balance carryforward
        $result = [];
        $current = strtotime($startDate);
        $end = strtotime($endDate);

        while ($current <= $end) {
            $dateStr = date('Y-m-d', $current);
            $newTasks = $dailyNewMap[$dateStr] ?? 0;
            $closedTasks = $dailyClosedMap[$dateStr] ?? 0;
            $openingBalance = $runningOpeningBalance;
            $pendingTasks = max(0, $openingBalance + $newTasks - $closedTasks);
            $totalWorkload = $openingBalance + $newTasks;
            $closureRate = $totalWorkload > 0 ? round(($closedTasks / $totalWorkload) * 100, 1) : 0;

            $result[] = [
                'date' => $dateStr,
                'date_formatted' => date('d M Y', $current),
                'day_name' => date('D', $current),
                'opening_balance' => $openingBalance,
                'new_tasks' => $newTasks,
                'closed_tasks' => $closedTasks,
                'pending_tasks' => $pendingTasks,
                'total_workload' => $totalWorkload,
                'closure_rate' => $closureRate
            ];

            $runningOpeningBalance = $pendingTasks;
            $current = strtotime('+1 day', $current);
        }

        return array_reverse($result);
    }
}

