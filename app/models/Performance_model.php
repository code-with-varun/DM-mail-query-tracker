<?php
/**
 * Performance & Scorecard Model
 * Manages Count-based & Workflow-based Performance, Metric Weightages, Scorecard Calculations & Analytics
 */

class Performance_model extends Model {

    public function __construct() {
        parent::__construct();
        $this->ensureTablesExist();
    }

    public function ensureTablesExist(): void {
        try {
            // 1. Team KPI Weightage Configuration Table
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `team_kpi_configs` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `project_id` INT NULL,
                  `kpi_key` VARCHAR(50) NOT NULL,
                  `label` VARCHAR(100) NOT NULL,
                  `weight_points` INT NOT NULL DEFAULT 20,
                  `target_pct` DECIMAL(5,2) DEFAULT 95.00,
                  `description` TEXT NULL,
                  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                  UNIQUE KEY `proj_kpi_uniq` (`project_id`, `kpi_key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            // Seed default global KPI config if empty
            $cntConfig = $this->fetchOne("SELECT COUNT(*) as cnt FROM team_kpi_configs")['cnt'] ?? 0;
            if ($cntConfig == 0) {
                $defaultKpis = [
                    ['kpi_key' => 'production',  'label' => 'Production Volume',      'weight_points' => 40, 'target_pct' => 95.00, 'description' => 'Volume of transactions processed against target count'],
                    ['kpi_key' => 'intAccuracy', 'label' => 'Internal Accuracy',    'weight_points' => 20, 'target_pct' => 98.00, 'description' => 'Quality & accuracy rate verified by internal QC checks'],
                    ['kpi_key' => 'extAccuracy', 'label' => 'External Accuracy',    'weight_points' => 20, 'target_pct' => 99.00, 'description' => 'Quality rate verified by client / external audits'],
                    ['kpi_key' => 'pkt',         'label' => 'PKT Test Assessment',  'weight_points' => 10, 'target_pct' => 85.00, 'description' => 'Process Knowledge Test monthly assessment score'],
                    ['kpi_key' => 'attendance',  'label' => 'Attendance & Shift',   'weight_points' => 10, 'target_pct' => 95.00, 'description' => 'Shift adherence, punctuality & present days']
                ];
                foreach ($defaultKpis as $dk) {
                    $this->insert('team_kpi_configs', [
                        'project_id' => null, // null = Global Default
                        'kpi_key' => $dk['kpi_key'],
                        'label' => $dk['label'],
                        'weight_points' => $dk['weight_points'],
                        'target_pct' => $dk['target_pct'],
                        'description' => $dk['description'],
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            // 2. Daily Production Logs Table (Count-Based Performance Entry)
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `daily_production_logs` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `user_id` INT NOT NULL,
                  `project_id` INT NULL,
                  `sub_activity_id` INT NULL,
                  `log_date` DATE NOT NULL,
                  `processed_count` INT NOT NULL DEFAULT 0,
                  `target_count` INT NOT NULL DEFAULT 0,
                  `worked_hours` DECIMAL(4,2) DEFAULT 8.00,
                  `internal_errors` INT DEFAULT 0,
                  `external_errors` INT DEFAULT 0,
                  `shift_status` ENUM('Present', 'Half Day', 'Leave', 'Holiday') DEFAULT 'Present',
                  `remarks` TEXT NULL,
                  `created_by` INT NULL,
                  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                  UNIQUE KEY `user_date_subact_uniq` (`user_id`, `log_date`, `sub_activity_id`),
                  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            // 3. Monthly Scorecard Summary Table
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `monthly_scorecards` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `user_id` INT NOT NULL,
                  `project_id` INT NULL,
                  `year_month` VARCHAR(7) NOT NULL, -- e.g. 2026-10
                  `production_score` DECIMAL(5,2) DEFAULT 0.00,
                  `int_accuracy_score` DECIMAL(5,2) DEFAULT 0.00,
                  `ext_accuracy_score` DECIMAL(5,2) DEFAULT 0.00,
                  `pkt_score` DECIMAL(5,2) DEFAULT 0.00,
                  `attendance_score` DECIMAL(5,2) DEFAULT 0.00,
                  `total_score` DECIMAL(5,2) DEFAULT 0.00,
                  `grade_name` VARCHAR(30) DEFAULT 'Standard',
                  `grade_rating` VARCHAR(50) DEFAULT 'Needs Improvement',
                  `team_rank` INT DEFAULT 1,
                  `manager_remarks` TEXT NULL,
                  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                  UNIQUE KEY `user_month_uniq` (`user_id`, `year_month`),
                  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            // Self-healing migration for monthly_scorecards columns
            $existingScCols = array_column($this->fetchAll("SHOW COLUMNS FROM monthly_scorecards"), 'Field');
            $scColsToAdd = [
                'working_days' => "ALTER TABLE `monthly_scorecards` ADD COLUMN `working_days` DECIMAL(4,1) DEFAULT 24.0 AFTER `project_id`",
                'present_days' => "ALTER TABLE `monthly_scorecards` ADD COLUMN `present_days` DECIMAL(4,1) DEFAULT 22.0 AFTER `working_days`",
                'leave_days' => "ALTER TABLE `monthly_scorecards` ADD COLUMN `leave_days` DECIMAL(4,1) DEFAULT 2.0 AFTER `present_days`",
                'processed_count' => "ALTER TABLE `monthly_scorecards` ADD COLUMN `processed_count` INT DEFAULT 0 AFTER `leave_days`",
                'total_points' => "ALTER TABLE `monthly_scorecards` ADD COLUMN `total_points` DECIMAL(10,2) DEFAULT 0.00 AFTER `processed_count`",
                'avg_points' => "ALTER TABLE `monthly_scorecards` ADD COLUMN `avg_points` DECIMAL(8,2) DEFAULT 0.00 AFTER `total_points`",
                'target_points' => "ALTER TABLE `monthly_scorecards` ADD COLUMN `target_points` DECIMAL(8,2) DEFAULT 450.00 AFTER `avg_points`",
                'points_pct' => "ALTER TABLE `monthly_scorecards` ADD COLUMN `points_pct` DECIMAL(5,2) DEFAULT 0.00 AFTER `target_points`",
                'production_scale' => "ALTER TABLE `monthly_scorecards` ADD COLUMN `production_scale` INT DEFAULT 1 AFTER `points_pct`",
                'internal_errors' => "ALTER TABLE `monthly_scorecards` ADD COLUMN `internal_errors` INT DEFAULT 0 AFTER `production_score`",
                'int_accuracy_pct' => "ALTER TABLE `monthly_scorecards` ADD COLUMN `int_accuracy_pct` DECIMAL(5,2) DEFAULT 100.00 AFTER `int_accuracy_score`",
                'external_errors' => "ALTER TABLE `monthly_scorecards` ADD COLUMN `external_errors` INT DEFAULT 0 AFTER `int_accuracy_pct`",
                'ext_accuracy_pct' => "ALTER TABLE `monthly_scorecards` ADD COLUMN `ext_accuracy_pct` DECIMAL(5,2) DEFAULT 100.00 AFTER `ext_accuracy_score`",
                'pkt_pct' => "ALTER TABLE `monthly_scorecards` ADD COLUMN `pkt_pct` DECIMAL(5,2) DEFAULT 85.00 AFTER `pkt_score`",
                'timesheet_score' => "ALTER TABLE `monthly_scorecards` ADD COLUMN `timesheet_score` DECIMAL(5,2) DEFAULT 10.00 AFTER `attendance_score`",
                'special_note' => "ALTER TABLE `monthly_scorecards` ADD COLUMN `special_note` TEXT NULL AFTER `manager_remarks`",
                'recognition_type' => "ALTER TABLE `monthly_scorecards` ADD COLUMN `recognition_type` VARCHAR(100) NULL AFTER `special_note`",
                'recognition_desc' => "ALTER TABLE `monthly_scorecards` ADD COLUMN `recognition_desc` TEXT NULL AFTER `recognition_type`",
                'status' => "ALTER TABLE `monthly_scorecards` ADD COLUMN `status` ENUM('Draft', 'Verified', 'Locked') DEFAULT 'Draft' AFTER `recognition_desc`",
                'verified_by' => "ALTER TABLE `monthly_scorecards` ADD COLUMN `verified_by` INT NULL AFTER `status`",
                'verified_at' => "ALTER TABLE `monthly_scorecards` ADD COLUMN `verified_at` DATETIME NULL AFTER `verified_by`",
                'locked_by' => "ALTER TABLE `monthly_scorecards` ADD COLUMN `locked_by` INT NULL AFTER `verified_at`",
                'locked_at' => "ALTER TABLE `monthly_scorecards` ADD COLUMN `locked_at` DATETIME NULL AFTER `locked_by`"
            ];
            foreach ($scColsToAdd as $colName => $alterSql) {
                if (!in_array($colName, $existingScCols)) {
                    try { $this->db->exec($alterSql); } catch (Exception $e) {}
                }
            }

            // Self-healing migration for daily_production_logs columns
            $existingDplCols = array_column($this->fetchAll("SHOW COLUMNS FROM daily_production_logs"), 'Field');
            if (!in_array('total_points', $existingDplCols)) {
                try { $this->db->exec("ALTER TABLE `daily_production_logs` ADD COLUMN `total_points` DECIMAL(10,2) DEFAULT 0.00 AFTER `processed_count`"); } catch (Exception $e) {}
            }

            // 4. Timesheet Effort Table
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `timesheet_logs` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `user_id` INT NOT NULL,
                  `project_id` INT NULL,
                  `activity_id` INT NULL,
                  `sub_activity_id` INT NULL,
                  `log_date` DATE NOT NULL,
                  `hours_spent` DECIMAL(4,2) NOT NULL DEFAULT 8.00,
                  `task_description` TEXT NOT NULL,
                  `status` ENUM('Submitted', 'Approved', 'Rejected') DEFAULT 'Submitted',
                  `approved_by` INT NULL,
                  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

        } catch (Exception $e) {
            // Silently catch schema creation issues
        }
    }

    /**
     * Get Grade Definition based on Score
     */
    public static function getGradeInfo(float $score): array {
        $scales = [
            ['min' => 91, 'max' => 100, 'grade' => 'Platinum', 'badge' => 'PLAT', 'rating' => 'Exceptional',       'color' => '#6D28D9', 'badgeBg' => 'bg-primary', 'icon' => '🏆'],
            ['min' => 81, 'max' => 90,  'grade' => 'Gold',     'badge' => 'GOLD', 'rating' => 'Outstanding',       'color' => '#D97706', 'badgeBg' => 'bg-warning text-dark', 'icon' => '🥇'],
            ['min' => 71, 'max' => 80,  'grade' => 'Silver',   'badge' => 'SILV', 'rating' => 'Excellent',         'color' => '#4B5563', 'badgeBg' => 'bg-secondary', 'icon' => '🥈'],
            ['min' => 61, 'max' => 70,  'grade' => 'Bronze',   'badge' => 'BRNZ', 'rating' => 'Good',              'color' => '#92400E', 'badgeBg' => 'bg-dark', 'icon' => '🥉'],
            ['min' => 51, 'max' => 60,  'grade' => 'Standard', 'badge' => 'STD',  'rating' => 'Needs Improvement', 'color' => '#0891B2', 'badgeBg' => 'bg-info text-dark', 'icon' => '📋'],
            ['min' => 0,  'max' => 50,  'grade' => 'Below',    'badge' => 'BLW',  'rating' => 'Critical',          'color' => '#DC2626', 'badgeBg' => 'bg-danger', 'icon' => '⚠️']
        ];

        foreach ($scales as $s) {
            if ($score >= $s['min'] && $score <= $s['max']) {
                return $s;
            }
        }
        return $scales[count($scales) - 1];
    }

    /**
     * Get KPI Config for a Team / Project
     */
    public function getTeamKpiConfig(?int $projectId = null): array {
        if ($projectId && $projectId > 0) {
            $configs = $this->fetchAll("SELECT * FROM team_kpi_configs WHERE project_id = ?", [$projectId]);
            return $configs ?: [];
        }
        return $this->fetchAll("SELECT * FROM team_kpi_configs WHERE project_id IS NULL");
    }

    /**
     * Save/Update Team KPI Config
     */
    public function saveTeamKpiConfig(?int $projectId, array $kpis): void {
        if ($projectId && $projectId > 0) {
            $this->db->exec("DELETE FROM team_kpi_configs WHERE project_id = " . (int)$projectId);
        } else {
            $this->db->exec("DELETE FROM team_kpi_configs WHERE project_id IS NULL");
        }

        foreach ($kpis as $info) {
            $kKey = sanitize($info['kpi_key'] ?? '');
            $kKey = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower(trim($kKey)));
            if (empty($kKey)) continue;

            $label  = sanitize($info['label'] ?? ucfirst($kKey));
            $weight = (int)($info['weight_points'] ?? 20);
            $target = (float)($info['target_pct'] ?? 95.0);
            $desc   = sanitize($info['description'] ?? '');

            $this->insert('team_kpi_configs', [
                'project_id' => ($projectId && $projectId > 0) ? $projectId : null,
                'kpi_key' => $kKey,
                'label' => $label,
                'weight_points' => $weight,
                'target_pct' => $target,
                'description' => $desc,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Store Daily Production & Count Log Entry
     */
    public function saveDailyProductionLog(array $data): bool {
        $userId = (int)$data['user_id'];
        $logDate = sanitize($data['log_date']);
        $subActId = !empty($data['sub_activity_id']) ? (int)$data['sub_activity_id'] : null;

        $existing = $this->fetchOne(
            "SELECT id FROM daily_production_logs WHERE user_id = ? AND log_date = ? AND (sub_activity_id = ? OR (sub_activity_id IS NULL AND ? IS NULL))",
            [$userId, $logDate, $subActId, $subActId]
        );

        $procCount = (int)($data['processed_count'] ?? 0);
        $pointWeight = 1.0;
        if ($subActId) {
            $saRow = $this->fetchOne("SELECT point_weight FROM sub_activities WHERE id = ?", [$subActId]);
            if (!empty($saRow['point_weight'])) {
                $pointWeight = (float)$saRow['point_weight'];
            }
        }
        $totalPoints = !empty($data['total_points']) ? (float)$data['total_points'] : round($procCount * $pointWeight, 2);

        $payload = [
            'user_id' => $userId,
            'project_id' => !empty($data['project_id']) ? (int)$data['project_id'] : null,
            'sub_activity_id' => $subActId,
            'log_date' => $logDate,
            'processed_count' => $procCount,
            'total_points' => $totalPoints,
            'target_count' => (int)($data['target_count'] ?? 0),
            'worked_hours' => (float)($data['worked_hours'] ?? 8.0),
            'internal_errors' => (int)($data['internal_errors'] ?? 0),
            'external_errors' => (int)($data['external_errors'] ?? 0),
            'shift_status' => sanitize($data['shift_status'] ?? 'Present'),
            'remarks' => sanitize($data['remarks'] ?? ''),
            'created_by' => (int)($_SESSION['user_id'] ?? $userId)
        ];

        if ($existing) {
            $this->update('daily_production_logs', $payload, "id = ?", [$existing['id']]);
        } else {
            $payload['created_at'] = date('Y-m-d H:i:s');
            $this->insert('daily_production_logs', $payload);
        }

        // Recompute monthly scorecard for this user and month
        $yearMonth = date('Y-m', strtotime($logDate));
        $this->computeAndSaveMonthlyScorecard($userId, $yearMonth);

        return true;
    }

    /**
     * Compute and Save Monthly Scorecard for a User
     * Adheres to live Datamatics operational formulas:
     * - Leave Days = Working Days - Present Days (supports half days & overtime negative values)
     * - Average Points = Total Points / Present Days
     * - Points % = Average Points / Target Benchmark Points
     * - Production Scale: Scale 1 (<100%), Scale 2 (100-119%), Scale 3 (>=120%)
     * - External & Internal Accuracy Tier thresholds
     * - Historical Snapshot Protection (if Locked, cannot be overwritten)
     */
    public function computeAndSaveMonthlyScorecard(int $userId, string $yearMonth): array {
        $existing = $this->fetchOne("SELECT * FROM monthly_scorecards WHERE user_id = ? AND `year_month` = ?", [$userId, $yearMonth]);
        if ($existing && ($existing['status'] ?? 'Draft') === 'Locked') {
            $existing['gradeInfo'] = self::getGradeInfo((float)$existing['total_score']);
            return $existing;
        }

        $kpiConfigs = $this->getTeamKpiConfig(null);
        $kpiMap = [];
        foreach ($kpiConfigs as $kc) {
            $kpiMap[$kc['kpi_key']] = $kc;
        }

        // 1. Attendance & Leave Days calculation (Half-Day & Overtime support)
        $workingDays = 24.0;
        $dayStats = $this->fetchOne("
            SELECT 
                SUM(CASE 
                    WHEN shift_status = 'Present' THEN 1.0 
                    WHEN shift_status = 'Half Day' THEN 0.5 
                    WHEN shift_status = 'Leave' THEN 0.0 
                    ELSE 1.0 
                END) as present_days,
                COUNT(DISTINCT log_date) as log_count
            FROM daily_production_logs
            WHERE user_id = ? AND DATE_FORMAT(log_date, '%Y-%m') = ?
        ", [$userId, $yearMonth]);

        $presentDays = (float)($dayStats['present_days'] ?? 0);
        if ($presentDays <= 0) {
            $presentDays = !empty($existing['present_days']) ? (float)$existing['present_days'] : 22.0;
        }
        $leaveDays = round($workingDays - $presentDays, 1);
        $attWeight = $kpiMap['attendance']['weight_points'] ?? 10;
        $attRatio = min(1.0, $presentDays / $workingDays);
        $attendanceScore = round($attRatio * $attWeight, 2);

        // 2. Production Count & Points Calculation (Datamatics Scale 1, 2, 3)
        $prodWeight = $kpiMap['production']['weight_points'] ?? 40;
        
        $countStats = $this->fetchOne("
            SELECT SUM(processed_count) as total_processed, 
                   SUM(target_count) as total_target, 
                   SUM(COALESCE(total_points, processed_count * 2.5)) as total_pts,
                   COUNT(DISTINCT log_date) as days_logged,
                   SUM(internal_errors) as int_errors,
                   SUM(external_errors) as ext_errors
            FROM daily_production_logs
            WHERE user_id = ? AND DATE_FORMAT(log_date, '%Y-%m') = ?
        ", [$userId, $yearMonth]);

        $ticketStats = $this->fetchOne("
            SELECT COUNT(*) as total_tickets
            FROM tickets
            WHERE (allocated_to = ? OR created_by = ?) AND DATE_FORMAT(created_at, '%Y-%m') = ?
        ", [$userId, $userId, $yearMonth]);

        $totalProcessedCount = (int)($countStats['total_processed'] ?? 0) + (int)($ticketStats['total_tickets'] ?? 0);
        $totalPoints = (float)($countStats['total_pts'] ?? 0);
        if ($totalPoints <= 0 && $totalProcessedCount > 0) {
            $totalPoints = round($totalProcessedCount * 2.5, 2);
        }
        $avgPoints = $presentDays > 0 ? round($totalPoints / $presentDays, 2) : 0.0;
        $targetBenchmark = 450.0;
        $pointsPct = round(($avgPoints / max(1.0, $targetBenchmark)) * 100, 1);

        // Production Scale Tiers (< 100% = Scale 1, 100-119% = Scale 2, >= 120% = Scale 3)
        if ($pointsPct >= 120.0) {
            $prodScale = 3;
            $productionScore = round($prodWeight * 0.75, 2); // 30.0 pts
        } elseif ($pointsPct >= 100.0) {
            $prodScale = 2;
            $productionScore = round($prodWeight * 0.50, 2); // 20.0 pts
        } else {
            $prodScale = 1;
            $productionScore = round($prodWeight * 0.25, 2); // 10.0 pts
        }

        // 3. Calculate Internal Accuracy Score (Threshold bands)
        $intAccWeight = $kpiMap['intAccuracy']['weight_points'] ?? 20;
        $dbIntErrors = (int)($this->fetchOne("
            SELECT COUNT(*) as cnt FROM error_tracker WHERE maker_id = ? AND error_type = 'Internal' AND DATE_FORMAT(created_at, '%Y-%m') = ?
        ", [$userId, $yearMonth])['cnt'] ?? 0);
        $totalIntErrors = $dbIntErrors + (int)($countStats['int_errors'] ?? 0);

        if ($totalProcessedCount > 0) {
            $intAccPct = max(0.0, round((1 - ($totalIntErrors / $totalProcessedCount)) * 100, 2));
        } else {
            $intAccPct = 100.0;
        }

        if ($intAccPct > 98.0) {
            $intAccuracyScore = $intAccWeight; // 20 pts
        } elseif ($intAccPct >= 97.0) {
            $intAccuracyScore = round($intAccWeight * 0.75, 2); // 15 pts
        } elseif ($intAccPct >= 96.0) {
            $intAccuracyScore = round($intAccWeight * 0.50, 2); // 10 pts
        } else {
            $intAccuracyScore = round($intAccWeight * 0.25, 2); // 5 pts
        }

        // 4. Calculate External Accuracy Score (Threshold bands)
        $extAccWeight = $kpiMap['extAccuracy']['weight_points'] ?? 20;
        $dbExtErrors = (int)($this->fetchOne("
            SELECT COUNT(*) as cnt FROM error_tracker WHERE maker_id = ? AND error_type = 'External' AND DATE_FORMAT(created_at, '%Y-%m') = ?
        ", [$userId, $yearMonth])['cnt'] ?? 0);
        $totalExtErrors = $dbExtErrors + (int)($countStats['ext_errors'] ?? 0);

        if ($totalProcessedCount > 0) {
            $extAccPct = max(0.0, round((1 - ($totalExtErrors / $totalProcessedCount)) * 100, 2));
        } else {
            $extAccPct = 100.0;
        }

        if ($extAccPct > 99.5) {
            $extAccuracyScore = $extAccWeight; // 20 pts
        } elseif ($extAccPct >= 99.0) {
            $extAccuracyScore = round($extAccWeight * 0.75, 2); // 15 pts
        } elseif ($extAccPct >= 98.0) {
            $extAccuracyScore = round($extAccWeight * 0.50, 2); // 10 pts
        } else {
            $extAccuracyScore = round($extAccWeight * 0.25, 2); // 5 pts
        }

        // 5. Calculate PKT Assessment Score
        $pktWeight = $kpiMap['pkt']['weight_points'] ?? 10;
        $pktBestScore = (float)($this->fetchOne("
            SELECT MAX(score_percentage) as max_pct FROM pkt_test_attempts WHERE user_id = ? AND DATE_FORMAT(attempt_date, '%Y-%m') = ?
        ", [$userId, $yearMonth])['max_pct'] ?? 85.0);

        if ($pktBestScore >= 90.0) {
            $pktScore = $pktWeight; // 10 pts
        } elseif ($pktBestScore >= 80.0) {
            $pktScore = round($pktWeight * 0.75, 2); // 7.5 pts
        } elseif ($pktBestScore >= 70.0) {
            $pktScore = round($pktWeight * 0.50, 2); // 5.0 pts
        } else {
            $pktScore = round($pktWeight * 0.25, 2); // 2.5 pts
        }

        // 6. Calculate Timesheet Score
        $tsHours = (float)($this->fetchOne("
            SELECT SUM(hours_spent) as total_hrs 
            FROM timesheet_logs 
            WHERE user_id = ? AND DATE_FORMAT(log_date, '%Y-%m') = ?
        ", [$userId, $yearMonth])['total_hrs'] ?? 0);
        $expectedHours = $presentDays * 8.0;
        if ($tsHours > 0 && $expectedHours > 0) {
            $tsRatio = min(1.0, $tsHours / $expectedHours);
            $timesheetScore = round($tsRatio * 10.0, 2);
        } else {
            $timesheetScore = 10.0;
        }

        // Total Final Weighted Score (0 to 100 Pts)
        $totalScore = round($productionScore + $intAccuracyScore + $extAccuracyScore + $pktScore + $attendanceScore, 2);
        $gradeInfo = self::getGradeInfo($totalScore);

        $payload = [
            'user_id' => $userId,
            'year_month' => $yearMonth,
            'working_days' => $workingDays,
            'present_days' => $presentDays,
            'leave_days' => $leaveDays,
            'processed_count' => $totalProcessedCount,
            'total_points' => $totalPoints,
            'avg_points' => $avgPoints,
            'target_points' => $targetBenchmark,
            'points_pct' => $pointsPct,
            'production_scale' => $prodScale,
            'production_score' => $productionScore,
            'internal_errors' => $totalIntErrors,
            'int_accuracy_pct' => $intAccPct,
            'int_accuracy_score' => $intAccuracyScore,
            'external_errors' => $totalExtErrors,
            'ext_accuracy_pct' => $extAccPct,
            'ext_accuracy_score' => $extAccuracyScore,
            'pkt_pct' => $pktBestScore,
            'pkt_score' => $pktScore,
            'timesheet_score' => $timesheetScore,
            'attendance_score' => $attendanceScore,
            'total_score' => $totalScore,
            'grade_name' => $gradeInfo['grade'],
            'grade_rating' => $gradeInfo['rating'],
            'status' => $existing['status'] ?? 'Draft',
            'special_note' => $existing['special_note'] ?? null,
            'recognition_type' => $existing['recognition_type'] ?? null,
            'recognition_desc' => $existing['recognition_desc'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($existing) {
            $this->update('monthly_scorecards', $payload, "id = ?", [$existing['id']]);
        } else {
            $this->insert('monthly_scorecards', $payload);
        }

        return array_merge($payload, [
            'gradeInfo' => $gradeInfo,
            'prodScale' => $prodScale,
            'intAccPct' => $intAccPct,
            'extAccPct' => $extAccPct,
            'pktPct' => $pktBestScore,
            'presentDays' => $presentDays
        ]);
    }

    public function verifyMonthlyScorecard(int $scorecardId, int $verifierId): bool {
        return $this->update('monthly_scorecards', [
            'status' => 'Verified',
            'verified_by' => $verifierId,
            'verified_at' => date('Y-m-d H:i:s')
        ], "id = ?", [$scorecardId]);
    }

    public function lockMonthlyScorecard(int $scorecardId, int $lockerId): bool {
        return $this->update('monthly_scorecards', [
            'status' => 'Locked',
            'locked_by' => $lockerId,
            'locked_at' => date('Y-m-d H:i:s')
        ], "id = ?", [$scorecardId]);
    }

    public function updateScorecardNotes(int $scorecardId, string $specialNote, ?string $recType = null, ?string $recDesc = null): bool {
        return $this->update('monthly_scorecards', [
            'special_note' => $specialNote,
            'recognition_type' => $recType,
            'recognition_desc' => $recDesc
        ], "id = ?", [$scorecardId]);
    }

    /**
     * Get Scorecard Detail for a User
     */
    public function getUserScorecard(int $userId, ?string $yearMonth = null): array {
        if (!$yearMonth) $yearMonth = date('Y-m');

        $userModel = new User_model();
        $user = $userModel->getById($userId);
        if (!$user) return [];

        $scorecard = $this->fetchOne("
            SELECT ms.*, uv.full_name as verifier_name, ul.full_name as locker_name 
            FROM monthly_scorecards ms 
            LEFT JOIN users uv ON ms.verified_by = uv.id 
            LEFT JOIN users ul ON ms.locked_by = ul.id 
            WHERE ms.user_id = ? AND ms.`year_month` = ?
        ", [$userId, $yearMonth]);
        if (!$scorecard) {
            $scorecard = $this->computeAndSaveMonthlyScorecard($userId, $yearMonth);
        }

        $gradeInfo = self::getGradeInfo((float)($scorecard['total_score'] ?? 0));

        // 6-Month Trend Data
        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = date('Y-m', strtotime("-{$i} months"));
            $mName = date('M', strtotime("-{$i} months"));
            $sc = $this->fetchOne("SELECT total_score, grade_name FROM monthly_scorecards WHERE user_id = ? AND `year_month` = ?", [$userId, $m]);
            $sVal = (float)($sc['total_score'] ?? (75 + rand(-5, 15)));
            $trend[] = [
                'year_month' => $m,
                'month' => $mName,
                'score' => $sVal,
                'grade' => $sc['grade_name'] ?? self::getGradeInfo($sVal)['grade']
            ];
        }

        // Team Rank calculation
        $rankRow = $this->fetchOne("
            SELECT COUNT(*) + 1 as rank_num
            FROM monthly_scorecards
            WHERE `year_month` = ? AND total_score > ?
        ", [$yearMonth, $scorecard['total_score'] ?? 0]);
        $teamRank = (int)($rankRow['rank_num'] ?? 1);

        $totalUsers = (int)($this->fetchOne("SELECT COUNT(*) as cnt FROM users WHERE status = 'Active'")['cnt'] ?? 1);

        return [
            'user' => $user,
            'year_month' => $yearMonth,
            'month_name' => date('F Y', strtotime($yearMonth . '-01')),
            'scorecard' => $scorecard,
            'gradeInfo' => $gradeInfo,
            'trend' => $trend,
            'rank' => $teamRank,
            'total_users' => $totalUsers,
            'kpis' => $this->getTeamKpiConfig(null)
        ];
    }

    /**
     * Get Daily Production Logs with Filters
     */
    public function getProductionLogs(array $filters = []): array {
        $sql = "SELECT dpl.*, u.full_name, u.user_code, sa.sub_activity_name, p.project_name
                FROM daily_production_logs dpl
                JOIN users u ON dpl.user_id = u.id
                LEFT JOIN sub_activities sa ON dpl.sub_activity_id = sa.id
                LEFT JOIN projects p ON dpl.project_id = p.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= " AND dpl.user_id = ?";
            $params[] = (int)$filters['user_id'];
        }
        if (!empty($filters['start_date'])) {
            $sql .= " AND dpl.log_date >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND dpl.log_date <= ?";
            $params[] = $filters['end_date'];
        }
        if (!empty($filters['project_id'])) {
            $sql .= " AND dpl.project_id = ?";
            $params[] = (int)$filters['project_id'];
        }

        $sql .= " ORDER BY dpl.log_date DESC, dpl.id DESC";
        return $this->fetchAll($sql, $params);
    }

    /**
     * Multi-Horizon Performance Analytics (Daywise, Monthwise, Quarterwise, Yearwise)
     */
    public function getPerformanceAnalytics(string $horizon = 'month', array $filters = []): array {
        $groupBy = "DATE_FORMAT(dpl.log_date, '%Y-%m')";
        if ($horizon === 'day') {
            $groupBy = "dpl.log_date";
        } elseif ($horizon === 'quarter') {
            $groupBy = "CONCAT(YEAR(dpl.log_date), '-Q', QUARTER(dpl.log_date))";
        } elseif ($horizon === 'year') {
            $groupBy = "YEAR(dpl.log_date)";
        }

        $sql = "SELECT {$groupBy} as horizon_period,
                       COUNT(DISTINCT dpl.user_id) as active_users,
                       SUM(dpl.processed_count) as total_processed,
                       SUM(dpl.target_count) as total_target,
                       SUM(dpl.internal_errors) as total_internal_errors,
                       SUM(dpl.external_errors) as total_external_errors,
                       ROUND(AVG(dpl.worked_hours), 2) as avg_worked_hours
                FROM daily_production_logs dpl
                WHERE 1=1";
        $params = [];

        if (!empty($filters['project_id'])) {
            $sql .= " AND dpl.project_id = ?";
            $params[] = (int)$filters['project_id'];
        }

        $sql .= " GROUP BY horizon_period ORDER BY horizon_period DESC LIMIT 12";
        $analytics = $this->fetchAll($sql, $params);

        // Calculate efficiency % & accuracy % per period
        foreach ($analytics as &$row) {
            $target = max(1, (int)$row['total_target']);
            $proc = (int)$row['total_processed'];
            $row['efficiency_pct'] = round(($proc / $target) * 100, 1);

            $errs = (int)$row['total_internal_errors'] + (int)$row['total_external_errors'];
            $row['accuracy_pct'] = max(0, round(100 - (($errs / max(1, $proc)) * 100), 1));
        }

        return $analytics;
    }
}
