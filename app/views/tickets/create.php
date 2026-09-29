<div class="container-fluid px-4 py-3">
    <!-- Compact Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0 text-dark fs-5"><i class="fas fa-plus-circle text-primary me-2"></i>Create New Ticket</h4>
            <small class="text-muted fs-8">Log query email, preserve original mail files, & compute SLA TAT</small>
        </div>
        <a href="<?= base_url('tickets') ?>" class="btn btn-outline-secondary btn-sm fw-bold fs-8">
            <i class="fas fa-arrow-left me-1"></i>Back to Tickets List
        </a>
    </div>

    <!-- Outlook Mail Quick Auto-Fill & Drag-Drop Card -->
    <div class="card border-0 shadow-sm mb-3 bg-light border-start border-3 border-primary">
        <div class="card-body p-2 px-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <ul class="nav nav-pills nav-sm gap-2" id="outlookTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold fs-8 py-1 px-3 rounded-pill" id="drag-drop-tab" data-bs-toggle="pill" data-bs-target="#dragDropTabContent" type="button" role="tab">
                            <i class="fas fa-file-envelope me-1 text-primary"></i>1. Drag & Drop Outlook File (.msg, .eml, .html, .txt)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold fs-8 py-1 px-3 rounded-pill" id="copy-paste-tab" data-bs-toggle="pill" data-bs-target="#copyPasteTabContent" type="button" role="tab">
                            <i class="fab fa-microsoft me-1 text-primary"></i>2. Copy-Paste Outlook Row(s)
                        </button>
                    </li>
                </ul>
                <small class="text-muted fs-8"><i class="fas fa-magic text-primary me-1"></i>Auto-fills form details</small>
            </div>

            <div class="tab-content" id="outlookTabContent">
                <!-- Tab 1: Drag & Drop Outlook File -->
                <div class="tab-pane fade show active" id="dragDropTabContent" role="tabpanel">
                    <div id="outlookDropzone" class="border border-2 border-dashed border-primary rounded-3 p-2 text-center bg-white cursor-pointer" onclick="document.getElementById('ticket_attachment_input').click();">
                        <span class="d-block text-dark fw-bold fs-7 mb-0">
                            <i class="fas fa-paperclip text-primary me-2"></i>Drag & Drop Original Outlook Email File Here (or Click to Browse)
                        </span>
                        <small class="text-muted fs-8">Supports <code>.msg</code>, <code>.eml</code>, <code>.html</code>, <code>.txt</code></small>
                        <div id="selectedFileDisplay" class="fw-bold text-success fs-8 mt-1"></div>
                    </div>
                </div>

                <!-- Tab 2: Copy-Paste Text Auto-Fill -->
                <div class="tab-pane fade" id="copyPasteTabContent" role="tabpanel">
                    <textarea id="outlook_paste_box" class="form-control form-control-sm fs-8 border-primary border-opacity-25 mb-1" rows="2" placeholder="Paste Outlook copied mail row(s) here (e.g. From	Subject	Received)..."></textarea>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div id="outlook_parse_status" class="fs-8 fw-bold"></div>
                        <div class="d-flex gap-2">
                            <button type="button" id="btn_clear_outlook" class="btn btn-outline-secondary btn-sm fs-8 py-0" style="display:none;">
                                <i class="fas fa-times me-1"></i>Clear
                            </button>
                            <button type="button" id="btn_parse_outlook" class="btn btn-primary btn-sm fs-8 fw-bold py-0">
                                <i class="fas fa-magic me-1"></i>Auto-Fill Form
                            </button>
                        </div>
                    </div>

                    <!-- Parsed Mails Preview List -->
                    <div id="outlook_mails_preview" class="mt-2" style="display:none;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="fw-bold text-dark fs-8"><i class="fas fa-list me-1 text-primary"></i>Parsed Mails (<span id="parsed_count">0</span> detected):</small>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover bg-white border rounded mb-0 align-middle">
                                <thead class="table-light fs-8">
                                    <tr>
                                        <th>From</th>
                                        <th>Subject</th>
                                        <th>Received Time</th>
                                        <th class="text-end" style="width: 120px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="parsed_mails_body" class="fs-8"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Ticket Creation Form Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark fs-7"><i class="fas fa-ticket-alt text-primary me-2"></i>Ticket Information & SLA Details</h6>
            <span class="badge bg-light text-muted border fs-8">* Required Fields</span>
        </div>

        <div class="card-body p-3">
            <form action="<?= base_url('tickets/create') ?>" method="POST" enctype="multipart/form-data" id="ticketCreateForm">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                
                <!-- Compact Row 1: Core Identifiers -->
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <label class="form-label fs-8 fw-bold text-dark mb-1">Ticket Type <span class="text-danger">*</span></label>
                        <select name="ticket_type" class="form-select form-select-sm fw-bold" required>
                            <option value="Query Ticket" selected>Query Ticket</option>
                            <option value="Task Ticket">Task Ticket</option>
                        </select>
                    </div>

                    <div class="col-md-2 col-lg-2">
                        <label class="form-label fs-8 fw-bold text-dark mb-1"><i class="fas fa-calendar-alt text-primary me-1"></i>Billing Month <span class="text-danger">*</span></label>
                        <input type="month" name="billing_month" id="billing_month" class="form-control form-control-sm fw-bold" value="<?= date('Y-m') ?>" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fs-8 fw-bold text-dark mb-1">Received Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="received_datetime" id="received_datetime" class="form-control form-control-sm" value="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-8 fw-bold text-dark mb-1">From Address / Sender <span class="text-danger">*</span></label>
                        <input type="text" name="from_address" id="from_address" class="form-control form-control-sm" placeholder="client@agency.com or Sender Name" required>
                    </div>
                </div>

                <!-- Compact Row 2: Subject, Category & Priority -->
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fs-8 fw-bold text-dark mb-1">Email Subject / Title <span class="text-danger">*</span></label>
                        <input type="text" name="subject" id="subject" class="form-control form-control-sm fw-bold" placeholder="Enter query email subject line" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fs-8 fw-bold text-dark mb-1">Category</label>
                        <select name="category_id" id="category_id" class="form-select form-select-sm">
                            <option value="">General Query / Unclassified</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fs-8 fw-bold text-dark mb-1">Priority</label>
                        <select name="priority" class="form-select form-select-sm">
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                            <option value="Critical">Critical</option>
                        </select>
                    </div>
                </div>

                <!-- Compact Row 3: Operational Hierarchy (Division -> Activity -> Sub-Activity) -->
                <div class="row g-2 mb-3 p-2 bg-light rounded border border-light-subtle">
                    <div class="col-md-4">
                        <label class="form-label fs-8 fw-bold text-dark mb-1">Division</label>
                        <select name="division_id" id="division_id" class="form-select form-select-sm" onchange="onFormDivisionChange(this.value)">
                            <option value="">Select Division</option>
                            <?php foreach ($divisions as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['division_name']) ?> (<?= $d['code'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-8 fw-bold text-dark mb-1">Activity <span class="text-danger">*</span></label>
                        <select name="activity_id" id="activity_id" class="form-select form-select-sm" onchange="onFormActivityChange(this.value)" required>
                            <option value="">Select Parent Activity</option>
                            <?php foreach ($activities as $act): ?>
                                <option value="<?= $act['id'] ?>"><?= htmlspecialchars($act['activity_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-8 fw-bold text-dark mb-1">Sub Activity <span class="text-danger">*</span></label>
                        <select name="sub_activity_id" id="sub_activity_id" class="form-select form-select-sm" onchange="onFormSubActivityChange(this.value)" required>
                            <option value="">Select Activity First</option>
                        </select>
                    </div>
                </div>

                <!-- Compact Row 4: Allocation, Metadata & TAT -->
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <label class="form-label fs-8 fw-bold text-dark mb-1">Allocated To (Maker Employee)</label>
                        <select name="allocated_to" id="allocated_to" class="form-select form-select-sm">
                            <option value="">Unassigned (Open Pool)</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?> (<?= $u['user_code'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fs-8 fw-bold text-dark mb-1">Agency Code</label>
                        <input type="text" name="agency_code" class="form-control form-control-sm" placeholder="e.g. AGC-9940">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fs-8 fw-bold text-dark mb-1">Manager Name</label>
                        <input type="text" name="manager_name" class="form-control form-control-sm" placeholder="Reporting Manager Name">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fs-8 fw-bold text-dark mb-1">TAT Target Date & Time</label>
                        <input type="datetime-local" name="tat_datetime" id="tat_datetime" class="form-control form-control-sm fw-bold" placeholder="Auto SLA">
                    </div>
                </div>

                <!-- Compact Row 5: Remarks & File Attachment -->
                <div class="row g-2 mb-3">
                    <div class="col-md-7">
                        <label class="form-label fs-8 fw-bold text-dark mb-1">Remarks / Description</label>
                        <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="Enter query details or initial instructions..."></textarea>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label fs-8 fw-bold text-dark mb-1">Original Mail Attachment / File</label>
                        <input type="file" name="attachment" id="ticket_attachment_input" class="form-control form-control-sm" onchange="onFileInputChange(this)">
                        <small class="text-muted fs-8 d-block mt-1">Preserves original Outlook <code>.msg</code>, <code>.eml</code>, <code>.html</code> file on ticket record</small>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                    <a href="<?= base_url('tickets') ?>" class="btn btn-light btn-sm border px-3 fw-bold">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold"><i class="fas fa-check-circle me-1"></i>Create Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript Engine -->
<script>
function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// Dynamic Cascading Dropdowns
function onFormDivisionChange(divId) {
    var actSelect = document.getElementById('activity_id');
    var subSelect = document.getElementById('sub_activity_id');
    
    actSelect.innerHTML = '<option value="">Loading Activities...</option>';
    subSelect.innerHTML = '<option value="">Select Activity First</option>';

    var url = '<?= base_url("api/activities") ?>' + (divId ? '?division_id=' + divId : '');
    fetch(url)
        .then(res => res.json())
        .then(res => {
            var html = '<option value="">Select Parent Activity</option>';
            if (res.success && res.data && res.data.length > 0) {
                res.data.forEach(act => {
                    html += '<option value="' + act.id + '">' + escapeHtml(act.activity_name) + '</option>';
                });
            } else {
                html = '<option value="">No Activities Found</option>';
            }
            actSelect.innerHTML = html;
        })
        .catch(() => {
            actSelect.innerHTML = '<option value="">Select Parent Activity</option>';
        });
}

function onFormActivityChange(actId) {
    var subSelect = document.getElementById('sub_activity_id');
    if (!actId) {
        subSelect.innerHTML = '<option value="">Select Activity First</option>';
        return;
    }

    subSelect.innerHTML = '<option value="">Loading Sub-Activities...</option>';

    fetch('<?= base_url("api/sub-activities?activity_id=") ?>' + actId)
        .then(res => res.json())
        .then(res => {
            var html = '<option value="">Select Sub-Activity</option>';
            if (res.success && res.data && res.data.length > 0) {
                res.data.forEach(sa => {
                    html += '<option value="' + sa.id + '" data-tat="' + (sa.default_tat_hours || 24) + '" data-user="' + (sa.default_user_id || '') + '">' + escapeHtml(sa.sub_activity_name) + '</option>';
                });
            } else {
                html = '<option value="">No Sub-Activities Found</option>';
            }
            subSelect.innerHTML = html;
        })
        .catch(() => {
            subSelect.innerHTML = '<option value="">Select Sub-Activity</option>';
        });
}

function onFormSubActivityChange(subId) {
    var subSelect = document.getElementById('sub_activity_id');
    var selectedOpt = subSelect.options[subSelect.selectedIndex];
    if (!selectedOpt || !selectedOpt.value) return;

    var tatHours = parseInt(selectedOpt.getAttribute('data-tat') || 24, 10);
    var defaultUserId = selectedOpt.getAttribute('data-user');

    // Auto-calculate TAT Datetime
    var now = new Date();
    now.setHours(now.getHours() + tatHours);
    
    var yyyy = now.getFullYear();
    var mm = (now.getMonth() + 1).toString().padStart(2, '0');
    var dd = now.getDate().toString().padStart(2, '0');
    var hh = now.getHours().toString().padStart(2, '0');
    var min = now.getMinutes().toString().padStart(2, '0');
    
    var tatInput = document.getElementById('tat_datetime');
    if (tatInput) {
        tatInput.value = `${yyyy}-${mm}-${dd}T${hh}:${min}`;
        tatInput.classList.add('border-success');
        setTimeout(() => tatInput.classList.remove('border-success'), 1500);
    }

    // Auto-select Default Employee if available
    var allocSelect = document.getElementById('allocated_to');
    if (allocSelect && defaultUserId) {
        allocSelect.value = defaultUserId;
        allocSelect.classList.add('border-success');
        setTimeout(() => allocSelect.classList.remove('border-success'), 1500);
    }
}

// Drag & Drop Outlook File Handling
document.addEventListener('DOMContentLoaded', function() {
    var dropzone = document.getElementById('outlookDropzone');
    var fileInput = document.getElementById('ticket_attachment_input');

    if (dropzone && fileInput) {
        ['dragenter', 'dragover'].forEach(function(eventName) {
            dropzone.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('bg-primary', 'bg-opacity-10');
            }, false);
        });

        ['dragleave', 'drop'].forEach(function(eventName) {
            dropzone.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('bg-primary', 'bg-opacity-10');
            }, false);
        });

        dropzone.addEventListener('drop', function(e) {
            var dt = e.dataTransfer;
            var files = dt.files;

            if (files && files.length > 0) {
                fileInput.files = files;
                onFileInputChange(fileInput);
            }
        });
    }
});

function onFileInputChange(input) {
    var display = document.getElementById('selectedFileDisplay');
    var subjectInput = document.getElementById('subject');
    
    if (input.files && input.files[0]) {
        var file = input.files[0];
        if (display) {
            display.innerHTML = '<i class="fas fa-check-circle me-1"></i>Selected: <strong>' + escapeHtml(file.name) + '</strong> (' + (file.size / 1024).toFixed(1) + ' KB)';
        }
        
        if (subjectInput && subjectInput.value.trim() === '') {
            var nameWithoutExt = file.name.substring(0, file.name.lastIndexOf('.')) || file.name;
            subjectInput.value = nameWithoutExt.replace(/_/g, ' ');
            subjectInput.classList.add('border-success');
            setTimeout(() => subjectInput.classList.remove('border-success'), 1500);
        }
    } else {
        if (display) display.innerHTML = '';
    }
}

// Outlook Copy-Paste Parser JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const pasteBox = document.getElementById('outlook_paste_box');
    const parseStatus = document.getElementById('outlook_parse_status');
    const clearBtn = document.getElementById('btn_clear_outlook');
    const previewContainer = document.getElementById('outlook_mails_preview');
    const previewBody = document.getElementById('parsed_mails_body');
    const parsedCountSpan = document.getElementById('parsed_count');

    const fromInput = document.getElementById('from_address');
    const subjectInput = document.getElementById('subject');
    const receivedInput = document.getElementById('received_datetime');

    function parseDateToLocalFormat(dateStr) {
        if (!dateStr) return '';
        dateStr = dateStr.trim();
        try {
            let parsedDate = new Date(dateStr);
            if (!isNaN(parsedDate.getTime())) {
                let yyyy = parsedDate.getFullYear();
                let mm = (parsedDate.getMonth() + 1).toString().padStart(2, '0');
                let dd = parsedDate.getDate().toString().padStart(2, '0');
                let hh = parsedDate.getHours().toString().padStart(2, '0');
                let min = parsedDate.getMinutes().toString().padStart(2, '0');
                return `${yyyy}-${mm}-${dd}T${hh}:${min}`;
            }
        } catch (e) {}

        const match = dateStr.match(/(\d{1,4})[\/\.-](\d{1,4})[\/\.-](\d{1,4})(?:\s+(\d{1,2}):(\d{2})(?::(\d{2}))?\s*(AM|PM)?)?/i);
        if (!match) return '';

        let part1 = parseInt(match[1], 10);
        let part2 = parseInt(match[2], 10);
        let part3 = parseInt(match[3], 10);
        let hours = match[4] ? parseInt(match[4], 10) : 0;
        let minutes = match[5] ? parseInt(match[5], 10) : 0;
        let ampm = match[7] ? match[7].toUpperCase() : null;

        if (ampm === 'PM' && hours < 12) hours += 12;
        if (ampm === 'AM' && hours === 12) hours = 0;

        let year, month, day;
        if (part1 > 31) {
            year = part1; month = part2; day = part3;
        } else {
            year = part3 < 100 ? 2000 + part3 : part3;
            month = part1;
            day = part2;
        }

        const yyyy = year.toString().padStart(4, '20');
        const mm = month.toString().padStart(2, '0');
        const dd = day.toString().padStart(2, '0');
        const hh = hours.toString().padStart(2, '0');
        const min = minutes.toString().padStart(2, '0');

        return `${yyyy}-${mm}-${dd}T${hh}:${min}`;
    }

    function parseOutlookText(rawText) {
        if (!rawText || !rawText.trim()) return [];
        const lines = rawText.split(/\r?\n/);
        const parsed = [];

        lines.forEach(line => {
            let trimmed = line.trim();
            if (!trimmed) return;
            if (/^From\b/i.test(trimmed) && /Subject\b/i.test(trimmed)) return;

            let parts = trimmed.split(/\t+/);
            if (parts.length < 2) {
                parts = trimmed.split(/\s{2,}/);
            }

            if (parts.length >= 2) {
                let fromVal = parts[0].trim();
                let subjectVal = parts[1].trim();
                let receivedVal = parts[2] ? parts[2].trim() : '';

                if (fromVal.toLowerCase() === 'from' || subjectVal.toLowerCase() === 'subject') return;

                parsed.push({
                    from: fromVal,
                    subject: subjectVal,
                    receivedRaw: receivedVal,
                    receivedFormatted: parseDateToLocalFormat(receivedVal)
                });
            }
        });

        return parsed;
    }

    function populateForm(item) {
        if (item.from && fromInput) fromInput.value = item.from;
        if (item.subject && subjectInput) subjectInput.value = item.subject;
        if (item.receivedFormatted && receivedInput) receivedInput.value = item.receivedFormatted;

        [fromInput, subjectInput, receivedInput].forEach(input => {
            if (input) {
                input.classList.add('border-success');
                setTimeout(() => input.classList.remove('border-success'), 2000);
            }
        });
    }

    function handleParse() {
        const text = pasteBox.value;
        const mails = parseOutlookText(text);

        if (mails.length === 0) {
            parseStatus.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>No valid Outlook mail rows detected. Ensure tabs or spaces separate From, Subject, and Received columns.</span>';
            previewContainer.style.display = 'none';
            clearBtn.style.display = text ? 'inline-block' : 'none';
            return;
        }

        populateForm(mails[0]);
        clearBtn.style.display = 'inline-block';

        if (mails.length === 1) {
            parseStatus.innerHTML = '<span class="text-success"><i class="fas fa-check-circle me-1"></i>Successfully auto-filled form with Outlook mail details!</span>';
            previewContainer.style.display = 'none';
        } else {
            parseStatus.innerHTML = `<span class="text-success"><i class="fas fa-check-circle me-1"></i>Detected ${mails.length} Outlook mails! Auto-filled 1st mail into form below.</span>`;
            parsedCountSpan.textContent = mails.length;
            previewContainer.style.display = 'block';

            let rowsHtml = '';
            mails.forEach((m, idx) => {
                rowsHtml += `
                    <tr class="${idx === 0 ? 'table-success bg-opacity-25' : ''}">
                        <td class="fw-bold text-dark">${escapeHtml(m.from)}</td>
                        <td>${escapeHtml(m.subject)}</td>
                        <td><small class="text-muted">${escapeHtml(m.receivedRaw)}</small></td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-${idx === 0 ? 'success' : 'outline-primary'} py-0 px-2 btn-populate-mail" data-index="${idx}">
                                ${idx === 0 ? '<i class="fas fa-check me-1"></i>Active' : '<i class="fas fa-edit me-1"></i>Populate Form'}
                            </button>
                        </td>
                    </tr>
                `;
            });
            previewBody.innerHTML = rowsHtml;

            document.querySelectorAll('.btn-populate-mail').forEach(btn => {
                btn.addEventListener('click', function() {
                    const idx = parseInt(this.dataset.index, 10);
                    if (mails[idx]) {
                        populateForm(mails[idx]);
                        parseStatus.innerHTML = `<span class="text-success"><i class="fas fa-check-circle me-1"></i>Populated Mail #${idx + 1} (${escapeHtml(mails[idx].subject)}) into form!</span>`;
                        
                        document.querySelectorAll('#parsed_mails_body tr').forEach((tr, i) => {
                            if (i === idx) {
                                tr.className = 'table-success bg-opacity-25';
                                tr.querySelector('.btn-populate-mail').className = 'btn btn-sm btn-success py-0 px-2 btn-populate-mail';
                                tr.querySelector('.btn-populate-mail').innerHTML = '<i class="fas fa-check me-1"></i>Active';
                            } else {
                                tr.className = '';
                                tr.querySelector('.btn-populate-mail').className = 'btn btn-sm btn-outline-primary py-0 px-2 btn-populate-mail';
                                tr.querySelector('.btn-populate-mail').innerHTML = '<i class="fas fa-edit me-1"></i>Populate Form';
                            }
                        });
                    }
                });
            });
        }
    }

    if (pasteBox) {
        pasteBox.addEventListener('paste', function() {
            setTimeout(handleParse, 100);
        });

        pasteBox.addEventListener('input', function() {
            if (this.value.trim() === '') {
                parseStatus.innerHTML = '';
                previewContainer.style.display = 'none';
                clearBtn.style.display = 'none';
            } else {
                clearBtn.style.display = 'inline-block';
            }
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            pasteBox.value = '';
            parseStatus.innerHTML = '';
            previewContainer.style.display = 'none';
            this.style.display = 'none';
        });
    }

    if (document.getElementById('btn_parse_outlook')) {
        document.getElementById('btn_parse_outlook').addEventListener('click', handleParse);
    }
});
</script>
