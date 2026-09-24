<?php
/**
 * Hold / Release Management Controller
 */

class Hold extends Controller {
    public function index() {
        $this->requireAuth();
        $ticketModel = $this->model('Ticket_model');
        
        $sql = "SELECT t.*, u_alloc.full_name as allocated_user_name, c.category_name
                FROM tickets t
                LEFT JOIN users u_alloc ON t.allocated_to = u_alloc.id
                LEFT JOIN ticket_categories c ON t.category_id = c.id
                WHERE t.status IN ('On Hold', 'Released') OR c.category_slug IN ('hold', 'release')
                ORDER BY t.id DESC";
        $tickets = $ticketModel->fetchAll($sql);

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
