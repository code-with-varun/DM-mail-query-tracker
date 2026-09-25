<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="fw-bold mb-0">Ticket #<?= htmlspecialchars($ticket['ticket_number']) ?></h4>
                <?= get_status_badge($ticket['status']) ?>
                <span class="badge bg-purple text-white fw-bold"><i class="fas fa-layer-group me-1"></i><?= htmlspecialchars($ticket['stage'] ?? 'Maker Phase') ?></span>
                <?= get_tat_badge($ticket['tat_datetime'], $ticket['status']) ?>
            </div>
            <p class="text-muted fs-7 mb-0">Created on <?= format_datetime($ticket['created_at']) ?> by <?= htmlspecialchars($ticket['creator_name'] ?? 'System') ?></p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= base_url('tickets') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
            
            <button type="button" class="btn btn-outline-warning text-dark btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#rescheduleModal">
                <i class="fas fa-clock me-1"></i>Reschedule
            </button>

            <button type="button" class="btn btn-outline-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#reassignModal">
                <i class="fas fa-user-edit me-1"></i>Reassign
            </button>

            <?php if ($ticket['status'] === 'On Hold'): ?>
                <button type="button" class="btn btn-info btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#releaseModal">
                    <i class="fas fa-play-circle me-1"></i>Release Hold
                </button>
            <?php else: ?>
                <button type="button" class="btn btn-dark btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#holdModal">
                    <i class="fas fa-pause-circle me-1"></i>Put On Hold
                </button>
            <?php endif; ?>

            <button type="button" class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#statusModal">
                <i class="fas fa-sync-alt me-1"></i>Update Status
            </button>
        </div>
    </div>

    <!-- Workflow Stage Progress Banner -->
    <div class="card border-0 shadow-sm mb-4 bg-light">
        <div class="card-body p-3">
            <div class="row text-center g-2 align-items-center">
                <?php
                    $curStage = $ticket['stage'] ?? 'Maker Phase';
                ?>
                <div class="col-md-3">
                    <div class="p-2 rounded <?= $curStage === 'Maker Phase' ? 'bg-primary text-white shadow-sm' : 'bg-white border text-muted' ?>">
                        <i class="fas fa-pencil-alt me-1"></i><strong>1. Maker Phase</strong>
                        <div class="fs-8 mt-1"><?= $curStage === 'Maker Phase' ? 'File in Preparation' : 'Completed' ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2 rounded <?= $curStage === 'Checker Phase' ? 'bg-warning text-dark shadow-sm' : 'bg-white border text-muted' ?>">
                        <i class="fas fa-user-check me-1"></i><strong>2. Checker Audit</strong>
                        <div class="fs-8 mt-1"><?= $curStage === 'Checker Phase' ? 'Under Review' : (($curStage === 'Delivery Phase' || $curStage === 'Completed') ? 'Approved' : 'Pending') ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2 rounded <?= $curStage === 'Delivery Phase' ? 'bg-success text-white shadow-sm' : 'bg-white border text-muted' ?>">
                        <i class="fas fa-paper-plane me-1"></i><strong>3. Delivery Phase</strong>
                        <div class="fs-8 mt-1"><?= $curStage === 'Delivery Phase' ? 'Awaiting Dispatch' : ($curStage === 'Completed' ? 'Delivered' : 'Pending') ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-2 rounded <?= $curStage === 'Completed' ? 'bg-dark text-white shadow-sm' : 'bg-white border text-muted' ?>">
                        <i class="fas fa-check-circle me-1"></i><strong>4. Ticket Closed</strong>
                        <div class="fs-8 mt-1"><?= $curStage === 'Completed' ? 'Closed' : 'Pending' ?></div>
                    </div>
                </div>
            </div>

            <!-- Stage Action Buttons -->
            <div class="mt-3 text-end">
                <?php if ($curStage === 'Maker Phase' && $ticket['status'] !== 'Closed'): ?>
                    <button type="button" class="btn btn-warning text-dark fw-bold px-4" data-bs-toggle="modal" data-bs-target="#submitCheckerModal">
                        <i class="fas fa-paper-plane me-1"></i>Submit to Checker Queue
                    </button>
                <?php elseif ($curStage === 'Checker Phase' && $ticket['status'] !== 'Closed'): ?>
                    <button type="button" class="btn btn-success fw-bold px-3 me-2" data-bs-toggle="modal" data-bs-target="#approveCheckerModal">
                        <i class="fas fa-check-circle me-1"></i>Approve Checker Audit
                    </button>
                    <button type="button" class="btn btn-danger fw-bold px-3" data-bs-toggle="modal" data-bs-target="#rejectCheckerModal">
                        <i class="fas fa-times-circle me-1"></i>Reject & Log Error
                    </button>
                <?php elseif ($curStage === 'Delivery Phase' && $ticket['status'] !== 'Closed'): ?>
                    <button type="button" class="btn btn-success fw-bold px-4" data-bs-toggle="modal" data-bs-target="#deliverModal">
                        <i class="fas fa-file-upload me-1"></i>Deliver File & Close Ticket
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Ticket Information Left Column -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-envelope-open-text text-primary me-2"></i>Query / Task Details</h6>
                </div>
                <div class="card-body">
                    <h5 class="fw-bold text-dark mb-3"><?= htmlspecialchars($ticket['subject']) ?></h5>
                    
                    <div class="row g-3 bg-light p-3 rounded mb-4">
                        <div class="col-md-6">
                            <span class="text-muted fs-8 fw-bold d-block">FROM ADDRESS / SENDER</span>
                            <span class="fs-7 fw-bold text-dark"><?= htmlspecialchars($ticket['from_address']) ?></span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted fs-8 fw-bold d-block">RECEIVED DATETIME</span>
                            <span class="fs-7 text-dark"><?= format_datetime($ticket['received_datetime']) ?></span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted fs-8 fw-bold d-block">DIVISION</span>
                            <span class="fs-7 text-dark"><?= htmlspecialchars($ticket['division_name'] ?? 'N/A') ?></span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted fs-8 fw-bold d-block">ACTIVITY</span>
                            <span class="fs-7 text-dark"><?= htmlspecialchars($ticket['activity_name'] ?? 'N/A') ?></span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted fs-8 fw-bold d-block">SUB ACTIVITY</span>
                            <span class="fs-7 text-dark"><?= htmlspecialchars($ticket['sub_activity_name'] ?? 'N/A') ?></span>
                        </div>
                    </div>

                    <?php if (!empty($ticket['remarks'])): ?>
                    <div class="mb-4">
                        <h6 class="fw-bold fs-7 text-muted uppercase">REMARKS / DESCRIPTION</h6>
                        <div class="p-3 bg-white border rounded fs-7 style-preserve">
                            <?= nl2br(htmlspecialchars($ticket['remarks'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($ticket['closure_attachment'])): ?>
                    <div class="mb-4 p-3 bg-success bg-opacity-10 border border-success rounded">
                        <h6 class="fw-bold fs-7 text-success mb-2"><i class="fas fa-file-check me-2"></i>DELIVERY PROOF ATTACHMENT / SCREENSHOT</h6>
                        <a href="<?= base_url($ticket['closure_attachment']) ?>" target="_blank" class="btn btn-sm btn-success fw-bold">
                            <i class="fas fa-download me-1"></i>View Delivery Screenshot (<?= basename($ticket['closure_attachment']) ?>)
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($attachments)): ?>
                    <div>
                        <h6 class="fw-bold fs-7 text-muted uppercase mb-2">TICKET ATTACHMENTS</h6>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php foreach ($attachments as $att): ?>
                                <a href="<?= base_url($att['file_path']) ?>" target="_blank" class="btn btn-sm btn-light border">
                                    <i class="fas fa-paperclip me-1 text-primary"></i><?= htmlspecialchars($att['file_name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Comments & Remarks Timeline -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-comments text-primary me-2"></i>Activity & Remarks History</h6>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('tickets/update-status') ?>" method="POST" class="mb-4">
                        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                        <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                        <input type="hidden" name="status" value="<?= $ticket['status'] ?>">
                        <div class="mb-2">
                            <textarea name="remarks" class="form-control" rows="2" placeholder="Add a comment or remark to this ticket..." required></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-sm btn-primary fw-bold"><i class="fas fa-comment me-1"></i>Add Remark</button>
                        </div>
                    </form>

                    <div class="timeline">
                        <?php if (empty($comments)): ?>
                            <p class="text-muted text-center py-3 fs-7">No remarks logged yet.</p>
                        <?php else: ?>
                            <?php foreach ($comments as $c): ?>
                            <div class="p-3 bg-light rounded mb-2 border-start border-4 border-primary">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold fs-7 text-dark"><?= htmlspecialchars($c['full_name']) ?> (<?= $c['user_code'] ?>)</span>
                                    <small class="text-muted fs-8"><?= format_datetime($c['created_at']) ?></small>
                                </div>
                                <div class="fs-7 text-secondary"><?= nl2br(htmlspecialchars($c['comment'])) ?></div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Summary Right Column -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-info-circle text-primary me-2"></i>MetaData Summary</h6>
                </div>
                <div class="card-body fs-7">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Maker (Allocated)</span>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($ticket['allocated_user_name'] ?? 'Unassigned') ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Scheduled Work Date</span>
                        <span class="fw-bold text-dark"><?= !empty($ticket['scheduled_date']) ? date('d M Y', strtotime($ticket['scheduled_date'])) : 'Today' ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Priority</span>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($ticket['priority']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Target SLA TAT</span>
                        <span class="fw-bold text-dark"><?= format_datetime($ticket['tat_datetime']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Agency Code</span>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($ticket['agency_code'] ?: 'N/A') ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Manager Name</span>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($ticket['manager_name'] ?: 'N/A') ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Replied By</span>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($ticket['replied_user_name'] ?: 'Not Replied') ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Replied Date</span>
                        <span class="fw-bold text-dark"><?= $ticket['replied_datetime'] ? format_datetime($ticket['replied_datetime']) : 'N/A' ?></span>
                    </div>
                </div>
            </div>

            <!-- Hold History Card -->
            <?php if (!empty($holdHistory)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-history text-dark me-2"></i>Hold & Release Log</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush fs-8">
                        <?php foreach ($holdHistory as $hh): ?>
                        <li class="list-group-item">
                            <div class="fw-bold text-dark">Hold Reason: <?= htmlspecialchars($hh['hold_reason']) ?></div>
                            <div class="text-muted">Held on: <?= format_datetime($hh['hold_date']) ?> by <?= htmlspecialchars($hh['held_by_name']) ?></div>
                            <?php if ($hh['release_date']): ?>
                                <div class="text-success mt-1"><i class="fas fa-check me-1"></i>Released on: <?= format_datetime($hh['release_date']) ?> by <?= htmlspecialchars($hh['released_by_name']) ?></div>
                            <?php else: ?>
                                <div class="text-danger mt-1"><i class="fas fa-pause-circle me-1"></i>Currently Active Hold</div>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal: Submit to Checker -->
<div class="modal fade" id="submitCheckerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('tickets/submit-to-checker') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold"><i class="fas fa-paper-plane me-2"></i>Submit to Checker Queue</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Select Designated Checker <span class="text-danger">*</span></label>
                        <select name="checker_id" class="form-select" required>
                            <option value="">Select Checker</option>
                            <?php foreach ($checkers as $chk): ?>
                                <option value="<?= $chk['id'] ?>"><?= htmlspecialchars($chk['full_name']) ?> (<?= $chk['user_code'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Maker Work Remarks / File Notes</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Enter notes for the checker..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold px-4"><i class="fas fa-check me-1"></i>Submit to Checker</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Approve Checker Audit -->
<div class="modal fade" id="approveCheckerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('tickets/checker-action') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <input type="hidden" name="decision" value="approve">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-check-circle me-2"></i>Approve Checker Audit</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-dark">Confirm that file verification and quality audit are clean without errors. This will move the ticket to <strong>Delivery Phase</strong>.</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Audit Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Verified and approved clean file..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold px-4"><i class="fas fa-thumbs-up me-1"></i>Approve Audit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Reject & Log Error -->
<div class="modal fade" id="rejectCheckerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('tickets/checker-action') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <input type="hidden" name="decision" value="reject">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Reject & Log Quality Error</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted fs-7 mb-3">Rejecting will automatically log this observation into the <strong>Error Tracker</strong> and send the ticket back to the Maker.</p>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Error Observation / Category <span class="text-danger">*</span></label>
                            <input type="text" name="error_observation" class="form-control" placeholder="e.g. Calculation Formula Error" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Error Type <span class="text-danger">*</span></label>
                            <select name="error_type" class="form-select" required>
                                <option value="Internal">Internal Error</option>
                                <option value="External">External Error</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Error Description <span class="text-danger">*</span></label>
                        <textarea name="error_description" class="form-control" rows="2" placeholder="Describe the specific error observed..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Recommended Resolution / Solution</label>
                        <textarea name="resolution_solution" class="form-control" rows="2" placeholder="Corrective steps for maker..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold px-4"><i class="fas fa-undo me-1"></i>Log Error & Return to Maker</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Deliver File & Close Ticket -->
<div class="modal fade" id="deliverModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('tickets/deliver') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-paper-plane me-2"></i>Deliver File & Close Ticket</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Upload Delivery Screenshot / Proof Image</label>
                        <input type="file" name="closure_attachment" class="form-control" accept="image/*,.pdf,.doc,.docx">
                        <small class="text-muted fs-8">Attach delivery email screenshot or acknowledgement proof.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Delivery Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="File sent via email to client..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold px-4"><i class="fas fa-check-circle me-1"></i>Deliver & Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Reschedule Ticket -->
<div class="modal fade" id="rescheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('tickets/reschedule') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold"><i class="fas fa-clock me-2"></i>Reschedule Work Date</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Target Scheduled Date <span class="text-danger">*</span></label>
                        <input type="date" name="scheduled_date" class="form-control fw-bold" value="<?= $ticket['scheduled_date'] ?? date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Reschedule reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold px-4"><i class="fas fa-calendar-check me-1"></i>Save Date</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Reassign Ticket -->
<div class="modal fade" id="reassignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('tickets/reassign') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-edit me-2"></i>Reassign Ticket</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Select Employee <span class="text-danger">*</span></label>
                        <select name="allocated_to" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= ($ticket['allocated_to'] == $u['id']) ? 'selected' : '' ?>><?= htmlspecialchars($u['full_name']) ?> (<?= $u['user_code'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Reassignment Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Reassignment reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4"><i class="fas fa-user-check me-1"></i>Confirm Reassign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Update Status -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('tickets/update-status') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Update Ticket Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Status</label>
                        <select name="status" class="form-select" required>
                            <?php foreach (['New', 'Assigned', 'In Progress', 'Pending', 'Waiting for Customer', 'Waiting for Internal Team', 'Completed', 'Closed', 'Cancelled'] as $st): ?>
                                <option value="<?= $st ?>" <?= $ticket['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Status Update Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Enter status update details..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Save Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Put On Hold -->
<div class="modal fade" id="holdModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('tickets/update-status') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <input type="hidden" name="status" value="On Hold">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark">Put Ticket On Hold</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Hold Reason <span class="text-danger">*</span></label>
                        <input type="text" name="remarks" class="form-control" placeholder="e.g. Waiting for client documentation" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark fw-bold">Confirm Hold</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Release Hold -->
<div class="modal fade" id="releaseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('hold/release') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-info">Release Ticket From Hold</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Release Remarks <span class="text-danger">*</span></label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Enter release details..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info fw-bold">Release Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>
