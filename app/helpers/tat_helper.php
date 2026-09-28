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
        return '<span class="badge bg-secondary"><i class="fas fa-check-circle me-1"></i>Completed</span>';
    } elseif ($tatStatus === 'Within TAT') {
        return '<span class="badge bg-success"><i class="fas fa-clock me-1"></i>Within TAT</span>';
    } elseif ($tatStatus === 'Due Today') {
        return '<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i>Due Today</span>';
    } else {
        return '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Overdue</span>';
    }
}

function get_status_badge(string $status): string {
    switch ($status) {
        case 'New': return '<span class="badge bg-primary">New</span>';
        case 'Assigned': return '<span class="badge bg-info text-dark">Assigned</span>';
        case 'In Progress': return '<span class="badge bg-primary">In Progress</span>';
        case 'Pending': return '<span class="badge bg-warning text-dark">Pending</span>';
        case 'Waiting for Customer': return '<span class="badge bg-secondary">Waiting Customer</span>';
        case 'Waiting for Internal Team': return '<span class="badge bg-secondary">Waiting Team</span>';
        case 'On Hold': return '<span class="badge bg-dark">On Hold</span>';
        case 'Released': return '<span class="badge bg-info text-dark">Released</span>';
        case 'Completed': return '<span class="badge bg-success">Completed</span>';
        case 'Closed': return '<span class="badge bg-success">Closed</span>';
        case 'Cancelled': return '<span class="badge bg-secondary">Cancelled</span>';
        default: return '<span class="badge bg-secondary">' . htmlspecialchars($status) . '</span>';
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
            'badge_class' => 'bg-secondary'
        ];
    }

    if ($stage === 'Checker Phase') {
        if (!empty($ticket['checker_user_name'])) {
            $userName = $ticket['checker_user_name'];
            $userCode = $ticket['checker_user_code'] ?? '';
            return [
                'stage_name' => 'Checker Phase',
                'role_label' => 'Checker',
                'user_name' => $userName,
                'user_code' => $userCode,
                'display_string' => $userName . ($userCode ? " ({$userCode})" : ''),
                'badge_class' => 'bg-warning text-dark'
            ];
        } else {
            return [
                'stage_name' => 'Checker Phase',
                'role_label' => 'Checker',
                'user_name' => 'Unassigned Checker Queue',
                'user_code' => '',
                'display_string' => 'Unassigned Checker Queue',
                'badge_class' => 'bg-warning text-dark'
            ];
        }
    }

    if ($stage === 'Delivery Phase') {
        $userName = !empty($ticket['allocated_user_name']) ? $ticket['allocated_user_name'] : 'Delivery Desk';
        $userCode = $ticket['allocated_user_code'] ?? '';
        return [
            'stage_name' => 'Delivery Phase',
            'role_label' => 'Delivery',
            'user_name' => $userName,
            'user_code' => $userCode,
            'display_string' => $userName . ($userCode ? " ({$userCode})" : ''),
            'badge_class' => 'bg-success'
        ];
    }

    // Default: Maker Phase
    $userName = !empty($ticket['allocated_user_name']) ? $ticket['allocated_user_name'] : 'Unassigned Pool';
    $userCode = $ticket['allocated_user_code'] ?? '';
    return [
        'stage_name' => 'Maker Phase',
        'role_label' => 'Maker',
        'user_name' => $userName,
        'user_code' => $userCode,
        'display_string' => $userName . ($userCode ? " ({$userCode})" : ''),
        'badge_class' => 'bg-primary'
    ];
}
