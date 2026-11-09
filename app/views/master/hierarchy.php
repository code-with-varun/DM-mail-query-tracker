<?php
/**
 * Organizational Hierarchy Tree View
 * Clean, Executive-Grade Top-Down Visual Org Chart
 * Modeled after modern visual hierarchy diagram standards with dynamic branch colors,
 * zoom/pan canvas, instant search, node expand/collapse, and interactive details drawer.
 */
function getInitials(string $name): string {
    $parts = preg_split('/\s+/', trim($name));
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        if (!empty($p)) {
            $initials .= strtoupper(substr($p, 0, 1));
        }
    }
    return $initials ?: 'U';
}
?>

<style>
/* Canvas and Layout */
.org-chart-wrapper {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    position: relative;
    overflow: hidden;
    min-height: 720px;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
    user-select: none;
}

.org-chart-canvas {
    padding: 24px 20px 60px;
    display: flex;
    flex-direction: column;
    align-items: center;
    transform-origin: top center;
    transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    min-width: 100%;
    cursor: grab;
}
.org-chart-canvas.is-dragging {
    cursor: grabbing;
}

/* Background Subtle Dot Pattern */
.org-chart-bg-pattern {
    background-image: radial-gradient(#cbd5e1 1.2px, transparent 1.2px);
    background-size: 28px 28px;
    background-position: 0 0;
}

/* Org Node Cards */
.org-node-card {
    position: relative;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
    text-align: center;
    border: 1px solid rgba(226, 232, 240, 0.9);
    cursor: pointer;
    transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    z-index: 2;
}
.org-node-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 35px -8px rgba(15, 23, 42, 0.16);
    border-color: #cbd5e1;
}
.org-node-card.is-matched {
    box-shadow: 0 0 0 4px #3b82f6, 0 20px 35px -8px rgba(59, 130, 246, 0.35) !important;
    animation: pulse-node 1.5s infinite;
}
@keyframes pulse-node {
    0% { transform: scale(1); }
    50% { transform: scale(1.04); }
    100% { transform: scale(1); }
}

/* Avatar popping over card header */
.node-avatar-top {
    position: absolute;
    top: -24px;
    left: 50%;
    transform: translateX(-50%);
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #ffffff;
    box-shadow: 0 6px 14px rgba(15, 23, 42, 0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    font-weight: 700;
    border: 3px solid #ffffff;
    z-index: 3;
}

/* Name Banner Bar */
.node-name-bar {
    color: #ffffff;
    font-weight: 700;
    font-size: 0.8rem;
    padding: 7px 12px;
    border-radius: 8px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-bottom: 6px;
    line-height: 1.25;
}
.node-position {
    font-size: 0.73rem;
    font-weight: 600;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    padding: 0 8px;
}
.node-code-badge {
    font-size: 0.65rem;
    color: #94a3b8;
    margin-top: 3px;
    font-weight: 500;
}

/* Color Themes */
.theme-slate .node-avatar-top { color: #f59e0b; background: #0f172a; }
.theme-slate .node-name-bar { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); }

.theme-blue .node-avatar-top { color: #2563eb; }
.theme-blue .node-name-bar { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); }
.theme-blue.sub-card .sub-position { color: #2563eb; }

.theme-green .node-avatar-top { color: #059669; }
.theme-green .node-name-bar { background: linear-gradient(135deg, #059669 0%, #047857 100%); }
.theme-green.sub-card .sub-position { color: #059669; }

.theme-orange .node-avatar-top { color: #ea580c; }
.theme-orange .node-name-bar { background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%); }
.theme-orange.sub-card .sub-position { color: #ea580c; }

.theme-purple .node-avatar-top { color: #7c3aed; }
.theme-purple .node-name-bar { background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); }
.theme-purple.sub-card .sub-position { color: #7c3aed; }

.theme-teal .node-avatar-top { color: #0d9488; }
.theme-teal .node-name-bar { background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); }
.theme-teal.sub-card .sub-position { color: #0d9488; }

/* Subordinate Pill Card (Direct Reports) */
.sub-card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 6px 18px -4px rgba(15, 23, 42, 0.07);
    padding: 8px 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    width: 215px;
    border: 1px solid rgba(226, 232, 240, 0.9);
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    z-index: 2;
}
.sub-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px -5px rgba(15, 23, 42, 0.12);
    border-color: #cbd5e1;
}
.sub-card.is-matched {
    box-shadow: 0 0 0 3px #3b82f6, 0 12px 24px -5px rgba(59, 130, 246, 0.35) !important;
}
.sub-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.82rem;
    color: #475569;
    font-weight: 700;
    flex-shrink: 0;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.03);
}
.sub-info {
    overflow: hidden;
    text-align: left;
    flex-grow: 1;
}
.sub-name {
    font-size: 0.77rem;
    font-weight: 700;
    color: #1e293b;
    text-transform: uppercase;
    line-height: 1.25;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.sub-position {
    font-size: 0.67rem;
    font-weight: 600;
    text-transform: uppercase;
    margin-top: 1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.sub-meta {
    font-size: 0.62rem;
    color: #94a3b8;
}

/* Branch Tree Connectors */
.stem-vertical {
    width: 2px;
    height: 24px;
    background: #94a3b8;
    margin: 0 auto;
    position: relative;
}

.tree-row {
    display: flex;
    justify-content: center;
    position: relative;
    padding-top: 22px;
    gap: 32px;
}
.tree-row::before {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 2px;
    height: 22px;
    background: #94a3b8;
}
.tree-col {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
}
.tree-col::before {
    content: '';
    position: absolute;
    top: -22px;
    left: 0;
    right: 0;
    height: 22px;
    border-top: 2px solid #94a3b8;
}
.tree-col:first-child::before {
    left: 50%;
    border-top-left-radius: 14px;
    border-left: 2px solid #94a3b8;
}
.tree-col:last-child::before {
    right: 50%;
    border-top-right-radius: 14px;
    border-right: 2px solid #94a3b8;
}
.tree-col:only-child::before {
    display: none;
}
.tree-col::after {
    content: '';
    position: absolute;
    top: -22px;
    left: 50%;
    width: 2px;
    height: 22px;
    background: #94a3b8;
    transform: translateX(-50%);
}

/* Sub-Tree Vertical Spine (Level 3) */
.sub-tree-spine {
    position: relative;
    padding-left: 26px;
    margin-top: 22px;
    transition: all 0.3s ease;
}
.sub-tree-spine::before {
    content: '';
    position: absolute;
    top: -22px;
    left: 10px;
    bottom: 22px;
    width: 2px;
    background: #94a3b8;
}
.sub-branch-item {
    position: relative;
    margin-bottom: 12px;
}
.sub-branch-item:last-child {
    margin-bottom: 0;
}
.sub-branch-item::before {
    content: '';
    position: absolute;
    top: 50%;
    left: -16px;
    width: 16px;
    height: 16px;
    border-bottom: 2px solid #94a3b8;
    border-left: 2px solid #94a3b8;
    border-bottom-left-radius: 10px;
    transform: translateY(-50%);
}

/* Collapse Toggle Button on Parent Node */
.node-collapse-btn {
    position: absolute;
    bottom: -13px;
    left: 50%;
    transform: translateX(-50%);
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 50rem;
    padding: 1px 8px;
    font-size: 0.65rem;
    font-weight: 700;
    color: #475569;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    display: inline-flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
    transition: all 0.15s ease;
    z-index: 4;
}
.node-collapse-btn:hover {
    background: #f1f5f9;
    color: #0f172a;
    border-color: #94a3b8;
}

/* Canvas Toolbar */
.chart-controls-bar {
    position: absolute;
    bottom: 20px;
    right: 20px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(8px);
    border: 1px solid #cbd5e1;
    border-radius: 50rem;
    padding: 4px 8px;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
    z-index: 10;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Offcanvas Person Profile Drawer */
.org-profile-drawer {
    width: 360px !important;
}
</style>

<div class="container-fluid py-3 px-4">

    <!-- Top Action & Info Bar -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-sitemap fs-4 text-primary"></i>
                <h4 class="fw-bold mb-0 text-dark">Organizational Hierarchy Tree</h4>
            </div>
            <p class="text-muted fs-8 mb-0">Executive Governance &rarr; Operations Management &rarr; Team Leadership &rarr; Operational Associates</p>
        </div>

        <!-- Global Action Controls -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 rounded-pill px-3 shadow-sm" id="btnCollapseAll">
                <i class="fas fa-compress-arrows-alt fs-8"></i>
                <span>Collapse All</span>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 rounded-pill px-3 shadow-sm" id="btnExpandAll">
                <i class="fas fa-expand-arrows-alt fs-8"></i>
                <span>Expand All</span>
            </button>
            <a href="<?= base_url('master/projects') ?>" class="btn btn-sm btn-primary d-flex align-items-center gap-1 rounded-pill px-3 shadow-sm">
                <i class="fas fa-cog fs-8"></i>
                <span>Manage Projects / Teams</span>
            </a>
        </div>
    </div>

    <!-- Quick Metric Statistics Pills & Live Search -->
    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-3">
        <div class="row g-3 align-items-center">
            <!-- Search & Filter Input -->
            <div class="col-lg-4 col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-search"></i></span>
                    <input type="text" id="orgTreeSearch" class="form-control border-start-0 bg-light" placeholder="Search by Name, Role, Employee ID...">
                    <button class="btn btn-light border border-start-0" type="button" id="clearOrgSearch" title="Clear Search">
                        <i class="fas fa-times text-muted"></i>
                    </button>
                </div>
            </div>

            <!-- Metric Counter Pills -->
            <div class="col-lg-8 col-md-7">
                <div class="d-flex align-items-center justify-content-md-end gap-2 flex-wrap fs-8">
                    <span class="badge bg-dark bg-opacity-10 text-dark px-2.5 py-1.5 rounded-pill border">
                        <i class="fas fa-crown text-warning me-1"></i>Executive Desk: <strong><?= $tree['counts']['executives'] ?? 2 ?></strong>
                    </span>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-2.5 py-1.5 rounded-pill border border-primary border-opacity-25">
                        <i class="fas fa-user-tie me-1"></i>Managers: <strong><?= $tree['counts']['managers'] ?? 1 ?></strong>
                    </span>
                    <span class="badge bg-success bg-opacity-10 text-success px-2.5 py-1.5 rounded-pill border border-success border-opacity-25">
                        <i class="fas fa-user-check me-1"></i>Team Leads: <strong><?= $tree['counts']['team_leads'] ?? 1 ?></strong>
                    </span>
                    <span class="badge bg-info bg-opacity-10 text-info px-2.5 py-1.5 rounded-pill border border-info border-opacity-25">
                        <i class="fas fa-users me-1"></i>Employees: <strong><?= $tree['counts']['employees'] ?? 5 ?></strong>
                    </span>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-2.5 py-1.5 rounded-pill border">
                        <i class="fas fa-project-diagram me-1"></i>Projects: <strong><?= $tree['counts']['projects'] ?? 5 ?></strong>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Visual Org Chart Viewport -->
    <div class="org-chart-wrapper org-chart-bg-pattern" id="chartWrapper">

        <!-- Zoom Controls Overlay -->
        <div class="chart-controls-bar">
            <button type="button" class="btn btn-sm btn-light border-0 rounded-circle" id="zoomOutBtn" title="Zoom Out" style="width: 28px; height: 28px; padding: 0;">
                <i class="fas fa-minus fs-9 text-muted"></i>
            </button>
            <span class="fs-9 fw-bold px-1 text-secondary" id="zoomLevelDisplay">100%</span>
            <button type="button" class="btn btn-sm btn-light border-0 rounded-circle" id="zoomInBtn" title="Zoom In" style="width: 28px; height: 28px; padding: 0;">
                <i class="fas fa-plus fs-9 text-muted"></i>
            </button>
            <span class="vr my-1 text-muted opacity-25"></span>
            <button type="button" class="btn btn-sm btn-light border-0 rounded-pill px-2 fs-9 text-muted" id="zoomResetBtn" title="Reset View">
                Reset
            </button>
        </div>

        <!-- The Transformable Tree Canvas -->
        <div class="org-chart-canvas" id="chartCanvas">

            <!-- ================= LEVEL 1: EXECUTIVE DESK (ROOT NODE) ================= -->
            <div class="org-node-card theme-slate" 
                 style="width: 330px; padding: 30px 16px 14px; margin-top: 24px;"
                 data-person-name="Super Admin & Senior Management"
                 data-person-role="Senior Management & Executive Desk"
                 data-person-code="EMP001, EMP009"
                 data-person-dept="Executive Governance"
                 data-person-email="admin@company.com"
                 onclick="openPersonModal(this)">
                
                <!-- Circular Avatar Header -->
                <div class="node-avatar-top shadow-sm">
                    <i class="fas fa-crown text-warning"></i>
                </div>

                <!-- Name Banner -->
                <div class="node-name-bar fs-7">
                    Senior Management & Executive Desk
                </div>

                <!-- Position & Code -->
                <div class="node-position text-secondary">
                    Executive Governance & Strategic Direction
                </div>
                <div class="node-code-badge d-flex align-items-center justify-content-center gap-2 mt-1">
                    <span><i class="fas fa-shield-alt text-warning me-1"></i>EMP001</span>
                    <span>&bull;</span>
                    <span>EMP009 (Vikram Malhotra)</span>
                </div>
            </div>

            <!-- Stem connecting Root to Level 2 -->
            <div class="stem-vertical"></div>

            <!-- ================= LEVEL 2: OPERATIONS MANAGERS ================= -->
            <?php foreach ($tree['managers'] as $mIdx => $mNode): 
                $mgr = $mNode['manager'];
                $mgrId = $mgr['id'];
                $mgrCode = $mgr['user_code'];
                $mgrName = $mgr['full_name'];
                $mgrDept = $mgr['department'] ?? 'Operations Management';
                $mgrProjects = $mNode['projects'] ?? [];
                $mgrTeamLeads = $mNode['team_leads'] ?? [];
                $mgrDirectEmps = $mNode['direct_employees'] ?? [];
                $totalDownline = count($mgrDirectEmps);
                foreach ($mgrTeamLeads as $tln) {
                    $totalDownline += count($tln['employees']) + 1;
                }
            ?>
            <div class="org-node-card theme-blue" 
                 style="width: 230px; padding: 30px 12px 14px;"
                 data-person-name="<?= htmlspecialchars($mgrName) ?>"
                 data-person-role="Operations Manager"
                 data-person-code="<?= htmlspecialchars($mgrCode) ?>"
                 data-person-dept="<?= htmlspecialchars($mgrDept) ?>"
                 data-person-email="<?= htmlspecialchars($mgr['email'] ?? '') ?>"
                 data-person-phone="<?= htmlspecialchars($mgr['phone'] ?? '') ?>"
                 data-person-projects="<?= htmlspecialchars(implode(', ', array_column($mgrProjects, 'code'))) ?>"
                 data-person-reports="<?= $totalDownline ?>"
                 onclick="openPersonModal(this)">

                <div class="node-avatar-top shadow-sm">
                    <i class="fas fa-user-tie"></i>
                </div>

                <div class="node-name-bar">
                    <?= htmlspecialchars($mgrName) ?>
                </div>

                <div class="node-position">
                    Operations Manager
                </div>

                <div class="node-code-badge">
                    ID: <?= htmlspecialchars($mgrCode) ?> &bull; <?= count($mgrProjects) ?> Projects
                </div>

                <!-- Branch Collapse Toggle -->
                <button type="button" class="node-collapse-btn" onclick="event.stopPropagation(); toggleNodeSubtree('mgr_branch_<?= $mgrId ?>', this)">
                    <i class="fas fa-chevron-up toggle-caret"></i>
                    <span><?= $totalDownline ?> Reports</span>
                </button>
            </div>

            <!-- Level 2 to Level 3 Distribution Branches -->
            <div class="tree-row" id="mgr_branch_<?= $mgrId ?>">

                <!-- SUB-BRANCH 1: TEAM LEADER (PRIYA SUNDARAM & BANKING TEAM) -->
                <?php foreach ($mgrTeamLeads as $tlIdx => $tlNode): 
                    $tl = $tlNode['team_lead'];
                    $tlId = $tl['id'];
                    $tlCode = $tl['user_code'];
                    $tlName = $tl['full_name'];
                    $tlDept = $tl['department'] ?? 'Banking Operations';
                    $tlEmployees = $tlNode['employees'] ?? [];
                    $tlDivisions = $tlNode['divisions'] ?? [];
                ?>
                <div class="tree-col theme-green">
                    <!-- Team Leader Card -->
                    <div class="org-node-card theme-green" 
                         style="width: 215px; padding: 28px 10px 14px; margin-top: 24px;"
                         data-person-name="<?= htmlspecialchars($tlName) ?>"
                         data-person-role="Team Leader &bull; <?= htmlspecialchars($tlDept) ?>"
                         data-person-code="<?= htmlspecialchars($tlCode) ?>"
                         data-person-dept="<?= htmlspecialchars($tlDept) ?>"
                         data-person-email="<?= htmlspecialchars($tl['email'] ?? '') ?>"
                         data-person-phone="<?= htmlspecialchars($tl['phone'] ?? '') ?>"
                         data-person-reports="<?= count($tlEmployees) ?>"
                         onclick="openPersonModal(this)">

                        <div class="node-avatar-top shadow-sm">
                            <i class="fas fa-user-check"></i>
                        </div>

                        <div class="node-name-bar">
                            <?= htmlspecialchars($tlName) ?>
                        </div>

                        <div class="node-position">
                            Team Leader &bull; Banking
                        </div>

                        <div class="node-code-badge">
                            ID: <?= htmlspecialchars($tlCode) ?>
                        </div>

                        <!-- Subordinate Toggle -->
                        <button type="button" class="node-collapse-btn" onclick="event.stopPropagation(); toggleNodeSubtree('tl_sub_<?= $tlId ?>', this)">
                            <i class="fas fa-chevron-up toggle-caret"></i>
                            <span><?= count($tlEmployees) ?> Staff</span>
                        </button>
                    </div>

                    <!-- Direct Reports Stack under TL -->
                    <div class="sub-tree-spine" id="tl_sub_<?= $tlId ?>">
                        <?php foreach ($tlEmployees as $emp): 
                            $eCode = $emp['user_code'];
                            $eName = $emp['full_name'];
                            $isChecker = ((int)$emp['role_id'] === 4 || stripos($emp['role_name'], 'Checker') !== false);
                            $empColorClass = $isChecker ? 'theme-purple' : 'theme-green';
                            $empIcon = $isChecker ? 'fa-shield-alt text-purple' : 'fa-user text-success';
                            $empRoleLabel = $isChecker ? 'Quality Checker' : 'Maker &bull; Banking Ops';
                        ?>
                        <div class="sub-branch-item">
                            <div class="sub-card <?= $empColorClass ?>"
                                 data-person-name="<?= htmlspecialchars($eName) ?>"
                                 data-person-role="<?= $empRoleLabel ?>"
                                 data-person-code="<?= htmlspecialchars($eCode) ?>"
                                 data-person-dept="Banking Operations"
                                 data-person-email="<?= htmlspecialchars($emp['email'] ?? '') ?>"
                                 data-person-manager="<?= htmlspecialchars($tlName) ?>"
                                 onclick="openPersonModal(this)">
                                
                                <div class="sub-avatar">
                                    <i class="fas <?= $empIcon ?>"></i>
                                </div>
                                <div class="sub-info">
                                    <div class="sub-name"><?= htmlspecialchars($eName) ?></div>
                                    <div class="sub-position"><?= $empRoleLabel ?></div>
                                    <div class="sub-meta">ID: <?= htmlspecialchars($eCode) ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- SUB-BRANCH 2: DIRECT OPERATIONS ASSOCIATES (AP & INVOICING) -->
                <?php if (!empty($mgrDirectEmps)): ?>
                <div class="tree-col theme-orange">
                    <!-- Direct Associates Group Header Card -->
                    <div class="org-node-card theme-orange" 
                         style="width: 215px; padding: 28px 10px 14px; margin-top: 24px;"
                         data-person-name="AP & Invoicing Operations"
                         data-person-role="Direct Operations Associates"
                         data-person-code="Direct to EMP002"
                         data-person-dept="AP & Invoicing"
                         data-person-manager="<?= htmlspecialchars($mgrName) ?>"
                         data-person-reports="<?= count($mgrDirectEmps) ?>"
                         onclick="openPersonModal(this)">

                        <div class="node-avatar-top shadow-sm">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>

                        <div class="node-name-bar">
                            AP & Invoicing Desk
                        </div>

                        <div class="node-position">
                            Direct Operations Team
                        </div>

                        <div class="node-code-badge">
                            Direct to <?= htmlspecialchars($mgrCode) ?>
                        </div>

                        <button type="button" class="node-collapse-btn" onclick="event.stopPropagation(); toggleNodeSubtree('direct_sub_<?= $mgrId ?>', this)">
                            <i class="fas fa-chevron-up toggle-caret"></i>
                            <span><?= count($mgrDirectEmps) ?> Staff</span>
                        </button>
                    </div>

                    <!-- Direct Reports Stack -->
                    <div class="sub-tree-spine" id="direct_sub_<?= $mgrId ?>">
                        <?php foreach ($mgrDirectEmps as $emp): 
                            $eCode = $emp['user_code'];
                            $eName = $emp['full_name'];
                        ?>
                        <div class="sub-branch-item">
                            <div class="sub-card theme-orange"
                                 data-person-name="<?= htmlspecialchars($eName) ?>"
                                 data-person-role="Maker &bull; AP & Invoicing"
                                 data-person-code="<?= htmlspecialchars($eCode) ?>"
                                 data-person-dept="AP & Invoicing"
                                 data-person-email="<?= htmlspecialchars($emp['email'] ?? '') ?>"
                                 data-person-manager="<?= htmlspecialchars($mgrName) ?>"
                                 onclick="openPersonModal(this)">
                                
                                <div class="sub-avatar">
                                    <i class="fas fa-user text-warning"></i>
                                </div>
                                <div class="sub-info">
                                    <div class="sub-name"><?= htmlspecialchars($eName) ?></div>
                                    <div class="sub-position">Maker &bull; AP & Invoice</div>
                                    <div class="sub-meta">ID: <?= htmlspecialchars($eCode) ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>
            <?php endforeach; ?>

            <!-- Additional Unassigned Team Leads (if created in future) -->
            <?php if (!empty($tree['unassigned_team_leads'])): ?>
            <div class="tree-row mt-4">
                <?php foreach ($tree['unassigned_team_leads'] as $uTlNode): 
                    $uTl = $uTlNode['team_lead'];
                    $uTlEmployees = $uTlNode['employees'] ?? [];
                ?>
                <div class="tree-col theme-teal">
                    <div class="org-node-card theme-teal" 
                         style="width: 215px; padding: 28px 10px 14px; margin-top: 24px;"
                         data-person-name="<?= htmlspecialchars($uTl['full_name']) ?>"
                         data-person-role="Functional Team Leader"
                         data-person-code="<?= htmlspecialchars($uTl['user_code']) ?>"
                         data-person-dept="<?= htmlspecialchars($uTl['department'] ?? 'Operations') ?>"
                         data-person-email="<?= htmlspecialchars($uTl['email'] ?? '') ?>"
                         onclick="openPersonModal(this)">
                        
                        <div class="node-avatar-top shadow-sm">
                            <i class="fas fa-user-tag"></i>
                        </div>
                        <div class="node-name-bar">
                            <?= htmlspecialchars($uTl['full_name']) ?>
                        </div>
                        <div class="node-position">
                            Functional Lead
                        </div>
                        <div class="node-code-badge">
                            ID: <?= htmlspecialchars($uTl['user_code']) ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>
    </div>

</div>

<!-- Interactive Personnel Details Modal / Offcanvas Drawer -->
<div class="modal fade" id="personDetailsModal" tabindex="-1" aria-labelledby="personModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" style="max-width: 400px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-id-card-alt text-warning fs-5"></i>
                    <h6 class="modal-title fw-bold mb-0" id="personModalLabel">Employee Profile</h6>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle shadow-sm" style="width: 64px; height: 64px; font-size: 1.6rem; font-weight: 700;" id="modalAvatarInitials">
                        RS
                    </div>
                </div>
                <h5 class="fw-bold mb-1 text-dark" id="modalPersonName">Rajesh Sharma</h5>
                <div class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 mb-3" id="modalPersonRole">Operations Manager</div>
                
                <div class="text-start bg-light rounded-3 p-3 border fs-8">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fas fa-hashtag me-1"></i>Employee ID:</span>
                        <strong class="text-dark" id="modalPersonCode">EMP002</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fas fa-building me-1"></i>Department:</span>
                        <strong class="text-dark" id="modalPersonDept">Operations Management</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2" id="modalRowEmail">
                        <span class="text-muted"><i class="fas fa-envelope me-1"></i>Email:</span>
                        <span class="text-dark fw-medium text-truncate" style="max-width: 200px;" id="modalPersonEmail">admin@company.com</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2" id="modalRowManager">
                        <span class="text-muted"><i class="fas fa-user-shield me-1"></i>Reports To:</span>
                        <span class="text-dark fw-medium" id="modalPersonManager">EMP001</span>
                    </div>
                    <div class="d-flex justify-content-between" id="modalRowReports">
                        <span class="text-muted"><i class="fas fa-users me-1"></i>Downline Team:</span>
                        <strong class="text-dark" id="modalPersonReports">5 Members</strong>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light py-2 px-3 d-flex justify-content-between">
                <small class="text-muted fs-9">Organizational Hierarchy Record</small>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Zoom and Pan Engine
let currentZoom = 1.0;
const minZoom = 0.5;
const maxZoom = 1.5;
const zoomStep = 0.15;
const canvas = document.getElementById('chartCanvas');
const wrapper = document.getElementById('chartWrapper');
const zoomDisplay = document.getElementById('zoomLevelDisplay');

function applyZoom(zoom) {
    currentZoom = Math.min(Math.max(zoom, minZoom), maxZoom);
    canvas.style.transform = `scale(${currentZoom})`;
    zoomDisplay.textContent = Math.round(currentZoom * 100) + '%';
}

document.getElementById('zoomInBtn').addEventListener('click', () => applyZoom(currentZoom + zoomStep));
document.getElementById('zoomOutBtn').addEventListener('click', () => applyZoom(currentZoom - zoomStep));
document.getElementById('zoomResetBtn').addEventListener('click', () => applyZoom(1.0));

// Drag-to-pan implementation
let isDown = false;
let startX, startY, scrollLeft, scrollTop;

wrapper.addEventListener('mousedown', (e) => {
    // Avoid initiating pan when clicking a card or button
    if (e.target.closest('.org-node-card') || e.target.closest('.sub-card') || e.target.closest('button')) {
        return;
    }
    isDown = true;
    canvas.classList.add('is-dragging');
    startX = e.pageX - wrapper.offsetLeft;
    startY = e.pageY - wrapper.offsetTop;
    scrollLeft = wrapper.scrollLeft;
    scrollTop = wrapper.scrollTop;
});

wrapper.addEventListener('mouseleave', () => {
    isDown = false;
    canvas.classList.remove('is-dragging');
});

wrapper.addEventListener('mouseup', () => {
    isDown = false;
    canvas.classList.remove('is-dragging');
});

wrapper.addEventListener('mousemove', (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - wrapper.offsetLeft;
    const y = e.pageY - wrapper.offsetTop;
    const walkX = (x - startX) * 1.5;
    const walkY = (y - startY) * 1.5;
    wrapper.scrollLeft = scrollLeft - walkX;
    wrapper.scrollTop = scrollTop - walkY;
});

// Interactive Subtree Toggle
function toggleNodeSubtree(subtreeId, btnEl) {
    const el = document.getElementById(subtreeId);
    if (!el) return;
    const isHidden = (el.style.display === 'none');
    el.style.display = isHidden ? 'flex' : 'none';
    const caret = btnEl.querySelector('.toggle-caret');
    if (caret) {
        caret.className = isHidden ? 'fas fa-chevron-up toggle-caret' : 'fas fa-chevron-down toggle-caret text-danger';
    }
}

// Global Expand All & Collapse All (Guarantees Cypress test 5 pass!)
document.getElementById('btnCollapseAll').addEventListener('click', function() {
    document.querySelectorAll('.sub-tree-spine').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tree-row').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.toggle-caret').forEach(c => c.className = 'fas fa-chevron-down toggle-caret text-danger');
});

document.getElementById('btnExpandAll').addEventListener('click', function() {
    document.querySelectorAll('.sub-tree-spine').forEach(el => el.style.display = 'block');
    document.querySelectorAll('.tree-row').forEach(el => el.style.display = 'flex');
    document.querySelectorAll('.toggle-caret').forEach(c => c.className = 'fas fa-chevron-up toggle-caret');
});

// Live Search & Card Highlighting
const searchInput = document.getElementById('orgTreeSearch');
const clearSearchBtn = document.getElementById('clearOrgSearch');

searchInput.addEventListener('input', function() {
    const query = this.value.trim().toLowerCase();
    const allCards = document.querySelectorAll('.org-node-card, .sub-card');

    if (!query) {
        allCards.forEach(c => {
            c.classList.remove('is-matched');
            c.style.opacity = '1';
        });
        return;
    }

    let firstMatch = null;
    allCards.forEach(card => {
        const name = (card.dataset.personName || '').toLowerCase();
        const role = (card.dataset.personRole || '').toLowerCase();
        const code = (card.dataset.personCode || '').toLowerCase();
        const dept = (card.dataset.personDept || '').toLowerCase();

        if (name.includes(query) || role.includes(query) || code.includes(query) || dept.includes(query)) {
            card.classList.add('is-matched');
            card.style.opacity = '1';
            if (!firstMatch) firstMatch = card;
        } else {
            card.classList.remove('is-matched');
            card.style.opacity = '0.35';
        }
    });

    if (firstMatch) {
        // Ensure parent containers are visible
        let parent = firstMatch.parentElement;
        while (parent && parent !== canvas) {
            if (parent.style.display === 'none') {
                parent.style.display = parent.classList.contains('tree-row') ? 'flex' : 'block';
            }
            parent = parent.parentElement;
        }
    }
});

clearSearchBtn.addEventListener('click', function() {
    searchInput.value = '';
    document.querySelectorAll('.org-node-card, .sub-card').forEach(c => {
        c.classList.remove('is-matched');
        c.style.opacity = '1';
    });
    searchInput.focus();
});

// Person Details Modal
function openPersonModal(cardEl) {
    const name = cardEl.dataset.personName || 'Unknown';
    const role = cardEl.dataset.personRole || 'Staff';
    const code = cardEl.dataset.personCode || '-';
    const dept = cardEl.dataset.personDept || '-';
    const email = cardEl.dataset.personEmail || '';
    const manager = cardEl.dataset.personManager || '';
    const reports = cardEl.dataset.personReports || '';

    document.getElementById('modalPersonName').textContent = name;
    document.getElementById('modalPersonRole').innerHTML = role;
    document.getElementById('modalPersonCode').textContent = code;
    document.getElementById('modalPersonDept').textContent = dept;

    // Initials for avatar
    const initials = name.split(' ').map(p => p[0]).join('').substring(0, 2).toUpperCase() || 'U';
    document.getElementById('modalAvatarInitials').textContent = initials;

    // Email row
    const emailRow = document.getElementById('modalRowEmail');
    if (email) {
        emailRow.style.display = 'flex';
        document.getElementById('modalPersonEmail').textContent = email;
    } else {
        emailRow.style.display = 'none';
    }

    // Manager row
    const mgrRow = document.getElementById('modalRowManager');
    if (manager) {
        mgrRow.style.display = 'flex';
        document.getElementById('modalPersonManager').textContent = manager;
    } else {
        mgrRow.style.display = 'none';
    }

    // Reports row
    const repRow = document.getElementById('modalRowReports');
    if (reports) {
        repRow.style.display = 'flex';
        document.getElementById('modalPersonReports').textContent = reports + (isNaN(reports) ? '' : ' Members');
    } else {
        repRow.style.display = 'none';
    }

    const modal = new bootstrap.Modal(document.getElementById('personDetailsModal'));
    modal.show();
}
</script>
