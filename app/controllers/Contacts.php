<?php
/**
 * Contacts Controller (Contact Manager)
 */

class Contacts extends Controller {
    public function index() {
        $this->requireAuth();
        $contactModel = $this->model('Contact_model');

        $selectedOrg = isset($_GET['org']) ? sanitize($_GET['org']) : null;
        $contacts = $contactModel->getContacts($selectedOrg);
        $stats = $contactModel->getStats();

        $this->render('contacts/index', [
            'title' => 'Contact Manager',
            'contacts' => $contacts,
            'stats' => $stats,
            'selectedOrg' => $selectedOrg
        ]);
    }

    public function store() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('contacts');
            }

            $name = sanitize($_POST['name'] ?? '');
            $contactNumber = sanitize($_POST['contact_number'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $organisationType = sanitize($_POST['organisation_type'] ?? 'Client');
            $remarks = sanitize($_POST['remarks'] ?? '');

            if (empty($name)) {
                Session::setFlash('danger', 'Contact Name is required.');
                redirect('contacts');
            }

            if (!in_array($organisationType, ['Client', 'Internal', 'Third Party'])) {
                $organisationType = 'Client';
            }

            $contactModel = $this->model('Contact_model');
            $contactModel->createContact([
                'name' => $name,
                'contact_number' => $contactNumber,
                'email' => $email,
                'organisation_type' => $organisationType,
                'remarks' => $remarks,
                'created_by' => Session::get('user_id')
            ]);

            Session::setFlash('success', 'New contact added successfully.');
        }

        redirect('contacts');
    }

    public function update() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('contacts');
            }

            $id = (int)($_POST['id'] ?? 0);
            $name = sanitize($_POST['name'] ?? '');
            $contactNumber = sanitize($_POST['contact_number'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $organisationType = sanitize($_POST['organisation_type'] ?? 'Client');
            $remarks = sanitize($_POST['remarks'] ?? '');

            if (!$id || empty($name)) {
                Session::setFlash('danger', 'Contact ID and Name are required for updating.');
                redirect('contacts');
            }

            if (!in_array($organisationType, ['Client', 'Internal', 'Third Party'])) {
                $organisationType = 'Client';
            }

            $contactModel = $this->model('Contact_model');
            $updated = $contactModel->updateContact($id, [
                'name' => $name,
                'contact_number' => $contactNumber,
                'email' => $email,
                'organisation_type' => $organisationType,
                'remarks' => $remarks
            ]);

            if ($updated) {
                Session::setFlash('success', 'Contact updated successfully.');
            } else {
                Session::setFlash('danger', 'Contact not found or could not be updated.');
            }
        }

        redirect('contacts');
    }

    public function delete() {
        $this->requireAuth();

        // Delete permission check: only Admin or Super Admin
        if (!is_admin()) {
            Session::setFlash('danger', 'Unauthorized access! Only Admin or Super Admin can delete contacts.');
            redirect('contacts');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('contacts');
            }

            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $contactModel = $this->model('Contact_model');
                $deleted = $contactModel->deleteContact($id);

                if ($deleted) {
                    Session::setFlash('success', 'Contact deleted successfully.');
                } else {
                    Session::setFlash('danger', 'Contact could not be found or deleted.');
                }
            }
        }

        redirect('contacts');
    }
}
