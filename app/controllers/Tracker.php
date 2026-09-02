<?php
/**
 * Tracker Controller (Input & Delivery Trackers)
 */

class Tracker extends Controller {
    public function input() {
        $this->requireAuth();
        $trackerModel = $this->model('Tracker_model');
        $userModel = $this->model('User_model');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('tracker/input');
            }

            $source = sanitize($_POST['source'] ?? '');
            $receivedFrom = sanitize($_POST['received_from'] ?? '');
            $docRef = sanitize($_POST['document_reference'] ?? '');

            if (empty($source) || empty($receivedFrom) || empty($docRef)) {
                Session::setFlash('danger', 'Source, Received From, and Document Reference are mandatory.');
                redirect('tracker/input');
            }

            $trackerModel->createInputLog([
                'source' => $source,
                'received_date' => date('Y-m-d H:i:s', strtotime($_POST['received_date'] ?? 'now')),
                'received_from' => $receivedFrom,
                'document_reference' => $docRef,
                'assigned_to' => !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null,
                'status' => sanitize($_POST['status'] ?? 'Received'),
                'remarks' => sanitize($_POST['remarks'] ?? ''),
                'created_by' => Session::get('user_id')
            ]);

            Session::setFlash('success', 'Input item logged successfully.');
            redirect('tracker/input');
        }

        $logs = $trackerModel->getInputLogs();
        $this->render('tracker/input', [
            'title' => 'Input Tracker',
            'logs' => $logs,
            'users' => $userModel->getEmployees()
        ]);
    }

    public function delivery() {
        $this->requireAuth();
        $trackerModel = $this->model('Tracker_model');
        $ticketModel = $this->model('Ticket_model');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('tracker/delivery');
            }

            $deliveredTo = sanitize($_POST['delivered_to'] ?? '');
            $mode = sanitize($_POST['delivery_mode'] ?? 'Email');

            if (empty($deliveredTo)) {
                Session::setFlash('danger', 'Delivered To field is required.');
                redirect('tracker/delivery');
            }

            $attachmentPath = null;
            if (!empty($_FILES['attachment']['name'])) {
                $uploadDir = __DIR__ . '/../../public/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $fileName = time() . '_del_' . basename($_FILES['attachment']['name']);
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadDir . $fileName)) {
                    $attachmentPath = 'public/uploads/' . $fileName;
                }
            }

            $trackerModel->createDeliveryLog([
                'ticket_id' => !empty($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : null,
                'delivered_to' => $deliveredTo,
                'delivery_date' => date('Y-m-d H:i:s', strtotime($_POST['delivery_date'] ?? 'now')),
                'delivery_mode' => $mode,
                'ack_received' => sanitize($_POST['ack_received'] ?? 'Pending'),
                'remarks' => sanitize($_POST['remarks'] ?? ''),
                'attachment_path' => $attachmentPath,
                'created_by' => Session::get('user_id')
            ]);

            Session::setFlash('success', 'Output / Delivery log created successfully.');
            redirect('tracker/delivery');
        }

        $logs = $trackerModel->getDeliveryLogs();
        $this->render('tracker/delivery', [
            'title' => 'Output / Delivery Tracker',
            'logs' => $logs,
            'tickets' => $ticketModel->getTickets([], Session::get('user_id'), Session::get('role_id'))
        ]);
    }
}
