<div class="container-fluid px-4 py-3">
    <!-- Header Title & Add Action -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Process Queues Master</h4>
            <p class="text-muted fs-7 mb-0">Create and configure process queues/stages that appear on the Visual Workflow Canvas building palette.</p>
        </div>
        <button type="button" class="btn btn-primary fw-bold shadow-2xs" onclick="openQueueModal()">
            <i class="fas fa-plus-circle me-2"></i>Create New Queue Stage
        </button>
    </div>

    <!-- Queues Data Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold text-dark mb-0"><i class="fas fa-cubes text-primary me-2"></i>Configured Process Queues</h6>
            <div class="input-group input-group-sm" style="width: 250px;">
                <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" id="queueTableSearch" class="form-control bg-light border-start-0 fs-8" placeholder="Search queues..." onkeyup="filterQueuesTable()">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="queuesTable">
                <thead class="table-light text-muted fs-8 text-uppercase">
                    <tr>
                        <th style="width: 60px;" class="text-center">ORDER</th>
                        <th style="width: 50px;" class="text-center">ICON</th>
                        <th>QUEUE NAME & DESCRIPTION</th>
                        <th style="width: 150px;">QUEUE CODE</th>
                        <th style="width: 140px;">DEFAULT ROLE</th>
                        <th style="width: 120px;" class="text-center">COLOR BADGE</th>
                        <th style="width: 100px;" class="text-center">STATUS</th>
                        <th style="width: 120px;" class="text-center">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="fs-8 fw-semibold">
                    <?php if (empty($queues)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fas fa-cubes fs-1 text-muted mb-3 d-block"></i>
                            <h6 class="fw-bold text-dark">No Queues Configured</h6>
                            <p class="text-muted fs-8 mb-0">Click 'Create New Queue Stage' to define your first process queue.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($queues as $q): ?>
                    <?php 
                        $cleanName = preg_replace('/^\d+[\.\)]\s*/', '', $q['queue_name']);
                        $cleanDesc = !empty($q['description']) ? preg_replace('/^\d+[\.\)]\s*/', '', $q['description']) : 'No description provided.';
                        $iconClass = !empty($q['icon']) ? $q['icon'] : 'fa-list-alt';
                    ?>
                    <tr>
                        <td class="text-center fw-bold text-secondary"><?= $q['display_order'] ?></td>
                        <td class="text-center">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white flex-shrink-0 shadow-2xs" style="width: 32px; height: 32px; background-color: <?= htmlspecialchars($q['badge_color']) ?>;">
                                <i class="fas <?= htmlspecialchars($iconClass) ?> fs-8"></i>
                            </div>
                        </td>
                        <td>
                            <div>
                                <span class="fw-bold text-dark fs-7 d-block mb-0"><?= htmlspecialchars($cleanName) ?></span>
                                <small class="text-muted fs-8 fw-normal"><?= htmlspecialchars($cleanDesc) ?></small>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border px-2 py-1 fs-8 fw-bold">
                                <?= htmlspecialchars($q['queue_code']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary px-2 py-1 fs-8">
                                <?= htmlspecialchars($q['assigned_role']) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded border bg-light fs-9 fw-bold">
                                <span class="rounded-circle" style="width: 10px; height: 10px; background-color: <?= htmlspecialchars($q['badge_color']) ?>;"></span>
                                <code><?= htmlspecialchars($q['badge_color']) ?></code>
                            </div>
                        </td>
                        <td class="text-center">
                            <?php if ($q['status'] === 'Active'): ?>
                                <span class="badge bg-success-subtle text-success px-2 py-1 fs-8">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1 fs-8">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <button type="button" class="btn btn-outline-primary btn-sm p-1 px-2" onclick="editQueue(<?= $q['id'] ?>)" title="Edit Queue">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="<?= base_url('queues/delete/' . $q['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this queue stage?');">
                                    <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm p-1 px-2" title="Delete Queue">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
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

<!-- Modal: Create / Edit Queue Stage -->
<div class="modal fade" id="queueModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="queueModalTitle"><i class="fas fa-cubes me-2"></i>Create Process Queue</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('queues/store') ?>" method="POST" id="queueForm">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="queue_id" id="modal_queue_id" value="">
                <input type="hidden" name="icon" id="modal_icon" value="fa-list-alt">

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold text-dark">Queue Stage Name <span class="text-danger">*</span></label>
                        <input type="text" name="queue_name" id="modal_queue_name" class="form-control" placeholder="e.g. Input Recv (Client / System)" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold text-dark">Queue Code <span class="text-danger">*</span></label>
                            <input type="text" name="queue_code" id="modal_queue_code" class="form-control" placeholder="e.g. Input, Maker, Checker" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold text-dark">Assigned Role</label>
                            <select name="assigned_role" id="modal_assigned_role" class="form-select">
                                <option value="Maker">Maker</option>
                                <option value="Checker">Checker</option>
                                <option value="Both">Both</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                    </div>

                    <!-- Icon Palette Selector -->
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold text-dark d-flex align-items-center justify-content-between">
                            <span>Queue Icon Palette <span class="text-muted font-normal fs-8">(Click icon to select)</span></span>
                            <span class="badge bg-light text-dark border d-flex align-items-center gap-2 px-2 py-1">
                                Preview:
                                <span class="rounded-circle d-inline-flex align-items-center justify-content-center text-white" id="iconPreviewBadge" style="width: 24px; height: 24px; background-color: #2563eb;">
                                    <i class="fas fa-list-alt" id="iconPreviewI"></i>
                                </span>
                            </span>
                        </label>
                        
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="iconPaletteSearch" class="form-control bg-light border-start-0 fs-8" placeholder="Filter icons..." onkeyup="filterIconPalette()">
                        </div>

                        <div class="p-2 border rounded bg-light" style="max-height: 150px; overflow-y: auto;">
                            <div class="d-flex flex-wrap gap-2" id="iconPaletteGrid">
                                <?php
                                $paletteIcons = [
                                    'fa-inbox', 'fa-pencil-ruler', 'fa-clipboard-check', 'fa-exclamation-triangle',
                                    'fa-sync', 'fa-paper-plane', 'fa-envelope-open-text', 'fa-user-shield',
                                    'fa-cloud-upload-alt', 'fa-database', 'fa-truck-moving', 'fa-check-double',
                                    'fa-tasks', 'fa-list-alt', 'fa-cogs', 'fa-layer-group', 'fa-file-alt',
                                    'fa-user-check', 'fa-history', 'fa-flag', 'fa-bell', 'fa-filter',
                                    'fa-folder-open', 'fa-share', 'fa-check-circle', 'fa-clock', 'fa-search',
                                    'fa-chart-line', 'fa-bug', 'fa-shield-alt', 'fa-envelope', 'fa-calculator',
                                    'fa-file-invoice', 'fa-tag', 'fa-wrench', 'fa-headset'
                                ];
                                foreach ($paletteIcons as $icon):
                                ?>
                                <button type="button" class="btn btn-sm btn-white border icon-btn-option p-2 text-dark" data-icon="<?= $icon ?>" onclick="selectPaletteIcon('<?= $icon ?>')" style="width: 40px; height: 40px;" title="<?= $icon ?>">
                                    <i class="fas <?= $icon ?> fs-6"></i>
                                </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold text-dark">Badge Accent Color</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="modal_color_picker" value="#2563eb" title="Choose color" onchange="updateBadgeColor(this.value)">
                                <input type="text" name="badge_color" id="modal_badge_color" class="form-control" value="#2563eb" onchange="updateBadgeColor(this.value)" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold text-dark">Display Order</label>
                            <input type="number" name="display_order" id="modal_display_order" class="form-control" value="1" min="1">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold text-dark">Description</label>
                        <textarea name="description" id="modal_description" class="form-control" rows="2" placeholder="Brief queue purpose or guidelines..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold text-dark">Status</label>
                        <select name="status" id="modal_status" class="form-select">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer bg-light py-3">
                    <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="fas fa-save me-1"></i>Save Queue Stage
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateBadgeColor(color) {
    document.getElementById('modal_badge_color').value = color;
    document.getElementById('modal_color_picker').value = color;
    document.getElementById('iconPreviewBadge').style.backgroundColor = color;
}

function selectPaletteIcon(iconClass) {
    document.getElementById('modal_icon').value = iconClass;
    document.getElementById('iconPreviewI').className = 'fas ' + iconClass;

    // Highlight selected button
    document.querySelectorAll('.icon-btn-option').forEach(function(btn) {
        if (btn.getAttribute('data-icon') === iconClass) {
            btn.classList.add('btn-primary', 'text-white');
            btn.classList.remove('btn-white', 'text-dark');
        } else {
            btn.classList.remove('btn-primary', 'text-white');
            btn.classList.add('btn-white', 'text-dark');
        }
    });
}

function filterIconPalette() {
    var term = document.getElementById('iconPaletteSearch').value.toLowerCase().trim();
    document.querySelectorAll('.icon-btn-option').forEach(function(btn) {
        var icon = btn.getAttribute('data-icon').toLowerCase();
        if (icon.includes(term)) {
            btn.style.display = '';
        } else {
            btn.style.display = 'none';
        }
    });
}

function openQueueModal() {
    document.getElementById('queueForm').reset();
    document.getElementById('modal_queue_id').value = '';
    updateBadgeColor('#2563eb');
    selectPaletteIcon('fa-list-alt');
    document.getElementById('queueModalTitle').innerHTML = '<i class="fas fa-cubes me-2"></i>Create Process Queue';
    var modal = new bootstrap.Modal(document.getElementById('queueModal'));
    modal.show();
}

function editQueue(id) {
    $.getJSON('<?= base_url('queues/get/') ?>' + id, function(res) {
        if (!res || !res.success) {
            alert(res ? res.message : 'Error loading queue');
            return;
        }

        var q = res.queue;
        document.getElementById('modal_queue_id').value = q.id;
        document.getElementById('modal_queue_name').value = (q.queue_name || '').replace(/^\d+[\.\)]\s*/, '');
        document.getElementById('modal_queue_code').value = q.queue_code || '';
        document.getElementById('modal_assigned_role').value = q.assigned_role || 'Maker';
        updateBadgeColor(q.badge_color || '#2563eb');
        selectPaletteIcon(q.icon || 'fa-list-alt');
        document.getElementById('modal_display_order').value = q.display_order || 1;
        document.getElementById('modal_description').value = (q.description || '').replace(/^\d+[\.\)]\s*/, '');
        document.getElementById('modal_status').value = q.status || 'Active';

        document.getElementById('queueModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Process Queue';
        var modal = new bootstrap.Modal(document.getElementById('queueModal'));
        modal.show();
    });
}

function filterQueuesTable() {
    var term = document.getElementById('queueTableSearch').value.toLowerCase().trim();
    var rows = document.querySelectorAll('#queuesTable tbody tr');
    rows.forEach(function(row) {
        var txt = row.innerText.toLowerCase();
        if (txt.includes(term)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
