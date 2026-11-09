<div class="container-fluid px-4 py-3">
    <!-- Header Page Title & Action -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Workflow & Queue Management</h4>
            <p class="text-muted fs-7 mb-0">Create and manage process workflows mapped to Mail/Ticket Categories. Assign workflows to sub-activities in the Sub-Activities Master.</p>
        </div>
        <a href="<?= base_url('workflows/builder') ?>" target="_blank" class="btn btn-primary fw-bold shadow-2xs">
            <i class="fas fa-plus-circle me-2"></i>Create New Workflow
        </a>
    </div>

    <!-- Workflows List View Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold text-dark mb-0">
                <i class="fas fa-list-ul text-primary me-2"></i>Configured Process Workflows
            </h6>
            <div class="input-group input-group-sm" style="width: 250px;">
                <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" id="wfSearchInput" class="form-control bg-light border-start-0 fs-8" placeholder="Search workflows..." onkeyup="filterWfTable()">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="workflowsTable">
                <thead class="table-light text-muted fs-8 text-uppercase">
                    <tr>
                        <th style="width: 60px;" class="text-center">#</th>
                        <th>WORKFLOW NAME & DESCRIPTION</th>
                        <th style="width: 220px;">MAPPED CATEGORY</th>
                        <th style="width: 120px;" class="text-center">STAGES</th>
                        <th style="width: 130px;" class="text-center">STATUS</th>
                        <th style="width: 260px;" class="text-center">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="fs-8 fw-semibold">
                    <?php if (empty($workflows)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="fas fa-diagram-project fs-1 text-muted mb-3 d-block"></i>
                            <h6 class="fw-bold text-dark">No Workflows Found</h6>
                            <p class="text-muted fs-8 mb-0">Click 'Create New Workflow' to open the Visual Canvas Builder in a new tab.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($workflows as $idx => $wf): ?>
                    <tr>
                        <td class="text-center text-muted fw-bold"><?= $idx + 1 ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-primary-subtle text-primary p-2 rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                    <i class="fas fa-sitemap fs-6"></i>
                                </div>
                                <div>
                                    <span class="fw-bold text-dark fs-7 d-block mb-0"><?= htmlspecialchars($wf['workflow_name']) ?></span>
                                    <small class="text-muted fs-8 fw-normal"><?= htmlspecialchars($wf['description'] ?: 'No description specified.') ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($wf['category_name'])): ?>
                                <span class="badge bg-info-subtle text-info border border-info px-2 py-1 fs-8">
                                    <i class="fas fa-tag me-1"></i><?= htmlspecialchars($wf['category_name']) ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-light text-muted border px-2 py-1 fs-8">Fallback Workflow</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary rounded-pill px-3 py-1 fs-8">
                                <i class="fas fa-cubes me-1"></i><?= $wf['stage_count'] ?> Stages
                            </span>
                        </td>
                        <td class="text-center">
                            <?php if (!empty($wf['is_default'])): ?>
                                <span class="badge bg-success-subtle text-success border border-success me-1 px-2 py-1 fs-8"><i class="fas fa-star me-1"></i>Default</span>
                            <?php endif; ?>
                            <?php if ($wf['status'] === 'Active'): ?>
                                <span class="badge bg-success-subtle text-success px-2 py-1 fs-8">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1 fs-8">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <a href="<?= base_url('workflows/builder/' . $wf['id']) ?>" target="_blank" class="btn btn-outline-primary btn-sm fw-bold px-3" title="Open Visual Canvas Editor in new tab">
                                    <i class="fas fa-external-link-alt me-1"></i> Visual Canvas
                                </a>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="editWorkflowDetails(<?= $wf['id'] ?>)" title="Edit Name & Category">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm btn-delete-wf" data-id="<?= $wf['id'] ?>" data-name="<?= htmlspecialchars($wf['workflow_name'], ENT_QUOTES) ?>" title="Delete Workflow">
                                    <i class="fas fa-trash-alt"></i>
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

<!-- Modal: Quick Edit Workflow Details -->
<div class="modal fade" id="workflowModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="workflowModalTitle"><i class="fas fa-edit me-2"></i>Edit Workflow</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('workflows/store') ?>" method="POST" id="workflowForm">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="workflow_id" id="wf_id" value="">

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold text-dark">Workflow Name <span class="text-danger">*</span></label>
                        <input type="text" name="workflow_name" id="wf_name" class="form-control" placeholder="e.g. Mail Query Workflow, Standard File Preparation Workflow" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold text-dark">Mapped Mail/Ticket Category</label>
                        <select name="category_id" id="wf_category_id" class="form-select">
                            <option value="">-- None (Apply as fallback workflow) --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold text-dark">Description</label>
                        <textarea name="description" id="wf_description" class="form-control" rows="2" placeholder="Brief workflow description..."></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold text-dark">Status</label>
                            <select name="status" id="wf_status" class="form-select">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="is_default" value="1" id="wf_is_default">
                                <label class="form-check-label fs-8 fw-bold text-dark" for="wf_is_default">
                                    System Default
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-3">
                    <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="fas fa-save me-1"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Delete Workflow Confirmation Modal -->
<div class="modal fade" id="deleteWorkflowModal" tabindex="-1" aria-labelledby="deleteWorkflowModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title fw-bold" id="deleteWorkflowModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Delete Workflow Confirmation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" id="deleteWorkflowForm">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-body p-4 text-center">
                    <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                    <h5 class="fw-bold text-dark">Are you sure?</h5>
                    <p class="text-muted mb-0 fs-8">
                        You are about to delete workflow <strong id="delete_wf_name_text" class="text-dark"></strong>.
                        This action cannot be undone.
                    </p>
                </div>
                <div class="modal-footer bg-light px-4 py-3 justify-content-center border-0 gap-2">
                    <button type="button" class="btn btn-secondary fw-bold px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold px-4 rounded-pill">
                        <i class="fas fa-trash-alt me-1"></i>Confirm Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-delete-wf');
        if (btn) {
            var id = btn.dataset.id;
            var name = btn.dataset.name;
            document.getElementById('deleteWorkflowForm').action = '<?= base_url('workflows/delete/') ?>' + id;
            document.getElementById('delete_wf_name_text').textContent = name;
            
            var modal = new bootstrap.Modal(document.getElementById('deleteWorkflowModal'));
            modal.show();
        }
    });
});

function editWorkflowDetails(id) {
    $.getJSON('<?= base_url('workflows/get/') ?>' + id, function(res) {
        if (!res || !res.success) {
            alert(res ? res.message : 'Error loading workflow');
            return;
        }

        var wf = res.workflow;
        document.getElementById('wf_id').value = wf.id;
        document.getElementById('wf_name').value = wf.workflow_name || '';
        document.getElementById('wf_status').value = wf.status || 'Active';
        document.getElementById('wf_category_id').value = wf.category_id || '';
        document.getElementById('wf_description').value = wf.description || '';
        document.getElementById('wf_is_default').checked = (wf.is_default == 1);

        var modal = new bootstrap.Modal(document.getElementById('workflowModal'));
        modal.show();
    });
}

function filterWfTable() {
    var term = document.getElementById('wfSearchInput').value.toLowerCase().trim();
    var rows = document.querySelectorAll('#workflowsTable tbody tr');
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
