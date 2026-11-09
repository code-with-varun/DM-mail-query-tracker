<?php
/**
 * Process Queues Master Controller
 */

class Queues extends Controller {

    private Queue_model $queueModel;

    public function __construct() {
        parent::__construct();
        $this->requireRole([1, 2]);
        $this->queueModel = $this->model('Queue_model');
    }

    public function index(): void {
        $queues = $this->queueModel->getAllQueues();

        $this->render('queues/index', [
            'title' => 'Process Queues Master - DM-Ispark',
            'queues' => $queues
        ]);
    }

    public function store(): void {
        Session::verifyCsrf();

        $queueId = post('queue_id');
        $queueName = post('queue_name');
        $queueCode = post('queue_code');
        $assignedRole = post('assigned_role');
        $badgeColor = post('badge_color');
        $icon = post('icon') ?: 'fa-list-alt';
        $displayOrder = post('display_order');
        $description = post('description');
        $status = post('status');

        if (empty($queueName) || empty($queueCode)) {
            Session::setFlash('danger', 'Queue Name and Queue Code are required.');
            redirect('queues');
            return;
        }

        $this->queueModel->saveQueue([
            'queue_id' => $queueId,
            'queue_name' => $queueName,
            'queue_code' => $queueCode,
            'assigned_role' => $assignedRole,
            'badge_color' => $badgeColor,
            'icon' => $icon,
            'display_order' => $displayOrder,
            'description' => $description,
            'status' => $status
        ]);

        Session::setFlash('success', 'Process Queue saved successfully.');
        redirect('queues');
    }

    public function get(int $id): void {
        $queue = $this->queueModel->getQueueById($id);
        if (!$queue) {
            $this->json(['success' => false, 'message' => 'Queue not found']);
            return;
        }

        $this->json(['success' => true, 'queue' => $queue]);
    }

    public function get_queues_json(): void {
        $queues = $this->queueModel->getActiveQueues();
        $this->json(['success' => true, 'queues' => $queues]);
    }

    public function delete(int $id): void {
        Session::verifyCsrf();
        $this->queueModel->deleteQueue($id);
        Session::setFlash('success', 'Process Queue deleted successfully.');
        redirect('queues');
    }
}
