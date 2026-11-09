<?php
/**
 * TAT SLA Helper Engine
 * Rules:
 * Current Time < TAT -> Within TAT (Green)
 * Same Calendar Day -> Due Today (Orange)
 * Current Time > TAT -> Overdue (Red)
 */

function calculate_tat_status(string $tatDatetime, string $status = ''): string {
    if (in_array($status, ['Completed', 'Closed', 'Cancelled'])) {
        return 'Closed';
    }

    $now = time();
    $tat = strtotime($tatDatetime);

    if ($now > $tat) {
        return 'Overdue';
    }

    $todayEnd = strtotime('today 23:59:59');
    if ($tat <= $todayEnd) {
        return 'Due Today';
    }

    return 'Within TAT';
}

function get_ticket_type_badge(?string $ticketType, bool $isTask = false): string {
    $type = !empty($ticketType) ? $ticketType : ($isTask ? 'Task Ticket' : 'Query Ticket');
    $typeLower = strtolower($type);
    
    if (str_contains($typeLower, 'query')) {
        return '<span class="badge badge-query-ticket"><i class="fas fa-question-circle me-1"></i>Query Ticket</span>';
    } elseif (str_contains($typeLower, 'internal')) {
        return '<span class="badge badge-internal-task"><i class="fas fa-tasks me-1"></i>Internal Task</span>';
    } else {
        return '<span class="badge badge-task-ticket"><i class="fas fa-file-alt me-1"></i>Task Ticket (File Task)</span>';
    }
}

function get_tat_badge(string $tatDatetime, string $status = ''): string {
    $tatStatus = calculate_tat_status($tatDatetime, $status);

    if ($tatStatus === 'Closed') {
        return '';
    } elseif ($tatStatus === 'Within TAT') {
        return '<span class="badge bg-success bg-opacity-10 text-success border border-success fw-bold fs-8"><i class="fas fa-clock me-1"></i>Within TAT</span>';
    } elseif ($tatStatus === 'Due Today') {
        return '<span class="badge bg-warning bg-opacity-15 text-dark border border-warning fw-bold fs-8"><i class="fas fa-exclamation-triangle me-1 text-dark"></i>Due Today</span>';
    } else {
        return '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger fw-bold fs-8 animate__animated animate__pulse animate__infinite"><i class="fas fa-fire me-1"></i>Overdue SLA</span>';
    }
}

function get_status_badge(string $status): string {
    switch ($status) {
        case 'New': 
            return '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary fw-bold fs-8"><i class="fas fa-asterisk me-1"></i>New</span>';
        case 'Assigned': 
            return '<span class="badge bg-info bg-opacity-10 text-info border border-info fw-bold fs-8"><i class="fas fa-user-check me-1"></i>Assigned</span>';
        case 'In Progress': 
            return '<span class="badge bg-primary bg-opacity-15 text-primary border border-primary fw-bold fs-8"><i class="fas fa-spinner fa-spin me-1"></i>In Progress</span>';
        case 'Pending': 
            return '<span class="badge bg-warning bg-opacity-15 text-dark border border-warning fw-bold fs-8"><i class="fas fa-hourglass-half me-1 text-dark"></i>Pending</span>';
        case 'Waiting for Customer': 
            return '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary fw-bold fs-8"><i class="fas fa-user-clock me-1"></i>Waiting Customer</span>';
        case 'Waiting for Internal Team': 
            return '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary fw-bold fs-8"><i class="fas fa-users-cog me-1"></i>Waiting Team</span>';
        case 'On Hold': 
            return '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger fw-bold fs-8"><i class="fas fa-pause-circle me-1"></i>On Hold</span>';
        case 'Released': 
            return '<span class="badge bg-success bg-opacity-10 text-success border border-success fw-bold fs-8"><i class="fas fa-play-circle me-1"></i>Released</span>';
        case 'Completed': 
        case 'Closed': 
            return '<span class="badge bg-success bg-opacity-10 text-success border border-success fw-bold fs-8"><i class="fas fa-check-circle me-1"></i>Closed</span>';
        case 'Cancelled': 
            return '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary fw-bold fs-8"><i class="fas fa-ban me-1"></i>Cancelled</span>';
        default: 
            return '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary fw-bold fs-8">' . htmlspecialchars($status) . '</span>';
    }
}

function get_category_badge(?string $categoryName): string {
    if (empty($categoryName) || trim($categoryName) === '' || $categoryName === '-') {
        return '<span class="badge bg-secondary bg-opacity-10 text-muted border border-secondary border-opacity-20 fw-bold fs-8">-</span>';
    }
    $catLower = strtolower($categoryName);
    if (str_contains($catLower, 'hold')) {
        return '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger fw-bold fs-8"><i class="fas fa-hand-paper me-1"></i>' . htmlspecialchars($categoryName) . '</span>';
    } elseif (str_contains($catLower, 'input')) {
        return '<span class="badge bg-info bg-opacity-10 text-info border border-info fw-bold fs-8"><i class="fas fa-file-import me-1"></i>' . htmlspecialchars($categoryName) . '</span>';
    } elseif (str_contains($catLower, 'query')) {
        return '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary fw-bold fs-8"><i class="fas fa-question-circle me-1"></i>' . htmlspecialchars($categoryName) . '</span>';
    } elseif (str_contains($catLower, 'rerun')) {
        return '<span class="badge bg-warning bg-opacity-15 text-dark border border-warning fw-bold fs-8"><i class="fas fa-redo me-1 text-dark"></i>' . htmlspecialchars($categoryName) . '</span>';
    } elseif (str_contains($catLower, 'grid')) {
        return '<span class="badge bg-purple bg-opacity-10 text-purple border border-purple fw-bold fs-8"><i class="fas fa-th me-1"></i>' . htmlspecialchars($categoryName) . '</span>';
    } elseif (str_contains($catLower, 'checklist')) {
        return '<span class="badge bg-teal bg-opacity-10 text-teal border border-teal fw-bold fs-8"><i class="fas fa-tasks me-1"></i>' . htmlspecialchars($categoryName) . '</span>';
    } else {
        return '<span class="badge bg-light text-dark border fw-bold fs-8"><i class="fas fa-tag me-1 text-secondary"></i>' . htmlspecialchars($categoryName) . '</span>';
    }
}

function format_datetime(string $datetime): string {
    return date('d M Y, h:i A', strtotime($datetime));
}

function format_date(?string $date): string {
    if (empty($date)) return '-';
    return date('d M Y', strtotime($date));
}

function get_current_bucket_info(array $ticket): array {
    $stage = $ticket['stage'] ?? 'Maker Phase';
    $status = $ticket['status'] ?? 'New';

    if (in_array($status, ['Closed', 'Completed', 'Cancelled']) || $stage === 'Closed') {
        $userName = !empty($ticket['replied_user_name']) ? $ticket['replied_user_name'] : ($ticket['allocated_user_name'] ?? 'System');
        $userCode = !empty($ticket['replied_user_code']) ? $ticket['replied_user_code'] : ($ticket['allocated_user_code'] ?? '');
        return [
            'stage_name' => 'Closed',
            'role_label' => 'Closed Phase',
            'user_name' => $userName,
            'user_code' => $userCode,
            'display_string' => $userName . ($userCode ? " ({$userCode})" : ''),
            'badge_class' => 'bg-secondary bg-opacity-10 text-secondary border border-secondary',
            'icon' => 'fa-check-double'
        ];
    }

    if ($stage === 'Checker Phase' || $stage === 'Checker Queue' || $stage === 'Error Log') {
        if (!empty($ticket['checker_user_name'])) {
            $userName = $ticket['checker_user_name'];
            $userCode = $ticket['checker_user_code'] ?? '';
            return [
                'stage_name' => 'Checker Queue',
                'role_label' => 'Checker Queue',
                'user_name' => $userName,
                'user_code' => $userCode,
                'display_string' => $userName . ($userCode ? " ({$userCode})" : ''),
                'badge_class' => 'bg-warning bg-opacity-15 text-dark border border-warning',
                'icon' => 'fa-user-check'
            ];
        } else {
            return [
                'stage_name' => 'Checker Queue',
                'role_label' => 'Checker Queue',
                'user_name' => 'Unassigned Checker Pool',
                'user_code' => '',
                'display_string' => 'Unassigned Checker Pool',
                'badge_class' => 'bg-warning bg-opacity-15 text-dark border border-warning',
                'icon' => 'fa-user-clock'
            ];
        }
    }

    if ($stage === 'Delivery Phase' || $stage === 'Delivery') {
        $userName = !empty($ticket['allocated_user_name']) ? $ticket['allocated_user_name'] : 'Delivery Desk';
        $userCode = $ticket['allocated_user_code'] ?? '';
        return [
            'stage_name' => 'Delivery Phase',
            'role_label' => 'Delivery Desk',
            'user_name' => $userName,
            'user_code' => $userCode,
            'display_string' => $userName . ($userCode ? " ({$userCode})" : ''),
            'badge_class' => 'bg-info bg-opacity-10 text-info border border-info',
            'icon' => 'fa-paper-plane'
        ];
    }

    if ($stage === 'Acknowledgement') {
        return [
            'stage_name' => 'Acknowledgement',
            'role_label' => 'Ack Receive',
            'user_name' => 'Client / Operation Desk',
            'user_code' => '',
            'display_string' => 'Client / Operation Desk',
            'badge_class' => 'bg-purple bg-opacity-10 text-purple border border-purple',
            'icon' => 'fa-envelope-open-text'
        ];
    }

    if ($stage === 'Approval Queue') {
        return [
            'stage_name' => 'Approval Queue',
            'role_label' => 'Business Approval',
            'user_name' => 'Business Head / Manager',
            'user_code' => '',
            'display_string' => 'Business Head / Manager',
            'badge_class' => 'bg-primary bg-opacity-10 text-primary border border-primary',
            'icon' => 'fa-user-shield'
        ];
    }

    if ($stage === 'Uploader Queue') {
        return [
            'stage_name' => 'Uploader Queue',
            'role_label' => 'Claims Upload',
            'user_name' => 'Claims Uploader Desk',
            'user_code' => '',
            'display_string' => 'Claims Uploader Desk',
            'badge_class' => 'bg-info bg-opacity-10 text-info border border-info',
            'icon' => 'fa-cloud-upload-alt'
        ];
    }

    if ($stage === 'Dump Sharing') {
        return [
            'stage_name' => 'Dump Sharing',
            'role_label' => 'Finance Share',
            'user_name' => 'Finance Team',
            'user_code' => '',
            'display_string' => 'Finance Team',
            'badge_class' => 'bg-warning bg-opacity-15 text-dark border border-warning',
            'icon' => 'fa-database'
        ];
    }

    if ($stage === 'Consol Vendor Tracker') {
        $userName = !empty($ticket['allocated_user_name']) ? $ticket['allocated_user_name'] : 'Maker';
        $userCode = $ticket['allocated_user_code'] ?? '';
        return [
            'stage_name' => 'Consol Vendor Tracker',
            'role_label' => 'Consol Vendor',
            'user_name' => $userName,
            'user_code' => $userCode,
            'display_string' => $userName . ($userCode ? " ({$userCode})" : ''),
            'badge_class' => 'bg-teal bg-opacity-10 text-teal border border-teal',
            'icon' => 'fa-truck-moving'
        ];
    }

    // Default: Maker Phase
    $userName = !empty($ticket['allocated_user_name']) ? $ticket['allocated_user_name'] : 'Unassigned Open Pool';
    $userCode = $ticket['allocated_user_code'] ?? '';
    return [
        'stage_name' => 'Maker Queue',
        'role_label' => 'Maker Queue',
        'user_name' => $userName,
        'user_code' => $userCode,
        'display_string' => $userName . ($userCode ? " ({$userCode})" : ''),
        'badge_class' => 'bg-primary bg-opacity-10 text-primary border border-primary',
        'icon' => 'fa-pencil-alt'
    ];
}
