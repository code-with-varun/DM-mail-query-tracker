<div class="container-fluid px-4 py-4">
    <!-- Header Toolbar -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-project-diagram text-primary me-2"></i>Process Lifecycle & Monthwise Tracker</h4>
            <p class="text-muted fs-7 mb-0">12-Stage File Preparation & Query Lifecycle Tracker &bull; Timestamps & Stage Attachments</p>
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
                <button type="submit" class="btn btn-success btn-sm fw-bold">
                    <i class="fas fa-sync-alt me-1"></i>Populate Subtasks for <?= htmlspecialchars($billingMonth) ?>
                </button>
            </form>

            <!-- Export CSV -->
            <a href="<?= base_url('lifecycle/export-excel?month=' . urlencode($billingMonth)) ?>" class="btn btn-outline-dark btn-sm fw-bold">
                <i class="fas fa-file-excel me-1 text-success"></i>Export CSV
            </a>

            <!-- Manual Add Item -->
            <button class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addItemModal">
                <i class="fas fa-plus-circle me-1"></i>Add Lifecycle Item
            </button>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="<?= base_url('lifecycle') ?>" method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="month" value="<?= htmlspecialchars($billingMonth) ?>">

                <div class="col-md-3">
                    <div class="position-relative">
                        <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-secondary"></i>
                        <input type="text" name="search" class="form-control form-control-sm ps-5" placeholder="Search ticket #, sub-activity..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- All Ticket Types --</option>
                        <option value="File Preparation" <?= ($filters['ticket_type'] ?? '') === 'File Preparation' ? 'selected' : '' ?>>File Preparation</option>
                        <option value="Rerun" <?= ($filters['ticket_type'] ?? '') === 'Rerun' ? 'selected' : '' ?>>Rerun Task</option>
                        <option value="Query Ticket" <?= ($filters['ticket_type'] ?? '') === 'Query Ticket' ? 'selected' : '' ?>>Query Ticket</option>
                        <option value="Task Ticket" <?= ($filters['ticket_type'] ?? '') === 'Task Ticket' ? 'selected' : '' ?>>Task Ticket</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="stage" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- All Lifecycle Stages --</option>
                        <?php 
                            $stages = [
                                'Input Received', 'Maker Queue', 'Checker Queue', 'Error Log',
                                'Maker Checker Rework', 'Delivery', 'Acknowledgement', 'Approval Queue',
                                'Uploader Queue', 'Dump Sharing', 'Consol Vendor Tracker', 'Closed'
                            ];
                            foreach ($stages as $st):
                        ?>
                            <option value="<?= $st ?>" <?= ($filters['stage'] ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 text-end">
                    <a href="<?= base_url('lifecycle?month=' . urlencode($billingMonth)) ?>" class="btn btn-light btn-sm border">Reset Filters</a>
                    <button type="submit" class="btn btn-secondary btn-sm fw-bold">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Lifecycle Fresh Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 border-0 fs-8">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 110px;">ID / TICKET</th>
                            <th>SUB ACTIVITY & HIERARCHY</th>
                            <th style="width: 100px;">MONTH</th>
                            <th style="width: 140px;">MAKER</th>
                            <th style="width: 140px;">CHECKER</th>
                            <th style="min-width: 250px;">LIFECYCLE STAGE PROGRESS</th>
                            <th style="width: 160px;">STAGE TIMESTAMPS</th>
                            <th class="text-end" style="width: 130px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 bg-white">
                                    <i class="fas fa-project-diagram fs-1 text-muted mb-3 d-block"></i>
                                    <h6 class="fw-bold text-secondary">No Lifecycle Items Found for Month <?= htmlspecialchars($billingMonth) ?></h6>
                                    <p class="text-muted fs-8 mb-3">Click <strong>"Populate Subtasks for <?= htmlspecialchars($billingMonth) ?>"</strong> above to automatically copy active subtasks into this tracker.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <?php
                                    $currStage = $item['current_stage'];
                                    $badgeClass = 'bg-primary';
                                    if ($currStage === 'Closed') $badgeClass = 'bg-success';
                                    elseif ($currStage === 'Error Log' || $currStage === 'Maker Checker Rework') $badgeClass = 'bg-danger';
                                    elseif (in_array($currStage, ['Maker Queue', 'Checker Queue', 'Approval Queue', 'Uploader Queue'])) $badgeClass = 'bg-warning text-dark';
                                    elseif (in_array($currStage, ['Delivery', 'Acknowledgement', 'Dump Sharing', 'Consol Vendor Tracker'])) $badgeClass = 'bg-info text-dark';
                                ?>
                                <tr>
                                    <!-- Ticket ID / Number -->
                                    <td>
                                        <strong class="text-dark d-block"><?= htmlspecialchars($item['ticket_number']) ?></strong>
                                        <span class="badge bg-light text-dark border fs-9"><?= htmlspecialchars($item['ticket_type']) ?></span>
                                    </td>

                                    <!-- Sub-Activity, Activity & Division -->
                                    <td>
                                        <div class="fw-bold text-dark fs-7"><?= htmlspecialchars($item['sub_activity_name']) ?></div>
                                        <small class="text-muted d-block fs-8">
                                            <i class="fas fa-sitemap me-1 text-primary"></i><?= htmlspecialchars($item['activity_name']) ?>
                                            &bull; <span class="text-secondary"><?= htmlspecialchars($item['division_name']) ?></span>
                                        </small>
                                    </td>

                                    <!-- Billing Month -->
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1 fs-8">
                                            <i class="fas fa-calendar-alt me-1"></i><?= htmlspecialchars($item['billing_month']) ?>
                                        </span>
                                    </td>

                                    <!-- Maker -->
                                    <td>
                                        <?php if (!empty($item['maker_name'])): ?>
                                            <span class="fw-bold text-dark d-block fs-8"><i class="fas fa-user-edit me-1 text-primary"></i><?= htmlspecialchars($item['maker_name']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted fs-8"><i class="fas fa-minus-circle me-1"></i>Unassigned</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Checker -->
                                    <td>
                                        <?php if (!empty($item['checker_name'])): ?>
                                            <span class="fw-bold text-dark d-block fs-8"><i class="fas fa-user-check me-1 text-warning"></i><?= htmlspecialchars($item['checker_name']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted fs-8"><i class="fas fa-minus-circle me-1"></i>Unassigned</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Current Stage & Progress Stepper -->
                                    <td>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge <?= $badgeClass ?> px-3 py-1 fs-8 fw-bold rounded-pill">
                                                <?= htmlspecialchars($currStage) ?>
                                            </span>
                                            <?php if ((int)$item['rework_loop_count'] > 0): ?>
                                                <span class="badge bg-danger rounded-pill" title="Rework loops count">
                                                    <i class="fas fa-sync-alt me-1"></i><?= $item['rework_loop_count'] ?> Rework(s)
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Small Stage Pills Indicator -->
                                        <div class="d-flex align-items-center gap-1 flex-wrap mt-1 fs-9 text-muted">
                                            <span class="px-1 rounded <?= !empty($item['input_received_at']) ? 'bg-success text-white' : 'bg-light border' ?>">1.Input</span> &rarr;
                                            <span class="px-1 rounded <?= !empty($item['maker_completed_at']) ? 'bg-success text-white' : ($currStage==='Maker Queue' ? 'bg-warning text-dark fw-bold' : 'bg-light border') ?>">2.Maker</span> &rarr;
                                            <span class="px-1 rounded <?= !empty($item['checker_completed_at']) ? 'bg-success text-white' : ($currStage==='Checker Queue' ? 'bg-warning text-dark fw-bold' : 'bg-light border') ?>">3.Checker</span> &rarr;
                                            <span class="px-1 rounded <?= !empty($item['delivery_at']) ? 'bg-success text-white' : 'bg-light border' ?>">6.Deliv</span> &rarr;
                                            <span class="px-1 rounded <?= !empty($item['approved_at']) ? 'bg-success text-white' : 'bg-light border' ?>">8.Appr</span> &rarr;
                                            <span class="px-1 rounded <?= $currStage==='Closed' ? 'bg-dark text-white' : 'bg-light border' ?>">12.Closed</span>
                                        </div>
                                    </td>

                                    <!-- Stage Timestamps & Attachments -->
                                    <td>
                                        <?php if ($currStage === 'Closed' && !empty($item['closed_at'])): ?>
                                            <small class="text-success fw-bold d-block"><i class="fas fa-check-double me-1"></i>Closed on <?= date('d M H:i', strtotime($item['closed_at'])) ?></small>
                                        <?php elseif (!empty($item['checker_completed_at'])): ?>
                                            <small class="text-muted d-block"><i class="fas fa-clock me-1"></i>Checked: <?= date('d M H:i', strtotime($item['checker_completed_at'])) ?></small>
                                        <?php elseif (!empty($item['maker_completed_at'])): ?>
                                            <small class="text-muted d-block"><i class="fas fa-clock me-1"></i>Maker: <?= date('d M H:i', strtotime($item['maker_completed_at'])) ?></small>
                                        <?php elseif (!empty($item['input_received_at'])): ?>
                                            <small class="text-muted d-block"><i class="fas fa-clock me-1"></i>Input: <?= date('d M H:i', strtotime($item['input_received_at'])) ?></small>
                                        <?php endif; ?>

                                        <!-- Linked Attachments -->
                                        <div class="mt-1 d-flex gap-1 flex-wrap">
                                            <?php if (!empty($item['input_received_attachment'])): ?>
                                                <a href="<?= base_url($item['input_received_attachment']) ?>" target="_blank" class="badge bg-light text-primary border" title="Input Attachment"><i class="fas fa-paperclip"></i> Input</a>
                                            <?php endif; ?>
                                            <?php if (!empty($item['maker_attachment'])): ?>
                                                <a href="<?= base_url($item['maker_attachment']) ?>" target="_blank" class="badge bg-light text-primary border" title="Maker Attachment"><i class="fas fa-file-export"></i> Maker</a>
                                            <?php endif; ?>
                                            <?php if (!empty($item['checker_attachment'])): ?>
                                                <a href="<?= base_url($item['checker_attachment']) ?>" target="_blank" class="badge bg-light text-primary border" title="Checker Attachment"><i class="fas fa-file-check"></i> Checker</a>
                                            <?php endif; ?>
                                            <?php if (!empty($item['delivery_attachment'])): ?>
                                                <a href="<?= base_url($item['delivery_attachment']) ?>" target="_blank" class="badge bg-light text-success border" title="Delivery Attachment"><i class="fas fa-paper-plane"></i> Deliv</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <!-- Actions -->
                                    <td class="text-end">
                                        <button type="button" class="btn btn-outline-primary btn-sm fs-8 py-1 px-2 btn-update-stage" 
                                                data-id="<?= $item['id'] ?>" 
                                                data-number="<?= htmlspecialchars($item['ticket_number']) ?>" 
                                                data-subname="<?= htmlspecialchars($item['sub_activity_name']) ?>"
                                                data-stage="<?= htmlspecialchars($item['current_stage']) ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#updateStageModal">
                                            <i class="fas fa-step-forward me-1"></i>Update Stage
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

<!-- Modal: Update Stage & Attach File -->
<div class="modal fade" id="updateStageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h6 class="modal-title fw-bold" id="updateStageTitle"><i class="fas fa-step-forward me-2"></i>Advance Lifecycle Stage</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('lifecycle/update_stage') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="lifecycle_id" id="modal_lifecycle_id">
                <input type="hidden" name="current_month" value="<?= htmlspecialchars($billingMonth) ?>">

                <div class="modal-body p-4 fs-8">
                    <div class="alert alert-light border mb-3 p-2">
                        <strong class="d-block text-dark" id="modal_ticket_number">LFC-XXXXX</strong>
                        <small class="text-muted" id="modal_sub_name">Sub Activity Name</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-8 fw-bold">Select New Stage <span class="text-danger">*</span></label>
                        <select name="new_stage" id="modal_new_stage" class="form-select form-select-sm fw-bold" required>
                            <option value="Input Received">1. Input Received</option>
                            <option value="Maker Queue">2. Maker Queue</option>
                            <option value="Checker Queue">3. Checker Queue</option>
                            <option value="Error Log">4. Error Log (Log Error / Defect)</option>
                            <option value="Maker Checker Rework">5. Maker Checker Rework (Loop Return)</option>
                            <option value="Delivery">6. Delivery</option>
                            <option value="Acknowledgement">7. Acknowledgement</option>
                            <option value="Approval Queue">8. Approval Queue</option>
                            <option value="Uploader Queue">9. Uploader Queue</option>
                            <option value="Dump Sharing">10. Dump Sharing</option>
                            <option value="Consol Vendor Tracker">11. Consol Vendor Tracker</option>
                            <option value="Closed">12. Closed (Complete Lifecycle)</option>
                        </select>
                    </div>

                    <div class="mb-3" id="errorDescGroup" style="display:none;">
                        <label class="form-label fs-8 fw-bold text-danger">Error Observation / Description <span class="text-danger">*</span></label>
                        <textarea name="error_description" class="form-control form-control-sm" rows="2" placeholder="Details of error observed during Maker/Checker validation..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-8 fw-bold">Attach Stage Document / File (Optional)</label>
                        <input type="file" name="stage_attachment" class="form-control form-control-sm">
                        <small class="text-muted fs-8">Attach delivery file, error log, dump file, or approval screenshot</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-8 fw-bold">Stage Remarks / Notes</label>
                        <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="Enter notes or updates for this stage..."></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold"><i class="fas fa-save me-1"></i>Update Stage</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Manual Add Lifecycle Item -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white py-3">
                <h6 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Add Lifecycle Line Item</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('lifecycle/create') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="billing_month" value="<?= htmlspecialchars($billingMonth) ?>">

                <div class="modal-body p-4 fs-8">
                    <div class="mb-3">
                        <label class="form-label fs-8 fw-bold">Ticket Type <span class="text-danger">*</span></label>
                        <select name="ticket_type" class="form-select form-select-sm" required>
                            <option value="File Preparation" selected>File Preparation Task</option>
                            <option value="Rerun">Rerun Task</option>
                            <option value="Query Ticket">Query Ticket</option>
                            <option value="Task Ticket">Task Ticket</option>
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
                        <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="Initial setup notes..."></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold"><i class="fas fa-save me-1"></i>Add Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-update-stage').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const num = this.dataset.number;
            const subName = this.dataset.subname;
            const stage = this.dataset.stage;

            document.getElementById('modal_lifecycle_id').value = id;
            document.getElementById('modal_ticket_number').textContent = num;
            document.getElementById('modal_sub_name').textContent = subName;
            document.getElementById('modal_new_stage').value = stage;

            toggleErrorGroup(stage);
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
