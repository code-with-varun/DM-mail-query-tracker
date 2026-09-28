<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Competency - <?= htmlspecialchars($cert['full_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .certificate-container {
            max-width: 900px;
            margin: 40px auto;
            background: #ffffff;
            padding: 50px;
            border: 12px double #0d6efd;
            border-radius: 8px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            font-size: 8rem;
            color: rgba(13, 110, 253, 0.04);
            font-weight: 900;
            user-select: none;
            pointer-events: none;
            white-space: nowrap;
        }
        @media print {
            body { background: white; margin: 0; }
            .certificate-container { margin: 0; border-width: 8px; box-shadow: none; width: 100%; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="text-center mt-3 no-print">
    <button onclick="window.print()" class="btn btn-primary fw-bold px-4 shadow-sm me-2">
        <i class="fas fa-print me-2"></i>Print / Save PDF Certificate
    </button>
    <a href="<?= base_url('training/certifications') ?>" class="btn btn-outline-secondary fw-bold px-4 shadow-sm">
        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
    </a>
</div>

<div class="certificate-container">
    <div class="watermark">DM-ISPARK</div>

    <!-- Top Header Logo & Branding -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <img src="<?= base_url('public/assets/logo/Datamatics-Responsive-Logo.png') ?>" alt="Datamatics" style="height: 50px;">
            <div>
                <h5 class="fw-bold text-dark mb-0">DM-Ispark</h5>
                <p class="text-muted fs-8 mb-0">Business Operations & Performance Management System</p>
            </div>
        </div>
        <div class="text-end">
            <span class="badge bg-primary fs-7 px-3 py-2 fw-bold">OFFICIAL CERTIFICATION</span>
        </div>
    </div>

    <!-- Certificate Title -->
    <div class="text-center my-4">
        <h1 class="display-5 fw-bold text-dark mb-2" style="font-family: Georgia, serif; color: #1a252f;">Certificate of Competency</h1>
        <p class="text-uppercase tracking-wider text-secondary fw-bold fs-7">This is to certify that</p>
        
        <h2 class="display-6 fw-bold text-primary my-3 border-bottom d-inline-block pb-2 px-5" style="border-color: #0d6efd !important;">
            <?= htmlspecialchars($cert['full_name']) ?>
        </h2>
        <p class="text-muted fs-7 mb-0">Employee Code: <strong><?= htmlspecialchars($cert['user_code']) ?></strong> &bull; Department: <strong><?= htmlspecialchars($cert['department']) ?></strong></p>
    </div>

    <!-- Certificate Body Content -->
    <div class="text-center my-4 fs-6 text-dark leading-relaxed">
        <p class="mb-2">Has successfully completed the comprehensive onboarding program, Knowledge Transfer modules,</p>
        <p class="mb-2">passed the <strong>Process Knowledge Test (PKT)</strong> with a score of <strong class="text-success"><?= number_format($cert['pkt_score'], 1) ?>%</strong>,</p>
        <p class="mb-0">and verified zero-error compliance on Maker-Checker practice files for the operational sub-activity:</p>
        
        <div class="bg-light p-3 rounded-3 my-3 d-inline-block border">
            <h5 class="fw-bold text-dark mb-0">
                <i class="fas fa-check-circle text-success me-2"></i><?= htmlspecialchars($cert['activity_name']) ?> &mdash; <?= htmlspecialchars($cert['sub_activity_name']) ?>
            </h5>
        </div>
    </div>

    <!-- Signatures & Verification Stamp -->
    <div class="row align-items-end mt-5 pt-4">
        <div class="col-4 text-center">
            <div class="border-top border-dark pt-2 fw-bold fs-7">Operations Lead</div>
            <span class="text-muted fs-8">DM-Ispark Operations</span>
        </div>

        <div class="col-4 text-center">
            <div class="p-2 border rounded d-inline-block bg-light">
                <i class="fas fa-certificate text-warning fs-1 d-block mb-1"></i>
                <small class="fw-bold text-dark d-block fs-8">VERIFIED PRACTITIONER</small>
            </div>
        </div>

        <div class="col-4 text-center">
            <div class="border-top border-dark pt-2 fw-bold fs-7">Quality & Training Head</div>
            <span class="text-muted fs-8">Performance Management</span>
        </div>
    </div>

    <!-- Footer Verification Info -->
    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top text-muted fs-8">
        <div>
            Certificate ID: <code><?= htmlspecialchars($cert['certificate_code']) ?></code>
        </div>
        <div>
            Issued Date: <strong><?= date('d F Y', strtotime($cert['certified_at'])) ?></strong>
        </div>
    </div>
</div>

</body>
</html>
