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
}
