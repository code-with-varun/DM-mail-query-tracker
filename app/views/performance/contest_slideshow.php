<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Grand Prix Championship — Live Executive Slideshow') ?> | DM-Ispark</title>
    
    <!-- Google Fonts & FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --bg-dark: #0a0e17;
            --card-dark: #111827;
            --border-dark: #1f2937;
            --accent-gold: #f59e0b;
            --accent-blue: #3b82f6;
            --accent-green: #10b981;
            --accent-purple: #8b5cf6;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-dark);
            color: #f8fafc;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            user-select: none;
        }

        /* Top TV Telemetry Header */
        .tv-header {
            background: rgba(17, 24, 39, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 12px 24px;
            position: relative;
            z-index: 50;
        }

        /* Slide Progress Timer Bar */
        .slide-progress-bar {
            height: 4px;
            background: linear-gradient(90deg, var(--accent-gold), var(--accent-green));
            width: 0%;
            transition: width 0.1s linear;
        }

        /* Slide Container & Carousel Transition */
        .slideshow-viewport {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 32px;
            position: relative;
            overflow: hidden;
        }

        .slide-item {
            display: none;
            width: 100%;
            max-width: 1400px;
            opacity: 0;
            transform: scale(0.97) translateY(12px);
            transition: opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1), transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .slide-item.active {
            display: block;
            opacity: 1;
            transform: scale(1) translateY(0);
        }

        /* Glassmorphism Presentation Cards */
        .glass-panel {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        .metric-pill {
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 18px;
            padding: 18px;
            transition: transform 0.25s ease;
        }
        .metric-pill:hover {
            transform: translateY(-3px);
            background: rgba(30, 41, 59, 0.85);
        }

        .driver-card {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 14px;
            transition: all 0.2s ease;
        }
        .driver-card:hover {
            border-color: var(--accent-gold);
            transform: scale(1.02);
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Ambient Glow behind the Slide */
        .ambient-glow {
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            filter: blur(140px);
            opacity: 0.18;
            pointer-events: none;
            z-index: 1;
        }
    </style>
</head>
<body>

    <!-- Top TV Telemetry Header -->
    <header class="tv-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-warning text-dark px-3 py-1 rounded-pill fw-black fs-8 d-flex align-items-center gap-1 shadow-sm">
                <i class="fas fa-flag-checkered"></i> GRAND PRIX SLIDESHOW
            </div>
            <div class="d-none d-md-block">
                <h6 class="fw-bold mb-0 text-white">Operations Performance Showcase</h6>
                <span class="text-secondary fs-8">Team-by-Team Dynamic Standing &amp; Velocity Snapshot</span>
            </div>
        </div>

        <!-- Carousel Telemetry Controls -->
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-dark text-warning border border-secondary border-opacity-25 px-3 py-2 font-mono fs-8" id="slideCounterBadge">
                Slide 1 / <?= count($slides) ?>
            </span>

            <button type="button" class="btn btn-outline-secondary btn-sm text-light rounded-pill px-3" onclick="prevSlide()" title="Previous Slide (Arrow Left)">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button type="button" class="btn btn-outline-warning btn-sm text-warning rounded-pill px-3" id="btnPlayPause" onclick="togglePlayPause()" title="Play / Pause Slideshow (Spacebar)">
                <i class="fas fa-pause" id="playPauseIcon"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm text-light rounded-pill px-3" onclick="nextSlide()" title="Next Slide (Arrow Right)">
                <i class="fas fa-chevron-right"></i>
            </button>

            <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-3 ms-2" onclick="toggleFullScreen()" title="Toggle Fullscreen (F)">
                <i class="fas fa-expand me-1"></i> Fullscreen
            </button>
            <a href="<?= base_url('performance/contest') ?>" class="btn btn-danger btn-sm rounded-pill px-3" title="Exit Presentation">
                <i class="fas fa-times me-1"></i> Exit
            </a>
        </div>
    </header>

    <!-- Top Linear Progress Bar for Slide Timer -->
    <div class="w-100 bg-secondary bg-opacity-25" style="height: 4px;">
        <div class="slide-progress-bar" id="slideProgressBar"></div>
    </div>

    <!-- Main Slideshow Viewport -->
    <main class="slideshow-viewport">
        <!-- Dynamic Ambient Light Sphere -->
        <div class="ambient-glow" id="ambientLight" style="background-color: #3b82f6;"></div>

        <?php if (empty($slides)): ?>
            <div class="text-center p-5">
                <i class="fas fa-trophy text-warning display-1 mb-3"></i>
                <h3 class="text-white">No Team Contest Records Available</h3>
                <p class="text-secondary">Please check back once contest logs are registered.</p>
                <a href="<?= base_url('performance/contest') ?>" class="btn btn-primary rounded-pill px-4">Return to Contest Hub</a>
            </div>
        <?php else: ?>
            <?php foreach ($slides as $idx => $s): 
                $rank = $s['rank'];
                $isPole = $rank === 1;
                $isPodium = $rank <= 3;
                $teamColor = $s['team_color'];
            ?>
            <div class="slide-item <?= $idx === 0 ? 'active' : '' ?>" id="slide_<?= $idx ?>" data-color="<?= $teamColor ?>">
                <div class="glass-panel p-4 p-lg-5 position-relative z-2">
                    
                    <!-- Slide Header Row -->
                    <div class="row align-items-center mb-4 pb-3 border-bottom border-secondary border-opacity-25">
                        <div class="col-lg-8 d-flex align-items-center gap-4">
                            <!-- Massive Position Badge -->
                            <div class="rounded-4 d-flex align-items-center justify-content-center text-white fw-black font-mono shadow-lg flex-shrink-0"
                                 style="width: 90px; height: 90px; font-size: 38px; background: <?= $s['team_gradient'] ?>; border: 2px solid rgba(255,255,255,0.2);">
                                P<?= $rank ?>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                    <span class="badge rounded-pill px-3 py-1 fs-8 fw-black text-uppercase" 
                                          style="background-color: <?= $teamColor ?>; color: #fff;">
                                        <?= htmlspecialchars($s['podium_badge']) ?>
                                    </span>
                                    <span class="badge bg-secondary bg-opacity-40 text-light border border-secondary border-opacity-25 px-3 py-1 fs-8">
                                        <?= htmlspecialchars($s['operation_type']) ?>
                                    </span>
                                    <?php if ($isPole): ?>
                                        <span class="badge bg-warning text-dark fw-black px-3 py-1 fs-8">
                                            👑 CHAMPIONSHIP LEADER
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <h1 class="display-5 fw-black text-white mb-0 text-truncate" title="<?= htmlspecialchars($s['team_name']) ?>">
                                    <?= htmlspecialchars($s['team_name']) ?>
                                </h1>
                                <span class="text-secondary fs-7">
                                    Client / Project: <strong class="text-light"><?= htmlspecialchars($s['client_name']) ?></strong> &bull; Total Contenders: <?= $s['total_members'] ?>
                                </span>
                            </div>
                        </div>

                        <!-- Score Tachometer Badge -->
                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <div class="d-inline-flex flex-column align-items-lg-end">
                                <span class="fs-8 fw-bold text-uppercase text-secondary">Championship Score</span>
                                <div class="d-flex align-items-baseline gap-2">
                                    <span class="display-3 fw-black font-mono text-warning"><?= number_format($s['total_score'], 1) ?></span>
                                    <span class="fs-6 text-secondary">/ 100</span>
                                </div>
                                <span class="badge bg-dark text-secondary border border-secondary border-opacity-25 px-3 py-1 rounded-pill fs-8">
                                    Gap: <?= htmlspecialchars($s['gap_to_leader']) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 4 Primary Metric Showcase Cards -->
                    <div class="row g-3 mb-4">
                        <!-- Volume & Target -->
                        <div class="col-6 col-lg-3">
                            <div class="metric-pill">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fs-8 text-secondary text-uppercase fw-bold">Output Volume</span>
                                    <i class="fas fa-boxes text-primary fs-6"></i>
                                </div>
                                <h3 class="fw-black text-white font-mono mb-1"><?= number_format($s['total_volume']) ?></h3>
                                <div class="progress rounded-pill bg-dark mb-1" style="height: 6px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= min(100, $s['completion_pct']) ?>%;"></div>
                                </div>
                                <span class="fs-9 text-secondary"><?= $s['completion_pct'] ?>% of <?= number_format($s['target_volume']) ?> Target</span>
                            </div>
                        </div>

                        <!-- Quality Accuracy -->
                        <div class="col-6 col-lg-3">
                            <div class="metric-pill">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fs-8 text-secondary text-uppercase fw-bold">Quality Rate</span>
                                    <i class="fas fa-shield-alt text-success fs-6"></i>
                                </div>
                                <h3 class="fw-black text-success font-mono mb-1"><?= number_format($s['accuracy_pct'], 1) ?>%</h3>
                                <div class="progress rounded-pill bg-dark mb-1" style="height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $s['accuracy_pct'] ?>%;"></div>
                                </div>
                                <span class="fs-9 text-secondary">QC &amp; Audit Precision Rate</span>
                            </div>
                        </div>

                        <!-- Productivity Velocity -->
                        <div class="col-6 col-lg-3">
                            <div class="metric-pill">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fs-8 text-secondary text-uppercase fw-bold">Speed Velocity</span>
                                    <i class="fas fa-tachometer-alt text-warning fs-6"></i>
                                </div>
                                <h3 class="fw-black text-warning font-mono mb-1"><?= $s['speed_index'] ?></h3>
                                <span class="fs-8 text-light fw-bold d-block">units / contributor hr</span>
                                <span class="fs-9 text-secondary">High operational throughput</span>
                            </div>
                        </div>

                        <!-- Active Drivers -->
                        <div class="col-6 col-lg-3">
                            <div class="metric-pill">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fs-8 text-secondary text-uppercase fw-bold">Active Racers</span>
                                    <i class="fas fa-users text-info fs-6"></i>
                                </div>
                                <h3 class="fw-black text-info font-mono mb-1"><?= $s['active_racers'] ?> / <?= $s['total_members'] ?></h3>
                                <span class="fs-8 text-light fw-bold d-block">Contributors Logged</span>
                                <span class="fs-9 text-secondary">Adherence: <?= round(($s['active_racers'] / max(1, $s['total_members'])) * 100) ?>%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Details: MVP Drivers & Accolades -->
                    <div class="row g-4 align-items-stretch">
                        <!-- Left: Top 3 MVP Drivers -->
                        <div class="col-lg-7">
                            <h6 class="fs-8 text-uppercase fw-black text-secondary mb-3">
                                <i class="fas fa-user-astronaut text-warning me-1"></i> Team Drivers &amp; MVP Contributors
                            </h6>
                            <div class="row g-2">
                                <?php if (!empty($s['top_drivers'])): ?>
                                    <?php foreach ($s['top_drivers'] as $d): ?>
                                    <div class="col-12 col-md-4">
                                        <div class="driver-card h-100">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <span class="fs-6"><?= $d['podium'] ?></span>
                                                <span class="badge bg-warning bg-opacity-20 text-warning border border-warning-subtle fs-9 px-2 py-0">
                                                    <?= htmlspecialchars($d['badge']) ?>
                                                </span>
                                            </div>
                                            <strong class="text-white fs-8 d-block text-truncate mb-1" title="<?= htmlspecialchars($d['full_name']) ?>">
                                                <?= htmlspecialchars($d['full_name']) ?>
                                            </strong>
                                            <div class="d-flex justify-content-between align-items-center fs-9 text-secondary">
                                                <span>Score: <strong class="text-warning"><?= $d['score'] ?></strong></span>
                                                <span>Vol: <?= $d['volume'] ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12 text-secondary fs-8 p-3">Roster contributors logged in active sprint.</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Right: Accolades & Highlights Badges -->
                        <div class="col-lg-5">
                            <h6 class="fs-8 text-uppercase fw-black text-secondary mb-3">
                                <i class="fas fa-award text-success me-1"></i> Telemetry Highlights &amp; Badges
                            </h6>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($s['highlights'] as $hl): ?>
                                <span class="badge bg-dark text-light border border-secondary border-opacity-25 px-3 py-2 rounded-pill fs-8 d-flex align-items-center gap-2">
                                    <i class="<?= $hl['icon'] ?> text-<?= $hl['color'] ?>"></i>
                                    <span><?= htmlspecialchars($hl['label']) ?></span>
                                </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <!-- Slideshow Control Engine -->
    <script>
        var currentSlideIndex = 0;
        var totalSlides = <?= count($slides) ?>;
        var slideDuration = 8000; // 8 seconds per team slide
        var progressInterval = 50; // update progress every 50ms
        var timerProgress = 0;
        var isPaused = false;
        var slideTimer = null;

        document.addEventListener("DOMContentLoaded", function() {
            if (totalSlides > 0) {
                updateSlideView(0);
                startSlideTimer();
            }
        });

        function updateSlideView(index) {
            var items = document.querySelectorAll(".slide-item");
            if (items.length === 0) return;

            items.forEach(function(el) {
                el.classList.remove("active");
            });

            currentSlideIndex = (index + totalSlides) % totalSlides;
            var target = document.getElementById("slide_" + currentSlideIndex);
            if (target) {
                target.classList.add("active");
                var color = target.getAttribute("data-color") || "#3b82f6";
                var glow = document.getElementById("ambientLight");
                if (glow) glow.style.backgroundColor = color;
            }

            var counter = document.getElementById("slideCounterBadge");
            if (counter) {
                counter.textContent = "Slide " + (currentSlideIndex + 1) + " / " + totalSlides;
            }

            timerProgress = 0;
            updateProgressBar();
        }

        function startSlideTimer() {
            if (slideTimer) clearInterval(slideTimer);
            slideTimer = setInterval(function() {
                if (!isPaused) {
                    timerProgress += progressInterval;
                    updateProgressBar();
                    if (timerProgress >= slideDuration) {
                        nextSlide();
                    }
                }
            }, progressInterval);
        }

        function updateProgressBar() {
            var bar = document.getElementById("slideProgressBar");
            if (bar) {
                var pct = (timerProgress / slideDuration) * 100;
                bar.style.width = Math.min(100, pct) + "%";
            }
        }

        function nextSlide() {
            updateSlideView(currentSlideIndex + 1);
        }

        function prevSlide() {
            updateSlideView(currentSlideIndex - 1);
        }

        function togglePlayPause() {
            isPaused = !isPaused;
            var icon = document.getElementById("playPauseIcon");
            if (icon) {
                if (isPaused) {
                    icon.className = "fas fa-play";
                } else {
                    icon.className = "fas fa-pause";
                }
            }
        }

        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(function(e) {
                    console.warn("Fullscreen request error:", e);
                });
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }

        // Keyboard Controls
        document.addEventListener("keydown", function(e) {
            if (e.key === "ArrowRight") {
                nextSlide();
            } else if (e.key === "ArrowLeft") {
                prevSlide();
            } else if (e.key === " " || e.code === "Space") {
                e.preventDefault();
                togglePlayPause();
            } else if (e.key === "f" || e.key === "F") {
                toggleFullScreen();
            }
        });
    </script>
</body>
</html>
