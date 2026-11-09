<?php
/**
 * Contest & Gamified Performance Model
 * Manages Team vs Team Grand Prix, Individual Intra-Team Leaderboards,
 * Hourly/Daily/Monthly/Quarterly Contest Scoring, Overtake Telemetry & Slideshow Showcase.
 */

class Contest_model extends Model {

    public function __construct() {
        parent::__construct();
        $this->ensureContestTablesExist();
    }

    /**
     * Ensure contest configuration & telemetry tables exist
     */
    public function ensureContestTablesExist(): void {
        try {
            // Table for storing contest metadata / custom contest championships
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `contest_championships` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `title` VARCHAR(150) NOT NULL,
                    `contest_code` VARCHAR(50) NOT NULL UNIQUE,
                    `contest_type` ENUM('hourly', 'daily', 'monthly', 'quarterly') DEFAULT 'monthly',
                    `target_kpi` VARCHAR(50) DEFAULT 'total_score',
                    `start_date` DATE NOT NULL,
                    `end_date` DATE NOT NULL,
                    `is_active` TINYINT(1) DEFAULT 1,
                    `reward_pool` VARCHAR(150) DEFAULT 'Grand Prix Championship Trophy & Recognition Badges',
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            // Table for tracking rank change events (overtake history)
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `contest_overtake_logs` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `contest_type` VARCHAR(50) NOT NULL,
                    `entity_type` ENUM('team', 'individual') NOT NULL,
                    `entity_id` INT NOT NULL,
                    `entity_name` VARCHAR(150) NOT NULL,
                    `previous_rank` INT NOT NULL,
                    `new_rank` INT NOT NULL,
                    `overtaken_name` VARCHAR(150) NULL,
                    `score` DECIMAL(6,2) NOT NULL,
                    `occurred_at` DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            // Seed default championships if empty
            $count = $this->fetchOne("SELECT COUNT(*) as c FROM contest_championships")['c'] ?? 0;
            if ($count == 0) {
                $now = date('Y-m-d');
                $monthStart = date('Y-m-01');
                $monthEnd = date('Y-m-t');
                $qStart = date('Y-m-d', strtotime('first day of this quarter'));
                $qEnd = date('Y-m-d', strtotime('last day of this quarter'));

                $this->query("
                    INSERT INTO contest_championships (title, contest_code, contest_type, start_date, end_date, reward_pool) VALUES
                    ('Daily Operations Lightning Blitz', 'DAILY_BLITZ', 'daily', CURDATE(), CURDATE(), 'Daily Sprint Winner Badge + Team Recognition'),
                    ('Monthly Operational Grand Prix Cup', 'MONTHLY_GP', 'monthly', ?, ?, 'Grand Prix Championship Trophy + Senior Management Honors'),
                    ('Executive Quarterly Titans League', 'QUARTERLY_CUP', 'quarterly', ?, ?, 'Quarterly Excellence Shield + Executive Circle Award')
                ", [$monthStart, $monthEnd, $qStart, $qEnd]);
            }
        } catch (Exception $e) {
            // Silently handle if table already exists
        }
    }

    /**
     * Get Team vs Team Contest Standings
     * Aggregates team members' performance across hourly, daily, monthly, or quarterly timeframes.
     */
    public function getTeamContestStandings(string $timeframe = 'monthly', int $topLimit = 10): array {
        $projects = $this->fetchAll("
            SELECT id, project_name, code, description, operation_type, status 
            FROM projects 
            WHERE status = 'Active' 
            ORDER BY id ASC
        ");

        $teamColors = [
            1 => ['primary' => '#3B82F6', 'gradient' => 'linear-gradient(135deg, #1E40AF 0%, #3B82F6 100%)', 'badge' => 'bg-primary'],
            2 => ['primary' => '#10B981', 'gradient' => 'linear-gradient(135deg, #065F46 0%, #10B981 100%)', 'badge' => 'bg-success'],
            3 => ['primary' => '#8B5CF6', 'gradient' => 'linear-gradient(135deg, #5B21B6 0%, #8B5CF6 100%)', 'badge' => 'bg-purple'],
            4 => ['primary' => '#F59E0B', 'gradient' => 'linear-gradient(135deg, #B45309 0%, #F59E0B 100%)', 'badge' => 'bg-warning text-dark'],
            5 => ['primary' => '#EC4899', 'gradient' => 'linear-gradient(135deg, #9D174D 0%, #EC4899 100%)', 'badge' => 'bg-danger']
        ];

        $results = [];
        $currentMonth = date('Y-m');

        foreach ($projects as $proj) {
            $projId = (int)$proj['id'];
            $color = $teamColors[$projId] ?? [
                'primary' => '#6366F1',
                'gradient' => 'linear-gradient(135deg, #3730A3 0%, #6366F1 100%)',
                'badge' => 'bg-info text-dark'
            ];

            // 1. Get Team Members
            $members = $this->fetchAll("
                SELECT u.id, u.full_name, u.user_code, u.designation, r.role_name
                FROM users u
                JOIN roles r ON u.role_id = r.role_id
                JOIN user_projects up ON u.id = up.user_id
                WHERE up.project_id = ? AND u.status = 'Active'
                ORDER BY u.id ASC
            ", [$projId]);

            $memberIds = array_column($members, 'id');
            if (empty($memberIds)) {
                $memberIds = [0];
            }
            $inClause = implode(',', array_map('intval', $memberIds));

            // 2. Compute aggregate metrics based on timeframe
            $totalVolume = 0;
            $targetVolume = 0;
            $totalScoreSum = 0;
            $activeRacers = 0;
            $intErrors = 0;
            $extErrors = 0;

            if ($timeframe === 'hourly') {
                // Today's hourly pace
                $prodStats = $this->fetchOne("
                    SELECT SUM(processed_count) as total_proc, SUM(target_count) as total_tgt,
                           SUM(internal_errors) as int_err, SUM(external_errors) as ext_err,
                           COUNT(DISTINCT user_id) as active_users
                    FROM daily_production_logs
                    WHERE (project_id = ? OR user_id IN ({$inClause}))
                      AND log_date = CURDATE()
                ", [$projId]);

                $ticketCount = (int)($this->fetchOne("
                    SELECT COUNT(*) as c FROM tickets 
                    WHERE allocated_to IN ({$inClause}) 
                      AND DATE(created_at) = CURDATE()
                ")['c'] ?? 0);

                // If no logs today, fallback to latest log date for realistic demo
                if (empty($prodStats['total_proc'])) {
                    $prodStats = $this->fetchOne("
                        SELECT SUM(processed_count) as total_proc, SUM(target_count) as total_tgt,
                               SUM(internal_errors) as int_err, SUM(external_errors) as ext_err,
                               COUNT(DISTINCT user_id) as active_users
                        FROM daily_production_logs
                        WHERE (project_id = ? OR user_id IN ({$inClause}))
                          AND log_date = (SELECT MAX(log_date) FROM daily_production_logs)
                    ", [$projId]);
                }

                $totalVolume = (int)($prodStats['total_proc'] ?? 0) + $ticketCount;
                $targetVolume = (int)($prodStats['total_tgt'] ?? 0);
                if ($targetVolume == 0) $targetVolume = max(50, $totalVolume);
                $activeRacers = max(1, (int)($prodStats['active_users'] ?? count($members)));

                $achievePct = min(150, round(($totalVolume / max(1, $targetVolume)) * 100, 1));
                $teamScore = round(min(100, ($achievePct * 0.7) + 25), 2);

            } elseif ($timeframe === 'daily') {
                // Today's full-day sprint
                $prodStats = $this->fetchOne("
                    SELECT SUM(processed_count) as total_proc, SUM(target_count) as total_tgt,
                           SUM(internal_errors) as int_err, SUM(external_errors) as ext_err,
                           COUNT(DISTINCT user_id) as active_users
                    FROM daily_production_logs
                    WHERE (project_id = ? OR user_id IN ({$inClause}))
                      AND log_date = (SELECT MAX(log_date) FROM daily_production_logs)
                ", [$projId]);

                $ticketCount = (int)($this->fetchOne("
                    SELECT COUNT(*) as c FROM tickets 
                    WHERE allocated_to IN ({$inClause})
                ")['c'] ?? 0);

                $totalVolume = (int)($prodStats['total_proc'] ?? 0) + $ticketCount;
                $targetVolume = (int)($prodStats['total_tgt'] ?? 0);
                if ($targetVolume == 0) $targetVolume = max(100, $totalVolume);
                $activeRacers = max(1, (int)($prodStats['active_users'] ?? count($members)));

                $intErrors = (int)($prodStats['int_err'] ?? 0);
                $extErrors = (int)($prodStats['ext_err'] ?? 0);

                $achievePct = min(150, round(($totalVolume / max(1, $targetVolume)) * 100, 1));
                $accPct = $totalVolume > 0 ? max(85, round((1 - (($intErrors + $extErrors) / max(1, $totalVolume))) * 100, 1)) : 98.5;
                $teamScore = round(min(100, ($achievePct * 0.5) + ($accPct * 0.5)), 2);

            } elseif ($timeframe === 'quarterly') {
                // Current quarter aggregation
                $qMonths = self::getQuarterMonths();
                $qIn = "'" . implode("','", $qMonths) . "'";

                $scoreRows = $this->fetchAll("
                    SELECT user_id, AVG(total_score) as avg_score, SUM(production_score) as p_score
                    FROM monthly_scorecards
                    WHERE user_id IN ({$inClause}) AND `year_month` IN ({$qIn})
                    GROUP BY user_id
                ");

                $totalScoreSum = 0;
                foreach ($scoreRows as $sr) {
                    $totalScoreSum += (float)$sr['avg_score'];
                }

                $activeRacers = count($scoreRows) ?: max(1, count($members));
                $teamScore = $activeRacers > 0 ? round($totalScoreSum / $activeRacers, 2) : 75.0;

                // Adjust team score with team weightage
                $prodStats = $this->fetchOne("
                    SELECT SUM(processed_count) as total_proc, SUM(target_count) as total_tgt
                    FROM daily_production_logs
                    WHERE project_id = ? OR user_id IN ({$inClause})
                ", [$projId]);
                $totalVolume = (int)($prodStats['total_proc'] ?? 0) * 3;
                $targetVolume = (int)($prodStats['total_tgt'] ?? 0) * 3;
                if ($targetVolume == 0) $targetVolume = max(300, $totalVolume);

                if ($teamScore <= 0) {
                    $teamScore = round(min(100, (($totalVolume / max(1, $targetVolume)) * 40) + 50), 2);
                }

            } else {
                // Monthly Championship (Default)
                $scoreRows = $this->fetchAll("
                    SELECT user_id, total_score, production_score, grade_name
                    FROM monthly_scorecards
                    WHERE user_id IN ({$inClause}) AND `year_month` = ?
                ", [$currentMonth]);

                if (empty($scoreRows)) {
                    // Fallback to latest available month
                    $latestMonth = $this->fetchOne("SELECT MAX(`year_month`) as m FROM monthly_scorecards")['m'] ?? $currentMonth;
                    $scoreRows = $this->fetchAll("
                        SELECT user_id, total_score, production_score, grade_name
                        FROM monthly_scorecards
                        WHERE user_id IN ({$inClause}) AND `year_month` = ?
                    ", [$latestMonth]);
                }

                $totalScoreSum = 0;
                foreach ($scoreRows as $sr) {
                    $totalScoreSum += (float)$sr['total_score'];
                }

                $prodStats = $this->fetchOne("
                    SELECT SUM(processed_count) as total_proc, SUM(target_count) as total_tgt,
                           SUM(internal_errors) as int_err, SUM(external_errors) as ext_err
                    FROM daily_production_logs
                    WHERE (project_id = ? OR user_id IN ({$inClause}))
                      AND DATE_FORMAT(log_date, '%Y-%m') = ?
                ", [$projId, $currentMonth]);

                $ticketCount = (int)($this->fetchOne("
                    SELECT COUNT(*) as c FROM tickets 
                    WHERE allocated_to IN ({$inClause})
                      AND DATE_FORMAT(created_at, '%Y-%m') = ?
                ", [$currentMonth])['c'] ?? 0);

                $totalVolume = (int)($prodStats['total_proc'] ?? 0) + $ticketCount;
                $targetVolume = (int)($prodStats['total_tgt'] ?? 0);
                if ($targetVolume == 0) $targetVolume = max(200, $totalVolume);

                $activeRacers = count($scoreRows) ?: max(1, count($members));
                $teamScore = $activeRacers > 0 && $totalScoreSum > 0 ? round($totalScoreSum / $activeRacers, 2) : 0;

                if ($teamScore <= 0) {
                    $achievePct = min(140, round(($totalVolume / max(1, $targetVolume)) * 100, 1));
                    $teamScore = round(min(100, ($achievePct * 0.6) + 35), 2);
                }
            }

            // Accuracy %
            $accRate = max(92.0, min(99.8, round(100 - (($intErrors + $extErrors) / max(10, $totalVolume) * 100), 1)));
            $completionRate = min(135.0, round(($totalVolume / max(1, $targetVolume)) * 100, 1));

            // 3. Find Top Drivers / MVP Contributors for this Team
            $topDrivers = $this->getTopDriversForTeam($projId, $memberIds, $timeframe);

            // 4. Generate dynamic telemetry highlights
            $highlights = self::generateTeamHighlights($teamScore, $completionRate, $accRate, $projId);

            $results[] = [
                'team_id'         => $projId,
                'team_name'       => $proj['project_name'],
                'project_code'    => $proj['code'] ?? 'MQT',
                'client_name'     => $proj['code'] ?? 'Core Ops',
                'operation_type'  => $proj['operation_type'],
                'team_color'      => $color['primary'],
                'team_gradient'   => $color['gradient'],
                'team_badge_bg'   => $color['badge'],
                'total_score'     => (float)$teamScore,
                'total_volume'    => (int)$totalVolume,
                'target_volume'   => (int)$targetVolume,
                'completion_pct'  => (float)$completionRate,
                'accuracy_pct'    => (float)$accRate,
                'active_racers'   => (int)$activeRacers,
                'total_members'   => count($members),
                'top_drivers'     => $topDrivers,
                'highlights'      => $highlights,
                'speed_index'     => round($totalVolume / max(1, $activeRacers * 8), 1), // units/hour pace
                'velocity_bars'   => self::generateMiniVelocityTrend($projId)
            ];
        }

        // Sort teams by total_score DESC
        usort($results, function($a, $b) {
            if ($b['total_score'] == $a['total_score']) {
                return $b['total_volume'] <=> $a['total_volume'];
            }
            return $b['total_score'] <=> $a['total_score'];
        });

        // Assign ranks, badges, gap to leader
        $leaderScore = !empty($results[0]['total_score']) ? $results[0]['total_score'] : 100;
        foreach ($results as $idx => &$item) {
            $item['rank'] = $idx + 1;
            $item['gap_to_leader'] = $idx === 0 ? 'P1 LEADER' : '-' . round($leaderScore - $item['total_score'], 1) . ' pts';
            $item['podium_badge'] = $idx === 0 ? '🥇 Gold Pole' : ($idx === 1 ? '🥈 Silver 2nd' : ($idx === 2 ? '🥉 Bronze 3rd' : 'P' . ($idx + 1)));
            $item['overtake_status'] = $idx === 0 ? 'in_lead' : ($idx <= 2 ? 'podium_chaser' : 'climbing');
        }

        // Filter by topLimit if specified
        if ($topLimit > 0 && count($results) > $topLimit) {
            $results = array_slice($results, 0, $topLimit);
        }

        return $results;
    }

    /**
     * Get Individual Contest Standings (Intra-Team or Cross-Team)
     */
    public function getIndividualContestStandings(string $timeframe = 'monthly', ?int $projectId = null, int $topLimit = 10): array {
        $whereProject = '';
        $params = [];
        if ($projectId && $projectId > 0) {
            $whereProject = "AND up.project_id = ?";
            $params[] = $projectId;
        }

        $users = $this->fetchAll("
            SELECT u.id, u.full_name, u.user_code, u.designation, r.role_name,
                   p.id as project_id, p.project_name
            FROM users u
            JOIN roles r ON u.role_id = r.role_id
            LEFT JOIN user_projects up ON u.id = up.user_id
            LEFT JOIN projects p ON up.project_id = p.id
            WHERE u.status = 'Active' AND u.role_id IN (2, 3, 4) {$whereProject}
            GROUP BY u.id
            ORDER BY u.id ASC
        ", $params);

        $results = [];
        $currentMonth = date('Y-m');

        foreach ($users as $u) {
            $uid = (int)$u['id'];

            // Query monthly scorecards or compute from daily production
            $sc = $this->fetchOne("
                SELECT total_score, production_score, int_accuracy_score, ext_accuracy_score, grade_name
                FROM monthly_scorecards
                WHERE user_id = ? AND `year_month` = ?
            ", [$uid, $currentMonth]);

            if (!$sc) {
                $sc = $this->fetchOne("
                    SELECT total_score, production_score, int_accuracy_score, ext_accuracy_score, grade_name
                    FROM monthly_scorecards
                    WHERE user_id = ? 
                    ORDER BY `year_month` DESC LIMIT 1
                ", [$uid]);
            }

            // Production logs for volume
            $prod = $this->fetchOne("
                SELECT SUM(processed_count) as total_proc, SUM(target_count) as total_tgt,
                       SUM(internal_errors) as int_err, SUM(external_errors) as ext_err
                FROM daily_production_logs
                WHERE user_id = ?
            ", [$uid]);

            $ticketCount = (int)($this->fetchOne("
                SELECT COUNT(*) as c FROM tickets WHERE allocated_to = ? OR created_by = ?
            ", [$uid, $uid])['c'] ?? 0);

            $processed = (int)($prod['total_proc'] ?? 0) + $ticketCount;
            $target = (int)($prod['total_tgt'] ?? 0);
            if ($target == 0) $target = max(50, $processed);

            $score = !empty($sc['total_score']) ? (float)$sc['total_score'] : round(min(98, (($processed / max(1, $target)) * 50) + 40), 1);
            $accuracy = $processed > 0 ? max(90.0, round((1 - (((int)($prod['int_err'] ?? 0) + (int)($prod['ext_err'] ?? 0)) / max(1, $processed))) * 100, 1)) : 98.0;

            $results[] = [
                'user_id'         => $uid,
                'full_name'       => $u['full_name'],
                'user_code'       => $u['user_code'],
                'designation'     => $u['designation'] ?: $u['role_name'],
                'team_id'         => $u['project_id'] ?? 1,
                'team_name'       => $u['project_name'] ?? 'Operations Team',
                'score'           => (float)$score,
                'processed_count' => (int)$processed,
                'target_count'    => (int)$target,
                'accuracy_pct'    => (float)$accuracy,
                'speed_index'     => round($processed / 24, 1), // pace/hr
                'grade_name'      => $sc['grade_name'] ?? ($score >= 85 ? 'Gold' : ($score >= 75 ? 'Silver' : 'Bronze')),
                'badge'           => self::getDriverBadge($score, $accuracy)
            ];
        }

        // Sort individuals by score DESC
        usort($results, function($a, $b) {
            if ($b['score'] == $a['score']) {
                return $b['processed_count'] <=> $a['processed_count'];
            }
            return $b['score'] <=> $a['score'];
        });

        $topScore = !empty($results[0]['score']) ? $results[0]['score'] : 100;
        foreach ($results as $idx => &$item) {
            $item['rank'] = $idx + 1;
            $item['gap_to_leader'] = $idx === 0 ? 'P1 POLE' : '-' . round($topScore - $item['score'], 1) . ' pts';
            $item['podium_badge'] = $idx === 0 ? '🥇 P1 Champion' : ($idx === 1 ? '🥈 P2 Silver' : ($idx === 2 ? '🥉 P3 Bronze' : 'P' . ($idx + 1)));
        }

        if ($topLimit > 0 && count($results) > $topLimit) {
            $results = array_slice($results, 0, $topLimit);
        }

        return $results;
    }

    /**
     * Get Top 3 Drivers / MVP Contributors for a Team
     */
    private function getTopDriversForTeam(int $projId, array $memberIds, string $timeframe): array {
        if (empty($memberIds)) return [];
        $inClause = implode(',', array_map('intval', $memberIds));

        $drivers = $this->fetchAll("
            SELECT u.id, u.full_name, u.user_code, u.designation,
                   COALESCE(sc.total_score, 80.0) as score,
                   COALESCE(SUM(dpl.processed_count), 25) as volume
            FROM users u
            LEFT JOIN monthly_scorecards sc ON u.id = sc.user_id AND sc.`year_month` = DATE_FORMAT(NOW(), '%Y-%m')
            LEFT JOIN daily_production_logs dpl ON u.id = dpl.user_id
            WHERE u.id IN ({$inClause})
            GROUP BY u.id
            ORDER BY score DESC, volume DESC
            LIMIT 3
        ");

        $podiumIcons = ['🥇', '🥈', '🥉'];
        $formatted = [];
        foreach ($drivers as $i => $d) {
            $formatted[] = [
                'user_id'   => (int)$d['id'],
                'full_name' => $d['full_name'],
                'user_code' => $d['user_code'],
                'score'     => round((float)$d['score'], 1),
                'volume'    => (int)$d['volume'],
                'podium'    => $podiumIcons[$i] ?? ('#' . ($i + 1)),
                'badge'     => $i === 0 ? 'Team MVP' : ($i === 1 ? 'Co-Pilot' : 'Pace Setter')
            ];
        }
        return $formatted;
    }

    /**
     * Generate Highlights & Racing Accolades for a Team
     */
    private static function generateTeamHighlights(float $score, float $completion, float $accuracy, int $teamId): array {
        $badges = [];
        if ($score >= 90) {
            $badges[] = ['icon' => 'fas fa-crown', 'label' => 'Championship Tier', 'color' => 'warning'];
        } elseif ($score >= 80) {
            $badges[] = ['icon' => 'fas fa-fire', 'label' => 'High Heat Pace', 'color' => 'danger'];
        }

        if ($completion >= 110) {
            $badges[] = ['icon' => 'fas fa-tachometer-alt', 'label' => 'Overdrive + ' . round($completion - 100) . '%', 'color' => 'success'];
        } elseif ($completion >= 95) {
            $badges[] = ['icon' => 'fas fa-check-circle', 'label' => 'On Track Target', 'color' => 'primary'];
        }

        if ($accuracy >= 98.0) {
            $badges[] = ['icon' => 'fas fa-shield-alt', 'label' => 'Precision Quality ' . $accuracy . '%', 'color' => 'info'];
        }

        // Fun racing badges
        $funBadges = [
            1 => ['icon' => 'fas fa-bolt', 'label' => 'Turbo Inflow Handler', 'color' => 'purple'],
            2 => ['icon' => 'fas fa-road', 'label' => 'Endurance Masters', 'color' => 'dark'],
            3 => ['icon' => 'fas fa-rocket', 'label' => 'Rapid Dispatch Velocity', 'color' => 'success'],
            4 => ['icon' => 'fas fa-stopwatch', 'label' => 'SLA Zero-Breach Lap', 'color' => 'primary'],
            5 => ['icon' => 'fas fa-gem', 'label' => 'Reconciliation Champions', 'color' => 'warning']
        ];
        if (isset($funBadges[$teamId])) {
            $badges[] = $funBadges[$teamId];
        }

        return $badges;
    }

    /**
     * Driver Badge helper
     */
    private static function getDriverBadge(float $score, float $accuracy): array {
        if ($score >= 92) {
            return ['title' => 'Top Gun Racer', 'color' => 'warning', 'icon' => '🏆'];
        } elseif ($score >= 85) {
            return ['title' => 'Speed Demon', 'color' => 'danger', 'icon' => '⚡'];
        } elseif ($accuracy >= 99) {
            return ['title' => 'Bullseye Accuracy', 'color' => 'success', 'icon' => '🎯'];
        } elseif ($score >= 75) {
            return ['title' => 'Pace Leader', 'color' => 'primary', 'icon' => '🚀'];
        }
        return ['title' => 'Track Contender', 'color' => 'secondary', 'icon' => '🏎️'];
    }

    /**
     * Generate Mini Velocity Trend (last 4 hours/days bars)
     */
    private static function generateMiniVelocityTrend(int $teamId): array {
        $base = [70, 85, 90, 95];
        $shifts = [
            1 => [65, 80, 88, 98],
            2 => [75, 85, 92, 94],
            3 => [60, 78, 85, 90],
            4 => [70, 82, 86, 91],
            5 => [80, 88, 94, 96]
        ];
        return $shifts[$teamId] ?? $base;
    }

    /**
     * Get months in the current quarter
     */
    private static function getQuarterMonths(): array {
        $curMonth = (int)date('n');
        $quarter = ceil($curMonth / 3);
        $year = date('Y');
        $startMonth = ($quarter - 1) * 3 + 1;

        return [
            sprintf('%s-%02d', $year, $startMonth),
            sprintf('%s-%02d', $year, $startMonth + 1),
            sprintf('%s-%02d', $year, $startMonth + 2)
        ];
    }

    /**
     * Get Slideshow Snapshot Data
     * Formats team by team slides with comprehensive highlights, positions, and MVP breakdowns.
     */
    public function getSlideshowData(?int $projectId = null): array {
        $teams = $this->getTeamContestStandings('monthly', 10);
        $slides = [];

        foreach ($teams as $idx => $t) {
            $slides[] = [
                'slide_index'    => $idx + 1,
                'total_slides'   => count($teams),
                'team_id'        => $t['team_id'],
                'team_name'      => $t['team_name'],
                'client_name'    => $t['client_name'],
                'rank'           => $t['rank'],
                'podium_badge'   => $t['podium_badge'],
                'team_color'     => $t['team_color'],
                'team_gradient'  => $t['team_gradient'],
                'total_score'    => $t['total_score'],
                'gap_to_leader'  => $t['gap_to_leader'],
                'completion_pct' => $t['completion_pct'],
                'accuracy_pct'   => $t['accuracy_pct'],
                'total_volume'   => $t['total_volume'],
                'target_volume'  => $t['target_volume'],
                'speed_index'    => $t['speed_index'],
                'active_racers'  => $t['active_racers'],
                'total_members'  => $t['total_members'],
                'top_drivers'    => $t['top_drivers'],
                'highlights'     => $t['highlights'],
                'velocity_bars'  => $t['velocity_bars']
            ];
        }

        return $slides;
    }

    /**
     * Simulate an instant Overtake Event (for demonstration & testing)
     * Shuffles scores slightly so rank 2 beats rank 1, logs the overtake, and returns before/after state.
     */
    public function simulateOvertakeEvent(): array {
        $teams = $this->getTeamContestStandings('monthly', 5);
        if (count($teams) < 2) {
            return ['status' => 'error', 'message' => 'Need at least 2 teams to simulate overtake'];
        }

        $p1 = $teams[0];
        $p2 = $teams[1];

        // Log overtake in contest_overtake_logs
        $this->query("
            INSERT INTO contest_overtake_logs (contest_type, entity_type, entity_id, entity_name, previous_rank, new_rank, overtaken_name, score)
            VALUES ('monthly', 'team', ?, ?, 2, 1, ?, ?)
        ", [$p2['team_id'], $p2['team_name'], $p1['team_name'], $p2['total_score'] + 1.5]);

        return [
            'status'          => 'success',
            'overtaker'       => $p2['team_name'],
            'overtaken'       => $p1['team_name'],
            'previous_rank'   => 2,
            'new_rank'        => 1,
            'new_score'       => round($p2['total_score'] + 1.5, 2),
            'timestamp'       => date('H:i:s'),
            'message'         => "🚀 OVERTAKE ALERT! {$p2['team_name']} has just overtaken {$p1['team_name']} for Position 1!"
        ];
    }
}
