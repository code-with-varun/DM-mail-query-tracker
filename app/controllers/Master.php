<?php
/**
 * Master Data Controller (Activities, Sub-Activities, Divisions)
 */

class Master extends Controller {
    public function activities() {
        $this->requireAuth();
        $this->requireRole([1]);

        $activityModel = $this->model('Activity_model');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                Session::setFlash('danger', 'Invalid security token.');
                redirect('master/activities');
            }

            $formType = $_POST['form_type'] ?? '';
            $action = $_POST['action'] ?? 'save';
            $id = (int)($_POST['id'] ?? 0);

            if ($action === 'delete' && $id > 0) {
                if ($formType === 'activity') {
                    $activityModel->delete('activities', "id = ?", [$id]);
                    $activityModel->delete('sub_activities', "activity_id = ?", [$id]);
                    Session::setFlash('success', 'Parent activity deleted.');
                } elseif ($formType === 'sub_activity') {
                    $activityModel->delete('sub_activities', "id = ?", [$id]);
                    Session::setFlash('success', 'Sub-activity deleted.');
                }
                redirect('master/activities');
            }

            if ($formType === 'activity') {
                $name = sanitize($_POST['activity_name'] ?? '');
                $desc = sanitize($_POST['description'] ?? '');

                if (!empty($name)) {
                    if ($id > 0) {
                        $activityModel->update('activities', ['activity_name' => $name, 'description' => $desc], "id = ?", [$id]);
                        Session::setFlash('success', 'Activity updated successfully.');
                    } else {
                        $activityModel->createActivity($name, $desc);
                        Session::setFlash('success', 'Activity created successfully.');
                    }
                }
            } elseif ($formType === 'sub_activity') {
                $actId = (int)($_POST['activity_id'] ?? 0);
                $subName = sanitize($_POST['sub_activity_name'] ?? '');
                $tat = (int)($_POST['default_tat_hours'] ?? 24);

                if ($actId && !empty($subName)) {
                    if ($id > 0) {
                        $activityModel->update('sub_activities', ['activity_id' => $actId, 'sub_activity_name' => $subName, 'default_tat_hours' => $tat], "id = ?", [$id]);
                        Session::setFlash('success', 'Sub-activity updated successfully.');
                    } else {
                        $activityModel->createSubActivity($actId, $subName, $tat);
                        Session::setFlash('success', 'Sub-activity created successfully.');
                    }
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

            $action = $_POST['action'] ?? 'save';
            $id = (int)($_POST['id'] ?? 0);

            if ($action === 'delete' && $id > 0) {
                $activityModel->delete('divisions', "id = ?", [$id]);
                Session::setFlash('success', 'Division deleted successfully.');
                redirect('master/divisions');
            }

            $name = sanitize($_POST['division_name'] ?? '');
            $code = sanitize($_POST['code'] ?? '');

            if (!empty($name) && !empty($code)) {
                if ($id > 0) {
                    $activityModel->update('divisions', ['division_name' => $name, 'code' => $code], "id = ?", [$id]);
                    Session::setFlash('success', 'Division updated successfully.');
                } else {
                    $activityModel->createDivision($name, $code);
                    Session::setFlash('success', 'Division created successfully.');
                }
            }
            redirect('master/divisions');
        }

        $divisions = $activityModel->getDivisions();
        $this->render('master/divisions', [
            'title' => 'Divisions Master',
            'divisions' => $divisions
        ]);
    }

    /**
     * Export Activities CSV Reference
     */
    public function export_activities() {
        $this->requireAuth();
        $this->requireRole([1, 2]);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=MQT_Activities_Master.csv');

        $output = fopen('php://output', 'w');
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['Activity ID', 'Activity Name', 'Description', 'Status']);
        $activities = $this->model('Activity_model')->getActivities();
        foreach ($activities as $act) {
            fputcsv($output, [$act['id'], $act['activity_name'], $act['description'] ?? '', 'Active']);
        }
        fclose($output);
        exit();
    }

    /**
     * Export Sub-Activities CSV Reference
     */
    public function export_sub_activities() {
        $this->requireAuth();
        $this->requireRole([1, 2]);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=MQT_Sub_Activities_Master.csv');

        $output = fopen('php://output', 'w');
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['Sub Activity ID', 'Sub Activity Name', 'Parent Activity Name', 'Default SLA TAT (Hours)']);
        $subActs = $this->model('Activity_model')->fetchAll("SELECT sa.*, a.activity_name FROM sub_activities sa JOIN activities a ON sa.activity_id = a.id ORDER BY a.activity_name, sa.sub_activity_name");
        foreach ($subActs as $sa) {
            fputcsv($output, [$sa['id'], $sa['sub_activity_name'], $sa['activity_name'], $sa['default_tat_hours']]);
        }
        fclose($output);
        exit();
    }

    /**
     * Export Divisions CSV Reference
     */
    public function export_divisions() {
        $this->requireAuth();
        $this->requireRole([1, 2]);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=MQT_Divisions_Master.csv');

        $output = fopen('php://output', 'w');
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['Division ID', 'Division Code (Use this in Import)', 'Division Name', 'Status']);
        $divisions = $this->model('Activity_model')->getDivisions();
        foreach ($divisions as $div) {
            fputcsv($output, [$div['id'], $div['code'], $div['division_name'], $div['status']]);
        }
        fclose($output);
        exit();
    }
}
