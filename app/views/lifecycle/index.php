<div class="container-fluid px-4 py-4">
    <!-- Header Toolbar -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark fs-5"><i class="fas fa-project-diagram text-primary me-2"></i>Process Lifecycle & Monthwise Tracker</h4>
            <p class="text-muted fs-8 mb-0">Simplified Task Register & Stage Progress Tracker for Month <?= htmlspecialchars($billingMonth) ?></p>
        </div>
        
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <!-- Billing Month Selector -->
            <form action="<?= base_url('lifecycle') ?>" method="GET" class="d-flex align-items-center gap-2">
                <label class="fs-8 fw-bold text-dark mb-0">Billing Month:</label>
                <input type="month" name="month" class="form-control form-control-sm fw-bold" value="<?= htmlspecialchars($billingMonth) ?>" onchange="this.form.submit()">
            </form>

            <!-- Reset & Sync with Tickets Button -->
            <form action="<?= base_url('lifecycle/populate-subtasks') ?>" method="POST" class="d-inline" onsubmit="return confirm('Reset and re-sync all Lifecycle Tracker data for month <?= htmlspecialchars($billingMonth) ?> with File Preparation tickets?');">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="billing_month" value="<?= htmlspecialchars($billingMonth) ?>">
                <button type="submit" class="btn btn-danger btn-sm fw-bold fs-8 shadow-2xs">
                    <i class="fas fa-sync-alt me-1"></i>Reset & Sync with Tickets
                </button>
            </form>

            <!-- Export CSV -->
            <a href="<?= base_url('lifecycle/export-excel?month=' . urlencode($billingMonth)) ?>" class="btn btn-outline-dark btn-sm fw-bold fs-8">
                <i class="fas fa-file-excel me-1 text-success"></i>Export CSV
            </a>

            <!-- Manual Add Item -->
            <button class="btn btn-primary btn-sm fw-bold fs-8" data-bs-toggle="modal" data-bs-target="#addItemModal">
                <i class="fas fa-plus-circle me-1"></i>Add Lifecycle Task
            </button>
        </div>
    </div>

    <!-- Flash Notifications -->
    <?php $flash = Session::getFlash(); ?>
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show shadow-sm mb-3 fs-8" role="alert">
            <i class="fas <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> me-2"></i>
            <?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Overall Month Level Progress KPI Summary Card -->
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 h-100 bg-white border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <small class="text-muted fs-8 fw-bold text-uppercase d-block">Overall Month Progress</small>
                        <h4 class="fw-bold mb-0 text-dark"><?= $monthOverall['percentage'] ?? 0 ?>%</h4>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                        <i class="fas fa-chart-line fs-4"></i>
                    </div>
                </div>
                <div class="progress mb-2" style="height: 8px;">
                    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= min(100, $monthOverall['percentage'] ?? 0) ?>%;"></div>
                </div>
                <small class="text-muted fs-8">
                    <strong><?= $monthOverall['closed'] ?? 0 ?></strong> of <strong><?= $monthOverall['total'] ?? 0 ?></strong> Tasks Closed for <?= htmlspecialchars($billingMonth) ?>
                </small>
            </div>
        </div>

        <div class="col-md-8">
            <div class="row g-2 h-100">
                <div class="col-4">
                    <div class="card border-0 shadow-sm p-3 h-100 bg-white text-center">
                        <small class="text-muted fs-8 fw-bold text-uppercase d-block mb-1">Maker Stage</small>
                        <h3 class="fw-bold text-primary mb-0"><?= $monthOverall['maker_tasks'] ?? 0 ?></h3>
                        <small class="text-muted fs-9">Input & Processing</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm p-3 h-100 bg-white text-center">
                        <small class="text-muted fs-8 fw-bold text-uppercase d-block mb-1">Checker Stage</small>
                        <h3 class="fw-bold text-warning mb-0"><?= $monthOverall['checker_tasks'] ?? 0 ?></h3>
                        <small class="text-muted fs-9">Audit & Rework</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm p-3 h-100 bg-white text-center">
                        <small class="text-muted fs-8 fw-bold text-uppercase d-block mb-1">Delivery / Closure</small>
                        <h3 class="fw-bold text-success mb-0"><?= ($monthOverall['delivery_tasks'] ?? 0) + ($monthOverall['closed'] ?? 0) ?></h3>
                        <small class="text-muted fs-9"><?= $monthOverall['closed'] ?? 0 ?> Closed</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity-Level Progress Summary Collapsible Card -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center cursor-pointer" data-bs-toggle="collapse" data-bs-target="#activityProgressCollapse" aria-expanded="false">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-sitemap text-primary fs-7"></i>
                <span class="fw-bold fs-8 text-dark">Activity Breakdown Details (<?= htmlspecialchars($billingMonth) ?>)</span>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary fs-8 fw-bold ms-1"><?= count($activityProgress ?? []) ?> Activities</span>
            </div>
            <i class="fas fa-chevron-down text-muted fs-8"></i>
        </div>
        <div class="collapse" id="activityProgressCollapse">
            <div class="card-body p-0 border-top">
                <?php if (empty($activityProgress)): ?>
                    <p class="text-muted fs-8 p-3 mb-0">No active subtasks populated for month <?= htmlspecialchars($billingMonth) ?> yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 fs-8 border-0">
                            <thead class="table-light text-uppercase text-muted fs-9">
                                <tr>
                                    <th style="width: 50px;" class="text-center">#</th>
                                    <th>ACTIVITY NAME</th>
                                    <th style="width: 120px;" class="text-center">TOTAL TASKS</th>
                                    <th style="width: 130px;" class="text-center">MAKER STAGE</th>
                                    <th style="width: 130px;" class="text-center">CHECKER STAGE</th>
                                    <th style="width: 130px;" class="text-center">DELIVERY STAGE</th>
                                    <th style="width: 130px;" class="text-center">CLOSED TASKS</th>
                                    <th style="width: 230px;">COMPLETION PROGRESS</th>
                                </tr>
                            </thead>
                            <tbody class="fw-semibold">
                                <?php $apIdx = 1; foreach ($activityProgress as $ap): ?>
                                <tr>
                                    <td class="text-center text-secondary fw-bold"><?= $apIdx++ ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                                <i class="fas fa-sitemap fs-8"></i>
                                            </div>
                                            <span class="fw-bold text-dark fs-7"><?= htmlspecialchars($ap['activity_name']) ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border px-2 py-1 fs-8"><?= $ap['total_tasks'] ?> Tasks</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1 fs-8"><?= $ap['maker_stage_count'] ?> Maker</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning bg-opacity-20 text-dark border border-warning px-2 py-1 fs-8"><?= $ap['checker_stage_count'] ?> Checker</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info px-2 py-1 fs-8"><?= $ap['delivery_stage_count'] ?> Delivery</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1 fs-8"><?= $ap['closed_count'] ?> Closed</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 7px;">
                                                <div class="progress-bar bg-<?= $ap['progress_percentage'] >= 100 ? 'success' : ($ap['progress_percentage'] >= 50 ? 'info' : 'warning') ?>" 
                                                     role="progressbar" 
                                                     style="width: <?= min(100, $ap['progress_percentage']) ?>%;"></div>
                                            </div>
                                            <span class="fw-bold fs-8 text-dark text-nowrap" style="min-width: 45px;"><?= $ap['progress_percentage'] ?>%</span>
                                        </div>
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

    <!-- Filters Bar -->
    <div class="card border-0 shadow-sm mb-3 bg-light border-start border-3 border-primary">
        <div class="card-body p-2 px-3">
            <form action="<?= base_url('lifecycle') ?>" method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="month" value="<?= htmlspecialchars($billingMonth) ?>">

                <div class="col-md-3">
                    <div class="position-relative">
                        <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-secondary fs-8"></i>
                        <input type="text" name="search" class="form-control form-control-sm ps-5 fs-8" placeholder="Search ticket #, sub-activity..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <select name="type" class="form-select form-select-sm fs-8" onchange="this.form.submit()">
                        <option value="">-- All Task Types --</option>
                        <option value="File Preparation" <?= ($filters['ticket_type'] ?? '') === 'File Preparation' ? 'selected' : '' ?>>File Preparation Task</option>
                        <option value="Rerun" <?= ($filters['ticket_type'] ?? '') === 'Rerun' ? 'selected' : '' ?>>Rerun Task</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="stage" class="form-select form-select-sm fs-8" onchange="this.form.submit()">
                        <option value="">-- All Lifecycle Stages --</option>
                        <?php 
                            $stagesList = [
                                'Input Received', 'Maker Queue', 'Checker Queue', 'Error Log',
                                'Maker Checker Rework', 'Delivery', 'Acknowledgement', 'Approval Queue',
                                'Uploader Queue', 'Dump Sharing', 'Consol Vendor Tracker', 'Closed'
                            ];
                            foreach ($stagesList as $st):
                        ?>
                            <option value="<?= $st ?>" <?= ($filters['stage'] ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 text-end">
                    <a href="<?= base_url('lifecycle?month=' . urlencode($billingMonth)) ?>" class="btn btn-light btn-sm border fs-8 me-1">Reset</a>
                    <button type="submit" class="btn btn-secondary btn-sm fw-bold fs-8">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Lifecycle Streamlined Data Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 border-0 fs-8 datatable">
                    <thead class="table-light text-nowrap">
                        <tr>
                            <th style="width: 140px;">TICKET #</th>
                            <th style="min-width: 220px;">SUB-ACTIVITY & HIERARCHY</th>
                            <th style="width: 120px;">BILLING MONTH</th>
                            <th style="width: 160px;">MAKER</th>
                            <th style="width: 160px;">CHECKER</th>
                            <th style="min-width: 200px;">STAGE & PROGRESS</th>
                            <th class="text-end" style="width: 120px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 bg-white">
                                    <i class="fas fa-project-diagram fs-1 text-muted mb-3 d-block"></i>
                                    <h6 class="fw-bold text-secondary">No Tasks Logged for Month <?= htmlspecialchars($billingMonth) ?></h6>
                                    <p class="text-muted fs-8 mb-3">Click <strong>"Reset & Sync with Tickets"</strong> above to load File Preparation tickets for <?= htmlspecialchars($billingMonth) ?>.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php 
                                $stageOrderMap = [
                                    'Input Received'        => 1,
                                    'Maker Queue'           => 2,
                                    'Checker Queue'         => 3,
                                    'Error Log'             => 4,
                                    'Maker Checker Rework'  => 5,
                                    'Delivery'              => 6,
                                    'Acknowledgement'       => 7,
                                    'Approval Queue'        => 8,
                                    'Uploader Queue'        => 9,
                                    'Dump Sharing'          => 10,
                                    'Consol Vendor Tracker' => 11,
                                    'Closed'                => 12
                                ];

                                $nextStageMap = [
                                    'Input Received' => ['next' => 'Maker Queue', 'label' => 'Update Stage', 'icon' => 'fa-edit', 'btn' => 'btn-primary'],
                                    'Maker Queue' => ['next' => 'Checker Queue', 'label' => 'Update Stage', 'icon' => 'fa-edit', 'btn' => 'btn-warning text-dark'],
                                    'Checker Queue' => ['next' => 'Delivery', 'label' => 'Update Stage', 'icon' => 'fa-edit', 'btn' => 'btn-success'],
                                    'Error Log' => ['next' => 'Maker Checker Rework', 'label' => 'Update Stage', 'icon' => 'fa-edit', 'btn' => 'btn-danger'],
                                    'Maker Checker Rework' => ['next' => 'Checker Queue', 'label' => 'Update Stage', 'icon' => 'fa-edit', 'btn' => 'btn-warning text-dark'],
                                    'Delivery' => ['next' => 'Acknowledgement', 'label' => 'Update Stage', 'icon' => 'fa-edit', 'btn' => 'btn-info text-dark'],
                                    'Acknowledgement' => ['next' => 'Approval Queue', 'label' => 'Update Stage', 'icon' => 'fa-edit', 'btn' => 'btn-primary'],
                                    'Approval Queue' => ['next' => 'Uploader Queue', 'label' => 'Update Stage', 'icon' => 'fa-edit', 'btn' => 'btn-success'],
                                    'Uploader Queue' => ['next' => 'Dump Sharing', 'label' => 'Update Stage', 'icon' => 'fa-edit', 'btn' => 'btn-secondary'],
                                    'Dump Sharing' => ['next' => 'Consol Vendor Tracker', 'label' => 'Update Stage', 'icon' => 'fa-edit', 'btn' => 'btn-dark'],
                                    'Consol Vendor Tracker' => ['next' => 'Closed', 'label' => 'Update Stage', 'icon' => 'fa-edit', 'btn' => 'btn-success'],
                                    'Closed' => ['next' => 'Closed', 'label' => 'Closed', 'icon' => 'fa-lock', 'btn' => 'btn-light border disabled']
                                ];

                                $stageBadgeColors = [
                                    'Input Received' => 'bg-info bg-opacity-10 text-info border border-info',
                                    'Maker Queue' => 'bg-primary bg-opacity-10 text-primary border border-primary',
                                    'Checker Queue' => 'bg-warning bg-opacity-20 text-dark border border-warning',
                                    'Error Log' => 'bg-danger bg-opacity-10 text-danger border border-danger',
                                    'Maker Checker Rework' => 'bg-danger bg-opacity-10 text-danger border border-danger',
                                    'Delivery' => 'bg-success bg-opacity-10 text-success border border-success',
                                    'Acknowledgement' => 'bg-info bg-opacity-10 text-info border border-info',
                                    'Approval Queue' => 'bg-primary bg-opacity-10 text-primary border border-primary',
                                    'Uploader Queue' => 'bg-success bg-opacity-10 text-success border border-success',
                                    'Dump Sharing' => 'bg-secondary bg-opacity-10 text-secondary border border-secondary',
                                    'Consol Vendor Tracker' => 'bg-dark bg-opacity-10 text-dark border border-dark',
                                    'Closed' => 'bg-success bg-opacity-15 text-success border border-success'
                                ];
                            ?>
                            <?php foreach ($items as $item): ?>
                                <?php
                                    $currStage = $item['current_stage'];
                                    $actionInfo = $nextStageMap[$currStage] ?? ['next' => 'Closed', 'label' => 'Update Stage', 'icon' => 'fa-edit', 'btn' => 'btn-primary'];
                                    $badgeStyle = $stageBadgeColors[$currStage] ?? 'bg-light text-dark border';

                                    $stageNum = $stageOrderMap[$currStage] ?? 2;
                                    $stagePct = round(($stageNum / 12) * 100);
                                ?>
                                <tr>
                                    <!-- Ticket / Task ID -->
                                    <td class="text-nowrap">
                                        <?php if (!empty($item['ticket_id'])): ?>
                                            <a href="<?= base_url('tickets/view/' . $item['ticket_id']) ?>" class="ticket-no-link fw-bold fs-7" title="View Full Ticket Details">
                                                <?= htmlspecialchars($item['ticket_number']) ?>
                                            </a>
                                        <?php else: ?>
                                            <strong class="text-dark fs-7"><?= htmlspecialchars($item['ticket_number']) ?></strong>
                                        <?php endif; ?>
                                        <span class="badge bg-light text-muted border fs-9 d-block mt-1"><?= htmlspecialchars($item['ticket_type']) ?></span>
                                    </td>

                                    <!-- Sub-Activity & Hierarchy -->
                                    <td>
                                        <div class="fw-bold text-dark fs-7 mb-0"><?= htmlspecialchars($item['sub_activity_name']) ?></div>
                                        <small class="text-muted fs-8">
                                            <i class="fas fa-sitemap text-secondary me-1"></i><?= htmlspecialchars($item['activity_name']) ?>
                                            <?php if (!empty($item['division_name'])): ?>
                                                &bull; <i class="fas fa-building me-1"></i><?= htmlspecialchars($item['division_name']) ?>
                                            <?php endif; ?>
                                        </small>
                                    </td>

                                    <!-- Billing Month -->
                                    <td class="text-nowrap">
                                        <span class="badge bg-light text-dark border fw-bold fs-8">
                                            <i class="fas fa-calendar-alt me-1 text-primary"></i><?= htmlspecialchars($item['billing_month']) ?>
                                        </span>
                                    </td>

                                    <!-- Maker -->
                                    <td class="text-nowrap">
                                        <?php if (!empty($item['maker_name'])): ?>
                                            <div class="fw-bold text-dark fs-7"><i class="fas fa-user-edit me-1 text-primary"></i><?= htmlspecialchars($item['maker_name']) ?></div>
                                            <button type="button" class="btn btn-link text-muted p-0 fs-8 text-decoration-none btn-assign-modal" data-id="<?= $item['id'] ?>" data-maker="<?= $item['maker_id'] ?>" data-checker="<?= $item['checker_id'] ?>" data-bs-toggle="modal" data-bs-target="#assignUserModal">
                                                <i class="fas fa-edit me-1"></i>Change
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 fs-8 fw-bold btn-assign-modal" data-id="<?= $item['id'] ?>" data-maker="<?= $item['maker_id'] ?>" data-checker="<?= $item['checker_id'] ?>" data-bs-toggle="modal" data-bs-target="#assignUserModal">
                                                <i class="fas fa-user-plus me-1"></i>Assign Maker
                                            </button>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Checker -->
                                    <td class="text-nowrap">
                                        <?php if (!empty($item['checker_name'])): ?>
                                            <div class="fw-bold text-dark fs-7"><i class="fas fa-user-check me-1 text-warning"></i><?= htmlspecialchars($item['checker_name']) ?></div>
                                            <button type="button" class="btn btn-link text-muted p-0 fs-8 text-decoration-none btn-assign-modal" data-id="<?= $item['id'] ?>" data-maker="<?= $item['maker_id'] ?>" data-checker="<?= $item['checker_id'] ?>" data-bs-toggle="modal" data-bs-target="#assignUserModal">
                                                <i class="fas fa-edit me-1"></i>Change
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-outline-warning text-dark py-0 px-2 fs-8 fw-bold btn-assign-modal" data-id="<?= $item['id'] ?>" data-maker="<?= $item['maker_id'] ?>" data-checker="<?= $item['checker_id'] ?>" data-bs-toggle="modal" data-bs-target="#assignUserModal">
                                                <i class="fas fa-user-plus me-1"></i>Assign Checker
                                            </button>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Stage & Progress Bar -->
                                    <td class="text-nowrap">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="badge <?= $badgeStyle ?> px-2 py-1 fw-bold fs-8">
                                                <i class="fas fa-flag me-1"></i><?= htmlspecialchars($currStage) ?>
                                            </span>
                                            <small class="fw-bold text-dark fs-8"><?= $stagePct ?>%</small>
                                        </div>
                                        <div class="progress" style="height: 6px; width: 160px;">
                                            <div class="progress-bar bg-<?= $stagePct >= 100 ? 'success' : ($stagePct >= 50 ? 'info' : 'primary') ?>" 
                                                 role="progressbar" 
                                                 style="width: <?= $stagePct ?>%;"></div>
                                        </div>
                                        <small class="text-muted fs-9 d-block mt-1">Stage <?= $stageNum ?> of 12</small>
                                    </td>

                                    <!-- Actions -->
                                    <td class="text-end text-nowrap">
                                        <button type="button" 
                                                class="btn btn-sm <?= $actionInfo['btn'] ?> fs-8 fw-bold py-1 px-2 btn-next-stage"
                                                data-id="<?= $item['id'] ?>" 
                                                data-number="<?= htmlspecialchars($item['ticket_number']) ?>" 
                                                data-subname="<?= htmlspecialchars($item['sub_activity_name']) ?>"
                                                data-targetstage="<?= $actionInfo['next'] ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#updateStageModal"
                                                title="Update Stage">
                                            <i class="fas <?= $actionInfo['icon'] ?> me-1"></i><?= $actionInfo['label'] ?>
                                        </button>
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

<!-- Modal: Advance / Update Stage -->
<div class="modal fade" id="updateStageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h6 class="modal-title fw-bold"><i class="fas fa-step-forward me-2"></i>Update Task Lifecycle Stage</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('lifecycle/update_stage') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="lifecycle_id" id="modal_lifecycle_id">
                <input type="hidden" name="current_month" value="<?= htmlspecialchars($billingMonth) ?>">

                <div class="modal-body p-4 fs-8">
                    <div class="alert alert-light border mb-3 p-2 rounded">
                        <strong class="d-block text-dark fs-7" id="modal_ticket_number">LFC-XXXXX</strong>
                        <small class="text-muted" id="modal_sub_name">Sub Activity Name</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-8 fw-bold">Target Stage <span class="text-danger">*</span></label>
                        <select name="new_stage" id="modal_new_stage" class="form-select form-select-sm fw-bold" required>
                            <option value="Input Received">1. Input Received (8%)</option>
                            <option value="Maker Queue">2. Maker Queue (17%)</option>
                            <option value="Checker Queue">3. Checker Queue (25%)</option>
                            <option value="Error Log">4. Error Log (33%)</option>
                            <option value="Maker Checker Rework">5. Maker Checker Rework (42%)</option>
                            <option value="Delivery">6. Delivery (50%)</option>
                            <option value="Acknowledgement">7. Acknowledgement (58%)</option>
                            <option value="Approval Queue">8. Approval Queue (67%)</option>
                            <option value="Uploader Queue">9. Uploader Queue (75%)</option>
                            <option value="Dump Sharing">10. Dump Sharing (83%)</option>
                            <option value="Consol Vendor Tracker">11. Consol Vendor Tracker (92%)</option>
                            <option value="Closed">12. Closed (100%)</option>
                        </select>
                    </div>

                    <div class="mb-3" id="errorDescGroup" style="display:none;">
                        <label class="form-label fs-8 fw-bold text-danger">Error Observation / Defect Details <span class="text-danger">*</span></label>
                        <textarea name="error_description" class="form-control form-control-sm" rows="2" placeholder="Details of defect or rework reason..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-8 fw-bold">Attach Document / Output File (Optional)</label>
                        <input type="file" name="stage_attachment" class="form-control form-control-sm">
                        <small class="text-muted fs-8">Supports deliverable files, checker reports, or approval proofs</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-8 fw-bold">Stage Remarks / Notes</label>
                        <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="Enter status notes or completion details..."></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm fs-8" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fs-8 fw-bold"><i class="fas fa-save me-1"></i>Save Stage Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Quick Assign Maker / Checker -->
<div class="modal fade" id="assignUserModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white py-2 px-3">
                <h6 class="modal-title fs-8 fw-bold"><i class="fas fa-users-cog me-2"></i>Assign Maker / Checker</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('lifecycle/assign_users') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="lifecycle_id" id="assign_lifecycle_id">
                <input type="hidden" name="current_month" value="<?= htmlspecialchars($billingMonth) ?>">

                <div class="modal-body p-3 fs-8">
                    <div class="mb-2">
                        <label class="form-label fs-8 fw-bold text-dark mb-1">Maker Employee</label>
                        <select name="maker_id" id="assign_maker_id" class="form-select form-select-sm">
                            <option value="">-- Select Maker --</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fs-8 fw-bold text-dark mb-1">Checker Employee</label>
                        <select name="checker_id" id="assign_checker_id" class="form-select form-select-sm">
                            <option value="">-- Select Checker --</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm fs-8" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fs-8 fw-bold"><i class="fas fa-save me-1"></i>Save Assignments</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Manual Add Lifecycle Task -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white py-3">
                <h6 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Add Lifecycle Task</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('lifecycle/create') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="billing_month" value="<?= htmlspecialchars($billingMonth) ?>">

                <div class="modal-body p-4 fs-8">
                    <div class="mb-3">
                        <label class="form-label fs-8 fw-bold">Task Type <span class="text-danger">*</span></label>
                        <select name="ticket_type" class="form-select form-select-sm" required>
                            <option value="File Preparation" selected>File Preparation Task</option>
                            <option value="Rerun">Rerun Task</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-8 fw-bold">Select Sub Activity <span class="text-danger">*</span></label>
                        <select name="sub_activity_id" class="form-select form-select-sm" required>
                            <option value="">-- Choose Sub Activity --</option>
                            <?php foreach (($allSubActivities ?? []) as $sa): ?>
                                <option value="<?= $sa['id'] ?>">
                                    [<?= htmlspecialchars($sa['division_name'] ?? 'Gen') ?>] <?= htmlspecialchars($sa['activity_name'] ?? '') ?> &rarr; <?= htmlspecialchars($sa['sub_activity_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-8 fw-bold">Maker Employee</label>
                            <select name="maker_id" class="form-select form-select-sm">
                                <option value="">-- Select Maker --</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fs-8 fw-bold">Checker Employee</label>
                            <select name="checker_id" class="form-select form-select-sm">
                                <option value="">-- Select Checker --</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-8 fw-bold">Initial Remarks / Notes</label>
                        <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="Initial task setup notes..."></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm fs-8" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fs-8 fw-bold"><i class="fas fa-save me-1"></i>Add Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.cursor-pointer {
    cursor: pointer;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Stage Step Click Event (Jump/Update to specific stage)
    document.querySelectorAll('.btn-next-stage').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const num = this.dataset.number;
            const subName = this.dataset.subname;
            const targetStage = this.dataset.targetstage;

            if (id) {
                document.getElementById('modal_lifecycle_id').value = id;
                document.getElementById('modal_ticket_number').textContent = num;
                document.getElementById('modal_sub_name').textContent = subName;
                document.getElementById('modal_new_stage').value = targetStage;

                toggleErrorGroup(targetStage);
            }
        });
    });

    // Quick Assign Maker/Checker Event
    document.querySelectorAll('.btn-assign-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('assign_lifecycle_id').value = this.dataset.id;
            document.getElementById('assign_maker_id').value = this.dataset.maker || '';
            document.getElementById('assign_checker_id').value = this.dataset.checker || '';
        });
    });

    const stageSelect = document.getElementById('modal_new_stage');
    if (stageSelect) {
        stageSelect.addEventListener('change', function() {
            toggleErrorGroup(this.value);
        });
    }

    function toggleErrorGroup(stg) {
        const errorGrp = document.getElementById('errorDescGroup');
        if (errorGrp) {
            if (stg === 'Error Log' || stg === 'Maker Checker Rework') {
                errorGrp.style.display = 'block';
            } else {
                errorGrp.style.display = 'none';
            }
        }
    }
});
</script>
