<div class="container-fluid px-4 py-4" id="contestDashboard">

    <!-- Grand Prix Hero Banner -->
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4 bg-dark text-white position-relative" style="background: radial-gradient(circle at top right, #1e293b 0%, #0f172a 100%);">
        <div class="position-absolute end-0 top-0 h-100 w-50 opacity-15 pointer-events-none d-none d-lg-block" style="background: repeating-linear-gradient(45deg, rgba(255,255,255,0.04), rgba(255,255,255,0.04) 15px, transparent 15px, transparent 30px);"></div>
        
        <div class="card-body p-4 p-lg-5 position-relative z-1">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-4">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill fs-8">
                            <i class="fas fa-flag-checkered me-1"></i> OPERATIONAL GRAND PRIX
                        </span>
                        <span class="badge bg-danger bg-opacity-75 text-white fw-bold px-3 py-2 rounded-pill fs-8 d-inline-flex align-items-center gap-1">
                            <span class="spinner-grow spinner-grow-sm text-light" style="width: 8px; height: 8px;" role="status"></span> LIVE TELEMETRY
                        </span>
                    </div>
                    <h2 class="fw-black mb-1 text-white text-uppercase tracking-wider">
                        Contest Championship <span class="text-warning">&amp; Racing Board</span>
                    </h2>
                    <p class="text-light text-opacity-75 fs-7 mb-0" style="max-width: 650px;">
                        Gamified performance race tracking team vs team velocity, intra-team top performers, and live operational momentum.
                    </p>
                </div>

                <!-- Right Action Center -->
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <!-- Audio Toggle -->
                    <button type="button" class="btn btn-outline-light btn-sm fw-bold px-3 py-2 rounded-pill shadow-sm" id="btnAudioToggle" onclick="toggleAudioFx()" title="Toggle Racing Sound Effects">
                        <i class="fas fa-volume-up text-warning me-1" id="audioIcon"></i> <span id="audioText">Sound: ON</span>
                    </button>

                    <!-- Simulate Overtake Test Button -->
                    <button type="button" class="btn btn-warning text-dark btn-sm fw-bold px-3 py-2 rounded-pill shadow-sm hover-scale" onclick="triggerSimulatedOvertake()" title="Simulate a live race overtake with sound and animation">
                        <i class="fas fa-bolt me-1"></i> Test Overtake
                    </button>

                    <!-- Slideshow Presentation Launcher -->
                    <a href="<?= base_url('performance/contest-slideshow' . ($selectedProjectId ? '?project_id=' . $selectedProjectId : '')) ?>" target="_blank" class="btn btn-primary btn-sm fw-bold px-3 py-2 rounded-pill shadow-sm hover-scale" title="Open Fullscreen Slideshow for Floor TVs / Executive Presentation">
                        <i class="fas fa-tv me-1"></i> TV Slideshow Mode
                    </a>

                    <!-- 1-Minute Live Auto Refresh Countdown Widget -->
                    <div class="bg-black bg-opacity-40 border border-light border-opacity-20 px-3 py-2 rounded-pill d-flex align-items-center gap-2" title="Auto-refreshes every 1 minute">
                        <div class="position-relative d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                            <i class="fas fa-sync-alt fa-spin text-warning fs-8" id="syncSpinner" style="display: none;"></i>
                            <i class="fas fa-stopwatch text-warning fs-8" id="stopwatchIcon"></i>
                        </div>
                        <span class="fs-8 fw-bold font-monospace text-light" id="countdownLabel">Sync in 60s</span>
                        <button type="button" class="btn btn-xs btn-link text-warning p-0 text-decoration-none" onclick="refreshTelemetry(true)" title="Force Refresh Now">
                            <i class="fas fa-redo-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Control Toolbar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3 p-lg-4">
            <div class="row g-3 align-items-center justify-content-between">
                
                <!-- Left: Scope Selector (Between Teams vs Within Team) -->
                <div class="col-xl-4 col-lg-5">
                    <label class="form-label fs-8 fw-bold text-uppercase text-muted mb-1 d-block">
                        <i class="fas fa-compass me-1 text-primary"></i>Competition Scope
                    </label>
                    <div class="btn-group w-100 shadow-2xs rounded-pill p-1 bg-light border" role="group">
                        <a href="<?= base_url("performance/contest?scope=teams&timeframe={$timeframe}&top={$top}") ?>" 
                           class="btn btn-sm rounded-pill fw-bold py-2 <?= $scope === 'teams' ? 'btn-primary shadow-sm' : 'btn-light text-muted' ?>">
                            <i class="fas fa-users-cog me-1"></i>Between Teams (Team vs Team)
                        </a>
                        <a href="<?= base_url("performance/contest?scope=individuals&timeframe={$timeframe}&top={$top}" . ($selectedProjectId ? "&project_id={$selectedProjectId}" : '')) ?>" 
                           class="btn btn-sm rounded-pill fw-bold py-2 <?= $scope === 'individuals' ? 'btn-primary shadow-sm' : 'btn-light text-muted' ?>">
                            <i class="fas fa-user-astronaut me-1"></i>Within Team (Racers)
                        </a>
                    </div>
                </div>

                <!-- Center: Time Horizon Tabs (Hourly, Daily, Monthly, Quarterly) -->
                <div class="col-xl-5 col-lg-5">
                    <label class="form-label fs-8 fw-bold text-uppercase text-muted mb-1 d-block">
                        <i class="fas fa-clock me-1 text-primary"></i>Contest Horizon / Sprint
                    </label>
                    <div class="btn-group w-100 shadow-2xs rounded-pill p-1 bg-light border" role="group">
                        <?php 
                        $horizons = [
                            'hourly'    => ['label' => 'Hourly', 'icon' => 'fas fa-bolt'],
                            'daily'     => ['label' => 'Daily Blitz', 'icon' => 'fas fa-sun'],
                            'monthly'   => ['label' => 'Monthly GP', 'icon' => 'fas fa-calendar-alt'],
                            'quarterly' => ['label' => 'Quarterly Cup', 'icon' => 'fas fa-trophy']
                        ];
                        foreach ($horizons as $hKey => $hData): 
                            $isActive = $timeframe === $hKey;
                        ?>
                            <a href="<?= base_url("performance/contest?scope={$scope}&timeframe={$hKey}&top={$top}" . ($selectedProjectId ? "&project_id={$selectedProjectId}" : '')) ?>" 
                               class="btn btn-sm rounded-pill fw-bold py-2 <?= $isActive ? 'btn-dark text-white shadow-sm' : 'btn-light text-muted' ?>">
                                <i class="<?= $hData['icon'] ?> me-1 <?= $isActive ? 'text-warning' : '' ?>"></i><?= $hData['label'] ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Right: Top Performer Cutoff Filter (Top 3, Top 5, Top 10) -->
                <div class="col-xl-3 col-lg-12 d-flex align-items-center justify-content-xl-end gap-2">
                    <div class="w-100">
                        <label class="form-label fs-8 fw-bold text-uppercase text-muted mb-1 d-block">
                            <i class="fas fa-filter me-1 text-primary"></i>Leaderboard Cutoff
                        </label>
                        <div class="btn-group w-100 shadow-2xs rounded-pill p-1 bg-light border" role="group">
                            <?php foreach ([3 => 'Top 3', 5 => 'Top 5', 10 => 'Top 10', 0 => 'All'] as $cutVal => $cutLabel): 
                                $isCutActive = $top === $cutVal;
                            ?>
                                <a href="<?= base_url("performance/contest?scope={$scope}&timeframe={$timeframe}&top={$cutVal}" . ($selectedProjectId ? "&project_id={$selectedProjectId}" : '')) ?>" 
                                   class="btn btn-sm rounded-pill fw-bold py-2 <?= $isCutActive ? 'btn-warning text-dark shadow-sm' : 'btn-light text-muted' ?>">
                                    <?= $cutLabel ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Optional Team Filter when viewing Individual Racers -->
            <?php if ($scope === 'individuals'): ?>
            <div class="row mt-3 pt-3 border-top align-items-center">
                <div class="col-md-6 d-flex align-items-center gap-2">
                    <span class="fs-8 fw-bold text-muted text-uppercase text-nowrap"><i class="fas fa-building me-1 text-primary"></i>Filter Team:</span>
                    <select class="form-select form-select-sm rounded-pill" onchange="location.href='<?= base_url("performance/contest?scope=individuals&timeframe={$timeframe}&top={$top}&project_id=") ?>' + this.value;">
                        <option value="" <?= empty($selectedProjectId) ? 'selected' : '' ?>>All Teams Combined (Cross-Team Drivers)</option>
                        <?php foreach ($allProjects as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $selectedProjectId == $p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['project_name']) ?> (<?= htmlspecialchars($p['client_name'] ?? 'Team') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 text-md-end text-muted fs-8 mt-2 mt-md-0">
                    Displaying top individual contributors ranked by weighted KPI performance score.
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Live Toast Alert for Overtake Banner -->
    <div id="overtakeAlertBanner" class="alert alert-warning border-2 border-warning shadow-lg rounded-4 p-3 mb-4 d-none align-items-center justify-content-between animate__animated animate__bounceIn" role="alert">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-warning text-dark p-2 rounded-circle fs-4" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-bolt fa-beat"></i>
            </div>
            <div>
                <strong class="d-block fs-7 text-dark text-uppercase tracking-wider" id="overtakeTitle">🚀 RACING OVERTAKE DETECTED!</strong>
                <span class="fs-8 text-secondary" id="overtakeSubtitle">Team has climbed into Position 1!</span>
            </div>
        </div>
        <button type="button" class="btn-close" onclick="document.getElementById('overtakeAlertBanner').classList.add('d-none')" aria-label="Close"></button>
    </div>

    <!-- Top 3 Podium Showcase (Displayed if at least 3 entries exist) -->
    <?php if (count($standings) >= 3): 
        $p1 = $standings[0];
        $p2 = $standings[1];
        $p3 = $standings[2];
    ?>
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-award text-warning fs-5"></i>
                <h6 class="fw-black mb-0 text-dark text-uppercase tracking-wider">Top 3 Championship Podium</h6>
            </div>
            <span class="badge bg-warning bg-opacity-20 text-dark border border-warning-subtle px-3 py-1 rounded-pill fs-8 fw-bold">
                <?= ucfirst($timeframe) ?> Stage Leaders
            </span>
        </div>

        <div class="card-body p-4">
            <div class="row g-4 align-items-end justify-content-center text-center">
                
                <!-- 2ND PLACE PODIUM (SILVER) -->
                <div class="col-12 col-md-4 order-2 order-md-1">
                    <div class="podium-card p-4 rounded-4 bg-white shadow-sm border border-secondary border-opacity-25 position-relative hover-lift">
                        <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-secondary text-white px-3 py-2 fs-7 fw-bold shadow-sm">
                            🥈 P2 SILVER
                        </span>
                        <div class="avatar-circle mx-auto mb-3 mt-2 d-flex align-items-center justify-content-center rounded-circle shadow-sm" style="width: 72px; height: 72px; font-size: 28px; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border: 2px solid #94a3b8; color: #334155;">
                            <i class="<?= $scope === 'teams' ? 'fas fa-shield-alt' : 'fas fa-user-astronaut' ?>"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1 text-truncate" title="<?= htmlspecialchars($p2['team_name'] ?? $p2['full_name']) ?>">
                            <?= htmlspecialchars($p2['team_name'] ?? $p2['full_name']) ?>
                        </h6>
                        <span class="text-muted fs-8 d-block mb-2">
                            <?= htmlspecialchars($p2['client_name'] ?? $p2['team_name'] ?? 'Operational Driver') ?>
                        </span>
                        <div class="d-flex justify-content-center align-items-baseline gap-1 my-2">
                            <span class="display-6 fw-black text-secondary"><?= number_format($p2['total_score'] ?? $p2['score'], 1) ?></span>
                            <span class="fs-8 text-muted">pts</span>
                        </div>
                        <span class="badge bg-light text-secondary border px-3 py-1 rounded-pill fs-8">
                            Gap: <?= htmlspecialchars($p2['gap_to_leader']) ?>
                        </span>
                    </div>
                </div>

                <!-- 1ST PLACE PODIUM (GOLD POLE POSITION) -->
                <div class="col-12 col-md-4 order-1 order-md-2">
                    <div class="podium-card p-4 rounded-4 bg-white shadow-lg border border-warning border-3 position-relative hover-lift" style="transform: scale(1.05); z-index: 2; box-shadow: 0 10px 25px rgba(234, 179, 8, 0.25) !important;">
                        <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-warning text-dark px-4 py-2 fs-6 fw-black shadow">
                            👑 P1 CHAMPION
                        </span>
                        <div class="avatar-circle mx-auto mb-3 mt-3 d-flex align-items-center justify-content-center rounded-circle shadow" style="width: 88px; height: 88px; font-size: 38px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border: 3px solid #b45309; box-shadow: 0 4px 15px rgba(217, 119, 6, 0.4) !important;">
                            <i class="fas fa-trophy text-white" style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.25));"></i>
                        </div>
                        <h5 class="fw-black text-dark mb-1 text-truncate" title="<?= htmlspecialchars($p1['team_name'] ?? $p1['full_name']) ?>">
                            <?= htmlspecialchars($p1['team_name'] ?? $p1['full_name']) ?>
                        </h5>
                        <span class="text-muted fs-8 d-block mb-2">
                            <?= htmlspecialchars($p1['client_name'] ?? $p1['team_name'] ?? 'Pole Position Leader') ?>
                        </span>
                        <div class="d-flex justify-content-center align-items-baseline gap-1 my-2">
                            <span class="display-5 fw-black" style="color: #b45309;"><?= number_format($p1['total_score'] ?? $p1['score'], 1) ?></span>
                            <span class="fs-7 text-muted">pts</span>
                        </div>
                        <span class="badge bg-success bg-opacity-15 text-success border border-success-subtle px-3 py-1 rounded-pill fs-8 fw-bold">
                            <i class="fas fa-flag-checkered me-1"></i>Pole Position Leader
                        </span>
                    </div>
                </div>

                <!-- 3RD PLACE PODIUM (BRONZE) -->
                <div class="col-12 col-md-4 order-3 order-md-3">
                    <div class="podium-card p-4 rounded-4 bg-white shadow-sm border border-danger border-opacity-25 position-relative hover-lift">
                        <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-dark text-white px-3 py-2 fs-7 fw-bold shadow-sm" style="background-color: #92400e !important;">
                            🥉 P3 BRONZE
                        </span>
                        <div class="avatar-circle mx-auto mb-3 mt-2 d-flex align-items-center justify-content-center rounded-circle shadow-sm" style="width: 72px; height: 72px; font-size: 28px; background: linear-gradient(135deg, #fff7ed 0%, #fed7aa 100%); border: 2px solid #ea580c; color: #9a3412;">
                            <i class="<?= $scope === 'teams' ? 'fas fa-shield-alt' : 'fas fa-user-astronaut' ?>"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1 text-truncate" title="<?= htmlspecialchars($p3['team_name'] ?? $p3['full_name']) ?>">
                            <?= htmlspecialchars($p3['team_name'] ?? $p3['full_name']) ?>
                        </h6>
                        <span class="text-muted fs-8 d-block mb-2">
                            <?= htmlspecialchars($p3['client_name'] ?? $p3['team_name'] ?? 'Operational Driver') ?>
                        </span>
                        <div class="d-flex justify-content-center align-items-baseline gap-1 my-2">
                            <span class="display-6 fw-black text-dark" style="color: #92400e !important;"><?= number_format($p3['total_score'] ?? $p3['score'], 1) ?></span>
                            <span class="fs-8 text-muted">pts</span>
                        </div>
                        <span class="badge bg-light text-dark border px-3 py-1 rounded-pill fs-8">
                            Gap: <?= htmlspecialchars($p3['gap_to_leader']) ?>
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Grand Prix Racing Leaderboard Track -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-list-ol text-primary fs-5"></i>
                <h6 class="fw-black mb-0 text-dark text-uppercase tracking-wider">
                    <?= $scope === 'teams' ? 'Teams Championship Standings' : 'Individual Racers Leaderboard' ?>
                </h6>
                <span class="badge bg-light text-muted border fs-8 ms-2">
                    <?= count($standings) ?> <?= $scope === 'teams' ? 'Teams Racing' : 'Contenders' ?>
                </span>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="text-muted fs-8">Live Telemetry Synced: <strong id="telemetrySyncedLabel" class="text-dark"><?= date('H:i:s') ?></strong></span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="racingLeaderboardTable">
                    <thead class="table-light fs-8 text-uppercase">
                        <tr>
                            <th style="width: 70px;" class="text-center">Pos</th>
                            <th style="width: 300px;"><?= $scope === 'teams' ? 'Team & Division' : 'Racer & Team' ?></th>
                            <th style="width: 320px;">Race Track &amp; Speed Progress</th>
                            <th style="width: 140px;" class="text-center">Output Volume</th>
                            <th style="width: 130px;" class="text-center">Quality Rate</th>
                            <th style="width: 130px;" class="text-end">Points / Score</th>
                            <th style="width: 110px;" class="text-center">Gap to P1</th>
                        </tr>
                    </thead>
                    <tbody id="leaderboardTbody">
                        <?php foreach ($standings as $idx => $row): 
                            $rank = $row['rank'];
                            $isTop3 = $rank <= 3;
                            $rankBadge = $rank === 1 ? 'bg-warning text-dark fw-black' : ($rank === 2 ? 'bg-secondary text-white' : ($rank === 3 ? 'bg-dark text-white' : 'bg-light text-muted border'));
                            $score = (float)($row['total_score'] ?? $row['score']);
                            $scorePct = min(100, max(10, $score));
                            $teamColor = $row['team_color'] ?? '#3b82f6';
                            $rowId = 'row_' . ($scope === 'teams' ? 'team_' . $row['team_id'] : 'user_' . $row['user_id']);
                        ?>
                        <tr class="racing-row <?= $rank === 1 ? 'table-warning-subtle' : '' ?> transition-all" 
                            id="<?= $rowId ?>" 
                            data-rank="<?= $rank ?>" 
                            data-score="<?= $score ?>">
                            
                            <!-- Position Number -->
                            <td class="text-center">
                                <span class="badge rounded-pill px-3 py-2 fs-7 <?= $rankBadge ?>" id="badge_pos_<?= $rowId ?>">
                                    P<?= $rank ?>
                                </span>
                            </td>

                            <!-- Entity Info -->
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-2xs flex-shrink-0" 
                                         style="width: 42px; height: 42px; background: <?= $row['team_gradient'] ?? 'linear-gradient(135deg, #1E40AF 0%, #3B82F6 100%)' ?>;">
                                        <?= $scope === 'teams' ? ('T' . $row['team_id']) : strtoupper(substr($row['full_name'], 0, 2)) ?>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="d-flex align-items-center gap-2">
                                            <strong class="text-dark fs-7 text-truncate d-block">
                                                <?= htmlspecialchars($row['team_name'] ?? $row['full_name']) ?>
                                            </strong>
                                            <?php if ($rank === 1): ?>
                                                <span class="badge bg-warning text-dark fs-9 px-2 py-0">LEADER</span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="text-muted fs-8 d-block text-truncate">
                                            <?= htmlspecialchars($row['client_name'] ?? $row['designation'] ?? 'Operations') ?>
                                            <?php if ($scope === 'individuals' && !empty($row['team_name'])): ?>
                                                &bull; <span class="text-primary fw-semibold"><?= htmlspecialchars($row['team_name']) ?></span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Racing Track Progress Bar -->
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <div class="d-flex justify-content-between align-items-center fs-9 text-muted">
                                        <span>Velocity Track: <strong><?= $row['speed_index'] ?? round($score / 1.2, 1) ?> units/hr</strong></span>
                                        <span class="fw-bold text-dark"><?= round($scorePct, 1) ?>%</span>
                                    </div>
                                    <div class="progress rounded-pill shadow-2xs position-relative" style="height: 12px; background-color: #f1f5f9;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated rounded-pill" 
                                             role="progressbar" 
                                             style="width: <?= $scorePct ?>%; background: <?= $row['team_gradient'] ?? 'linear-gradient(90deg, #3b82f6, #1d4ed8)' ?>;" 
                                             aria-valuenow="<?= $scorePct ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                        <!-- Animated racecar indicator -->
                                        <div class="position-absolute top-50 translate-middle-y" style="left: calc(<?= min(95, $scorePct) ?>% - 10px);">
                                            <i class="fas fa-car-side text-dark fs-9 filter-drop-shadow"></i>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Output Volume -->
                            <td class="text-center">
                                <span class="fw-bold text-dark fs-7 d-block">
                                    <?= number_format($row['total_volume'] ?? $row['processed_count']) ?>
                                </span>
                                <span class="text-muted fs-9">
                                    / <?= number_format($row['target_volume'] ?? $row['target_count']) ?> tgt
                                </span>
                            </td>

                            <!-- Quality Rate -->
                            <td class="text-center">
                                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-1 fs-8">
                                    <?= number_format($row['accuracy_pct'], 1) ?>% Acc
                                </span>
                            </td>

                            <!-- Score Points -->
                            <td class="text-end">
                                <span class="display-7 fw-black text-dark d-block">
                                    <?= number_format($score, 1) ?>
                                </span>
                                <span class="text-muted fs-9">points</span>
                            </td>

                            <!-- Gap to P1 Leader -->
                            <td class="text-center">
                                <span class="badge bg-light text-secondary border px-2 py-1 fs-8 text-nowrap">
                                    <?= htmlspecialchars($row['gap_to_leader']) ?>
                                </span>
                            </td>

                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Slideshow Preview Modal Trigger -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center mb-4">
        <div class="row align-items-center justify-content-between">
            <div class="col-md-8 text-md-start mb-3 mb-md-0">
                <h5 class="fw-black mb-1 text-dark"><i class="fas fa-tv text-primary me-2"></i>Executive Team-By-Team Slideshow</h5>
                <p class="text-muted fs-7 mb-0">Launch high-impact digital presentation showcasing team positions, MVPs, and performance highlights for boardroom or floor displays.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="<?= base_url('performance/contest-slideshow' . ($selectedProjectId ? '?project_id=' . $selectedProjectId : '')) ?>" target="_blank" class="btn btn-dark fw-bold px-4 py-2 rounded-pill shadow-sm hover-scale">
                    <i class="fas fa-external-link-alt me-1"></i> Open TV Slideshow
                </a>
            </div>
        </div>
    </div>

</div>

<!-- Audio Synthesizer & Auto-Refresh Scripts -->
<script>
// State Tracking for Overtake Detection
var currentStandingsState = <?= json_encode($standings) ?>;
var currentScope = "<?= $scope ?>";
var currentTimeframe = "<?= $timeframe ?>";
var currentTop = <?= (int)$top ?>;
var currentProjectId = <?= $selectedProjectId ? (int)$selectedProjectId : "null" ?>;
var isAudioEnabled = localStorage.getItem("contest_audio_muted") !== "true";
var countdownSeconds = 60;
var countdownTimer = null;

document.addEventListener("DOMContentLoaded", function() {
    updateAudioUI();
    startCountdownTimer();
});

// Web Audio API Synthesizer (Formula 1 Rev + Overtake Whoosh + Victory Chime)
function playOvertakeSound() {
    if (!isAudioEnabled) return;
    try {
        var AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        var ctx = new AudioCtx();

        var now = ctx.currentTime;

        // 1. Engine Turbo Whoosh (Oscillator with rapid pitch sweep)
        var osc = ctx.createOscillator();
        var gain = ctx.createGain();
        osc.type = "sawtooth";
        osc.frequency.setValueAtTime(90, now);
        osc.frequency.exponentialRampToValueAtTime(750, now + 0.35);
        osc.frequency.exponentialRampToValueAtTime(140, now + 0.7);

        gain.gain.setValueAtTime(0.01, now);
        gain.gain.linearRampToValueAtTime(0.3, now + 0.25);
        gain.gain.linearRampToValueAtTime(0.001, now + 0.7);

        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(now);
        osc.stop(now + 0.7);

        // 2. Victory Arpeggio Chime (C5, E5, G5, C6)
        var notes = [523.25, 659.25, 783.99, 1046.50];
        notes.forEach(function(freq, i) {
            var cOsc = ctx.createOscillator();
            var cGain = ctx.createGain();
            var noteStart = now + 0.35 + (i * 0.1);
            cOsc.type = "sine";
            cOsc.frequency.setValueAtTime(freq, noteStart);
            cGain.gain.setValueAtTime(0.01, noteStart);
            cGain.gain.linearRampToValueAtTime(0.25, noteStart + 0.05);
            cGain.gain.exponentialRampToValueAtTime(0.001, noteStart + 0.4);

            cOsc.connect(cGain);
            cGain.connect(ctx.destination);
            cOsc.start(noteStart);
            cOsc.stop(noteStart + 0.45);
        });
    } catch(e) {
        console.warn("Audio Context playback error:", e);
    }
}

function toggleAudioFx() {
    isAudioEnabled = !isAudioEnabled;
    localStorage.setItem("contest_audio_muted", (!isAudioEnabled).toString());
    updateAudioUI();
    if (isAudioEnabled) {
        playOvertakeSound();
    }
}

function updateAudioUI() {
    var icon = document.getElementById("audioIcon");
    var label = document.getElementById("audioText");
    if (!icon || !label) return;

    if (isAudioEnabled) {
        icon.className = "fas fa-volume-up text-warning me-1";
        label.textContent = "Sound: ON";
    } else {
        icon.className = "fas fa-volume-mute text-secondary me-1";
        label.textContent = "Sound: OFF";
    }
}

// 1-Minute Live Auto-Refresh Countdown Loop
function startCountdownTimer() {
    if (countdownTimer) clearInterval(countdownTimer);
    countdownSeconds = 60;

    countdownTimer = setInterval(function() {
        countdownSeconds--;
        var label = document.getElementById("countdownLabel");
        if (label) {
            label.textContent = "Sync in " + countdownSeconds + "s";
        }

        if (countdownSeconds <= 0) {
            countdownSeconds = 60;
            refreshTelemetry(false);
        }
    }, 1000);
}

// Telemetry Refresh AJAX
function refreshTelemetry(isManual) {
    var spinner = document.getElementById("syncSpinner");
    var watch = document.getElementById("stopwatchIcon");
    if (spinner && watch) {
        spinner.style.display = "inline-block";
        watch.style.display = "none";
    }

    var url = "<?= base_url('performance/contest-data') ?>" 
        + "?scope=" + encodeURIComponent(currentScope)
        + "&timeframe=" + encodeURIComponent(currentTimeframe)
        + "&top=" + encodeURIComponent(currentTop);

    if (currentProjectId) {
        url += "&project_id=" + encodeURIComponent(currentProjectId);
    }

    fetch(url)
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (spinner && watch) {
                spinner.style.display = "none";
                watch.style.display = "inline-block";
            }
            if (data.status === "success" && data.standings) {
                var syncLabel = document.getElementById("telemetrySyncedLabel");
                if (syncLabel) syncLabel.textContent = data.synced_at || new Date().toLocaleTimeString();
                
                checkAndTriggerOvertakes(data.standings);
                countdownSeconds = 60;
            }
        })
        .catch(function(err) {
            console.error("Telemetry fetch error:", err);
            if (spinner && watch) {
                spinner.style.display = "none";
                watch.style.display = "inline-block";
            }
        });
}

// Compare Previous vs Fresh Standings to detect position overtakes
function checkAndTriggerOvertakes(newStandings) {
    if (!currentStandingsState || currentStandingsState.length === 0) {
        currentStandingsState = newStandings;
        return;
    }

    var oldRankMap = {};
    currentStandingsState.forEach(function(item) {
        var key = item.team_id ? "team_" + item.team_id : "user_" + item.user_id;
        oldRankMap[key] = item.rank;
    });

    var hasOvertake = false;
    var overtakerName = "";
    var prevPos = 0;
    var newPos = 0;

    newStandings.forEach(function(item) {
        var key = item.team_id ? "team_" + item.team_id : "user_" + item.user_id;
        var oldR = oldRankMap[key];
        var newR = item.rank;

        // If entity improved position (e.g. was 2, now 1)
        if (oldR && newR < oldR) {
            hasOvertake = true;
            overtakerName = item.team_name || item.full_name;
            prevPos = oldR;
            newPos = newR;
            highlightOvertakingRow(key, newR);
        }
    });

    if (hasOvertake) {
        playOvertakeSound();
        showOvertakeBanner(overtakerName, prevPos, newPos);
    }

    currentStandingsState = newStandings;
}

function highlightOvertakingRow(key, newRank) {
    var row = document.getElementById("row_" + key);
    if (!row) return;

    // Apply high-octane overtake CSS animation
    row.classList.add("overtake-animation");
    setTimeout(function() {
        row.classList.remove("overtake-animation");
    }, 2500);

    // Update position badge
    var badge = document.getElementById("badge_pos_row_" + key);
    if (badge) {
        badge.textContent = "P" + newRank;
        if (newRank === 1) {
            badge.className = "badge rounded-pill px-3 py-2 fs-7 bg-warning text-dark fw-black animate__animated animate__pulse";
        }
    }
}

function showOvertakeBanner(name, prevPos, newPos) {
    var banner = document.getElementById("overtakeAlertBanner");
    var title = document.getElementById("overtakeTitle");
    var sub = document.getElementById("overtakeSubtitle");

    if (banner && title && sub) {
        title.textContent = "🚀 OVERTAKE ALERT! " + name.toUpperCase() + " MOVED TO P" + newPos + "!";
        sub.textContent = name + " has climbed from Position " + prevPos + " into Position " + newPos + "!";
        banner.classList.remove("d-none");
        banner.classList.add("d-flex");

        setTimeout(function() {
            banner.classList.add("d-none");
            banner.classList.remove("d-flex");
        }, 8000);
    }
}

// Manual Test Simulation Button for Senior Management Demo
function triggerSimulatedOvertake() {
    playOvertakeSound();
    
    // Pick the 2nd row and slide it above the 1st row
    var tbody = document.getElementById("leaderboardTbody");
    var rows = tbody ? tbody.querySelectorAll("tr") : [];

    if (rows.length >= 2) {
        var firstRow = rows[0];
        var secondRow = rows[1];

        // Animate swap
        secondRow.classList.add("overtake-animation");
        setTimeout(function() {
            tbody.insertBefore(secondRow, firstRow);
            
            // Swap badges
            var b1 = secondRow.querySelector(".badge");
            var b2 = firstRow.querySelector(".badge");
            if (b1) { b1.textContent = "P1"; b1.className = "badge rounded-pill px-3 py-2 fs-7 bg-warning text-dark fw-black"; }
            if (b2) { b2.textContent = "P2"; b2.className = "badge rounded-pill px-3 py-2 fs-7 bg-secondary text-white"; }

            var overtakerName = secondRow.querySelector("strong") ? secondRow.querySelector("strong").textContent.trim() : "Team";
            showOvertakeBanner(overtakerName, 2, 1);

            setTimeout(function() {
                secondRow.classList.remove("overtake-animation");
            }, 2000);
        }, 300);
    } else {
        showOvertakeBanner("Racing Leader", 2, 1);
    }
}
</script>

<style>
/* High-Octane Grand Prix Contest Styling */
.hover-scale {
    transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.hover-scale:hover {
    transform: scale(1.05);
}
.hover-lift {
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.hover-lift:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08) !important;
}

/* Overtake Boost Keyframe Animation */
@keyframes overtakeBoost {
    0% {
        background-color: rgba(234, 179, 8, 0.4);
        transform: scale(1.02) translateX(-4px);
        box-shadow: 0 0 25px rgba(234, 179, 8, 0.6);
    }
    50% {
        background-color: rgba(34, 197, 94, 0.3);
        transform: scale(1.03) translateX(0);
        box-shadow: 0 0 35px rgba(34, 197, 94, 0.8);
    }
    100% {
        background-color: transparent;
        transform: scale(1) translateX(0);
        box-shadow: none;
    }
}

.overtake-animation {
    animation: overtakeBoost 1.8s ease-out forwards;
    z-index: 10;
    position: relative;
}

.filter-drop-shadow {
    filter: drop-shadow(0 2px 3px rgba(0,0,0,0.3));
}
</style>
