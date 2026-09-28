<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-graduation-cap text-primary me-2"></i>Training Plan & Knowledge Transfer (KT)</h4>
            <p class="text-muted fs-7 mb-0">DM-Ispark Employee Onboarding & Activity Competency Checklist</p>
        </div>
        <?php if (is_super_admin() || is_admin()): ?>
        <button class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addKtModal">
            <i class="fas fa-upload me-1"></i>Upload KT Training Material
        </button>
        <?php endif; ?>
    </div>

    <!-- Activity Cards Container -->
    <div class="row g-4">
        <?php if (empty($activityCards)): ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm p-5 text-center bg-white">
                    <i class="fas fa-book-reader fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="fw-bold text-secondary">No Activity Training Modules Available</h5>
                    <p class="text-muted fs-7 mb-0">When supervisor assigns activities or configures sub-activities, they will appear here.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($activityCards as $act): ?>
                <?php
                    $totalSub = count($act['sub_activities']);
                    $learnedSub = 0;
                    foreach ($act['sub_activities'] as $sa) {
                        if (!empty($sa['is_learned'])) {
                            $learnedSub++;
                        }
                    }
                    $pct = ($totalSub > 0) ? round(($learnedSub / $totalSub) * 100) : 0;
                ?>
                <div class="col-md-6 col-lg-6">
                    <div class="card border-0 shadow-sm h-100 border-start border-4 <?= ($learnedSub === $totalSub && $totalSub > 0) ? 'border-success' : 'border-primary' ?>">
                        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-light text-dark border fs-8 mb-1">
                                    <i class="fas fa-building me-1 text-primary"></i><?= htmlspecialchars($act['division_name']) ?>
                                </span>
                                <h5 class="fw-bold text-dark mb-0"><?= htmlspecialchars($act['activity_name']) ?></h5>
                            </div>
                            <div class="text-end">
                                <span class="badge <?= ($learnedSub === $totalSub && $totalSub > 0) ? 'bg-success' : 'bg-primary' ?> fs-8 fw-bold">
                                    <?= $learnedSub ?> / <?= $totalSub ?> Sub-Activities Completed (<?= $pct ?>%)
                                </span>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="px-3">
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar <?= ($learnedSub === $totalSub && $totalSub > 0) ? 'bg-success' : 'bg-primary' ?>" role="progressbar" style="width: <?= $pct ?>%;"></div>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <small class="fw-bold text-muted d-block mb-2 text-uppercase fs-8"><i class="fas fa-list-check me-1 text-primary"></i>Sub-Activities Training Checklist:</small>

                            <div class="list-group list-group-flush border rounded">
                                <?php foreach ($act['sub_activities'] as $sa): ?>
                                    <div class="list-group-item p-3 bg-white d-flex justify-content-between align-items-start gap-2 border-bottom">
                                        <div class="d-flex align-items-start gap-3 flex-grow-1">
                                            <form action="<?= base_url('training/toggle-kt') ?>" method="POST" class="mt-1">
                                                <input type="hidden" name="sub_activity_id" value="<?= $sa['sub_activity_id'] ?>">
                                                <input type="checkbox" name="is_learned" value="1" id="sub_check_<?= $sa['sub_activity_id'] ?>" 
                                                       class="form-check-input fs-5 cursor-pointer mt-0" 
                                                       <?= !empty($sa['is_learned']) ? 'checked' : '' ?> 
                                                       onchange="this.form.submit()">
                                            </form>

                                            <div>
                                                <label for="sub_check_<?= $sa['sub_activity_id'] ?>" class="fw-bold text-dark fs-7 cursor-pointer mb-1 d-block">
                                                    <?= htmlspecialchars($sa['sub_activity_name']) ?>
                                                </label>

                                                <?php if (!empty($sa['plan_title'])): ?>
                                                    <p class="text-muted fs-8 mb-1">
                                                        <strong>KT Module:</strong> <?= htmlspecialchars($sa['plan_title']) ?>
                                                        <?php if (!empty($sa['plan_description'])): ?>
                                                            &mdash; <span class="text-secondary"><?= htmlspecialchars($sa['plan_description']) ?></span>
                                                        <?php endif; ?>
                                                    </p>
                                                <?php endif; ?>

                                                <?php if (!empty($sa['is_learned'])): ?>
                                                    <small class="text-success fs-8 fw-bold">
                                                        <i class="fas fa-check-circle me-1"></i>Learned on <?= date('d M Y H:i', strtotime($sa['learned_at'])) ?>
                                                    </small>
                                                <?php else: ?>
                                                    <small class="text-warning fs-8 fw-bold">
                                                        <i class="fas fa-clock me-1"></i>Pending Review
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <?php if (!empty($sa['kt_document_path'])): ?>
                                            <a href="<?= base_url('training/download-kt/' . $sa['plan_id']) ?>" class="btn btn-outline-primary btn-sm fs-8 fw-bold flex-shrink-0">
                                                <i class="fas fa-download me-1"></i>Download KT
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Add KT Material -->
<?php if (is_super_admin() || is_admin()): ?>
<div class="modal fade" id="addKtModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-upload me-2"></i>Upload KT Training Material</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('training/add-kt-module') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Select Target Sub Activity <span class="text-danger">*</span></label>
                        <select name="sub_activity_id" class="form-select" required>
                            <option value="">-- Choose Sub Activity --</option>
                            <?php foreach ($subActivities as $sa): ?>
                                <option value="<?= $sa['id'] ?>">
                                    [<?= htmlspecialchars($sa['division_name'] ?? 'Gen') ?>] <?= htmlspecialchars($sa['activity_name']) ?> &rarr; <?= htmlspecialchars($sa['sub_activity_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Module Title / Topic <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Standard Operating Procedure for Billing Query Handling" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">KT Description / Notes</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Key takeaways and instructions from Senior Executive..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Attach KT Document (PDF, PPT, Word, Excel, Video link)</label>
                        <input type="file" name="kt_document" class="form-control">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold"><i class="fas fa-save me-1"></i>Save KT Module</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
