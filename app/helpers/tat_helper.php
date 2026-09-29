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

function get_tat_badge(string $tatDatetime, string $status = ''): string {
    $tatStatus = calculate_tat_status($tatDatetime, $status);

    if ($tatStatus === 'Closed') {
        return '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary fw-bold fs-8"><i class="fas fa-check-circle me-1"></i>Closed</span>';
    } elseif ($tatStatus === 'Within TAT') {
        return '<span class="badge bg-success bg-opacity-10 text-success border border-success fw-bold fs-8"><i class="fas fa-clock me-1"></i>Within TAT</span>';
    } elseif ($tatStatus === 'Due Today') {
        return '<span class="badge bg-warning bg-opacity-15 text-dark border border-warning fw-bold fs-8"><i class="fas fa-exclamation-triangle me-1 text-warning"></i>Due Today</span>';
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
            return '<span class="badge bg-warning bg-opacity-15 text-dark border border-warning fw-bold fs-8"><i class="fas fa-hourglass-half me-1 text-warning"></i>Pending</span>';
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
    if (empty($categoryName) || $categoryName === '-') {
        return '<span class="text-muted fs-8">-</span>';
    }
    $catLower = strtolower($categoryName);
    if (str_contains($catLower, 'hold')) {
        return '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger fw-bold fs-8"><i class="fas fa-hand-paper me-1"></i>' . htmlspecialchars($categoryName) . '</span>';
    } elseif (str_contains($catLower, 'input')) {
        return '<span class="badge bg-info bg-opacity-10 text-info border border-info fw-bold fs-8"><i class="fas fa-file-import me-1"></i>' . htmlspecialchars($categoryName) . '</span>';
    } elseif (str_contains($catLower, 'query')) {
        return '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary fw-bold fs-8"><i class="fas fa-question-circle me-1"></i>' . htmlspecialchars($categoryName) . '</span>';
    } elseif (str_contains($catLower, 'rerun')) {
        return '<span class="badge bg-warning bg-opacity-15 text-dark border border-warning fw-bold fs-8"><i class="fas fa-redo me-1 text-warning"></i>' . htmlspecialchars($categoryName) . '</span>';
    } elseif (str_contains($catLower, 'grid')) {
        return '<span class="badge bg-purple bg-opacity-10 text-purple border border-purple fw-bold fs-8"><i class="fas fa-th me-1"></i>' . htmlspecialchars($categoryName) . '</span>';
    } elseif (str_contains($catLower, 'checklist')) {
        return '<span class="badge bg-teal bg-opacity-10 text-teal border border-teal fw-bold fs-8"><i class="fas fa-tasks me-1"></i>' . htmlspecialchars($categoryName) . '</span>';
    } else {
        return '<span class="badge bg-light text-dark border fw-bold fs-8">' . htmlspecialchars($categoryName) . '</span>';
    }
}

function format_datetime(string $datetime): string {
    return date('d M Y, h:i A', strtotime($datetime));
}

function get_current_bucket_info(array $ticket): array {
    $stage = $ticket['stage'] ?? 'Maker Phase';
    $status = $ticket['status'] ?? 'New';

    if (in_array($status, ['Closed', 'Completed', 'Cancelled'])) {
        $userName = !empty($ticket['replied_user_name']) ? $ticket['replied_user_name'] : ($ticket['allocated_user_name'] ?? 'System');
        $userCode = !empty($ticket['replied_user_code']) ? $ticket['replied_user_code'] : ($ticket['allocated_user_code'] ?? '');
        return [
            'stage_name' => 'Closed',
            'role_label' => 'Closed',
            'user_name' => $userName,
            'user_code' => $userCode,
            'display_string' => $userName . ($userCode ? " ({$userCode})" : ''),
            'badge_class' => 'bg-secondary bg-opacity-10 text-secondary border border-secondary',
            'icon' => 'fa-check-double'
        ];
    }

    if ($stage === 'Checker Phase') {
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

    if ($stage === 'Delivery Phase') {
        $userName = !empty($ticket['allocated_user_name']) ? $ticket['allocated_user_name'] : 'Delivery Desk';
        $userCode = $ticket['allocated_user_code'] ?? '';
        return [
            'stage_name' => 'Delivery Phase',
            'role_label' => 'Delivery Desk',
            'user_name' => $userName,
            'user_code' => $userCode,
            'display_string' => $userName . ($userCode ? " ({$userCode})" : ''),
            'badge_class' => 'bg-success bg-opacity-10 text-success border border-success',
            'icon' => 'fa-paper-plane'
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
