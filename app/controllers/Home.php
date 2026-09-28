<?php
/**
 * Home / Launchpad / cPanel Controller
 */

class Home extends Controller {

    public function __construct() {
        $this->requireAuth();
    }

    public function index() {
        $user = current_user();
        $ticketModel = $this->model('Ticket_model');
        $taskModel = $this->model('Task_model');
        $trainingModel = $this->model('Training_model');

        // Quick KPI stats for the cPanel header
        $bucketCount = count($ticketModel->getMyBucket($user['id']));
        $pendingTasksCount = count($taskModel->getPendingTasksForUser($user['id']));
        $certs = $trainingModel->getUserCertifications($user['id']);
        
        $certifiedCount = 0;
        foreach ($certs as $c) {
            if (($c['status'] ?? '') === 'Certified') {
                $certifiedCount++;
            }
        }

        $this->render('home/index', [
            'title' => 'Home Hub & Control Panel',
            'user' => $user,
            'bucketCount' => $bucketCount,
            'pendingTasksCount' => $pendingTasksCount,
            'certifiedCount' => $certifiedCount
        ]);
    }
}
