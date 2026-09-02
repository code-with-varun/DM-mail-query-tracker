<?php
/**
 * API Endpoint Controller for AJAX requests
 */

class Api extends Controller {
    public function get_sub_activities() {
        $activityId = (int)($_GET['activity_id'] ?? 0);
        if (!$activityId) {
            $this->json(['success' => false, 'message' => 'Activity ID required']);
        }

        $activityModel = $this->model('Activity_model');
        $subActivities = $activityModel->getSubActivitiesByActivity($activityId);
        
        $this->json(['success' => true, 'data' => $subActivities]);
    }

    public function get_notifications() {
        if (!Session::has('user_id')) {
            $this->json(['success' => false, 'message' => 'Unauthorized']);
        }

        $userId = Session::get('user_id');
        $ticketModel = $this->model('Ticket_model');
        
        $notifications = $ticketModel->fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT 10",
            [$userId]
        );
        $unreadCount = $ticketModel->fetchOne(
            "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0",
            [$userId]
        )['count'] ?? 0;

        $this->json([
            'success' => true,
            'unread_count' => (int)$unreadCount,
            'notifications' => $notifications
        ]);
    }

    public function mark_read() {
        if (!Session::has('user_id')) {
            $this->json(['success' => false, 'message' => 'Unauthorized']);
        }

        $userId = Session::get('user_id');
        $ticketModel = $this->model('Ticket_model');
        $ticketModel->update('notifications', ['is_read' => 1], "user_id = ?", [$userId]);

        $this->json(['success' => true]);
    }
}
