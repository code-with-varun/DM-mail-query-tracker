<?php
    $curStage = $ticket['stage'] ?? 'Maker Phase';
    $currentUserId = Session::get('user_id');
    $isAdminOrSuper = is_admin() || is_super_admin();

    $isAllocatedMaker = (!empty($ticket['allocated_to']) && $ticket['allocated_to'] == $currentUserId);
    $isAssignedChecker = (!empty($ticket['checker_id']) && $ticket['checker_id'] == $currentUserId);

    // Action button authorization: Restricted to Assigned Maker, Assigned Checker (in Checker Phase), & Admins
    $canManageTicket = $isAdminOrSuper || $isAllocatedMaker || ($isAssignedChecker && ($curStage === 'Checker Phase' || $curStage === 'Checker Queue'));
    $canSubmitToChecker = ($isAdminOrSuper || $isAllocatedMaker) && ($curStage === 'Maker Phase' || $curStage === 'Maker Queue' || $curStage === 'Input Received') && ($ticket['status'] !== 'Closed');
    $canPerformCheckerAudit = ($isAdminOrSuper || $isAssignedChecker) && ($curStage === 'Checker Phase' || $curStage === 'Checker Queue') && ($ticket['status'] !== 'Closed');
    $canDeliverTicket = ($isAdminOrSuper || $isAllocatedMaker) && ($curStage === 'Delivery Phase' || $curStage === 'Delivery') && ($ticket['status'] !== 'Closed');

    // 12 Master Lifecycle Stages Fallback
    $master12Stages = [
        1 => ['name' => 'Input Received', 'title' => 'Input Recv', 'queue_type' => 'Input', 'icon' => 'fa-inbox', 'owner' => 'Client', 'remarks' => 'Task or Mail input received'],
        2 => ['name' => 'Maker Queue', 'title' => 'Maker Phase', 'queue_type' => 'Maker', 'icon' => 'fa-pencil-ruler', 'owner' => !empty($ticket['allocated_user_name']) ? htmlspecialchars($ticket['allocated_user_name']) : 'Maker', 'remarks' => 'we have to prepare and update ticket'],
        3 => ['name' => 'Checker Queue', 'title' => 'Checker Audit', 'queue_type' => 'Checker', 'icon' => 'fa-clipboard-check', 'owner' => !empty($ticket['checker_user_name']) ? htmlspecialchars($ticket['checker_user_name']) : 'Checker', 'remarks' => 'Quality audit and verification'],
        4 => ['name' => 'Error Log', 'title' => 'Error Log', 'queue_type' => 'ErrorLog', 'icon' => 'fa-exclamation-triangle', 'owner' => !empty($ticket['checker_user_name']) ? htmlspecialchars($ticket['checker_user_name']) : 'Audit', 'remarks' => 'Error logged during audit'],
        5 => ['name' => 'Maker Checker Rework', 'title' => 'MC Rework', 'queue_type' => 'Rework', 'icon' => 'fa-sync', 'owner' => !empty($ticket['allocated_user_name']) ? htmlspecialchars($ticket['allocated_user_name']) : 'Maker', 'remarks' => 'Rework loop for audit correction'],
        6 => ['name' => 'Delivery', 'title' => 'Delivery Phase', 'queue_type' => 'Delivery', 'icon' => 'fa-paper-plane', 'owner' => !empty($ticket['allocated_user_name']) ? htmlspecialchars($ticket['allocated_user_name']) : 'Maker', 'remarks' => 'we have to prepare and update ticket'],
        7 => ['name' => 'Acknowledgement', 'title' => 'Ack Recv', 'queue_type' => 'Ack', 'icon' => 'fa-envelope-open-text', 'owner' => 'Client', 'remarks' => 'we receive email confirmation and update ticket'],
        8 => ['name' => 'Approval Queue', 'title' => 'Approval', 'queue_type' => 'Approval', 'icon' => 'fa-user-shield', 'owner' => 'Business Head', 'remarks' => 'we receive email confirmation and update ticket'],
        9 => ['name' => 'Uploader Queue', 'title' => 'Uploader', 'queue_type' => 'Uploader', 'icon' => 'fa-cloud-upload-alt', 'owner' => 'Claims uploading', 'remarks' => 'we have to upload and update ticket'],
        10 => ['name' => 'Dump Sharing', 'title' => 'Dump Share', 'queue_type' => 'DumpShare', 'icon' => 'fa-database', 'owner' => 'Shared to Finance Team', 'remarks' => 'we have to share and update ticket'],
        11 => ['name' => 'Consol Vendor Tracker', 'title' => 'Consol Vendor', 'queue_type' => 'ConsolVendor', 'icon' => 'fa-truck-moving', 'owner' => 'Maker', 'remarks' => 'we have to prepare and update ticket'],
        12 => ['name' => 'Closed', 'title' => 'Ticket Closed', 'queue_type' => 'Closed', 'icon' => 'fa-check-double', 'owner' => 'Maker', 'remarks' => 'Ticket output ready & completed']
    ];

    $queueMetaMap = [
        'Input'        => ['icon' => 'fa-inbox', 'default_owner' => 'Client', 'default_title' => 'Input Recv'],
        'Maker'        => ['icon' => 'fa-pencil-ruler', 'default_owner' => !empty($ticket['allocated_user_name']) ? htmlspecialchars($ticket['allocated_user_name']) : 'Maker', 'default_title' => 'Maker Phase'],
        'Checker'      => ['icon' => 'fa-clipboard-check', 'default_owner' => !empty($ticket['checker_user_name']) ? htmlspecialchars($ticket['checker_user_name']) : 'Checker', 'default_title' => 'Checker Audit'],
        'ErrorLog'     => ['icon' => 'fa-exclamation-triangle', 'default_owner' => !empty($ticket['checker_user_name']) ? htmlspecialchars($ticket['checker_user_name']) : 'Audit', 'default_title' => 'Error Log'],
        'Rework'       => ['icon' => 'fa-sync', 'default_owner' => !empty($ticket['allocated_user_name']) ? htmlspecialchars($ticket['allocated_user_name']) : 'Maker', 'default_title' => 'MC Rework'],
        'Delivery'     => ['icon' => 'fa-paper-plane', 'default_owner' => !empty($ticket['allocated_user_name']) ? htmlspecialchars($ticket['allocated_user_name']) : 'Maker', 'default_title' => 'Delivery Phase'],
        'Ack'          => ['icon' => 'fa-envelope-open-text', 'default_owner' => 'Client', 'default_title' => 'Ack Recv'],
        'Approval'     => ['icon' => 'fa-user-shield', 'default_owner' => 'Business Head', 'default_title' => 'Approval'],
        'Uploader'     => ['icon' => 'fa-cloud-upload-alt', 'default_owner' => 'Claims uploader', 'default_title' => 'Uploader'],
        'DumpShare'    => ['icon' => 'fa-database', 'default_owner' => 'Shared to Finance', 'default_title' => 'Dump Share'],
        'ConsolVendor' => ['icon' => 'fa-truck-moving', 'default_owner' => 'Maker', 'default_title' => 'Consol Vendor'],
        'Closed'       => ['icon' => 'fa-check-double', 'default_owner' => 'Maker', 'default_title' => 'Ticket Closed']
    ];

    $hasCustomWorkflow = !empty($workflow) && !empty($workflow['stages']);
    $pipelineStages = [];

    if ($hasCustomWorkflow) {
        foreach ($workflow['stages'] as $stg) {
            $qType = $stg['queue_type'] ?? 'Maker';
            $meta = $queueMetaMap[$qType] ?? $queueMetaMap['Maker'];
            
            $owner = $meta['default_owner'];
            if (!empty($stg['assigned_role'])) {
                if ($stg['assigned_role'] === 'Checker') {
                    $owner = !empty($ticket['checker_user_name']) ? htmlspecialchars($ticket['checker_user_name']) : 'Checker';
                } elseif ($stg['assigned_role'] === 'Admin') {
                    $owner = 'Business Head';
                } elseif ($stg['assigned_role'] === 'Both') {
                    $owner = 'Maker / Checker';
                }
            }

            $pipelineStages[] = [
                'id' => $stg['id'],
                'order' => $stg['stage_order'],
                'name' => $stg['stage_name'],
                'title' => $stg['stage_name'],
                'queue_type' => $qType,
                'icon' => $meta['icon'],
                'owner' => $owner,
                'remarks' => $stg['description'] ?? ''
            ];
        }
    } else {
        $enabledQueuesList = !empty($ticket['enabled_queues']) ? json_decode($ticket['enabled_queues'], true) : null;
        foreach ($master12Stages as $sKey => $sVal) {
            if ($enabledQueuesList === null || !is_array($enabledQueuesList) || in_array($sVal['name'], $enabledQueuesList)) {
                $pipelineStages[] = $sVal;
            }
        }
    }

    $totalStagesCount = count($pipelineStages);
    $curStageNum = 1;

    if ($ticket['status'] === 'Closed' || $ticket['status'] === 'Completed') {
        $curStageNum = $totalStagesCount;
    } else {
        $foundStage = false;
        if (!empty($ticket['current_stage_id'])) {
            foreach ($pipelineStages as $idx => $stgItem) {
                if (isset($stgItem['id']) && (int)$stgItem['id'] === (int)$ticket['current_stage_id']) {
                    $curStageNum = $idx + 1;
                    $foundStage = true;
                    break;
                }
            }
        }

        if (!$foundStage) {
            foreach ($pipelineStages as $idx => $stgItem) {
                if (strcasecmp(trim($stgItem['name']), trim($curStage)) === 0 || strcasecmp(trim($stgItem['title']), trim($curStage)) === 0) {
                    $curStageNum = $idx + 1;
                    $foundStage = true;
                    break;
                }
            }
        }

        if (!$foundStage) {
            foreach ($pipelineStages as $idx => $stgItem) {
                $qType = $stgItem['queue_type'] ?? '';
                if (!empty($qType) && (stripos($curStage, $qType) !== false || stripos($qType, $curStage) !== false)) {
                    $curStageNum = $idx + 1;
                    $foundStage = true;
                    break;
                }
            }
        }
    }

    $activeStageObj = $pipelineStages[$curStageNum - 1] ?? ($pipelineStages[0] ?? null);
    $activeQueueType = $activeStageObj['queue_type'] ?? 'Maker';
?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="fw-bold mb-0">Ticket #<?= htmlspecialchars($ticket['ticket_number']) ?></h4>
                <?= get_status_badge($ticket['status']) ?>
                <?php if (!empty($ticket['category_name'])): ?>
                    <span class="badge bg-secondary text-white fw-bold"><i class="fas fa-folder me-1"></i><?= htmlspecialchars($ticket['category_name']) ?></span>
                <?php endif; ?>
                <?php if (strcasecmp($curStage, $ticket['status']) !== 0 && strcasecmp($curStage, 'Completed') !== 0 && strcasecmp($curStage, 'Closed') !== 0): ?>
                    <span class="badge bg-purple text-white fw-bold"><i class="fas fa-layer-group me-1"></i><?= htmlspecialchars($curStage) ?></span>
                <?php endif; ?>
                <?= get_tat_badge($ticket['tat_datetime'], $ticket['status']) ?>
            </div>
            <p class="text-muted fs-7 mb-0">Created on <?= format_datetime($ticket['created_at']) ?> by <?= htmlspecialchars($ticket['creator_name'] ?? 'System') ?></p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= base_url('tickets') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
            
            <?php if ($canManageTicket && $ticket['status'] !== 'Closed'): ?>
            <button type="button" class="btn btn-outline-warning text-dark btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#rescheduleModal">
                <i class="fas fa-clock me-1"></i>Reschedule
            </button>

            <button type="button" class="btn btn-outline-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#reassignModal">
                <i class="fas fa-user-edit me-1"></i>Reassign
            </button>

            <button type="button" class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#statusModal">
                <i class="fas fa-sync-alt me-1"></i>Update Status
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Process Queue Pipeline Banner -->
    <div class="card border-0 shadow-sm mb-4 bg-white border-top border-4 border-primary">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div>
                    <h6 class="fw-bold mb-0 text-dark fs-7"><i class="fas fa-project-diagram text-primary me-2"></i><?= $hasCustomWorkflow ? htmlspecialchars($workflow['workflow_name']) : 'Task Process Queue Pipeline' ?></h6>
                    <small class="text-muted fs-8"><?= $hasCustomWorkflow ? 'Configured Category Process Workflow Tracker & Ownership Monitor' : 'Configured Sub-Activity Lifecycle Tracker & Queue Ownership Monitor (6 Per Row)' ?></small>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <?php if ($hasCustomWorkflow): ?>
                        <span class="badge bg-purple text-white border border-purple fs-8"><i class="fas fa-sitemap me-1"></i>Category Workflow (<?= count($pipelineStages) ?> Stages)</span>
                    <?php endif; ?>
                    <span class="badge bg-light text-dark border fs-8 px-3 py-1">Current Stage: <strong><?= htmlspecialchars($curStage) ?></strong></span>
                </div>
            </div>

            <!-- Stepper Container -->
            <div class="dropship-stepper-12-container">
                <div class="dropship-stepper-12">
                    <?php 
                    $stageChunks = array_chunk($pipelineStages, 6, true);
                    foreach ($stageChunks as $rIdx => $rowStages):
                        $rowItemCount = count($rowStages);
                        $itemNum = 0;
                    ?>
                        <div class="pipeline-row">
                            <?php foreach ($rowStages as $idx => $stg): 
                                $itemNum++;
                                $stageNum = $idx + 1;
                                $isLastStage = ($stageNum === $totalStagesCount) || (($stg['queue_type'] ?? '') === 'Closed');

                                if ($ticket['status'] === 'Closed' || $stageNum < $curStageNum) {
                                    $nodeClass = 'completed';
                                    $subText = $isLastStage ? 'Closed' : 'Completed';
                                } elseif ($stageNum === $curStageNum) {
                                    if ($isLastStage || $ticket['status'] === 'Closed') {
                                        $nodeClass = 'completed';
                                        $subText = 'Closed';
                                    } else {
                                        $nodeClass = 'active';
                                        $subText = 'In Progress';
                                    }
                                } else {
                                    $nodeClass = '';
                                    $subText = 'Pending';
                                }
                            ?>
                                <div class="stepper-12-item <?= $nodeClass ?>" title="<?= htmlspecialchars($stg['name'] . (!empty($stg['remarks']) ? ': ' . $stg['remarks'] : '')) ?>">
                                    <div class="stepper-12-node">
                                        <i class="fas <?= $nodeClass === 'completed' ? ($isLastStage ? 'fa-check-double' : 'fa-check') : $stg['icon'] ?>"></i>
                                    </div>
                                    <div class="stepper-12-content">
                                        <span class="stepper-12-title" title="<?= htmlspecialchars($stg['name']) ?>"><?= htmlspecialchars($stg['title']) ?></span>
                                        <span class="stepper-12-sub" title="<?= htmlspecialchars($stg['remarks'] ?? '') ?>"><?= $subText ?></span>
                                        <span class="stepper-12-owner" title="<?= htmlspecialchars($stg['remarks'] ?? '') ?>"><i class="fas fa-user me-1"></i><?= htmlspecialchars($stg['owner']) ?></span>
                                    </div>
                                </div>

                                <?php if ($itemNum < $rowItemCount): ?>
                                    <?php 
                                    if ($ticket['status'] === 'Closed' || $stageNum < $curStageNum) {
                                        $lineClass = 'completed';
                                    } elseif ($stageNum === $curStageNum - 1) {
                                        $lineClass = 'active';
                                    } else {
                                        $lineClass = '';
                                    }
                                    ?>
                                    <div class="stepper-12-line <?= $lineClass ?>"></div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Stage Control Bar -->
            <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-start">
                    <small class="text-muted fw-bold fs-8 d-block"><i class="fas fa-shield-alt text-primary me-1"></i>Stage Ownership Rights:</small>
                    <span class="fs-8 text-dark">
                        <?php if ($ticket['status'] === 'Closed'): ?>
                            Ticket is <strong>Closed & Archived</strong>
                        <?php else: ?>
                            Controlled by <strong><?= htmlspecialchars($activeStageObj['owner'] ?? 'Queue Processor') ?></strong> (Stage: <?= htmlspecialchars($activeStageObj['name'] ?? $curStage) ?>)
                        <?php endif; ?>
                    </span>
                </div>

                <div class="text-end">
                    <?php if ($ticket['status'] === 'Closed'): ?>
                        <span class="badge bg-success bg-opacity-15 text-success border border-success px-3 py-2 fs-8"><i class="fas fa-check-double me-1"></i>Ticket Fully Completed & Archived</span>

                    <?php elseif (in_array($activeQueueType, ['Input', 'Maker', 'Rework'])): ?>
                        <?php if ($canSubmitToChecker): ?>
                            <button type="button" class="btn btn-warning text-dark fw-bold px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#submitCheckerModal">
                                <i class="fas fa-paper-plane me-1"></i>Submit to Checker Queue
                            </button>
                        <?php else: ?>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border px-3 py-2 fs-8"><i class="fas fa-lock me-1"></i>Awaiting Maker File Preparation (Owner: <?= htmlspecialchars($ticket['allocated_user_name'] ?? 'Maker') ?>)</span>
                        <?php endif; ?>

                    <?php elseif (in_array($activeQueueType, ['Checker', 'ErrorLog'])): ?>
                        <?php if ($canPerformCheckerAudit): ?>
                            <button type="button" class="btn btn-success fw-bold px-3 me-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#approveCheckerModal">
                                <i class="fas fa-check-circle me-1"></i>Approve Checker Audit
                            </button>
                            <button type="button" class="btn btn-danger fw-bold px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#rejectCheckerModal">
                                <i class="fas fa-times-circle me-1"></i>Reject & Log Error
                            </button>
                        <?php else: ?>
                            <span class="badge bg-warning bg-opacity-15 text-dark border border-warning px-3 py-2 fs-8"><i class="fas fa-lock me-1"></i>Awaiting Checker Audit Review (Owner: <?= htmlspecialchars($ticket['checker_user_name'] ?? 'Checker Pool') ?>)</span>
                        <?php endif; ?>

                    <?php elseif ($activeQueueType === 'Delivery'): ?>
                        <?php if ($canDeliverTicket || $isAdminOrSuper): ?>
                            <button type="button" class="btn btn-success fw-bold px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#deliverModal">
                                <i class="fas fa-paper-plane me-1"></i>Log Delivery & Advance Queue
                            </button>
                        <?php else: ?>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border px-3 py-2 fs-8"><i class="fas fa-lock me-1"></i>Awaiting Maker Delivery Acknowledgment (Owner: <?= htmlspecialchars($ticket['allocated_user_name'] ?? 'Maker') ?>)</span>
                        <?php endif; ?>

                    <?php else: ?>
                        <?php if ($canManageTicket || $isAdminOrSuper): ?>
                            <?php
                            $actionLabels = [
                                'Ack'          => '<i class="fas fa-envelope-open-text me-1"></i>Log Client Ack & Proceed',
                                'Approval'     => '<i class="fas fa-user-shield me-1"></i>Log Manager Approval & Proceed',
                                'Uploader'     => '<i class="fas fa-cloud-upload-alt me-1"></i>Log Claims Upload & Proceed',
                                'DumpShare'    => '<i class="fas fa-database me-1"></i>Log Dump Share & Proceed',
                                'ConsolVendor' => '<i class="fas fa-truck-moving me-1"></i>Log Vendor Consolidation & Close',
                            ];
                            $btnText = $actionLabels[$activeQueueType] ?? '<i class="fas fa-step-forward me-1"></i>Log & Advance Queue';
                            $btnClass = ($activeQueueType === 'ConsolVendor' || $curStageNum === $totalStagesCount) ? 'btn-success' : 'btn-primary';
                            ?>
                            <button type="button" class="btn <?= $btnClass ?> fw-bold px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#advanceQueueModal">
                                <?= $btnText ?>
                            </button>
                        <?php else: ?>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border px-3 py-2 fs-8"><i class="fas fa-lock me-1"></i>Awaiting Queue Processing (Stage: <?= htmlspecialchars($curStage) ?>)</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>


    <div class="row g-4">
        <!-- Ticket Information Left Column -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-envelope-open-text text-primary me-2"></i>Query / Task Details</h6>
                </div>
                <div class="card-body">
                    <h5 class="fw-bold text-dark mb-3"><?= htmlspecialchars($ticket['subject']) ?></h5>
                    
                    <div class="row g-3 bg-light p-3 rounded mb-4">
                        <div class="col-md-6">
                            <span class="text-muted fs-8 fw-bold d-block">FROM ADDRESS / SENDER</span>
                            <span class="fs-7 fw-bold text-dark"><?= htmlspecialchars($ticket['from_address']) ?></span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted fs-8 fw-bold d-block">RECEIVED DATETIME</span>
                            <span class="fs-7 text-dark"><?= format_datetime($ticket['received_datetime']) ?></span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted fs-8 fw-bold d-block">DIVISION</span>
                            <span class="fs-7 text-dark"><?= htmlspecialchars($ticket['division_name'] ?? 'N/A') ?></span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted fs-8 fw-bold d-block">ACTIVITY</span>
                            <span class="fs-7 text-dark"><?= htmlspecialchars($ticket['activity_name'] ?? 'N/A') ?></span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted fs-8 fw-bold d-block">SUB ACTIVITY</span>
                            <span class="fs-7 text-dark"><?= htmlspecialchars($ticket['sub_activity_name'] ?? 'N/A') ?></span>
                        </div>
                    </div>

                    <?php if (!empty($ticket['remarks'])): ?>
                    <div class="mb-4">
                        <h6 class="fw-bold fs-7 text-muted uppercase">REMARKS / DESCRIPTION</h6>
                        <div class="p-3 bg-white border rounded fs-7 style-preserve">
                            <?= nl2br(htmlspecialchars($ticket['remarks'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($ticket['closure_attachment'])): ?>
                    <div class="mb-4 p-3 bg-success bg-opacity-10 border border-success rounded">
                        <h6 class="fw-bold fs-7 text-success mb-2"><i class="fas fa-file-check me-2"></i>DELIVERY PROOF ATTACHMENT / SCREENSHOT</h6>
                        <a href="<?= base_url($ticket['closure_attachment']) ?>" target="_blank" class="btn btn-sm btn-success fw-bold">
                            <i class="fas fa-download me-1"></i>View Delivery Screenshot (<?= basename($ticket['closure_attachment']) ?>)
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($attachments)): ?>
                    <div>
                        <h6 class="fw-bold fs-7 text-muted uppercase mb-2">TICKET ATTACHMENTS</h6>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php foreach ($attachments as $att): ?>
                                <a href="<?= base_url($att['file_path']) ?>" target="_blank" class="btn btn-sm btn-light border">
                                    <i class="fas fa-paperclip me-1 text-primary"></i><?= htmlspecialchars($att['file_name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Comments & Remarks Timeline -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-comments text-primary me-2"></i>Activity & Remarks History</h6>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('tickets/update-status') ?>" method="POST" class="mb-4">
                        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                        <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                        <input type="hidden" name="status" value="<?= $ticket['status'] ?>">
                        <div class="mb-2">
                            <textarea name="remarks" class="form-control" rows="2" placeholder="Add a comment or remark to this ticket..." required></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-sm btn-primary fw-bold"><i class="fas fa-comment me-1"></i>Add Remark</button>
                        </div>
                    </form>

                    <div class="timeline">
                        <?php if (empty($comments)): ?>
                            <p class="text-muted text-center py-3 fs-7">No remarks logged yet.</p>
                        <?php else: ?>
                            <?php foreach ($comments as $c): ?>
                            <div class="p-3 bg-light rounded mb-2 border-start border-4 border-primary">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold fs-7 text-dark"><?= htmlspecialchars($c['full_name']) ?> (<?= $c['user_code'] ?>)</span>
                                    <small class="text-muted fs-8"><?= format_datetime($c['created_at']) ?></small>
                                </div>
                                <div class="fs-7 text-secondary"><?= nl2br(htmlspecialchars($c['comment'])) ?></div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Summary Right Column -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-info-circle text-primary me-2"></i>MetaData Summary</h6>
                </div>
                <div class="card-body fs-7">
                    <?php $bucketInfo = get_current_bucket_info($ticket); ?>
                    <div class="p-3 mb-3 rounded bg-light border-start border-4 border-primary shadow-sm">
                        <small class="text-uppercase text-muted fw-bold d-block fs-8 mb-1"><i class="fas fa-user-clock me-1 text-primary"></i>Current Bucket Owner</small>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold text-dark mb-0"><?= htmlspecialchars($bucketInfo['user_name']) ?></h6>
                                <?php if (!empty($bucketInfo['user_code'])): ?>
                                    <span class="badge bg-secondary fs-8 mt-1"><i class="fas fa-id-badge me-1"></i>Emp ID: <?= htmlspecialchars($bucketInfo['user_code']) ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="badge <?= $bucketInfo['badge_class'] ?> px-2 py-1 fw-bold"><?= htmlspecialchars($bucketInfo['role_label']) ?> Phase</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Maker (Allocated)</span>
                        <div class="text-end">
                            <span class="fw-bold text-dark d-block"><?= htmlspecialchars($ticket['allocated_user_name'] ?? 'Unassigned') ?></span>
                            <?php if (!empty($ticket['allocated_user_code'])): ?>
                                <small class="text-muted">(ID: <?= htmlspecialchars($ticket['allocated_user_code']) ?>)</small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($ticket['checker_user_name'])): ?>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Checker (Designated)</span>
                        <div class="text-end">
                            <span class="fw-bold text-dark d-block"><?= htmlspecialchars($ticket['checker_user_name']) ?></span>
                            <?php if (!empty($ticket['checker_user_code'])): ?>
                                <small class="text-muted">(ID: <?= htmlspecialchars($ticket['checker_user_code']) ?>)</small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Scheduled Work Date</span>
                        <span class="fw-bold text-dark"><?= !empty($ticket['scheduled_date']) ? date('d M Y', strtotime($ticket['scheduled_date'])) : 'Today' ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Priority</span>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($ticket['priority']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Target SLA TAT</span>
                        <span class="fw-bold text-dark"><?= format_datetime($ticket['tat_datetime']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Agency Code</span>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($ticket['agency_code'] ?: 'N/A') ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Manager Name</span>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($ticket['manager_name'] ?: 'N/A') ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Replied By</span>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($ticket['replied_user_name'] ?: 'Not Replied') ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Replied Date</span>
                        <span class="fw-bold text-dark"><?= $ticket['replied_datetime'] ? format_datetime($ticket['replied_datetime']) : 'N/A' ?></span>
                    </div>
                </div>
            </div>

            <!-- Hold History Card -->
            <?php if (!empty($holdHistory)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-history text-dark me-2"></i>Hold & Release Log</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush fs-8">
                        <?php foreach ($holdHistory as $hh): ?>
                        <li class="list-group-item">
                            <div class="fw-bold text-dark">Hold Reason: <?= htmlspecialchars($hh['hold_reason']) ?></div>
                            <div class="text-muted">Held on: <?= format_datetime($hh['hold_date']) ?> by <?= htmlspecialchars($hh['held_by_name']) ?></div>
                            <?php if ($hh['release_date']): ?>
                                <div class="text-success mt-1"><i class="fas fa-check me-1"></i>Released on: <?= format_datetime($hh['release_date']) ?> by <?= htmlspecialchars($hh['released_by_name']) ?></div>
                            <?php else: ?>
                                <div class="text-danger mt-1"><i class="fas fa-pause-circle me-1"></i>Currently Active Hold</div>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal: Submit to Checker -->
<div class="modal fade" id="submitCheckerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('tickets/submit-to-checker') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold"><i class="fas fa-paper-plane me-2"></i>Submit to Checker Queue</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Select Designated Checker <span class="text-danger">*</span></label>
                        <select name="checker_id" class="form-select" required>
                            <option value="">Select Checker</option>
                            <?php foreach ($checkers as $chk): ?>
                                <option value="<?= $chk['id'] ?>"><?= htmlspecialchars($chk['full_name']) ?> (<?= $chk['user_code'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Maker Work Remarks / File Notes</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Enter notes for the checker..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold px-4"><i class="fas fa-check me-1"></i>Submit to Checker</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Approve Checker Audit -->
<div class="modal fade" id="approveCheckerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('tickets/checker-action') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <input type="hidden" name="decision" value="approve">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-check-circle me-2"></i>Approve Checker Audit</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-dark">Confirm that file verification and quality audit are clean without errors. This will move the ticket to <strong>Delivery Phase</strong>.</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Audit Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Verified and approved clean file..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold px-4"><i class="fas fa-thumbs-up me-1"></i>Approve Audit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Reject & Log Quality Error -->
<div class="modal fade" id="rejectCheckerModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('tickets/checker-action') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <input type="hidden" name="decision" value="reject">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Reject & Log Quality Error</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted fs-7 mb-3">Rejecting will automatically log this observation into the <strong>Error Tracker</strong> and send the ticket back to the Maker.</p>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Billing Month (Month-Year) <span class="text-danger">*</span></label>
                            <input type="month" name="billing_month" class="form-control" value="<?= date('Y-m') ?>" required>
                            <small class="text-muted fs-8">Date defaults to 01 of selected month</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Checking Month (Month-Year) <span class="text-danger">*</span></label>
                            <input type="month" name="checking_month" class="form-control" value="<?= date('Y-m') ?>" required>
                            <small class="text-muted fs-8">Date defaults to 01 of selected month</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Error Observation <span class="text-danger">*</span></label>
                        <input type="text" name="error_observation" class="form-control" placeholder="Enter key error observation title" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Error Description</label>
                        <textarea name="error_description" class="form-control" rows="3" placeholder="Provide detailed description of the error observed..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Resolution / Solution</label>
                        <textarea name="resolution_solution" class="form-control" rows="3" placeholder="Enter resolution, corrective action taken, or solution..."></textarea>
                    </div>

                    <input type="hidden" name="maker_id" value="<?= $ticket['allocated_to'] ?>">
                    <input type="hidden" name="checker_id" value="<?= Session::get('user_id') ?>">

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark">Error Type <span class="text-danger">*</span></label>
                            <select name="error_type" class="form-select" required>
                                <option value="Internal" selected>Internal</option>
                                <option value="External">External</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark">Maker (Processor)</label>
                            <div class="form-control bg-light text-dark fw-bold border text-truncate">
                                <i class="fas fa-user-edit me-1 text-secondary"></i>
                                <?= htmlspecialchars($ticket['allocated_user_name'] ?? 'Maker') ?>
                                <?php if (!empty($ticket['allocated_user_code'])): ?>
                                    <small class="text-muted">(<?= htmlspecialchars($ticket['allocated_user_code']) ?>)</small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark">Checker (Auditor)</label>
                            <div class="form-control bg-light text-dark fw-bold border text-truncate">
                                <i class="fas fa-user-check me-1 text-secondary"></i>
                                <?= htmlspecialchars(Session::get('full_name') ?: ($ticket['checker_user_name'] ?? 'Checker')) ?>
                                <?php if (!empty(Session::get('user_code')) || !empty($ticket['checker_user_code'])): ?>
                                    <small class="text-muted">(<?= htmlspecialchars(Session::get('user_code') ?: $ticket['checker_user_code']) ?>)</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold px-4">
                        <i class="fas fa-undo me-1"></i>Log Error & Return to Maker
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Deliver File & Close Ticket -->
<div class="modal fade" id="deliverModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('tickets/deliver') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-paper-plane me-2"></i>Deliver File & Close Ticket</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Upload Delivery Screenshot / Proof Image</label>
                        <input type="file" name="closure_attachment" class="form-control" accept="image/*,.pdf,.doc,.docx">
                        <small class="text-muted fs-8">Attach delivery email screenshot or acknowledgement proof.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Delivery Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="File sent via email to client..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold px-4"><i class="fas fa-check-circle me-1"></i>Deliver & Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Reschedule Ticket -->
<div class="modal fade" id="rescheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('tickets/reschedule') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold"><i class="fas fa-clock me-2"></i>Reschedule Work Date</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Target Scheduled Date <span class="text-danger">*</span></label>
                        <input type="date" name="scheduled_date" class="form-control fw-bold" value="<?= $ticket['scheduled_date'] ?? date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Reschedule reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold px-4"><i class="fas fa-calendar-check me-1"></i>Save Date</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Reassign Ticket -->
<div class="modal fade" id="reassignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('tickets/reassign') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-edit me-2"></i>Reassign Ticket</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Select Employee <span class="text-danger">*</span></label>
                        <select name="allocated_to" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= ($ticket['allocated_to'] == $u['id']) ? 'selected' : '' ?>><?= htmlspecialchars($u['full_name']) ?> (<?= $u['user_code'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Reassignment Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Reassignment reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4"><i class="fas fa-user-check me-1"></i>Confirm Reassign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Update Status -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('tickets/update-status') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Update Ticket Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Status</label>
                        <select name="status" class="form-select" required>
                            <?php foreach (['New', 'In Progress', 'Pending', 'Closed', 'Cancelled'] as $st): ?>
                                <option value="<?= $st ?>" <?= $ticket['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Status Update Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Enter status update details..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Save Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Advance Queue Phase -->
<div class="modal fade" id="advanceQueueModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('tickets/advance-queue') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <input type="hidden" name="current_stage" value="<?= htmlspecialchars($curStage) ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-step-forward me-2"></i>Log Queue Completion & Proceed</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded border border-primary border-opacity-25 mb-3">
                        <small class="text-muted text-uppercase fw-bold fs-8 d-block mb-1">Current Queue Stage</small>
                        <div class="fw-bold text-dark fs-6"><i class="fas fa-layer-group text-primary me-2"></i><?= htmlspecialchars($curStage) ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Upload Proof Document / Screenshot (Optional)</label>
                        <input type="file" name="stage_attachment" class="form-control" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx">
                        <small class="text-muted fs-8">Attach email screenshot, upload confirmation, or reference document.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Queue Action Remarks / Notes <span class="text-danger">*</span></label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Enter completion notes for stage <?= htmlspecialchars($curStage) ?>..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4"><i class="fas fa-check-circle me-1"></i>Complete & Advance</button>
                </div>
            </form>
        </div>
    </div>
</div>

