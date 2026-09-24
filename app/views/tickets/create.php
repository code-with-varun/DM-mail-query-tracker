<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Create New Query Ticket</h4>
            <p class="text-muted fs-7 mb-0">Log incoming email query, allocate employee, and calculate SLA TAT</p>
        </div>
        <a href="<?= base_url('tickets') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Tickets</a>
    </div>

    <!-- Outlook Mail Copy-Paste Auto-Fill Box -->
    <div class="card border-0 shadow-sm mb-4 bg-light border-start border-4 border-primary">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0 text-primary">
                    <i class="fab fa-microsoft me-2"></i>Outlook Quick Mail Auto-Fill
                </h6>
                <span class="badge bg-primary bg-opacity-10 text-primary fs-8">Paste copied Outlook mail row(s) below</span>
            </div>
            <p class="text-muted fs-8 mb-2">
                Copy mail row(s) directly from Outlook list view (`From`, `Subject`, `Received`) and paste into the box below. It will automatically parse and populate the subject, sender, and received time fields in the form.
            </p>
            <div class="position-relative">
                <textarea id="outlook_paste_box" class="form-control fs-7 border-primary border-opacity-25" rows="3" placeholder="Paste Outlook copied mail row(s) here (e.g. From	Subject	Received	Size)..."></textarea>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <div id="outlook_parse_status" class="fs-8 fw-bold"></div>
                <div class="d-flex gap-2">
                    <button type="button" id="btn_clear_outlook" class="btn btn-outline-secondary btn-sm fs-8" style="display:none;">
                        <i class="fas fa-times me-1"></i>Clear
                    </button>
                    <button type="button" id="btn_parse_outlook" class="btn btn-primary btn-sm fs-8 fw-bold">
                        <i class="fas fa-magic me-1"></i>Auto-Fill Form
                    </button>
                </div>
            </div>

            <!-- Parsed Mails Preview List (When multiple mails copied) -->
            <div id="outlook_mails_preview" class="mt-3" style="display:none;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="fw-bold text-dark fs-8"><i class="fas fa-list me-1 text-primary"></i>Parsed Mails (<span id="parsed_count">0</span> detected):</small>
                    <small class="text-muted fs-8">Click "Populate Form" to fill any mail details into the form below</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover bg-white border rounded mb-0 align-middle">
                        <thead class="table-light fs-8">
                            <tr>
                                <th>From</th>
                                <th>Subject</th>
                                <th>Received Time</th>
                                <th class="text-end" style="width: 140px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="parsed_mails_body" class="fs-8">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Ticket Form -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="<?= base_url('tickets/create') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-bold">Ticket Type <span class="text-danger">*</span></label>
                        <select name="ticket_type" class="form-select" required>
                            <option value="Query Ticket" selected>Query Ticket</option>
                            <option value="Task Ticket">Task Ticket</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-bold">Received Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="received_datetime" id="received_datetime" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-bold">From Address / Sender <span class="text-danger">*</span></label>
                        <input type="text" name="from_address" id="from_address" class="form-control" placeholder="client@agency.com or Sender Name" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fs-7 fw-bold">Email Subject <span class="text-danger">*</span></label>
                    <input type="text" name="subject" id="subject" class="form-control" placeholder="Enter query email subject line" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-bold">Division</label>
                        <select name="division_id" id="division_id" class="form-select">
                            <option value="">Select Division</option>
                            <?php foreach ($divisions as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['division_name']) ?> (<?= $d['code'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Dynamic Dependent Dropdown: Activity -->
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-bold">Activity <span class="text-danger">*</span></label>
                        <select name="activity_id" id="activity_id" class="form-select" required>
                            <option value="">Select Parent Activity</option>
                            <?php foreach ($activities as $act): ?>
                                <option value="<?= $act['id'] ?>"><?= htmlspecialchars($act['activity_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Dynamic Dependent Dropdown: Sub-Activity (Populated via AJAX) -->
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-bold">Sub Activity <span class="text-danger">*</span></label>
                        <select name="sub_activity_id" id="sub_activity_id" class="form-select" required>
                            <option value="">Select Activity First</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-bold">Category</label>
                        <select name="category_id" id="category_id" class="form-select">
                            <option value="">General Query / Unclassified</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-bold">Allocated To (Employee)</label>
                        <select name="allocated_to" id="allocated_to" class="form-select">
                            <option value="">Unassigned (Open Pool)</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?> (<?= $u['user_code'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-bold">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                            <option value="Critical">Critical</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-bold">TAT Target Date & Time</label>
                        <input type="datetime-local" name="tat_datetime" id="tat_datetime" class="form-control" placeholder="Auto calculated from SLA">
                        <small class="text-muted fs-8">Leaves empty to auto-calculate based on Sub-Activity SLA</small>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-bold">Agency Code</label>
                        <input type="text" name="agency_code" class="form-control" placeholder="e.g. AGC-9940">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-bold">Manager Name</label>
                        <input type="text" name="manager_name" class="form-control" placeholder="Reporting Manager Name">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fs-7 fw-bold">Remarks / Description</label>
                    <textarea name="remarks" class="form-control" rows="3" placeholder="Enter query details, initial notes, or instructions..."></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fs-7 fw-bold">Attachment Upload (Optional)</label>
                    <input type="file" name="attachment" class="form-control">
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= base_url('tickets') ?>" class="btn btn-light border px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="fas fa-check-circle me-2"></i>Create Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Outlook Copy-Paste Parser JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pasteBox = document.getElementById('outlook_paste_box');
    const parseStatus = document.getElementById('outlook_parse_status');
    const clearBtn = document.getElementById('btn_clear_outlook');
    const previewContainer = document.getElementById('outlook_mails_preview');
    const previewBody = document.getElementById('parsed_mails_body');
    const parsedCountSpan = document.getElementById('parsed_count');

    // Form input references
    const fromInput = document.getElementById('from_address');
    const subjectInput = document.getElementById('subject');
    const receivedInput = document.getElementById('received_datetime');

    function parseDateToLocalFormat(dateStr) {
        if (!dateStr) return '';
        dateStr = dateStr.trim();

        // Check native JS Date parsing first
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

        // Regex fallback matching M/D/YYYY H:MM AM/PM or D/M/YYYY H:MM AM/PM
        // e.g. "9/10/2026 3:53 PM" or "9/10/2026 15:53"
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

            // Filter out header lines (e.g. From	Subject	Received...)
            if (/^From\b/i.test(trimmed) && /Subject\b/i.test(trimmed)) return;

            // Split line by Tabs or 2+ spaces
            let parts = trimmed.split(/\t+/);
            if (parts.length < 2) {
                parts = trimmed.split(/\s{2,}/);
            }

            if (parts.length >= 2) {
                let fromVal = parts[0].trim();
                let subjectVal = parts[1].trim();
                let receivedVal = parts[2] ? parts[2].trim() : '';

                // Skip header duplicates
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

        // Visual feedback effect on populated inputs
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

        // Auto-fill the 1st mail into form
        populateForm(mails[0]);
        clearBtn.style.display = 'inline-block';

        if (mails.length === 1) {
            parseStatus.innerHTML = '<span class="text-success"><i class="fas fa-check-circle me-1"></i>Successfully auto-filled form with Outlook mail details!</span>';
            previewContainer.style.display = 'none';
        } else {
            parseStatus.innerHTML = `<span class="text-success"><i class="fas fa-check-circle me-1"></i>Detected ${mails.length} Outlook mails! Auto-filled 1st mail into form below.</span>`;
            parsedCountSpan.textContent = mails.length;
            previewContainer.style.display = 'block';

            // Build preview table rows
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

            // Attach event listener for row buttons
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

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // Auto trigger parsing on paste event
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

    clearBtn.addEventListener('click', function() {
        pasteBox.value = '';
        parseStatus.innerHTML = '';
        previewContainer.style.display = 'none';
        this.style.display = 'none';
    });

    document.getElementById('btn_parse_outlook').addEventListener('click', handleParse);
});
</script>
