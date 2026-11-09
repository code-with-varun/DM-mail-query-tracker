<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-graduation-cap text-primary me-2"></i>Training Plan & Knowledge Transfer (KT)</h4>
            <p class="text-muted fs-7 mb-0">Scheduled KT Sessions, Trainer/Trainee Assignments, Meeting Links & Competency Progress</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <?php if (!empty($isAdmin)): ?>
                <!-- Employee Filter for Admins -->
                <?php if (!empty($users)): ?>
                <div class="d-flex align-items-center gap-1 me-2">
                    <span class="text-muted fs-8 fw-bold text-nowrap"><i class="fas fa-filter me-1"></i>Trainee:</span>
                    <select class="form-select form-select-sm shadow-sm" style="min-width: 190px;" onchange="location = this.value;">
                        <option value="<?= base_url('training/plan') ?>">-- All Trainees --</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= base_url('training/plan?user_id=' . $u['id']) ?>" <?= (!empty($filterUserId) && $filterUserId == $u['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['user_code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <button class="btn btn-primary btn-sm fw-bold shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#addKtModal">
                    <i class="fas fa-plus me-1"></i>Schedule Training Session
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Single Unified Training KT & Competency Table -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-list text-primary me-2"></i>Training KT Sessions & Progress Tracker</h6>
            <span class="badge bg-primary px-3 py-1 fs-8 rounded-pill"><?= count($scheduledPlans) ?> Sessions</span>
        </div>

        <div class="card-body p-0">
            <?php if (empty($scheduledPlans)): ?>
                <div class="p-5 text-center bg-white">
                    <i class="fas fa-graduation-cap fs-1 text-muted mb-3 d-block"></i>
                    <h6 class="fw-bold text-secondary mb-1">No Training KT Sessions Found</h6>
                    <p class="text-muted fs-7 mb-0">Admins can click "Schedule Training Session" to assign Trainers, Trainees, and Meeting URLs.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 fs-7 border-top">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;" class="text-center">#</th>
                                <th style="min-width: 220px;">SUB-ACTIVITY & TOPIC</th>
                                <th style="width: 140px;">TRAINER</th>
                                <th style="min-width: 180px;">TRAINEES</th>
                                <th style="width: 150px;">SCHEDULED DATE</th>
                                <th style="width: 120px;">MEETING LINK</th>
                                <th style="width: 110px;">KT DOC</th>
                                <th style="min-width: 210px;">PROGRESS</th>
                                <th style="width: 160px;" class="text-center">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($scheduledPlans as $idx => $plan): ?>
                            <tr>
                                <td class="text-center text-muted fw-bold"><?= $idx + 1 ?></td>

                                <!-- Sub-Activity Name & Topic -->
                                <td>
                                    <div class="fw-bold text-primary mb-1"><?= htmlspecialchars($plan['sub_activity_name']) ?></div>
                                    <div class="text-dark fw-semibold fs-8 mb-1"><?= htmlspecialchars($plan['title']) ?></div>
                                    <small class="text-muted fs-8"><i class="fas fa-building text-secondary me-1"></i><?= htmlspecialchars($plan['division_name'] ?? 'General') ?> &rarr; <?= htmlspecialchars($plan['activity_name'] ?? '') ?></small>
                                </td>

                                <!-- Trainer -->
                                <td>
                                    <span class="badge bg-purple text-white px-2 py-1 fs-8 rounded-pill">
                                        <i class="fas fa-chalkboard-teacher me-1"></i><?= htmlspecialchars($plan['trainer_name'] ?? $plan['creator_name'] ?? 'Trainer') ?>
                                    </span>
                                </td>

                                <!-- Trainees List -->
                                <td>
                                    <?php if (!empty($plan['trainees_list'])): ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach ($plan['trainees_list'] as $tItem): ?>
                                                <span class="badge <?= !empty($tItem['is_acknowledged']) ? 'bg-success-subtle text-success border border-success' : 'bg-light text-dark border' ?> fs-8">
                                                    <?= htmlspecialchars($tItem['full_name']) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border fs-8">All Assigned</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Scheduled Date & Time -->
                                <td>
                                    <?php if (!empty($plan['training_date'])): ?>
                                        <div class="fw-bold text-dark fs-8"><i class="far fa-calendar-alt text-primary me-1"></i><?= date('d M Y', strtotime($plan['training_date'])) ?></div>
                                        <div class="text-muted fs-8"><i class="far fa-clock text-secondary me-1"></i><?= date('h:i A', strtotime($plan['training_date'])) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted fs-8"><i class="fas fa-minus-circle me-1"></i>Flexible</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Meeting URL & Join Button -->
                                <td>
                                    <?php $meetUrl = !empty($plan['effective_meeting_url']) ? $plan['effective_meeting_url'] : (!empty($plan['meeting_url']) ? $plan['meeting_url'] : ($plan['meeting_link'] ?? '')); ?>
                                    <?php if (!empty($meetUrl)): ?>
                                        <a href="<?= htmlspecialchars($meetUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-success btn-sm fw-bold px-3 py-1 rounded-pill fs-8 text-nowrap">
                                            <i class="fas fa-video me-1"></i>Join
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted fs-8">No Link</span>
                                    <?php endif; ?>
                                </td>

                                <!-- KT Material Document -->
                                <td>
                                    <?php if (!empty($plan['kt_document_path'])): ?>
                                        <a href="<?= base_url('training/download-kt/' . $plan['id']) ?>" class="btn btn-outline-primary btn-sm py-0 px-2 fs-8 rounded-pill text-nowrap">
                                            <i class="fas fa-download me-1"></i>Doc
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted fs-8">No Doc</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Workflow Progress Bar (All Roles) -->
                                <td>
                                    <?php if (empty($isAdmin)): ?>
                                        <!-- Trainee View: Personal Milestone Completion Progress Bar -->
                                        <?php
                                            $pPercent = 0;
                                            if (!empty($plan['user_is_acknowledged'])) $pPercent += 34;
                                            if (!empty($plan['pkt_passed'])) $pPercent += 33;
                                            if (!empty($plan['practice_passed'])) $pPercent += 33;

                                            $barBg = 'bg-secondary';
                                            if ($pPercent == 100) $barBg = 'bg-success';
                                            elseif ($pPercent >= 67) $barBg = 'bg-info';
                                            elseif ($pPercent >= 34) $barBg = 'bg-primary';
                                        ?>
                                        <div style="min-width: 180px;">
                                            <div class="d-flex justify-content-between align-items-center mb-1 fs-8">
                                                <span class="fw-bold text-dark"><?= $pPercent ?>% Completed</span>
                                                <span class="fs-8">
                                                    <?php 
                                                    if ($pPercent == 100) echo '<span class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i>Certified</span>';
                                                    elseif (!empty($plan['user_is_acknowledged'])) echo '<span class="text-primary fw-semibold"><i class="fas fa-check me-1"></i>KT Acked</span>';
                                                    else echo '<span class="text-muted"><i class="far fa-clock me-1"></i>Pending Ack</span>';
                                                    ?>
                                                </span>
                                            </div>
                                            <div class="progress shadow-sm" style="height: 8px; border-radius: 4px;">
                                                <div class="progress-bar <?= $barBg ?> progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= $pPercent ?>%;" aria-valuenow="<?= $pPercent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <!-- Admin View: Trainee Acknowledgement Progress Bar -->
                                        <?php
                                            $totalTrainees = max(count($plan['trainees_list']), 1);
                                            $ackCount = $plan['acknowledged_count'] ?? 0;
                                            $pPercent = round(($ackCount / $totalTrainees) * 100);

                                            $barBg = 'bg-secondary';
                                            if ($pPercent == 100) $barBg = 'bg-success';
                                            elseif ($pPercent >= 50) $barBg = 'bg-primary';
                                            elseif ($pPercent > 0) $barBg = 'bg-warning';
                                        ?>
                                        <div style="min-width: 180px;">
                                            <div class="d-flex justify-content-between align-items-center mb-1 fs-8">
                                                <span class="fw-bold text-dark"><?= $pPercent ?>% Overall</span>
                                                <span class="text-muted fs-8 fw-semibold"><?= $ackCount ?> / <?= $totalTrainees ?> Acked</span>
                                            </div>
                                            <div class="progress shadow-sm" style="height: 8px; border-radius: 4px;">
                                                <div class="progress-bar <?= $barBg ?> progress-bar-striped" role="progressbar" style="width: <?= $pPercent ?>%;" aria-valuenow="<?= $pPercent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                 <!-- Action / Acknowledge (Role Restricted & Toggleable) -->
                                <td class="text-center">
                                    <?php if (empty($isAdmin)): ?>
                                        <!-- Employee Acknowledge / Revoke Toggle Action -->
                                        <form action="<?= base_url('training/acknowledge-plan') ?>" method="POST" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                            <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                                            <?php if (!empty($plan['user_is_acknowledged'])): ?>
                                                <button type="submit" class="btn btn-success btn-sm fw-bold px-3 py-1 text-nowrap fs-8 shadow-sm" title="Click to Revoke Acknowledgment">
                                                    <i class="fas fa-check-circle me-1"></i>Acknowledged
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-primary btn-sm fw-bold px-3 py-1 text-nowrap fs-8 shadow-sm">
                                                    <i class="fas fa-check me-1"></i>Acknowledge
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    <?php else: ?>
                                        <!-- Admin Overview Action -->
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <span class="badge bg-light text-dark border fs-8 px-2 py-1">
                                                <?= $plan['acknowledged_count'] ?> / <?= max(count($plan['trainees_list']), 1) ?> Ack
                                            </span>
                                            <form action="<?= base_url('training/delete-plan') ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete this training session?');">
                                                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                                <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                                                <button type="submit" class="btn btn-link text-danger p-0 ms-1" title="Delete Session">
                                                    <i class="fas fa-trash-alt fs-8"></i>
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal: Create Training Plan & KT Session -->
<?php if (!empty($isAdmin)): ?>
<div class="modal fade" id="addKtModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold"><i class="fas fa-calendar-plus me-2"></i>Schedule Training Session</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('training/add-kt-module') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Select Trainer -->
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Select Trainer <span class="text-danger">*</span></label>
                            <select name="trainer_id" class="form-select fs-7" required>
                                <option value="">-- Choose Trainer --</option>
                                <?php if (!empty($users)): ?>
                                    <?php foreach ($users as $u): ?>
                                        <option value="<?= $u['id'] ?>" <?= (current_user()['id'] == $u['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['user_code']) ?>) &mdash; <?= htmlspecialchars($u['department']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Target Sub Activity / Skill -->
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Select Sub Activity / Skill <span class="text-danger">*</span></label>
                            <select name="sub_activity_id" class="form-select fs-7" required>
                                <option value="">-- Choose Sub Activity --</option>
                                <?php foreach ($subActivities as $sa): ?>
                                    <option value="<?= $sa['id'] ?>">
                                        [<?= htmlspecialchars($sa['division_name'] ?? 'Gen') ?>] <?= htmlspecialchars($sa['activity_name']) ?> &rarr; <?= htmlspecialchars($sa['sub_activity_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Multi-Select Searchable Trainees Dropdown -->
                        <div class="col-12">
                            <label class="form-label fs-7 fw-bold">Select Trainees (Multi-Select Employees) <span class="text-danger">*</span></label>
                            <div class="slicer-dropdown-container position-relative">
                                <button type="button" class="slicer-btn w-100 bg-white" onclick="toggleSlicerDropdown('dropdown-trainees', event)">
                                    <span id="btn-trainees-label" class="truncate">Select Trainees (0 Selected)</span>
                                    <i class="fas fa-chevron-down fs-8 text-muted ms-auto"></i>
                                </button>
                                <div id="dropdown-trainees" class="slicer-panel w-100 shadow-lg" style="z-index: 1060;">
                                    <input type="text" oninput="filterSlicerOptions('trainees')" id="search-trainees" placeholder="Search trainee by name, code, department..." class="form-control form-control-sm slicer-search-input mb-2">
                                    <div class="d-flex justify-content-between align-items-center pb-2 mb-2 border-bottom fs-8">
                                        <button type="button" onclick="selectAllSlicer('trainees')" class="btn btn-link p-0 text-primary fw-bold text-decoration-none fs-8">Select All</button>
                                        <button type="button" onclick="clearAllSlicer('trainees')" class="btn btn-link p-0 text-muted text-decoration-none fs-8">Clear</button>
                                    </div>
                                    <div id="list-trainees" class="slicer-list-scroll" style="max-height: 200px;">
                                        <?php if (!empty($users)): ?>
                                            <?php foreach ($users as $u): ?>
                                                <label class="slicer-item-option">
                                                    <input type="checkbox" name="trainee_ids[]" value="<?= $u['id'] ?>" <?= (!empty($filterUserId) && $filterUserId == $u['id']) ? 'checked' : '' ?> onchange="updateSlicerLabel('trainees')">
                                                    <span class="fs-7 fw-semibold text-dark me-1"><?= htmlspecialchars($u['full_name']) ?></span>
                                                    <small class="text-muted fs-8">(<?= htmlspecialchars($u['user_code']) ?> &mdash; <?= htmlspecialchars($u['department']) ?>)</small>
                                                </label>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted fs-8">Search and select all employees who must attend and acknowledge this Knowledge Transfer session.</small>
                        </div>

                        <!-- Training Title -->
                        <div class="col-12">
                            <label class="form-label fs-7 fw-bold">Training Title / Topic <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control fs-7" placeholder="e.g. Urban Recovery Billing Standard Operating Procedure & System KT" required>
                        </div>

                        <!-- Scheduled Training Date & Time -->
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Training Date & Time</label>
                            <input type="datetime-local" name="training_date" class="form-control fs-7">
                        </div>

                        <!-- Meeting Link URL -->
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Meeting Link URL (Google Meet / Teams / Zoom)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fas fa-video text-success"></i></span>
                                <input type="url" name="meeting_url" class="form-control fs-7" placeholder="https://meet.google.com/xyz-abc-123">
                            </div>
                        </div>

                        <!-- KT Document File Upload -->
                        <div class="col-12">
                            <label class="form-label fs-7 fw-bold">Attach KT Training Material (PDF, PPT, Doc, Zip)</label>
                            <input type="file" name="kt_document" class="form-control fs-7">
                        </div>

                        <!-- Description / Agenda -->
                        <div class="col-12">
                            <label class="form-label fs-7 fw-bold">Training Description / Agenda Notes</label>
                            <textarea name="description" class="form-control fs-7" rows="2" placeholder="Outline the KT agenda, key takeaways, and SOP documentation references..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-4"><i class="fas fa-save me-1"></i>Save & Schedule Session</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.cursor-pointer {
    cursor: pointer;
}
.bg-purple {
    background-color: #6f42c1 !important;
}
</style>

<script>
function toggleSlicerDropdown(dropdownId, event) {
    if (event) event.stopPropagation();
    const targetPanel = document.getElementById(dropdownId);
    if (!targetPanel) return;
    const isShowing = targetPanel.classList.contains('show');
    
    document.querySelectorAll('.slicer-panel').forEach(panel => {
        if (panel !== targetPanel) panel.classList.remove('show');
    });

    if (!isShowing) {
        targetPanel.classList.add('show');
        const searchInput = targetPanel.querySelector('.slicer-search-input');
        if (searchInput) {
            setTimeout(() => searchInput.focus(), 50);
        }
    } else {
        targetPanel.classList.remove('show');
    }
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.slicer-dropdown-container')) {
        document.querySelectorAll('.slicer-panel').forEach(panel => panel.classList.remove('show'));
    }
});

function filterSlicerOptions(key) {
    const input = document.getElementById('search-' + key);
    if (!input) return;
    const filter = input.value.toLowerCase();
    const list = document.getElementById('list-' + key);
    if (!list) return;
    const options = list.getElementsByClassName('slicer-item-option');

    for (let i = 0; i < options.length; i++) {
        const txtValue = options[i].textContent || options[i].innerText;
        if (txtValue.toLowerCase().indexOf(filter) > -1) {
            options[i].style.display = "flex";
        } else {
            options[i].style.display = "none";
        }
    }
}

function selectAllSlicer(key) {
    const list = document.getElementById('list-' + key);
    if (!list) return;
    const options = list.getElementsByClassName('slicer-item-option');
    for (let i = 0; i < options.length; i++) {
        if (options[i].style.display !== 'none') {
            const cb = options[i].querySelector('input[type="checkbox"]');
            if (cb) cb.checked = true;
        }
    }
    updateSlicerLabel(key);
}

function clearAllSlicer(key) {
    const list = document.getElementById('list-' + key);
    if (!list) return;
    const options = list.getElementsByClassName('slicer-item-option');
    for (let i = 0; i < options.length; i++) {
        if (options[i].style.display !== 'none') {
            const cb = options[i].querySelector('input[type="checkbox"]');
            if (cb) cb.checked = false;
        }
    }
    updateSlicerLabel(key);
}

function updateSlicerLabel(key) {
    const list = document.getElementById('list-' + key);
    if (!list) return;
    const checkboxes = list.querySelectorAll('input[type="checkbox"]');
    const checked = list.querySelectorAll('input[type="checkbox"]:checked');
    const labelSpan = document.getElementById('btn-' + key + '-label');

    if (!labelSpan) return;

    if (checked.length === 0) {
        labelSpan.textContent = 'Select Trainees (0 Selected)';
    } else if (checked.length === checkboxes.length) {
        labelSpan.textContent = 'All Trainees Selected (' + checkboxes.length + ')';
    } else {
        labelSpan.textContent = checked.length + ' Trainees Selected';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    updateSlicerLabel('trainees');
});
</script>
