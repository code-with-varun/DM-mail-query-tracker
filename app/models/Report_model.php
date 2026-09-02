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
}
