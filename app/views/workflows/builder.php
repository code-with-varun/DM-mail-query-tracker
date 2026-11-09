<div class="container-fluid px-3 py-2" id="builderWrapper">
    <!-- Top Action & Navigation Bar -->
    <div class="card border-0 shadow-sm mb-2" id="builderTopHeader">
        <div class="card-body p-2 px-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-light btn-sm text-secondary fw-bold" onclick="toggleMainSidebar()" title="Collapse / Expand Main App Sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    <a href="<?= base_url('workflows') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
                        <i class="fas fa-arrow-left me-1"></i> Workflows
                    </a>
                    <div class="vr mx-1"></div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="fas fa-project-diagram text-primary"></i>
                            <span id="headerWfTitle"><?= htmlspecialchars($workflow['workflow_name'] ?? 'Visual Workflow Canvas') ?></span>
                        </h6>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <!-- Workspace Toggle Buttons for Max Space -->
                    <button type="button" class="btn btn-outline-secondary btn-sm fw-bold" id="btnTogglePalette" onclick="togglePaletteSidebar()" title="Show/Hide Queue Library Sidebar">
                        <i class="fas fa-columns me-1"></i> Library
                    </button>

                    <button type="button" class="btn btn-outline-secondary btn-sm fw-bold" id="btnToggleSettings" onclick="toggleSettingsBar()" title="Show/Hide Top Settings Bar">
                        <i class="fas fa-sliders-h me-1"></i> Settings
                    </button>

                    <div class="vr mx-1"></div>

                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary fw-bold" onclick="autoLayoutHorizontal()" title="Arrange Horizontal Flow">
                            <i class="fas fa-arrows-left-right me-1"></i> Flow
                        </button>
                        <button type="button" class="btn btn-outline-secondary fw-bold" onclick="autoLayoutGrid()" title="Arrange Grid">
                            <i class="fas fa-th me-1"></i> Grid
                        </button>
                    </div>

                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold shadow-2xs" onclick="zoomFitToScreen()" title="Zoom Fit All Nodes">
                        <i class="fas fa-expand-arrows-alt me-1"></i> Fit Screen
                    </button>

                    <button type="button" class="btn btn-outline-success btn-sm fw-bold shadow-2xs" onclick="addNewNode()">
                        <i class="fas fa-plus me-1"></i> Add Stage
                    </button>

                    <button type="button" class="btn btn-primary btn-sm fw-bold shadow-2xs px-3" id="btnSaveCanvas" onclick="saveVisualWorkflow()">
                        <i class="fas fa-save me-1"></i> Save Workflow
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Collapsible Workflow Settings Bar (Collapsed by default for max visual space) -->
    <div class="card border-0 shadow-sm mb-2 bg-light" id="wfSettingsBar" style="display: none;">
        <div class="card-body p-2 px-3">
            <form id="wfMetaForm" class="row g-2 align-items-center">
                <input type="hidden" id="wf_id" value="<?= $workflow['id'] ?? 0 ?>">
                
                <div class="col-md-5">
                    <label class="form-label fs-9 fw-bold text-dark mb-0">Workflow Name <span class="text-danger">*</span></label>
                    <input type="text" id="wf_name" class="form-control form-control-sm fw-bold bg-white" value="<?= htmlspecialchars($workflow['workflow_name'] ?? 'Custom Process Workflow') ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fs-9 fw-bold text-dark mb-0">Mapped Mail/Ticket Category</label>
                    <select id="wf_category_id" class="form-select form-select-sm bg-white">
                        <option value="">-- None (Fallback Workflow) --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (!empty($workflow['category_id']) && $workflow['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['category_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-center justify-content-end gap-3 pt-3">
                    <div class="form-check me-2">
                        <input class="form-check-input" type="checkbox" id="wf_is_default" value="1" <?= (!empty($workflow['is_default'])) ? 'checked' : '' ?>>
                        <label class="form-check-label fs-8 fw-bold text-dark" for="wf_is_default">System Default</label>
                    </div>
                    <select id="wf_status" class="form-select form-select-sm bg-white" style="width: 100px;">
                        <option value="Active" <?= (!empty($workflow['status']) && $workflow['status'] === 'Active') ? 'selected' : '' ?>>Active</option>
                        <option value="Inactive" <?= (!empty($workflow['status']) && $workflow['status'] === 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Visual Workspace Canvas Layout -->
    <div class="row g-2">
        <!-- Sidebar Palette of Dynamic Process Queues (Collapsed by default for max visual space) -->
        <div class="col-md-3 col-lg-2" id="paletteCol" style="display: none;">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-2 fw-bold fs-8 text-dark d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-cubes text-primary me-2"></i>Queue Library</span>
                    <a href="<?= base_url('queues') ?>" target="_blank" class="btn btn-link text-primary btn-sm p-0 fs-9 text-decoration-none" title="Manage Queues in Master">
                        <i class="fas fa-cog"></i> Master
                    </a>
                </div>
                <div class="card-body p-2 d-flex flex-column" id="paletteCardBody">
                    <div class="mb-2">
                        <input type="text" id="queueSearch" class="form-control form-control-sm fs-8" placeholder="Search queues..." onkeyup="filterQueuePalette(this.value)">
                    </div>
                    
                    <small class="text-muted fs-9 mb-2 d-block">Drag or click queue to insert:</small>

                    <div class="flex-grow-1 overflow-auto pe-1" id="paletteList" style="max-height: calc(100vh - 200px);">
                        <?php if (empty($processQueues)): ?>
                            <div class="text-center py-3 text-muted fs-9">No active queues found.</div>
                        <?php else: ?>
                        <?php foreach ($processQueues as $pq): 
                            $cleanName = preg_replace('/^\d+[\.\)]\s*/', '', $pq['queue_name']);
                        ?>
                            <div class="card border mb-2 p-2 shadow-2xs cursor-pointer hover-shadow transition-all palette-item"
                                 draggable="true"
                                 ondragstart="onPaletteDragStart(event, '<?= htmlspecialchars($cleanName, ENT_QUOTES) ?>', '<?= htmlspecialchars($pq['queue_code'], ENT_QUOTES) ?>', '<?= htmlspecialchars($pq['assigned_role'], ENT_QUOTES) ?>', '<?= htmlspecialchars($pq['badge_color'], ENT_QUOTES) ?>')"
                                 onclick="addPaletteNode('<?= htmlspecialchars($cleanName, ENT_QUOTES) ?>', '<?= htmlspecialchars($pq['queue_code'], ENT_QUOTES) ?>', '<?= htmlspecialchars($pq['assigned_role'], ENT_QUOTES) ?>', '<?= htmlspecialchars($pq['badge_color'], ENT_QUOTES) ?>')">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle flex-shrink-0" style="width: 12px; height: 12px; background-color: <?= htmlspecialchars($pq['badge_color']) ?>;"></div>
                                    <div class="overflow-hidden">
                                        <strong class="d-block fs-8 text-dark text-truncate title-text"><?= htmlspecialchars($cleanName) ?></strong>
                                        <small class="text-muted fs-9"><?= htmlspecialchars($pq['queue_code']) ?> &bull; <?= htmlspecialchars($pq['assigned_role']) ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dynamic Max-Height Viewport Canvas Surface (Full col-12 width by default) -->
        <div class="col-12" id="canvasCol">
            <div class="card border-0 shadow-sm position-relative overflow-hidden" id="canvasCard" style="height: calc(100vh - 90px); background-color: #f8fafc; transition: height 0.2s ease;">
                
                <!-- Floating Controls Toolbar -->
                <div class="position-absolute top-0 end-0 m-3 z-3 d-flex align-items-center gap-1 bg-white p-1 rounded-3 shadow-sm border">
                    <button type="button" class="btn btn-light btn-sm text-dark px-2 fw-bold" onclick="zoomCanvas(0.1)" title="Zoom In">
                        <i class="fas fa-plus"></i>
                    </button>
                    <span class="fs-8 fw-bold px-2 text-secondary" id="zoomLevelText">100%</span>
                    <button type="button" class="btn btn-light btn-sm text-dark px-2 fw-bold" onclick="zoomCanvas(-0.1)" title="Zoom Out">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-light btn-sm text-dark px-2 fw-bold" onclick="zoomFitToScreen()" title="Fit All Nodes">
                        <i class="fas fa-compress-arrows-alt"></i>
                    </button>
                    <div class="vr mx-1"></div>
                    <button type="button" class="btn btn-light btn-sm text-dark px-2 fw-bold" onclick="toggleCanvasFullscreen()" title="Toggle Fullscreen">
                        <i class="fas fa-expand" id="fsIcon"></i>
                    </button>
                </div>

                <!-- Dotted Canvas Scroll Surface -->
                <div id="wfCanvasContainer" 
                     class="w-100 h-100 position-relative" 
                     style="overflow: auto; background-image: radial-gradient(#cbd5e1 1.2px, transparent 1.2px); background-size: 24px 24px;"
                     ondragover="event.preventDefault()"
                     ondrop="onCanvasDrop(event)">

                    <!-- Transform Wrapper for Dynamic Zooming -->
                    <div id="wfCanvasTransform" style="position: absolute; top: 0; left: 0; width: 3500px; height: 3500px; transform-origin: 0 0; transition: transform 0.15s ease-out;">
                        <!-- SVG Overlay for Curved Bezier Connecting Arrows -->
                        <svg id="wfSvgOverlay" style="position: absolute; top: 0; left: 0; width: 3500px; height: 3500px; pointer-events: none; z-index: 1;">
                            <defs>
                                <marker id="arrowhead" markerWidth="10" markerHeight="7" refX="9" refY="3.5" orient="auto">
                                    <polygon points="0 0, 10 3.5, 0 7" fill="#2563eb" />
                                </marker>
                                <filter id="glow" x="-20%" y="-20%" width="140%" height="140%">
                                    <feDropShadow dx="0" dy="2" stdDeviation="2" flood-color="#2563eb" flood-opacity="0.3"/>
                                </filter>
                            </defs>
                        </svg>

                        <!-- Draggable Nodes Layer -->
                        <div id="wfNodesContainer" style="position: absolute; top: 0; left: 0; width: 3500px; height: 3500px; z-index: 2;">
                            <!-- Nodes generated dynamically via JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>

<!-- Modal 1: Visually Appealing Alert Modal (Contact Card Style) -->
<div class="modal fade" id="appAlertModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header border-0 py-3" id="appAlertHeader">
                <h6 class="modal-title fw-bold text-white mb-0" id="appAlertHeaderTitle">Notification</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <i class="fas fa-check-circle fa-3x text-success mb-3" id="appAlertIcon"></i>
                <h5 class="fw-bold text-dark mb-2" id="appAlertTitle">Canvas Saved</h5>
                <p class="text-muted fs-8 mb-0" id="appAlertMessage">Visual Workflow Canvas saved successfully!</p>
            </div>
            <div class="modal-footer bg-light border-0 px-4 py-3 justify-content-center">
                <button type="button" class="btn btn-primary fw-bold px-4 rounded-pill" id="appAlertOkBtn" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Visually Appealing Delete/Confirm Modal (Contact Card Style) -->
<div class="modal fade" id="appConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title fw-bold" id="appConfirmModalHeaderTitle">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmation Required
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <i class="fas fa-trash-alt fa-3x text-danger mb-3" id="appConfirmIcon"></i>
                <h5 class="fw-bold text-dark" id="appConfirmTitle">Are you sure?</h5>
                <p class="text-muted mb-0 fs-8" id="appConfirmMessage">
                    This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer bg-light px-4 py-3 justify-content-center border-0 gap-2">
                <button type="button" class="btn btn-secondary fw-bold px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger fw-bold px-4 rounded-pill" id="appConfirmBtn">
                    <i class="fas fa-trash-alt me-1"></i>Confirm Delete
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.wf-node-card {
    position: absolute;
    width: 260px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, 0.08);
    border: 2px solid #e2e8f0;
    user-select: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.wf-node-card:hover {
    border-color: #2563eb !important;
    box-shadow: 0 10px 25px rgba(37, 99, 235, 0.18) !important;
}
.wf-node-card.selected-node {
    border-color: #2563eb !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.25) !important;
}
.wf-node-header {
    background: #f8fafc;
    padding: 8px 12px;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
    cursor: move;
    border-bottom: 1px solid #e2e8f0;
}
.wf-port {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    box-shadow: 0 0 6px rgba(0,0,0,0.25);
    z-index: 10;
    transition: transform 0.2s ease, background-color 0.2s ease;
}
.wf-port-in { 
    left: -8px; 
    background: #10b981; 
    border: 2.5px solid #ffffff; 
}
.wf-port-out { 
    right: -8px; 
    background: #2563eb; 
    border: 2.5px solid #ffffff; 
}
.wf-port:hover { 
    transform: translateY(-50%) scale(1.4); 
}
.wf-port-pulse {
    animation: portPulse 1.2s infinite alternate;
}
@keyframes portPulse {
    0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    100% { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
}
.connection-line {
    pointer-events: stroke;
    cursor: pointer;
    transition: stroke-width 0.2s ease, stroke 0.2s ease;
}
.connection-line:hover {
    stroke-width: 5px !important;
    stroke: #dc2626 !important;
}
</style>

<script>
var nodes = [];
var activeConnectingPort = null;
var isDraggingNode = false;
var dragNodeId = null;
var dragOffsetX = 0;
var dragOffsetY = 0;
var zoomScale = 1.0;
var isFullscreen = false;
var isPaletteVisible = false;
var isSettingsVisible = false;

var availableQueues = <?= json_encode($processQueues ?? []) ?>;

var queueColors = {
    'Input': '#0284c7',
    'Maker': '#2563eb',
    'Checker': '#d97706',
    'ErrorLog': '#dc2626',
    'Rework': '#9333ea',
    'Delivery': '#0891b2',
    'Ack': '#16a34a',
    'Approval': '#475569',
    'Uploader': '#4f46e5',
    'DumpShare': '#0d9488',
    'ConsolVendor': '#db2777',
    'Closed': '#059669'
};

availableQueues.forEach(function(q) {
    if (q.queue_code && q.badge_color) {
        queueColors[q.queue_code] = q.badge_color;
    }
});

function cleanStageTitle(name) {
    if (!name) return '';
    return String(name).replace(/^\d+[\.\)]\s*/, '').trim();
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-collapse main app sidebar for maximum visual canvas space
    var sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle && !document.body.classList.contains('sb-sidenav-toggled')) {
        document.body.classList.add('sb-sidenav-toggled');
    }

    var initialStages = <?= json_encode($workflow['stages'] ?? []) ?>;

    if (initialStages && initialStages.length > 0) {
        initialStages.forEach(function(st, idx) {
            var posX = st.pos_x ? parseInt(st.pos_x) : (60 + (idx * 290));
            var posY = st.pos_y ? parseInt(st.pos_y) : (160 + ((idx % 2) * 60));
            nodes.push({
                id: 'node_' + (idx + 1),
                stage_name: cleanStageTitle(st.stage_name),
                queue_type: st.queue_type || 'Maker',
                assigned_role: st.assigned_role || 'Maker',
                pos_x: posX,
                pos_y: posY,
                connected_to: st.connected_to || ''
            });
        });
    } else {
        // Default starter pipeline without leading number prefixes in title
        nodes = [
            { id: 'node_1', stage_name: 'Input Receiving', queue_type: 'Input', assigned_role: 'Maker', pos_x: 60, pos_y: 160, connected_to: 'node_2' },
            { id: 'node_2', stage_name: 'Maker Processing', queue_type: 'Maker', assigned_role: 'Maker', pos_x: 350, pos_y: 160, connected_to: 'node_3' },
            { id: 'node_3', stage_name: 'Checker Review', queue_type: 'Checker', assigned_role: 'Checker', pos_x: 640, pos_y: 160, connected_to: 'node_4' },
            { id: 'node_4', stage_name: 'Delivery Phase', queue_type: 'Delivery', assigned_role: 'Maker', pos_x: 930, pos_y: 160, connected_to: 'node_5' },
            { id: 'node_5', stage_name: 'Ticket Closed', queue_type: 'Closed', assigned_role: 'Both', pos_x: 1220, pos_y: 160, connected_to: '' }
        ];
    }

    renderCanvas();

    // Auto-fit to screen and align horizontal flow on load
    setTimeout(function() {
        autoLayoutHorizontal();
    }, 100);
});

function renderCanvas() {
    var container = document.getElementById('wfNodesContainer');
    container.innerHTML = '';

    nodes.forEach(function(node, idx) {
        var card = document.createElement('div');
        card.className = 'wf-node-card';
        card.id = node.id;
        card.style.left = node.pos_x + 'px';
        card.style.top = node.pos_y + 'px';

        var accentColor = queueColors[node.queue_type] || '#2563eb';
        card.style.borderTop = '4px solid ' + accentColor;

        var displayTitle = cleanStageTitle(node.stage_name);

        var queueOptionsHtml = '';
        if (availableQueues && availableQueues.length > 0) {
            availableQueues.forEach(function(q) {
                var sel = (node.queue_type === q.queue_code) ? 'selected' : '';
                var qCleanName = cleanStageTitle(q.queue_name);
                queueOptionsHtml += `<option value="${escapeHtml(q.queue_code)}" ${sel}>${escapeHtml(qCleanName)}</option>`;
            });
        } else {
            queueOptionsHtml = `
                <option value="Input" ${node.queue_type === 'Input' ? 'selected' : ''}>Input Recv</option>
                <option value="Maker" ${node.queue_type === 'Maker' ? 'selected' : ''}>Maker Phase</option>
                <option value="Checker" ${node.queue_type === 'Checker' ? 'selected' : ''}>Checker Audit</option>
                <option value="ErrorLog" ${node.queue_type === 'ErrorLog' ? 'selected' : ''}>Error Log</option>
                <option value="Rework" ${node.queue_type === 'Rework' ? 'selected' : ''}>MC Rework</option>
                <option value="Delivery" ${node.queue_type === 'Delivery' ? 'selected' : ''}>Delivery</option>
                <option value="Closed" ${node.queue_type === 'Closed' ? 'selected' : ''}>Closed</option>
            `;
        }

        card.innerHTML = `
            <div class="wf-port wf-port-in ${activeConnectingPort ? 'wf-port-pulse' : ''}" onclick="onPortClick('${node.id}', 'in')" title="Connect Input Port"></div>
            <div class="wf-node-header d-flex align-items-center justify-content-between" onmousedown="startDragNode(event, '${node.id}')">
                <div class="d-flex align-items-center gap-2 overflow-hidden me-1">
                    <span class="badge rounded-circle px-0 flex-shrink-0" style="background-color: ${accentColor}; width: 22px; height: 22px; line-height: 22px; font-size: 11px; text-align: center; color: #fff;">${idx + 1}</span>
                    <strong class="fs-8 text-dark text-truncate title-label">${escapeHtml(displayTitle)}</strong>
                </div>
                <button type="button" class="btn btn-link text-muted btn-sm p-0 text-decoration-none hover-danger" onclick="deleteNode('${node.id}')" title="Delete Node">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-2 bg-white rounded-bottom">
                <div class="mb-2">
                    <label class="form-label fs-9 fw-bold text-secondary mb-0">Stage Title</label>
                    <input type="text" class="form-control form-control-sm fs-8 fw-bold" value="${escapeHtml(displayTitle)}" onchange="updateNodeField('${node.id}', 'stage_name', this.value)">
                </div>
                <div class="row g-1">
                    <div class="col-7">
                        <label class="form-label fs-9 fw-bold text-secondary mb-0">Queue Type</label>
                        <select class="form-select form-select-sm fs-9" onchange="updateNodeField('${node.id}', 'queue_type', this.value)">
                            ${queueOptionsHtml}
                        </select>
                    </div>
                    <div class="col-5">
                        <label class="form-label fs-9 fw-bold text-secondary mb-0">Role</label>
                        <select class="form-select form-select-sm fs-9" onchange="updateNodeField('${node.id}', 'assigned_role', this.value)">
                            <option value="Maker" ${node.assigned_role === 'Maker' ? 'selected' : ''}>Maker</option>
                            <option value="Checker" ${node.assigned_role === 'Checker' ? 'selected' : ''}>Checker</option>
                            <option value="Both" ${node.assigned_role === 'Both' ? 'selected' : ''}>Both</option>
                            <option value="Admin" ${node.assigned_role === 'Admin' ? 'selected' : ''}>Admin</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="wf-port wf-port-out" onclick="onPortClick('${node.id}', 'out')" title="Connect Output Port"></div>
        `;
        container.appendChild(card);
    });

    // Request animation frame to guarantee layout reflow before measuring height & drawing SVG lines
    requestAnimationFrame(function() {
        drawConnectionLines();
    });
}

function updateNodeField(nodeId, field, val) {
    var n = nodes.find(x => x.id === nodeId);
    if (n) {
        if (field === 'stage_name') val = cleanStageTitle(val);
        n[field] = val;
        renderCanvas();
    }
}

function startDragNode(e, nodeId) {
    if (e.target.closest('button') || e.target.closest('input') || e.target.closest('select')) return;
    e.preventDefault();
    isDraggingNode = true;
    dragNodeId = nodeId;

    var cardElem = document.getElementById(nodeId);
    if (cardElem) cardElem.classList.add('selected-node');

    var rect = cardElem.getBoundingClientRect();
    dragOffsetX = (e.clientX - rect.left) / zoomScale;
    dragOffsetY = (e.clientY - rect.top) / zoomScale;

    document.addEventListener('mousemove', onMouseMoveDrag);
    document.addEventListener('mouseup', onMouseUpDrag);
}

function onMouseMoveDrag(e) {
    if (!isDraggingNode || !dragNodeId) return;
    var container = document.getElementById('wfCanvasContainer');
    var cRect = container.getBoundingClientRect();

    var newX = ((e.clientX - cRect.left) / zoomScale) + container.scrollLeft - dragOffsetX;
    var newY = ((e.clientY - cRect.top) / zoomScale) + container.scrollTop - dragOffsetY;

    if (newX < 10) newX = 10;
    if (newY < 10) newY = 10;

    var n = nodes.find(x => x.id === dragNodeId);
    if (n) {
        n.pos_x = Math.round(newX);
        n.pos_y = Math.round(newY);
        var elem = document.getElementById(dragNodeId);
        if (elem) {
            elem.style.left = n.pos_x + 'px';
            elem.style.top = n.pos_y + 'px';
        }
        drawConnectionLines();
    }
}

function onMouseUpDrag() {
    if (dragNodeId) {
        var cardElem = document.getElementById(dragNodeId);
        if (cardElem) cardElem.classList.remove('selected-node');
    }
    isDraggingNode = false;
    dragNodeId = null;
    document.removeEventListener('mousemove', onMouseMoveDrag);
    document.removeEventListener('mouseup', onMouseUpDrag);
}

function onPortClick(nodeId, portType) {
    if (!activeConnectingPort) {
        if (portType === 'out') {
            activeConnectingPort = nodeId;
            var elem = document.getElementById(nodeId);
            if (elem) elem.classList.add('selected-node');
            renderCanvas();
        }
    } else {
        if (portType === 'in' && activeConnectingPort !== nodeId) {
            var sourceNode = nodes.find(x => x.id === activeConnectingPort);
            if (sourceNode) {
                sourceNode.connected_to = nodeId;
            }
            activeConnectingPort = null;
            renderCanvas();
        } else {
            activeConnectingPort = null;
            renderCanvas();
        }
    }
}

function drawConnectionLines() {
    var svg = document.getElementById('wfSvgOverlay');
    if (!svg) return;

    var oldPaths = svg.querySelectorAll('path');
    oldPaths.forEach(p => p.remove());

    nodes.forEach(function(node) {
        if (node.connected_to) {
            var targetNode = nodes.find(x => x.id === node.connected_to);
            if (targetNode) {
                var nodeElem = document.getElementById(node.id);
                var targetElem = document.getElementById(targetNode.id);

                var cardWidth = 260;
                var cardHeight = (nodeElem && nodeElem.offsetHeight > 40) ? nodeElem.offsetHeight : 130;
                var targetHeight = (targetElem && targetElem.offsetHeight > 40) ? targetElem.offsetHeight : 130;

                var x1 = node.pos_x + cardWidth;
                var y1 = node.pos_y + (cardHeight / 2);
                var x2 = targetNode.pos_x;
                var y2 = targetNode.pos_y + (targetHeight / 2);

                var pathData = '';

                // Smart Bezier Curve Logic
                if (x2 < x1 - 30) {
                    var midY = (y1 + y2) / 2;
                    pathData = `M ${x1} ${y1} C ${x1 + 90} ${y1}, ${x1 + 90} ${midY}, ${(x1 + x2) / 2} ${midY} S ${x2 - 90} ${y2}, ${x2} ${y2}`;
                } else {
                    var dx = Math.max(60, Math.abs(x2 - x1) / 2);
                    pathData = `M ${x1} ${y1} C ${x1 + dx} ${y1}, ${x2 - dx} ${y2}, ${x2} ${y2}`;
                }

                var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                path.setAttribute('d', pathData);
                path.setAttribute('stroke', '#2563eb');
                path.setAttribute('stroke-width', '3.5');
                path.setAttribute('fill', 'none');
                path.setAttribute('marker-end', 'url(#arrowhead)');
                path.setAttribute('filter', 'url(#glow)');
                path.setAttribute('class', 'connection-line');
                path.setAttribute('title', 'Click to disconnect line');

                path.onclick = function() {
                    var stageFrom = cleanStageTitle(node.stage_name);
                    var stageTo = cleanStageTitle(targetNode.stage_name);
                    showVisualConfirm(
                        'Disconnect Line',
                        'Are you sure you want to disconnect connection between <strong>' + escapeHtml(stageFrom) + '</strong> and <strong>' + escapeHtml(stageTo) + '</strong>?',
                        function() {
                            node.connected_to = '';
                            renderCanvas();
                        },
                        'Disconnect',
                        'btn-warning',
                        'fas fa-unlink'
                    );
                };

                svg.appendChild(path);
            }
        }
    });
}

function onPaletteDragStart(e, name, type, role, color) {
    e.dataTransfer.setData('text/plain', JSON.stringify({ name: cleanStageTitle(name), type: type, role: role, color: color }));
}

function onCanvasDrop(e) {
    e.preventDefault();
    var rawData = e.dataTransfer.getData('text/plain');
    if (!rawData) return;
    try {
        var data = JSON.parse(rawData);
        var container = document.getElementById('wfCanvasContainer');
        var cRect = container.getBoundingClientRect();
        var dropX = Math.round(((e.clientX - cRect.left) / zoomScale) + container.scrollLeft - 130);
        var dropY = Math.round(((e.clientY - cRect.top) / zoomScale) + container.scrollTop - 40);

        if (dropX < 10) dropX = 10;
        if (dropY < 10) dropY = 10;

        addPaletteNodeAtPos(cleanStageTitle(data.name), data.type, data.role, data.color, dropX, dropY);
    } catch(err){}
}

function addPaletteNode(name, type, role, color) {
    var lastNode = nodes[nodes.length - 1];
    var newX = lastNode ? lastNode.pos_x + 290 : 60;
    var newY = lastNode ? lastNode.pos_y : 160;
    addPaletteNodeAtPos(cleanStageTitle(name), type, role, color, newX, newY);
}

function addPaletteNodeAtPos(name, type, role, color, x, y) {
    var newId = 'node_' + Date.now() + '_' + Math.floor(Math.random() * 100);
    var lastNode = nodes[nodes.length - 1];

    if (color && type) {
        queueColors[type] = color;
    }

    var newNode = {
        id: newId,
        stage_name: cleanStageTitle(name),
        queue_type: type,
        assigned_role: role,
        pos_x: x,
        pos_y: y,
        connected_to: ''
    };

    if (lastNode && !lastNode.connected_to) {
        lastNode.connected_to = newId;
    }

    nodes.push(newNode);
    renderCanvas();
    zoomFitToScreen();
}

function addNewNode() {
    addPaletteNode('New Queue Stage', 'Maker', 'Maker', '#2563eb');
}

function deleteNode(nodeId) {
    var n = nodes.find(x => x.id === nodeId);
    var stageName = n ? cleanStageTitle(n.stage_name) : 'this stage';
    showVisualConfirm(
        'Delete Stage Node',
        'You are about to delete stage <strong>' + escapeHtml(stageName) + '</strong> from canvas. This action cannot be undone.',
        function() {
            nodes = nodes.filter(x => x.id !== nodeId);
            nodes.forEach(x => {
                if (x.connected_to === nodeId) x.connected_to = '';
            });
            renderCanvas();
        },
        'Confirm Delete',
        'btn-danger',
        'fas fa-trash-alt'
    );
}

function autoLayoutHorizontal() {
    nodes.forEach(function(n, idx) {
        n.pos_x = 60 + (idx * 290);
        n.pos_y = 160;
        if (idx < nodes.length - 1) {
            n.connected_to = nodes[idx + 1].id;
        }
    });
    renderCanvas();
    zoomFitToScreen();
}

function autoLayoutGrid() {
    var itemsPerRow = Math.ceil(nodes.length / 2);
    if (itemsPerRow < 3) itemsPerRow = 3;

    nodes.forEach(function(n, idx) {
        var row = Math.floor(idx / itemsPerRow);
        var col = idx % itemsPerRow;

        n.pos_x = 60 + (col * 290);
        n.pos_y = 100 + (row * 220);

        if (idx < nodes.length - 1) {
            n.connected_to = nodes[idx + 1].id;
        }
    });
    renderCanvas();
    zoomFitToScreen();
}

function zoomCanvas(delta) {
    zoomScale = Math.min(Math.max(0.4, zoomScale + delta), 1.5);
    document.getElementById('zoomLevelText').innerText = Math.round(zoomScale * 100) + '%';
    document.getElementById('wfCanvasTransform').style.transform = `scale(${zoomScale})`;
    drawConnectionLines();
}

function zoomFitToScreen() {
    if (!nodes || nodes.length === 0) return;

    var minX = Infinity, maxX = -Infinity, minY = Infinity, maxY = -Infinity;
    nodes.forEach(function(n) {
        if (n.pos_x < minX) minX = n.pos_x;
        if (n.pos_x + 260 > maxX) maxX = n.pos_x + 260;
        if (n.pos_y < minY) minY = n.pos_y;
        if (n.pos_y + 150 > maxY) maxY = n.pos_y + 150;
    });

    var bboxWidth = (maxX - minX) + 120;
    var bboxHeight = (maxY - minY) + 120;

    var container = document.getElementById('wfCanvasContainer');
    var containerWidth = container.clientWidth - 40;
    var containerHeight = container.clientHeight - 40;

    var scaleX = containerWidth / bboxWidth;
    var scaleY = containerHeight / bboxHeight;
    var calculatedScale = Math.min(scaleX, scaleY);

    zoomScale = Math.min(Math.max(0.45, calculatedScale), 1.1);
    zoomScale = Math.round(zoomScale * 100) / 100;

    document.getElementById('zoomLevelText').innerText = Math.round(zoomScale * 100) + '%';
    document.getElementById('wfCanvasTransform').style.transform = `scale(${zoomScale})`;

    container.scrollLeft = Math.max(0, (minX - 40) * zoomScale);
    container.scrollTop = Math.max(0, (minY - 40) * zoomScale);

    drawConnectionLines();
}

function toggleMainSidebar() {
    var body = document.body;
    body.classList.toggle('sb-sidenav-toggled');
    setTimeout(zoomFitToScreen, 100);
}

function togglePaletteSidebar() {
    var paletteCol = document.getElementById('paletteCol');
    var canvasCol = document.getElementById('canvasCol');
    var btn = document.getElementById('btnTogglePalette');

    if (isPaletteVisible) {
        paletteCol.style.display = 'none';
        canvasCol.className = 'col-12';
        btn.classList.replace('btn-primary', 'btn-outline-secondary');
        isPaletteVisible = false;
    } else {
        paletteCol.style.display = 'block';
        canvasCol.className = 'col-md-9 col-lg-10';
        btn.classList.replace('btn-outline-secondary', 'btn-primary');
        isPaletteVisible = true;
    }
    setTimeout(zoomFitToScreen, 100);
}

function toggleSettingsBar() {
    var settingsBar = document.getElementById('wfSettingsBar');
    var canvasCard = document.getElementById('canvasCard');
    var btn = document.getElementById('btnToggleSettings');

    if (isSettingsVisible) {
        settingsBar.style.display = 'none';
        canvasCard.style.height = 'calc(100vh - 90px)';
        btn.classList.replace('btn-primary', 'btn-outline-secondary');
        isSettingsVisible = false;
    } else {
        settingsBar.style.display = 'block';
        canvasCard.style.height = 'calc(100vh - 150px)';
        btn.classList.replace('btn-primary', 'btn-outline-secondary');
        isSettingsVisible = true;
    }
    setTimeout(zoomFitToScreen, 100);
}

function toggleCanvasFullscreen() {
    var card = document.getElementById('canvasCard');
    var icon = document.getElementById('fsIcon');
    if (!isFullscreen) {
        card.style.position = 'fixed';
        card.style.top = '0';
        card.style.left = '0';
        card.style.width = '100vw';
        card.style.height = '100vh';
        card.style.zIndex = '9999';
        icon.className = 'fas fa-compress';
        isFullscreen = true;
    } else {
        card.style.position = 'relative';
        card.style.top = 'auto';
        card.style.left = 'auto';
        card.style.width = '100%';
        card.style.height = isSettingsVisible ? 'calc(100vh - 150px)' : 'calc(100vh - 90px)';
        card.style.zIndex = 'auto';
        icon.className = 'fas fa-expand';
        isFullscreen = false;
    }
    setTimeout(zoomFitToScreen, 100);
}

function filterQueuePalette(term) {
    term = term.toLowerCase().trim();
    var items = document.querySelectorAll('.palette-item');
    items.forEach(function(item) {
        var txt = item.innerText.toLowerCase();
        if (txt.includes(term)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

function saveVisualWorkflow() {
    var wfId = document.getElementById('wf_id').value;
    var wfName = document.getElementById('wf_name').value.trim();
    var categoryId = document.getElementById('wf_category_id').value;
    var isDefault = document.getElementById('wf_is_default').checked ? 1 : 0;
    var status = document.getElementById('wf_status').value;

    if (!wfName) {
        showVisualAlert('Workflow Name Required', 'Please enter a Workflow Name.', 'warning');
        return;
    }

    if (nodes.length === 0) {
        showVisualAlert('Stage Nodes Required', 'Please add at least one queue stage node onto the canvas.', 'warning');
        return;
    }

    var btn = document.getElementById('btnSaveCanvas');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

    var payload = {
        workflow_id: wfId,
        workflow_name: wfName,
        category_id: categoryId,
        sub_activity_id: '',
        is_default: isDefault,
        status: status,
        nodes: nodes
    };

    fetch('<?= base_url('workflows/save-canvas') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Workflow';

        if (data.success) {
            showVisualAlert('Workflow Saved!', 'Visual Workflow Canvas saved successfully!', 'success', function() {
                window.location.href = '<?= base_url('workflows') ?>';
            });
        } else {
            showVisualAlert('Save Error', data.message || 'Error saving workflow', 'danger');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Workflow';
        showVisualAlert('Server Error', 'Failed to communicate with server.', 'danger');
    });
}

function showVisualAlert(title, message, type, onOk) {
    type = type || 'success';
    var modalEl = document.getElementById('appAlertModal');
    var headerEl = document.getElementById('appAlertHeader');
    var iconEl = document.getElementById('appAlertIcon');
    var titleEl = document.getElementById('appAlertTitle');
    var msgEl = document.getElementById('appAlertMessage');
    var okBtn = document.getElementById('appAlertOkBtn');

    if (type === 'danger') {
        headerEl.className = 'modal-header bg-danger text-white border-0 py-3';
        iconEl.className = 'fas fa-times-circle fa-3x text-danger mb-3';
        okBtn.className = 'btn btn-danger fw-bold px-4 rounded-pill';
    } else if (type === 'warning') {
        headerEl.className = 'modal-header bg-warning text-dark border-0 py-3';
        iconEl.className = 'fas fa-exclamation-triangle fa-3x text-warning mb-3';
        okBtn.className = 'btn btn-warning text-dark fw-bold px-4 rounded-pill';
    } else {
        headerEl.className = 'modal-header bg-success text-white border-0 py-3';
        iconEl.className = 'fas fa-check-circle fa-3x text-success mb-3';
        okBtn.className = 'btn btn-success fw-bold px-4 rounded-pill';
    }

    titleEl.textContent = title;
    msgEl.innerHTML = message || '';

    var bsModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    
    var onHidden = function() {
        modalEl.removeEventListener('hidden.bs.modal', onHidden);
        if (typeof onOk === 'function') onOk();
    };

    if (typeof onOk === 'function') {
        modalEl.addEventListener('hidden.bs.modal', onHidden);
    }

    bsModal.show();
}

function showVisualConfirm(title, message, onConfirm, btnText, btnClass, iconClass) {
    btnText = btnText || 'Confirm Delete';
    btnClass = btnClass || 'btn-danger';
    iconClass = iconClass || 'fas fa-trash-alt';

    var modalEl = document.getElementById('appConfirmModal');
    var titleEl = document.getElementById('appConfirmTitle');
    var msgEl = document.getElementById('appConfirmMessage');
    var confirmBtn = document.getElementById('appConfirmBtn');
    var confirmIcon = document.getElementById('appConfirmIcon');

    titleEl.textContent = title || 'Are you sure?';
    msgEl.innerHTML = message || '';
    confirmBtn.className = 'btn fw-bold px-4 rounded-pill ' + btnClass;
    confirmBtn.innerHTML = `<i class="${iconClass} me-1"></i>${btnText}`;
    confirmIcon.className = iconClass + ' fa-3x text-danger mb-3';

    var bsModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);

    confirmBtn.onclick = function() {
        bsModal.hide();
        if (typeof onConfirm === 'function') onConfirm();
    };

    bsModal.show();
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>
