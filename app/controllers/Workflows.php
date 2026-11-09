<?php
/**
 * Workflow & Queue Management Controller
 */

class Workflows extends Controller {

    public function index() {
        $this->requireRole([1, 2]);

        $wfModel = $this->model('Workflow_model');
        $catModel = $this->model('Category_model');
        $actModel = $this->model('Activity_model');

        $workflows = $wfModel->getAllWorkflows();
        $categories = $catModel->getCategories();
        $subActivities = $actModel->getAllSubActivitiesWithHierarchy();

        $this->render('workflows/index', [
            'title' => 'Workflow & Queue Management',
            'workflows' => $workflows,
            'categories' => $categories,
            'subActivities' => $subActivities
        ]);
    }

    public function get(int $id) {
        $this->requireRole([1, 2]);
        header('Content-Type: application/json');

        $wfModel = $this->model('Workflow_model');
        $wf = $wfModel->getWorkflowById($id);

        if (!$wf) {
            echo json_encode(['success' => false, 'message' => 'Workflow not found']);
            exit();
        }

        echo json_encode(['success' => true, 'workflow' => $wf]);
        exit();
    }

    public function store() {
        $this->requireRole([1, 2]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('workflows');
            }

            $wfModel = $this->model('Workflow_model');
            
            $wfId = !empty($_POST['workflow_id']) ? (int)$_POST['workflow_id'] : 0;
            $data = [
                'workflow_id' => $wfId,
                'workflow_name' => trim($_POST['workflow_name'] ?? ''),
                'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
                'sub_activity_id' => !empty($_POST['sub_activity_id']) ? (int)$_POST['sub_activity_id'] : null,
                'description' => trim($_POST['description'] ?? ''),
                'is_default' => !empty($_POST['is_default']) ? 1 : 0,
                'status' => $_POST['status'] ?? 'Active'
            ];

            if (empty($data['workflow_name'])) {
                Session::setFlash('danger', 'Workflow Name is required.');
                redirect('workflows');
            }

            $stagesInput = $_POST['stages'] ?? [];
            $stages = [];

            if (is_array($stagesInput)) {
                foreach ($stagesInput as $st) {
                    if (!empty($st['stage_name'])) {
                        $stages[] = [
                            'stage_name' => trim($st['stage_name']),
                            'queue_type' => $st['queue_type'] ?? 'Maker',
                            'assigned_role' => $st['assigned_role'] ?? 'Maker',
                            'description' => trim($st['description'] ?? '')
                        ];
                    }
                }
            }

            if (empty($stages)) {
                Session::setFlash('danger', 'At least one workflow queue stage is required.');
                redirect('workflows');
            }

            $savedId = $wfModel->saveWorkflow($data, $stages);
            $userModel = $this->model('User_model');
            $userModel->logAudit('Workflows', $wfId ? 'Update Workflow' : 'Create Workflow', null, ['id' => $savedId, 'name' => $data['workflow_name']]);

            Session::setFlash('success', 'Workflow & Queue stages saved successfully.');
            redirect('workflows');
        }
    }

    public function builder($id = null) {
        $this->requireRole([1, 2]);

        $wfModel = $this->model('Workflow_model');
        $catModel = $this->model('Category_model');
        $queueModel = $this->model('Queue_model');

        $workflow = null;
        if ($id && is_numeric($id)) {
            $workflow = $wfModel->getWorkflowById((int)$id);
        }

        $categories = $catModel->getCategories();
        $processQueues = $queueModel->getActiveQueues();

        $this->render('workflows/builder', [
            'title' => $workflow ? 'Visual Canvas Builder: ' . $workflow['workflow_name'] : 'Create New Visual Workflow Canvas',
            'workflow' => $workflow,
            'categories' => $categories,
            'processQueues' => $processQueues
        ]);
    }

    public function save_canvas() {
        $this->requireRole([1, 2]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rawInput = file_get_contents('php://input');
            $postData = json_decode($rawInput, true);

            if (empty($postData)) {
                $postData = $_POST;
            }

            $wfId = !empty($postData['workflow_id']) ? (int)$postData['workflow_id'] : 0;
            $wfName = trim($postData['workflow_name'] ?? '');

            if (empty($wfName)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Workflow Name is required.']);
                exit();
            }

            $wfData = [
                'workflow_name' => $wfName,
                'category_id' => !empty($postData['category_id']) ? (int)$postData['category_id'] : null,
                'sub_activity_id' => null,
                'description' => trim($postData['description'] ?? ''),
                'is_default' => !empty($postData['is_default']) ? 1 : 0,
                'status' => $postData['status'] ?? 'Active'
            ];

            $nodes = $postData['nodes'] ?? [];
            if (!is_array($nodes) || empty($nodes)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'At least one visual node is required on the canvas.']);
                exit();
            }

            $wfModel = $this->model('Workflow_model');
            if ($wfId === 0) {
                $wfId = $wfModel->insert('workflows', [
                    'workflow_name' => $wfData['workflow_name'],
                    'category_id' => $wfData['category_id'],
                    'sub_activity_id' => null,
                    'description' => $wfData['description'],
                    'is_default' => $wfData['is_default'],
                    'status' => $wfData['status'],
                    'created_by' => current_user()['id'] ?? 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            $wfModel->saveCanvasWorkflow($wfId, $wfData, $nodes);

            $userModel = $this->model('User_model');
            $userModel->logAudit('Workflows', 'Save Visual Canvas', null, ['id' => $wfId, 'name' => $wfName]);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'workflow_id' => $wfId, 'message' => 'Visual Canvas saved successfully!']);
            exit();
        }
    }

    public function delete(int $id) {
        $this->requireRole([1, 2]);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('workflows');
            }

            $wfModel = $this->model('Workflow_model');
            $success = $wfModel->deleteWorkflow($id);

            if ($success) {
                $userModel = $this->model('User_model');
                $userModel->logAudit('Workflows', 'Delete Workflow', null, ['id' => $id]);
                Session::setFlash('success', 'Workflow removed successfully.');
            } else {
                Session::setFlash('danger', 'Failed to delete workflow. Please try again.');
            }

            redirect('workflows');
        }
    }
}
