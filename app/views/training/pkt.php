<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-tasks-alt text-primary me-2"></i>PKT (Process Knowledge Test) Center</h4>
            <p class="text-muted fs-7 mb-0">Attend multiple-choice process knowledge tests for assigned activities & evaluate your score</p>
        </div>
        <?php if (is_super_admin() || is_admin()): ?>
        <a href="<?= base_url('training/manage-pkt') ?>" class="btn btn-dark btn-sm fw-bold">
            <i class="fas fa-cogs me-1"></i>Manage Question Bank & Raise Tests
        </a>
        <?php endif; ?>
    </div>

    <!-- Active Published Tests List -->
    <div class="row g-4">
        <?php if (empty($tests)): ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm p-5 text-center bg-white">
                    <i class="fas fa-clipboard-check fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="fw-bold text-secondary">No PKT Tests Available Right Now</h5>
                    <p class="text-muted fs-7 mb-0">When your admin publishes a PKT test for your assigned sub-activity, it will appear here.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($tests as $t): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100 border-start border-4 <?= $t['is_passed'] ? 'border-success' : ($t['attempt_count'] > 0 ? 'border-danger' : 'border-primary') ?>">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-light text-dark border fs-8">
                                    <i class="fas fa-sitemap me-1 text-primary"></i><?= htmlspecialchars($t['sub_activity_name']) ?>
                                </span>
                                <?php if ($t['is_passed']): ?>
                                    <span class="badge bg-success text-white fw-bold fs-8"><i class="fas fa-trophy me-1"></i>PASSED</span>
                                <?php elseif ($t['attempt_count'] > 0): ?>
                                    <span class="badge bg-danger text-white fw-bold fs-8"><i class="fas fa-redo me-1"></i>RE-ATTEMPT REQUIRED</span>
                                <?php else: ?>
                                    <span class="badge bg-info text-dark fw-bold fs-8"><i class="fas fa-star me-1"></i>READY FOR TEST</span>
                                <?php endif; ?>
                            </div>

                            <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($t['test_title']) ?></h5>
                            <p class="text-muted fs-8 mb-3">Activity: <?= htmlspecialchars($t['activity_name']) ?></p>

                            <div class="bg-light p-3 rounded mb-3 fs-7">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted"><i class="fas fa-question-circle me-1"></i>Total Questions:</span>
                                    <strong class="text-dark"><?= $t['total_questions'] ?> MCQs</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted"><i class="fas fa-stopwatch me-1"></i>Time Limit:</span>
                                    <strong class="text-dark"><?= $t['time_limit_minutes'] ?> Minutes</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted"><i class="fas fa-bullseye me-1"></i>Passing Score:</span>
                                    <strong class="text-success"><?= $t['passing_score'] ?>%</strong>
                                </div>
                                <?php if ($t['attempt_count'] > 0): ?>
                                <div class="d-flex justify-content-between pt-1 border-top">
                                    <span class="text-muted"><i class="fas fa-chart-line me-1"></i>Your Best Score:</span>
                                    <strong class="<?= $t['is_passed'] ? 'text-success' : 'text-danger' ?>"><?= number_format($t['best_score'], 1) ?>%</strong>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="mt-auto">
                                <a href="<?= base_url('training/take-test/' . $t['id']) ?>" class="btn <?= $t['is_passed'] ? 'btn-outline-success' : 'btn-primary' ?> btn-sm w-100 fw-bold py-2">
                                    <i class="fas <?= $t['is_passed'] ? 'fa-redo' : 'fa-play-circle' ?> me-1"></i>
                                    <?= $t['is_passed'] ? 'Re-take PKT Test' : ($t['attempt_count'] > 0 ? 'Retry PKT Test' : 'Attend PKT Test Now') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
