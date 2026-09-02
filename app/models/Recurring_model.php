<?php
/**
 * Recurring Task Model & Engine Handler
 */

require_once __DIR__ . '/Ticket_model.php';

class Recurring_model extends Model {
    public function getTemplates(?int $userId = null, ?int $roleId = null): array {
        $sql = "SELECT rt.*, a.activity_name, sa.sub_activity_name, u.full_name as assigned_user_name
                FROM recurring_templates rt
                LEFT JOIN activities a ON rt.activity_id = a.id
                LEFT JOIN sub_activities sa ON rt.sub_activity_id = sa.id
                LEFT JOIN users u ON rt.assigned_user_id = u.id
                WHERE 1=1";
        $params = [];

        if ($roleId == 2 && $userId) {
            $sql .= " AND (rt.assigned_user_id IN (SELECT id FROM users WHERE manager_id = ? OR id = ?) OR rt.created_by = ?)";
            $params[] = $userId;
            $params[] = $userId;
            $params[] = $userId;
        }

        $sql .= " ORDER BY rt.id DESC";
        return $this->fetchAll($sql, $params);
    }

    public function createTemplate(array $data): int {
        $data['created_at'] = date('Y-m-d H:i:s');
        $id = $this->insert('recurring_templates', $data);
        $this->logAudit('Recurring', 'Create Recurring Template', null, ['id' => $id, 'name' => $data['template_name']]);
        return $id;
    }

    /**
     * Engine: Process due recurring templates and generate tickets
     */
    public function processScheduler(): array {
        $templates = $this->fetchAll("SELECT * FROM recurring_templates WHERE is_active = 1 AND start_date <= CURDATE() AND (end_date IS NULL OR end_date >= CURDATE())");
        $generatedCount = 0;
        $ticketModel = new Ticket_model();

        foreach ($templates as $tmpl) {
            $shouldGenerate = false;
            $today = date('Y-m-d');
            $currentDayOfMonth = (int) date('j');

            // Simple Frequency Logic
            if (empty($tmpl['last_generated_at'])) {
                $shouldGenerate = true;
            } else {
                $lastGenDate = date('Y-m-d', strtotime($tmpl['last_generated_at']));
                switch ($tmpl['frequency']) {
                    case 'Daily':
                        if ($lastGenDate < $today) $shouldGenerate = true;
                        break;
                    case 'Weekly':
                        if (strtotime($today) >= strtotime($lastGenDate . ' +7 days')) $shouldGenerate = true;
                        break;
                    case 'Monthly':
                        if (date('Y-m', strtotime($lastGenDate)) < date('Y-m') && $currentDayOfMonth >= $tmpl['due_day']) {
                            $shouldGenerate = true;
                        }
                        break;
                    case 'Quarterly':
                        if (strtotime($today) >= strtotime($lastGenDate . ' +3 months')) $shouldGenerate = true;
                        break;
                    case 'Half Yearly':
                        if (strtotime($today) >= strtotime($lastGenDate . ' +6 months')) $shouldGenerate = true;
                        break;
                    case 'Yearly':
                        if (date('Y', strtotime($lastGenDate)) < date('Y')) $shouldGenerate = true;
                        break;
                }
            }

            if ($shouldGenerate) {
                // Fetch Sub Activity TAT
                $subAct = $this->fetchOne("SELECT default_tat_hours FROM sub_activities WHERE id = ?", [$tmpl['sub_activity_id']]);
                $tatHours = $subAct['default_tat_hours'] ?? 24;
                $tatDatetime = date('Y-m-d H:i:s', strtotime("+{$tatHours} hours"));

                // Generate Ticket
                $ticketId = $ticketModel->createTicket([
                    'ticket_type' => 'Task Ticket',
                    'received_datetime' => date('Y-m-d H:i:s'),
                    'from_address' => 'SYSTEM_RECURRING',
                    'subject' => "[Recurring] " . $tmpl['template_name'],
                    'activity_id' => $tmpl['activity_id'],
                    'sub_activity_id' => $tmpl['sub_activity_id'],
                    'status' => 'New',
                    'priority' => $tmpl['priority'],
                    'tat_datetime' => $tatDatetime,
                    'allocated_to' => $tmpl['assigned_user_id'],
                    'remarks' => "Automatically generated recurring task template: " . $tmpl['template_name'],
                    'created_by' => $tmpl['created_by']
                ]);

                // Record instance
                $this->insert('recurring_instances', [
                    'template_id' => $tmpl['id'],
                    'ticket_id' => $ticketId,
                    'generated_date' => date('Y-m-d H:i:s'),
                    'status' => 'Generated'
                ]);

                // Update template last generated timestamp
                $this->update('recurring_templates', ['last_generated_at' => date('Y-m-d H:i:s')], "id = ?", [$tmpl['id']]);
                $generatedCount++;
            }
        }

        return ['status' => 'success', 'generated_count' => $generatedCount];
    }
}
