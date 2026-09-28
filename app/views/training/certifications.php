<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-award text-primary me-2"></i>Activity Certifications & Competency Dashboard</h4>
            <p class="text-muted fs-7 mb-0">Track onboarding certification progress: Training KT &rarr; PKT Exam &rarr; Practice File Checker Approval</p>
        </div>
    </div>

    <!-- My Certifications Cards -->
    <h5 class="fw-bold text-dark mb-3"><i class="fas fa-user-graduate me-2 text-primary"></i>My Assigned Activity Competency Matrix</h5>

    <div class="row g-4 mb-5">
        <?php if (empty($myCertifications)): ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm p-4 text-center bg-white">
                    <p class="text-muted fs-7 mb-0">No assigned activities found for your user profile. Ask your supervisor to assign handled activities.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($myCertifications as $c): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100 border-start border-4 <?= ($c['status'] ?? '') === 'Certified' ? 'border-success' : 'border-primary' ?>">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-light text-dark border fs-8">
                                    <?= htmlspecialchars($c['division_name'] ?? 'Operations') ?>
                                </span>
                                <?php if (($c['status'] ?? '') === 'Certified'): ?>
                                    <span class="badge bg-success text-white fw-bold fs-8"><i class="fas fa-certificate me-1"></i>CERTIFIED</span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning fw-bold fs-8"><i class="fas fa-spinner fa-spin me-1"></i>IN PROGRESS</span>
                                <?php endif; ?>
                            </div>

                            <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($c['sub_activity_name']) ?></h5>
                            <p class="text-muted fs-8 mb-3">Activity: <?= htmlspecialchars($c['activity_name']) ?></p>

                            <!-- Milestone Checklist -->
                            <div class="bg-light p-3 rounded mb-4 fs-7">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>1. KT Training Plan Items:</span>
                                    <?php if (!empty($c['training_completed'])): ?>
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i>Completed</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Pending</span>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>2. PKT Process Knowledge Test:</span>
                                    <?php if (!empty($c['pkt_passed'])): ?>
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i>Score: <?= number_format($c['pkt_score'], 1) ?>%</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Pending</span>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <span>3. Practice File Validation:</span>
                                    <?php if (!empty($c['practice_passed'])): ?>
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i>Approved</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Pending</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mt-auto">
                                <?php if (($c['status'] ?? '') === 'Certified'): ?>
                                    <a href="<?= base_url('training/certificate/' . $c['id']) ?>" class="btn btn-success btn-sm w-100 fw-bold py-2" target="_blank">
                                        <i class="fas fa-award me-1"></i>View & Print Certificate
                                    </a>
                                <?php else: ?>
                                    <div class="text-center text-muted fs-8">Complete all 3 milestones to unlock certification</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
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
