<?php
/**
 * Training, PKT LMS, Practice File & Certification Model
 */

class Training_model extends Model {

    public function __construct() {
        parent::__construct();
        $this->ensureTablesExist();
    }

    /**
     * Ensure database schema tables exist automatically
     */
    private function ensureTablesExist(): void {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `training_plans` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `sub_activity_id` INT NOT NULL,
                  `title` VARCHAR(255) NOT NULL,
                  `description` TEXT NULL,
                  `kt_document_path` VARCHAR(255) NULL,
                  `kt_document_name` VARCHAR(255) NULL,
                  `created_by` INT NOT NULL,
                  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `user_training_progress` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `user_id` INT NOT NULL,
                  `training_plan_id` INT NOT NULL DEFAULT 0,
                  `sub_activity_id` INT NOT NULL,
                  `is_learned` TINYINT(1) DEFAULT 0,
                  `learned_at` DATETIME NULL,
                  UNIQUE KEY `idx_user_subact_progress` (`user_id`, `sub_activity_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            try {
                $this->db->exec("ALTER TABLE `training_plans` ADD COLUMN `user_id` INT NULL DEFAULT NULL AFTER `id`");
            } catch (Exception $e) {}
            try {
                $this->db->exec("ALTER TABLE `training_plans` ADD COLUMN `training_date` DATETIME NULL DEFAULT NULL AFTER `description`");
            } catch (Exception $e) {}
            try {
                $this->db->exec("ALTER TABLE `training_plans` ADD COLUMN `meeting_url` VARCHAR(500) NULL DEFAULT NULL AFTER `training_date`");
            } catch (Exception $e) {}
            try {
                $this->db->exec("ALTER TABLE `training_plans` ADD COLUMN `status` ENUM('Scheduled', 'Completed', 'Acknowledged') DEFAULT 'Scheduled' AFTER `kt_document_name`");
            } catch (Exception $e) {}
            try {
                $this->db->exec("ALTER TABLE `user_training_progress` ADD COLUMN `acknowledged_at` DATETIME NULL DEFAULT NULL AFTER `learned_at`");
            } catch (Exception $e) {}

            try {
                $this->db->exec("ALTER TABLE user_training_progress DROP INDEX idx_user_plan");
            } catch (Exception $e) {}
            try {
                $this->db->exec("ALTER TABLE user_training_progress ADD UNIQUE KEY idx_user_subact_progress (user_id, sub_activity_id)");
            } catch (Exception $e) {}

            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `pkt_question_bank` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `sub_activity_id` INT NOT NULL,
                  `question` TEXT NOT NULL,
                  `option_a` VARCHAR(255) NOT NULL,
                  `option_b` VARCHAR(255) NOT NULL,
                  `option_c` VARCHAR(255) NOT NULL,
                  `option_d` VARCHAR(255) NOT NULL,
                  `correct_option` ENUM('A', 'B', 'C', 'D') NOT NULL,
                  `created_by` INT NOT NULL,
                  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `pkt_tests` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `test_title` VARCHAR(255) NOT NULL,
                  `sub_activity_id` INT NOT NULL,
                  `total_questions` INT DEFAULT 5,
                  `passing_score` INT DEFAULT 70,
                  `time_limit_minutes` INT DEFAULT 20,
                  `status` ENUM('Published', 'Closed') DEFAULT 'Published',
                  `created_by` INT NOT NULL,
                  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `pkt_test_attempts` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `test_id` INT NOT NULL,
                  `user_id` INT NOT NULL,
                  `sub_activity_id` INT NOT NULL,
                  `total_questions` INT NOT NULL,
                  `correct_answers` INT NOT NULL,
                  `score_percentage` DECIMAL(5,2) NOT NULL,
                  `passed` TINYINT(1) NOT NULL DEFAULT 0,
                  `attempt_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
                  `answers_json` TEXT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `practice_files` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `user_id` INT NOT NULL,
                  `division_id` INT NULL,
                  `activity_id` INT NOT NULL,
                  `sub_activity_id` INT NOT NULL,
                  `task_title` VARCHAR(255) NOT NULL,
                  `file_path` VARCHAR(255) NULL,
                  `file_name` VARCHAR(255) NULL,
                  `remarks` TEXT NULL,
                  `checker_id` INT NULL,
                  `status` ENUM('In Progress', 'Submitted to Checker', 'Rework Required', 'Approved') DEFAULT 'In Progress',
                  `error_observation` VARCHAR(255) NULL,
                  `error_description` TEXT NULL,
                  `error_type` ENUM('Internal', 'External') DEFAULT 'Internal',
                  `submitted_at` DATETIME NULL,
                  `validated_at` DATETIME NULL,
                  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `activity_certifications` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `user_id` INT NOT NULL,
                  `sub_activity_id` INT NOT NULL,
                  `training_completed` TINYINT(1) DEFAULT 0,
                  `pkt_passed` TINYINT(1) DEFAULT 0,
                  `pkt_score` DECIMAL(5,2) NULL,
                  `practice_passed` TINYINT(1) DEFAULT 0,
                  `status` ENUM('In Progress', 'Certified') DEFAULT 'In Progress',
                  `certified_at` DATETIME NULL,
                  `certificate_code` VARCHAR(100) NULL,
                  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                  UNIQUE KEY `idx_user_subact` (`user_id`, `sub_activity_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        } catch (Exception $e) {
            // Ignore if tables already exist or database driver warnings
        }
    }

    // ----------------------------------------------------
    // TRAINING PLAN & KT METHODS
    // ----------------------------------------------------
    public function createTrainingPlanWithTrainees(array $data): int {
        $meetingUrl = !empty($data['meeting_url']) ? sanitize($data['meeting_url']) : null;
        $trainees = !empty($data['trainee_ids']) && is_array($data['trainee_ids']) ? array_map('intval', $data['trainee_ids']) : [];

        $planData = [
            'trainer_id' => !empty($data['trainer_id']) ? (int)$data['trainer_id'] : (int)$data['created_by'],
            'user_id' => !empty($trainees[0]) ? $trainees[0] : null,
            'sub_activity_id' => (int)$data['sub_activity_id'],
            'title' => sanitize($data['title']),
            'description' => sanitize($data['description'] ?? ''),
            'training_date' => !empty($data['training_date']) ? date('Y-m-d H:i:s', strtotime($data['training_date'])) : null,
            'meeting_url' => $meetingUrl,
            'meeting_link' => $meetingUrl,
            'kt_document_path' => $data['kt_document_path'] ?? null,
            'kt_document_name' => $data['kt_document_name'] ?? null,
            'trainees_json' => !empty($trainees) ? json_encode($trainees) : null,
            'status' => 'Scheduled',
            'created_by' => (int)$data['created_by'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        $planId = $this->insert('training_plans', $planData);

        // Attach trainees to training_plan_trainees & user_training_progress
        if (!empty($trainees)) {
            foreach ($trainees as $uid) {
                if ($uid <= 0) continue;
                try {
                    $this->db->prepare("INSERT IGNORE INTO training_plan_trainees (training_plan_id, user_id, is_acknowledged) VALUES (?, ?, 0)")
                             ->execute([$planId, $uid]);
                } catch (Exception $e) {}

                // Ensure user_training_progress initialized for trainee & sub_activity
                $existing = $this->fetchOne("SELECT id FROM user_training_progress WHERE user_id = ? AND sub_activity_id = ?", [$uid, $planData['sub_activity_id']]);
                if (!$existing) {
                    $this->insert('user_training_progress', [
                        'user_id' => $uid,
                        'training_plan_id' => $planId,
                        'sub_activity_id' => $planData['sub_activity_id'],
                        'is_learned' => 0
                    ]);
                }
            }
        }

        return $planId;
    }

    public function getScheduledTrainingPlansForUser(int $userId, bool $isAdmin = false, ?int $filterUserId = null): array {
        $sql = "SELECT tp.*, 
                       COALESCE(NULLIF(tp.meeting_url, ''), tp.meeting_link) as effective_meeting_url,
                       sa.sub_activity_name, 
                       a.activity_name, 
                       d.division_name,
                       u_trainer.full_name as trainer_name, u_trainer.user_code as trainer_code,
                       u_creator.full_name as creator_name
                FROM training_plans tp
                JOIN sub_activities sa ON tp.sub_activity_id = sa.id
                JOIN activities a ON sa.activity_id = a.id
                LEFT JOIN divisions d ON sa.division_id = d.id
                LEFT JOIN users u_trainer ON tp.trainer_id = u_trainer.id
                LEFT JOIN users u_creator ON tp.created_by = u_creator.id
                WHERE 1=1";
        
        $params = [];

        if (!$isAdmin) {
            $sql .= " AND (tp.id IN (SELECT training_plan_id FROM training_plan_trainees WHERE user_id = ?) OR tp.user_id = ? OR tp.user_id IS NULL)";
            $params[] = $userId;
            $params[] = $userId;
        } elseif ($filterUserId && $isAdmin) {
            $sql .= " AND (tp.id IN (SELECT training_plan_id FROM training_plan_trainees WHERE user_id = ?) OR tp.user_id = ? OR tp.user_id IS NULL)";
            $params[] = $filterUserId;
            $params[] = $filterUserId;
        }

        $sql .= " ORDER BY CASE WHEN tp.status = 'Scheduled' THEN 0 ELSE 1 END, tp.training_date DESC, tp.id DESC";
        $plans = $this->fetchAll($sql, $params);

        foreach ($plans as &$p) {
            $trainees = $this->fetchAll("
                SELECT tpt.is_acknowledged, tpt.acknowledged_at, u.id as user_id, u.full_name, u.user_code, u.department
                FROM training_plan_trainees tpt
                JOIN users u ON tpt.user_id = u.id
                WHERE tpt.training_plan_id = ?
            ", [$p['id']]);

            if (empty($trainees) && !empty($p['trainees_json'])) {
                $tIds = json_decode($p['trainees_json'], true);
                if (is_array($tIds) && !empty($tIds)) {
                    $inC = implode(',', array_map('intval', $tIds));
                    $trainees = $this->fetchAll("SELECT 0 as is_acknowledged, NULL as acknowledged_at, id as user_id, full_name, user_code, department FROM users WHERE id IN ($inC)");
                }
            }

            $p['trainees_list'] = $trainees;
            $p['acknowledged_count'] = 0;
            $p['user_is_acknowledged'] = 0;
            $p['user_acknowledged_at'] = null;

            foreach ($trainees as $tItem) {
                if (!empty($tItem['is_acknowledged'])) {
                    $p['acknowledged_count']++;
                }
                if ($tItem['user_id'] == $userId) {
                    $p['user_is_acknowledged'] = !empty($tItem['is_acknowledged']) ? 1 : 0;
                    $p['user_acknowledged_at'] = $tItem['acknowledged_at'] ?? null;
                }
            }

            $cert = $this->fetchOne("SELECT pkt_passed, practice_passed FROM activity_certifications WHERE user_id = ? AND sub_activity_id = ?", [$userId, $p['sub_activity_id']]);
            $p['pkt_passed'] = !empty($cert['pkt_passed']) ? 1 : 0;
            $p['practice_passed'] = !empty($cert['practice_passed']) ? 1 : 0;
        }

        return $plans;
    }

    public function acknowledgeTraineeKT(int $planId, int $userId): array {
        $plan = $this->fetchOne("SELECT * FROM training_plans WHERE id = ?", [$planId]);
        if (!$plan) return ['success' => false, 'status' => 'error'];

        $subActivityId = $plan['sub_activity_id'];
        $now = date('Y-m-d H:i:s');

        // Check existing trainee record
        $tCheck = $this->fetchOne("SELECT id, is_acknowledged FROM training_plan_trainees WHERE training_plan_id = ? AND user_id = ?", [$planId, $userId]);
        
        $newStatus = 1;
        if ($tCheck && !empty($tCheck['is_acknowledged'])) {
            $newStatus = 0; // Revoke acknowledgment
        }

        if ($tCheck) {
            $this->update('training_plan_trainees', [
                'is_acknowledged' => $newStatus,
                'acknowledged_at' => $newStatus ? $now : null
            ], "id = ?", [$tCheck['id']]);
        } else {
            $this->insert('training_plan_trainees', [
                'training_plan_id' => $planId,
                'user_id' => $userId,
                'is_acknowledged' => $newStatus,
                'acknowledged_at' => $newStatus ? $now : null
            ]);
        }

        // Update user_training_progress
        $existing = $this->fetchOne("SELECT id FROM user_training_progress WHERE user_id = ? AND sub_activity_id = ?", [$userId, $subActivityId]);
        if ($existing) {
            $this->update('user_training_progress', [
                'training_plan_id' => $planId,
                'is_learned' => $newStatus,
                'learned_at' => $newStatus ? $now : null,
                'acknowledged_at' => $newStatus ? $now : null
            ], "id = ?", [$existing['id']]);
        } else {
            $this->insert('user_training_progress', [
                'user_id' => $userId,
                'training_plan_id' => $planId,
                'sub_activity_id' => $subActivityId,
                'is_learned' => $newStatus,
                'learned_at' => $newStatus ? $now : null,
                'acknowledged_at' => $newStatus ? $now : null
            ]);
        }

        $this->updateCertificationProgress($userId, $subActivityId);
        return [
            'success' => true,
            'status' => $newStatus ? 'acknowledged' : 'revoked'
        ];
    }

    public function deleteTrainingPlan(int $planId): bool {
        $this->delete('training_plan_trainees', "training_plan_id = ?", [$planId]);
        return $this->delete('training_plans', "id = ?", [$planId]);
    }

    public function getFlatTrainingPlanTable(int $userId, bool $isAdmin = false, ?int $targetUserId = null): array {
        $effectiveUserId = ($targetUserId && $isAdmin) ? $targetUserId : $userId;

        $sql = "SELECT 
                    sa.id as sub_activity_id,
                    sa.sub_activity_name,
                    sa.activity_id,
                    a.activity_name,
                    sa.division_id,
                    COALESCE(d.division_name, 'General Operations') as division_name,
                    usa.role_type,
                    COALESCE(utp.is_learned, 0) as is_learned,
                    utp.learned_at,
                    utp.acknowledged_at,
                    tp.id as plan_id,
                    tp.title as plan_title,
                    tp.description as plan_description,
                    COALESCE(NULLIF(tp.meeting_url, ''), tp.meeting_link) as meeting_url,
                    tp.kt_document_path,
                    tp.kt_document_name,
                    ac.training_completed,
                    ac.pkt_passed,
                    ac.pkt_score,
                    ac.practice_passed,
                    ac.status as cert_status,
                    ac.certificate_code
                FROM sub_activities sa
                JOIN activities a ON sa.activity_id = a.id
                LEFT JOIN divisions d ON sa.division_id = d.id
                JOIN user_sub_activities usa ON (usa.sub_activity_id = sa.id AND usa.user_id = ?)
                LEFT JOIN user_training_progress utp ON (utp.sub_activity_id = sa.id AND utp.user_id = ?)
                LEFT JOIN training_plans tp ON tp.sub_activity_id = sa.id
                LEFT JOIN activity_certifications ac ON (ac.sub_activity_id = sa.id AND ac.user_id = ?)
                WHERE sa.status = 'Active'
                ORDER BY d.division_name ASC, a.activity_name ASC, sa.sub_activity_name ASC";

        return $this->fetchAll($sql, [$effectiveUserId, $effectiveUserId, $effectiveUserId]);
    }

    public function getGroupedTrainingPlan(int $userId, bool $isAdmin = false, ?int $filterUserId = null): array {
        $assignedSkills = [];
        $userSkills = $this->fetchAll("SELECT sub_activity_id, role_type FROM user_sub_activities WHERE user_id = ?", [$userId]);
        if (!empty($userSkills)) {
            foreach ($userSkills as $us) {
                $assignedSkills[$us['sub_activity_id']] = $us['role_type'];
            }
        }

        $sql = "SELECT 
                    sa.id as sub_activity_id,
                    sa.sub_activity_name,
                    sa.activity_id,
                    a.activity_name,
                    sa.division_id,
                    d.division_name,
                    tp.id as plan_id,
                    tp.title as plan_title,
                    tp.description as plan_description,
                    tp.kt_document_path,
                    tp.kt_document_name,
                    COALESCE(utp.is_learned, 0) as is_learned,
                    utp.learned_at,
                    usa.role_type
                FROM sub_activities sa
                JOIN activities a ON sa.activity_id = a.id
                LEFT JOIN divisions d ON sa.division_id = d.id
                LEFT JOIN training_plans tp ON tp.sub_activity_id = sa.id
                LEFT JOIN user_training_progress utp ON (utp.sub_activity_id = sa.id AND utp.user_id = ?)
                LEFT JOIN user_sub_activities usa ON (usa.sub_activity_id = sa.id AND usa.user_id = ?)
                WHERE sa.status = 'Active'";
        
        $params = [$userId, $userId];

        if (!empty($assignedSkills)) {
            $inClause = implode(',', array_map('intval', array_keys($assignedSkills)));
            $sql .= " AND sa.id IN ($inClause)";
        } elseif (!$isAdmin) {
            return [];
        }

        $sql .= " ORDER BY d.division_name ASC, a.activity_name ASC, sa.sub_activity_name ASC";
        
        $rows = $this->fetchAll($sql, $params);

        $grouped = [];
        foreach ($rows as $r) {
            $actId = $r['activity_id'];
            if (!isset($grouped[$actId])) {
                $grouped[$actId] = [
                    'activity_id' => $actId,
                    'activity_name' => $r['activity_name'],
                    'division_name' => !empty($r['division_name']) ? $r['division_name'] : 'General Operations',
                    'sub_activities' => []
                ];
            }
            $r['assigned_role'] = $r['role_type'] ?? ($assignedSkills[$r['sub_activity_id']] ?? 'Maker');
            $grouped[$actId]['sub_activities'][] = $r;
        }

        return array_values($grouped);
    }

    public function toggleSubActivityProgress(int $userId, int $subActivityId, bool $isLearned): bool {
        $existing = $this->fetchOne("SELECT id FROM user_training_progress WHERE user_id = ? AND sub_activity_id = ?", [$userId, $subActivityId]);
        if ($existing) {
            $this->update('user_training_progress', [
                'is_learned' => $isLearned ? 1 : 0,
                'learned_at' => $isLearned ? date('Y-m-d H:i:s') : null
            ], "id = ?", [$existing['id']]);
        } else {
            $plan = $this->fetchOne("SELECT id FROM training_plans WHERE sub_activity_id = ? ORDER BY id DESC LIMIT 1", [$subActivityId]);
            $planId = $plan['id'] ?? $subActivityId;

            $this->insert('user_training_progress', [
                'user_id' => $userId,
                'training_plan_id' => $planId,
                'sub_activity_id' => $subActivityId,
                'is_learned' => $isLearned ? 1 : 0,
                'learned_at' => $isLearned ? date('Y-m-d H:i:s') : null
            ]);
        }

        $this->updateCertificationProgress($userId, $subActivityId);
        return true;
    }

    // ----------------------------------------------------
    // PKT QUESTION BANK & TEST METHODS
    // ----------------------------------------------------
    public function getQuestionBank(?int $subActivityId = null): array {
        if ($subActivityId) {
            $sql = "SELECT qb.*, sa.sub_activity_name, a.activity_name 
                    FROM pkt_question_bank qb
                    JOIN sub_activities sa ON qb.sub_activity_id = sa.id
                    JOIN activities a ON sa.activity_id = a.id
                    WHERE qb.sub_activity_id = ?
                    ORDER BY qb.id DESC";
            return $this->fetchAll($sql, [$subActivityId]);
        }
        $sql = "SELECT qb.*, sa.sub_activity_name, a.activity_name 
                FROM pkt_question_bank qb
                JOIN sub_activities sa ON qb.sub_activity_id = sa.id
                JOIN activities a ON sa.activity_id = a.id
                ORDER BY qb.id DESC";
        return $this->fetchAll($sql);
    }

    public function addQuestion(array $data): int {
        return $this->insert('pkt_question_bank', $data);
    }

    public function bulkImportQuestions(array $questions): int {
        $count = 0;
        foreach ($questions as $q) {
            if (!empty($q['question']) && !empty($q['option_a']) && !empty($q['option_b']) && !empty($q['correct_option'])) {
                $this->insert('pkt_question_bank', [
                    'sub_activity_id' => (int)$q['sub_activity_id'],
                    'question' => sanitize($q['question']),
                    'option_a' => sanitize($q['option_a']),
                    'option_b' => sanitize($q['option_b']),
                    'option_c' => sanitize($q['option_c']),
                    'option_d' => sanitize($q['option_d']),
                    'correct_option' => strtoupper(trim($q['correct_option'])),
                    'created_by' => (int)$q['created_by'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $count++;
            }
        }
        return $count;
    }

    public function getPublishedTestsForUser(int $userId): array {
        $sql = "SELECT t.*, sa.sub_activity_name, a.activity_name, d.division_name,
                       (SELECT COUNT(*) FROM pkt_test_attempts WHERE test_id = t.id AND user_id = ?) as attempt_count,
                       (SELECT MAX(score_percentage) FROM pkt_test_attempts WHERE test_id = t.id AND user_id = ?) as best_score,
                       (SELECT MAX(passed) FROM pkt_test_attempts WHERE test_id = t.id AND user_id = ?) as is_passed
                FROM pkt_tests t
                JOIN sub_activities sa ON t.sub_activity_id = sa.id
                JOIN activities a ON sa.activity_id = a.id
                LEFT JOIN divisions d ON sa.division_id = d.id
                JOIN user_sub_activities usa ON usa.sub_activity_id = sa.id
                WHERE usa.user_id = ? AND t.status = 'Published'
                ORDER BY t.id DESC";
        return $this->fetchAll($sql, [$userId, $userId, $userId, $userId]);
    }

    public function getAllTests(): array {
        $sql = "SELECT t.*, sa.sub_activity_name, a.activity_name, u.full_name as creator_name,
                       (SELECT COUNT(DISTINCT user_id) FROM pkt_test_attempts WHERE test_id = t.id) as total_candidates,
                       (SELECT COUNT(*) FROM pkt_question_bank WHERE sub_activity_id = t.sub_activity_id) as available_questions
                FROM pkt_tests t
                JOIN sub_activities sa ON t.sub_activity_id = sa.id
                JOIN activities a ON sa.activity_id = a.id
                LEFT JOIN users u ON t.created_by = u.id
                ORDER BY t.id DESC";
        return $this->fetchAll($sql);
    }

    public function createTest(array $data): int {
        return $this->insert('pkt_tests', $data);
    }

    public function getTestById(int $testId): ?array {
        $sql = "SELECT t.*, sa.sub_activity_name, a.activity_name 
                FROM pkt_tests t 
                JOIN sub_activities sa ON t.sub_activity_id = sa.id
                JOIN activities a ON sa.activity_id = a.id
                WHERE t.id = ? LIMIT 1";
        return $this->fetchOne($sql, [$testId]);
    }

    public function getRandomQuestionsForSubActivity(int $subActivityId, int $limit = 5): array {
        $sql = "SELECT * FROM pkt_question_bank WHERE sub_activity_id = ? ORDER BY RAND() LIMIT ?";
        $questions = $this->fetchAll($sql, [$subActivityId, $limit]);
        if (!empty($questions)) {
            shuffle($questions);
        }
        return $questions;
    }

    public function submitTestAttempt(int $userId, int $testId, array $userAnswers): array {
        $test = $this->getTestById($testId);
        if (!$test) return ['success' => false, 'message' => 'Test not found'];

        $submittedQuestionIds = array_map('intval', array_keys($userAnswers));
        if (empty($submittedQuestionIds)) {
            return ['success' => false, 'message' => 'No question answers were submitted.'];
        }

        $inClause = implode(',', $submittedQuestionIds);
        $questions = $this->fetchAll("SELECT * FROM pkt_question_bank WHERE id IN ($inClause) AND sub_activity_id = ?", [$test['sub_activity_id']]);
        if (empty($questions)) {
            return ['success' => false, 'message' => 'No valid questions found for this test.'];
        }

        $correct = 0;
        $total = count($questions);
        $details = [];

        foreach ($questions as $q) {
            $submittedAns = strtoupper(trim($userAnswers[$q['id']] ?? ''));
            $isRight = ($submittedAns === strtoupper(trim($q['correct_option'])));
            if ($isRight) {
                $correct++;
            }
            $details[] = [
                'question_id' => $q['id'],
                'user_answer' => $submittedAns,
                'correct_answer' => $q['correct_option'],
                'is_correct' => $isRight
            ];
        }

        $percentage = ($total > 0) ? round(($correct / $total) * 100, 2) : 0;
        $passed = ($percentage >= $test['passing_score']) ? 1 : 0;

        $attemptId = $this->insert('pkt_test_attempts', [
            'test_id' => $testId,
            'user_id' => $userId,
            'sub_activity_id' => $test['sub_activity_id'],
            'total_questions' => $total,
            'correct_answers' => $correct,
            'score_percentage' => $percentage,
            'passed' => $passed,
            'attempt_date' => date('Y-m-d H:i:s'),
            'answers_json' => json_encode($details)
        ]);

        $this->updateCertificationProgress($userId, $test['sub_activity_id']);

        return [
            'success' => true,
            'attempt_id' => $attemptId,
            'total' => $total,
            'correct' => $correct,
            'score' => $percentage,
            'passed' => $passed,
            'passing_score' => $test['passing_score']
        ];
    }

    // ----------------------------------------------------
    // PRACTICE FILES & CHECKER VALIDATION (WITH ERROR LOGGING)
    // ----------------------------------------------------
    public function getPracticeFilesForUser(int $userId): array {
        $sql = "SELECT pf.*, sa.sub_activity_name, a.activity_name, d.division_name, c.full_name as checker_name
                FROM practice_files pf
                JOIN sub_activities sa ON pf.sub_activity_id = sa.id
                JOIN activities a ON pf.activity_id = a.id
                LEFT JOIN divisions d ON pf.division_id = d.id
                LEFT JOIN users c ON pf.checker_id = c.id
                WHERE pf.user_id = ?
                ORDER BY pf.id DESC";
        return $this->fetchAll($sql, [$userId]);
    }

    public function getPracticeFilesForChecker(int $checkerId): array {
        $sql = "SELECT pf.*, sa.sub_activity_name, a.activity_name, d.division_name, u.full_name as maker_name
                FROM practice_files pf
                JOIN sub_activities sa ON pf.sub_activity_id = sa.id
                JOIN activities a ON pf.activity_id = a.id
                LEFT JOIN divisions d ON pf.division_id = d.id
                JOIN users u ON pf.user_id = u.id
                WHERE pf.checker_id = ? OR pf.checker_id IS NULL OR ? IN (1, 2)
                ORDER BY pf.id DESC";
        return $this->fetchAll($sql, [$checkerId, $checkerId]);
    }

    public function createPracticeFile(array $data): int {
        $data['status'] = 'Submitted to Checker';
        $data['submitted_at'] = date('Y-m-d H:i:s');
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert('practice_files', $data);
    }

    public function validatePracticeFile(int $practiceId, int $checkerId, string $action, ?string $errorObs = null, ?string $errorDesc = null, ?string $errorType = 'Internal'): bool {
        $pf = $this->fetchOne("SELECT * FROM practice_files WHERE id = ?", [$practiceId]);
        if (!$pf) return false;

        if ($action === 'Approve') {
            $this->update('practice_files', [
                'status' => 'Approved',
                'checker_id' => $checkerId,
                'error_observation' => null,
                'error_description' => null,
                'validated_at' => date('Y-m-d H:i:s')
            ], "id = ?", [$practiceId]);

            $this->updateCertificationProgress($pf['user_id'], $pf['sub_activity_id']);
        } else {
            // Reject with Error Logging
            $this->update('practice_files', [
                'status' => 'Rework Required',
                'checker_id' => $checkerId,
                'error_observation' => sanitize($errorObs ?? 'Validation Error in Practice File'),
                'error_description' => sanitize($errorDesc ?? 'Errors detected by Checker during practice file evaluation'),
                'error_type' => in_array($errorType, ['Internal', 'External']) ? $errorType : 'Internal',
                'validated_at' => date('Y-m-d H:i:s')
            ], "id = ?", [$practiceId]);

            // Also log into error_tracker system table for audit & quality tracking
            $this->insert('error_tracker', [
                'billing_month' => date('Y-m-01'),
                'checking_month' => date('Y-m-01'),
                'error_observation' => 'PRACTICE FILE REJECT: ' . sanitize($errorObs ?? 'Practice Error'),
                'error_description' => sanitize($errorDesc ?? ''),
                'resolution_solution' => 'Rework required by Trainee (Maker)',
                'error_type' => in_array($errorType, ['Internal', 'External']) ? $errorType : 'Internal',
                'maker_id' => $pf['user_id'],
                'checker_id' => $checkerId,
                'created_by' => $checkerId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        return true;
    }

    // ----------------------------------------------------
    // CERTIFICATION ENGINE
    // ----------------------------------------------------
    public function updateCertificationProgress(int $userId, int $subActivityId): void {
        // 1. Check if all training items for this sub_activity are checked off
        $allPlans = $this->fetchAll("SELECT id FROM training_plans WHERE sub_activity_id = ?", [$subActivityId]);
        $trainingCompleted = 1;
        if (!empty($allPlans)) {
            $planIds = array_column($allPlans, 'id');
            $inClause = implode(',', array_map('intval', $planIds));
            $learnedCount = $this->fetchOne("
                SELECT COUNT(*) as cnt 
                FROM user_training_progress 
                WHERE user_id = ? AND training_plan_id IN ($inClause) AND is_learned = 1
            ", [$userId])['cnt'] ?? 0;
            
            if ($learnedCount < count($allPlans)) {
                $trainingCompleted = 0;
            }
        }

        // 2. Check PKT test attempt
        $pktAttempt = $this->fetchOne("
            SELECT MAX(score_percentage) as best_score, MAX(passed) as is_passed 
            FROM pkt_test_attempts 
            WHERE user_id = ? AND sub_activity_id = ?
        ", [$userId, $subActivityId]);

        $pktPassed = ($pktAttempt && !empty($pktAttempt['is_passed'])) ? 1 : 0;
        $pktScore = $pktAttempt['best_score'] ?? 0;

        // 3. Check Practice File Approval
        $practicePassed = $this->fetchOne("
            SELECT COUNT(*) as cnt 
            FROM practice_files 
            WHERE user_id = ? AND sub_activity_id = ? AND status = 'Approved'
        ", [$userId, $subActivityId])['cnt'] > 0 ? 1 : 0;

        // Determine Certification Status
        $isCertified = ($trainingCompleted && $pktPassed && $practicePassed);
        $status = $isCertified ? 'Certified' : 'In Progress';
        $certCode = $isCertified ? ('CERT-' . strtoupper(substr(md5($userId . '_' . $subActivityId), 0, 8))) : null;
        $certifiedAt = $isCertified ? date('Y-m-d H:i:s') : null;

        $existing = $this->fetchOne("SELECT id, status FROM activity_certifications WHERE user_id = ? AND sub_activity_id = ?", [$userId, $subActivityId]);
        if ($existing) {
            $this->update('activity_certifications', [
                'training_completed' => $trainingCompleted,
                'pkt_passed' => $pktPassed,
                'pkt_score' => $pktScore,
                'practice_passed' => $practicePassed,
                'status' => $status,
                'certified_at' => $isCertified ? ($existing['status'] === 'Certified' ? $existing['certified_at'] ?? $certifiedAt : $certifiedAt) : null,
                'certificate_code' => $isCertified ? ($existing['certificate_code'] ?? $certCode) : null
            ], "id = ?", [$existing['id']]);
        } else {
            $this->insert('activity_certifications', [
                'user_id' => $userId,
                'sub_activity_id' => $subActivityId,
                'training_completed' => $trainingCompleted,
                'pkt_passed' => $pktPassed,
                'pkt_score' => $pktScore,
                'practice_passed' => $practicePassed,
                'status' => $status,
                'certified_at' => $certifiedAt,
                'certificate_code' => $certCode
            ]);
        }
    }

    public function getUserCertifications(int $userId): array {
        $sql = "SELECT ac.*, sa.sub_activity_name, a.activity_name, d.division_name
                FROM user_sub_activities usa
                JOIN sub_activities sa ON usa.sub_activity_id = sa.id
                JOIN activities a ON sa.activity_id = a.id
                LEFT JOIN divisions d ON sa.division_id = d.id
                LEFT JOIN activity_certifications ac ON (ac.sub_activity_id = sa.id AND ac.user_id = ?)
                WHERE usa.user_id = ?
                ORDER BY d.division_name ASC, a.activity_name ASC, sa.sub_activity_name ASC";
        return $this->fetchAll($sql, [$userId, $userId]);
    }

    public function getAllCertifications(): array {
        $sql = "SELECT ac.*, sa.sub_activity_name, a.activity_name, u.full_name, u.user_code, u.department
                FROM activity_certifications ac
                JOIN sub_activities sa ON ac.sub_activity_id = sa.id
                JOIN activities a ON sa.activity_id = a.id
                JOIN users u ON ac.user_id = u.id
                ORDER BY ac.certified_at DESC, u.full_name ASC";
        return $this->fetchAll($sql);
    }
}
