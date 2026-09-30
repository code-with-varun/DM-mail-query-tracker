<?php
/**
 * Reports Controller
 * Filterable reports and native CSV / Excel export
 */

class Reports extends Controller {
    public function index() {
        $this->requireAuth();
        
        $reportModel = $this->model('Report_model');
        $activityModel = $this->model('Activity_model');
        $userModel = $this->model('User_model');

        $filters = [
            'start_date' => sanitize($_GET['start_date'] ?? date('Y-m-01')),
            'end_date' => sanitize($_GET['end_date'] ?? date('Y-m-d')),
            'status' => sanitize($_GET['status'] ?? ''),
            'activity_id' => sanitize($_GET['activity_id'] ?? ''),
            'allocated_to' => sanitize($_GET['allocated_to'] ?? '')
        ];

        $tickets = $reportModel->getFilteredTickets($filters);
        $performance = $reportModel->getEmployeePerformanceReport();

        $this->render('reports/index', [
            'title' => 'Reports & Analytics',
            'tickets' => $tickets,
            'performance' => $performance,
            'filters' => $filters,
            'activities' => $activityModel->getActivities(),
            'users' => $userModel->getEmployees()
        ]);
    }

    public function export_csv() {
        $this->requireAuth();
        
        $reportModel = $this->model('Report_model');
        $filters = [
            'start_date' => sanitize($_GET['start_date'] ?? ''),
            'end_date' => sanitize($_GET['end_date'] ?? ''),
            'status' => sanitize($_GET['status'] ?? ''),
            'activity_id' => sanitize($_GET['activity_id'] ?? ''),
            'allocated_to' => sanitize($_GET['allocated_to'] ?? '')
        ];

        $tickets = $reportModel->getFilteredTickets($filters);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=MQT_Report_' . date('Y-m-d_H-i') . '.csv');

        $output = fopen('php://output', 'w');

        // CSV Header Row
        fputcsv($output, [
            'Ticket Number', 'Type', 'Received Date', 'From', 'Subject', 
            'Division', 'Activity', 'Sub Activity', 'Status', 'Priority', 
            'TAT Date Time', 'Allocated To', 'Agency Code', 'Manager Name', 'Created Date'
        ]);

        foreach ($tickets as $t) {
            fputcsv($output, [
                $t['ticket_number'],
                $t['ticket_type'],
                $t['received_datetime'],
                $t['from_address'],
                $t['subject'],
                $t['division_name'] ?? '',
                $t['activity_name'] ?? '',
                $t['sub_activity_name'] ?? '',
                $t['status'],
                $t['priority'],
                $t['tat_datetime'],
                $t['allocated_user_name'] ?? 'Unassigned',
                $t['agency_code'] ?? '',
                $t['manager_name'] ?? '',
                $t['created_at']
            ]);
        }

        fclose($output);
        exit;
    }

    public function production() {
        $this->requireAuth();

        $reportModel = $this->model('Report_model');
        $activityModel = $this->model('Activity_model');
        $userModel = $this->model('User_model');

        $filters = [
            'start_date' => sanitize($_GET['start_date'] ?? date('Y-m-01')),
            'end_date' => sanitize($_GET['end_date'] ?? date('Y-m-d')),
            'activity_id' => !empty($_GET['activity_id']) ? (int)$_GET['activity_id'] : null,
            'allocated_to' => !empty($_GET['allocated_to']) ? (int)$_GET['allocated_to'] : null
        ];

        // For employee role (role_id == 3), default to their own tasks unless specified
        if (Session::get('role_id') == 3 && empty($filters['allocated_to'])) {
            $filters['allocated_to'] = Session::get('user_id');
        }

        $report = $reportModel->getDaywiseProductionReport($filters);

        // Summary KPI Metrics
        $totalNew = 0;
        $totalClosed = 0;
        $workingDays = count($report);

        foreach ($report as $r) {
            $totalNew += $r['new_tasks'];
            $totalClosed += $r['closed_tasks'];
        }

        $latestPending = !empty($report[0]) ? $report[0]['pending_tasks'] : 0;
        $avgClosed = $workingDays > 0 ? round($totalClosed / $workingDays, 1) : 0;

        $this->render('reports/production', [
            'title' => 'Daywise Production Tracker',
            'report' => $report,
            'filters' => $filters,
            'activities' => $activityModel->getActivities(),
            'users' => $userModel->getEmployees(),
            'kpis' => [
                'total_new' => $totalNew,
                'total_closed' => $totalClosed,
                'latest_pending' => $latestPending,
                'avg_closed' => $avgClosed,
                'working_days' => $workingDays
            ]
        ]);
    }

    public function export_production_csv() {
        $this->requireAuth();

        $reportModel = $this->model('Report_model');
        $filters = [
            'start_date' => sanitize($_GET['start_date'] ?? date('Y-m-01')),
            'end_date' => sanitize($_GET['end_date'] ?? date('Y-m-d')),
            'activity_id' => !empty($_GET['activity_id']) ? (int)$_GET['activity_id'] : null,
            'allocated_to' => !empty($_GET['allocated_to']) ? (int)$_GET['allocated_to'] : null
        ];

        if (Session::get('role_id') == 3 && empty($filters['allocated_to'])) {
            $filters['allocated_to'] = Session::get('user_id');
        }

        $report = $reportModel->getDaywiseProductionReport($filters);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Daywise_Production_Tracker_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');

        fputcsv($output, [
            'Date', 'Day', 'Opening Balance (Task)', 'New Tasks', 'Closed Tasks', 
            'Pending Tasks (Closing Balance)', 'Total Workload', 'Closure Rate (%)'
        ]);

        foreach ($report as $r) {
            fputcsv($output, [
                $r['date_formatted'],
                $r['day_name'],
                $r['opening_balance'],
                $r['new_tasks'],
                $r['closed_tasks'],
                $r['pending_tasks'],
                $r['total_workload'],
                $r['closure_rate'] . '%'
            ]);
        }

        fclose($output);
        exit;
    }
}

