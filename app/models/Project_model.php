<?php
/**
 * Project / Team Master Model
 * Manages Project/Team hierarchy, Manager/TL/Employee mappings & Sub-Activity mappings
 */

class Project_model extends Model {

    public function __construct() {
        parent::__construct();
        $this->ensureTablesExist();
    }

    public function ensureTablesExist(): void {
        try {
            // 1. Roles table & 5 System Roles
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `roles` (
                  `role_id` INT AUTO_INCREMENT PRIMARY KEY,
                  `role_name` VARCHAR(50) NOT NULL UNIQUE,
                  `description` VARCHAR(255) NULL,
                  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            $roles = [
                1 => ['Super Admin', 'Full IT support, global configuration & master management'],
                2 => ['Admin (TL)', 'Team Lead, team queue & ticket management'],
                3 => ['Manager', 'Operations Manager, department oversight & analytics'],
                4 => ['Employee', 'Individual worker, assigned ticket execution'],
                5 => ['Senior Management', 'Executive oversight, cross-project analytics & SLA reporting']
            ];

            foreach ($roles as $rId => $info) {
                $existing = $this->fetchOne("SELECT role_id FROM roles WHERE role_id = ?", [$rId]);
                if ($existing) {
                    $this->update('roles', ['role_name' => $info[0], 'description' => $info[1]], "role_id = ?", [$rId]);
                } else {
                    $this->insert('roles', ['role_id' => $rId, 'role_name' => $info[0], 'description' => $info[1]]);
                }
            }

            // 2. Projects / Teams table
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `projects` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `project_name` VARCHAR(150) NOT NULL UNIQUE,
                  `code` VARCHAR(50) NOT NULL UNIQUE,
                  `description` TEXT NULL,
                  `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
                  `operation_type` ENUM('production_count', 'ticket_workflow', 'hybrid') DEFAULT 'production_count',
                  `created_by` INT NULL,
                  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            try {
                $cols = $this->fetchAll("SHOW COLUMNS FROM projects LIKE 'operation_type'");
                if (empty($cols)) {
                    $this->db->exec("ALTER TABLE `projects` ADD COLUMN `operation_type` ENUM('production_count', 'ticket_workflow', 'hybrid') DEFAULT 'production_count' AFTER `status`");
                }
            } catch (Exception $e) {}

            // Seed default project if empty
            $countProjects = $this->fetchOne("SELECT COUNT(*) as count FROM projects")['count'] ?? 0;
            if ($countProjects == 0) {
                $defaultProjId = $this->insert('projects', [
                    'project_name' => 'Default Operations Project',
                    'code' => 'DOP',
                    'description' => 'Default system project for operations and ticket tracking',
                    'status' => 'Active',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $this->db->exec("UPDATE divisions SET project_id = {$defaultProjId} WHERE project_id IS NULL");
            }

            // 3. User Projects Mapping Table
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `user_projects` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `user_id` INT NOT NULL,
                  `project_id` INT NOT NULL,
                  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                  UNIQUE KEY `user_project_uniq` (`user_id`, `project_id`),
                  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            // 4. Divisions project_id column
            $cols = $this->fetchAll("SHOW COLUMNS FROM divisions LIKE 'project_id'");
            if (empty($cols)) {
                $this->db->exec("ALTER TABLE `divisions` ADD COLUMN `project_id` INT NULL AFTER `id`");
            }

            // 5. User Sub-Activities Mapping Table
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `user_sub_activities` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `user_id` INT NOT NULL,
                  `sub_activity_id` INT NOT NULL,
                  `role_type` ENUM('Maker', 'Checker', 'Both') DEFAULT 'Both',
                  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                  UNIQUE KEY `user_subact_uniq` (`user_id`, `sub_activity_id`),
                  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                  FOREIGN KEY (`sub_activity_id`) REFERENCES `sub_activities`(`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

        } catch (Exception $e) {
            // Silently handle schema setup issues
        }
    }

    public function getProjects(): array {
        $sql = "SELECT p.*, 
                       u.full_name as creator_name,
                       COUNT(DISTINCT d.id) as division_count,
                       COUNT(DISTINCT up.user_id) as mapped_user_count
                FROM projects p
                LEFT JOIN users u ON p.created_by = u.id
                LEFT JOIN divisions d ON d.project_id = p.id
                LEFT JOIN user_projects up ON up.project_id = p.id
                GROUP BY p.id
                ORDER BY p.project_name ASC";
        return $this->fetchAll($sql);
    }

    public function getAll(): array {
        return $this->getProjects();
    }

    public function getProjectById(int $id): ?array {
        $sql = "SELECT p.*, u.full_name as creator_name
                FROM projects p
                LEFT JOIN users u ON p.created_by = u.id
                WHERE p.id = ?
                LIMIT 1";
        return $this->fetchOne($sql, [$id]);
    }

    public function createProject(array $data): int {
        $data['created_at'] = date('Y-m-d H:i:s');
        $id = $this->insert('projects', $data);
        $this->logAudit('Projects', 'Create Project', null, ['id' => $id, 'name' => $data['project_name']]);
        return $id;
    }

    public function updateProject(int $id, array $data): bool {
        $res = $this->update('projects', $data, "id = ?", [$id]);
        $this->logAudit('Projects', 'Update Project', ['id' => $id], $data);
        return (bool)$res;
    }

    public function deleteProject(int $id): bool {
        try {
            $this->update('divisions', ['project_id' => null], "project_id = ?", [$id]);
            $this->delete('user_projects', "project_id = ?", [$id]);
            $res = $this->delete('projects', "id = ?", [$id]) > 0;
            $this->logAudit('Projects', 'Delete Project', ['id' => $id], null);
            return $res;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getProjectUserIds(int $projectId): array {
        $rows = $this->fetchAll("SELECT user_id FROM user_projects WHERE project_id = ?", [$projectId]);
        return array_column($rows, 'user_id');
    }

    public function setProjectUsers(int $projectId, array $userIds): void {
        $this->delete('user_projects', "project_id = ?", [$projectId]);
        foreach ($userIds as $uid) {
            $uid = (int)$uid;
            if ($uid > 0) {
                try {
                    $this->insert('user_projects', [
                        'user_id' => $uid,
                        'project_id' => $projectId,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                } catch (Exception $e) {}
            }
        }
    }

    public function getUserProjectIds(int $userId): array {
        $rows = $this->fetchAll("SELECT project_id FROM user_projects WHERE user_id = ?", [$userId]);
        return array_column($rows, 'project_id');
    }

    public function setUserProjects(int $userId, array $projectIds): void {
        $this->delete('user_projects', "user_id = ?", [$userId]);
        foreach ($projectIds as $pid) {
            $pid = (int)$pid;
            if ($pid > 0) {
                try {
                    $this->insert('user_projects', [
                        'user_id' => $userId,
                        'project_id' => $pid,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                } catch (Exception $e) {}
            }
        }
    }

    public function getUserSubActivityIds(int $userId): array {
        $rows = $this->fetchAll("SELECT sub_activity_id FROM user_sub_activities WHERE user_id = ?", [$userId]);
        return array_column($rows, 'sub_activity_id');
    }

    public function setUserSubActivities(int $userId, array $subActivityIds): void {
        $this->delete('user_sub_activities', "user_id = ?", [$userId]);
        foreach ($subActivityIds as $saId) {
            $saId = (int)$saId;
            if ($saId > 0) {
                try {
                    $this->insert('user_sub_activities', [
                        'user_id' => $userId,
                        'sub_activity_id' => $saId,
                        'role_type' => 'Both',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                } catch (Exception $e) {}
            }
        }
    }

    /**
     * Build dynamic organizational hierarchy tree
     * Senior Management -> Managers -> Team Leads -> Projects/Teams -> Divisions -> Activities -> Sub-Activities -> Employees
     */
    public function getOrganizationalTree(): array {
        // 1. Fetch Users with Roles and Managers
        $users = $this->fetchAll("
            SELECT u.*, r.role_name, m.full_name as manager_name, m.user_code as manager_code
            FROM users u
            JOIN roles r ON u.role_id = r.role_id
            LEFT JOIN users m ON u.manager_id = m.id
            WHERE u.status = 'Active'
            ORDER BY u.id ASC
        ");

        $usersById = [];
        $subordinatesMap = [];
        foreach ($users as $u) {
            $usersById[$u['id']] = $u;
            $mgrId = (int)($u['manager_id'] ?? 0);
            if ($mgrId > 0) {
                $subordinatesMap[$mgrId][] = $u;
            }
        }

        // 2. Fetch Divisions with Assigned Team Leaders and Projects
        $divisions = $this->fetchAll("
            SELECT d.*, p.project_name, p.code as project_code, tl.full_name as tl_name, tl.user_code as tl_code
            FROM divisions d
            LEFT JOIN projects p ON d.project_id = p.id
            LEFT JOIN users tl ON d.team_leader_id = tl.id
            ORDER BY d.division_name ASC
        ");
        $divisionsByTl = [];
        $divisionsByProj = [];
        $tlDivisionUserIds = [];
        foreach ($divisions as $div) {
            if (!empty($div['team_leader_id'])) {
                $divisionsByTl[$div['team_leader_id']][] = $div;
                $tlDivisionUserIds[] = (int)$div['team_leader_id'];
            }
            $pId = $div['project_id'] ?? 0;
            $divisionsByProj[$pId][] = $div;
        }

        // 3. User Project Mappings
        $userProjects = $this->fetchAll("SELECT user_id, project_id FROM user_projects");
        $projectUsersMap = [];
        $userProjectsMap = [];
        foreach ($userProjects as $up) {
            $projectUsersMap[$up['project_id']][] = (int)$up['user_id'];
            $userProjectsMap[$up['user_id']][] = (int)$up['project_id'];
        }

        // 4. Compact Projects Summary
        $projects = $this->getProjects();
        $activities = $this->fetchAll("SELECT * FROM activities");
        $actCountByDiv = [];
        foreach ($activities as $act) {
            $actCountByDiv[$act['division_id'] ?? 0] = ($actCountByDiv[$act['division_id'] ?? 0] ?? 0) + 1;
        }

        $projectsById = [];
        foreach ($projects as $p) {
            $pId = $p['id'];
            $projDivs = $divisionsByProj[$pId] ?? [];
            $totalActs = 0;
            foreach ($projDivs as $pd) {
                $totalActs += ($actCountByDiv[$pd['id']] ?? 0);
            }
            $p['divisions'] = $projDivs;
            $p['division_count'] = count($projDivs);
            $p['activity_count'] = $totalActs;
            $p['team_count'] = count($projectUsersMap[$pId] ?? []);
            $projectsById[$pId] = $p;
        }

        // 5. Intelligent Role Classification
        $executives = [];
        $managers = [];
        $teamLeads = [];
        $employees = [];

        foreach ($users as $u) {
            $rId = (int)$u['role_id'];
            $uCode = $u['user_code'];
            $rName = $u['role_name'] ?? '';
            $uId = (int)$u['id'];

            // Level 1: Executives (Super Admin, Senior Management)
            if ($rId === 1 || $rId === 5 || in_array($uCode, ['EMP001', 'EMP009']) || in_array($rName, ['Super Admin', 'Senior Management'])) {
                $executives[] = $u;
                continue;
            }

            // Level 2: Operations Managers
            // Users who manage TLs or have Manager role / code MGR
            $hasSubManager = false;
            foreach ($subordinatesMap[$uId] ?? [] as $sub) {
                if (!empty($subordinatesMap[$sub['id']])) {
                    $hasSubManager = true;
                    break;
                }
            }

            if ($uCode === 'EMP002' || str_starts_with($uCode, 'MGR') || stripos($rName, 'Manager') !== false || stripos($u['designation'] ?? '', 'Manager') !== false || $hasSubManager) {
                $managers[] = $u;
                continue;
            }

            // Level 3: Team Leads
            // Users managing employees or assigned as division leads (excluding standard Quality Checkers and Makers)
            $isExplicitLead = ($uCode === 'EMP003' || str_starts_with($uCode, 'TL') || stripos($rName, 'Lead') !== false || stripos($u['designation'] ?? '', 'Lead') !== false);
            $managesPeople = !empty($subordinatesMap[$uId]) && $rId !== 4;
            $isDivisionLead = in_array($uId, $tlDivisionUserIds) && $rId !== 4 && $rId !== 3;

            if ($isExplicitLead || $managesPeople || $isDivisionLead) {
                $teamLeads[] = $u;
                continue;
            }

            // Level 4: Operations Employees (Makers, Quality Checkers, Associates)
            $employees[] = $u;
        }

        // 6. Assemble Tree per Manager
        $managerTrees = [];
        $assignedTlIds = [];
        $assignedEmpIds = [];

        foreach ($managers as $m) {
            $mId = $m['id'];

            // Mapped Projects
            $mPids = $userProjectsMap[$mId] ?? [];
            $mProjects = [];
            foreach ($mPids as $pid) {
                if (isset($projectsById[$pid])) {
                    $mProjects[] = $projectsById[$pid];
                }
            }

            // Team Leads reporting to this Manager
            $mTeamLeads = array_filter($teamLeads, function($tl) use ($mId) {
                return (int)($tl['manager_id'] ?? 0) === $mId;
            });

            // If none by manager_id, fallback for seeded EMP002 -> EMP003
            if (empty($mTeamLeads) && $m['user_code'] === 'EMP002') {
                $mTeamLeads = array_filter($teamLeads, function($tl) {
                    return $tl['user_code'] === 'EMP003';
                });
            }

            $tlNodes = [];
            foreach ($mTeamLeads as $tl) {
                $tlId = $tl['id'];
                $assignedTlIds[$tlId] = true;

                // TL Projects
                $tlPids = $userProjectsMap[$tlId] ?? [];
                $tlProjects = [];
                foreach ($tlPids as $pid) {
                    if (isset($projectsById[$pid])) {
                        $tlProjects[] = $projectsById[$pid];
                    }
                }

                // TL Assigned Divisions
                $tlDivisions = $divisionsByTl[$tlId] ?? [];

                // Employees reporting to this TL
                $tlEmployees = array_filter($employees, function($e) use ($tlId) {
                    return (int)($e['manager_id'] ?? 0) === $tlId;
                });
                foreach ($tlEmployees as $te) {
                    $assignedEmpIds[$te['id']] = true;
                }

                $tlNodes[] = [
                    'team_lead' => $tl,
                    'projects' => array_values($tlProjects),
                    'divisions' => $tlDivisions,
                    'employees' => array_values($tlEmployees)
                ];
            }

            // Direct Employees reporting to this Manager without intermediary TL
            $directEmployees = array_filter($employees, function($e) use ($mId, $assignedEmpIds) {
                return (int)($e['manager_id'] ?? 0) === $mId && empty($assignedEmpIds[$e['id']]);
            });
            foreach ($directEmployees as $de) {
                $assignedEmpIds[$de['id']] = true;
            }

            $managerTrees[] = [
                'manager' => $m,
                'projects' => array_values($mProjects),
                'team_leads' => $tlNodes,
                'direct_employees' => array_values($directEmployees)
            ];
        }

        // Standalone Team Leads (not mapped to a manager)
        $unassignedTls = [];
        foreach ($teamLeads as $tl) {
            if (empty($assignedTlIds[$tl['id']])) {
                $tlId = $tl['id'];
                $tlPids = $userProjectsMap[$tlId] ?? [];
                $tlProjects = [];
                foreach ($tlPids as $pid) {
                    if (isset($projectsById[$pid])) {
                        $tlProjects[] = $projectsById[$pid];
                    }
                }
                $tlEmployees = array_filter($employees, function($e) use ($tlId) {
                    return (int)($e['manager_id'] ?? 0) === $tlId;
                });
                $unassignedTls[] = [
                    'team_lead' => $tl,
                    'projects' => array_values($tlProjects),
                    'divisions' => $divisionsByTl[$tlId] ?? [],
                    'employees' => array_values($tlEmployees)
                ];
            }
        }

        // Standalone Employees
        $unassignedEmployees = [];
        foreach ($employees as $e) {
            if (empty($assignedEmpIds[$e['id']])) {
                $unassignedEmployees[] = $e;
            }
        }

        return [
            'executives' => $executives,
            'managers' => $managerTrees,
            'unassigned_team_leads' => $unassignedTls,
            'unassigned_employees' => $unassignedEmployees,
            'all_projects' => array_values($projectsById),
            'all_users' => array_values($usersById),
            'counts' => [
                'executives' => count($executives),
                'managers' => count($managers),
                'team_leads' => count($teamLeads),
                'employees' => count($employees),
                'projects' => count($projectsById)
            ]
        ];
    }
}
