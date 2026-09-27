<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-mail-bulk text-primary me-2"></i>Process Updates Tracker</h4>
            <p class="text-muted fs-7 mb-0">Preserve Outlook process update emails, policy revisions & monthly billing guidelines</p>
        </div>
        <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#newProcessUpdateModal">
            <i class="fas fa-plus-circle me-1"></i>Log Process Update Mail
        </button>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="<?= base_url('tracker/process-updates') ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search title, notes, or file name..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <select name="division_id" id="filter_division_id" class="form-select form-select-sm" onchange="onFilterDivisionChange(this.value)">
                        <option value="">All Divisions</option>
                        <?php foreach ($divisions as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= ($filters['division_id'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['division_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="activity_id" id="filter_activity_id" class="form-select form-select-sm" onchange="onFilterActivityChange(this.value)">
                        <option value="">All Activities</option>
                        <?php foreach ($activities as $a): ?>
                            <option value="<?= $a['id'] ?>" data-div="<?= $a['division_id'] ?>" <?= ($filters['activity_id'] ?? '') == $a['id'] ? 'selected' : '' ?>><?= htmlspecialchars($a['activity_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="sub_activity_id" id="filter_sub_activity_id" class="form-select form-select-sm">
                        <option value="">All Sub-Activities</option>
                        <?php foreach ($subActivities as $sa): ?>
                            <option value="<?= $sa['id'] ?>" data-act="<?= $sa['activity_id'] ?>" <?= ($filters['sub_activity_id'] ?? '') == $sa['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sa['sub_activity_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="month" name="billing_month" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['billing_month'] ?? '') ?>" title="Billing Month">
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold" title="Filter Records"><i class="fas fa-filter"></i></button>
                    <a href="<?= base_url('tracker/process-updates') ?>" class="btn btn-sm btn-light border" title="Reset Filters"><i class="fas fa-undo"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Process Updates Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light align-middle text-nowrap">
                        <tr>
                            <th>Billing Month</th>
                            <th>Title / Subject</th>
                            <th>Division</th>
                            <th>Activity / Sub-Activity</th>
                            <th>Attachment / File</th>
                            <th>Notes</th>
                            <th>Uploaded By & Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($updates as $u): ?>
                        <tr>
                            <td class="text-nowrap">
                                <span class="badge bg-primary fs-8 px-2 py-1"><i class="far fa-calendar-alt me-1"></i><?= htmlspecialchars($u['billing_month']) ?></span>
                            </td>
                            <td class="fw-bold text-dark">
                                <a href="javascript:void(0)" onclick="previewProcessUpdate(<?= $u['id'] ?>)" class="text-dark text-decoration-none hover-primary">
                                    <?= htmlspecialchars($u['title']) ?>
                                </a>
                            </td>
                            <td class="fs-8 text-nowrap">
                                <?php if (!empty($u['division_name'])): ?>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($u['division_name']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">General</span>
                                <?php endif; ?>
                            </td>
                            <td class="fs-8">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($u['activity_name'] ?? 'N/A') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($u['sub_activity_name'] ?? '') ?></small>
                            </td>
                            <td class="fs-8 text-nowrap">
                                <?php if (!empty($u['file_name'])): ?>
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="fas fa-paperclip text-primary"></i>
                                        <span class="fw-bold text-dark" title="<?= htmlspecialchars($u['file_name']) ?>">
                                            <?= htmlspecialchars(substr($u['file_name'], 0, 20)) ?><?= strlen($u['file_name']) > 20 ? '...' : '' ?>
                                        </span>
                                        <span class="badge bg-light text-dark border text-uppercase ms-1"><?= htmlspecialchars($u['file_ext'] ?? 'file') ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted fs-8">No File</span>
                                <?php endif; ?>
                            </td>
                            <td class="fs-8 text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($u['notes'] ?? '') ?>">
                                <?= htmlspecialchars($u['notes'] ?? 'None') ?>
                            </td>
                            <td class="fs-8 text-nowrap text-muted">
                                <div><i class="fas fa-user-circle me-1 text-secondary"></i><?= htmlspecialchars($u['creator_name'] ?? 'System') ?></div>
                                <small><?= format_datetime($u['created_at']) ?></small>
                            </td>
                            <td class="text-end text-nowrap">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-info" onclick="previewProcessUpdate(<?= $u['id'] ?>)" title="Preview Mail / Process Update">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if (!empty($u['file_path'])): ?>
                                        <a href="<?= base_url('tracker/process-updates/download/' . $u['id']) ?>" class="btn btn-outline-success" title="Download Mail Attachment">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (is_super_admin() || is_admin()): ?>
                                    <form action="<?= base_url('tracker/process-updates/delete/' . $u['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this process update record?');">
                                        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                        <button type="submit" class="btn btn-outline-danger" title="Delete Record">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: New Process Update Mail (with Outlook Drag & Drop) -->
<div class="modal fade" id="newProcessUpdateModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('tracker/process-updates/store') ?>" method="POST" enctype="multipart/form-data" id="processUpdateForm">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-mail-bulk me-2"></i>Log Process Update Mail</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    
                    <!-- Outlook Drag & Drop Zone -->
                    <div id="outlookDropzone" class="border border-2 border-dashed border-primary rounded p-4 text-center bg-light mb-3 cursor-pointer" onclick="document.getElementById('outlookFileInput').click();">
                        <i class="fas fa-envelope-open-text fs-1 text-primary d-block mb-2"></i>
                        <strong class="d-block text-dark fs-6">Drag & Drop Outlook Email File Here</strong>
                        <span class="text-muted fs-8">Supports <code>.msg</code>, <code>.eml</code>, <code>.html</code>, <code>.txt</code> or click to browse</span>
                        <input type="file" name="email_file" id="outlookFileInput" class="d-none" onchange="onOutlookFileSelected(this)">
                        <div id="selectedFileNameDisplay" class="mt-2 fw-bold text-success fs-7"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold text-dark">Process Update Title / Subject <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="form_title" class="form-control" placeholder="e.g. October 2026 Agency Billing Revised Slab Rate Update" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fs-7 fw-bold text-dark">Division</label>
                            <select name="division_id" id="modal_division_id" class="form-select" onchange="onModalDivisionChange(this.value)">
                                <option value="">Select Division</option>
                                <?php foreach ($divisions as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['division_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fs-7 fw-bold text-dark">Activity</label>
                            <select name="activity_id" id="modal_activity_id" class="form-select" onchange="onModalActivityChange(this.value)">
                                <option value="">Select Activity</option>
                                <?php foreach ($activities as $a): ?>
                                    <option value="<?= $a['id'] ?>" data-div="<?= $a['division_id'] ?>"><?= htmlspecialchars($a['activity_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fs-7 fw-bold text-dark">Sub-Activity</label>
                            <select name="sub_activity_id" id="modal_sub_activity_id" class="form-select">
                                <option value="">Select Sub-Activity</option>
                                <?php foreach ($subActivities as $sa): ?>
                                    <option value="<?= $sa['id'] ?>" data-act="<?= $sa['activity_id'] ?>"><?= htmlspecialchars($sa['sub_activity_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold text-dark">Billing Month (Year & Month) <span class="text-danger">*</span></label>
                            <input type="month" name="billing_month" class="form-control fw-bold" value="<?= date('Y-m') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold text-dark">Upload Date</label>
                            <input type="text" class="form-control bg-light" value="<?= date('d M Y') ?>" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold text-dark">Notes / Guidelines / Process Revisions Summary</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Enter key process changes, revised SLAs, or billing guidelines detailed in this mail..."></textarea>
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="fas fa-save me-1"></i>Save Process Update Mail
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Mail Preview & Record Details -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold" id="preview_modal_title"><i class="fas fa-mail-bulk text-info me-2"></i>Process Update Mail Preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <small class="text-muted d-block fs-8">Billing Month</small>
                        <span id="preview_billing_month" class="badge bg-primary fs-7"></span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block fs-8">Division / Activity</small>
                        <strong id="preview_hierarchy" class="text-dark fs-7"></strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block fs-8">Uploaded By</small>
                        <span id="preview_creator" class="fw-bold text-dark fs-7"></span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-dark fs-7 mb-1">Process Guidelines / Notes</label>
                    <div id="preview_notes" class="p-3 bg-light border rounded text-dark fs-7" style="white-space: pre-wrap;"></div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fw-bold text-dark fs-7 mb-0">Attached Mail File</label>
                        <a id="preview_download_btn" href="#" class="btn btn-sm btn-outline-success fw-bold"><i class="fas fa-download me-1"></i>Download Original File</a>
                    </div>
                    <div id="preview_file_info" class="fs-8 text-muted mb-2"></div>
                    <div id="preview_content_box" class="border rounded p-3 bg-white font-monospace fs-8" style="max-height: 250px; overflow-y: auto; display: none;"></div>
                </div>

            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Outlook Drag & Drop Handling
document.addEventListener('DOMContentLoaded', function() {
    var dropzone = document.getElementById('outlookDropzone');
    var fileInput = document.getElementById('outlookFileInput');

    if (!dropzone) return;

    ['dragenter', 'dragover'].forEach(function(eventName) {
        dropzone.addEventListener(eventName, function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('bg-primary', 'bg-opacity-10');
        }, false);
    });

    ['dragleave', 'drop'].forEach(function(eventName) {
        dropzone.addEventListener(eventName, function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('bg-primary', 'bg-opacity-10');
        }, false);
    });

    dropzone.addEventListener('drop', function(e) {
        var dt = e.dataTransfer;
        var files = dt.files;

        if (files && files.length > 0) {
            fileInput.files = files;
            onOutlookFileSelected(fileInput);
        }
    });
});

function onOutlookFileSelected(input) {
    var display = document.getElementById('selectedFileNameDisplay');
    var titleInput = document.getElementById('form_title');
    if (input.files && input.files[0]) {
        var file = input.files[0];
        display.innerHTML = '<i class="fas fa-check-circle me-1"></i>Selected: ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
        
        if (titleInput && titleInput.value.trim() === '') {
            var nameWithoutExt = file.name.substring(0, file.name.lastIndexOf('.')) || file.name;
            titleInput.value = nameWithoutExt.replace(/_/g, ' ');
        }
    } else {
        display.innerHTML = '';
    }
}

// Cascading Filter Dropdowns for Modal
function onModalDivisionChange(divId) {
    var actSelect = document.getElementById('modal_activity_id');
    var subSelect = document.getElementById('modal_sub_activity_id');
    
    Array.from(actSelect.options).forEach(function(opt) {
        if (opt.value === '') return;
        var div = opt.getAttribute('data-div');
        opt.style.display = (!divId || div == divId) ? '' : 'none';
    });

    actSelect.value = '';
    subSelect.value = '';
}

function onModalActivityChange(actId) {
    var subSelect = document.getElementById('modal_sub_activity_id');
    Array.from(subSelect.options).forEach(function(opt) {
        if (opt.value === '') return;
        var act = opt.getAttribute('data-act');
        opt.style.display = (!actId || act == actId) ? '' : 'none';
    });

    subSelect.value = '';
}

function onFilterDivisionChange(divId) {
    var actSelect = document.getElementById('filter_activity_id');
    Array.from(actSelect.options).forEach(function(opt) {
        if (opt.value === '') return;
        var div = opt.getAttribute('data-div');
        opt.style.display = (!divId || div == divId) ? '' : 'none';
    });
}

function onFilterActivityChange(actId) {
    var subSelect = document.getElementById('filter_sub_activity_id');
    Array.from(subSelect.options).forEach(function(opt) {
        if (opt.value === '') return;
        var act = opt.getAttribute('data-act');
        opt.style.display = (!actId || act == actId) ? '' : 'none';
    });
}

// Mail Preview AJAX Call
function previewProcessUpdate(id) {
    fetch('<?= base_url('tracker/process-updates/preview/') ?>' + id)
        .then(function(res) { return res.json(); })
        .then(function(res) {
            if (res.success && res.data) {
                var d = res.data;
                document.getElementById('preview_modal_title').innerHTML = '<i class="fas fa-mail-bulk text-info me-2"></i>' + d.title;
                document.getElementById('preview_billing_month').textContent = d.billing_month;
                
                var hier = (d.division_name || 'General') + ' > ' + (d.activity_name || 'N/A');
                if (d.sub_activity_name) hier += ' > ' + d.sub_activity_name;
                document.getElementById('preview_hierarchy').textContent = hier;
                
                document.getElementById('preview_creator').textContent = (d.creator_name || 'System') + ' (' + (d.created_at || '') + ')';
                document.getElementById('preview_notes').textContent = d.notes || 'No process notes recorded.';

                var dlBtn = document.getElementById('preview_download_btn');
                var fileInfo = document.getElementById('preview_file_info');
                var contentBox = document.getElementById('preview_content_box');

                if (d.file_name && d.file_path) {
                    dlBtn.style.display = 'inline-block';
                    dlBtn.href = '<?= base_url('tracker/process-updates/download/') ?>' + d.id;
                    fileInfo.innerHTML = '<i class="fas fa-paperclip me-1"></i>' + d.file_name + ' (' + (d.file_ext ? d.file_ext.toUpperCase() : 'FILE') + ')';

                    if (res.file_content) {
                        contentBox.style.display = 'block';
                        contentBox.textContent = res.file_content;
                    } else {
                        contentBox.style.display = 'none';
                    }
                } else {
                    dlBtn.style.display = 'none';
                    fileInfo.textContent = 'No file attached to this record.';
                    contentBox.style.display = 'none';
                }

                var modal = new bootstrap.Modal(document.getElementById('previewModal'));
                modal.show();
            } else {
                alert('Failed to load process update preview.');
            }
        })
        .catch(function(err) {
            alert('Error fetching preview: ' + err);
        });
}
</script>
