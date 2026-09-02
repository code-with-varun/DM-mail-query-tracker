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
