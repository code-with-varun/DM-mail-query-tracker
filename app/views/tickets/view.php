<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="fw-bold mb-0">Ticket #<?= htmlspecialchars($ticket['ticket_number']) ?></h4>
                <?= get_status_badge($ticket['status']) ?>
                <?= get_tat_badge($ticket['tat_datetime'], $ticket['status']) ?>
            </div>
            <p class="text-muted fs-7 mb-0">Created on <?= format_datetime($ticket['created_at']) ?> by <?= htmlspecialchars($ticket['creator_name'] ?? 'System') ?></p>
        </div>

        <div class="d-flex gap-2">
            <a href="<?= base_url('tickets') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
            
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

    <div class="row g-4">
        <!-- Ticket Information Left Column -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-envelope-open-text text-primary me-2"></i>Query Details</h6>
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

                    <?php if (!empty($attachments)): ?>
                    <div>
                        <h6 class="fw-bold fs-7 text-muted uppercase mb-2">ATTACHMENTS</h6>
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
                        <span class="text-muted">Allocated Employee</span>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($ticket['allocated_user_name'] ?? 'Unassigned') ?></span>
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
