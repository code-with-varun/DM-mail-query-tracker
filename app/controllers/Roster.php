<?php
/**
 * Daily Roster & Planner Controller
 */

class Roster extends Controller {
    public function index() {
        $this->requireAuth();

        $ticketModel = $this->model('Ticket_model');
        $userModel = $this->model('User_model');

        $targetDate = sanitize($_GET['date'] ?? date('Y-m-d'));
        if (empty($targetDate)) {
            $targetDate = date('Y-m-d');
        }

        $rawStatus = $_GET['status'] ?? [];
        $rawStage = $_GET['stage'] ?? [];
        $rawPriority = $_GET['priority'] ?? [];

        $filters = [
            'search' => sanitize($_GET['search'] ?? ''),
            'status' => is_array($rawStatus) ? array_map('sanitize', $rawStatus) : (empty($rawStatus) ? [] : [sanitize($rawStatus)]),
            'stage' => is_array($rawStage) ? array_map('sanitize', $rawStage) : (empty($rawStage) ? [] : [sanitize($rawStage)]),
            'priority' => is_array($rawPriority) ? array_map('sanitize', $rawPriority) : (empty($rawPriority) ? [] : [sanitize($rawPriority)]),
        ];

        $tickets = $ticketModel->getRosterTickets($targetDate, Session::get('user_id'), Session::get('role_id'), $filters);

        $this->render('tickets/roster', [
            'title' => 'Daily Roster & Operations Planner',
            'targetDate' => $targetDate,
            'tickets' => $tickets,
            'filters' => $filters,
            'users' => $userModel->getEmployees()
        ]);
    }
}
