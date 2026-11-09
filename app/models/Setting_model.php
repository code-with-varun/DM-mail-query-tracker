<?php
/**
 * System Settings Model
 */

class Setting_model extends Model {
    public function getAllSettings(): array {
        $rows = $this->fetchAll("SELECT * FROM settings ORDER BY id ASC");
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['setting_key']] = [
                'value' => $r['setting_value'],
                'description' => $r['description']
            ];
        }
        return $settings;
    }

    public function getSetting(string $key, string $default = ''): string {
        $row = $this->fetchOne("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1", [$key]);
        return $row ? $row['setting_value'] : $default;
    }

    public function updateSetting(string $key, string $value, ?string $description = null): void {
        $exists = $this->fetchOne("SELECT id FROM settings WHERE setting_key = ? LIMIT 1", [$key]);
        if ($exists) {
            $this->update('settings', ['setting_value' => $value, 'updated_at' => date('Y-m-d H:i:s')], "setting_key = ?", [$key]);
        } else {
            $this->insert('settings', [
                'setting_key' => $key,
                'setting_value' => $value,
                'description' => $description,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Complete Production Reset Engine
     * Wipes ALL 45 tables cleanly with foreign key checks disabled.
     * Retains ONLY the Super Admin account (EMP001), 5 roles, factory role permissions,
     * default settings, baseline categories, and system queues.
     * Leaves 0 dummy projects, 0 dummy divisions, 0 dummy users, 0 dummy tickets/logs.
     */
    public function executeProductionReset(?int $preserveUserId = null): array {
        // 1. Preserve active Super Admin credentials
        $superAdmin = null;
        if ($preserveUserId) {
            $superAdmin = $this->fetchOne("SELECT * FROM users WHERE id = ? LIMIT 1", [$preserveUserId]);
        }
        if (!$superAdmin) {
            $superAdmin = $this->fetchOne("SELECT * FROM users WHERE role_id = 1 ORDER BY id ASC LIMIT 1");
        }
        $defaultPass = $superAdmin['password'] ?? password_hash('ChangeMe@123', PASSWORD_BCRYPT);
        $superAdminEmail = $superAdmin['email'] ?? 'superadmin@company.com';
        $superAdminName = $superAdmin['full_name'] ?? 'Super Admin';
        $superAdminCode = $superAdmin['user_code'] ?? 'EMP001';
        $superAdminMobile = $superAdmin['mobile'] ?? '9999999999';

        // 2. Disable foreign key checks
        $this->query("SET FOREIGN_KEY_CHECKS = 0;");

        // 3. Complete list of all 45 tables to truncate
        $allTables = [
            'audit_logs', 'notifications', 'hold_history', 'ticket_attachments',
            'ticket_comments', 'task_tickets', 'ticket_lifecycle_history',
            'ticket_lifecycle_tracker', 'delivery_tracker', 'input_tracker',
            'recurring_instances', 'recurring_templates', 'error_tracker',
            'daily_production_logs', 'monthly_scorecards', 'team_kpi_configs', 'timesheet_logs',
            'process_updates', 'practice_files', 'pkt_test_attempts',
            'pkt_tests', 'pkt_question_bank',
            'activity_certifications', 'user_training_progress', 'training_plan_trainees',
            'training_plans', 'user_sub_activities', 'user_projects',
            'sub_activities', 'activities', 'divisions', 'ticket_categories',
            'workflow_stages', 'workflows', 'process_queues', 'projects',
            'contacts', 'tickets', 'users', 'roles',
            'contest_championships', 'contest_overtake_logs', 'role_permissions',
            'settings', 'user_permissions'
        ];

        foreach ($allTables as $tbl) {
            try {
                $this->query("TRUNCATE TABLE `{$tbl}`");
                $this->query("ALTER TABLE `{$tbl}` AUTO_INCREMENT = 1");
            } catch (\Throwable $e) {}
        }

        // 4. Seed Foundational Roles (5 roles)
        $roles = [
            [1, 'Super Admin', 'Full system access, global configuration, admin management'],
            [2, 'Admin (TL)', 'Team Lead, team queue & ticket management'],
            [3, 'Manager', 'Operations Manager, department oversight & analytics'],
            [4, 'Employee', 'Individual worker, assigned ticket execution'],
            [5, 'Senior Management', 'Executive oversight, cross-project analytics & SLA reporting']
        ];
        $stmtRole = $this->db->prepare("INSERT INTO `roles` (`role_id`, `role_name`, `description`) VALUES (?, ?, ?)");
        foreach ($roles as $r) {
            $stmtRole->execute($r);
        }

        // 5. Seed Single Super Admin Account (id = 1)
        $stmtUser = $this->db->prepare("INSERT INTO `users` (`id`, `user_code`, `full_name`, `email`, `mobile`, `password`, `role_id`, `department`, `manager_id`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtUser->execute([1, $superAdminCode, $superAdminName, $superAdminEmail, $superAdminMobile, $defaultPass, 1, 'Management', null, 'Active']);
        $this->query("ALTER TABLE `users` AUTO_INCREMENT = 2");

        // 6. Seed Factory Role Permissions (Full CRUD matrix across 5 roles)
        require_once __DIR__ . '/Permission_model.php';
        $permModel = new Permission_model();
        $permModel->seedDefaultRolePermissions(true);

        // 7. Seed Production Baseline Settings
        $defaultSettings = [
            ['app_title', 'DM-Ispark (Business Operations & Performance Management System)', 'Application display title'],
            ['company_name', 'Global Operations Services Pvt Ltd', 'Company organization name'],
            ['default_tat_hours', '24', 'Global default SLA TAT hours'],
            ['session_timeout_minutes', '60', 'Inactivity session logout timeout in minutes'],
            ['enable_email_notifications', '1', 'Master switch for email alerts'],
            ['theme_color', '#2563eb', 'Primary accent brand color'],
            ['maker_checker_enabled', '1', 'Enable mandatory four-eyes review on financial tickets'],
            ['maintenance_mode', '0', 'System maintenance mode switch']
        ];
        $stmtSetting = $this->db->prepare("INSERT INTO `settings` (`setting_key`, `setting_value`, `description`, `updated_at`) VALUES (?, ?, ?, NOW())");
        foreach ($defaultSettings as $ds) {
            $stmtSetting->execute($ds);
        }

        // 8. Seed Baseline Production Categories
        $defaultCategories = [
            ['Query Ticket', 'query-ticket', 'Active'],
            ['Task Ticket', 'task-ticket', 'Active'],
            ['Incident & Escalation', 'incident-escalation', 'Active'],
            ['Service Request', 'service-request', 'Active'],
            ['Audit & Compliance Query', 'audit-compliance', 'Active']
        ];
        $stmtCat = $this->db->prepare("INSERT INTO `ticket_categories` (`category_name`, `category_slug`, `status`) VALUES (?, ?, ?)");
        foreach ($defaultCategories as $cat) {
            $stmtCat->execute($cat);
        }

        // 9. Seed Core Process Queues (12 system queues)
        require_once __DIR__ . '/Queue_model.php';
        $queueModel = new Queue_model();
        $queueModel->seedDefaultQueues();

        // 10. Re-enable foreign key checks
        $this->query("SET FOREIGN_KEY_CHECKS = 1;");

        // 11. Clean public/uploads folder
        $uploadDir = __DIR__ . '/../../public/uploads/';
        if (is_dir($uploadDir)) {
            $files = glob($uploadDir . '*');
            foreach ($files as $file) {
                if (is_file($file) && basename($file) !== '.gitkeep') {
                    @unlink($file);
                }
            }
        }

        // 12. Record fresh initial audit log
        try {
            $stmtAudit = $this->db->prepare("INSERT INTO `audit_logs` (`user_id`, `module`, `action`, `record_id`, `details`, `ip_address`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmtAudit->execute([1, 'System', 'FULL_PRODUCTION_RESET', null, json_encode(['reset_by' => $superAdminName, 'mode' => 'Production Fresh Install']), '127.0.0.1']);
        } catch (\Throwable $e) {}

        return [
            'status' => 'success',
            'message' => 'Full Production Reset Complete. All 45 tables cleared, factory roles, permissions, baseline categories & settings initialized with Super Admin (EMP001).',
            'super_admin' => $superAdminCode
        ];
    }
}

