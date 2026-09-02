<?php
/**
 * Master Controller (Activities, Sub-Activities, Divisions)
 */

class Master extends Controller {
    public function activities() {
        $this->requireAuth();
        $this->requireRole([1]); // Super Admin only

        $activityModel = $this->model('Activity_model');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('master/activities');
            }

            $type = $_POST['form_type'] ?? '';
            if ($type === 'activity') {
                $name = sanitize($_POST['activity_name'] ?? '');
                $desc = sanitize($_POST['description'] ?? '');
                if (!empty($name)) {
                    $activityModel->createActivity($name, $desc);
                    Session::setFlash('success', 'Parent Activity created successfully.');
                }
            } elseif ($type === 'sub_activity') {
                $activityId = (int)($_POST['activity_id'] ?? 0);
                $name = sanitize($_POST['sub_activity_name'] ?? '');
                $tatHours = (int)($_POST['default_tat_hours'] ?? 24);
                if ($activityId && !empty($name)) {
                    $activityModel->createSubActivity($activityId, $name, $tatHours);
                    Session::setFlash('success', 'Sub-Activity created successfully.');
                }
            }
            redirect('master/activities');
        }

        $activities = $activityModel->getAllActivitiesWithSub();
        $this->render('master/activities', [
            'title' => 'Activities Master',
            'activities' => $activities
        ]);
    }

    public function divisions() {
        $this->requireAuth();
        $this->requireRole([1]);

        $activityModel = $this->model('Activity_model');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('master/divisions');
            }

            $name = sanitize($_POST['division_name'] ?? '');
            $code = sanitize($_POST['code'] ?? '');

            if (!empty($name) && !empty($code)) {
                $activityModel->createDivision($name, $code);
                Session::setFlash('success', 'Division created successfully.');
            }
            redirect('master/divisions');
        }

        $divisions = $activityModel->getDivisions();
        $this->render('master/divisions', [
            'title' => 'Divisions Master',
            'divisions' => $divisions
        ]);
    }
}
