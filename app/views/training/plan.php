<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-graduation-cap text-primary me-2"></i>Training Plan & Knowledge Transfer (KT)</h4>
            <p class="text-muted fs-7 mb-0">Employee Onboarding Checklist & Skill Alignment Acknowledgment</p>
        </div>
        <?php if (is_super_admin() || is_admin()): ?>
        <button class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addKtModal">
            <i class="fas fa-upload me-1"></i>Upload KT Training Material
        </button>
        <?php endif; ?>
    </div>

    <!-- Skill Matrix Style Activity Accordion List -->
    <div class="row">
        <div class="col-12">
            <?php if (empty($activityCards)): ?>
                <div class="card border-0 shadow-sm p-5 text-center bg-white rounded-4">
                    <i class="fas fa-user-shield fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="fw-bold text-secondary">No Assigned Sub-Activities Found</h5>
                    <p class="text-muted fs-7 mb-0">When your Supervisor/Admin configures your Skill Matrix alignment, assigned activities will automatically appear here for your training acknowledgment.</p>
                </div>
            <?php else: ?>
                <div class="accordion d-flex flex-column gap-3" id="trainingPlanAccordion">
                    <?php foreach ($activityCards as $index => $act): ?>
                        <?php
                            $totalSub = count($act['sub_activities']);
                            $learnedSub = 0;
                            foreach ($act['sub_activities'] as $sa) {
                                if (!empty($sa['is_learned'])) {
                                    $learnedSub++;
                                }
                            }
                            $pct = ($totalSub > 0) ? round(($learnedSub / $totalSub) * 100) : 0;
                            $isComplete = ($learnedSub === $totalSub && $totalSub > 0);
                            $collapseId = "activity_collapse_" . $act['activity_id'];
                        ?>
                        <div class="card border border-light-subtle shadow-sm rounded-3 overflow-hidden">
                            <!-- Activity Accordion Header -->
                            <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between cursor-pointer" 
                                 data-bs-toggle="collapse" 
                                 data-bs-target="#<?= $collapseId ?>" 
                                 aria-expanded="true" 
                                 aria-controls="<?= $collapseId ?>">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center <?= $isComplete ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' ?>" style="width: 38px; height: 38px;">
                                        <i class="fas <?= $isComplete ? 'fa-check-circle' : 'fa-folder-open' ?> fs-6"></i>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge bg-light text-dark border fs-8">
                                                <i class="fas fa-building text-secondary me-1"></i><?= htmlspecialchars($act['division_name']) ?>
                                            </span>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-0"><?= htmlspecialchars($act['activity_name']) ?></h6>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge <?= $isComplete ? 'bg-success' : 'bg-primary' ?> px-3 py-2 fs-8 fw-bold rounded-pill">
                                        <?= $learnedSub ?> / <?= $totalSub ?> Sub-Activities Completed (<?= $pct ?>%)
                                    </span>
                                    <i class="fas fa-chevron-down text-muted accordion-arrow"></i>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="progress rounded-0" style="height: 4px;">
                                <div class="progress-bar <?= $isComplete ? 'bg-success' : 'bg-primary' ?>" role="progressbar" style="width: <?= $pct ?>%;"></div>
                            </div>

                            <!-- Collapsible Table Body (Skill Matrix Table Style) -->
                            <div id="<?= $collapseId ?>" class="collapse show" data-bs-parent="#trainingPlanAccordion">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0 border-top">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 80px;" class="text-center">ACKNOWLEDGE</th>
                                                    <th>SUB-ACTIVITY NAME</th>
                                                    <th style="width: 150px;">ASSIGNED ROLE</th>
                                                    <th style="width: 220px;">KT MATERIAL / DOC</th>
                                                    <th style="width: 200px;">COMPETENCY STATUS</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($act['sub_activities'] as $sa): ?>
                                                    <tr>
                                                        <!-- Acknowledge Checkbox -->
                                                        <td class="text-center py-3">
                                                            <form action="<?= base_url('training/toggle-kt') ?>" method="POST" class="d-inline">
                                                                <input type="hidden" name="sub_activity_id" value="<?= $sa['sub_activity_id'] ?>">
                                                                <input type="checkbox" name="is_learned" value="1" 
                                                                       id="sub_check_<?= $sa['sub_activity_id'] ?>" 
                                                                       class="form-check-input fs-5 cursor-pointer" 
                                                                       <?= !empty($sa['is_learned']) ? 'checked' : '' ?> 
                                                                       onchange="this.form.submit()">
                                                            </form>
                                                        </td>

                                                        <!-- Sub-Activity Name & KT Info -->
                                                        <td>
                                                            <label for="sub_check_<?= $sa['sub_activity_id'] ?>" class="fw-bold text-dark cursor-pointer mb-1 d-block">
                                                                <?= htmlspecialchars($sa['sub_activity_name']) ?>
                                                            </label>
                                                            <?php if (!empty($sa['plan_title'])): ?>
                                                                <small class="text-muted d-block fs-8">
                                                                    <i class="fas fa-book-open me-1 text-primary"></i><strong>KT Module:</strong> <?= htmlspecialchars($sa['plan_title']) ?>
                                                                    <?php if (!empty($sa['plan_description'])): ?>
                                                                        &mdash; <?= htmlspecialchars($sa['plan_description']) ?>
                                                                    <?php endif; ?>
                                                                </small>
                                                            <?php endif; ?>
                                                        </td>

                                                        <!-- Assigned Role Badge (Skill Matrix style) -->
                                                        <td>
                                                            <?php
                                                                $role = $sa['assigned_role'] ?? 'Maker';
                                                                if ($role === 'Checker') {
                                                                    $badgeClass = 'bg-warning-subtle text-warning-emphasis border border-warning';
                                                                } elseif ($role === 'Both') {
                                                                    $badgeClass = 'bg-success-subtle text-success-emphasis border border-success';
                                                                } else {
                                                                    $badgeClass = 'bg-primary-subtle text-primary border border-primary';
                                                                }
                                                            ?>
                                                            <span class="badge <?= $badgeClass ?> px-3 py-1 fs-8 fw-bold rounded-pill">
                                                                <i class="fas fa-user-tag me-1"></i><?= htmlspecialchars($role) ?>
                                                            </span>
                                                        </td>

                                                        <!-- KT Document Download -->
                                                        <td>
                                                            <?php if (!empty($sa['kt_document_path'])): ?>
                                                                <a href="<?= base_url('training/download-kt/' . $sa['plan_id']) ?>" class="btn btn-outline-primary btn-sm fs-8 fw-bold rounded-pill">
                                                                    <i class="fas fa-download me-1"></i>Download KT File
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted fs-8"><i class="fas fa-minus-circle me-1"></i>No Doc Attached</span>
                                                            <?php endif; ?>
                                                        </td>

                                                        <!-- Status & Learned Timestamp -->
                                                        <td>
                                                            <?php if (!empty($sa['is_learned'])): ?>
                                                                <small class="text-success fs-8 fw-bold d-block">
                                                                    <i class="fas fa-check-circle me-1"></i>Learned on <?= date('d M Y H:i', strtotime($sa['learned_at'])) ?>
                                                                </small>
                                                            <?php else: ?>
                                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning fs-8 px-2 py-1 rounded-pill">
                                                                    <i class="fas fa-clock me-1"></i>Pending Acknowledgment
                                                                </span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
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

<style>
.cursor-pointer {
    cursor: pointer;
}
.accordion-arrow {
    transition: transform 0.2s ease;
}
[aria-expanded="false"] .accordion-arrow {
    transform: rotate(-90deg);
}
</style>
