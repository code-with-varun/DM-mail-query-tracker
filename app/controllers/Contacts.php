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

            // Uniqueness Check for Email ID
            if (!empty($email)) {
                $existingEmail = $contactModel->getByEmail($email);
                if ($existingEmail) {
                    Session::setFlash('danger', "A contact with Email '{$email}' already exists ({$existingEmail['name']}).");
                    redirect('contacts');
                }
            }

            // Uniqueness Check for Contact Number
            if (!empty($contactNumber)) {
                $existingPhone = $contactModel->getByPhone($contactNumber);
                if ($existingPhone) {
                    Session::setFlash('danger', "A contact with Mobile Number '{$contactNumber}' already exists ({$existingPhone['name']}).");
                    redirect('contacts');
                }
            }

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

            // Uniqueness Check for Email ID (excluding current ID)
            if (!empty($email)) {
                $existingEmail = $contactModel->getByEmail($email, $id);
                if ($existingEmail) {
                    Session::setFlash('danger', "Another contact with Email '{$email}' already exists ({$existingEmail['name']}).");
                    redirect('contacts');
                }
            }

            // Uniqueness Check for Contact Number (excluding current ID)
            if (!empty($contactNumber)) {
                $existingPhone = $contactModel->getByPhone($contactNumber, $id);
                if ($existingPhone) {
                    Session::setFlash('danger', "Another contact with Mobile Number '{$contactNumber}' already exists ({$existingPhone['name']}).");
                    redirect('contacts');
                }
            }

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

    /**
     * Download CSV Template for Contact Bulk Import
     */
    public function download_template() {
        $this->requireAuth();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=MQT_Contact_Import_Template.csv');

        $output = fopen('php://output', 'w');
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['Contact Name', 'Organisation Type', 'Contact Number', 'Email Address', 'Remark Notes']);
        fputcsv($output, ['John Doe', 'Client', '+91 9876543210', 'john.doe@client.com', 'Key billing contact']);
        fputcsv($output, ['Jane Smith', 'Third Party', '+91 9123456789', 'jane.smith@vendor.com', 'Audit consultant']);

        fclose($output);
        exit();
    }

    /**
     * Bulk Contact CSV Import Engine
     */
    public function import() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('contacts');
            }

            if (empty($_FILES['import_file']['tmp_name'])) {
                Session::setFlash('danger', 'Please select a CSV file to upload.');
                redirect('contacts');
            }

            $handle = fopen($_FILES['import_file']['tmp_name'], 'r');
            if (!$handle) {
                Session::setFlash('danger', 'Failed to read uploaded file.');
                redirect('contacts');
            }

            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $header = fgetcsv($handle);
            $contactModel = $this->model('Contact_model');
            $importedCount = 0;
            $skippedCount = 0;

            while (($row = fgetcsv($handle)) !== false) {
                if (empty(array_filter($row))) continue;

                $name = sanitize($row[0] ?? '');
                $orgType = sanitize($row[1] ?? 'Client');
                $phone = sanitize($row[2] ?? '');
                $email = sanitize($row[3] ?? '');
                $remarks = sanitize($row[4] ?? '');

                if (empty($name)) {
                    $skippedCount++;
                    continue;
                }

                if (!in_array($orgType, ['Client', 'Internal', 'Third Party'])) {
                    $orgType = 'Client';
                }

                // Skip if duplicate email or phone exists
                if (!empty($email) && $contactModel->getByEmail($email)) {
                    $skippedCount++;
                    continue;
                }
                if (!empty($phone) && $contactModel->getByPhone($phone)) {
                    $skippedCount++;
                    continue;
                }

                try {
                    $contactModel->createContact([
                        'name' => $name,
                        'organisation_type' => $orgType,
                        'contact_number' => $phone,
                        'email' => $email,
                        'remarks' => $remarks,
                        'created_by' => Session::get('user_id')
                    ]);
                    $importedCount++;
                } catch (\Exception $e) {
                    $skippedCount++;
                }
            }

            fclose($handle);
            Session::setFlash('success', "Bulk Contact Import Complete: Successfully imported {$importedCount} contacts" . ($skippedCount > 0 ? " ({$skippedCount} skipped/duplicates)." : "."));
        }

        redirect('contacts');
    }
}
