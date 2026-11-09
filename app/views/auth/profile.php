<div class="container-fluid px-4 py-4">
    <!-- Top Profile Banner Card -->
    <div class="card border-0 shadow-sm mb-4 overflow-hidden" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff;">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start justify-content-between gap-4">
                <div class="d-flex flex-column flex-sm-row align-items-center align-items-sm-start gap-3 text-center text-sm-start">
                    <div class="user-avatar shadow-lg border border-3 border-white text-dark bg-white fw-bold d-flex align-items-center justify-content-center rounded-circle" style="width: 84px; height: 84px; font-size: 2.2rem; flex-shrink: 0;">
                        <?= strtoupper(substr($user['full_name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 justify-content-center justify-content-sm-start flex-wrap">
                            <h4 class="fw-bold mb-0 text-white"><?= htmlspecialchars($user['full_name'] ?? '') ?></h4>
                            <span class="badge bg-primary px-3 py-1 fs-8 text-uppercase tracking-wider"><?= htmlspecialchars($user['role_name'] ?? 'Employee') ?></span>
                            <?php if (!empty($user['designation'])): ?>
                                <span class="badge bg-info text-dark px-3 py-1 fs-8 fw-bold"><i class="fas fa-briefcase me-1"></i><?= htmlspecialchars($user['designation']) ?></span>
                            <?php endif; ?>
                            <span class="badge bg-success-subtle text-success border border-success px-2 py-1 fs-8"><i class="fas fa-check-circle me-1"></i><?= htmlspecialchars($user['status'] ?? 'Active') ?></span>
                        </div>
                        <p class="text-white-50 fs-7 mb-2 mt-1">
                            <i class="fas fa-id-badge me-1"></i> Code: <strong class="text-white"><?= htmlspecialchars($user['user_code'] ?? 'N/A') ?></strong> &nbsp;|&nbsp; 
                            <i class="fas fa-building me-1"></i> Dept: <strong class="text-white"><?= htmlspecialchars($user['department'] ?? 'Operations') ?></strong>
                        </p>
                        <div class="d-flex align-items-center gap-3 fs-8 text-white-50 flex-wrap justify-content-center justify-content-sm-start">
                            <span><i class="fas fa-envelope me-1"></i> <?= htmlspecialchars($user['email'] ?? '') ?></span>
                            <span><i class="fas fa-phone me-1"></i> <?= htmlspecialchars($user['mobile'] ?? 'N/A') ?></span>
                            <span><i class="fas fa-user-shield me-1"></i> Manager: <?= htmlspecialchars($user['manager_name'] ?? 'Super Admin / None') ?></span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="<?= base_url('change-password') ?>" class="btn btn-light btn-sm fw-bold shadow-sm">
                        <i class="fas fa-key me-1 text-primary"></i> Change Password
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stat Widgets -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 h-100 border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-8 fw-bold text-uppercase d-block mb-1">Total Tasks Handled</span>
                        <h3 class="fw-bold mb-0 text-dark"><?= number_format($stats['total_handled'] ?? 0) ?></h3>
                        <small class="text-muted fs-8">Maker: <?= $stats['maker_count'] ?> | Checker: <?= $stats['checker_count'] ?></small>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-circle fs-4">
                        <i class="fas fa-tasks"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 h-100 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-8 fw-bold text-uppercase d-block mb-1">Delivered / Completed</span>
                        <h3 class="fw-bold mb-0 text-success"><?= number_format($stats['delivered_count'] ?? 0) ?></h3>
                        <small class="text-muted fs-8">Successful Closures</small>
                    </div>
                    <div class="bg-success-subtle text-success p-3 rounded-circle fs-4">
                        <i class="fas fa-check-double"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 h-100 border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-8 fw-bold text-uppercase d-block mb-1">Bucket Active</span>
                        <h3 class="fw-bold mb-0" style="color: #b45309;"><?= number_format($stats['bucket_count'] ?? 0) ?></h3>
                        <small class="text-muted fs-8">Pending Work Items</small>
                    </div>
                    <div class="p-3 rounded-circle fs-4" style="background-color: #fef3c7; color: #b45309;">
                        <i class="fas fa-inbox"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 h-100 border-start border-4 border-info">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-8 fw-bold text-uppercase d-block mb-1">KT & Certifications</span>
                        <h3 class="fw-bold mb-0 text-info"><?= number_format($stats['kt_count'] ?? 0) ?></h3>
                        <small class="text-muted fs-8">Passed Certs: <?= $stats['cert_count'] ?></small>
                    </div>
                    <div class="bg-info-subtle text-info p-3 rounded-circle fs-4">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- User Information Details Column -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-user-circle me-2 text-primary"></i>User Account Details</h6>
                    <span class="badge bg-light text-secondary border">Account Facts</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted fs-8 fw-bold mb-1">FULL NAME</label>
                            <div class="form-control bg-light fw-bold text-dark fs-7"><?= htmlspecialchars($user['full_name'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted fs-8 fw-bold mb-1">EMPLOYEE CODE</label>
                            <div class="form-control bg-light fw-bold text-dark fs-7"><?= htmlspecialchars($user['user_code'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted fs-8 fw-bold mb-1">SYSTEM ROLE</label>
                            <div class="form-control bg-light fw-bold text-dark fs-7"><?= htmlspecialchars($user['role_name'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted fs-8 fw-bold mb-1">EMAIL ADDRESS</label>
                            <div class="form-control bg-light fw-bold text-dark fs-7"><?= htmlspecialchars($user['email'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted fs-8 fw-bold mb-1">MOBILE NUMBER</label>
                            <div class="form-control bg-light fw-bold text-dark fs-7"><?= htmlspecialchars($user['mobile'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted fs-8 fw-bold mb-1">DEPARTMENT</label>
                            <div class="form-control bg-light fw-bold text-dark fs-7"><?= htmlspecialchars($user['department'] ?? 'Operations') ?></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted fs-8 fw-bold mb-1">REPORTING MANAGER</label>
                            <div class="form-control bg-light fw-bold text-dark fs-7"><?= htmlspecialchars($user['manager_name'] ?? 'Super Admin / None') ?></div>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted fs-8 fw-bold mb-1">ACCOUNT CREATED</label>
                            <div class="form-control bg-light fs-8 text-secondary">
                                <?= !empty($user['created_at']) ? date('M d, Y H:i', strtotime($user['created_at'])) : 'N/A' ?>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted fs-8 fw-bold mb-1">LAST LOGIN</label>
                            <div class="form-control bg-light fs-8 text-secondary">
                                <?= !empty($user['last_login']) ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Role Activities & Skills Matrix Column -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-layer-group me-2 text-primary"></i>Key Role Activities & Skill Matrix</h6>
                    <span class="badge bg-primary-subtle text-primary border border-primary fs-8"><?= count($skills) ?> Sub-Activities</span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($skills)): ?>
                    <div class="text-center py-4 text-muted fs-7">
                        <i class="fas fa-info-circle me-1 text-primary"></i> No specific sub-activity skill mappings assigned to your profile yet.
                    </div>
                    <?php else: ?>
                    <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0 fs-7">
                            <thead class="table-light sticky-top fs-8 text-muted">
                                <tr>
                                    <th class="ps-3">DIVISION & ACTIVITY</th>
                                    <th>SUB-ACTIVITY</th>
                                    <th>ASSIGNED ROLE</th>
                                    <th class="pe-3 text-end">KT STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($skills as $sk): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($sk['division_name'] ?? 'General') ?></div>
                                        <small class="text-muted fs-8"><?= htmlspecialchars($sk['activity_name'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark"><?= htmlspecialchars($sk['sub_activity_name'] ?? '') ?></span>
                                    </td>
                                    <td>
                                        <?php if ($sk['role_type'] === 'Maker'): ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary fs-8">Maker</span>
                                        <?php elseif ($sk['role_type'] === 'Checker'): ?>
                                            <span class="badge fs-8 fw-semibold" style="background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a;">Checker</span>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success border border-success fs-8">Maker & Checker</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="pe-3 text-end">
                                        <?php if (!empty($sk['acknowledged_at']) || !empty($sk['is_learned'])): ?>
                                            <span class="badge bg-success-subtle text-success border border-success fs-8"><i class="fas fa-check me-1"></i>Acknowledged</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary fs-8">In Progress</span>
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

            <!-- Account Related History & Facts (Audit Trail) -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-history me-2 text-primary"></i>Account History & Activity Audit Trail</h6>
                    <span class="badge bg-light text-secondary border">Recent Actions</span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($auditTrail)): ?>
                    <div class="text-center py-4 text-muted fs-7">
                        <i class="fas fa-history me-1"></i> No recent account activity recorded.
                    </div>
                    <?php else: ?>
                    <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0 fs-8">
                            <thead class="table-light sticky-top text-muted">
                                <tr>
                                    <th class="ps-3">DATE & TIME</th>
                                    <th>MODULE</th>
                                    <th>ACTION PERFORMED</th>
                                    <th class="pe-3 text-end">IP ADDRESS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($auditTrail as $log): ?>
                                <tr>
                                    <td class="ps-3 text-nowrap text-secondary">
                                        <i class="far fa-clock me-1 text-muted"></i>
                                        <?= date('M d, Y H:i:s', strtotime($log['created_at'])) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($log['module'] ?? 'System') ?></span>
                                    </td>
                                    <td class="fw-semibold text-dark">
                                        <?= htmlspecialchars($log['action'] ?? 'Action') ?>
                                    </td>
                                    <td class="pe-3 text-end text-muted font-monospace fs-8">
                                        <?= htmlspecialchars($log['ip_address'] ?? '::1') ?>
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
    </div>
</div>
