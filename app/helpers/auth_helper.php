<?php
/**
 * Auth Helper Functions & Role-Based Access Control (RBAC)
 */

function is_logged_in(): bool {
    return Session::has('user_id');
}

function current_user(): ?array {
    if (!is_logged_in()) return null;
    return [
        'id' => Session::get('user_id'),
        'user_code' => Session::get('user_code'),
        'name' => Session::get('full_name'),
        'email' => Session::get('email'),
        'role_id' => Session::get('role_id'),
        'role_name' => Session::get('role_name'),
        'designation' => Session::get('designation'),
        'department' => Session::get('department')
    ];
}

// 1: Super Admin (Full technical support, masters, system settings)
function is_super_admin(): bool {
    return Session::get('role_id') == 1 || Session::get('role_name') === 'Super Admin';
}

// 2: Admin (TL) (Team Leader: task allocation, team operations, queues, PKT)
function is_team_lead(): bool {
    return Session::get('role_id') == 2 || in_array(Session::get('role_name'), ['Admin (TL)', 'Admin', 'Team Lead']);
}

// 3: Manager (Operations Manager: department oversight, team scorecards & analytics)
function is_manager(): bool {
    return Session::get('role_id') == 3 || Session::get('role_name') === 'Manager';
}

// 4: Employee (Individual Maker / Checker worker: ticket processing, daily logger & PKT)
function is_employee(): bool {
    return Session::get('role_id') == 4 || Session::get('role_name') === 'Employee';
}

// 5: Senior Management (Executive leadership: cross-project analytics, KPIs & governance)
function is_senior_management(): bool {
    return Session::get('role_id') == 5 || Session::get('role_name') === 'Senior Management';
}

// Backwards-compatible Admin check (Super Admin, Senior Management, Admin (TL), Manager)
function is_admin(): bool {
    return in_array(Session::get('role_id'), [1, 2, 3, 5]) || is_super_admin() || is_senior_management() || is_team_lead() || is_manager();
}

/**
 * Dynamic Access Rights: Get full CRUD permissions for active user on a specific page
 * Returns: ['is_allowed' => int, 'can_create' => int, 'can_edit' => int, 'can_delete' => int]
 */
function get_user_page_perms(string $pageKey): array {
    if (!is_logged_in()) {
        return ['is_allowed' => 0, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0];
    }
    
    // Super Admin always has full master CRUD access across all modules
    if (is_super_admin()) {
        return ['is_allowed' => 1, 'can_create' => 1, 'can_edit' => 1, 'can_delete' => 1];
    }

    $userId = (int)Session::get('user_id');
    $roleId = (int)Session::get('role_id');

    // In-memory static cache for fast repeated lookups DURING THIS HTTP REQUEST ONLY
    // We intentionally DO NOT store this in $_SESSION to guarantee 100% instant sync
    // across all active browser sessions when Super Admin saves changes.
    static $permsCache = [];
    $cacheKey = "{$userId}_{$roleId}";

    if (!isset($permsCache[$cacheKey])) {
        require_once APP_PATH . '/models/Permission_model.php';
        $pModel = new Permission_model();
        $permsCache[$cacheKey] = $pModel->getEffectiveUserPermissions($userId, $roleId);
    }

    $p = $permsCache[$cacheKey][$pageKey] ?? null;
    if (is_array($p)) {
        return [
            'is_allowed' => (int)($p['is_allowed'] ?? 0),
            'can_create' => (int)($p['can_create'] ?? 0),
            'can_edit'   => (int)($p['can_edit'] ?? 0),
            'can_delete' => (int)($p['can_delete'] ?? 0)
        ];
    }

    return ['is_allowed' => 0, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0];
}

/**
 * Dynamic Access Rights Check: Can active user access / view a specific page?
 */
function can_access_page(string $pageKey): bool {
    $p = get_user_page_perms($pageKey);
    return !empty($p['is_allowed']);
}

/**
 * Dynamic Access Rights Check: Can active user CREATE new records on this page/module?
 */
function can_create(string $pageKey): bool {
    $p = get_user_page_perms($pageKey);
    return !empty($p['can_create']);
}

/**
 * Dynamic Access Rights Check: Can active user UPDATE / EDIT records on this page/module?
 */
function can_edit(string $pageKey): bool {
    $p = get_user_page_perms($pageKey);
    return !empty($p['can_edit']);
}

/**
 * Dynamic Access Rights Check: Can active user DELETE records on this page/module?
 */
function can_delete(string $pageKey): bool {
    $p = get_user_page_perms($pageKey);
    return !empty($p['can_delete']);
}

/**
 * Dynamic Access Rights Check: Can active user access a specific module?
 */
function can_access_module(string $moduleKey): bool {
    if (!is_logged_in()) {
        return false;
    }
    if (is_super_admin()) {
        return true;
    }

    require_once APP_PATH . '/models/Permission_model.php';
    static $catalog = null;
    if ($catalog === null) {
        $pModel = new Permission_model();
        $catalog = $pModel->getModuleCatalog();
    }

    if (!isset($catalog[$moduleKey])) {
        return false;
    }

    foreach (array_keys($catalog[$moduleKey]['pages']) as $pageKey) {
        if (can_access_page($pageKey)) {
            return true;
        }
    }

    return false;
}

/**
 * Can user access task/ticket operational menus (My Bucket, All Tasks, Mail Tickets, Lifecycle, Trackers)?
 */
function can_access_tasks(): bool {
    return is_super_admin() || can_access_module('tickets_tasks') || can_access_module('trackers');
}

/**
 * Can user access Training & PKT LMS (Plan, Test Center, Practice, Certifications)?
 */
function can_access_pkt(): bool {
    return is_super_admin() || can_access_module('training');
}

/**
 * Can user manage PKT Question Bank?
 */
function can_manage_pkt_bank(): bool {
    return is_super_admin() || can_access_page('manage_pkt');
}

/**
 * Can user manage Workflows & Process Queues?
 */
function can_manage_workflows(): bool {
    return is_super_admin() || can_access_page('workflows') || can_access_page('queues');
}

/**
 * Can user view Performance Analytics & Scorecard?
 */
function can_view_analytics(): bool {
    return is_super_admin() || can_access_page('performance_scorecard') || can_access_page('performance_analytics');
}

/**
 * Can user view Reports & Export?
 */
function can_view_reports(): bool {
    return is_super_admin() || can_access_page('reports_export') || can_access_page('daywise_count');
}

/**
 * Can user view Employee Accounts & Skill Matrix Hub?
 */
function can_view_employees_and_skills(): bool {
    return is_super_admin() || can_access_page('employees') || can_access_page('skill_matrix');
}

/**
 * Can user access Technical Masters (Projects & Teams, Divisions, Activities, Sub-Activities, Categories)?
 */
function can_manage_masters(): bool {
    return is_super_admin() || can_access_page('master_projects');
}

/**
 * Can user view Org Hierarchy Tree?
 */
function can_view_org_hierarchy(): bool {
    return is_super_admin() || can_access_page('org_hierarchy');
}

/**
 * Check if Ticket & Workflow Module (Mail Query, Queues, Hold List) is enabled for active user/project.
 */
function is_ticket_workflow_enabled(): bool {
    if (is_super_admin() || is_senior_management()) {
        return true;
    }
    $mode = get_active_project_mode();
    return in_array($mode, ['ticket_workflow', 'hybrid']);
}

/**
 * Check if Production Count Based Module is enabled for active user/project.
 */
function is_production_count_enabled(): bool {
    if (is_super_admin() || is_senior_management()) {
        return true;
    }
    $mode = get_active_project_mode();
    return in_array($mode, ['production_count', 'hybrid']);
}

/**
 * Get active project mode for current session user.
 */
function get_active_project_mode(): string {
    if (Session::has('active_project_mode')) {
        return Session::get('active_project_mode');
    }

    $userId = Session::get('user_id');
    if (!$userId) return 'hybrid';

    $db = new Model();
    $projects = $db->fetchAll("
        SELECT p.operation_type 
        FROM projects p
        JOIN user_projects up ON p.id = up.project_id
        WHERE up.user_id = ? AND p.status = 'Active'
    ", [$userId]);

    if (empty($projects)) {
        $firstProj = $db->fetchOne("SELECT operation_type FROM projects WHERE status = 'Active' LIMIT 1");
        $mode = $firstProj['operation_type'] ?? 'production_count';
        Session::set('active_project_mode', $mode);
        return $mode;
    }

    $modes = array_column($projects, 'operation_type');
    if (in_array('hybrid', $modes)) {
        $mode = 'hybrid';
    } elseif (in_array('ticket_workflow', $modes) && in_array('production_count', $modes)) {
        $mode = 'hybrid';
    } elseif (in_array('ticket_workflow', $modes)) {
        $mode = 'ticket_workflow';
    } else {
        $mode = 'production_count';
    }

    Session::set('active_project_mode', $mode);
    return $mode;
}
