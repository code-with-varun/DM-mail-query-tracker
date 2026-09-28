<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-cogs text-primary me-2"></i>PKT Question Bank & Test Manager</h4>
            <p class="text-muted fs-7 mb-0">Import questions via Excel/CSV, manage question bank, and publish online PKT exams</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('training/pkt-template') ?>" class="btn btn-outline-success btn-sm fw-bold">
                <i class="fas fa-file-excel me-1"></i>Download Excel Template
            </a>
            <button class="btn btn-success btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                <i class="fas fa-file-import me-1"></i>Import Excel Questions
            </button>
            <button class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                <i class="fas fa-plus me-1"></i>Add Single Question
            </button>
            <button class="btn btn-dark btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#createTestModal">
                <i class="fas fa-paper-plane me-1"></i>Raise & Publish Test
            </button>
        </div>
    </div>

    <!-- Management Tabs -->
    <ul class="nav nav-tabs border-bottom mb-4" id="manageTab" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-bold py-2 px-4" id="questions-tab" data-bs-toggle="tab" data-bs-target="#questionsTabContent" type="button">
                <i class="fas fa-database me-2 text-primary"></i>Question Bank (<?= count($questions) ?>)
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold py-2 px-4" id="tests-tab" data-bs-toggle="tab" data-bs-target="#testsTabContent" type="button">
                <i class="fas fa-list-check me-2 text-primary"></i>Published Exams (<?= count($tests) ?>)
            </button>
        </li>
    </ul>

    <div class="tab-content" id="manageTabContent">
        <!-- Tab 1: Question Bank -->
        <div class="tab-pane fade show active" id="questionsTabContent">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light fs-7">
                                <tr>
                                    <th>#</th>
                                    <th>Sub Activity</th>
                                    <th style="width: 40%;">Question</th>
                                    <th>Options</th>
                                    <th>Correct Answer</th>
                                    <th>Date Added</th>
                                </tr>
                            </thead>
                            <tbody class="fs-7">
                                <?php if (empty($questions)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-folder-open fs-2 mb-2 d-block"></i>
                                            No questions in Question Bank. Click "Import Excel Questions" or "Add Single Question" above.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $i=1; foreach ($questions as $q): ?>
                                        <tr>
                                            <td class="fw-bold"><?= $i++ ?></td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <?= htmlspecialchars($q['activity_name']) ?> &rarr; <?= htmlspecialchars($q['sub_activity_name']) ?>
                                                </span>
                                            </td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($q['question']) ?></td>
                                            <td class="fs-8">
                                                <div><strong>A.</strong> <?= htmlspecialchars($q['option_a']) ?></div>
                                                <div><strong>B.</strong> <?= htmlspecialchars($q['option_b']) ?></div>
                                                <?php if (!empty($q['option_c'])): ?><div><strong>C.</strong> <?= htmlspecialchars($q['option_c']) ?></div><?php endif; ?>
                                                <?php if (!empty($q['option_d'])): ?><div><strong>D.</strong> <?= htmlspecialchars($q['option_d']) ?></div><?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-success fs-8">Option <?= $q['correct_option'] ?></span>
                                            </td>
                                            <td class="text-muted fs-8"><?= date('d M Y', strtotime($q['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Published Tests -->
        <div class="tab-pane fade" id="testsTabContent">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light fs-7">
                                <tr>
                                    <th>Test Title</th>
                                    <th>Sub Activity</th>
                                    <th>Questions Limit</th>
                                    <th>Passing Score</th>
                                    <th>Time Limit</th>
                                    <th>Candidates Attempted</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody class="fs-7">
                                <?php if (empty($tests)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">No tests created yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($tests as $t): ?>
                                        <tr>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($t['test_title']) ?></td>
                                            <td><?= htmlspecialchars($t['sub_activity_name']) ?></td>
                                            <td><?= $t['total_questions'] ?> MCQs</td>
                                            <td class="text-success fw-bold"><?= $t['passing_score'] ?>%</td>
                                            <td><?= $t['time_limit_minutes'] ?> Mins</td>
                                            <td><span class="badge bg-info text-dark"><?= $t['total_candidates'] ?> Employees</span></td>
                                            <td><span class="badge bg-success">Published</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Import Excel Questions -->
<div class="modal fade" id="importExcelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-excel me-2"></i>Import PKT Questions Excel / CSV</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('training/import-questions') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Select Target Sub-Activity <span class="text-danger">*</span></label>
                        <select name="sub_activity_id" class="form-select" required>
                            <option value="">-- Choose Sub Activity --</option>
                            <?php foreach ($subActivities as $sa): ?>
                                <option value="<?= $sa['id'] ?>">
                                    [<?= htmlspecialchars($sa['division_name'] ?? 'Gen') ?>] <?= htmlspecialchars($sa['activity_name']) ?> &rarr; <?= htmlspecialchars($sa['sub_activity_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Upload Excel / CSV File <span class="text-danger">*</span></label>
                        <input type="file" name="excel_file" class="form-control" accept=".xlsx, .xls, .csv" required>
                        <small class="text-muted d-block mt-1">Columns format: <code>Question, Option A, Option B, Option C, Option D, Correct Answer (A/B/C/D)</code></small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm fw-bold"><i class="fas fa-file-import me-1"></i>Start Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add Single Question -->
<div class="modal fade" id="addQuestionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Add Single Question</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('training/add-question') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Sub Activity <span class="text-danger">*</span></label>
                        <select name="sub_activity_id" class="form-select" required>
                            <option value="">-- Select Sub Activity --</option>
                            <?php foreach ($subActivities as $sa): ?>
                                <option value="<?= $sa['id'] ?>"><?= htmlspecialchars($sa['activity_name']) ?> &rarr; <?= htmlspecialchars($sa['sub_activity_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Question Text <span class="text-danger">*</span></label>
                        <textarea name="question" class="form-control" rows="2" required placeholder="Type multiple choice question here..."></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Option A <span class="text-danger">*</span></label>
                            <input type="text" name="option_a" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Option B <span class="text-danger">*</span></label>
                            <input type="text" name="option_b" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Option C</label>
                            <input type="text" name="option_c" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Option D</label>
                            <input type="text" name="option_d" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Correct Option <span class="text-danger">*</span></label>
                        <select name="correct_option" class="form-select fw-bold text-success" required>
                            <option value="A">Option A</option>
                            <option value="B">Option B</option>
                            <option value="C">Option C</option>
                            <option value="D">Option D</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Save Question</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Raise & Publish Test -->
<div class="modal fade" id="createTestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-paper-plane me-2"></i>Raise & Publish PKT Test</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('training/create-test') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Target Sub Activity <span class="text-danger">*</span></label>
                        <select name="sub_activity_id" class="form-select" required>
                            <option value="">-- Choose Sub Activity --</option>
                            <?php foreach ($subActivities as $sa): ?>
                                <option value="<?= $sa['id'] ?>"><?= htmlspecialchars($sa['activity_name']) ?> &rarr; <?= htmlspecialchars($sa['sub_activity_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Test Name / Title <span class="text-danger">*</span></label>
                        <input type="text" name="test_title" class="form-control" placeholder="e.g. Q3 Process Knowledge Exam - Billing" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fs-7 fw-bold">No. of Questions</label>
                            <input type="number" name="total_questions" class="form-control" value="5" min="1" max="50">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fs-7 fw-bold">Passing Score %</label>
                            <input type="number" name="passing_score" class="form-control" value="70" min="10" max="100">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fs-7 fw-bold">Time Limit (Mins)</label>
                            <input type="number" name="time_limit_minutes" class="form-control" value="20" min="1" max="180">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark btn-sm fw-bold"><i class="fas fa-check-circle me-1"></i>Publish Test Now</button>
                </div>
            </form>
        </div>
    </div>
</div>
