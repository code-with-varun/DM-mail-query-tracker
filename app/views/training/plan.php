<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-graduation-cap text-primary me-2"></i>Training Plan & Knowledge Transfer (KT)</h4>
            <p class="text-muted fs-7 mb-0">DM-Ispark Employee Onboarding & Sub-Activity Learning Modules</p>
        </div>
        <?php if (is_super_admin() || is_admin()): ?>
        <button class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addKtModal">
            <i class="fas fa-upload me-1"></i>Upload KT Training Material
        </button>
        <?php endif; ?>
    </div>

    <!-- KT Modules Cards -->
    <div class="row g-4">
        <?php if (empty($plans)): ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm p-5 text-center bg-white">
                    <i class="fas fa-book-reader fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="fw-bold text-secondary">No Training Modules Assigned Yet</h5>
                    <p class="text-muted fs-7 mb-0">When your supervisor assigns sub-activities or uploads KT documents, they will appear here.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($plans as $p): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100 border-start border-4 <?= $p['is_learned'] ? 'border-success' : 'border-warning' ?>">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-light text-dark border fs-8">
                                    <i class="fas fa-folder me-1 text-primary"></i><?= htmlspecialchars($p['division_name'] ?? 'General') ?>
                                </span>
                                <?php if ($p['is_learned']): ?>
                                    <span class="badge bg-success-subtle text-success border border-success fs-8 fw-bold">
                                        <i class="fas fa-check-circle me-1"></i>Learned
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning fs-8 fw-bold">
                                        <i class="fas fa-clock me-1"></i>Pending Review
                                    </span>
                                <?php endif; ?>
                            </div>

                            <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($p['title']) ?></h6>
                            <p class="text-muted fs-8 mb-2">
                                <strong>Activity:</strong> <?= htmlspecialchars($p['activity_name']) ?> &rarr; <?= htmlspecialchars($p['sub_activity_name']) ?>
                            </p>
                            
                            <p class="text-secondary fs-7 mb-3 flex-grow-1">
                                <?= nl2br(htmlspecialchars($p['description'] ?? 'Download material to review Knowledge Transfer from Senior Executives.')) ?>
                            </p>

                            <div class="border-top pt-3 mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <?php if (!empty($p['kt_document_path'])): ?>
                                        <a href="<?= base_url('training/download-kt/' . $p['id']) ?>" class="btn btn-outline-primary btn-sm fw-bold">
                                            <i class="fas fa-download me-1"></i>Download KT File
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted fs-8"><i class="fas fa-info-circle me-1"></i>Online Notes</span>
                                    <?php endif; ?>
                                    
                                    <span class="fs-8 text-muted">Uploaded by: <?= htmlspecialchars($p['created_by_name'] ?? 'Admin') ?></span>
                                </div>

                                <form action="<?= base_url('training/toggle-kt') ?>" method="POST" class="d-flex align-items-center gap-2 bg-light p-2 rounded">
                                    <input type="hidden" name="plan_id" value="<?= $p['id'] ?>">
                                    <input type="checkbox" name="is_learned" value="1" id="check_<?= $p['id'] ?>" class="form-check-input mt-0" <?= $p['is_learned'] ? 'checked' : '' ?> onchange="this.form.submit()">
                                    <label for="check_<?= $p['id'] ?>" class="form-check-label fs-7 fw-bold text-dark cursor-pointer mb-0">
                                        I have learned & understood this module
                                    </label>
                                </form>
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
