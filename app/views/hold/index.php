<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Hold & Release Register</h4>
            <p class="text-muted fs-7 mb-0">List of Tickets currently put ON HOLD with reasons</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light align-middle text-nowrap">
                        <tr>
                            <th>Ticket #</th>
                            <th>Subject</th>
                            <th>Category</th>
                            <th>Pending Reason / Status</th>
                            <th>Allocated To</th>
                            <th>TAT SLA</th>
                            <th class="text-end text-nowrap" style="width: 130px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $t): ?>
                        <tr>
                            <td class="text-nowrap">
                                <a href="<?= base_url('tickets/view/' . $t['id']) ?>" class="ticket-no-link" title="Click to view ticket details">
                                    <?= htmlspecialchars($t['ticket_number']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold fs-7"><?= htmlspecialchars($t['subject']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($t['from_address']) ?></small>
                            </td>
                            <td>
                                <?php if (!empty($t['category_name'])): ?>
                                    <span class="badge bg-purple text-white px-2 py-1" style="background-color: #6f42c1;"><?= htmlspecialchars($t['category_name']) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark border">General</span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold fs-7">
                                <?php if ($t['status'] === 'On Hold'): ?>
                                    <span class="text-danger"><i class="fas fa-pause-circle me-1"></i><?= htmlspecialchars($t['pending_reason'] ?? 'On Hold') ?></span>
                                <?php elseif ($t['status'] === 'Released'): ?>
                                    <span class="text-info"><i class="fas fa-play-circle me-1"></i>Released Claims</span>
                                <?php else: ?>
                                    <span class="text-secondary"><?= htmlspecialchars($t['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-nowrap"><?= htmlspecialchars($t['allocated_user_name'] ?? 'Unassigned') ?></td>
                            <td class="text-nowrap"><?= get_tat_badge($t['tat_datetime'], $t['status']) ?></td>
                            <td class="text-end text-nowrap">
                                <a href="<?= base_url('tickets/view/' . $t['id']) ?>" class="btn btn-sm btn-outline-primary p-1 px-2" title="View & Release Ticket"><i class="fas fa-play-circle"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
