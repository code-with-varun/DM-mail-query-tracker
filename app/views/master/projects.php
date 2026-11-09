<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-project-diagram text-primary me-2"></i>Projects & Teams Master</h4>
            <p class="text-muted fs-7 mb-0">Manage organizational Projects, Teams, Manager/TL assignments, and Division mappings</p>
        </div>
        <button type="button" class="btn btn-primary fw-bold" onclick="openCreateProjectModal()">
            <i class="fas fa-plus-circle me-1"></i>Create New Project / Team
        </button>
    </div>

    <!-- Projects Data Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light align-middle text-nowrap">
                        <tr>
                            <th>Code</th>
                            <th>Project / Team Name</th>
                            <th>Operational Model</th>
                            <th>Mapped Divisions</th>
                            <th>Assigned Team Members</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $p): ?>
                        <tr>
                            <td class="fw-bold text-nowrap">
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1 fs-8">
                                    <i class="fas fa-hashtag me-1"></i><?= htmlspecialchars($p['code']) ?>
                                </span>
                            </td>
                            <td class="fw-bold text-dark fs-7">
                                <div><?= htmlspecialchars($p['project_name']) ?></div>
                                <?php if (!empty($p['description'])): ?>
                                    <small class="text-muted fw-normal fs-8"><?= htmlspecialchars($p['description']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-nowrap fs-8">
                                <?php 
                                $opType = $p['operation_type'] ?? 'production_count';
                                if ($opType === 'production_count'): 
                                ?>
                                    <span class="badge bg-success bg-opacity-15 text-success border border-success px-2 py-1">
                                        <i class="fas fa-calculator me-1"></i>Production Count
                                    </span>
                                <?php elseif ($opType === 'ticket_workflow'): ?>
                                    <span class="badge bg-primary bg-opacity-15 text-primary border border-primary px-2 py-1">
                                        <i class="fas fa-ticket-alt me-1"></i>Ticket & Queue Workflow
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning bg-opacity-15 text-dark border border-warning px-2 py-1">
                                        <i class="fas fa-layer-group me-1"></i>Hybrid Setup
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="fs-8">
                                <span class="badge bg-info text-dark border px-2 py-1">
                                    <i class="fas fa-building me-1"></i><?= (int)($p['division_count'] ?? 0) ?> Divisions
                                </span>
                            </td>
                            <td class="fs-8">
                                <span class="badge bg-secondary border px-2 py-1">
                                    <i class="fas fa-users me-1"></i><?= count($p['mapped_user_ids'] ?? []) ?> Members Mapped
                                </span>
                            </td>
                            <td class="text-nowrap">
                                <?php if (($p['status'] ?? 'Active') === 'Active'): ?>
                                    <span class="badge bg-success bg-opacity-15 text-success border border-success px-2 py-1 fs-8"><i class="fas fa-check-circle me-1"></i>Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-15 text-danger border border-danger px-2 py-1 fs-8"><i class="fas fa-ban me-1"></i>Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="fs-8 text-muted text-nowrap">
                                <div><i class="fas fa-user-circle me-1"></i><?= htmlspecialchars($p['creator_name'] ?? 'System') ?></div>
                                <small><?= format_datetime($p['created_at']) ?></small>
                            </td>
                            <td class="text-end text-nowrap">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" 
                                            onclick="openEditProjectModal(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)" 
                                            title="Edit Project / Map Team">
                                        <i class="fas fa-edit"></i> Map / Edit
                                    </button>
                                    <form action="<?= base_url('master/projects') ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete project <?= htmlspecialchars($p['project_name'], ENT_QUOTES) ?>?');">
                                        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger" title="Delete Project">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
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

<!-- Modal: Create / Edit Project -->
<div class="modal fade" id="projectModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('master/projects') ?>" method="POST" id="projectForm">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" id="project_id" value="0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="projectModalTitle"><i class="fas fa-project-diagram me-2"></i>Create Project / Team</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label fs-7 fw-bold text-dark">Project / Team Name <span class="text-danger">*</span></label>
                            <input type="text" name="project_name" id="project_name" class="form-control" placeholder="e.g. Healthcare Claims Processing" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fs-7 fw-bold text-dark">Project Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="project_code" class="form-control text-uppercase" placeholder="e.g. HCP-01" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold text-dark mb-1"><i class="fas fa-sliders-h me-1 text-primary"></i>Operational Hierarchy & Workflow Model <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <input type="radio" class="btn-check" name="operation_type" id="op_type_production" value="production_count" checked>
                                <label class="btn btn-outline-success w-100 p-2 text-start h-100" for="op_type_production">
                                    <div class="fw-bold fs-7"><i class="fas fa-calculator me-1"></i>Production Count</div>
                                    <div class="fs-8 text-muted mt-1 fw-normal">Volume logger, target count & quality % (Ideal for AP/AR, Payroll, Data Entry).</div>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <input type="radio" class="btn-check" name="operation_type" id="op_type_ticket" value="ticket_workflow">
                                <label class="btn btn-outline-primary w-100 p-2 text-start h-100" for="op_type_ticket">
                                    <div class="fw-bold fs-7"><i class="fas fa-ticket-alt me-1"></i>Ticket Workflow</div>
                                    <div class="fs-8 text-muted mt-1 fw-normal">Mail query, queue SLA & ticket assignment (Ideal for Banking/IDFC).</div>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <input type="radio" class="btn-check" name="operation_type" id="op_type_hybrid" value="hybrid">
                                <label class="btn btn-outline-warning w-100 p-2 text-start h-100 text-dark" for="op_type_hybrid">
                                    <div class="fw-bold fs-7"><i class="fas fa-layer-group me-1"></i>Hybrid Setup</div>
                                    <div class="fs-8 text-muted mt-1 fw-normal">Full access to both volume logger & ticket/queue management.</div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold text-dark">Description</label>
                        <textarea name="description" id="project_desc" class="form-control" rows="2" placeholder="Brief project background, client scope, or operational focus..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold text-dark mb-1"><i class="fas fa-users-cog me-1 text-primary"></i>Assign Managers, Team Leads & Employees</label>
                        <div class="border rounded p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                            <?php 
                            $usersList = $users ?? [];
                            if (!empty($usersList)):
                                foreach ($usersList as $u): 
                            ?>
                                <div class="form-check fs-8 mb-1">
                                    <input class="form-check-input user-check-item" type="checkbox" name="mapped_users[]" value="<?= $u['id'] ?>" id="user_check_<?= $u['id'] ?>">
                                    <label class="form-check-label text-dark fw-bold" for="user_check_<?= $u['id'] ?>">
                                        <?= htmlspecialchars($u['full_name']) ?> <span class="text-muted fs-8">(ID: <?= htmlspecialchars($u['user_code']) ?> - Role: <?= htmlspecialchars($u['role_name'] ?? 'User') ?>)</span>
                                    </label>
                                </div>
                            <?php 
                                endforeach;
                            else:
                            ?>
                                <div class="text-muted fs-8 py-2">No users available for mapping.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold text-dark">Status</label>
                        <select name="status" id="project_status" class="form-select">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="fas fa-save me-1"></i>Save Project / Team
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreateProjectModal() {
    document.getElementById('project_id').value = 0;
    document.getElementById('projectModalTitle').innerHTML = '<i class="fas fa-project-diagram me-2"></i>Create Project / Team';
    document.getElementById('project_name').value = '';
    document.getElementById('project_code').value = '';
    document.getElementById('project_desc').value = '';
    document.getElementById('project_status').value = 'Active';
    document.getElementById('op_type_production').checked = true;
    
    document.querySelectorAll('.user-check-item').forEach(cb => cb.checked = false);

    var modal = new bootstrap.Modal(document.getElementById('projectModal'));
    modal.show();
}

function openEditProjectModal(data) {
    document.getElementById('project_id').value = data.id;
    document.getElementById('projectModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Project: ' + data.project_name;
    document.getElementById('project_name').value = data.project_name;
    document.getElementById('project_code').value = data.code;
    document.getElementById('project_desc').value = data.description || '';
    document.getElementById('project_status').value = data.status || 'Active';

    var opType = data.operation_type || 'production_count';
    if (opType === 'ticket_workflow') {
        document.getElementById('op_type_ticket').checked = true;
    } else if (opType === 'hybrid') {
        document.getElementById('op_type_hybrid').checked = true;
    } else {
        document.getElementById('op_type_production').checked = true;
    }

    var mappedIds = data.mapped_user_ids || [];
    document.querySelectorAll('.user-check-item').forEach(cb => {
        cb.checked = mappedIds.includes(parseInt(cb.value));
    });

    var modal = new bootstrap.Modal(document.getElementById('projectModal'));
    modal.show();
}
</script>
