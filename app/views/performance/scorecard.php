<div class="container-fluid px-4 py-4">
    <!-- Top Filter Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-trophy text-warning me-2"></i>Performance Scorecard Hub
            </h4>
            <p class="text-muted fs-7 mb-0">Unified Performance Governance & Grade Ranking Engine</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <form action="<?= base_url('performance/scorecard') ?>" method="GET" class="d-flex gap-2 align-items-center">
                <?php if (is_super_admin() || is_senior_management() || is_manager() || is_team_lead()): ?>
                <select name="user_id" class="form-select form-select-sm fw-bold border-primary" onchange="this.form.submit()">
                    <?php foreach ($allUsers as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= $selectedUserId == $u['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['user_code']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>

                <input type="month" name="month" class="form-control form-control-sm fw-bold border-primary" value="<?= $selectedMonth ?>" onchange="this.form.submit()">
            </form>

            <a href="<?= base_url('performance/production') ?>" class="btn btn-outline-primary btn-sm fw-bold">
                <i class="fas fa-plus-circle me-1"></i>Log Production Count
            </a>
            <?php if (is_super_admin() || is_senior_management() || is_manager()): ?>
            <button type="button" class="btn btn-outline-success btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#importScorecardModal">
                <i class="fas fa-file-excel me-1"></i>Import Scorecard Excel
            </button>
            <?php endif; ?>
            <a href="<?= base_url('performance/analytics') ?>" class="btn btn-primary btn-sm fw-bold">
                <i class="fas fa-chart-line me-1"></i>Multi-Horizon Analytics
            </a>
        </div>
    </div>

    <?php 
        $user = $data['user'] ?? [];
        $sc = $data['scorecard'] ?? [];
        $grade = $data['gradeInfo'] ?? [];
        $trend = $data['trend'] ?? [];
        $kpis = $data['kpis'] ?? [];
        $status = $sc['status'] ?? 'Draft';
    ?>

    <!-- Banner Card -->
    <div class="card border-0 shadow-lg text-white mb-4" style="background: linear-gradient(135deg, #0A1628 0%, #112240 60%, #1D3557 100%); border-radius: 16px;">
        <div class="card-body p-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 64px; height: 64px; font-size: 24px; border: 3px solid rgba(255,255,255,0.2);">
                            <?= strtoupper(substr($user['full_name'] ?? 'U', 0, 2)) ?>
                        </div>
                        <div>
                            <div class="fs-8 text-info text-uppercase fw-bold letter-spacing-1">Employee Profile & Overview</div>
                            <h3 class="fw-bold mb-1 text-white"><?= htmlspecialchars($user['full_name'] ?? 'N/A') ?></h3>
                            <div class="d-flex gap-2 flex-wrap fs-8 text-white-50">
                                <span><i class="fas fa-id-badge text-warning me-1"></i>Code: <strong><?= htmlspecialchars($user['user_code'] ?? 'N/A') ?></strong></span>
                                <span>•</span>
                                <span><i class="fas fa-user-tag text-success me-1"></i>Role: <strong><?= htmlspecialchars($user['role_name'] ?? 'N/A') ?></strong></span>
                                <span>•</span>
                                <span><i class="fas fa-building text-info me-1"></i>Dept: <strong><?= htmlspecialchars($user['department'] ?? 'Operations') ?></strong></span>
                            </div>
                        </div>
                    </div>
                    <p class="fs-8 text-white-50 mb-0">
                        Evaluated for <strong><?= $data['month_name'] ?></strong> across 5 weighted performance dimensions. Calculated automatically against team SLA benchmarks.
                    </p>
                </div>

                <!-- Score Highlight -->
                <div class="col-lg-5 text-lg-end">
                    <div class="d-inline-flex align-items-center gap-4 p-3 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-15 shadow-sm">
                        <div class="text-center">
                            <div class="fs-9 text-uppercase text-white-50 fw-bold">Overall Score</div>
                            <div class="display-5 fw-bold text-warning lh-1"><?= number_format($sc['total_score'] ?? 0, 1) ?></div>
                            <div class="fs-9 text-white-50 mt-1">out of 100 Pts</div>
                        </div>
                        <div class="border-start border-white border-opacity-25 ps-4 text-start">
                            <div class="mb-1">
                                <span class="badge <?= $grade['badgeBg'] ?? 'bg-primary' ?> fs-7 px-3 py-2 fw-bold shadow-sm">
                                    <?= $grade['icon'] ?? '🏆' ?> <?= htmlspecialchars($grade['grade'] ?? 'Standard') ?>
                                </span>
                            </div>
                            <div class="fw-bold text-white fs-7"><?= htmlspecialchars($grade['rating'] ?? 'Meeting Expectations') ?></div>
                            <div class="fs-8 text-info mt-1">
                                Team Rank: <strong class="text-warning">#<?= $data['rank'] ?? 1 ?></strong> of <?= $data['total_users'] ?? 1 ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5 Key Metric Cards -->
    <div class="row g-3 mb-4">
        <?php 
            $scoreKeys = [
                'production'  => ['val' => $sc['production_score'] ?? 0, 'max' => 40, 'icon' => 'fa-cogs', 'color' => 'primary'],
                'intAccuracy' => ['val' => $sc['int_accuracy_score'] ?? 0, 'max' => 20, 'icon' => 'fa-check-circle', 'color' => 'success'],
                'extAccuracy' => ['val' => $sc['ext_accuracy_score'] ?? 0, 'max' => 20, 'icon' => 'fa-bullseye', 'color' => 'purple'],
                'pkt'         => ['val' => $sc['pkt_score'] ?? 0, 'max' => 10, 'icon' => 'fa-graduation-cap', 'color' => 'warning'],
                'attendance'  => ['val' => $sc['attendance_score'] ?? 0, 'max' => 10, 'icon' => 'fa-user-clock', 'color' => 'info']
            ];
            foreach ($kpis as $k):
                $sk = $scoreKeys[$k['kpi_key']] ?? ['val' => 0, 'max' => $k['weight_points'], 'icon' => 'fa-chart-pie', 'color' => 'primary'];
                $pct = round(($sk['val'] / max(1, $sk['max'])) * 100);
        ?>
        <div class="col-md-4 col-lg-2-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fs-8 text-muted fw-bold text-uppercase"><?= htmlspecialchars($k['label']) ?></span>
                        <i class="fas <?= $sk['icon'] ?> text-<?= $sk['color'] ?> fs-6"></i>
                    </div>
                    <div class="d-flex align-items-baseline gap-1 mb-2">
                        <span class="fs-3 fw-bold text-dark"><?= number_format($sk['val'], 1) ?></span>
                        <span class="fs-8 text-muted">/ <?= $sk['max'] ?> pts</span>
                    </div>
                    <div class="progress mb-1" style="height: 6px;">
                        <div class="progress-bar bg-<?= $sk['color'] ?>" style="width: <?= $pct ?>%;"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center fs-9 text-muted mt-1">
                        <span>Achievement:</span>
                        <span class="fw-bold text-dark"><?= $pct ?>%</span>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- 2 Column Section: Trend & Story -->
    <div class="row g-4 mb-4">
        <!-- 6-Month Trend Chart -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-area text-primary me-2"></i>6-Month Performance Trend</h6>
                    <span class="badge bg-light text-primary border">Monthly Trajectory</span>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-around align-items-end pt-4 pb-2 text-center" style="height: 220px;">
                        <?php foreach ($trend as $tr): 
                            $h = max(20, round(($tr['score'] / 100) * 160));
                            $isCurrent = $tr['year_month'] === $selectedMonth;
                        ?>
                        <div class="d-flex flex-column align-items-center gap-2" style="width: 50px;">
                            <span class="fs-9 fw-bold text-dark"><?= round($tr['score']) ?></span>
                            <div class="w-100 rounded-top <?= $isCurrent ? 'bg-primary' : 'bg-secondary bg-opacity-25' ?> transition-all shadow-2xs" style="height: <?= $h ?>px;"></div>
                            <span class="fs-8 fw-bold <?= $isCurrent ? 'text-primary' : 'text-muted' ?>"><?= $tr['month'] ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Performance Story & Insights -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-sparkles text-warning me-2"></i>AI Performance Insights & Story</h6>
                    <?php 
                        $status = $sc['status'] ?? 'Draft';
                        $statusBadge = $status === 'Locked' ? 'bg-success' : ($status === 'Verified' ? 'bg-info text-dark' : 'bg-secondary');
                        $statusIcon = $status === 'Locked' ? 'fa-lock' : ($status === 'Verified' ? 'fa-check-double' : 'fa-edit');
                    ?>
                    <span class="badge <?= $statusBadge ?>"><i class="fas <?= $statusIcon ?> me-1"></i><?= $status ?></span>
                </div>
                <div class="card-body p-4">
                    <div class="p-3 bg-light rounded-3 border mb-3 fs-8">
                        <strong class="text-primary d-block mb-1"><i class="fas fa-check-circle me-1"></i>Executive Summary:</strong>
                        <?= htmlspecialchars($user['full_name'] ?? 'Employee') ?> achieved a total score of <strong><?= number_format($sc['total_score'] ?? 0, 1) ?>/100</strong> for <?= $data['month_name'] ?>, placing in the <strong><?= $grade['grade'] ?> Grade</strong> tier.
                    </div>

                    <?php if (!empty($sc['special_note'])): ?>
                    <div class="p-3 rounded-3 border mb-3 fs-8 bg-warning bg-opacity-10 border-warning border-opacity-25">
                        <strong class="text-dark d-block mb-1"><i class="fas fa-award text-warning me-1"></i>Special Appraisal Note:</strong>
                        <span class="fst-italic text-dark">"<?= htmlspecialchars($sc['special_note']) ?>"</span>
                        <?php if (!empty($sc['recognition_type'])): ?>
                        <div class="mt-2">
                            <span class="badge bg-warning text-dark"><i class="fas fa-medal me-1"></i><?= htmlspecialchars($sc['recognition_type']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="p-3 rounded-3 border mb-3 fs-8 bg-warning bg-opacity-10 border-warning border-opacity-50">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong class="text-dark"><i class="fas fa-trophy text-warning me-1"></i>Official Honours &amp; Accolades (<?= count($userAwards ?? []) ?>):</strong>
                            <a href="<?= base_url('performance/awards') ?>" class="badge bg-warning text-dark text-decoration-none">Awards Hub</a>
                        </div>
                        <?php if (!empty($userAwards)): ?>
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <?php foreach ($userAwards as $ua): ?>
                            <span class="badge rounded-pill px-2 py-1 shadow-sm fs-9" style="background-color: <?= $ua['badge_color'] ?>; color: #fff;" title="<?= htmlspecialchars($ua['citation']) ?>">
                                <i class="fas <?= $ua['badge_icon'] ?> me-1"></i><?= htmlspecialchars($ua['award_name']) ?> (<?= htmlspecialchars($ua['period_key']) ?>)
                            </span>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="d-flex align-items-center gap-2 text-muted fs-8">
                            <span class="badge bg-white text-secondary border rounded-pill px-2 py-1 fs-9"><i class="fas fa-medal me-1 text-warning"></i>Active Contender</span>
                            <span>Eligible for upcoming Datamatics AAA Periodic Recognition cycle.</span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="list-group list-group-flush fs-8">
                        <div class="list-group-item px-0 py-2 border-0 d-flex gap-2 align-items-start">
                            <i class="fas fa-thumbs-up text-success mt-1"></i>
                            <div>
                                <strong class="text-dark">Key Strength:</strong> High quality accuracy and process compliance.
                            </div>
                        </div>
                        <div class="list-group-item px-0 py-2 border-0 d-flex gap-2 align-items-start">
                            <i class="fas fa-lightbulb text-warning mt-1"></i>
                            <div>
                                <strong class="text-dark">Growth Area:</strong> Increasing daily transaction count logging.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Governance Bar: Verification & Historical Snapshot Lock -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="fs-8 text-muted fw-bold text-uppercase">Scorecard Lifecycle:</span>
                <?php if ($status === 'Locked'): ?>
                    <span class="badge bg-success fs-7 px-3 py-2 fw-bold">
                        <i class="fas fa-lock me-1"></i>Locked & Frozen by <?= htmlspecialchars($sc['locker_name'] ?? 'Management') ?>
                    </span>
                    <span class="fs-8 text-muted fst-italic ms-2">Immutable Historical Snapshot</span>
                <?php elseif ($status === 'Verified'): ?>
                    <span class="badge bg-info text-dark fs-7 px-3 py-2 fw-bold">
                        <i class="fas fa-check-double me-1"></i>Verified by <?= htmlspecialchars($sc['verifier_name'] ?? 'Team Lead') ?>
                    </span>
                    <span class="fs-8 text-muted ms-2">Awaiting Management Lock</span>
                <?php else: ?>
                    <span class="badge bg-secondary fs-7 px-3 py-2 fw-bold">
                        <i class="fas fa-edit me-1"></i>Draft (Open for Logging)
                    </span>
                <?php endif; ?>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <?php if (is_super_admin() || is_senior_management() || is_manager() || is_team_lead()): ?>
                <button type="button" class="btn btn-outline-warning btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#notesModal">
                    <i class="fas fa-comment-alt me-1"></i>Appraisal Note & Recognition
                </button>
                <?php endif; ?>

                <?php if ($status === 'Draft' && (is_super_admin() || is_senior_management() || is_manager() || is_team_lead())): ?>
                <form action="<?= base_url('performance/verify-scorecard') ?>" method="POST" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                    <input type="hidden" name="scorecard_id" value="<?= $sc['id'] ?? 0 ?>">
                    <input type="hidden" name="user_id" value="<?= $selectedUserId ?>">
                    <input type="hidden" name="month" value="<?= $selectedMonth ?>">
                    <button type="submit" class="btn btn-outline-info btn-sm fw-bold">
                        <i class="fas fa-check-double me-1"></i>Verify Scorecard (TL)
                    </button>
                </form>
                <?php endif; ?>

                <?php if ($status !== 'Locked' && (is_super_admin() || is_senior_management() || is_manager())): ?>
                <form action="<?= base_url('performance/lock-scorecard') ?>" method="POST" class="d-inline" onsubmit="return confirm('Freeze and lock this month\'s appraisal scorecard? This snapshot cannot be modified once locked.');">
                    <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                    <input type="hidden" name="scorecard_id" value="<?= $sc['id'] ?? 0 ?>">
                    <input type="hidden" name="user_id" value="<?= $selectedUserId ?>">
                    <input type="hidden" name="month" value="<?= $selectedMonth ?>">
                    <button type="submit" class="btn btn-outline-success btn-sm fw-bold">
                        <i class="fas fa-lock me-1"></i>Lock Scorecard (Manager)
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Detailed Operations Ledger Table (Datamatics Operations Model) -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fas fa-table text-primary me-2"></i>Detailed Operations Performance Breakdown (Datamatics BPO Matrix)
            </h6>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fw-bold">
                <?= $data['month_name'] ?>
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable text-center">
                    <thead class="table-light fs-8">
                        <tr>
                            <th>Working Days</th>
                            <th>Present</th>
                            <th>Leave Days</th>
                            <th>Attendance %</th>
                            <th>Invoices / Count</th>
                            <th>Total Points</th>
                            <th>Avg Points/Day</th>
                            <th>Target Benchmark</th>
                            <th>Points %</th>
                            <th>Production Scale</th>
                            <th>Internal Errors</th>
                            <th>External Errors</th>
                            <th>PKT Assessment</th>
                            <th>Timesheet</th>
                            <th>Total Score</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody class="fs-8">
                        <?php
                            $workDays = (float)($sc['working_days'] ?? 24.0);
                            $presDays = (float)($sc['present_days'] ?? 22.0);
                            $leaveDays = (float)($sc['leave_days'] ?? ($workDays - $presDays));
                            $attPct = $workDays > 0 ? round(($presDays / $workDays) * 100, 1) : 100.0;
                            $procCount = (int)($sc['processed_count'] ?? 0);
                            $totalPts = (float)($sc['total_points'] ?? ($procCount * 2.5));
                            $avgPts = (float)($sc['avg_points'] ?? ($presDays > 0 ? $totalPts / $presDays : 0));
                            $targetPts = (float)($sc['target_points'] ?? 450.0);
                            $ptsPct = (float)($sc['points_pct'] ?? ($avgPts / max(1.0, $targetPts) * 100));
                            $prodScale = (int)($sc['production_scale'] ?? ($ptsPct >= 120 ? 3 : ($ptsPct >= 100 ? 2 : 1)));
                            $intErrs = (int)($sc['internal_errors'] ?? 0);
                            $intAcc = (float)($sc['int_accuracy_pct'] ?? 100.0);
                            $extErrs = (int)($sc['external_errors'] ?? 0);
                            $extAcc = (float)($sc['ext_accuracy_pct'] ?? 100.0);
                            $pktVal = (float)($sc['pkt_pct'] ?? 85.0);
                            $tsVal = (float)($sc['timesheet_score'] ?? 10.0);
                            $totScore = (float)($sc['total_score'] ?? 0.0);
                        ?>
                        <tr>
                            <td class="fw-bold"><?= $workDays ?></td>
                            <td class="fw-bold text-success"><?= $presDays ?></td>
                            <td>
                                <?php if ($leaveDays < 0): ?>
                                    <span class="badge bg-primary fs-9" title="Extra shifts / weekend overtime worked">
                                        <i class="fas fa-plus-circle me-1"></i><?= abs($leaveDays) ?> (Overtime)
                                    </span>
                                <?php elseif ($leaveDays > 0): ?>
                                    <span class="badge bg-warning text-dark fs-9"><?= $leaveDays ?> days</span>
                                <?php else: ?>
                                    <span class="badge bg-success fs-9">0 days</span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold"><?= $attPct ?>%</td>
                            <td class="fw-bold text-dark"><?= number_format($procCount) ?></td>
                            <td class="fw-bold text-primary"><?= number_format($totalPts, 2) ?></td>
                            <td class="fw-bold"><?= number_format($avgPts, 2) ?></td>
                            <td class="text-muted"><?= number_format($targetPts, 1) ?></td>
                            <td class="fw-bold <?= $ptsPct >= 100 ? 'text-success' : 'text-danger' ?>"><?= $ptsPct ?>%</td>
                            <td>
                                <?php 
                                    $scaleBadge = $prodScale == 3 ? 'bg-success' : ($prodScale == 2 ? 'bg-primary' : 'bg-secondary');
                                ?>
                                <span class="badge <?= $scaleBadge ?> fs-9 fw-bold">Scale <?= $prodScale ?></span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= $intErrs ?> err</div>
                                <div class="fs-9 <?= $intAcc >= 98 ? 'text-success' : 'text-warning' ?>"><?= number_format($intAcc, 2) ?>%</div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= $extErrs ?> err</div>
                                <div class="fs-9 <?= $extAcc >= 99 ? 'text-success' : 'text-danger' ?>"><?= number_format($extAcc, 2) ?>%</div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= number_format($pktVal, 1) ?>%</div>
                                <div class="fs-9 text-muted"><?= number_format($sc['pkt_score'] ?? 10, 1) ?> pts</div>
                            </td>
                            <td>
                                <span class="badge bg-info text-dark fs-9"><?= number_format($tsVal, 1) ?> pts</span>
                            </td>
                            <td class="fw-bold fs-7 text-primary"><?= number_format($totScore, 1) ?></td>
                            <td>
                                <span class="badge <?= $grade['badgeBg'] ?? 'bg-primary' ?> fs-9">
                                    <?= $grade['grade'] ?? 'Standard' ?>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Appraisal Notes & Recognition -->
<div class="modal fade" id="notesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('performance/update-notes') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="scorecard_id" value="<?= $sc['id'] ?? 0 ?>">
                <input type="hidden" name="user_id" value="<?= $selectedUserId ?>">
                <input type="hidden" name="month" value="<?= $selectedMonth ?>">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold"><i class="fas fa-medal me-2"></i>Appraisal Note & Recognition Tag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Recognition Type / Milestone</label>
                        <input type="text" name="recognition_type" class="form-control" placeholder="e.g. Outstanding Performer, 5 year award, Actively Part in MIS" value="<?= htmlspecialchars($sc['recognition_type'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Special Appraisal Note</label>
                        <textarea name="special_note" class="form-control" rows="4" placeholder="Enter special performance praise, multi-tasking appreciation, or operational achievements..."><?= htmlspecialchars($sc['special_note'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Recognition Description (Optional)</label>
                        <input type="text" name="recognition_desc" class="form-control" placeholder="Additional details..." value="<?= htmlspecialchars($sc['recognition_desc'] ?? '') ?>">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold text-dark">Save Appraisal Notes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Import Scorecard Excel / CSV -->
<div class="modal fade" id="importScorecardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('performance/import') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-file-excel me-2"></i>Import Employee Scorecard Excel / CSV</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="fs-8 text-muted mb-3">Upload monthly scorecard summary or daily break-up CSV/Excel matching <code>Employee_Scorecard.xlsx</code> column structure.</p>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Select File (.csv, .xlsx)</label>
                        <input type="file" name="import_file" class="form-control" accept=".csv, .xlsx" required>
                    </div>
                    <div class="bg-light p-3 rounded border fs-8">
                        <div class="fw-bold mb-1"><i class="fas fa-info-circle text-primary me-1"></i>Expected Columns:</div>
                        <code>Emp Code, Name, Month (YYYY-MM), Working Days, Present Days, Processed Count, Target Count, Internal Errors, External Errors</code>
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <a href="<?= base_url('performance/sample-template') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
                        <i class="fas fa-download me-1"></i>Download CSV Template
                    </a>
                    <div>
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success fw-bold"><i class="fas fa-upload me-1"></i>Import File</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
