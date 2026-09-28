<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-file-signature text-primary me-2"></i>Practice Files & Checker Validation</h4>
            <p class="text-muted fs-7 mb-0">Submit practice task files, execute Maker-Checker evaluation with Error Logging, and achieve certification</p>
        </div>
        <button class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#submitPracticeModal">
            <i class="fas fa-plus-circle me-1"></i>Submit New Practice File
        </button>
    </div>

    <!-- Tabs for Maker & Checker -->
    <ul class="nav nav-tabs border-bottom mb-4" id="practiceTab" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-bold py-2 px-4" id="my-practice-tab" data-bs-toggle="tab" data-bs-target="#myPracticeTabContent" type="button">
                <i class="fas fa-user-edit me-2 text-primary"></i>My Submitted Practice Tasks (<?= count($myPracticeFiles) ?>)
            </button>
        </li>
        <?php if (is_admin() || is_super_admin()): ?>
        <li class="nav-item">
            <button class="nav-link fw-bold py-2 px-4" id="checker-queue-tab" data-bs-toggle="tab" data-bs-target="#checkerQueueTabContent" type="button">
                <i class="fas fa-user-check me-2 text-warning"></i>Checker Evaluation Queue (<?= count($checkerQueue) ?>)
            </button>
        </li>
        <?php endif; ?>
    </ul>

    <div class="tab-content" id="practiceTabContent">
        <!-- Tab 1: My Submitted Practice Files -->
        <div class="tab-pane fade show active" id="myPracticeTabContent">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light fs-7">
                                <tr>
                                    <th>Task Title</th>
                                    <th>Sub Activity</th>
                                    <th>Practice File</th>
                                    <th>Assigned Checker</th>
                                    <th>Status</th>
                                    <th>Validation / Error Log</th>
                                    <th>Submitted Date</th>
                                </tr>
                            </thead>
                            <tbody class="fs-7">
                                <?php if (empty($myPracticeFiles)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="fas fa-file-upload fs-2 mb-2 d-block"></i>
                                            No practice tasks submitted yet. Click "Submit New Practice File" above to submit your practice work.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($myPracticeFiles as $pf): ?>
                                        <tr>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($pf['task_title']) ?></td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <?= htmlspecialchars($pf['activity_name']) ?> &rarr; <?= htmlspecialchars($pf['sub_activity_name']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($pf['file_path'])): ?>
                                                    <a href="<?= base_url($pf['file_path']) ?>" download class="btn btn-sm btn-outline-secondary fs-8">
                                                        <i class="fas fa-paperclip me-1 text-primary"></i><?= htmlspecialchars($pf['file_name'] ?? 'Practice_File') ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted fs-8">No File</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($pf['checker_name'] ?? 'Unassigned (Open Queue)') ?></td>
                                            <td>
                                                <?php if ($pf['status'] === 'Approved'): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success fw-bold"><i class="fas fa-check-circle me-1"></i>Approved (Passed)</span>
                                                <?php elseif ($pf['status'] === 'Rework Required'): ?>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger fw-bold"><i class="fas fa-exclamation-triangle me-1"></i>Rework Required (Redo)</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning-subtle text-warning border border-warning fw-bold"><i class="fas fa-clock me-1"></i>Submitted to Checker</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($pf['error_observation'])): ?>
                                                    <div class="text-danger fs-8 bg-danger-subtle p-2 rounded border border-danger border-opacity-25">
                                                        <strong><i class="fas fa-bug me-1"></i>Error:</strong> <?= htmlspecialchars($pf['error_observation']) ?><br>
                                                        <span class="text-dark"><?= htmlspecialchars($pf['error_description'] ?? '') ?></span>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted fs-8">&mdash;</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-muted fs-8"><?= date('d M Y H:i', strtotime($pf['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Checker Evaluation Queue (Admin / Checker Login) -->
        <?php if (is_admin() || is_super_admin()): ?>
        <div class="tab-pane fade" id="checkerQueueTabContent">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light fs-7">
                                <tr>
                                    <th>Maker (Trainee)</th>
                                    <th>Task Title</th>
                                    <th>Sub Activity</th>
                                    <th>Practice File</th>
                                    <th>Status</th>
                                    <th>Action / Validate</th>
                                </tr>
                            </thead>
                            <tbody class="fs-7">
                                <?php if (empty($checkerQueue)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">No pending practice files in evaluation queue.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($checkerQueue as $cq): ?>
                                        <tr>
                                            <td class="fw-bold text-dark"><i class="fas fa-user-circle me-1 text-primary"></i><?= htmlspecialchars($cq['maker_name']) ?></td>
                                            <td class="fw-bold"><?= htmlspecialchars($cq['task_title']) ?></td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <?= htmlspecialchars($cq['activity_name']) ?> &rarr; <?= htmlspecialchars($cq['sub_activity_name']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($cq['file_path'])): ?>
                                                    <a href="<?= base_url($cq['file_path']) ?>" download class="btn btn-sm btn-outline-primary fs-8">
                                                        <i class="fas fa-download me-1"></i>Download Practice Work
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted fs-8">No File</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($cq['status'] === 'Approved'): ?>
                                                    <span class="badge bg-success">Approved</span>
                                                <?php elseif ($cq['status'] === 'Rework Required'): ?>
                                                    <span class="badge bg-danger">Rework Required</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark">Pending Checker Review</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-success fw-bold me-1" onclick="openValidateModal(<?= $cq['id'] ?>, 'Approve', '<?= htmlspecialchars($cq['task_title']) ?>')">
                                                    <i class="fas fa-check me-1"></i>Approve
                                                </button>
                                                <button class="btn btn-sm btn-danger fw-bold" onclick="openValidateModal(<?= $cq['id'] ?>, 'Reject', '<?= htmlspecialchars($cq['task_title']) ?>')">
                                                    <i class="fas fa-times me-1"></i>Log Error & Reject
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Submit Practice File -->
<div class="modal fade" id="submitPracticeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-upload me-2"></i>Submit Practice Task File</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('training/submit-practice') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Select Sub Activity <span class="text-danger">*</span></label>
                        <select name="sub_activity_id" class="form-select" required>
                            <option value="">-- Select Sub Activity --</option>
                            <?php foreach ($subActivities as $sa): ?>
                                <option value="<?= $sa['id'] ?>"><?= htmlspecialchars($sa['activity_name']) ?> &rarr; <?= htmlspecialchars($sa['sub_activity_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Practice Task Title <span class="text-danger">*</span></label>
                        <input type="text" name="task_title" class="form-control" placeholder="e.g. Practice Batch #01 - Invoice Entry Verification" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Upload Completed Practice File <span class="text-danger">*</span></label>
                        <input type="file" name="practice_file" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Select Checker / Supervisor (Optional)</label>
                        <select name="checker_id" class="form-select">
                            <option value="">-- Open Queue (Any Admin/Checker) --</option>
                            <?php foreach ($checkers as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['full_name']) ?> (<?= htmlspecialchars($c['role_name']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Trainee Remarks / Notes</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Notes for the checker..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold"><i class="fas fa-paper-plane me-1"></i>Submit to Checker Queue</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Checker Validation & Error Logging -->
<div class="modal fade" id="validateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="valModalTitle"><i class="fas fa-clipboard-check me-2"></i>Checker Evaluation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('training/validate-practice') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="practice_id" id="val_practice_id">
                <input type="hidden" name="action" id="val_action">

                <div class="modal-body p-4">
                    <p class="fs-7 text-muted mb-3">Task: <strong id="val_task_title" class="text-dark"></strong></p>

                    <div id="errorLogFields" style="display: none;">
                        <div class="alert alert-danger fs-8 py-2"><i class="fas fa-exclamation-circle me-1"></i>Logging validation errors will require the maker to redo this practice task.</div>

                        <div class="mb-3">
                            <label class="form-label fs-7 fw-bold">Error Observation <span class="text-danger">*</span></label>
                            <input type="text" name="error_observation" class="form-control" placeholder="e.g. Calculation discrepancy in total billing amount">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fs-7 fw-bold">Detailed Error Description & Correction Guidance</label>
                            <textarea name="error_description" class="form-control" rows="3" placeholder="Provide specific feedback so the maker can correct their work..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fs-7 fw-bold">Error Type</label>
                            <select name="error_type" class="form-select">
                                <option value="Internal" selected>Internal Process Error</option>
                                <option value="External">External / Data Error</option>
                            </select>
                        </div>
                    </div>

                    <div id="approveConfirmMsg" style="display: none;" class="alert alert-success fs-7">
                        <i class="fas fa-check-circle me-2"></i>Confirming approval of this practice task file. This will update the trainee's certification progress.
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="valSubmitBtn" class="btn btn-primary btn-sm fw-bold">Confirm Action</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openValidateModal(id, action, title) {
    document.getElementById('val_practice_id').value = id;
    document.getElementById('val_action').value = action;
    document.getElementById('val_task_title').innerText = title;

    if (action === 'Reject') {
        document.getElementById('valModalTitle').innerHTML = '<i class="fas fa-bug text-danger me-2"></i>Log Validation Error & Reject';
        document.getElementById('errorLogFields').style.display = 'block';
        document.getElementById('approveConfirmMsg').style.display = 'none';
        document.getElementById('valSubmitBtn').className = 'btn btn-danger btn-sm fw-bold';
        document.getElementById('valSubmitBtn').innerText = 'Submit Error Log & Reject';
    } else {
        document.getElementById('valModalTitle').innerHTML = '<i class="fas fa-check-circle text-success me-2"></i>Approve Practice Task';
        document.getElementById('errorLogFields').style.display = 'none';
        document.getElementById('approveConfirmMsg').style.display = 'block';
        document.getElementById('valSubmitBtn').className = 'btn btn-success btn-sm fw-bold';
        document.getElementById('valSubmitBtn').innerText = 'Approve Practice File';
    }

    var modal = new bootstrap.Modal(document.getElementById('validateModal'));
    modal.show();
}
</script>
