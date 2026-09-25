<?php
/**
 * Dashboard Controller
 * Custom Slicer Analytics Views for Super Admin, Admin, Employee
 */

class Dashboard extends Controller {
    public function index() {
        $this->requireAuth();
        
        $userId = Session::get('user_id');
        $roleId = Session::get('role_id');

        $ticketModel = $this->model('Ticket_model');
        $userModel = $this->model('User_model');
        $activityModel = $this->model('Activity_model');

        // Extract Slicer Filters
        $filters = [
            'division_id' => $_GET['division_id'] ?? '',
            'activity_id' => $_GET['activity_id'] ?? '',
            'allocated_to' => $_GET['allocated_to'] ?? '',
            'priority' => $_GET['priority'] ?? '',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? ''
        ];

        $stats = $ticketModel->getDashboardStats($userId, $roleId, $filters);

        $viewName = 'employee';
        if ($roleId == 1) {
            $viewName = 'superadmin';
            $data['total_admins'] = count($userModel->getAdmins());
            $data['total_employees'] = count($userModel->getEmployees());
        } elseif ($roleId == 2) {
            $viewName = 'admin';
            $data['team_employees'] = $userModel->getTeamEmployees($userId);
        }

        // Master Lists for Slicer Dropdowns
        $data['slicer_divisions'] = $activityModel->getDivisions();
        $data['slicer_activities'] = $activityModel->getActivities();
        $data['slicer_employees'] = $userModel->getEmployees();
        $data['filters'] = $filters;

        // Analytics Datasets
        $data['title'] = 'Dashboard - ' . Session::get('role_name');
        $data['stats'] = $stats;
        $data['status_breakdown'] = $ticketModel->getStatusBreakdown($userId, $roleId, $filters);
        $data['division_breakdown'] = $ticketModel->getDivisionBreakdown($userId, $roleId, $filters);
        $data['monthly_trend'] = $ticketModel->getMonthlyTrend($userId, $roleId, $filters);
        $data['priority_breakdown'] = $ticketModel->getPriorityBreakdown($userId, $roleId, $filters);
        $data['category_breakdown'] = $ticketModel->getCategoryBreakdown($userId, $roleId, $filters);
        $data['employee_breakdown'] = $ticketModel->getEmployeeBreakdown($userId, $roleId, $filters);
        $data['activity_breakdown'] = $ticketModel->getActivityBreakdown($userId, $roleId, $filters);
        $data['ticket_type_breakdown'] = $ticketModel->getTicketTypeBreakdown($userId, $roleId, $filters);
        $data['quality_sla_breakdown'] = $ticketModel->getQualitySlaBreakdown($userId, $roleId, $filters);

        $this->render("dashboard/{$viewName}", $data);
    }
}
