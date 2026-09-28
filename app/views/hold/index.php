<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-pause-circle text-danger me-2"></i>Hold & Release Register</h4>
            <p class="text-muted fs-7 mb-0">Audit & manage tickets categorized as Hold Release or currently put ON HOLD</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light align-middle text-nowrap fs-7">
                        <tr>
                            <th>Ticket #</th>
                            <th>Subject</th>
                            <th>Category</th>
                            <th>Pending Reason / Status</th>
                            <th>Allocated To</th>
                            <th>TAT SLA</th>
                            <th class="text-end text-nowrap" style="width: 160px;">Action</th>
                        </tr>
                    </thead>
                    <tbody class="fs-7">
                        <?php if (empty($tickets)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fs-2 mb-2 d-block"></i>
                                    No hold/release tickets found in register.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tickets as $t): ?>
                            <tr>
                                <td class="text-nowrap">
                                    <a href="<?= base_url('tickets/view/' . $t['id']) ?>" class="ticket-no-link fw-bold" title="Click to view ticket details">
                                        <?= htmlspecialchars($t['ticket_number']) ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-bold fs-7 text-dark"><?= htmlspecialchars($t['subject']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($t['from_address']) ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($t['category_name'])): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1 fw-bold"><?= htmlspecialchars($t['category_name']) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border">General</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold fs-7">
                                    <?php if ($t['status'] === 'On Hold'): ?>
                                        <span class="text-danger"><i class="fas fa-pause-circle me-1"></i><?= htmlspecialchars($t['pending_reason'] ?? 'On Hold') ?></span>
                                    <?php elseif ($t['status'] === 'Released'): ?>
                                        <span class="text-success"><i class="fas fa-play-circle me-1"></i>Released Claims</span>
                                    <?php else: ?>
                                        <span class="text-warning-emphasis"><i class="fas fa-clock me-1"></i><?= htmlspecialchars($t['status']) ?> (Hold Category)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-nowrap"><?= htmlspecialchars($t['allocated_user_name'] ?? 'Unassigned') ?></td>
                                <td class="text-nowrap"><?= get_tat_badge($t['tat_datetime'], $t['status']) ?></td>
                                <td class="text-end text-nowrap">
                                    <a href="<?= base_url('tickets/view/' . $t['id']) ?>" class="btn btn-sm btn-outline-secondary p-1 px-2 me-1" title="View Ticket Details"><i class="fas fa-eye"></i></a>
                                    <?php if ($t['status'] === 'On Hold' || (isset($t['category_slug']) && str_contains($t['category_slug'], 'hold'))): ?>
                                        <button type="button" class="btn btn-sm btn-success p-1 px-2 fw-bold" onclick="openReleaseModal(<?= $t['id'] ?>, '<?= htmlspecialchars($t['ticket_number']) ?>')" title="Release Ticket from Hold">
                                            <i class="fas fa-play me-1"></i>Release
                                        </button>
                                    <?php endif; ?>
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

<!-- Modal: Quick Release Ticket -->
<div class="modal fade" id="quickReleaseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-play-circle me-2"></i>Release Ticket From Hold</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('hold/release') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="ticket_id" id="release_ticket_id">
                <div class="modal-body p-4">
                    <p class="fs-7 text-muted mb-3">Releasing ticket: <strong id="release_ticket_number" class="text-dark"></strong></p>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Release Remarks / Actions Taken <span class="text-danger">*</span></label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Explain why this ticket is being released from hold..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm fw-bold"><i class="fas fa-check-circle me-1"></i>Confirm Release Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openReleaseModal(id, ticketNo) {
    document.getElementById('release_ticket_id').value = id;
    document.getElementById('release_ticket_number').innerText = ticketNo;
    var modal = new bootstrap.Modal(document.getElementById('quickReleaseModal'));
    modal.show();
}
</script>
