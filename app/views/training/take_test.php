<div class="container-fluid px-4 py-4">
    <!-- Test Header & Timer Header -->
    <div class="card border-0 shadow-sm mb-4 bg-primary text-white">
        <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <span class="badge bg-white text-primary fw-bold mb-1">PKT ONLINE EXAM</span>
                <h4 class="fw-bold mb-0"><?= htmlspecialchars($test['test_title']) ?></h4>
                <p class="mb-0 fs-7 text-white-50">Sub Activity: <?= htmlspecialchars($test['sub_activity_name']) ?> &bull; Total Questions: <?= count($questions) ?> &bull; Passing Criteria: <?= $test['passing_score'] ?>%</p>
            </div>

            <div class="bg-white text-dark px-4 py-2 rounded shadow-sm text-center">
                <span class="fs-8 text-muted d-block text-uppercase fw-bold">Time Remaining</span>
                <span id="testTimerDisplay" class="fs-4 fw-bold text-danger"><i class="fas fa-clock me-1"></i><?= sprintf('%02d:00', $test['time_limit_minutes']) ?></span>
            </div>
        </div>
    </div>

    <!-- Questions Form -->
    <form action="<?= base_url('training/submit-test') ?>" method="POST" id="pktExamForm">
        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
        <input type="hidden" name="test_id" value="<?= $test['id'] ?>">

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <?php $idx = 1; foreach ($questions as $q): ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark fs-6"><i class="fas fa-question-circle text-primary me-2"></i>Question <?= $idx ?> of <?= count($questions) ?></span>
                            <span class="badge bg-secondary">1 Mark</span>
                        </div>
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-dark fs-6 mb-4"><?= nl2br(htmlspecialchars($q['question'])) ?></h6>

                            <div class="row g-3">
                                <?php 
                                $opts = [
                                    'A' => $q['option_a'],
                                    'B' => $q['option_b'],
                                    'C' => $q['option_c'],
                                    'D' => $q['option_d']
                                ];
                                foreach ($opts as $key => $val): if (empty($val)) continue;
                                ?>
                                    <div class="col-md-6">
                                        <label class="form-check p-3 border rounded h-100 d-flex align-items-center cursor-pointer bg-white hover-shadow transition-all" style="cursor: pointer;">
                                            <input class="form-check-input ms-0 me-3 fs-5" type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $key ?>" required>
                                            <div class="fs-7">
                                                <strong class="text-primary me-2"><?= $key ?>.</strong> <?= htmlspecialchars($val) ?>
                                            </div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php $idx++; endforeach; ?>

                <div class="card border-0 shadow-sm p-4 bg-white text-center mb-5">
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-check-double text-success me-2"></i>Ready to Submit Your Answers?</h6>
                    <p class="text-muted fs-7 mb-4">Please verify all questions are answered before submitting. Scores will be calculated automatically.</p>
                    <div>
                        <a href="<?= base_url('training/pkt') ?>" class="btn btn-outline-secondary me-2 px-4" onclick="return confirm('Cancel test? Progress will not be saved.');">Cancel</a>
                        <button type="submit" class="btn btn-success btn-lg fw-bold px-5" id="btnSubmitTest">
                            <i class="fas fa-paper-plane me-2"></i>Submit Exam Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var totalSeconds = <?= (int)$test['time_limit_minutes'] * 60 ?>;
    var timerDisplay = document.getElementById('testTimerDisplay');

    var timerInterval = setInterval(function() {
        totalSeconds--;
        if (totalSeconds <= 0) {
            clearInterval(timerInterval);
            alert('Time is up! Your answers will now be submitted automatically.');
            document.getElementById('pktExamForm').submit();
        } else {
            var mins = Math.floor(totalSeconds / 60);
            var secs = totalSeconds % 60;
            timerDisplay.innerHTML = '<i class="fas fa-clock me-1"></i>' + (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;
        }
    }, 1000);
});
</script>
