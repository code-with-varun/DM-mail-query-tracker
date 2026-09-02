<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Bulk Ticket Import</h4>
            <p class="text-muted fs-7 mb-0">Dump and upload multiple Query and Task tickets from Excel / CSV</p>
        </div>
        <div>
            <a href="<?= base_url('tickets') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
                <i class="fas fa-arrow-left me-1"></i>Back to Tickets
            </a>
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
                            <h5 class="fw-bold text-dark">Drag & Drop your CSV or Excel file here</h5>
                            <p class="text-muted fs-7 mb-3">or click anywhere in this box to browse files</p>
                            <input type="file" name="csv_file" id="csv_file_input" class="form-control d-none" accept=".csv, .xlsx, .xls" required>
                            <div id="file-name-display" class="badge bg-success fs-6 px-3 py-2 d-none"></div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="<?= base_url('tickets/template') ?>" class="btn btn-outline-success fw-bold">
                                <i class="fas fa-download me-1"></i>Download Sample Template
                            </a>
                            <button type="submit" class="btn btn-success fw-bold px-4">
                                <i class="fas fa-upload me-1"></i>Start Import Process
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Side: Step-by-Step Instructions & Column Reference -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-list-ol me-2"></i>How to Import Tickets (3 Steps)</h6>
                </div>
                <div class="card-body p-4 fs-7">
                    <div class="d-flex gap-3 mb-3">
                        <div class="badge bg-primary rounded-circle p-2 fs-6 flex-shrink-0" style="width:32px; height:32px;">1</div>
                        <div>
                            <strong class="d-block text-dark">Download Template</strong>
                            Download the formatted <code>MQT_Tickets_Import_Template.csv</code> file.
                        </div>
                    </div>
                    <div class="d-flex gap-3 mb-3">
                        <div class="badge bg-primary rounded-circle p-2 fs-6 flex-shrink-0" style="width:32px; height:32px;">2</div>
                        <div>
                            <strong class="d-block text-dark">Dump Ticket Data</strong>
                            Fill in your query or task details. Make sure mandatory fields (Subject, From Address) are populated.
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="badge bg-primary rounded-circle p-2 fs-6 flex-shrink-0" style="width:32px; height:32px;">3</div>
                        <div>
                            <strong class="d-block text-dark">Upload & Import</strong>
                            Upload the saved file. The system will automatically map activities, assignees, and calculate SLA deadlines!
                        </div>
                    </div>
                </div>
            </div>

            <!-- Supported Headers Reference -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-table me-2 text-secondary"></i>Supported Template Headers</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0 fs-8">
                        <thead class="table-light">
                            <tr>
                                <th>Header</th>
                                <th>Mandatory?</th>
                                <th>Values / Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>Ticket Type</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>Query Ticket / Task Ticket</td>
                            </tr>
                            <tr>
                                <td><code>Received Datetime</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>YYYY-MM-DD HH:MM:SS</td>
                            </tr>
                            <tr>
                                <td><code>From Address</code></td>
                                <td><span class="badge bg-danger">Required</span></td>
                                <td>Sender email or INTERNAL_TASK</td>
                            </tr>
                            <tr>
                                <td><code>Subject</code></td>
                                <td><span class="badge bg-danger">Required</span></td>
                                <td>Query subject or task title</td>
                            </tr>
                            <tr>
                                <td><code>Activity Name</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>Agency Billing, Empanelment, Inhouse, Vendor</td>
                            </tr>
                            <tr>
                                <td><code>Sub Activity Name</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>Billing Query, Internal Support, etc.</td>
                            </tr>
                            <tr>
                                <td><code>Priority</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>Low, Medium, High, Critical</td>
                            </tr>
                            <tr>
                                <td><code>Allocated Employee Code</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>EMP002, EMP003, etc.</td>
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
