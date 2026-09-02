<?php
/**
 * Hold / Release Management Controller
 */

class Hold extends Controller {
    public function index() {
        $this->requireAuth();
        $ticketModel = $this->model('Ticket_model');
        
        $tickets = $ticketModel->getTickets(['status' => 'On Hold'], Session::get('user_id'), Session::get('role_id'));

        $this->render('hold/index', [
            'title' => 'Hold & Release Management',
            'tickets' => $tickets
        ]);
    }

    public function release() {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('hold');
            }

            $ticketId = (int)($_POST['ticket_id'] ?? 0);
            $remarks = sanitize($_POST['remarks'] ?? '');

            $ticketModel = $this->model('Ticket_model');
            if ($ticketModel->releaseTicket($ticketId, $remarks, Session::get('user_id'))) {
                Session::setFlash('success', 'Ticket released from hold successfully.');
            } else {
                Session::setFlash('danger', 'Failed to release ticket.');
            }
            redirect('tickets/view/' . $ticketId);
        }
    }
}
