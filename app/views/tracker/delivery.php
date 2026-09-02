<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Output / Delivery Tracker</h4>
            <p class="text-muted fs-7 mb-0">Outgoing Dispatch Register & Delivery Acknowledgements</p>
        </div>
        <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#deliveryModal">
            <i class="fas fa-paper-plane me-1"></i>Log Delivery Dispatch
        </button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light">
                        <tr>
                            <th>Delivery #</th>
                            <th>Ticket #</th>
                            <th>Delivered To</th>
                            <th>Delivery Mode</th>
                            <th>Delivery Date</th>
                            <th>Acknowledgement</th>
                            <th>Attachment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $l): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?= htmlspecialchars($l['delivery_number']) ?></td>
                            <td><?= $l['ticket_number'] ? htmlspecialchars($l['ticket_number']) : 'N/A' ?></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($l['delivered_to']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($l['delivery_mode']) ?></span></td>
                            <td class="fs-8"><?= format_datetime($l['delivery_date']) ?></td>
                            <td>
                                <span class="badge bg-<?= $l['ack_received'] === 'Yes' ? 'success' : ($l['ack_received'] === 'Pending' ? 'warning text-dark' : 'danger') ?>">
                                    Ack: <?= htmlspecialchars($l['ack_received']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($l['attachment_path']): ?>
                                    <a href="<?= base_url($l['attachment_path']) ?>" target="_blank" class="btn btn-sm btn-light border"><i class="fas fa-download"></i> Receipt</a>
                                <?php else: ?>
                                    <span class="text-muted fs-8">None</span>
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

<!-- Modal: Log Delivery -->
<div class="modal fade" id="deliveryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('tracker/delivery') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Log Output / Delivery Dispatch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Associated Ticket (Optional)</label>
                        <select name="ticket_id" class="form-select">
                            <option value="">Select Ticket</option>
                            <?php foreach ($tickets as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= $t['ticket_number'] ?> - <?= htmlspecialchars($t['subject']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Delivered To <span class="text-danger">*</span></label>
                        <input type="text" name="delivered_to" class="form-control" placeholder="Recipient Name or Agency Email" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Delivery Mode</label>
                            <select name="delivery_mode" class="form-select">
                                <option value="Email">Email</option>
                                <option value="Courier">Courier</option>
                                <option value="Portal Upload">Portal Upload</option>
                                <option value="Hand Delivery">Hand Delivery</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Acknowledgement</label>
                            <select name="ack_received" class="form-select">
                                <option value="Pending" selected>Pending</option>
                                <option value="Yes">Yes (Received)</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Delivery Date & Time</label>
                        <input type="datetime-local" name="delivery_date" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Proof of Delivery / Receipt File</label>
                        <input type="file" name="attachment" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Log Delivery</button>
                </div>
            </form>
        </div>
    </div>
</div>
