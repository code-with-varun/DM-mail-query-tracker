<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-award text-primary me-2"></i>Activity Certifications & Competency Dashboard</h4>
            <p class="text-muted fs-7 mb-0">Track onboarding certification progress: Training KT &rarr; PKT Exam &rarr; Practice File Checker Approval</p>
        </div>
    </div>

    <!-- My Certifications Line Items Table -->
    <h5 class="fw-bold text-dark mb-3"><i class="fas fa-user-graduate me-2 text-primary"></i>My Assigned Activity Competency Matrix</h5>

    <div class="card border-0 shadow-sm mb-5 rounded-3 overflow-hidden">
        <div class="card-body p-0">
            <?php if (empty($myCertifications)): ?>
                <div class="p-4 text-center bg-white">
                    <p class="text-muted fs-7 mb-0">No assigned activities found for your user profile. Ask your supervisor to assign handled activities.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 fs-7 border-top">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;" class="text-center">#</th>
                                <th style="min-width: 240px;">DIVISION, ACTIVITY & SUB-ACTIVITY</th>
                                <th style="width: 140px;">1. KT TRAINING</th>
                                <th style="width: 160px;">2. PKT EXAM</th>
                                <th style="width: 150px;">3. PRACTICE FILE</th>
                                <th style="min-width: 170px;">OVERALL PROGRESS</th>
                                <th style="width: 180px;" class="text-center">STATUS & ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($myCertifications as $idx => $c): ?>
                            <?php
                                $pPercent = 0;
                                if (!empty($c['training_completed'])) $pPercent += 34;
                                if (!empty($c['pkt_passed'])) $pPercent += 33;
                                if (!empty($c['practice_passed'])) $pPercent += 33;

                                $barBg = 'bg-secondary';
                                if ($pPercent == 100) $barBg = 'bg-success';
                                elseif ($pPercent >= 67) $barBg = 'bg-info';
                                elseif ($pPercent >= 34) $barBg = 'bg-primary';
                            ?>
                            <tr>
                                <td class="text-center text-muted fw-bold"><?= $idx + 1 ?></td>

                                <!-- Division & Activity Top, Sub Activity Below -->
                                <td>
                                    <div class="text-muted fs-8 mb-1">
                                        <span class="badge bg-light text-dark border fs-8 me-1"><i class="fas fa-building me-1 text-secondary"></i><?= htmlspecialchars($c['division_name'] ?? 'Operations') ?></span>
                                        <span class="fw-semibold text-secondary"><?= htmlspecialchars($c['activity_name']) ?></span>
                                    </div>
                                    <div class="fw-bold text-primary fs-7"><?= htmlspecialchars($c['sub_activity_name']) ?></div>
                                </td>

                                <!-- 1. KT Training -->
                                <td>
                                    <?php if (!empty($c['training_completed'])): ?>
                                        <span class="badge bg-success-subtle text-success border border-success px-2 py-1 fs-8">
                                            <i class="fas fa-check me-1"></i>Completed
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 fs-8">
                                            <i class="far fa-clock me-1"></i>Pending
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- 2. PKT Exam -->
                                <td>
                                    <?php if (!empty($c['pkt_passed'])): ?>
                                        <span class="badge bg-success-subtle text-success border border-success px-2 py-1 fs-8">
                                            <i class="fas fa-check me-1"></i>Passed (<?= number_format($c['pkt_score'], 1) ?>%)
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 fs-8">
                                            <i class="far fa-clock me-1"></i>Pending
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- 3. Practice File -->
                                <td>
                                    <?php if (!empty($c['practice_passed'])): ?>
                                        <span class="badge bg-success-subtle text-success border border-success px-2 py-1 fs-8">
                                            <i class="fas fa-check me-1"></i>Approved
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 fs-8">
                                            <i class="far fa-clock me-1"></i>Pending
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Overall Progress Bar -->
                                <td>
                                    <div style="min-width: 150px;">
                                        <div class="d-flex justify-content-between align-items-center mb-1 fs-8">
                                            <span class="fw-bold text-dark"><?= $pPercent ?>% Completed</span>
                                        </div>
                                        <div class="progress shadow-sm" style="height: 6px; border-radius: 3px;">
                                            <div class="progress-bar <?= $barBg ?> progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= $pPercent ?>%;"></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Status & Action -->
                                <td class="text-center">
                                    <?php if (($c['status'] ?? '') === 'Certified'): ?>
                                        <a href="<?= base_url('training/certificate/' . $c['id']) ?>" class="btn btn-success btn-sm fw-bold px-3 py-1 rounded-pill fs-8 text-nowrap shadow-sm" target="_blank">
                                            <i class="fas fa-certificate me-1"></i>View Certificate
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-2 py-1 fs-8 rounded-pill">
                                            <i class="fas fa-spinner fa-spin me-1"></i>In Progress
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Admin Master Certifications Table -->
    <?php if ((is_admin() || is_super_admin()) && !empty($allCertifications)): ?>
        <h5 class="fw-bold text-dark mb-3"><i class="fas fa-users me-2 text-primary"></i>All Employee Certifications Master</h5>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light fs-7">
                            <tr>
                                <th>Employee Code</th>
                                <th>Employee Name</th>
                                <th>Department</th>
                                <th>Certified Sub Activity</th>
                                <th>PKT Score</th>
                                <th>Certified Date</th>
                                <th>Certificate Code</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="fs-7">
                            <?php foreach ($allCertifications as $ac): ?>
                                <tr>
                                    <td class="fw-bold text-muted"><?= htmlspecialchars($ac['user_code']) ?></td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($ac['full_name']) ?></td>
                                    <td><?= htmlspecialchars($ac['department']) ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= htmlspecialchars($ac['activity_name']) ?> &rarr; <?= htmlspecialchars($ac['sub_activity_name']) ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold text-success"><?= number_format($ac['pkt_score'], 1) ?>%</td>
                                    <td><?= date('d M Y', strtotime($ac['certified_at'])) ?></td>
                                    <td><code><?= htmlspecialchars($ac['certificate_code']) ?></code></td>
                                    <td>
                                        <a href="<?= base_url('training/certificate/' . $ac['id']) ?>" class="btn btn-sm btn-outline-success fs-8 fw-bold" target="_blank">
                                            <i class="fas fa-print me-1"></i>Print Certificate
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
