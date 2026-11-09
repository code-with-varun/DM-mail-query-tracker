<?php
/**
 * Permission Model
 * Manages role-based and user-based module and page access rights
 */

class Permission_model extends Model {

    /**
     * Complete Module and Page Catalog
     */
    public function getModuleCatalog(): array {
        return [
            'workspace' => [
                'name' => 'Workspace',
                'icon' => 'fas fa-calendar-alt',
                'description' => 'Personal workload, daily shift scheduling & home launchpad',
                'pages' => [
                    'home' => ['name' => 'Home Hub', 'url' => 'home', 'desc' => 'Aggregated portal dashboard and module launcher'],
                    'dashboard' => ['name' => 'Dashboard', 'url' => 'dashboard', 'desc' => 'Operational & metric slicers dashboard'],
                    'my_bucket' => ['name' => 'My Bucket', 'url' => 'tickets/my-bucket', 'desc' => 'Personal assigned work queue'],
                    'roster' => ['name' => 'Daily Roster', 'url' => 'roster', 'desc' => 'Shift attendance & duty allocation planner']
                ]
            ],
            'performance' => [
                'name' => 'Performance',
                'icon' => 'fas fa-trophy',
                'description' => 'Employee & team scorecards, metrics, analytics & daily output logs',
                'pages' => [
                    'performance_scorecard' => ['name' => 'Performance Scorecard', 'url' => 'performance/scorecard', 'desc' => 'Monthly employee/team scorecard with dynamic benchmark rating'],
                    'performance_analytics' => ['name' => 'Performance Analytics', 'url' => 'performance/analytics', 'desc' => 'Executive trends, SLA compliance & multi-horizon analytics'],
                    'performance_contest'   => ['name' => 'Grand Prix Contest', 'url' => 'performance/contest', 'desc' => 'Gamified team & individual racing championship, live telemetry & slideshow'],
                    'performance_awards'    => ['name' => 'Awards & Honours Hub', 'url' => 'performance/awards', 'desc' => 'Datamatics Annual Achievers Awards (AAA) & DM-ISPARK Recognition Hub'],
                    'team_kpi_config'       => ['name' => 'Team Metric Config', 'url' => 'performance/config', 'desc' => 'Sub-activity point weightages and benchmark setup']
                ]
            ],
            'reports' => [
                'name' => 'Reports',
                'icon' => 'fas fa-chart-bar',
                'description' => 'Executive operational reporting, exports & count tracking',
                'pages' => [
                    'reports_export' => ['name' => 'Reports & Export', 'url' => 'reports', 'desc' => 'Comprehensive filtered CSV exports and registers'],
                    'daywise_count' => ['name' => 'Daywise Count Tracker', 'url' => 'reports/production', 'desc' => 'Day-wise count volume aggregation reports']
                ]
            ],
            'tickets_tasks' => [
                'name' => 'Tasks',
                'icon' => 'fas fa-tasks',
                'description' => 'Core ticketing, task execution & multi-stage lifecycle pipelines',
                'pages' => [
                    'all_tasks' => ['name' => 'All Tasks', 'url' => 'tickets', 'desc' => 'Central task queue & shared ticket register'],
                    'create_ticket' => ['name' => 'Create Mail Ticket', 'url' => 'tickets/create', 'desc' => 'Ingest and index new customer/process mail tickets'],
                    'internal_tasks' => ['name' => 'Internal Tasks', 'url' => 'tasks', 'desc' => 'Internal operations, ad-hoc tickets and maker/checker items'],
                    'lifecycle' => ['name' => 'Lifecycle Tracker', 'url' => 'lifecycle', 'desc' => 'Multi-stage processing pipeline and stage tracking']
                ]
            ],
            'trackers' => [
                'name' => 'Trackers',
                'icon' => 'fas fa-clipboard-list',
                'description' => 'Operational logs: Production, Queries, Holds, Inputs, Deliveries, SOP Updates, Errors',
                'pages' => [
                    'daily_production' => ['name' => 'Daily Production Logger', 'url' => 'performance/production', 'desc' => 'Count-based item output logging and verification'],
                    'query_tracker' => ['name' => 'Query Tracker', 'url' => 'tracker/query', 'desc' => 'Customer & team queries register with resolution tracking'],
                    'hold_list' => ['name' => 'Hold / Release List', 'url' => 'hold', 'desc' => 'Tasks on hold awaiting client clarification or data'],
                    'input_tracker' => ['name' => 'Input Tracker', 'url' => 'tracker/input', 'desc' => 'Raw input batches and file receipt logger'],
                    'delivery_tracker' => ['name' => 'Delivery Tracker', 'url' => 'tracker/delivery', 'desc' => 'Outbound deliveries, dispatch timestamps & SLAs'],
                    'process_updates' => ['name' => 'Process Updates Tracker', 'url' => 'tracker/process-updates', 'desc' => 'Client SOP updates, broadcast notices & change documents'],
                    'error_tracker' => ['name' => 'Error Tracker', 'url' => 'error-tracker', 'desc' => 'Quality checking defect logging and corrective action tracking']
                ]
            ],
            'directories' => [
                'name' => 'People',
                'icon' => 'fas fa-users',
                'description' => 'User accounts, skill matrices & contact books',
                'pages' => [
                    'contacts' => ['name' => 'Contact Manager', 'url' => 'contacts', 'desc' => 'Client, stakeholder and distribution list directory'],
                    'employees' => ['name' => 'Employee Accounts', 'url' => 'employees', 'desc' => 'System user accounts, credentials and manager hierarchy'],
                    'skill_matrix' => ['name' => 'Skill Matrix Hub', 'url' => 'employees/skill-matrix', 'desc' => 'Sub-activity matrix mapping & Maker-Checker assignments']
                ]
            ],
            'training' => [
                'name' => 'Training',
                'icon' => 'fas fa-graduation-cap',
                'description' => 'Process Knowledge Testing, Knowledge Transfer & Certifications',
                'pages' => [
                    'training_plan' => ['name' => 'Training Plan & KT', 'url' => 'training/plan', 'desc' => 'Knowledge transfer timelines and trainee roadmaps'],
                    'pkt_test' => ['name' => 'PKT Test Center', 'url' => 'training/pkt', 'desc' => 'Online Process Knowledge Tests and evaluations'],
                    'practice_files' => ['name' => 'Practice Files', 'url' => 'training/practice', 'desc' => 'Sample test files and hands-on practice repository'],
                    'certifications' => ['name' => 'Certifications', 'url' => 'training/certifications', 'desc' => 'Verified qualifications and skill badges'],
                    'manage_pkt' => ['name' => 'Manage PKT Bank', 'url' => 'training/manage-pkt', 'desc' => 'Question bank authoring and exam settings']
                ]
            ],
            'workflows' => [
                'name' => 'Workflows',
                'icon' => 'fas fa-project-diagram',
                'description' => 'Workflow pipeline stages & process queue configurations',
                'pages' => [
                    'workflows' => ['name' => 'Workflow Management', 'url' => 'workflows', 'desc' => 'Workflow visual stages and auto-routing rules'],
                    'queues' => ['name' => 'Process Queues', 'url' => 'queues', 'desc' => 'Queue capacity definitions and SLA rules']
                ]
            ],
            'administration' => [
                'name' => 'Administration',
                'icon' => 'fas fa-cogs',
                'description' => 'Organizational hierarchy, masters, audit logs & system controls',
                'pages' => [
                    'org_hierarchy' => ['name' => 'Org Hierarchy Tree', 'url' => 'master/hierarchy', 'desc' => 'Dynamic organizational chart and reporting hierarchy'],
                    'master_projects' => ['name' => 'Projects & Teams', 'url' => 'master/projects', 'desc' => 'Projects, clients & team operational mode settings'],
                    'master_divisions' => ['name' => 'Divisions Master', 'url' => 'master/divisions', 'desc' => 'Company divisional hierarchy'],
                    'master_activities' => ['name' => 'Activities Master', 'url' => 'master/activities', 'desc' => 'Operational activity catalog'],
                    'master_subactivities' => ['name' => 'Sub-Activities Master', 'url' => 'master/subactivities', 'desc' => 'Granular sub-activities, default users & standard TAT'],
                    'master_categories' => ['name' => 'Category Master', 'url' => 'master/categories', 'desc' => 'Ticket category classifications'],
                    'audit_logs' => ['name' => 'System Audit Logs', 'url' => 'audit', 'desc' => 'Complete tamper-evident system audit trail'],
                    'access_rights' => ['name' => 'Access Rights', 'url' => 'access-rights', 'desc' => 'Role and user page-wise permissions management'],
                    'settings' => ['name' => 'System Settings', 'url' => 'settings', 'desc' => 'Global system settings, database backup & maintenance mode']
                ]
            ]
        ];
    }

    /**
     * Map a page key to its parent module
     */
    public function getModuleForPage(string $pageKey): string {
        $catalog = $this->getModuleCatalog();
        foreach ($catalog as $modKey => $mod) {
            if (isset($mod['pages'][$pageKey])) {
                return $modKey;
            }
        }
        return 'workspace';
    }

    /**
     * Get default allowed pages and CRUD actions per role
     */
    public function getDefaultPermissionsByRole(int $roleId): array {
        $catalog = $this->getModuleCatalog();
        $defaults = [];

        switch ($roleId) {
            case 1: // Super Admin: All pages, Full CRUD (Create, Read/View, Update/Edit, Delete)
                foreach ($catalog as $mod) {
                    foreach (array_keys($mod['pages']) as $pageKey) {
                        $defaults[$pageKey] = [
                            'is_allowed' => 1,
                            'can_create' => 1,
                            'can_edit'   => 1,
                            'can_delete' => 1
                        ];
                    }
                }
                return $defaults;

            case 5: // Senior Management: Executive oversight, Scorecards, Analytics, Contest GP, Reports (View/Export only), Contacts.
                // Org Hierarchy and System Audit Logs are disabled by default (0 / 9 in Administration)
                return [
                    'home'                  => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'dashboard'             => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'performance_scorecard' => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'performance_analytics' => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'performance_contest'   => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'performance_awards'    => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'daily_production'      => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'reports_export'        => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 0, 'can_delete' => 0],
                    'daywise_count'         => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'contacts'              => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'org_hierarchy'         => ['is_allowed' => 0, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'audit_logs'            => ['is_allowed' => 0, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0]
                ];

            case 3: // Manager: Operations Management, Team Scorecards, Reports, Employees, Skill Matrix, Workflows
                return [
                    'home'                  => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'dashboard'             => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'roster'                => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'performance_scorecard' => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'performance_analytics' => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'performance_contest'   => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 0, 'can_delete' => 0],
                    'performance_awards'    => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'daily_production'      => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'team_kpi_config'       => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'reports_export'        => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 0, 'can_delete' => 0],
                    'daywise_count'         => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'contacts'              => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'employees'             => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'skill_matrix'          => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'workflows'             => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'queues'                => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'org_hierarchy'         => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'audit_logs'            => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0]
                ];

            case 2: // Admin (TL): Team Leader: Tasks, Trackers, Directories, Training, Workflows, Scorecards, Reports, Hierarchy
                return [
                    'home'                  => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'dashboard'             => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'my_bucket'             => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'roster'                => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'all_tasks'             => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'create_ticket'         => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 0, 'can_delete' => 0],
                    'internal_tasks'        => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'lifecycle'             => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'query_tracker'         => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'hold_list'             => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'input_tracker'         => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'delivery_tracker'      => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'process_updates'       => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'error_tracker'         => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'contacts'              => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'employees'             => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'skill_matrix'          => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'training_plan'         => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'pkt_test'              => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 0, 'can_delete' => 0],
                    'practice_files'        => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'certifications'        => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'manage_pkt'            => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'workflows'             => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'queues'                => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'performance_scorecard' => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'performance_analytics' => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'performance_contest'   => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'performance_awards'    => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 0, 'can_delete' => 0],
                    'daily_production'      => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'team_kpi_config'       => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0],
                    'reports_export'        => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 0, 'can_delete' => 0],
                    'daywise_count'         => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'org_hierarchy'         => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0]
                ];

            case 4: // Employee: Execution bucket, Tasks, Trackers, Training LMS, Daily Production
            default:
                return [
                    'home'                  => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'dashboard'             => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'my_bucket'             => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 1, 'can_delete' => 0],
                    'roster'                => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'all_tasks'             => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'create_ticket'         => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 0, 'can_delete' => 0],
                    'internal_tasks'        => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 1, 'can_delete' => 0],
                    'lifecycle'             => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'query_tracker'         => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 0, 'can_delete' => 0],
                    'hold_list'             => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'input_tracker'         => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'delivery_tracker'      => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'process_updates'       => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'error_tracker'         => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'contacts'              => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'training_plan'         => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'pkt_test'              => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 0, 'can_delete' => 0],
                    'practice_files'        => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'certifications'        => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'performance_contest'   => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'performance_awards'    => ['is_allowed' => 1, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0],
                    'daily_production'      => ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 0]
                ];
        }
    }

    /**
     * Ensure database has role_permissions populated
     */
    public function ensureInitialized(): void {
        $count = $this->fetchOne("SELECT COUNT(*) as c FROM role_permissions");
        if ((int)($count['c'] ?? 0) === 0) {
            $this->seedDefaultRolePermissions(true);
        }
    }

    /**
     * Seed default role permissions with CRUD values for all 5 roles
     */
    public function seedDefaultRolePermissions(bool $forceReset = false): void {
        $catalog = $this->getModuleCatalog();
        $roles = [1, 2, 3, 4, 5];

        foreach ($roles as $roleId) {
            $defaultMap = $this->getDefaultPermissionsByRole($roleId);
            foreach ($catalog as $modKey => $mod) {
                foreach (array_keys($mod['pages']) as $pageKey) {
                    $def = $defaultMap[$pageKey] ?? null;
                    if (is_array($def)) {
                        $isAllowed = (int)($def['is_allowed'] ?? 0);
                        $canCreate = (int)($def['can_create'] ?? 0);
                        $canEdit   = (int)($def['can_edit'] ?? 0);
                        $canDelete = (int)($def['can_delete'] ?? 0);
                    } elseif ($def !== null) {
                        $isAllowed = $def ? 1 : 0;
                        $canCreate = 0;
                        $canEdit   = 0;
                        $canDelete = 0;
                    } else {
                        $isAllowed = 0;
                        $canCreate = 0;
                        $canEdit   = 0;
                        $canDelete = 0;
                    }

                    if ($forceReset) {
                        $this->query("
                            INSERT INTO role_permissions (role_id, module_key, page_key, is_allowed, can_create, can_edit, can_delete)
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE 
                                is_allowed = VALUES(is_allowed),
                                can_create = VALUES(can_create),
                                can_edit = VALUES(can_edit),
                                can_delete = VALUES(can_delete),
                                updated_at = NOW()
                        ", [$roleId, $modKey, $pageKey, $isAllowed, $canCreate, $canEdit, $canDelete]);
                    } else {
                        // Insert only if not present
                        $this->query("
                            INSERT IGNORE INTO role_permissions (role_id, module_key, page_key, is_allowed, can_create, can_edit, can_delete)
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                        ", [$roleId, $modKey, $pageKey, $isAllowed, $canCreate, $canEdit, $canDelete]);
                    }
                }
            }
        }
    }

    /**
     * Get all permissions for a role as [page_key => ['is_allowed' => x, 'can_create' => x, 'can_edit' => x, 'can_delete' => x]]
     */
    public function getRolePermissions(int $roleId): array {
        $this->ensureInitialized();
        $rows = $this->fetchAll("
            SELECT page_key, is_allowed, can_create, can_edit, can_delete 
            FROM role_permissions 
            WHERE role_id = ?
        ", [$roleId]);

        $map = [];
        foreach ($rows as $r) {
            $map[$r['page_key']] = [
                'is_allowed' => (int)$r['is_allowed'],
                'can_create' => (int)$r['can_create'],
                'can_edit'   => (int)$r['can_edit'],
                'can_delete' => (int)$r['can_delete']
            ];
        }

        // Fill missing pages from catalog as 0
        $catalog = $this->getModuleCatalog();
        foreach ($catalog as $mod) {
            foreach (array_keys($mod['pages']) as $pk) {
                if (!isset($map[$pk])) {
                    $map[$pk] = ['is_allowed' => 0, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0];
                }
            }
        }

        return $map;
    }

    /**
     * Get user-specific override permissions as [page_key => ['is_allowed' => x, 'can_create' => x, 'can_edit' => x, 'can_delete' => x]]
     */
    public function getUserOverrides(int $userId): array {
        $rows = $this->fetchAll("
            SELECT page_key, is_allowed, can_create, can_edit, can_delete 
            FROM user_permissions 
            WHERE user_id = ?
        ", [$userId]);

        $map = [];
        foreach ($rows as $r) {
            $map[$r['page_key']] = [
                'is_allowed' => (int)$r['is_allowed'],
                'can_create' => (int)$r['can_create'],
                'can_edit'   => (int)$r['can_edit'],
                'can_delete' => (int)$r['can_delete']
            ];
        }
        return $map;
    }

    /**
     * Compute effective permissions for a user (combining role defaults + user overrides)
     */
    public function getEffectiveUserPermissions(int $userId, int $roleId): array {
        $rolePerms = $this->getRolePermissions($roleId);
        $userOverrides = $this->getUserOverrides($userId);

        // Merge: User override takes precedence over role permissions
        $effective = $rolePerms;
        foreach ($userOverrides as $pageKey => $val) {
            $effective[$pageKey] = $val;
        }

        return $effective;
    }

    /**
     * Save role permissions with full CRUD rights
     * $crudData can be:
     *   - ['home' => ['view' => 1, 'create' => 0, 'edit' => 0, 'delete' => 0], ...]
     *   - or legacy list of page keys: ['home', 'dashboard', ...]
     */
    public function saveRolePermissions(int $roleId, array $crudData): void {
        $catalog = $this->getModuleCatalog();

        foreach ($catalog as $modKey => $mod) {
            foreach (array_keys($mod['pages']) as $pageKey) {
                $item = $crudData[$pageKey] ?? null;

                if (is_array($item)) {
                    $isAllowed = !empty($item['view']) || !empty($item['is_allowed']) ? 1 : 0;
                    $canCreate = ($isAllowed && (!empty($item['create']) || !empty($item['can_create']))) ? 1 : 0;
                    $canEdit   = ($isAllowed && (!empty($item['edit']) || !empty($item['can_edit']))) ? 1 : 0;
                    $canDelete = ($isAllowed && (!empty($item['delete']) || !empty($item['can_delete']))) ? 1 : 0;
                } elseif ($item !== null) {
                    $isAllowed = !empty($item) ? 1 : 0;
                    $canCreate = 0;
                    $canEdit   = 0;
                    $canDelete = 0;
                } else {
                    $isAllowed = in_array($pageKey, $crudData, true) ? 1 : 0;
                    $canCreate = 0;
                    $canEdit   = 0;
                    $canDelete = 0;
                }

                // Super Admin always retains permanent full CRUD across all pages
                if ($roleId === 1) {
                    $isAllowed = 1;
                    $canCreate = 1;
                    $canEdit   = 1;
                    $canDelete = 1;
                }

                $this->query("
                    INSERT INTO role_permissions (role_id, module_key, page_key, is_allowed, can_create, can_edit, can_delete)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        is_allowed = VALUES(is_allowed),
                        can_create = VALUES(can_create),
                        can_edit   = VALUES(can_edit),
                        can_delete = VALUES(can_delete),
                        updated_at = NOW()
                ", [$roleId, $modKey, $pageKey, $isAllowed, $canCreate, $canEdit, $canDelete]);
            }
        }

        $this->logAudit('Access Rights', 'Update Role Permissions (CRUD Matrix)', ['role_id' => $roleId], ['pages_count' => count($crudData)]);
    }

    /**
     * Save user-specific overrides with full CRUD rights
     */
    public function saveUserPermissions(int $userId, array $crudData, bool $resetToRole = false): void {
        if ($resetToRole) {
            // Delete all custom overrides for this user so they inherit role defaults directly
            $this->query("DELETE FROM user_permissions WHERE user_id = ?", [$userId]);
            $this->logAudit('Access Rights', 'Reset User Permissions to Role Defaults', ['user_id' => $userId], []);
            return;
        }

        $catalog = $this->getModuleCatalog();

        foreach ($catalog as $modKey => $mod) {
            foreach (array_keys($mod['pages']) as $pageKey) {
                $item = $crudData[$pageKey] ?? null;

                if (is_array($item)) {
                    $isAllowed = !empty($item['view']) || !empty($item['is_allowed']) ? 1 : 0;
                    $canCreate = ($isAllowed && (!empty($item['create']) || !empty($item['can_create']))) ? 1 : 0;
                    $canEdit   = ($isAllowed && (!empty($item['edit']) || !empty($item['can_edit']))) ? 1 : 0;
                    $canDelete = ($isAllowed && (!empty($item['delete']) || !empty($item['can_delete']))) ? 1 : 0;
                } elseif ($item !== null) {
                    $isAllowed = !empty($item) ? 1 : 0;
                    $canCreate = 0;
                    $canEdit   = 0;
                    $canDelete = 0;
                } else {
                    $isAllowed = in_array($pageKey, $crudData, true) ? 1 : 0;
                    $canCreate = 0;
                    $canEdit   = 0;
                    $canDelete = 0;
                }

                $this->query("
                    INSERT INTO user_permissions (user_id, module_key, page_key, is_allowed, can_create, can_edit, can_delete)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        is_allowed = VALUES(is_allowed),
                        can_create = VALUES(can_create),
                        can_edit   = VALUES(can_edit),
                        can_delete = VALUES(can_delete),
                        updated_at = NOW()
                ", [$userId, $modKey, $pageKey, $isAllowed, $canCreate, $canEdit, $canDelete]);
            }
        }

        $this->logAudit('Access Rights', 'Update User Permissions Overrides (CRUD Matrix)', ['user_id' => $userId], ['pages_count' => count($crudData)]);
    }

    /**
     * Check if a specific page is permitted for a user/role
     */
    public function checkPermission(string $pageKey, ?int $userId = null, ?int $roleId = null): bool {
        if ($userId === null) {
            $userId = (int)Session::get('user_id');
        }
        if ($roleId === null) {
            $roleId = (int)Session::get('role_id');
        }

        // Super Admin always has full access to everything
        if ($roleId === 1) {
            return true;
        }

        $perms = $this->getEffectiveUserPermissions($userId, $roleId);
        $p = $perms[$pageKey] ?? null;
        if (is_array($p)) {
            return !empty($p['is_allowed']);
        }
        return !empty($p);
    }

    /**
     * Check if ANY page within a module is permitted
     */
    public function checkModulePermission(string $moduleKey, ?int $userId = null, ?int $roleId = null): bool {
        $catalog = $this->getModuleCatalog();
        if (!isset($catalog[$moduleKey])) {
            return false;
        }

        foreach (array_keys($catalog[$moduleKey]['pages']) as $pageKey) {
            if ($this->checkPermission($pageKey, $userId, $roleId)) {
                return true;
            }
        }

        return false;
    }
}
