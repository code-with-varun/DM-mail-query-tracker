<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Bulk Ticket Import</h4>
            <p class="text-muted fs-7 mb-0">Dump and upload multiple Query and Task tickets. Mandatory yellow fields auto-populate Division, Activity, and Employee!</p>
        </div>
        <div>
            <a href="<?= base_url('tickets') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
                <i class="fas fa-arrow-left me-1"></i>Back to Tickets
            </a>
        </div>
    </div>

    <!-- Master CSV Reference Download Bar -->
    <div class="card border-0 shadow-sm mb-4 bg-white">
        <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-database text-primary fs-5"></i>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Master Reference CSV Exports</h6>
                    <small class="text-muted fs-8">Download live master data to check exact Sub-Activity Names, Divisions, and Default Employees.</small>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= base_url('tickets/template') ?>" class="btn btn-success btn-sm fw-bold">
                    <i class="fas fa-file-csv me-1"></i>Ticket Dump Template (.csv)
                </a>
                <a href="<?= base_url('master/export_sub_activities') ?>" class="btn btn-outline-primary btn-sm fw-bold">
                    <i class="fas fa-list me-1"></i>Sub-Activities (.csv)
                </a>
                <a href="<?= base_url('master/export_activities') ?>" class="btn btn-outline-primary btn-sm fw-bold">
                    <i class="fas fa-folder me-1"></i>Activities (.csv)
                </a>
                <a href="<?= base_url('master/export_divisions') ?>" class="btn btn-outline-primary btn-sm fw-bold">
                    <i class="fas fa-building me-1"></i>Divisions (.csv)
                </a>
                <a href="<?= base_url('employees/export') ?>" class="btn btn-outline-primary btn-sm fw-bold">
                    <i class="fas fa-users me-1"></i>Employees (.csv)
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Side: Upload Card -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-file-upload text-success me-2"></i>Upload Filled Excel / CSV File</h6>
                </div>
                <div class="card-body p-4">
                    <form action="<?= base_url('tickets/import') ?>" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">

                        <!-- Drag & Drop Zone -->
                        <div id="drop-zone" class="mb-4 text-center p-5 border border-2 border-dashed rounded bg-light" style="cursor: pointer; transition: all 0.2s ease;">
                            <i class="fas fa-file-csv fs-1 text-success mb-3"></i>
                            <h5 class="fw-bold text-dark">Drag & Drop your CSV file here</h5>
                            <p class="text-muted fs-7 mb-3">or click anywhere in this box to browse files</p>
                            <input type="file" name="csv_file" id="csv_file_input" class="form-control d-none" accept=".csv, .xlsx, .xls" required>
                            <div id="file-name-display" class="badge bg-success fs-6 px-3 py-2 d-none"></div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="<?= base_url('tickets/template') ?>" class="btn btn-outline-success fw-bold">
                                <i class="fas fa-download me-1"></i>Download CSV Template
                            </a>
                            <button type="submit" class="btn btn-success fw-bold px-4">
                                <i class="fas fa-upload me-1"></i>Start Import Process
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Side: Step-by-Step Instructions & Column Hierarchy Reference -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-sitemap me-2"></i>Auto-Population Hierarchy Rule</h6>
                </div>
                <div class="card-body p-4 fs-7">
                    <div class="alert alert-warning border-warning p-3 mb-3">
                        <strong class="d-block text-dark mb-1"><i class="fas fa-magic text-warning me-1"></i>Sub-Activity Hierarchy Mapping</strong>
                        Providing the <span class="badge bg-warning text-dark fw-bold">Sub Activity Name</span> automatically populates:
                        <ul class="mb-0 mt-1 ps-3 text-dark">
                            <li>🍊 <strong>Activity Name</strong> (Division > Activity)</li>
                            <li>🍊 <strong>Division Code</strong> (e.g. DIV01, DIV02)</li>
                            <li>🍊 <strong>Allocated Employee</strong> (Mapped Default Assignee)</li>
                            <li>⏰ <strong>SLA TAT Deadline</strong> (Sub-Activity Hours)</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Supported Headers Reference -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-table me-2 text-secondary"></i>Template Headers Legend</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0 fs-8">
                        <thead class="table-light">
                            <tr>
                                <th>Header</th>
                                <th>Type</th>
                                <th>Behavior</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="table-warning">
                                <td><code>Ticket Type</code></td>
                                <td><span class="badge bg-warning text-dark">Mandatory</span></td>
                                <td>Query Ticket / Task Ticket</td>
                            </tr>
                            <tr class="table-warning">
                                <td><code>Received Datetime</code></td>
                                <td><span class="badge bg-warning text-dark">Mandatory</span></td>
                                <td>YYYY-MM-DD HH:MM:SS</td>
                            </tr>
                            <tr class="table-warning">
                                <td><code>From Address</code></td>
                                <td><span class="badge bg-warning text-dark">Mandatory</span></td>
                                <td>Sender email or INTERNAL_TASK</td>
                            </tr>
                            <tr class="table-warning">
                                <td><code>Subject</code></td>
                                <td><span class="badge bg-warning text-dark">Mandatory</span></td>
                                <td>Query subject or task title</td>
                            </tr>
                            <tr class="table-warning">
                                <td><code>Sub Activity Name</code></td>
                                <td><span class="badge bg-warning text-dark">Mandatory</span></td>
                                <td>Triggers backend auto-population</td>
                            </tr>
                            <tr>
                                <td><code>Activity Name</code></td>
                                <td><span class="badge bg-info text-dark">Auto-Populated</span></td>
                                <td>Inferred from Sub-Activity if blank</td>
                            </tr>
                            <tr>
                                <td><code>Division Code</code></td>
                                <td><span class="badge bg-info text-dark">Auto-Populated</span></td>
                                <td>Inferred from Sub-Activity if blank</td>
                            </tr>
                            <tr>
                                <td><code>Allocated Employee Code</code></td>
                                <td><span class="badge bg-info text-dark">Auto-Populated</span></td>
                                <td>Inferred from Sub-Activity default employee</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('csv_file_input');
    const nameDisplay = document.getElementById('file-name-display');

    dropZone.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            nameDisplay.textContent = 'Selected: ' + this.files[0].name;
            nameDisplay.classList.remove('d-none');
        }
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropZone.classList.add('bg-white', 'border-success');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropZone.classList.remove('bg-white', 'border-success');
        }, false);
    });

    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files && files.length > 0) {
            fileInput.files = files;
            nameDisplay.textContent = 'Selected: ' + files[0].name;
            nameDisplay.classList.remove('d-none');
        }
    });
});
</script>
