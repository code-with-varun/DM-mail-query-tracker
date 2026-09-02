<?php
/**
 * Dashboard Controller
 * Custom Views for Super Admin, Admin, Employee
 */

class Dashboard extends Controller {
    public function index() {
        $this->requireAuth();
        
        $userId = Session::get('user_id');
        $roleId = Session::get('role_id');

        $ticketModel = $this->model('Ticket_model');
        $userModel = $this->model('User_model');
        $recurringModel = $this->model('Recurring_model');

        $stats = $ticketModel->getDashboardStats($userId, $roleId);
        $recentTickets = $ticketModel->getTickets(['limit' => 10], $userId, $roleId);

        $viewName = 'employee';
        if ($roleId == 1) {
            $viewName = 'superadmin';
            $data['total_admins'] = count($userModel->getAdmins());
            $data['total_employees'] = count($userModel->getEmployees());
            $data['recurring_count'] = count($recurringModel->getTemplates());
        } elseif ($roleId == 2) {
            $viewName = 'admin';
            $data['team_employees'] = $userModel->getTeamEmployees($userId);
        }

        $data['title'] = 'Dashboard - ' . Session::get('role_name');
        $data['stats'] = $stats;
        $data['recent_tickets'] = $recentTickets;

        $this->render("dashboard/{$viewName}", $data);
    }
}
