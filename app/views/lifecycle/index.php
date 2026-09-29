<div class="container-fluid px-4 py-4">
    <!-- Header Toolbar -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark fs-5"><i class="fas fa-project-diagram text-primary me-2"></i>Process Lifecycle & Monthwise Tracker</h4>
            <p class="text-muted fs-8 mb-0">12-Stage Deliverable File Preparation & Rerun Tracker &bull; Interactive Stepper, Timestamps & Stage Attachments</p>
        </div>
        
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <!-- Billing Month Selector -->
            <form action="<?= base_url('lifecycle') ?>" method="GET" class="d-flex align-items-center gap-2">
                <label class="fs-8 fw-bold text-dark mb-0">Billing Month:</label>
                <input type="month" name="month" class="form-control form-control-sm fw-bold" value="<?= htmlspecialchars($billingMonth) ?>" onchange="this.form.submit()">
            </form>

            <!-- Auto-Populate Subtasks Button -->
            <form action="<?= base_url('lifecycle/populate-subtasks') ?>" method="POST" class="d-inline" onsubmit="return confirm('Populate all active sub-activities and assigned Maker-Checker details for month <?= htmlspecialchars($billingMonth) ?>?');">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="billing_month" value="<?= htmlspecialchars($billingMonth) ?>">
                <button type="submit" class="btn btn-success btn-sm fw-bold fs-8">
                    <i class="fas fa-sync-alt me-1"></i>Populate Active Subtasks for <?= htmlspecialchars($billingMonth) ?>
                </button>
            </form>

            <!-- Export CSV -->
            <a href="<?= base_url('lifecycle/export-excel?month=' . urlencode($billingMonth)) ?>" class="btn btn-outline-dark btn-sm fw-bold fs-8">
                <i class="fas fa-file-excel me-1 text-success"></i>Export CSV
            </a>

            <!-- Manual Add Item -->
            <button class="btn btn-primary btn-sm fw-bold fs-8" data-bs-toggle="modal" data-bs-target="#addItemModal">
                <i class="fas fa-plus-circle me-1"></i>Add Lifecycle Task
            </button>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="card border-0 shadow-sm mb-3 bg-light border-start border-3 border-primary">
        <div class="card-body p-2 px-3">
            <form action="<?= base_url('lifecycle') ?>" method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="month" value="<?= htmlspecialchars($billingMonth) ?>">

                <div class="col-md-3">
                    <div class="position-relative">
                        <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-secondary fs-8"></i>
                        <input type="text" name="search" class="form-control form-control-sm ps-5 fs-8" placeholder="Search ticket #, sub-activity..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <select name="type" class="form-select form-select-sm fs-8" onchange="this.form.submit()">
                        <option value="">-- All Task Types --</option>
                        <option value="File Preparation" <?= ($filters['ticket_type'] ?? '') === 'File Preparation' ? 'selected' : '' ?>>File Preparation Task</option>
                        <option value="Rerun" <?= ($filters['ticket_type'] ?? '') === 'Rerun' ? 'selected' : '' ?>>Rerun Task</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="stage" class="form-select form-select-sm fs-8" onchange="this.form.submit()">
                        <option value="">-- All 12 Lifecycle Stages --</option>
                        <?php 
                            $stagesList = [
                                'Input Received', 'Maker Queue', 'Checker Queue', 'Error Log',
                                'Maker Checker Rework', 'Delivery', 'Acknowledgement', 'Approval Queue',
                                'Uploader Queue', 'Dump Sharing', 'Consol Vendor Tracker', 'Closed'
                            ];
                            foreach ($stagesList as $st):
                        ?>
                            <option value="<?= $st ?>" <?= ($filters['stage'] ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 text-end">
                    <a href="<?= base_url('lifecycle?month=' . urlencode($billingMonth)) ?>" class="btn btn-light btn-sm border fs-8 me-1">Reset</a>
                    <button type="submit" class="btn btn-secondary btn-sm fw-bold fs-8">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Lifecycle Interactive Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 border-0 fs-8">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 100px;">TASK ID</th>
                            <th style="min-width: 220px;">SUB-ACTIVITY & DIVISION</th>
                            <th style="width: 130px;">MAKER</th>
                            <th style="width: 130px;">CHECKER</th>
                            <th>12-STAGE INTERACTIVE LIFECYCLE PIPELINE</th>
                            <th class="text-end" style="width: 150px;">QUICK ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 bg-white">
                                    <i class="fas fa-project-diagram fs-1 text-muted mb-3 d-block"></i>
                                    <h6 class="fw-bold text-secondary">No Tasks Logged for Month <?= htmlspecialchars($billingMonth) ?></h6>
                                    <p class="text-muted fs-8 mb-3">Click <strong>"Populate Active Subtasks for <?= htmlspecialchars($billingMonth) ?>"</strong> above to automatically load active sub-activities and assigned Maker-Checker mappings.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <?php
                                    $currStage = $item['current_stage'];
                                    
                                    // Calculate Next Logical Stage & Button Text
                                    $nextStageMap = [
                                        'Input Received' => ['next' => 'Maker Queue', 'label' => 'Start Maker Queue', 'icon' => 'fa-play', 'btn' => 'btn-primary'],
                                        'Maker Queue' => ['next' => 'Checker Queue', 'label' => 'Submit to Checker', 'icon' => 'fa-arrow-right', 'btn' => 'btn-warning text-dark'],
                                        'Checker Queue' => ['next' => 'Delivery', 'label' => 'Approve & Deliver', 'icon' => 'fa-paper-plane', 'btn' => 'btn-success'],
                                        'Error Log' => ['next' => 'Maker Checker Rework', 'label' => 'Send for Rework', 'icon' => 'fa-sync-alt', 'btn' => 'btn-danger'],
                                        'Maker Checker Rework' => ['next' => 'Checker Queue', 'label' => 'Resubmit to Checker', 'icon' => 'fa-redo', 'btn' => 'btn-warning text-dark'],
                                        'Delivery' => ['next' => 'Acknowledgement', 'label' => 'Mark Acknowledged', 'icon' => 'fa-check-circle', 'btn' => 'btn-info text-dark'],
                                        'Acknowledgement' => ['next' => 'Approval Queue', 'label' => 'Send to Approval', 'icon' => 'fa-file-signature', 'btn' => 'btn-primary'],
                                        'Approval Queue' => ['next' => 'Uploader Queue', 'label' => 'Approve & Upload', 'icon' => 'fa-upload', 'btn' => 'btn-success'],
                                        'Uploader Queue' => ['next' => 'Dump Sharing', 'label' => 'Share Dump File', 'icon' => 'fa-share-alt', 'btn' => 'btn-secondary'],
                                        'Dump Sharing' => ['next' => 'Consol Vendor Tracker', 'label' => 'Update Consol Vendor', 'icon' => 'fa-database', 'btn' => 'btn-dark'],
                                        'Consol Vendor Tracker' => ['next' => 'Closed', 'label' => 'Close Lifecycle', 'icon' => 'fa-check-double', 'btn' => 'btn-success'],
                                        'Closed' => ['next' => 'Closed', 'label' => 'Lifecycle Closed', 'icon' => 'fa-lock', 'btn' => 'btn-light border disabled']
                                    ];
                                    
                                    $actionInfo = $nextStageMap[$currStage] ?? ['next' => 'Closed', 'label' => 'Update Stage', 'icon' => 'fa-step-forward', 'btn' => 'btn-primary'];

                                    // Stage completion status map
                                    $stageCheck = [
                                        'Input Received' => !empty($item['input_received_at']),
                                        'Maker Queue' => !empty($item['maker_completed_at']),
                                        'Checker Queue' => !empty($item['checker_completed_at']),
                                        'Error Log' => !empty($item['error_log_at']),
                                        'Maker Checker Rework' => ((int)$item['rework_loop_count'] > 0),
                                        'Delivery' => !empty($item['delivery_at']),
                                        'Acknowledgement' => !empty($item['acknowledgement_at']),
                                        'Approval Queue' => !empty($item['approved_at']),
                                        'Uploader Queue' => !empty($item['uploaded_at']),
                                        'Dump Sharing' => !empty($item['dump_sharing_at']),
                                        'Consol Vendor Tracker' => !empty($item['consol_vendor_at']),
                                        'Closed' => ($currStage === 'Closed')
                                    ];

                                    // Linked attachments map
                                    $attachments = array_filter([
                                        'Input' => $item['input_received_attachment'],
                                        'Maker' => $item['maker_attachment'],
                                        'Checker' => $item['checker_attachment'],
                                        'Deliv' => $item['delivery_attachment'],
                                        'Approval' => $item['approval_attachment'],
                                        'Dump' => $item['dump_attachment']
                                    ]);
                                ?>
                                <tr class="py-2">
                                    <!-- Task ID & Type -->
                                    <td>
                                        <strong class="text-dark d-block fs-8"><?= htmlspecialchars($item['ticket_number']) ?></strong>
                                        <span class="badge bg-light text-dark border fs-9"><?= htmlspecialchars($item['ticket_type']) ?></span>
                                    </td>

                                    <!-- Sub-Activity & Hierarchy -->
                                    <td>
                                        <div class="fw-bold text-dark fs-7 mb-0"><?= htmlspecialchars($item['sub_activity_name']) ?></div>
                                        <small class="text-muted fs-8 d-block">
                                            <i class="fas fa-sitemap me-1 text-primary"></i><?= htmlspecialchars($item['activity_name']) ?>
                                            &bull; <span class="text-secondary"><?= htmlspecialchars($item['division_name']) ?></span>
                                        </small>

                                        <!-- Attached Files Links -->
                                        <?php if (!empty($attachments)): ?>
                                            <div class="mt-1 d-flex gap-1 flex-wrap">
                                                <?php foreach ($attachments as $attLabel => $attPath): ?>
                                                    <a href="<?= base_url($attPath) ?>" target="_blank" class="badge bg-primary-subtle text-primary border border-primary px-2 py-0 fs-9 text-decoration-none" title="View <?= $attLabel ?> Attachment">
                                                        <i class="fas fa-paperclip me-1"></i><?= $attLabel ?> File
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Maker -->
                                    <td>
                                        <?php if (!empty($item['maker_name'])): ?>
                                            <span class="fw-bold text-dark fs-8 d-block"><i class="fas fa-user-edit me-1 text-primary"></i><?= htmlspecialchars($item['maker_name']) ?></span>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-link text-primary p-0 fs-8 fw-bold text-decoration-none btn-assign-modal" data-id="<?= $item['id'] ?>" data-maker="<?= $item['maker_id'] ?>" data-checker="<?= $item['checker_id'] ?>" data-bs-toggle="modal" data-bs-target="#assignUserModal">
                                                + Assign Maker
                                            </button>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Checker -->
                                    <td>
                                        <?php if (!empty($item['checker_name'])): ?>
                                            <span class="fw-bold text-dark fs-8 d-block"><i class="fas fa-user-check me-1 text-warning"></i><?= htmlspecialchars($item['checker_name']) ?></span>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-link text-warning-emphasis p-0 fs-8 fw-bold text-decoration-none btn-assign-modal" data-id="<?= $item['id'] ?>" data-maker="<?= $item['maker_id'] ?>" data-checker="<?= $item['checker_id'] ?>" data-bs-toggle="modal" data-bs-target="#assignUserModal">
                                                + Assign Checker
                                            </button>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Interactive 12-Stage Pipeline Stepper -->
                                    <td>
                                        <div class="d-flex align-items-center gap-1 flex-wrap py-1">
                                            <?php 
                                                $pipelineStages = [
                                                    'Input Received' => '1.Input',
                                                    'Maker Queue' => '2.Maker',
                                                    'Checker Queue' => '3.Checker',
                                                    'Error Log' => '4.Error',
                                                    'Maker Checker Rework' => '5.Rework',
                                                    'Delivery' => '6.Delivery',
                                                    'Acknowledgement' => '7.Ack',
                                                    'Approval Queue' => '8.Approval',
                                                    'Uploader Queue' => '9.Uploader',
                                                    'Dump Sharing' => '10.Dump',
                                                    'Consol Vendor Tracker' => '11.Vendor',
                                                    'Closed' => '12.Closed'
                                                ];

                                                foreach ($pipelineStages as $stgKey => $stgLabel):
                                                    $isCurrent = ($currStage === $stgKey);
                                                    $isDone = !empty($stageCheck[$stgKey]);
                                                    
                                                    if ($isCurrent) {
                                                        if ($stgKey === 'Error Log' || $stgKey === 'Maker Checker Rework') {
                                                            $pillClass = 'bg-danger text-white fw-bold shadow-sm';
                                                        } else {
                                                            $pillClass = 'bg-primary text-white fw-bold shadow-sm';
                                                        }
                                                    } elseif ($isDone) {
                                                        $pillClass = 'bg-success text-white';
                                                    } else {
                                                        $pillClass = 'bg-light text-muted border';
                                                    }
                                            ?>
                                                <button type="button" 
                                                        class="btn btn-sm p-0 px-2 py-0 fs-9 border-0 rounded-pill cursor-pointer btn-stage-step <?= $pillClass ?>"
                                                        title="Click to jump/update to <?= $stgKey ?>"
                                                        data-id="<?= $item['id'] ?>" 
                                                        data-number="<?= htmlspecialchars($item['ticket_number']) ?>" 
                                                        data-subname="<?= htmlspecialchars($item['sub_activity_name']) ?>"
                                                        data-targetstage="<?= $stgKey ?>"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#updateStageModal">
                                                    <?php if ($isDone): ?><i class="fas fa-check me-1 fs-10"></i><?php endif; ?>
                                                    <?= $stgLabel ?>
                                                </button>
                                                <?php if ($stgKey !== 'Closed'): ?><span class="text-muted fs-9 opacity-50">&rsaquo;</span><?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>

                                    <!-- Quick Action Button -->
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            <button type="button" 
                                                    class="btn btn-sm <?= $actionInfo['btn'] ?> fs-8 fw-bold py-1 px-2 btn-next-stage"
                                                    data-id="<?= $item['id'] ?>" 
                                                    data-number="<?= htmlspecialchars($item['ticket_number']) ?>" 
                                                    data-subname="<?= htmlspecialchars($item['sub_activity_name']) ?>"
                                                    data-targetstage="<?= $actionInfo['next'] ?>"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#updateStageModal">
                                                <i class="fas <?= $actionInfo['icon'] ?> me-1"></i><?= $actionInfo['label'] ?>
                                            </button>
                                        </div>
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

<!-- Modal: Advance / Update Stage & File Upload -->
<div class="modal fade" id="updateStageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h6 class="modal-title fw-bold"><i class="fas fa-step-forward me-2"></i>Update Task Lifecycle Stage</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('lifecycle/update_stage') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="lifecycle_id" id="modal_lifecycle_id">
                <input type="hidden" name="current_month" value="<?= htmlspecialchars($billingMonth) ?>">

                <div class="modal-body p-4 fs-8">
                    <div class="alert alert-light border mb-3 p-2 rounded">
                        <strong class="d-block text-dark fs-7" id="modal_ticket_number">LFC-XXXXX</strong>
                        <small class="text-muted" id="modal_sub_name">Sub Activity Name</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-8 fw-bold">Target Stage <span class="text-danger">*</span></label>
                        <select name="new_stage" id="modal_new_stage" class="form-select form-select-sm fw-bold" required>
                            <option value="Input Received">1. Input Received</option>
                            <option value="Maker Queue">2. Maker Queue</option>
                            <option value="Checker Queue">3. Checker Queue</option>
                            <option value="Error Log">4. Error Log (Defect Logged)</option>
                            <option value="Maker Checker Rework">5. Maker Checker Rework (Loop Return)</option>
                            <option value="Delivery">6. Delivery (Deliverable Dispatched)</option>
                            <option value="Acknowledgement">7. Acknowledgement</option>
                            <option value="Approval Queue">8. Approval Queue</option>
                            <option value="Uploader Queue">9. Uploader Queue</option>
                            <option value="Dump Sharing">10. Dump Sharing</option>
                            <option value="Consol Vendor Tracker">11. Consol Vendor Tracker</option>
                            <option value="Closed">12. Closed (Task Completed)</option>
                        </select>
                    </div>

                    <div class="mb-3" id="errorDescGroup" style="display:none;">
                        <label class="form-label fs-8 fw-bold text-danger">Error Observation / Defect Details <span class="text-danger">*</span></label>
                        <textarea name="error_description" class="form-control form-control-sm" rows="2" placeholder="Details of defect or rework reason..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-8 fw-bold">Attach Stage Document / Output File (Optional)</label>
                        <input type="file" name="stage_attachment" class="form-control form-control-sm">
                        <small class="text-muted fs-8">Supports deliverable files, checker reports, dump files, or approval proofs</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-8 fw-bold">Stage Remarks / Notes</label>
                        <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="Enter status notes or completion details..."></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm fs-8" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fs-8 fw-bold"><i class="fas fa-save me-1"></i>Save Stage Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Quick Assign Maker / Checker -->
<div class="modal fade" id="assignUserModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white py-2 px-3">
                <h6 class="modal-title fs-8 fw-bold"><i class="fas fa-users-cog me-2"></i>Assign Maker / Checker</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('lifecycle/assign_users') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="lifecycle_id" id="assign_lifecycle_id">
                <input type="hidden" name="current_month" value="<?= htmlspecialchars($billingMonth) ?>">

                <div class="modal-body p-3 fs-8">
                    <div class="mb-2">
                        <label class="form-label fs-8 fw-bold text-dark mb-1">Maker Employee</label>
                        <select name="maker_id" id="assign_maker_id" class="form-select form-select-sm">
                            <option value="">-- Select Maker --</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fs-8 fw-bold text-dark mb-1">Checker Employee</label>
                        <select name="checker_id" id="assign_checker_id" class="form-select form-select-sm">
                            <option value="">-- Select Checker --</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm fs-8" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fs-8 fw-bold"><i class="fas fa-save me-1"></i>Save Assignments</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Manual Add Lifecycle Task -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white py-3">
                <h6 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Add Lifecycle Task</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('lifecycle/create') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="billing_month" value="<?= htmlspecialchars($billingMonth) ?>">

                <div class="modal-body p-4 fs-8">
                    <div class="mb-3">
                        <label class="form-label fs-8 fw-bold">Task Type <span class="text-danger">*</span></label>
                        <select name="ticket_type" class="form-select form-select-sm" required>
                            <option value="File Preparation" selected>File Preparation Task</option>
                            <option value="Rerun">Rerun Task</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-8 fw-bold">Select Sub Activity <span class="text-danger">*</span></label>
                        <select name="sub_activity_id" class="form-select form-select-sm" required>
                            <option value="">-- Choose Sub Activity --</option>
                            <?php 
                                $activityModel = new Activity_model();
                                $allSubs = $activityModel->getAllSubActivities();
                                foreach ($allSubs as $sa):
                            ?>
                                <option value="<?= $sa['id'] ?>">
                                    [<?= htmlspecialchars($sa['division_name'] ?? 'Gen') ?>] <?= htmlspecialchars($sa['activity_name']) ?> &rarr; <?= htmlspecialchars($sa['sub_activity_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-8 fw-bold">Maker Employee</label>
                            <select name="maker_id" class="form-select form-select-sm">
                                <option value="">-- Select Maker --</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fs-8 fw-bold">Checker Employee</label>
                            <select name="checker_id" class="form-select form-select-sm">
                                <option value="">-- Select Checker --</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-8 fw-bold">Initial Remarks / Notes</label>
                        <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="Initial task setup notes..."></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm fs-8" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fs-8 fw-bold"><i class="fas fa-save me-1"></i>Add Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.cursor-pointer {
    cursor: pointer;
}
.btn-stage-step:hover {
    transform: scale(1.05);
    transition: transform 0.15s ease;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Stage Step Click Event (Jump/Update to specific stage)
    document.querySelectorAll('.btn-stage-step, .btn-next-stage').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const num = this.dataset.number;
            const subName = this.dataset.subname;
            const targetStage = this.dataset.targetstage;

            if (id) {
                document.getElementById('modal_lifecycle_id').value = id;
                document.getElementById('modal_ticket_number').textContent = num;
                document.getElementById('modal_sub_name').textContent = subName;
                document.getElementById('modal_new_stage').value = targetStage;

                toggleErrorGroup(targetStage);
            }
        });
    });

    // Quick Assign Maker/Checker Event
    document.querySelectorAll('.btn-assign-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('assign_lifecycle_id').value = this.dataset.id;
            document.getElementById('assign_maker_id').value = this.dataset.maker || '';
            document.getElementById('assign_checker_id').value = this.dataset.checker || '';
        });
    });

    const stageSelect = document.getElementById('modal_new_stage');
    if (stageSelect) {
        stageSelect.addEventListener('change', function() {
            toggleErrorGroup(this.value);
        });
    }

    function toggleErrorGroup(stg) {
        const errorGrp = document.getElementById('errorDescGroup');
        if (errorGrp) {
            if (stg === 'Error Log' || stg === 'Maker Checker Rework') {
                errorGrp.style.display = 'block';
            } else {
                errorGrp.style.display = 'none';
            }
        }
    }
});
</script>
