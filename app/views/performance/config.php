<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-sliders-h text-primary me-2"></i>Team Metric Weightage Configurator
            </h4>
            <p class="text-muted fs-7 mb-0">Configure Team Performance Metric Weightages & Target Benchmark Ratios (Total = 100 Points)</p>
        </div>
        <div>
            <form action="<?= base_url('performance/config') ?>" method="GET" class="d-flex gap-2 align-items-center">
                <label class="fs-8 fw-bold text-dark text-nowrap me-1"><i class="fas fa-filter me-1 text-primary"></i>Team / Project Scope:</label>
                <select name="project_id" class="form-select form-select-sm fw-bold border-primary" style="min-width: 280px;" onchange="this.form.submit()">
                    <option value="">-- Global Default Team Config --</option>
                    <?php foreach ($projects as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $selectedProjectId == $p['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['project_name']) ?> (<?= htmlspecialchars($p['code']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <!-- Weightage Configuration Form -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fas fa-balance-scale text-warning me-2"></i>Performance Metric Weights & Target Benchmarks
            </h6>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary fw-bold" onclick="autoBalanceWeights()">
                    <i class="fas fa-balance-scale-right me-1 text-primary"></i>Auto-Balance Weights (100 Pts)
                </button>
                <button type="button" class="btn btn-sm btn-success fw-bold" onclick="addMetricRow()">
                    <i class="fas fa-plus-circle me-1"></i>Add Metric Line Item
                </button>
            </div>
        </div>
        <div class="card-body p-4">
            <form action="<?= base_url('performance/save-config') ?>" method="POST" id="kpiConfigForm">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="project_id" value="<?= $selectedProjectId ?>">

                <div class="alert alert-info border-0 shadow-2xs mb-4 fs-8 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <i class="fas fa-info-circle me-1 text-primary"></i> The sum of all metric point weights must equal <strong>100 Points</strong>. You can add, edit, remove metric line items and adjust target benchmark points dynamically.
                    </div>
                    <div>
                        <span id="totalWeightBadge" class="badge fs-7 px-3 py-2 bg-danger">
                            Total: <span id="totalWeightValue">0</span> / 100 Points
                        </span>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-hover align-middle mb-0" id="metricsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 180px;">Metric Key (Slug)</th>
                                <th>Display Label</th>
                                <th style="width: 170px;">Weight Points (Out of 100)</th>
                                <th style="width: 170px;">Target Benchmark %</th>
                                <th>Description / Formula</th>
                                <th style="width: 70px;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="metricsTbody">
                            <?php 
                            $totalWeight = 0;
                            $rowCount = 0;
                            if (!empty($kpiConfigs)):
                                foreach ($kpiConfigs as $kc): 
                                    $w = (int)$kc['weight_points'];
                                    $totalWeight += $w;
                                    $rowCount++;
                            ?>
                            <tr class="metric-row">
                                <td>
                                    <input type="text" name="kpis[<?= $rowCount ?>][kpi_key]" class="form-control form-control-sm text-monospace fw-bold kpi-key-input" value="<?= htmlspecialchars($kc['kpi_key']) ?>" required placeholder="e.g. sla_adherence">
                                </td>
                                <td>
                                    <input type="text" name="kpis[<?= $rowCount ?>][label]" class="form-control form-control-sm fw-bold" value="<?= htmlspecialchars($kc['label']) ?>" required placeholder="e.g. SLA TAT Adherence">
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="kpis[<?= $rowCount ?>][weight_points]" class="form-control weight-input fw-bold text-primary" value="<?= $w ?>" min="0" max="100" oninput="calculateTotalWeight()" required>
                                        <span class="input-group-text">Pts</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="0.5" name="kpis[<?= $rowCount ?>][target_pct]" class="form-control fw-bold text-success" value="<?= $kc['target_pct'] ?>" min="0" max="100" required>
                                        <span class="input-group-text">%</span>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" name="kpis[<?= $rowCount ?>][description]" class="form-control form-control-sm text-muted fs-8" value="<?= htmlspecialchars($kc['description'] ?? '') ?>" placeholder="Description / evaluation criteria...">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeMetricRow(this)" title="Delete Metric Line Item">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php 
                                endforeach;
                            endif;
                            ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="2" class="fw-bold text-end fs-7">Total Weightage Points:</td>
                                <td>
                                    <span id="totalWeightDisplay" class="badge fs-7 <?= $totalWeight === 100 ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $totalWeight ?> / 100 Points
                                    </span>
                                </td>
                                <td colspan="3" class="fs-8">
                                    <span id="weightWarning" class="<?= $totalWeight === 100 ? 'd-none' : '' ?> text-danger fw-bold">
                                        ⚠️ Total must equal exactly 100 Points. <button type="button" class="btn btn-link btn-sm p-0 text-danger text-decoration-underline ms-1" onclick="autoBalanceWeights()">Click to Auto-Balance</button>
                                    </span>
                                    <span id="weightSuccess" class="<?= $totalWeight === 100 ? '' : 'd-none' ?> text-success fw-bold">
                                        <i class="fas fa-check-circle me-1"></i> Weightage distribution balanced (100 Points)
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Empty State Banner if no rows exist -->
                <div id="emptyMetricsState" class="<?= count($kpiConfigs) === 0 ? '' : 'd-none' ?> text-center py-4 border rounded bg-light mb-4">
                    <i class="fas fa-sliders-h fa-2x text-muted mb-2"></i>
                    <h6 class="fw-bold text-dark mb-1">No Metric Line Items Configured for this Team</h6>
                    <p class="text-muted fs-8 mb-3">Add metric line items dynamically or initialize standard defaults.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-sm btn-primary fw-bold" onclick="initializeDefaultMetrics()">
                            <i class="fas fa-magic me-1"></i>Initialize Recommended Metrics
                        </button>
                        <button type="button" class="btn btn-sm btn-success fw-bold" onclick="addMetricRow()">
                            <i class="fas fa-plus-circle me-1"></i>Add Custom Metric Line Item
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-success btn-sm fw-bold" onclick="addMetricRow()">
                        <i class="fas fa-plus-circle me-1"></i>+ Add Line Item
                    </button>
                    <div class="d-flex gap-2">
                        <a href="<?= base_url('performance') ?>" class="btn btn-light border fw-bold">Cancel</a>
                        <button type="submit" id="btnSaveConfig" class="btn btn-primary fw-bold px-4" <?= $totalWeight !== 100 ? 'disabled' : '' ?>>
                            <i class="fas fa-save me-1"></i>Save Weightage Configuration
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var metricRowCounter = <?= $rowCount ?>;

function calculateTotalWeight() {
    var inputs = document.querySelectorAll('.weight-input');
    var total = 0;
    inputs.forEach(function(inp) {
        total += parseInt(inp.value) || 0;
    });

    var display = document.getElementById('totalWeightDisplay');
    var badgeVal = document.getElementById('totalWeightValue');
    var badgeBox = document.getElementById('totalWeightBadge');
    var warning = document.getElementById('weightWarning');
    var success = document.getElementById('weightSuccess');
    var btn = document.getElementById('btnSaveConfig');
    var emptyState = document.getElementById('emptyMetricsState');

    var rows = document.querySelectorAll('.metric-row');
    if (rows.length === 0) {
        emptyState.classList.remove('d-none');
    } else {
        emptyState.classList.add('d-none');
    }

    display.textContent = total + ' / 100 Points';
    if (badgeVal) badgeVal.textContent = total;

    if (total === 100 && rows.length > 0) {
        display.className = 'badge fs-7 bg-success';
        if (badgeBox) badgeBox.className = 'badge fs-7 px-3 py-2 bg-success';
        warning.classList.add('d-none');
        success.classList.remove('d-none');
        btn.disabled = false;
    } else {
        display.className = 'badge fs-7 bg-danger';
        if (badgeBox) badgeBox.className = 'badge fs-7 px-3 py-2 bg-danger';
        warning.classList.remove('d-none');
        success.classList.add('d-none');
        btn.disabled = true;
    }
}

function addMetricRow(customData) {
    metricRowCounter++;
    var idx = metricRowCounter;
    var tbody = document.getElementById('metricsTbody');
    
    var key = customData ? customData.key : 'metric_' + idx;
    var label = customData ? customData.label : 'New Metric Item ' + idx;
    var weight = customData ? customData.weight : 20;
    var target = customData ? customData.target : 95.00;
    var desc = customData ? customData.desc : 'Metric evaluation criteria';

    var tr = document.createElement('tr');
    tr.className = 'metric-row';
    tr.innerHTML = `
        <td>
            <input type="text" name="kpis[${idx}][kpi_key]" class="form-control form-control-sm text-monospace fw-bold kpi-key-input" value="${key}" required placeholder="e.g. sla_adherence">
        </td>
        <td>
            <input type="text" name="kpis[${idx}][label]" class="form-control form-control-sm fw-bold" value="${label}" required placeholder="e.g. SLA TAT Adherence">
        </td>
        <td>
            <div class="input-group input-group-sm">
                <input type="number" name="kpis[${idx}][weight_points]" class="form-control weight-input fw-bold text-primary" value="${weight}" min="0" max="100" oninput="calculateTotalWeight()" required>
                <span class="input-group-text">Pts</span>
            </div>
        </td>
        <td>
            <div class="input-group input-group-sm">
                <input type="number" step="0.5" name="kpis[${idx}][target_pct]" class="form-control fw-bold text-success" value="${target}" min="0" max="100" required>
                <span class="input-group-text">%</span>
            </div>
        </td>
        <td>
            <input type="text" name="kpis[${idx}][description]" class="form-control form-control-sm text-muted fs-8" value="${desc}" placeholder="Description / evaluation criteria...">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeMetricRow(this)" title="Delete Metric Line Item">
                <i class="fas fa-trash-alt"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    calculateTotalWeight();
}

function removeMetricRow(btn) {
    var tr = btn.closest('tr');
    if (tr) {
        tr.remove();
        calculateTotalWeight();
    }
}

function autoBalanceWeights() {
    var inputs = document.querySelectorAll('.weight-input');
    var count = inputs.length;
    if (count === 0) return;

    var baseWeight = Math.floor(100 / count);
    var remainder = 100 - (baseWeight * count);

    inputs.forEach(function(inp, index) {
        var w = baseWeight + (index === 0 ? remainder : 0);
        inp.value = w;
    });

    calculateTotalWeight();
}

function initializeDefaultMetrics() {
    var tbody = document.getElementById('metricsTbody');
    tbody.innerHTML = '';
    
    var defaults = [
        { key: 'production_volume', label: 'Daily Target Volume Output', weight: 35, target: 100.00, desc: 'Target volume count processed per shift' },
        { key: 'quality_accuracy', label: 'Quality & Data Keying Accuracy', weight: 35, target: 99.00, desc: 'Zero error accuracy rate across internal QC checks' },
        { key: 'tat_compliance', label: 'SLA Turnaround Time Compliance', weight: 30, target: 95.00, desc: 'Percentage of tickets/items processed within agreed TAT' }
    ];

    defaults.forEach(function(d) {
        addMetricRow(d);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    calculateTotalWeight();
});
</script>
