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

        $tickets = $ticketModel->getRosterTickets($targetDate, Session::get('user_id'), Session::get('role_id'));

        $this->render('tickets/roster', [
            'title' => 'Daily Roster & Operations Planner',
            'targetDate' => $targetDate,
            'tickets' => $tickets,
            'users' => $userModel->getEmployees()
        ]);
    }
}
