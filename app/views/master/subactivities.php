<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-list-ul text-primary me-2"></i>Sub-activities Master</h4>
            <p class="text-muted fs-7 mb-0">Manage operational sub-activities, default SLA TAT hours, and mapped employee allocations</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('master/export-sub-activities') ?>" class="btn btn-outline-success btn-sm fw-bold">
                <i class="fas fa-file-excel me-1"></i>Export Sub-Activities CSV
            </a>
            <button type="button" class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#subActivityModal">
                <i class="fas fa-plus-circle me-1"></i>New Sub-Activity
            </button>
        </div>
    </div>

    <!-- Sub-Activities Register Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light align-middle text-nowrap">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Sub Activity Name</th>
                            <th>Parent Activity</th>
                            <th>Division</th>
                            <th>Default SLA TAT</th>
                            <th>Occurrence Day</th>
                            <th>Default Mapped Employee</th>
                            <th style="width: 90px;" class="text-end text-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subActivities as $idx => $sa): ?>
                        <tr>
                            <td class="text-muted fs-7"><?= $idx + 1 ?></td>
                            <td class="fw-bold text-dark text-nowrap"><?= htmlspecialchars($sa['sub_activity_name']) ?></td>
                            <td class="text-nowrap"><span class="badge bg-light text-dark border"><?= htmlspecialchars($sa['activity_name']) ?></span></td>
                            <td class="text-nowrap">
                                <?php if (!empty($sa['division_code'])): ?>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info"><?= htmlspecialchars($sa['division_name']) ?> (<?= $sa['division_code'] ?>)</span>
                                <?php else: ?>
                                    <span class="text-muted fs-8">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-nowrap"><span class="badge bg-primary bg-opacity-10 text-primary border border-primary"><?= $sa['default_tat_hours'] ?> Hours</span></td>
                            <td class="text-nowrap">
                                <?php if (!empty($sa['default_occurrence_day'])): ?>
                                    <span class="badge bg-purple bg-opacity-10 text-purple border border-purple fw-bold"><i class="fas fa-calendar-day me-1"></i>Day <?= $sa['default_occurrence_day'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted fs-8">Daily / Ad-hoc</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-nowrap">
                                <?php if (!empty($sa['default_user_name'])): ?>
                                    <span class="fw-bold fs-8 text-dark"><i class="fas fa-user-check me-1 text-success"></i><?= htmlspecialchars($sa['default_user_name']) ?> (<?= $sa['default_user_code'] ?>)</span>
                                <?php else: ?>
                                    <span class="text-muted fs-8">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary btn-edit-sub"
                                            data-id="<?= $sa['id'] ?>"
                                            data-name="<?= htmlspecialchars($sa['sub_activity_name'], ENT_QUOTES) ?>"
                                            data-activity_id="<?= $sa['activity_id'] ?>"
                                            data-division_id="<?= $sa['division_id'] ?? '' ?>"
                                            data-tat="<?= $sa['default_tat_hours'] ?>"
                                            data-occurrence_day="<?= $sa['default_occurrence_day'] ?? '' ?>"
                                            data-user_id="<?= $sa['default_user_id'] ?? '' ?>"
                                            title="Edit Sub-Activity">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="<?= base_url('master/subactivities') ?>" method="POST" class="d-inline mb-0" onsubmit="return confirm('Delete sub-activity <?= htmlspecialchars($sa['sub_activity_name'], ENT_QUOTES) ?>?');">
                                        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $sa['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="fas fa-trash-alt"></i></button>
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

<!-- Modal: Add / Edit Sub-Activity -->
<div class="modal fade" id="subActivityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="subModalTitle"><i class="fas fa-plus-circle me-2"></i>New Sub-Activity</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('master/subactivities') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="id" id="sub_id" value="0">
                <div class="modal-body p-4">
                    <!-- Step 1: Select Division -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">1. Select Division</label>
                        <select name="division_id" id="sub_division_id" class="form-select">
                            <option value="">All Divisions / Auto Division</option>
                            <?php foreach ($divisions as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['division_name']) ?> (<?= $d['code'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted fs-8 d-block mt-1">Selecting a division will filter the parent activities below.</small>
                    </div>

                    <!-- Step 2: Select Parent Activity -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">2. Select Parent Activity <span class="text-danger">*</span></label>
                        <select name="activity_id" id="sub_activity_id_val" class="form-select" required>
                            <option value="">Select Parent Activity</option>
                            <?php foreach ($activities as $act): ?>
                                <option value="<?= $act['id'] ?>" data-division_id="<?= $act['division_id'] ?? '' ?>"><?= htmlspecialchars($act['activity_name']) ?> <?= !empty($act['division_code']) ? '('.$act['division_code'].')' : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Step 3: Sub-Activity Name -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">3. Sub-Activity Name <span class="text-danger">*</span></label>
                        <input type="text" name="sub_activity_name" id="sub_name" class="form-control" placeholder="e.g. Contract Query (24h SLA)" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">SLA TAT (Hours) <span class="text-danger">*</span></label>
                            <input type="number" name="default_tat_hours" id="sub_tat" class="form-control" value="24" min="1" max="720" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Occurrence Day</label>
                            <input type="number" name="default_occurrence_day" id="sub_occurrence_day" class="form-control" placeholder="1-31" min="1" max="31">
                            <small class="text-muted fs-8">Day of month (e.g. 23)</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Default Mapped Employee</label>
                        <select name="default_user_id" id="sub_user_id" class="form-select">
                            <option value="">Unassigned (Open Pool)</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['full_name']) ?> (<?= $emp['user_code'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted fs-8 d-block mt-1">When creating a ticket under this sub-activity, this employee will be automatically allocated by default.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4"><i class="fas fa-save me-1"></i>Save Sub-Activity</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var divisionSelect = document.getElementById('sub_division_id');
    var activitySelect = document.getElementById('sub_activity_id_val');

    function filterActivitiesByDivision() {
        var selectedDivId = divisionSelect.value;
        var options = activitySelect.querySelectorAll('option');

        options.forEach(function(opt) {
            if (opt.value === "") return;
            var optDivId = opt.getAttribute('data-division_id');
            if (!selectedDivId || !optDivId || optDivId == selectedDivId) {
                opt.style.display = "";
            } else {
                opt.style.display = "none";
            }
        });

        // Reset activity selection if hidden
        var selectedOption = activitySelect.options[activitySelect.selectedIndex];
        if (selectedOption && selectedOption.style.display === "none") {
            activitySelect.value = "";
        }
    }

    if (divisionSelect) {
        divisionSelect.addEventListener('change', filterActivitiesByDivision);
    }

    document.querySelectorAll('.btn-edit-sub').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('subModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Sub-Activity';
            document.getElementById('sub_id').value = this.dataset.id;
            document.getElementById('sub_name').value = this.dataset.name;
            document.getElementById('sub_division_id').value = this.dataset.division_id;
            
            filterActivitiesByDivision();
            document.getElementById('sub_activity_id_val').value = this.dataset.activity_id;

            document.getElementById('sub_tat').value = this.dataset.tat;
            document.getElementById('sub_occurrence_day').value = this.dataset.occurrence_day;
            document.getElementById('sub_user_id').value = this.dataset.user_id;

            var modal = new bootstrap.Modal(document.getElementById('subActivityModal'));
            modal.show();
        });
    });
});
</script>
