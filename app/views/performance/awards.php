<?php
/**
 * Datamatics Annual Achievers Awards (AAA) & DM-ISPARK Recognition Hub
 * Implements full awards framework from awards contest.xlsx:
 * - 20 Awards across 6 Categories
 * - Monthly, Quarterly, Annual, and Milestone tiers
 * - 6-Factor Automated Scoring Engine (30% + 25% + 15% + 10% + 10% + 10%)
 * - Interactive Podium, Hall of Fame Showcase & Slideshow launcher
 */
?>

<div class="container-fluid px-4 py-4" id="awardsHub">

    <!-- HERO HEADER -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden position-relative" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);">
        <div class="position-absolute top-0 end-0 p-4 opacity-10 pointer-events-none">
            <i class="fas fa-award fa-10x text-warning"></i>
        </div>
        <div class="card-body p-4 p-lg-5 text-white position-relative" style="z-index: 2;">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-warning bg-opacity-25 border border-warning border-opacity-50 text-warning fw-bold fs-7 mb-3">
                        <i class="fas fa-trophy"></i> Datamatics Annual Achievers Awards (AAA) &amp; Recognition Hub
                    </div>
                    <h2 class="fw-black mb-2 tracking-tight display-6">
                        Excellence, Innovation &amp; <span class="text-warning">Operational Honours</span>
                    </h2>
                    <p class="text-white-50 fs-6 mb-0 max-w-2xl">
                        Structured recognition architecture aligned with corporate Datamatics AAA Policy v5.0. Celebrate monthly high-performers, quarterly trailblazers, annual legends, and long-service milestones.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <button type="button" class="btn btn-outline-light rounded-pill px-3 py-2 fw-semibold shadow-sm hover-scale" data-bs-toggle="modal" data-bs-target="#scoringFormulaModal">
                            <i class="fas fa-calculator text-warning me-1"></i> Scoring Engine
                        </button>
                        <?php if (is_super_admin() || is_senior_management() || is_manager()): ?>
                        <button type="button" class="btn btn-warning rounded-pill px-3 py-2 fw-bold text-dark shadow-sm hover-scale" data-bs-toggle="modal" data-bs-target="#bestowAwardModal">
                            <i class="fas fa-plus-circle me-1"></i> Bestow Award
                        </button>
                        <?php endif; ?>
                        <a href="<?= base_url('performance/awards-slideshow') ?>" target="_blank" class="btn btn-danger rounded-pill px-3 py-2 fw-bold shadow-sm hover-scale" title="Launch Presentation for Floor Signage">
                            <i class="fas fa-tv me-1"></i> Live Slideshow
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    <?php if ($flash = Session::getFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4 border-0" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif ($flash = Session::getFlash('danger')): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4 border-0" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- KPI STAT SUMMARY TILES -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift border-start border-4 border-primary">
                <div class="card-body p-3 p-lg-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fs-7 fw-bold text-uppercase">Total Honours Conferred</span>
                        <div class="avatar-sm rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center">
                            <i class="fas fa-medal fs-6"></i>
                        </div>
                    </div>
                    <h3 class="fw-black text-dark mb-1"><?= $totalAwardsConferred ?></h3>
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill fs-8 fw-semibold">
                        Permanent Hall of Fame
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift border-start border-4 border-warning">
                <div class="card-body p-3 p-lg-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fs-7 fw-bold text-uppercase">Current Period Winners</span>
                        <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center" style="background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; width: 36px; height: 36px;">
                            <i class="fas fa-trophy fs-6"></i>
                        </div>
                    </div>
                    <h3 class="fw-black text-dark mb-1"><?= count($winners) ?></h3>
                    <span class="badge rounded-pill fs-8 fw-semibold" style="background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a;">
                        <?= htmlspecialchars(strtoupper($periodKey)) ?> Tier
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift border-start border-4 border-success">
                <div class="card-body p-3 p-lg-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fs-7 fw-bold text-uppercase">Standard Award Categories</span>
                        <div class="avatar-sm rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center">
                            <i class="fas fa-cubes fs-6"></i>
                        </div>
                    </div>
                    <h3 class="fw-black text-dark mb-1">20 Catalogued</h3>
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill fs-8 fw-semibold">
                        Role-Based &amp; Tiered
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift border-start border-4 border-info">
                <div class="card-body p-3 p-lg-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fs-7 fw-bold text-uppercase">Automated Evaluation</span>
                        <div class="avatar-sm rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center">
                            <i class="fas fa-robot fs-6"></i>
                        </div>
                    </div>
                    <h3 class="fw-black text-dark mb-1">6 Weighted KPIs</h3>
                    <span class="badge bg-info bg-opacity-10 text-info rounded-pill fs-8 fw-semibold">
                        30% Prod • 25% Qual • 15% SLA
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- TIER & PERIOD SELECTION CONTROL BAR -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-lg-4">
            <div class="row align-items-center g-3">
                <div class="col-lg-7">
                    <!-- Period Tier Buttons -->
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?= base_url('performance/awards?period_type=monthly&period_key=2026-10') ?>" 
                           class="btn <?= $periodType === 'monthly' ? 'btn-primary' : 'btn-outline-secondary' ?> btn-sm rounded-pill fw-bold px-3">
                            <i class="fas fa-calendar-alt me-1"></i> Monthly Recognition
                        </a>
                        <a href="<?= base_url('performance/awards?period_type=quarterly&period_key=2026-Q3') ?>" 
                           class="btn <?= $periodType === 'quarterly' ? 'btn-primary' : 'btn-outline-secondary' ?> btn-sm rounded-pill fw-bold px-3">
                            <i class="fas fa-chart-pie me-1"></i> Quarterly Achievers
                        </a>
                        <a href="<?= base_url('performance/awards?period_type=annual&period_key=2026-FY') ?>" 
                           class="btn <?= $periodType === 'annual' ? 'btn-primary' : 'btn-outline-secondary' ?> btn-sm rounded-pill fw-bold px-3">
                            <i class="fas fa-crown text-warning me-1"></i> Annual Prestigious Honours
                        </a>
                        <a href="<?= base_url('performance/awards?period_type=milestone&period_key=10-Years') ?>" 
                           class="btn <?= $periodType === 'milestone' ? 'btn-primary' : 'btn-outline-secondary' ?> btn-sm rounded-pill fw-bold px-3">
                            <i class="fas fa-hourglass-half me-1"></i> Long Service Milestones
                        </a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="d-flex gap-2 justify-content-lg-end">
                        <!-- Period Key Selector -->
                        <select class="form-select form-select-sm rounded-pill fw-semibold w-auto" onchange="location.href='<?= base_url("performance/awards?period_type={$periodType}&category={$category}&period_key=") ?>' + this.value;">
                            <?php 
                            $availKeys = $distinctPeriods[$periodType] ?? [];
                            if (empty($availKeys)) $availKeys = [$periodKey];
                            foreach ($availKeys as $pk): 
                            ?>
                                <option value="<?= htmlspecialchars($pk) ?>" <?= $pk === $periodKey ? 'selected' : '' ?>>
                                    Period: <?= htmlspecialchars($pk) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <!-- Category Filter -->
                        <select class="form-select form-select-sm rounded-pill fw-semibold w-auto" onchange="location.href='<?= base_url("performance/awards?period_type={$periodType}&period_key={$periodKey}&category=") ?>' + this.value;">
                            <option value="all" <?= $category === 'all' ? 'selected' : '' ?>>All Categories</option>
                            <option value="Performance" <?= $category === 'Performance' ? 'selected' : '' ?>>Performance</option>
                            <option value="Quality" <?= $category === 'Quality' ? 'selected' : '' ?>>Quality</option>
                            <option value="Productivity" <?= $category === 'Productivity' ? 'selected' : '' ?>>Productivity</option>
                            <option value="Innovation" <?= $category === 'Innovation' ? 'selected' : '' ?>>Innovation &amp; AI</option>
                            <option value="Leadership" <?= $category === 'Leadership' ? 'selected' : '' ?>>Leadership</option>
                            <option value="Strategic" <?= $category === 'Strategic' ? 'selected' : '' ?>>Strategic / Enterprise</option>
                            <option value="Service" <?= $category === 'Service' ? 'selected' : '' ?>>Service &amp; Loyalty</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GRAND PODIUM PRESENTATION (Top 3 in selected horizon) -->
    <?php if ($periodType === 'milestone'): ?>
    <div class="alert alert-primary border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fs-4" style="width: 50px; height: 50px; min-width: 50px;">
            <i class="fas fa-hourglass-half"></i>
        </div>
        <div>
            <h6 class="fw-bold mb-1 text-primary">Long Service Milestones • Dedication &amp; Loyalty</h6>
            <p class="fs-8 text-secondary mb-0">Honouring sustained commitment, dedication, and organizational loyalty across career tenure milestones.</p>
        </div>
    </div>
    <?php endif; ?>

    <?php 
    $podiumWinners = array_slice($winners, 0, 3);
    if (!empty($podiumWinners)): 
    ?>
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light overflow-hidden">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-dark mb-0">
                <i class="fas fa-trophy text-warning me-2"></i>Grand Podium • <?= htmlspecialchars(strtoupper($periodKey)) ?> Honours
            </h5>
            <span class="badge bg-dark rounded-pill px-3 py-1 fs-8">Top Ranked Performers</span>
        </div>
        <div class="card-body p-4">
            <div class="row align-items-end justify-content-center g-3 text-center">
                
                <!-- 2ND PLACE (SILVER) -->
                <?php if (isset($podiumWinners[1])): $w2 = $podiumWinners[1]; ?>
                <div class="col-md-4 col-sm-6 order-2 order-md-1">
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100 hover-lift border-top border-4 border-secondary">
                        <div class="position-relative d-inline-block mx-auto mb-3">
                            <div class="avatar-lg rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center mx-auto fs-3 fw-bold border border-2 border-secondary" style="width: 70px; height: 70px;">
                                <?= strtoupper(substr($w2['recipient_name'], 0, 2)) ?>
                            </div>
                            <span class="position-absolute bottom-0 end-0 badge rounded-pill bg-secondary shadow-sm">
                                🥈 2nd
                            </span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($w2['recipient_name']) ?></h6>
                        <span class="fs-8 text-muted d-block mb-2"><?= htmlspecialchars($w2['recipient_role']) ?></span>
                        <div class="badge rounded-pill mb-2 px-3 py-1 fs-8" style="background-color: <?= $w2['badge_color'] ?>15; color: <?= $w2['badge_color'] ?>;">
                            <i class="fas <?= $w2['badge_icon'] ?> me-1"></i><?= htmlspecialchars($w2['award_name']) ?>
                        </div>
                        <div class="fw-black text-secondary fs-5 mb-2"><?= number_format($w2['score'], 1) ?>%</div>
                        <p class="fs-8 text-muted fst-italic mb-0 line-clamp-2">"<?= htmlspecialchars($w2['citation']) ?>"</p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 1ST PLACE (GOLD) -->
                <?php if (isset($podiumWinners[0])): $w1 = $podiumWinners[0]; ?>
                <div class="col-md-4 col-sm-12 order-1 order-md-2 mb-3 mb-md-0">
                    <div class="card border-0 shadow-lg rounded-4 p-4 h-100 hover-lift position-relative" style="background: linear-gradient(180deg, #fffbeb 0%, #ffffff 100%); border-top: 5px solid #f59e0b !important;">
                        <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-warning text-dark fw-bold px-3 py-1 shadow-sm fs-7">
                            👑 Champion of the Horizon
                        </span>
                        <div class="position-relative d-inline-block mx-auto mb-3 mt-2">
                            <div class="avatar-xl rounded-circle d-flex align-items-center justify-content-center mx-auto fs-2 fw-black shadow" style="width: 90px; height: 90px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff; border: 3px solid #b45309; text-shadow: 0 1px 3px rgba(0,0,0,0.3);">
                                <?= strtoupper(substr($w1['recipient_name'], 0, 2)) ?>
                            </div>
                            <span class="position-absolute bottom-0 end-0 badge rounded-pill bg-warning text-dark shadow-sm fs-7">
                                🥇 1st
                            </span>
                        </div>
                        <h5 class="fw-black text-dark mb-1"><?= htmlspecialchars($w1['recipient_name']) ?></h5>
                        <span class="fs-7 text-muted d-block mb-2"><?= htmlspecialchars($w1['recipient_role']) ?></span>
                        <div class="badge rounded-pill mb-2 px-3 py-1 fs-7 shadow-sm" style="background-color: <?= $w1['badge_color'] ?>; color: #fff;">
                            <i class="fas <?= $w1['badge_icon'] ?> me-1"></i><?= htmlspecialchars($w1['award_name']) ?>
                        </div>
                        <div class="fw-black fs-3 mb-2" style="color: #b45309;"><?= number_format($w1['score'], 1) ?>%</div>
                        <p class="fs-7 text-secondary fst-italic mb-0">"<?= htmlspecialchars($w1['citation']) ?>"</p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 3RD PLACE (BRONZE) -->
                <?php if (isset($podiumWinners[2])): $w3 = $podiumWinners[2]; ?>
                <div class="col-md-4 col-sm-6 order-3 order-md-3">
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100 hover-lift border-top border-4 border-warning border-opacity-50">
                        <div class="position-relative d-inline-block mx-auto mb-3">
                            <div class="avatar-lg rounded-circle bg-warning bg-opacity-10 text-danger d-flex align-items-center justify-content-center mx-auto fs-3 fw-bold border border-2 border-warning" style="width: 70px; height: 70px;">
                                <?= strtoupper(substr($w3['recipient_name'], 0, 2)) ?>
                            </div>
                            <span class="position-absolute bottom-0 end-0 badge rounded-pill bg-danger shadow-sm">
                                🥉 3rd
                            </span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($w3['recipient_name']) ?></h6>
                        <span class="fs-8 text-muted d-block mb-2"><?= htmlspecialchars($w3['recipient_role']) ?></span>
                        <div class="badge rounded-pill mb-2 px-3 py-1 fs-8" style="background-color: <?= $w3['badge_color'] ?>15; color: <?= $w3['badge_color'] ?>;">
                            <i class="fas <?= $w3['badge_icon'] ?> me-1"></i><?= htmlspecialchars($w3['award_name']) ?>
                        </div>
                        <div class="fw-black text-danger fs-5 mb-2"><?= number_format($w3['score'], 1) ?>%</div>
                        <p class="fs-8 text-muted fst-italic mb-0 line-clamp-2">"<?= htmlspecialchars($w3['citation']) ?>"</p>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- COMPLETE HONOURS SHOWCASE GALLERY -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0">
                    <i class="fas fa-medal text-primary me-2"></i>Awards Conferred in <?= htmlspecialchars(strtoupper($periodKey)) ?>
                </h5>
                <span class="fs-8 text-muted">Permanent achievement registry mapped to employee profiles</span>
            </div>
            <span class="badge bg-light text-dark border rounded-pill px-3 py-2 fw-semibold">
                Showing <?= count($winners) ?> Conferred Records
            </span>
        </div>
        <div class="card-body p-4">
            <?php if (empty($winners)): ?>
                <div class="text-center py-5">
                    <div class="avatar-lg rounded-circle bg-light text-muted d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-award fa-3x"></i>
                    </div>
                    <h5 class="fw-bold text-dark">No Awards Conferred Yet</h5>
                    <p class="text-muted fs-7 mb-3">No award winners have been registered for period <?= htmlspecialchars($periodKey) ?>.</p>
                    <?php if (is_super_admin() || is_senior_management() || is_manager()): ?>
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#bestowAwardModal">
                        <i class="fas fa-plus-circle me-1"></i> Bestow an Award Now
                    </button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($winners as $w): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift overflow-hidden position-relative">
                            <div class="h-2" style="height: 4px; background: <?= $w['badge_color'] ?>;"></div>
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="badge rounded-pill px-3 py-1 fs-8" style="background-color: <?= $w['badge_color'] ?>15; color: <?= $w['badge_color'] ?>; border: 1px solid <?= $w['badge_color'] ?>30;">
                                        <i class="fas <?= $w['badge_icon'] ?> me-1"></i><?= htmlspecialchars($w['award_name']) ?>
                                    </div>
                                    <span class="badge bg-light text-secondary border rounded-pill fs-8">
                                        <?= htmlspecialchars(strtoupper($w['period_type'])) ?>
                                    </span>
                                </div>

                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="avatar-md rounded-circle text-white d-flex align-items-center justify-content-center fw-bold fs-5 shadow-sm" style="background-color: <?= $w['badge_color'] ?>; width: 50px; height: 50px;">
                                        <?= strtoupper(substr($w['recipient_name'], 0, 2)) ?>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0"><?= htmlspecialchars($w['recipient_name']) ?></h6>
                                        <span class="fs-8 text-muted d-block"><?= htmlspecialchars($w['recipient_role']) ?></span>
                                        <?php if (!empty($w['user_code'])): ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill fs-9"><?= htmlspecialchars($w['user_code']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Composite Score & Breakdown -->
                                <div class="bg-light rounded-3 p-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fs-8 text-muted fw-bold">Composite Score</span>
                                        <span class="fw-black fs-6" style="color: <?= $w['badge_color'] ?>;"><?= number_format($w['score'], 1) ?>%</span>
                                    </div>
                                    <div class="row g-2 text-center fs-9 text-muted">
                                        <div class="col-4">
                                            <div class="p-1 rounded bg-white border">
                                                <div class="fw-bold text-dark"><?= number_format($w['productivity_score'], 0) ?>%</div>
                                                <span class="fs-10">Prod (30%)</span>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-1 rounded bg-white border">
                                                <div class="fw-bold text-dark"><?= number_format($w['quality_score'], 0) ?>%</div>
                                                <span class="fs-10">Qual (25%)</span>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-1 rounded bg-white border">
                                                <div class="fw-bold text-dark"><?= number_format($w['sla_score'], 0) ?>%</div>
                                                <span class="fs-10">SLA (15%)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Citation -->
                                <p class="fs-8 text-secondary fst-italic mb-3">
                                    "<?= htmlspecialchars($w['citation']) ?>"
                                </p>

                                <!-- Footer -->
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                    <span class="fs-9 text-muted">
                                        <i class="far fa-calendar-check me-1"></i><?= htmlspecialchars($w['awarded_date']) ?>
                                    </span>
                                    <?php if (is_super_admin() || is_senior_management()): ?>
                                    <form method="POST" action="<?= base_url('performance/delete-award-winner') ?>" onsubmit="return confirm('Are you sure you want to revoke this award?');">
                                        <input type="hidden" name="winner_id" value="<?= $w['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-xs rounded-pill" title="Revoke Award">
                                            <i class="fas fa-trash-alt"></i> Revoke
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>

                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- COMPLETE AWARDS MASTER DIRECTORY (ACCORDION / CATALOG) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold text-dark mb-0">
                    <i class="fas fa-book-open text-primary me-2"></i>Awards Catalog &amp; Criteria Matrix
                </h5>
                <span class="fs-8 text-muted">All 20 Datamatics AAA &amp; DM-ISPARK Recognitions with official evaluation rules</span>
            </div>
            <button class="btn btn-sm btn-outline-primary rounded-pill fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#awardsCatalogCollapse">
                <i class="fas fa-chevron-down me-1"></i> Toggle Catalog
            </button>
        </div>
        <div class="collapse show" id="awardsCatalogCollapse">
            <div class="card-body p-4 pt-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 fs-8">
                        <thead class="table-light">
                            <tr>
                                <th>Award Name</th>
                                <th>Category</th>
                                <th>Competition</th>
                                <th>Periodicity</th>
                                <th>Eligible Role</th>
                                <th>Winners</th>
                                <th>Recognition Focus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allAwards as $a): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-xs rounded-circle d-flex align-items-center justify-content-center text-white" style="background-color: <?= $a['badge_color'] ?>; width: 30px; height: 30px;">
                                            <i class="fas <?= $a['badge_icon'] ?> fs-8"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark d-block">
                                                <?= htmlspecialchars($a['award_name']) ?>
                                                <?php if ($a['award_code'] === 'AWD-STAR'): ?>
                                                    <span class="text-muted fw-normal fs-9">(Star Performer of the Month)</span>
                                                <?php endif; ?>
                                            </span>
                                            <span class="fs-9 text-muted"><?= htmlspecialchars($a['award_code']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border rounded-pill">
                                        <?= htmlspecialchars($a['category']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-secondary fw-semibold">
                                        <?= htmlspecialchars(ucwords(str_replace('_', ' ', $a['competition_type']))) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">
                                        <?= htmlspecialchars(strtoupper($a['periodicity'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-warning bg-opacity-20 text-dark fw-bold rounded-pill">
                                        <?= htmlspecialchars($a['eligible_roles']) ?>
                                    </span>
                                </td>
                                <td class="fw-bold"><?= (int)$a['max_winners'] ?></td>
                                <td class="text-muted"><?= htmlspecialchars($a['description']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- MODAL: SCORING ENGINE & EVALUATION PREVIEW -->
<div class="modal fade" id="scoringFormulaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-black text-dark">
                    <i class="fas fa-calculator text-primary me-2"></i>Datamatics 6-Factor Automated Scoring Engine
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted fs-7 mb-4">
                    As defined in the official AAA Policy, awards are computed from weighted operational data logged across tickets, daily production registers, monthly scorecards, and error tracking:
                </p>

                <div class="row g-3 mb-4">
                    <div class="col-4">
                        <div class="p-3 rounded-3 bg-primary bg-opacity-10 text-center border border-primary border-opacity-25">
                            <h4 class="fw-black text-primary mb-0">30%</h4>
                            <span class="fs-8 fw-bold text-dark">Productivity</span>
                            <span class="fs-9 text-muted d-block">Points % &amp; Output</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 rounded-3 bg-success bg-opacity-10 text-center border border-success border-opacity-25">
                            <h4 class="fw-black text-success mb-0">25%</h4>
                            <span class="fs-8 fw-bold text-dark">Quality Accuracy</span>
                            <span class="fs-9 text-muted d-block">Internal &amp; External</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 rounded-3 bg-info bg-opacity-10 text-center border border-info border-opacity-25">
                            <h4 class="fw-black text-info mb-0">15%</h4>
                            <span class="fs-8 fw-bold text-dark">SLA Adherence</span>
                            <span class="fs-9 text-muted d-block">Turnaround Time</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 rounded-3 text-center border" style="background-color: #fef3c7; border-color: #fde68a !important;">
                            <h4 class="fw-black mb-0" style="color: #b45309;">10%</h4>
                            <span class="fs-8 fw-bold text-dark">Attendance</span>
                            <span class="fs-9 text-muted d-block">Shift Continuity</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 rounded-3 bg-purple bg-opacity-10 text-center border" style="background-color: #f3e8ff; border-color: #d8b4fe !important;">
                            <h4 class="fw-black text-purple mb-0" style="color: #9333ea;">10%</h4>
                            <span class="fs-8 fw-bold text-dark">Innovation &amp; AI</span>
                            <span class="fs-9 text-muted d-block">PKT &amp; Automations</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 rounded-3 bg-secondary bg-opacity-10 text-center border">
                            <h4 class="fw-black text-secondary mb-0">10%</h4>
                            <span class="fs-8 fw-bold text-dark">Teamwork</span>
                            <span class="fs-9 text-muted d-block">Four-Eyes &amp; Support</span>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold text-dark mb-3">Live Contenders Preview for <?= htmlspecialchars($periodKey) ?></h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0 fs-8">
                        <thead class="table-light">
                            <tr>
                                <th>Rank</th>
                                <th>Associate</th>
                                <th>Prod (30%)</th>
                                <th>Qual (25%)</th>
                                <th>SLA (15%)</th>
                                <th>Composite</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contenders as $idx => $c): ?>
                            <tr>
                                <td class="fw-bold">#<?= $idx + 1 ?></td>
                                <td>
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($c['full_name']) ?></span>
                                    <span class="fs-9 text-muted">(<?= htmlspecialchars($c['user_code']) ?>)</span>
                                </td>
                                <td><?= $c['productivity_score'] ?>%</td>
                                <td><?= $c['quality_score'] ?>%</td>
                                <td><?= $c['sla_score'] ?>%</td>
                                <td class="fw-black text-primary fs-7"><?= $c['composite_score'] ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: BESTOW / NOMINATE AN OFFICIAL AWARD -->
<?php if (is_super_admin() || is_senior_management() || is_manager()): ?>
<div class="modal fade" id="bestowAwardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form method="POST" action="<?= base_url('performance/bestow-award') ?>">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-black text-dark">
                        <i class="fas fa-trophy text-warning me-2"></i>Bestow Official Award
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold fs-8">Select Award <span class="text-danger">*</span></label>
                            <select name="award_id" class="form-select rounded-3 fs-8" required>
                                <option value="">-- Choose Award --</option>
                                <?php foreach ($allAwards as $a): ?>
                                <option value="<?= $a['id'] ?>">
                                    <?= htmlspecialchars($a['award_name']) ?> (<?= htmlspecialchars($a['category']) ?> • <?= htmlspecialchars(strtoupper($a['periodicity'])) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold fs-8">Period Tier <span class="text-danger">*</span></label>
                            <select name="period_type" class="form-select rounded-3 fs-8" required>
                                <option value="monthly" <?= $periodType === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                                <option value="quarterly" <?= $periodType === 'quarterly' ? 'selected' : '' ?>>Quarterly</option>
                                <option value="annual" <?= $periodType === 'annual' ? 'selected' : '' ?>>Annual</option>
                                <option value="milestone" <?= $periodType === 'milestone' ? 'selected' : '' ?>>Milestone</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold fs-8">Period Key <span class="text-danger">*</span></label>
                            <input type="text" name="period_key" class="form-control rounded-3 fs-8" value="<?= htmlspecialchars($periodKey) ?>" placeholder="e.g. 2026-10 or 2026-Q3" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold fs-8">Select Individual Recipient</label>
                            <select name="user_id" class="form-select rounded-3 fs-8">
                                <option value="">-- Choose Individual Associate --</option>
                                <?php foreach ($allUsers as $u): ?>
                                <option value="<?= $u['id'] ?>">
                                    <?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['user_code']) ?> - <?= htmlspecialchars($u['department']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="fs-9 text-muted">Leave blank if bestowing to an entire project team.</span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold fs-8">Select Project Team (if Team Award)</label>
                            <select name="project_id" class="form-select rounded-3 fs-8">
                                <option value="">-- Choose Project Team --</option>
                                <?php foreach ($allProjects as $p): ?>
                                <option value="<?= $p['id'] ?>">
                                    <?= htmlspecialchars($p['project_name']) ?> (<?= htmlspecialchars($p['code']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold fs-8">Recipient Name Override (optional)</label>
                            <input type="text" name="recipient_name" class="form-control rounded-3 fs-8" placeholder="Auto-fills from user or project if left blank">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold fs-8">Composite Score (%)</label>
                            <input type="number" step="0.1" name="score" class="form-control rounded-3 fs-8" value="95.5" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold fs-8">Podium Rank</label>
                            <select name="rank_position" class="form-select rounded-3 fs-8">
                                <option value="1">1st Place (Gold)</option>
                                <option value="2">2nd Place (Silver)</option>
                                <option value="3">3rd Place (Bronze)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold fs-8">Official Appraisal Citation &amp; Accomplishment <span class="text-danger">*</span></label>
                            <textarea name="citation" rows="3" class="form-control rounded-3 fs-8" placeholder="Specify measurable business impact, turnaround turnaround, or leadership demonstration..." required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark">
                        <i class="fas fa-check-circle me-1"></i> Bestow Honour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.fw-black { font-weight: 900; }
.tracking-tight { letter-spacing: -0.03em; }
.hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-lift:hover { transform: translateY(-4px); box-shadow: 0 12px 24px -8px rgba(0,0,0,0.12) !important; }
.hover-scale { transition: transform 0.2s ease; }
.hover-scale:hover { transform: scale(1.04); }
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.fs-7 { font-size: 0.875rem; }
.fs-8 { font-size: 0.8125rem; }
.fs-9 { font-size: 0.75rem; }
.fs-10 { font-size: 0.6875rem; }
</style>
