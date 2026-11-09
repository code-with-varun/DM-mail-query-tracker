<?php
/**
 * Process Updates & Reference Library Model
 * Preserves Outlook process update mails, policy changes, billing guidelines, 
 * as well as reference category tickets (Allocation Exclude, Checklist, Grid, Penalty).
 */

class Process_update_model extends Model {
    
    public function getAllUpdates(array $filters = []): array {
        $results = [];
        $selectedCatId = !empty($filters['category_id']) ? (int)$filters['category_id'] : null;

        // Fetch category info if selected
        $selectedCatName = '';
        if ($selectedCatId) {
            $catRow = $this->fetchOne("SELECT category_name FROM ticket_categories WHERE id = ?", [$selectedCatId]);
            if ($catRow) {
                $selectedCatName = strtolower(trim($catRow['category_name']));
            }
        }

        // 1. Fetch from process_updates table (unless a specific non-process-update category is filtered)
        $includeProcessUpdatesTable = true;
        if ($selectedCatId && $selectedCatName && !in_array($selectedCatName, ['process updates', 'process update'])) {
            $includeProcessUpdatesTable = false;
        }

        if ($includeProcessUpdatesTable) {
            $puSql = "SELECT pu.*, d.division_name, d.code as division_code, 
                           a.activity_name, sa.sub_activity_name, u.full_name as creator_name, u.user_code as creator_code
                    FROM process_updates pu
                    LEFT JOIN divisions d ON pu.division_id = d.id
                    LEFT JOIN activities a ON pu.activity_id = a.id
                    LEFT JOIN sub_activities sa ON pu.sub_activity_id = sa.id
                    LEFT JOIN users u ON pu.created_by = u.id
                    WHERE 1=1";
            
            $puParams = [];

            if (!empty($filters['search'])) {
                $puSql .= " AND (pu.title LIKE ? OR pu.notes LIKE ? OR pu.file_name LIKE ?)";
                $term = "%" . $filters['search'] . "%";
                $puParams[] = $term;
                $puParams[] = $term;
                $puParams[] = $term;
            }

            if (!empty($filters['division_id'])) {
                $puSql .= " AND pu.division_id = ?";
                $puParams[] = (int)$filters['division_id'];
            }

            if (!empty($filters['activity_id'])) {
                $puSql .= " AND pu.activity_id = ?";
                $puParams[] = (int)$filters['activity_id'];
            }

            if (!empty($filters['sub_activity_id'])) {
                $puSql .= " AND pu.sub_activity_id = ?";
                $puParams[] = (int)$filters['sub_activity_id'];
            }

            if (!empty($filters['billing_month'])) {
                $puSql .= " AND pu.billing_month = ?";
                $puParams[] = $filters['billing_month'];
            }

            $puRows = $this->fetchAll($puSql, $puParams);

            foreach ($puRows as $row) {
                $row['source'] = 'process_update';
                $row['ticket_number'] = null;
                $row['category_name'] = 'Process Updates';
                $row['category_slug'] = 'process-updates';
                $results[] = $row;
            }
        }

        // 2. Fetch from tickets table for reference categories
        $tSql = "SELECT t.id,
                        t.ticket_number,
                        COALESCE(tc.category_name, 'Reference') as category_name,
                        t.category_id,
                        tc.category_slug,
                        t.subject as title,
                        COALESCE(t.billing_month, DATE_FORMAT(t.created_at, '%Y-%m')) as billing_month,
                        t.division_id,
                        d.division_name,
                        d.code as division_code,
                        t.activity_id,
                        a.activity_name,
                        t.sub_activity_id,
                        sa.sub_activity_name,
                        ta.file_name,
                        ta.file_path,
                        LOWER(SUBSTRING_INDEX(ta.file_name, '.', -1)) as file_ext,
                        ta.file_size,
                        CONCAT_WS(' | ', NULLIF(t.from_address, ''), NULLIF(t.remarks, '')) as notes,
                        COALESCE(u.full_name, t.from_address, 'System') as creator_name,
                        u.user_code as creator_code,
                        t.created_at
                 FROM tickets t
                 JOIN ticket_categories tc ON t.category_id = tc.id
                 LEFT JOIN divisions d ON t.division_id = d.id
                 LEFT JOIN activities a ON t.activity_id = a.id
                 LEFT JOIN sub_activities sa ON t.sub_activity_id = sa.id
                 LEFT JOIN users u ON t.created_by = u.id
                 LEFT JOIN ticket_attachments ta ON ta.id = (SELECT MIN(id) FROM ticket_attachments WHERE ticket_id = t.id)
                 WHERE 1=1";

        $tParams = [];

        if ($selectedCatId) {
            $tSql .= " AND t.category_id = ?";
            $tParams[] = $selectedCatId;
        } else {
            // Default reference categories
            $tSql .= " AND (
                LOWER(tc.category_name) IN ('allocation exclude', 'checklist', 'grid', 'penalty', 'process updates', 'process update')
                OR tc.category_slug IN ('allocation-exclude', 'checklist', 'grid', 'penalty', 'process-updates', 'process-update')
            )";
        }

        if (!empty($filters['search'])) {
            $tSql .= " AND (t.subject LIKE ? OR t.remarks LIKE ? OR t.from_address LIKE ? OR t.ticket_number LIKE ? OR ta.file_name LIKE ?)";
            $term = "%" . $filters['search'] . "%";
            $tParams[] = $term;
            $tParams[] = $term;
            $tParams[] = $term;
            $tParams[] = $term;
            $tParams[] = $term;
        }

        if (!empty($filters['division_id'])) {
            $tSql .= " AND t.division_id = ?";
            $tParams[] = (int)$filters['division_id'];
        }

        if (!empty($filters['activity_id'])) {
            $tSql .= " AND t.activity_id = ?";
            $tParams[] = (int)$filters['activity_id'];
        }

        if (!empty($filters['sub_activity_id'])) {
            $tSql .= " AND t.sub_activity_id = ?";
            $tParams[] = (int)$filters['sub_activity_id'];
        }

        if (!empty($filters['billing_month'])) {
            $tSql .= " AND (t.billing_month = ? OR DATE_FORMAT(t.created_at, '%Y-%m') = ?)";
            $tParams[] = $filters['billing_month'];
            $tParams[] = $filters['billing_month'];
        }

        $ticketRows = $this->fetchAll($tSql, $tParams);

        foreach ($ticketRows as $tRow) {
            $tRow['source'] = 'ticket';
            $results[] = $tRow;
        }

        // Sort combined results by billing_month DESC, created_at DESC, id DESC
        usort($results, function($a, $b) {
            $bmComp = strcmp($b['billing_month'] ?? '', $a['billing_month'] ?? '');
            if ($bmComp !== 0) return $bmComp;
            $caComp = strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
            if ($caComp !== 0) return $caComp;
            return $b['id'] <=> $a['id'];
        });

        return $results;
    }

    public function getItemById(int $id, string $source = 'process_update'): ?array {
        if ($source === 'ticket') {
            $sql = "SELECT t.id,
                           t.ticket_number,
                           COALESCE(tc.category_name, 'Reference') as category_name,
                           t.category_id,
                           tc.category_slug,
                           t.subject as title,
                           COALESCE(t.billing_month, DATE_FORMAT(t.created_at, '%Y-%m')) as billing_month,
                           t.division_id,
                           d.division_name,
                           d.code as division_code,
                           t.activity_id,
                           a.activity_name,
                           t.sub_activity_id,
                           sa.sub_activity_name,
                           ta.file_name,
                           ta.file_path,
                           LOWER(SUBSTRING_INDEX(ta.file_name, '.', -1)) as file_ext,
                           ta.file_size,
                           CONCAT_WS(' | ', NULLIF(t.from_address, ''), NULLIF(t.remarks, '')) as notes,
                           COALESCE(u.full_name, t.from_address, 'System') as creator_name,
                           u.user_code as creator_code,
                           t.created_at
                    FROM tickets t
                    LEFT JOIN ticket_categories tc ON t.category_id = tc.id
                    LEFT JOIN divisions d ON t.division_id = d.id
                    LEFT JOIN activities a ON t.activity_id = a.id
                    LEFT JOIN sub_activities sa ON t.sub_activity_id = sa.id
                    LEFT JOIN users u ON t.created_by = u.id
                    LEFT JOIN ticket_attachments ta ON ta.id = (SELECT MIN(id) FROM ticket_attachments WHERE ticket_id = t.id)
                    WHERE t.id = ?
                    LIMIT 1";
            $res = $this->fetchOne($sql, [$id]);
            if ($res) {
                $res['source'] = 'ticket';
            }
            return $res;
        }

        $res = $this->getById($id);
        if ($res) {
            $res['source'] = 'process_update';
            $res['category_name'] = 'Process Updates';
            $res['category_slug'] = 'process-updates';
        }
        return $res;
    }

    public function getById(int $id): ?array {
        $sql = "SELECT pu.*, d.division_name, d.code as division_code, 
                       a.activity_name, sa.sub_activity_name, u.full_name as creator_name, u.user_code as creator_code
                FROM process_updates pu
                LEFT JOIN divisions d ON pu.division_id = d.id
                LEFT JOIN activities a ON pu.activity_id = a.id
                LEFT JOIN sub_activities sa ON pu.sub_activity_id = sa.id
                LEFT JOIN users u ON pu.created_by = u.id
                WHERE pu.id = ?
                LIMIT 1";
        return $this->fetchOne($sql, [$id]);
    }

    public function createUpdate(array $data): int {
        $data['created_at'] = date('Y-m-d H:i:s');
        $id = $this->insert('process_updates', $data);
        $this->logAudit('Process Updates', 'Create Process Update Mail', null, ['id' => $id, 'title' => $data['title']]);
        return $id;
    }

    public function updateUpdate(int $id, array $data): bool {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $result = $this->update('process_updates', $data, "id = ?", [$id]);
        $this->logAudit('Process Updates', 'Update Process Update Mail', ['id' => $id], $data);
        return $result;
    }

    public function deleteItem(int $id, string $source = 'process_update'): bool {
        if ($source === 'ticket') {
            $this->delete('ticket_attachments', "ticket_id = ?", [$id]);
            $res = $this->delete('tickets', "id = ?", [$id]) > 0;
            $this->logAudit('Tickets', 'Delete Ticket Reference Item', ['id' => $id], null);
            return $res;
        }
        return $this->deleteUpdate($id);
    }

    public function deleteUpdate(int $id): bool {
        $record = $this->getById($id);
        if ($record && !empty($record['file_path'])) {
            $fullPath = __DIR__ . '/../../' . $record['file_path'];
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }
        $res = $this->delete('process_updates', "id = ?", [$id]);
        $this->logAudit('Process Updates', 'Delete Process Update Mail', ['id' => $id], null);
        return $res;
    }
}
