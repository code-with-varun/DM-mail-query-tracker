<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Internal Tasks</h4>
            <p class="text-muted fs-7 mb-0">Internal Employee Task Allocation & Workload</p>
        </div>
        <a href="<?= base_url('tasks/create') ?>" class="btn btn-primary fw-bold"><i class="fas fa-plus-circle me-2"></i>New Task</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light align-middle text-nowrap">
                        <tr>
                            <th>Ticket #</th>
                            <th>Task Title</th>
                            <th>Priority</th>
                            <th>Assigned To</th>
                            <th>Assigned By</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): ?>
                        <?php 
                            $statusDisplay = $task['ticket_status'];
                            if ($statusDisplay === 'New' && !empty($task['allocated_user_name']) && $task['allocated_user_name'] !== 'Unassigned') {
                                $statusDisplay = 'Assigned';
                            }
                        ?>
                        <tr>
                            <td class="text-nowrap">
                                <a href="<?= base_url('tickets/view/' . $task['ticket_id']) ?>" class="ticket-no-link" title="Click to view task details">
                                    <?= htmlspecialchars($task['ticket_number']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold fs-7"><?= htmlspecialchars($task['task_title']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($task['description']) ?></small>
                            </td>
                            <td class="text-nowrap">
                                <span class="badge bg-<?= $task['priority'] === 'Critical' ? 'danger' : ($task['priority'] === 'High' ? 'warning text-dark' : 'secondary') ?>">
                                    <?= $task['priority'] ?>
                                </span>
                            </td>
                            <td class="text-nowrap"><?= htmlspecialchars($task['allocated_user_name'] ?? 'Unassigned') ?></td>
                            <td class="text-nowrap"><?= htmlspecialchars($task['creator_name'] ?? 'System') ?></td>
                            <td class="fs-8 text-nowrap"><?= format_datetime($task['due_date']) ?></td>
                            <td class="text-nowrap"><?= get_status_badge($statusDisplay) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
