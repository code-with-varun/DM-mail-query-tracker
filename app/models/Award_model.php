<?php
/**
 * Datamatics Annual Achievers Awards (AAA) & DM-ISPARK Recognition Model
 * Implements the complete awards framework from awards contest.xlsx:
 * - 20 Standard Corporate & Operational Awards across 6 Categories
 * - Monthly, Quarterly, Annual, and Long Service Milestone Tiers
 * - Role-Based Eligibility (Employee, TL, Manager, Senior Management, Team, Partner)
 * - 6-Factor Automated Scoring Engine (Productivity 30%, Quality 25%, SLA 15%, Attendance 10%, Innovation 10%, Teamwork 10%)
 */

class Award_model extends Model {

    public function __construct() {
        parent::__construct();
        $this->ensureAwardTablesExist();
    }

    /**
     * Ensure awards_master and award_winners tables exist
     */
    public function ensureAwardTablesExist(): void {
        try {
            $this->query("
                CREATE TABLE IF NOT EXISTS `awards_master` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `award_name` VARCHAR(150) NOT NULL UNIQUE,
                    `award_code` VARCHAR(50) NOT NULL UNIQUE,
                    `category` ENUM('Performance', 'Quality', 'Productivity', 'Innovation', 'Operations', 'Leadership', 'Strategic', 'Service', 'Recognition', 'Team', 'Executive', 'Lifetime') NOT NULL DEFAULT 'Performance',
                    `periodicity` ENUM('monthly', 'quarterly', 'annual', 'milestone', 'both_mq', 'all') NOT NULL DEFAULT 'monthly',
                    `competition_type` ENUM('individual', 'team_vs_team', 'tl_vs_tl', 'manager_vs_manager', 'external', 'milestone') NOT NULL DEFAULT 'individual',
                    `eligible_roles` VARCHAR(100) NOT NULL DEFAULT 'Employee',
                    `max_winners` INT NOT NULL DEFAULT 1,
                    `badge_icon` VARCHAR(50) NOT NULL DEFAULT 'fa-trophy',
                    `badge_color` VARCHAR(30) NOT NULL DEFAULT '#f59e0b',
                    `weight_productivity` DECIMAL(4,2) DEFAULT 0.30,
                    `weight_quality` DECIMAL(4,2) DEFAULT 0.25,
                    `weight_sla` DECIMAL(4,2) DEFAULT 0.15,
                    `weight_attendance` DECIMAL(4,2) DEFAULT 0.10,
                    `weight_innovation` DECIMAL(4,2) DEFAULT 0.10,
                    `weight_teamwork` DECIMAL(4,2) DEFAULT 0.10,
                    `description` TEXT NULL,
                    `criteria` TEXT NULL,
                    `is_automated` TINYINT(1) DEFAULT 1,
                    `display_order` INT DEFAULT 1,
                    `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            $this->query("
                CREATE TABLE IF NOT EXISTS `award_winners` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `award_id` INT NOT NULL,
                    `period_type` ENUM('monthly', 'quarterly', 'annual', 'milestone') NOT NULL DEFAULT 'monthly',
                    `period_key` VARCHAR(30) NOT NULL,
                    `user_id` INT NULL,
                    `project_id` INT NULL,
                    `recipient_name` VARCHAR(150) NOT NULL,
                    `recipient_role` VARCHAR(100) NOT NULL,
                    `score` DECIMAL(5,2) DEFAULT 0.00,
                    `rank_position` INT DEFAULT 1,
                    `citation` TEXT NOT NULL,
                    `productivity_score` DECIMAL(5,2) DEFAULT 0.00,
                    `quality_score` DECIMAL(5,2) DEFAULT 0.00,
                    `sla_score` DECIMAL(5,2) DEFAULT 0.00,
                    `attendance_score` DECIMAL(5,2) DEFAULT 0.00,
                    `innovation_score` DECIMAL(5,2) DEFAULT 0.00,
                    `teamwork_score` DECIMAL(5,2) DEFAULT 0.00,
                    `awarded_by` INT NULL,
                    `awarded_date` DATE NOT NULL,
                    `display_in_slideshow` TINYINT(1) DEFAULT 1,
                    `highlight_until` DATE NULL,
                    `status` ENUM('Awarded', 'Nominated', 'Approved') DEFAULT 'Awarded',
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (`award_id`) REFERENCES `awards_master`(`id`) ON DELETE CASCADE,
                    INDEX `idx_award_period` (`period_type`, `period_key`),
                    INDEX `idx_award_user` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            // Check if master catalog is seeded
            $cnt = (int)$this->fetchOne("SELECT COUNT(*) as c FROM awards_master")['c'];
            if ($cnt === 0) {
                $this->seedAwardsMaster();
            }
        } catch (\Throwable $e) {
            error_log("Error in ensureAwardTablesExist: " . $e->getMessage());
        }
    }

    /**
     * Seed all 20 Awards from the Datamatics AAA & DM-ISPARK Framework
     */
    public function seedAwardsMaster(): void {
        $awards = [
            // 1. Monthly Individual Awards
            [
                'Spot Award', 'AWD-SPOT', 'Recognition', 'monthly', 'individual', 'Employee', 2,
                'fa-bolt', '#f59e0b', 0.20, 0.20, 0.20, 0.10, 0.15, 0.15,
                'Immediate recognition for going above and beyond on critical queries or turnaround emergencies.',
                'Exceptional on-the-spot contribution, supervisor nomination, and unblocking critical client deliverables.',
                0, 1
            ],
            [
                'Star Performer', 'AWD-STAR', 'Performance', 'both_mq', 'individual', 'Employee', 2,
                'fa-star', '#3b82f6', 0.30, 0.25, 0.15, 0.10, 0.10, 0.10,
                'Highest overall weighted KPI score across productivity, accuracy, SLA, and shift discipline.',
                'Top-ranked composite score benchmarked against Datamatics operational scale standards.',
                1, 2
            ],
            [
                'Quality Champion', 'AWD-QUAL', 'Quality', 'all', 'individual', 'Employee', 2,
                'fa-award', '#10b981', 0.15, 0.50, 0.15, 0.10, 0.05, 0.05,
                'Excellence in process accuracy, zero audit defects, and impeccable four-eyes maker-checker standards.',
                'Internal accuracy >= 99.5% and External accuracy >= 99.8% with zero rework iterations.',
                1, 3
            ],
            [
                'Productivity Star', 'AWD-PROD', 'Productivity', 'all', 'individual', 'Employee', 2,
                'fa-chart-line', '#6366f1', 0.55, 0.20, 0.10, 0.10, 0.00, 0.05,
                'Highest processed invoice and mail query throughput volume against target benchmark points.',
                'Points achievement >= 120% (Scale 3) with sustained active daily production logging.',
                1, 4
            ],
            [
                'Customer Excellence Award', 'AWD-CUST', 'Service', 'all', 'individual', 'Employee', 1,
                'fa-smile-beam', '#06b6d4', 0.20, 0.25, 0.35, 0.10, 0.05, 0.05,
                'Flawless SLA adherence, stakeholder satisfaction, and zero customer escalations.',
                '100% on-time delivery across all assigned tickets, zero SLA breaches, and positive client feedback.',
                1, 5
            ],

            // 2. Quarterly & Emerging Talent Awards
            [
                'Rising Star Award', 'AWD-RISE', 'Innovation', 'both_mq', 'individual', 'Employee', 1,
                'fa-rocket', '#ec4899', 0.25, 0.25, 0.15, 0.10, 0.15, 0.10,
                'Fastest emerging talent with under 2 years experience demonstrating high learning agility and rapid mastery.',
                'Continuous service < 2 years, PKT assessment score >= 90%, and outstanding month-over-month trajectory.',
                1, 6
            ],
            [
                'AI Champion Award', 'AWD-AI', 'Innovation', 'all', 'individual', 'Any', 1,
                'fa-robot', '#8b5cf6', 0.15, 0.15, 0.10, 0.10, 0.40, 0.10,
                'Pioneering AI-first workflows, generative AI prompts, and intelligent processing solutions.',
                'Active development and implementation of AI tools, automated prompt libraries, or LLM-driven query resolution.',
                0, 7
            ],
            [
                'Automation Excellence Award', 'AWD-AUTO', 'Innovation', 'all', 'individual', 'Any', 1,
                'fa-cogs', '#14b8a6', 0.20, 0.15, 0.15, 0.05, 0.35, 0.10,
                'Developing macros, scripts, RPA bots, or system integrations that yielded measurable hours saved.',
                'Measurable reduction in processing turnaround time, cost savings, or eliminate manual repetitive steps.',
                0, 8
            ],

            // 3. Team & Cross-Team Awards
            [
                'Team Excellence Award', 'AWD-TEAM-EXC', 'Team', 'both_mq', 'team_vs_team', 'Team', 1,
                'fa-users', '#22c55e', 0.35, 0.25, 0.20, 0.10, 0.05, 0.05,
                'Best collective team throughput, balanced workload, and highest aggregate team points.',
                'Highest team average points, zero backlogs, and seamless collaboration between makers and checkers.',
                1, 9
            ],
            [
                'Best Team Award', 'AWD-BEST-TEAM', 'Team', 'all', 'team_vs_team', 'Team', 1,
                'fa-trophy', '#eab308', 0.30, 0.30, 0.15, 0.10, 0.05, 0.10,
                'Top-ranked team across all operational KPIs, audit consistency, and lowest error defect rate.',
                'Sustained #1 ranking on the Grand Prix racing leaderboard across multiple consecutive horizons.',
                1, 10
            ],

            // 4. Leadership Awards
            [
                'Best Team Lead', 'AWD-BEST-TL', 'Leadership', 'all', 'tl_vs_tl', 'TL', 1,
                'fa-user-tie', '#4f46e5', 0.25, 0.25, 0.20, 0.10, 0.10, 0.10,
                'Excellence in team coaching, capacity planning, SLA governance, and high team morale.',
                'Highest composite score across downline team productivity, quality adherence, and zero team attrition.',
                1, 11
            ],
            [
                'Best Manager Award', 'AWD-BEST-MGR', 'Leadership', 'all', 'manager_vs_manager', 'Manager', 1,
                'fa-user-shield', '#1e293b', 0.25, 0.25, 0.20, 0.10, 0.10, 0.10,
                'Multi-project governance, client relationship health, profit margin & resource optimization.',
                'Overall portfolio efficiency, client retention, contract expansion, and delivery governance excellence.',
                1, 12
            ],
            [
                'Operations Excellence Award', 'AWD-OPS-EXC', 'Operations', 'annual', 'individual', 'TL/Manager', 1,
                'fa-tasks', '#0284c7', 0.25, 0.25, 0.25, 0.10, 0.05, 0.10,
                'Demonstrated contribution towards operational efficiency, service quality, and process effectiveness.',
                'Measurable improvement in operational processes, workflows, or turnaround time across the organization.',
                0, 13
            ],
            [
                'Collaboration Excellence Award', 'AWD-COLLAB', 'Leadership', 'annual', 'individual', 'Any', 1,
                'fa-hands-helping', '#f97316', 0.15, 0.15, 0.15, 0.10, 0.15, 0.30,
                'Cross-functional alignment, breaking silos, and enabling collective organizational success.',
                'Effective coordination across business units, unblocking inter-team dependencies, and positive culture impact.',
                0, 14
            ],

            // 5. Strategic & Enterprise Honors (Datamatics AAA Corporate Tier)
            [
                'Enterprise Impact Award', 'AWD-ENT-IMP', 'Strategic', 'annual', 'individual', 'Senior Management', 1,
                'fa-globe', '#0ea5e9', 0.25, 0.20, 0.15, 0.05, 0.20, 0.15,
                'Exceptional contribution towards enterprise growth, profitability, scale, or strategic advancement.',
                'Significant measurable business impact, new revenue streams, or major strategic alignment with company goals.',
                0, 15
            ],
            [
                'Business Transformation Award', 'AWD-BIZ-TRANS', 'Strategic', 'annual', 'individual', 'Any', 1,
                'fa-sync-alt', '#a855f7', 0.20, 0.20, 0.15, 0.05, 0.25, 0.15,
                'Leading transformational initiatives resulting in meaningful organizational change and sustainable impact.',
                'Successful execution of digital transformation, operating model overhaul, or major systems modernization.',
                0, 16
            ],
            [
                'Performance Excellence Award', 'AWD-PERF-EXC', 'Performance', 'annual', 'individual', 'Employee', 1,
                'fa-medal', '#ef4444', 0.30, 0.30, 0.15, 0.10, 0.05, 0.10,
                'Consistent top-tier operational performance, dependability, and professional maturity across 12 months.',
                'Sustained Tier 3 scorecard rating across all months of the assessment year with zero disciplinary notes.',
                1, 17
            ],
            [
                'Employee of the Year', 'AWD-EMP-YEAR', 'Performance', 'annual', 'individual', 'Employee', 1,
                'fa-trophy', '#d97706', 0.30, 0.25, 0.15, 0.10, 0.10, 0.10,
                'The highest operational accolade for an individual associate, combining peerless work ethic and quality.',
                'Selected from the cohort of quarterly winners and top annual performers based on holistic excellence.',
                1, 18
            ],
            [
                'CEO Impact Award', 'AWD-CEO-IMP', 'Executive', 'annual', 'individual', 'Any', 1,
                'fa-crown', '#d97706', 0.20, 0.20, 0.15, 0.05, 0.20, 0.20,
                'Conferred at the sole discretion of the CEO for extraordinary contribution aligning with strategic vision.',
                'High-impact outcomes that strengthen organizational positioning, future readiness, and market prestige.',
                0, 19
            ],
            [
                'Datamatics Laureate Award', 'AWD-LAUREATE', 'Lifetime', 'annual', 'individual', 'Senior Management', 1,
                'fa-gem', '#7c3aed', 0.20, 0.20, 0.15, 0.05, 0.20, 0.20,
                'Organization highest lifetime recognition for multi-year legacy leadership, integrity, and stewardship.',
                'Exemplary conduct, industry respect, and sustained multi-year commitment towards Datamatics vision.',
                0, 20
            ],
            [
                'Long Service Award', 'AWD-LONG-SERV', 'Service', 'milestone', 'milestone', 'Any', 99,
                'fa-hourglass-half', '#64748b', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00,
                'Recognizes employees completing milestone years of service: 5, 10, 15, 20, 25, 30+ years.',
                'Loyalty, dedication, and sustained contribution towards organizational continuity and culture.',
                1, 21
            ]
        ];

        $stmt = $this->db->prepare("
            INSERT INTO `awards_master` (
                `award_name`, `award_code`, `category`, `periodicity`, `competition_type`, `eligible_roles`, `max_winners`,
                `badge_icon`, `badge_color`, `weight_productivity`, `weight_quality`, `weight_sla`, `weight_attendance`, `weight_innovation`, `weight_teamwork`,
                `description`, `criteria`, `is_automated`, `display_order`, `status`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')
            ON DUPLICATE KEY UPDATE 
                `category` = VALUES(`category`),
                `periodicity` = VALUES(`periodicity`),
                `description` = VALUES(`description`),
                `criteria` = VALUES(`criteria`),
                `display_order` = VALUES(`display_order`)
        ");

        foreach ($awards as $a) {
            $stmt->execute($a);
        }
    }

    /**
     * Get All Awards Catalog
     */
    public function getAllAwards(?string $category = null, ?string $periodicity = null): array {
        $sql = "SELECT * FROM awards_master WHERE status = 'Active'";
        $params = [];

        if ($category && $category !== 'all') {
            $sql .= " AND category = ?";
            $params[] = $category;
        }

        if ($periodicity && $periodicity !== 'all') {
            if ($periodicity === 'monthly') {
                $sql .= " AND (periodicity = 'monthly' OR periodicity = 'both_mq' OR periodicity = 'all')";
            } elseif ($periodicity === 'quarterly') {
                $sql .= " AND (periodicity = 'quarterly' OR periodicity = 'both_mq' OR periodicity = 'all')";
            } elseif ($periodicity === 'annual') {
                $sql .= " AND (periodicity = 'annual' OR periodicity = 'all')";
            } elseif ($periodicity === 'milestone') {
                $sql .= " AND periodicity = 'milestone'";
            }
        }

        $sql .= " ORDER BY display_order ASC, id ASC";
        return $this->fetchAll($sql, $params);
    }

    /**
     * Get Award by ID
     */
    public function getAwardById(int $id): ?array {
        return $this->fetchOne("SELECT * FROM awards_master WHERE id = ? LIMIT 1", [$id]);
    }

    /**
     * Get Award Winners by Period and Category
     */
    public function getWinners(string $periodType = 'monthly', ?string $periodKey = null, ?int $awardId = null): array {
        $sql = "
            SELECT w.*, a.award_name, a.award_code, a.category, a.badge_icon, a.badge_color,
                   u.user_code, u.department, u.email as user_email,
                   p.project_name, p.code as project_code
            FROM award_winners w
            JOIN awards_master a ON w.award_id = a.id
            LEFT JOIN users u ON w.user_id = u.id
            LEFT JOIN projects p ON w.project_id = p.id
            WHERE 1=1
        ";
        $params = [];

        if ($periodType && $periodType !== 'all') {
            $sql .= " AND w.period_type = ?";
            $params[] = $periodType;
        }

        if ($periodKey) {
            $sql .= " AND w.period_key = ?";
            $params[] = $periodKey;
        }

        if ($awardId) {
            $sql .= " AND w.award_id = ?";
            $params[] = $awardId;
        }

        $sql .= " ORDER BY w.period_key DESC, a.display_order ASC, w.rank_position ASC, w.score DESC";
        return $this->fetchAll($sql, $params);
    }

    /**
     * Get Distinct Periods Available for Filtering
     */
    public function getDistinctPeriods(): array {
        $rows = $this->fetchAll("
            SELECT DISTINCT period_type, period_key 
            FROM award_winners 
            ORDER BY period_key DESC
        ");
        $periods = [
            'monthly' => [],
            'quarterly' => [],
            'annual' => [],
            'milestone' => []
        ];
        foreach ($rows as $r) {
            $periods[$r['period_type']][] = $r['period_key'];
        }
        return $periods;
    }

    /**
     * Get Employee Awards with Excel Badge Priorities
     * Priority 1: Annual, Priority 2: Quarterly, Priority 3: Monthly, Priority 4: Spot
     */
    public function getEmployeeAwards(int $userId): array {
        return $this->fetchAll("
            SELECT w.*, a.award_name, a.award_code, a.category, a.badge_icon, a.badge_color,
                   CASE 
                       WHEN w.period_type = 'annual' THEN 1
                       WHEN w.period_type = 'quarterly' THEN 2
                       WHEN a.award_code = 'AWD-SPOT' THEN 4
                       WHEN w.period_type = 'monthly' THEN 3
                       ELSE 5
                   END as badge_priority
            FROM award_winners w
            JOIN awards_master a ON w.award_id = a.id
            WHERE w.user_id = ?
            ORDER BY badge_priority ASC, w.awarded_date DESC
        ", [$userId]);
    }

    /**
     * Automated Scoring Engine: Computes Top Contenders for Period
     * Uses Datamatics 6-Factor Model:
     * - Productivity: 30%
     * - Quality: 25%
     * - SLA Adherence: 15%
     * - Attendance: 10%
     * - Innovation / Ideas: 10%
     * - Team Contribution: 10%
     */
    public function evaluateAutomatedContenders(string $periodType = 'monthly', string $periodKey = '2026-10'): array {
        $users = $this->fetchAll("SELECT id, user_code, full_name, department, role_id FROM users WHERE status = 'Active' AND role_id IN (2, 3, 4)");
        $contenders = [];

        foreach ($users as $u) {
            $userId = (int)$u['id'];

            // 1. Productivity Score (Derived from production count / benchmark points)
            $prodRow = $this->fetchOne("
                SELECT SUM(processed_count) as total_units, SUM(total_points) as total_points, COUNT(DISTINCT log_date) as shift_days
                FROM daily_production_logs
                WHERE user_id = ? AND DATE_FORMAT(log_date, '%Y-%m') = ?
            ", [$userId, $periodKey]);

            $totalPts = (float)($prodRow['total_points'] ?? 0);
            $shiftDays = (int)($prodRow['shift_days'] ?? 1);
            $avgPoints = $shiftDays > 0 ? ($totalPts / $shiftDays) : 0;
            // Benchmark target: 450 points = 100%
            $prodScore = min(100.0, ($avgPoints / 450.0) * 100.0);

            // 2. Quality Score (Derived from internal & external accuracy in scorecards or errors)
            $scorecard = $this->fetchOne("
                SELECT int_accuracy_pct, ext_accuracy_pct, attendance_score, avg_points, total_points, total_score
                FROM monthly_scorecards
                WHERE user_id = ? AND `year_month` = ?
            ", [$userId, $periodKey]);

            $errCount = (int)($this->fetchOne("
                SELECT COUNT(*) as c FROM error_tracker WHERE maker_id = ? AND checking_month = ?
            ", [$userId, $periodKey])['c'] ?? 0);

            $qualityScore = 100.0 - min(30.0, $errCount * 5.0);
            if (!empty($scorecard['int_accuracy_pct'])) {
                $qualityScore = (float)$scorecard['int_accuracy_pct'];
            }

            // 3. SLA Adherence Score (Tickets resolved within TAT)
            $slaRow = $this->fetchOne("
                SELECT 
                    COUNT(*) as total_tickets,
                    SUM(CASE WHEN tat_status != 'Overdue' THEN 1 ELSE 0 END) as on_time_tickets
                FROM tickets
                WHERE allocated_to = ? AND DATE_FORMAT(received_datetime, '%Y-%m') = ?
            ", [$userId, $periodKey]);

            $totTix = (int)($slaRow['total_tickets'] ?? 0);
            $onTimeTix = (int)($slaRow['on_time_tickets'] ?? 0);
            $slaScore = $totTix > 0 ? (($onTimeTix / $totTix) * 100.0) : 95.0;

            // 4. Attendance Score
            $attScore = !empty($scorecard['attendance_score']) ? (float)$scorecard['attendance_score'] * 10.0 : 96.0;

            // 5. Innovation / AI Score (PKT score + automated scripts)
            $pktRow = $this->fetchOne("
                SELECT AVG(score_percentage) as avg_pkt
                FROM pkt_test_attempts
                WHERE user_id = ?
            ", [$userId]);
            $pktScore = (float)($pktRow['avg_pkt'] ?? 85.0);
            $innovationScore = min(100.0, ($pktScore * 0.9) + 10.0);

            // 6. Teamwork & Maker-Checker Score
            $teamworkScore = 90.0 + (int)($u['role_id'] == 4 ? 8.0 : 4.0);

            // Weighted Total: 30% Prod + 25% Qual + 15% SLA + 10% Att + 10% Innov + 10% Team
            $compositeScore = round(
                ($prodScore * 0.30) +
                ($qualityScore * 0.25) +
                ($slaScore * 0.15) +
                ($attScore * 0.10) +
                ($innovationScore * 0.10) +
                ($teamworkScore * 0.10),
                2
            );

            $contenders[] = [
                'user_id' => $userId,
                'user_code' => $u['user_code'],
                'full_name' => $u['full_name'],
                'department' => $u['department'],
                'role_id' => $u['role_id'],
                'composite_score' => $compositeScore,
                'productivity_score' => round($prodScore, 1),
                'quality_score' => round($qualityScore, 1),
                'sla_score' => round($slaScore, 1),
                'attendance_score' => round($attScore, 1),
                'innovation_score' => round($innovationScore, 1),
                'teamwork_score' => round($teamworkScore, 1)
            ];
        }

        // Sort descending by composite score
        usort($contenders, fn($a, $b) => $b['composite_score'] <=> $a['composite_score']);

        return $contenders;
    }

    /**
     * Bestow an Official Award
     */
    public function bestowAward(array $data): int {
        return $this->insert('award_winners', [
            'award_id' => (int)$data['award_id'],
            'period_type' => $data['period_type'] ?? 'monthly',
            'period_key' => $data['period_key'] ?? date('Y-m'),
            'user_id' => !empty($data['user_id']) ? (int)$data['user_id'] : null,
            'project_id' => !empty($data['project_id']) ? (int)$data['project_id'] : null,
            'recipient_name' => sanitize($data['recipient_name']),
            'recipient_role' => sanitize($data['recipient_role']),
            'score' => (float)($data['score'] ?? 0),
            'rank_position' => (int)($data['rank_position'] ?? 1),
            'citation' => trim($data['citation'] ?? ''),
            'productivity_score' => (float)($data['productivity_score'] ?? 0),
            'quality_score' => (float)($data['quality_score'] ?? 0),
            'sla_score' => (float)($data['sla_score'] ?? 0),
            'attendance_score' => (float)($data['attendance_score'] ?? 0),
            'innovation_score' => (float)($data['innovation_score'] ?? 0),
            'teamwork_score' => (float)($data['teamwork_score'] ?? 0),
            'awarded_by' => !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1,
            'awarded_date' => $data['awarded_date'] ?? date('Y-m-d'),
            'display_in_slideshow' => isset($data['display_in_slideshow']) ? (int)$data['display_in_slideshow'] : 1,
            'status' => 'Awarded',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Revoke / Delete Winner Record
     */
    public function deleteWinner(int $id): bool {
        return (bool)$this->query("DELETE FROM award_winners WHERE id = ?", [$id]);
    }

    /**
     * Get Slideshow Rotating Showcase Data
     */
    public function getSlideshowAwards(): array {
        return $this->fetchAll("
            SELECT w.*, a.award_name, a.award_code, a.category, a.badge_icon, a.badge_color,
                   u.user_code, u.department, p.project_name
            FROM award_winners w
            JOIN awards_master a ON w.award_id = a.id
            LEFT JOIN users u ON w.user_id = u.id
            LEFT JOIN projects p ON w.project_id = p.id
            WHERE w.display_in_slideshow = 1
            ORDER BY 
                CASE w.period_type 
                    WHEN 'annual' THEN 1 
                    WHEN 'quarterly' THEN 2 
                    WHEN 'monthly' THEN 3 
                    ELSE 4 
                END ASC,
                w.rank_position ASC,
                w.score DESC
            LIMIT 15
        ");
    }

    /**
     * Fully Seed Database with Rich, Realistic Scenarios Covering All Tiers
     */
    public function seedScenarioWinners(): void {
        // Clear existing award_winners
        $this->query("TRUNCATE TABLE `award_winners`");

        // Fetch awards mapped by code
        $awardMap = [];
        $rows = $this->fetchAll("SELECT id, award_code, award_name FROM awards_master");
        foreach ($rows as $r) {
            $awardMap[$r['award_code']] = (int)$r['id'];
        }

        $scenarioWinners = [
            // ================= SCENARIO 1: MONTHLY AWARDS (October 2026) =================
            [
                'award_code' => 'AWD-STAR',
                'period_type' => 'monthly',
                'period_key' => '2026-10',
                'user_id' => 5, // Varun Kumar (Maker 1)
                'project_id' => 1,
                'recipient_name' => 'Varun Kumar',
                'recipient_role' => 'Senior Banking Maker',
                'score' => 95.80,
                'rank_position' => 1,
                'productivity_score' => 98.5,
                'quality_score' => 99.4,
                'sla_score' => 100.0,
                'attendance_score' => 100.0,
                'innovation_score' => 88.0,
                'teamwork_score' => 92.0,
                'citation' => 'Ranked #1 across Banking Operations with an average throughput of 480 points/shift, zero NACH return re-clearing errors, and flawless attendance.'
            ],
            [
                'award_code' => 'AWD-STAR',
                'period_type' => 'monthly',
                'period_key' => '2026-10',
                'user_id' => 6, // Sneha Patel (Maker 2)
                'project_id' => 1,
                'recipient_name' => 'Sneha Patel',
                'recipient_role' => 'Banking Operations Maker',
                'score' => 92.40,
                'rank_position' => 2,
                'productivity_score' => 95.0,
                'quality_score' => 98.2,
                'sla_score' => 98.0,
                'attendance_score' => 96.0,
                'innovation_score' => 85.0,
                'teamwork_score' => 90.0,
                'citation' => 'Exceptional performance in clearing high-priority corporate CMS batches and maintaining an outstanding 98.2% internal accuracy rating.'
            ],
            [
                'award_code' => 'AWD-SPOT',
                'period_type' => 'monthly',
                'period_key' => '2026-10',
                'user_id' => 5, // Varun Kumar
                'project_id' => 1,
                'recipient_name' => 'Varun Kumar',
                'recipient_role' => 'Senior Banking Maker',
                'score' => 98.00,
                'rank_position' => 1,
                'productivity_score' => 100.0,
                'quality_score' => 100.0,
                'sla_score' => 100.0,
                'attendance_score' => 100.0,
                'innovation_score' => 95.0,
                'teamwork_score' => 95.0,
                'citation' => 'Conferred on-the-spot recognition for resolving 45 critical Escrow account clearing tickets within 3 hours during weekend month-end spike.'
            ],
            [
                'award_code' => 'AWD-SPOT',
                'period_type' => 'monthly',
                'period_key' => '2026-10',
                'user_id' => 6, // Sneha Patel
                'project_id' => 1,
                'recipient_name' => 'Sneha Patel',
                'recipient_role' => 'Banking Operations Maker',
                'score' => 94.00,
                'rank_position' => 2,
                'productivity_score' => 92.0,
                'quality_score' => 98.0,
                'sla_score' => 100.0,
                'attendance_score' => 96.0,
                'innovation_score' => 90.0,
                'teamwork_score' => 94.0,
                'citation' => 'Proactively identified a format discrepancy in incoming client dump files, preventing a multi-branch clearing mismatch.'
            ],
            [
                'award_code' => 'AWD-QUAL',
                'period_type' => 'monthly',
                'period_key' => '2026-10',
                'user_id' => 4, // Anand Verma (Checker)
                'project_id' => 1,
                'recipient_name' => 'Anand Verma',
                'recipient_role' => 'Lead Quality Checker',
                'score' => 99.20,
                'rank_position' => 1,
                'productivity_score' => 92.0,
                'quality_score' => 99.8,
                'sla_score' => 100.0,
                'attendance_score' => 100.0,
                'innovation_score' => 90.0,
                'teamwork_score' => 98.0,
                'citation' => 'Zero external audit defects logged, audited over 650 maker submissions with 99.8% precision and assisted makers in rapid rework turnarounds.'
            ],
            [
                'award_code' => 'AWD-PROD',
                'period_type' => 'monthly',
                'period_key' => '2026-10',
                'user_id' => 7, // Karthik Raja (AP Maker)
                'project_id' => 3,
                'recipient_name' => 'Karthik Raja',
                'recipient_role' => 'AP & Invoicing Specialist',
                'score' => 96.50,
                'rank_position' => 1,
                'productivity_score' => 100.0,
                'quality_score' => 98.0,
                'sla_score' => 96.0,
                'attendance_score' => 100.0,
                'innovation_score' => 92.0,
                'teamwork_score' => 90.0,
                'citation' => 'Processed 1,280 vendor tax invoices in October, reaching 132% of monthly target volume with automated 3-way line item validation.'
            ],
            [
                'award_code' => 'AWD-CUST',
                'period_type' => 'monthly',
                'period_key' => '2026-10',
                'user_id' => 8, // Divya Nair
                'project_id' => 3,
                'recipient_name' => 'Divya Nair',
                'recipient_role' => 'Process Associate',
                'score' => 94.80,
                'rank_position' => 1,
                'productivity_score' => 91.0,
                'quality_score' => 97.5,
                'sla_score' => 100.0,
                'attendance_score' => 100.0,
                'innovation_score' => 88.0,
                'teamwork_score' => 96.0,
                'citation' => 'Zero SLA breaches across all 180 customer inquiries and earned a 5-star customer praise letter from Axalta EMEA operations.'
            ],
            [
                'award_code' => 'AWD-TEAM-EXC',
                'period_type' => 'monthly',
                'period_key' => '2026-10',
                'user_id' => null,
                'project_id' => 1, // IDFC Banking Operations
                'recipient_name' => 'IDFC Banking Operations Team',
                'recipient_role' => 'Operations Unit',
                'score' => 97.10,
                'rank_position' => 1,
                'productivity_score' => 98.0,
                'quality_score' => 99.0,
                'sla_score' => 98.5,
                'attendance_score' => 98.0,
                'innovation_score' => 90.0,
                'teamwork_score' => 100.0,
                'citation' => 'Demonstrated peerless cross-shift collaboration, completing the entire monthly clearing pipeline with 99.0% quality and zero backlogs.'
            ],

            // ================= SCENARIO 2: QUARTERLY AWARDS (Q3 2026) =================
            [
                'award_code' => 'AWD-STAR',
                'period_type' => 'quarterly',
                'period_key' => '2026-Q3',
                'user_id' => 5, // Varun Kumar
                'project_id' => 1,
                'recipient_name' => 'Varun Kumar',
                'recipient_role' => 'Senior Banking Maker',
                'score' => 96.20,
                'rank_position' => 1,
                'productivity_score' => 99.0,
                'quality_score' => 99.2,
                'sla_score' => 99.5,
                'attendance_score' => 100.0,
                'innovation_score' => 90.0,
                'teamwork_score' => 94.0,
                'citation' => 'Quarterly Star Performer for sustaining the highest composite score across Q3 with over 3,500 tickets processed and 99.2% accuracy.'
            ],
            [
                'award_code' => 'AWD-QUAL',
                'period_type' => 'quarterly',
                'period_key' => '2026-Q3',
                'user_id' => 4, // Anand Verma
                'project_id' => 1,
                'recipient_name' => 'Anand Verma',
                'recipient_role' => 'Lead Quality Checker',
                'score' => 99.40,
                'rank_position' => 1,
                'productivity_score' => 93.0,
                'quality_score' => 99.9,
                'sla_score' => 100.0,
                'attendance_score' => 100.0,
                'innovation_score' => 92.0,
                'teamwork_score' => 98.0,
                'citation' => 'Quarterly Quality Champion: Led quality calibration sessions across teams, reducing rework loops by 38% throughout Q3.'
            ],
            [
                'award_code' => 'AWD-RISE',
                'period_type' => 'quarterly',
                'period_key' => '2026-Q3',
                'user_id' => 8, // Divya Nair (<2 yrs)
                'project_id' => 3,
                'recipient_name' => 'Divya Nair',
                'recipient_role' => 'Process Associate',
                'score' => 93.50,
                'rank_position' => 1,
                'productivity_score' => 90.0,
                'quality_score' => 98.0,
                'sla_score' => 98.0,
                'attendance_score' => 100.0,
                'innovation_score' => 95.0,
                'teamwork_score' => 92.0,
                'citation' => 'Rising Star Award: Joined in 2025, achieved 98% in PKT assessment within 6 months, and mastered both AP and NACH workflows ahead of schedule.'
            ],
            [
                'award_code' => 'AWD-AI',
                'period_type' => 'quarterly',
                'period_key' => '2026-Q3',
                'user_id' => 7, // Karthik Raja
                'project_id' => 3,
                'recipient_name' => 'Karthik Raja',
                'recipient_role' => 'AP & Invoicing Specialist',
                'score' => 95.00,
                'rank_position' => 1,
                'productivity_score' => 94.0,
                'quality_score' => 97.0,
                'sla_score' => 95.0,
                'attendance_score' => 100.0,
                'innovation_score' => 100.0,
                'teamwork_score' => 92.0,
                'citation' => 'AI Champion Award: Built an automated PDF parsing script that pre-extracts invoice line items, saving 120 hours of manual data entry per month.'
            ],
            [
                'award_code' => 'AWD-BEST-TL',
                'period_type' => 'quarterly',
                'period_key' => '2026-Q3',
                'user_id' => 3, // Priya Sundaram (TL)
                'project_id' => 1,
                'recipient_name' => 'Priya Sundaram',
                'recipient_role' => 'Team Leader • Banking Operations',
                'score' => 97.50,
                'rank_position' => 1,
                'productivity_score' => 98.0,
                'quality_score' => 99.0,
                'sla_score' => 99.0,
                'attendance_score' => 100.0,
                'innovation_score' => 94.0,
                'teamwork_score' => 98.0,
                'citation' => 'Best Team Lead: Steered the Banking Operations team to achieve 99.8% SLA adherence with zero attrition and 100% staff certification.'
            ],
            [
                'award_code' => 'AWD-BEST-MGR',
                'period_type' => 'quarterly',
                'period_key' => '2026-Q3',
                'user_id' => 2, // Rajesh Sharma (Operations Manager)
                'project_id' => 1,
                'recipient_name' => 'Rajesh Sharma',
                'recipient_role' => 'Operations Manager',
                'score' => 96.80,
                'rank_position' => 1,
                'productivity_score' => 97.0,
                'quality_score' => 98.5,
                'sla_score' => 98.0,
                'attendance_score' => 100.0,
                'innovation_score' => 95.0,
                'teamwork_score' => 96.0,
                'citation' => 'Best Manager Award: Successfully integrated hybrid workflows across 5 active client projects while optimizing floor capacity utilization by 22%.'
            ],
            [
                'award_code' => 'AWD-BEST-TEAM',
                'period_type' => 'quarterly',
                'period_key' => '2026-Q3',
                'user_id' => null,
                'project_id' => 2, // HDFC Card Services
                'recipient_name' => 'HDFC Card Services Team',
                'recipient_role' => 'Operations Unit',
                'score' => 96.40,
                'rank_position' => 1,
                'productivity_score' => 96.0,
                'quality_score' => 98.0,
                'sla_score' => 99.0,
                'attendance_score' => 98.0,
                'innovation_score' => 92.0,
                'teamwork_score' => 98.0,
                'citation' => 'Best Team Award (Q3): Maintained #1 position in dispute turnaround times and earned the highest quarterly client satisfaction rating.'
            ],

            // ================= SCENARIO 3: ANNUAL PRESTIGIOUS HONOURS (FY 2026) =================
            [
                'award_code' => 'AWD-EMP-YEAR',
                'period_type' => 'annual',
                'period_key' => '2026-FY',
                'user_id' => 5, // Varun Kumar
                'project_id' => 1,
                'recipient_name' => 'Varun Kumar',
                'recipient_role' => 'Senior Banking Maker',
                'score' => 97.80,
                'rank_position' => 1,
                'productivity_score' => 99.0,
                'quality_score' => 99.5,
                'sla_score' => 100.0,
                'attendance_score' => 100.0,
                'innovation_score' => 92.0,
                'teamwork_score' => 96.0,
                'citation' => 'Employee of the Year (FY 2026): Peerless operational benchmark with over 14,000 processed queries, 100% on-time turnaround, and mentoring 4 new joiners.'
            ],
            [
                'award_code' => 'AWD-OPS-EXC',
                'period_type' => 'annual',
                'period_key' => '2026-FY',
                'user_id' => 3, // Priya Sundaram
                'project_id' => 1,
                'recipient_name' => 'Priya Sundaram',
                'recipient_role' => 'Team Leader • Banking Operations',
                'score' => 98.20,
                'rank_position' => 1,
                'productivity_score' => 98.5,
                'quality_score' => 99.5,
                'sla_score' => 99.0,
                'attendance_score' => 100.0,
                'innovation_score' => 96.0,
                'teamwork_score' => 99.0,
                'citation' => 'Operations Excellence Award: Championed zero-defect quality initiatives across the banking division, earning recognition during internal audit cycles.'
            ],
            [
                'award_code' => 'AWD-LAUREATE',
                'period_type' => 'annual',
                'period_key' => '2026-FY',
                'user_id' => 9, // Vikram Malhotra (Senior Management)
                'project_id' => null,
                'recipient_name' => 'Vikram Malhotra',
                'recipient_role' => 'Senior Vice President • Operations',
                'score' => 99.50,
                'rank_position' => 1,
                'productivity_score' => 98.0,
                'quality_score' => 100.0,
                'sla_score' => 100.0,
                'attendance_score' => 100.0,
                'innovation_score' => 100.0,
                'teamwork_score' => 100.0,
                'citation' => 'Datamatics Laureate Award: Organization highest honor for multi-year legacy leadership, driving operational governance and enterprise digital transformation.'
            ],
            [
                'award_code' => 'AWD-CEO-IMP',
                'period_type' => 'annual',
                'period_key' => '2026-FY',
                'user_id' => 2, // Rajesh Sharma
                'project_id' => 1,
                'recipient_name' => 'Rajesh Sharma',
                'recipient_role' => 'Operations Manager',
                'score' => 98.00,
                'rank_position' => 1,
                'productivity_score' => 98.0,
                'quality_score' => 99.0,
                'sla_score' => 99.0,
                'attendance_score' => 100.0,
                'innovation_score' => 98.0,
                'teamwork_score' => 97.0,
                'citation' => 'CEO Impact Award: Conferred at the sole discretion of the CEO for visionary execution in scaling the shared services model and driving AI adoption.'
            ],
            [
                'award_code' => 'AWD-AUTO',
                'period_type' => 'annual',
                'period_key' => '2026-FY',
                'user_id' => null,
                'project_id' => 3, // AP & Invoicing Team
                'recipient_name' => 'AP & Invoicing Automation Team',
                'recipient_role' => 'Cross-Functional Unit',
                'score' => 96.50,
                'rank_position' => 1,
                'productivity_score' => 98.0,
                'quality_score' => 98.0,
                'sla_score' => 97.0,
                'attendance_score' => 98.0,
                'innovation_score' => 100.0,
                'teamwork_score' => 95.0,
                'citation' => 'Automation Excellence Award: Developed end-to-end optical character recognition (OCR) and automated 3-way line item match for high-volume vendor billing.'
            ],

            // ================= SCENARIO 4: LONG SERVICE AWARDS (Milestones) =================
            [
                'award_code' => 'AWD-LONG-SERV',
                'period_type' => 'milestone',
                'period_key' => '10-Years',
                'user_id' => 2, // Rajesh Sharma
                'project_id' => null,
                'recipient_name' => 'Rajesh Sharma',
                'recipient_role' => 'Operations Manager',
                'score' => 100.00,
                'rank_position' => 1,
                'productivity_score' => 100.0,
                'quality_score' => 100.0,
                'sla_score' => 100.0,
                'attendance_score' => 100.0,
                'innovation_score' => 100.0,
                'teamwork_score' => 100.0,
                'citation' => 'Decade of Dedication: 10 Years of continuous service, exemplary leadership, and steadfast commitment to organizational values (2016–2026).'
            ],
            [
                'award_code' => 'AWD-LONG-SERV',
                'period_type' => 'milestone',
                'period_key' => '5-Years',
                'user_id' => 3, // Priya Sundaram
                'project_id' => null,
                'recipient_name' => 'Priya Sundaram',
                'recipient_role' => 'Team Leader • Banking Operations',
                'score' => 100.00,
                'rank_position' => 1,
                'productivity_score' => 100.0,
                'quality_score' => 100.0,
                'sla_score' => 100.0,
                'attendance_score' => 100.0,
                'innovation_score' => 100.0,
                'teamwork_score' => 100.0,
                'citation' => '5 Years Service Milestone: Recognized during corporate celebrations for 5 years of continuous dedication and mentoring high-performance teams.'
            ],
            [
                'award_code' => 'AWD-LONG-SERV',
                'period_type' => 'milestone',
                'period_key' => '5-Years',
                'user_id' => 4, // Anand Verma
                'project_id' => null,
                'recipient_name' => 'Anand Verma',
                'recipient_role' => 'Lead Quality Checker',
                'score' => 100.00,
                'rank_position' => 2,
                'productivity_score' => 100.0,
                'quality_score' => 100.0,
                'sla_score' => 100.0,
                'attendance_score' => 100.0,
                'innovation_score' => 100.0,
                'teamwork_score' => 100.0,
                'citation' => '5 Years Service Milestone: Acknowledged for 5 years of outstanding quality stewardship and zero compliance lapses across banking operations.'
            ]
        ];

        $stmt = $this->db->prepare("
            INSERT INTO `award_winners` (
                `award_id`, `period_type`, `period_key`, `user_id`, `project_id`, `recipient_name`, `recipient_role`,
                `score`, `rank_position`, `citation`, `productivity_score`, `quality_score`, `sla_score`, `attendance_score`,
                `innovation_score`, `teamwork_score`, `awarded_by`, `awarded_date`, `display_in_slideshow`, `status`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, CURDATE(), 1, 'Awarded')
        ");

        foreach ($scenarioWinners as $sw) {
            $awardId = $awardMap[$sw['award_code']] ?? null;
            if (!$awardId) continue;

            $stmt->execute([
                $awardId,
                $sw['period_type'],
                $sw['period_key'],
                $sw['user_id'],
                $sw['project_id'],
                $sw['recipient_name'],
                $sw['recipient_role'],
                $sw['score'],
                $sw['rank_position'],
                $sw['citation'],
                $sw['productivity_score'],
                $sw['quality_score'],
                $sw['sla_score'],
                $sw['attendance_score'],
                $sw['innovation_score'],
                $sw['teamwork_score']
            ]);
        }
    }
}
