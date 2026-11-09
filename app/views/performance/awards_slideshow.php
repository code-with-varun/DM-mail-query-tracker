<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Datamatics Achievers Awards & Honours — Live Slideshow') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0a0e17;
            --gold-primary: #f59e0b;
            --gold-glow: rgba(245, 158, 11, 0.4);
        }
        body {
            background-color: var(--bg-dark);
            color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            padding: 0;
            overflow: hidden;
            height: 100vh;
            width: 100vw;
        }
        .fw-black { font-weight: 900; }
        .slideshow-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            background: radial-gradient(circle at 50% 20%, rgba(30, 27, 75, 0.6) 0%, rgba(10, 14, 23, 1) 80%);
        }
        .ambient-glow {
            position: absolute;
            top: -20%;
            left: 50%;
            transform: translateX(-50%);
            width: 800px;
            height: 500px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.15) 0%, rgba(0, 0, 0, 0) 70%);
            filter: blur(60px);
            pointer-events: none;
            z-index: 1;
        }
        .slide-card {
            display: none;
            animation: fadeInZoom 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            z-index: 2;
        }
        .slide-card.active {
            display: flex;
        }
        @keyframes fadeInZoom {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        .progress-bar-container {
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            position: relative;
            width: 100%;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #f59e0b, #ec4899);
            width: 0%;
            transition: width 0.1s linear;
        }
        .avatar-glow {
            box-shadow: 0 0 35px var(--gold-glow);
            border: 4px solid var(--gold-primary);
        }
        .metric-pill {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 10px 16px;
        }
    </style>
</head>
<body>

<div class="slideshow-container">
    <div class="ambient-glow"></div>

    <!-- HEADER BAR -->
    <header class="d-flex justify-content-between align-items-center px-5 py-3 border-bottom border-light border-opacity-10 position-relative" style="z-index: 10;">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 44px; height: 44px;">
                <i class="fas fa-award"></i>
            </div>
            <div>
                <h5 class="fw-black mb-0 tracking-tight text-white">DATAMATICS ANNUAL ACHIEVERS AWARDS</h5>
                <span class="fs-8 text-warning text-uppercase fw-bold letter-spacing-1">Live Honours Showcase • Continuous Floor Broadcast</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-white bg-opacity-10 text-white rounded-pill px-3 py-2 fs-7" id="slideCounter">Slide 1 of <?= count($slides) ?></span>
            <button class="btn btn-outline-light btn-sm rounded-pill px-3" onclick="togglePlay()" id="playPauseBtn">
                <i class="fas fa-pause me-1"></i> Pause
            </button>
            <a href="<?= base_url('performance/awards') ?>" class="btn btn-danger btn-sm rounded-pill px-3">
                <i class="fas fa-times me-1"></i> Exit
            </a>
        </div>
    </header>

    <!-- SLIDES CONTAINER -->
    <main class="flex-grow-1 d-flex align-items-center justify-content-center p-4">
        <?php if (empty($slides)): ?>
            <div class="text-center">
                <i class="fas fa-trophy fa-4x text-warning mb-3"></i>
                <h3 class="fw-bold">No Showcase Records Available</h3>
                <p class="text-muted">Register and bestow awards to activate the broadcast.</p>
                <a href="<?= base_url('performance/awards') ?>" class="btn btn-primary rounded-pill px-4">Go to Hub</a>
            </div>
        <?php else: ?>
            <?php foreach ($slides as $idx => $s): ?>
            <div class="slide-card <?= $idx === 0 ? 'active' : '' ?> flex-column align-items-center text-center max-w-4xl" data-index="<?= $idx ?>" style="max-width: 850px;">
                
                <!-- TIER BADGE -->
                <div class="d-inline-flex align-items-center gap-2 px-4 py-2 rounded-pill mb-4 shadow" style="background: <?= $s['badge_color'] ?>25; border: 1px solid <?= $s['badge_color'] ?>; color: #fff;">
                    <i class="fas <?= $s['badge_icon'] ?> text-warning fs-5"></i>
                    <span class="fw-bold text-uppercase fs-7 letter-spacing-1"><?= htmlspecialchars($s['award_name']) ?> • <?= htmlspecialchars(strtoupper($s['period_key'])) ?></span>
                </div>

                <!-- AVATAR & GLOW -->
                <div class="position-relative mb-4">
                    <div class="avatar-glow rounded-circle d-flex align-items-center justify-content-center fw-black display-4 mx-auto text-white" style="width: 130px; height: 130px; background: linear-gradient(135deg, <?= $s['badge_color'] ?>, #1e1b4b);">
                        <?= strtoupper(substr($s['recipient_name'], 0, 2)) ?>
                    </div>
                    <?php if ($s['rank_position'] == 1): ?>
                    <span class="position-absolute bottom-0 end-0 badge rounded-circle bg-warning text-dark p-2 shadow-lg fs-5">
                        👑
                    </span>
                    <?php endif; ?>
                </div>

                <!-- RECIPIENT DETAILS -->
                <h1 class="display-5 fw-black text-white mb-1"><?= htmlspecialchars($s['recipient_name']) ?></h1>
                <h5 class="text-warning fw-bold mb-4"><?= htmlspecialchars($s['recipient_role']) ?></h5>

                <!-- CITATION BLOCKQUOTE -->
                <div class="card border-0 rounded-4 p-4 mb-4 text-start shadow-lg" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-left: 4px solid <?= $s['badge_color'] ?> !important;">
                    <p class="fs-5 text-light fst-italic mb-0">
                        <i class="fas fa-quote-left text-warning opacity-50 me-2"></i>
                        <?= htmlspecialchars($s['citation']) ?>
                    </p>
                </div>

                <!-- TELEMETRY KPI TILES -->
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <div class="metric-pill text-center">
                        <span class="fs-8 text-white-50 d-block">Overall Score</span>
                        <span class="fw-black fs-4 text-warning"><?= number_format($s['score'], 1) ?>%</span>
                    </div>
                    <div class="metric-pill text-center">
                        <span class="fs-8 text-white-50 d-block">Productivity</span>
                        <span class="fw-bold fs-5 text-white"><?= number_format($s['productivity_score'], 0) ?>%</span>
                    </div>
                    <div class="metric-pill text-center">
                        <span class="fs-8 text-white-50 d-block">Quality Score</span>
                        <span class="fw-bold fs-5 text-success"><?= number_format($s['quality_score'], 0) ?>%</span>
                    </div>
                    <div class="metric-pill text-center">
                        <span class="fs-8 text-white-50 d-block">SLA Adherence</span>
                        <span class="fw-bold fs-5 text-info"><?= number_format($s['sla_score'], 0) ?>%</span>
                    </div>
                </div>

            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <!-- FOOTER & PROGRESS BAR -->
    <footer>
        <div class="progress-bar-container">
            <div class="progress-fill" id="progressFill"></div>
        </div>
        <div class="d-flex justify-content-between align-items-center px-5 py-2 text-white-50 fs-9">
            <span>Datamatics Global Services Ltd. • Annual Achievers Awards Framework v5.0</span>
            <span>Rotating every 6 seconds • Press ESC to exit</span>
        </div>
    </footer>
</div>

<script>
    const slides = document.querySelectorAll('.slide-card');
    const totalSlides = slides.length;
    let currentSlide = 0;
    let isPlaying = true;
    let progress = 0;
    const duration = 6000; // 6 seconds per slide
    const intervalTime = 50;
    let timer = null;

    function showSlide(index) {
        slides.forEach(s => s.classList.remove('active'));
        if (slides[index]) {
            slides[index].classList.add('active');
            document.getElementById('slideCounter').textContent = `Slide ${index + 1} of ${totalSlides}`;
        }
        progress = 0;
    }

    function nextSlide() {
        if (totalSlides <= 1) return;
        currentSlide = (currentSlide + 1) % totalSlides;
        showSlide(currentSlide);
    }

    function runProgress() {
        if (!isPlaying || totalSlides <= 1) return;
        progress += (intervalTime / duration) * 100;
        document.getElementById('progressFill').style.width = `${progress}%`;
        if (progress >= 100) {
            nextSlide();
        }
    }

    timer = setInterval(runProgress, intervalTime);

    function togglePlay() {
        isPlaying = !isPlaying;
        const btn = document.getElementById('playPauseBtn');
        if (isPlaying) {
            btn.innerHTML = '<i class="fas fa-pause me-1"></i> Pause';
        } else {
            btn.innerHTML = '<i class="fas fa-play me-1"></i> Play';
        }
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowRight' || e.key === ' ') {
            nextSlide();
        } else if (e.key === 'ArrowLeft') {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            showSlide(currentSlide);
        } else if (e.key === 'Escape') {
            window.location.href = '<?= base_url("performance/awards") ?>';
        }
    });
</script>

</body>
</html>
