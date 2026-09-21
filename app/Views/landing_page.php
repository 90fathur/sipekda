<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPEKDA ASSAMI | Sistem Informasi Pengelolaan Keuangan Daerah Kab. Polewali Mandar</title>
    <meta name="description" content="SIPEKDA ASSAMI - Portal Pengelolaan Keuangan Daerah Kabupaten Polewali Mandar. Terintegrasi SP2D Online Bank Sulselbar dan SIPD-RI Kemendagri.">
    <link rel="icon" type="image/png" href="<?= base_url('assets/Assami.png') ?>">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS Assets -->
    <link rel="stylesheet" href="<?= base_url('assets/lib/bootstrap/dist/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/lib/font-awesome6/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/animate.css') ?>">

    <style>
        :root {
            --bg-dark: #070c18;
            --bg-card: rgba(15, 23, 42, 0.75);
            --bg-card-hover: rgba(30, 41, 59, 0.85);
            --primary: #10b981;
            --primary-glow: rgba(16, 185, 129, 0.35);
            --primary-dark: #059669;
            --accent-cyan: #06b6d4;
            --accent-gold: #f59e0b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-glass: rgba(255, 255, 255, 0.08);
            --border-glass-hover: rgba(16, 185, 129, 0.4);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* Ambient glowing background */
        .ambient-glow {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }
        .glow-1 {
            position: absolute;
            top: -10%;
            left: 15%;
            width: 550px;
            height: 550px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.15) 0%, transparent 70%);
            filter: blur(80px);
            border-radius: 50%;
        }
        .glow-2 {
            position: absolute;
            top: 40%;
            right: -5%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(6, 182, 212, 0.12) 0%, transparent 70%);
            filter: blur(90px);
            border-radius: 50%;
        }
        .glow-3 {
            position: absolute;
            bottom: 5%;
            left: 5%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.08) 0%, transparent 70%);
            filter: blur(80px);
            border-radius: 50%;
        }

        .content-layer {
            position: relative;
            z-index: 1;
        }

        /* Glassmorphism Classes */
        .glass-card {
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border-glass);
            border-radius: 16px;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card:hover {
            border-color: var(--border-glass-hover);
            transform: translateY(-4px);
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.5), 0 0 25px rgba(16, 185, 129, 0.15);
        }

        /* Top Announcement Bar */
        .top-bar {
            background: rgba(16, 185, 129, 0.12);
            border-bottom: 1px solid rgba(16, 185, 129, 0.2);
            padding: 8px 0;
            font-size: 0.85rem;
            color: #6ee7b7;
        }

        /* Navbar */
        .navbar-custom {
            background: rgba(7, 12, 24, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-glass);
            padding: 14px 0;
            position: sticky;
            top: 0;
            z-index: 1030;
            transition: all 0.3s;
        }
        .navbar-brand-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }
        .brand-logos {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .brand-logo-img {
            height: 42px;
            width: auto;
            object-fit: contain;
        }
        .brand-text-title {
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #ffffff;
            margin: 0;
            line-height: 1.2;
        }
        .brand-text-sub {
            font-size: 0.72rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }

        .nav-link-custom {
            color: #cbd5e1 !important;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 8px 16px !important;
            transition: all 0.2s;
            border-radius: 8px;
        }
        .nav-link-custom:hover {
            color: var(--primary) !important;
            background: rgba(255, 255, 255, 0.04);
        }

        .btn-glow-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff !important;
            font-weight: 600;
            padding: 10px 24px;
            border-radius: 50px;
            border: none;
            box-shadow: 0 4px 20px var(--primary-glow);
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .btn-glow-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(16, 185, 129, 0.5);
            color: #ffffff !important;
        }

        .btn-glass-secondary {
            background: rgba(255, 255, 255, 0.06);
            color: #f1f5f9 !important;
            font-weight: 600;
            padding: 10px 24px;
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .btn-glass-secondary:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            color: #ffffff !important;
        }

        /* Hero Section */
        .hero-section {
            padding: 90px 0 70px 0;
            position: relative;
        }
        .badge-live-pulse {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #34d399;
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 0.82rem;
            font-weight: 600;
            margin-bottom: 24px;
        }
        .pulse-dot {
            width: 8px;
            height: 8px;
            background-color: #10b981;
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse-ring 2s infinite cubic-bezier(0.66, 0, 0, 1);
        }
        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .hero-title {
            font-size: 3.2rem;
            font-weight: 800;
            line-height: 1.18;
            margin-bottom: 22px;
            letter-spacing: -0.5px;
        }
        .text-gradient {
            background: linear-gradient(135deg, #ffffff 10%, #6ee7b7 60%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero-desc {
            font-size: 1.12rem;
            color: var(--text-muted);
            margin-bottom: 34px;
            max-width: 580px;
            font-weight: 400;
        }

        /* Hero Mockup Card */
        .mockup-preview-card {
            position: relative;
            padding: 28px;
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.85) 0%, rgba(10, 15, 30, 0.95) 100%);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7), 0 0 40px rgba(16, 185, 129, 0.12);
        }
        .mockup-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 16px;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .mockup-dots {
            display: flex;
            gap: 6px;
        }
        .mockup-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }
        .dot-red { background-color: #ef4444; }
        .dot-yellow { background-color: #f59e0b; }
        .dot-green { background-color: #10b981; }

        .mockup-status-tag {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .mockup-stat-box {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 14px;
        }
        .mockup-stat-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .mockup-stat-val {
            font-size: 1.3rem;
            font-weight: 700;
            color: #ffffff;
        }

        .mockup-timeline {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-top: 20px;
            padding-top: 10px;
        }
        .mockup-timeline::before {
            content: '';
            position: absolute;
            top: 22px;
            left: 20px;
            right: 20px;
            height: 2px;
            background: rgba(255, 255, 255, 0.1);
            z-index: 0;
        }
        .timeline-step {
            position: relative;
            z-index: 1;
            text-align: center;
        }
        .timeline-icon-bubble {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #0f172a;
            border: 2px solid #10b981;
            color: #10b981;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            margin: 0 auto 6px auto;
        }
        .timeline-label {
            font-size: 0.68rem;
            color: var(--text-muted);
            font-weight: 600;
        }

        /* Partners / Ecosystem */
        .partner-section {
            padding: 40px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            background: rgba(10, 15, 28, 0.4);
        }
        .partner-title {
            text-align: center;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--text-muted);
            margin-bottom: 24px;
            font-weight: 600;
        }
        .partner-logos {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 40px;
        }
        .partner-item {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.06);
            padding: 10px 20px;
            border-radius: 12px;
            transition: all 0.3s;
        }
        .partner-item:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }
        .partner-item img {
            height: 38px;
            width: auto;
            object-fit: contain;
        }
        .partner-name {
            font-size: 0.9rem;
            font-weight: 700;
            color: #e2e8f0;
        }
        .partner-sub {
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        /* Stats Grid */
        .stats-section {
            padding: 70px 0;
        }
        .stat-card {
            padding: 30px 24px;
            text-align: center;
            height: 100%;
        }
        .stat-icon {
            font-size: 2.2rem;
            margin-bottom: 16px;
            display: inline-block;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 6px;
            line-height: 1;
        }
        .stat-title {
            font-size: 1rem;
            font-weight: 600;
            color: #e2e8f0;
            margin-bottom: 4px;
        }
        .stat-desc {
            font-size: 0.82rem;
            color: var(--text-muted);
        }

        /* Features Section */
        .section-header {
            text-align: center;
            max-width: 680px;
            margin: 0 auto 50px auto;
        }
        .section-tag {
            color: var(--primary);
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
            display: inline-block;
        }
        .section-heading {
            font-size: 2.4rem;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 16px;
        }
        .section-subheading {
            font-size: 1rem;
            color: var(--text-muted);
        }

        .feature-card {
            padding: 32px 28px;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .feature-icon-wrapper {
            width: 58px;
            height: 58px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 22px;
            transition: all 0.3s;
        }
        .feature-card:hover .feature-icon-wrapper {
            transform: scale(1.1);
        }
        .icon-emerald { background: rgba(16, 185, 129, 0.15); color: #34d399; }
        .icon-blue { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }
        .icon-purple { background: rgba(168, 85, 247, 0.15); color: #c084fc; }
        .icon-gold { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
        .icon-cyan { background: rgba(6, 182, 212, 0.15); color: #22d3ee; }
        .icon-rose { background: rgba(244, 63, 94, 0.15); color: #fb7185; }

        .feature-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 12px;
        }
        .feature-text {
            font-size: 0.92rem;
            color: var(--text-muted);
            line-height: 1.6;
            flex-grow: 1;
        }

        /* Lifecycle / Workflow Step Cards */
        .workflow-step-card {
            position: relative;
            padding: 30px;
            height: 100%;
        }
        .step-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 2rem;
            font-weight: 800;
            color: rgba(255, 255, 255, 0.07);
        }
        .step-indicator {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        .step-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 10px;
        }
        .step-desc {
            font-size: 0.88rem;
            color: var(--text-muted);
        }

        /* Security Assurance Banner */
        .security-banner {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(6, 182, 212, 0.1) 100%);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 20px;
            padding: 40px;
            margin: 60px 0;
        }
        .security-badge-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 16px;
        }
        .security-badge-item i {
            color: #34d399;
            font-size: 1.3rem;
            margin-top: 3px;
        }

        /* FAQ Accordion */
        .faq-item {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--border-glass);
            border-radius: 12px;
            margin-bottom: 14px;
            overflow: hidden;
            transition: all 0.3s;
        }
        .faq-question {
            padding: 20px 24px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            color: #f1f5f9;
            user-select: none;
        }
        .faq-question:hover {
            color: var(--primary);
        }
        .faq-answer {
            padding: 0 24px 20px 24px;
            color: var(--text-muted);
            font-size: 0.94rem;
            line-height: 1.6;
            display: none;
        }
        .faq-item.active .faq-answer {
            display: block;
        }
        .faq-item.active {
            border-color: rgba(16, 185, 129, 0.3);
            background: rgba(15, 23, 42, 0.85);
        }
        .faq-item.active .faq-icon {
            transform: rotate(180deg);
            color: var(--primary);
        }
        .faq-icon {
            transition: transform 0.3s;
        }

        /* Bottom CTA */
        .cta-box {
            background: radial-gradient(circle at 50% 0%, rgba(16, 185, 129, 0.25) 0%, rgba(15, 23, 42, 0.9) 70%);
            border: 1px solid rgba(16, 185, 129, 0.35);
            border-radius: 24px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 30px 60px -20px rgba(0, 0, 0, 0.8), 0 0 50px rgba(16, 185, 129, 0.15);
        }
        .cta-title {
            font-size: 2.4rem;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 16px;
        }
        .cta-subtitle {
            font-size: 1.1rem;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto 32px auto;
        }

        /* Footer */
        .footer-custom {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding: 60px 0 30px 0;
            background: rgba(5, 8, 18, 0.95);
            font-size: 0.9rem;
            color: var(--text-muted);
        }
        .footer-logo {
            height: 48px;
            width: auto;
            margin-bottom: 16px;
        }
        .footer-title {
            color: #ffffff;
            font-weight: 700;
            font-size: 1.05rem;
            margin-bottom: 18px;
        }
        .footer-links {
            list-style: none;
            padding: 0;
        }
        .footer-links li {
            margin-bottom: 10px;
        }
        .footer-links a {
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
        }
        .footer-links a:hover {
            color: var(--primary);
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .hero-title { font-size: 2.4rem; }
            .mockup-preview-card { margin-top: 40px; }
            .navbar-collapse {
                background: rgba(10, 15, 28, 0.95);
                border: 1px solid var(--border-glass);
                border-radius: 12px;
                padding: 20px;
                margin-top: 15px;
            }
        }
    </style>
</head>
<body>

    <!-- Ambient Glowing Blobs -->
    <div class="ambient-glow">
        <div class="glow-1"></div>
        <div class="glow-2"></div>
        <div class="glow-3"></div>
    </div>

    <div class="content-layer">
        <!-- Top Announcement Bar -->
        <div class="top-bar">
            <div class="container text-center">
                <span><i class="fa-solid fa-shield-halved mr-1"></i> <strong>Sistem Resmi</strong> Badan Keuangan dan Aset Daerah (BKAD) Pemerintah Kabupaten Polewali Mandar</span>
            </div>
        </div>

        <!-- Sticky Glass Navbar -->
        <nav class="navbar navbar-expand-lg navbar-custom">
            <div class="container">
                <a class="navbar-brand-wrapper" href="<?= base_url('/') ?>">
                    <div class="brand-logos">
                        <img src="<?= base_url('assets/LogoPolman.png') ?>" alt="Logo Polman" class="brand-logo-img">
                        <img src="<?= base_url('assets/Assami.png') ?>" alt="SIPEKDA ASSAMI" class="brand-logo-img">
                    </div>
                    <div>
                        <h1 class="brand-text-title">SIPEKDA ASSAMI</h1>
                        <p class="brand-text-sub">Kabupaten Polewali Mandar</p>
                    </div>
                </a>

                <button class="navbar-toggler text-white" type="button" data-toggle="collapse" data-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <div class="collapse navbar-collapse" id="navbarContent">
                    <ul class="navbar-nav mx-auto">
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom" href="#beranda">Beranda</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom" href="#fitur">Fitur Unggulan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom" href="#alur-kerja">Alur Kerja</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom" href="#statistik">Statistik</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom" href="#faq">FAQ</a>
                        </li>
                    </ul>

                    <div class="d-flex align-items-center">
                        <?php if (!empty($loginData)): ?>
                            <a href="<?= ($loginData['JENIS_USER'] === 'Admin') ? base_url('user/userhome') : base_url('dashboards/main') ?>" class="btn-glow-primary">
                                <i class="fa-solid fa-gauge-high"></i> Buka Dashboard (<?= esc($loginData['USER_NAME']) ?>)
                            </a>
                        <?php else: ?>
                            <a href="<?= base_url('user/login') ?>" class="btn-glow-primary">
                                <i class="fa-solid fa-arrow-right-to-bracket"></i> Masuk ke Sistem
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="hero-section" id="beranda">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="badge-live-pulse animated fadeInDown">
                            <span class="pulse-dot"></span>
                            Terintegrasi Bank Sulselbar & SIPD-RI Kemendagri
                        </div>
                        <h2 class="hero-title animated fadeInUp">
                            Transformasi Pengelolaan Keuangan Daerah yang <span class="text-gradient">Akurat & Akuntabel</span>
                        </h2>
                        <p class="hero-desc animated fadeInUp" style="animation-delay: 0.1s;">
                            SIPEKDA ASSAMI menghadirkan digitalisasi pengajuan NPD, validasi SPM berjenjang, monitoring pagu anggaran, serta penerbitan SP2D online secara instan dan aman untuk seluruh SKPD di Kabupaten Polewali Mandar.
                        </p>
                        <div class="d-flex flex-wrap gap-3 animated fadeInUp" style="animation-delay: 0.2s; gap: 14px;">
                            <?php if (!empty($loginData)): ?>
                                <a href="<?= ($loginData['JENIS_USER'] === 'Admin') ? base_url('user/userhome') : base_url('dashboards/main') ?>" class="btn-glow-primary">
                                    <i class="fa-solid fa-gauge"></i> Masuk ke Dashboard
                                </a>
                            <?php else: ?>
                                <a href="<?= base_url('user/login') ?>" class="btn-glow-primary">
                                    <i class="fa-solid fa-key"></i> Masuk ke Aplikasi
                                </a>
                            <?php endif; ?>
                            <a href="#alur-kerja" class="btn-glass-secondary">
                                <i class="fa-solid fa-diagram-project"></i> Pelajari Alur Kerja
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-6 animated fadeInRight" style="animation-delay: 0.2s;">
                        <div class="mockup-preview-card">
                            <div class="mockup-header">
                                <div class="mockup-dots">
                                    <span class="mockup-dot dot-red"></span>
                                    <span class="mockup-dot dot-yellow"></span>
                                    <span class="mockup-dot dot-green"></span>
                                </div>
                                <div class="mockup-status-tag">
                                    <i class="fa-solid fa-circle-check mr-1"></i> SP2D Online Active
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="mockup-stat-box">
                                        <div class="mockup-stat-label">Total SKPD Aktif</div>
                                        <div class="mockup-stat-val text-gradient"><?= esc($skpdCount) ?> SKPD</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mockup-stat-box">
                                        <div class="mockup-stat-label">SPM Terverifikasi</div>
                                        <div class="mockup-stat-val text-white"><?= esc($spmCount) ?> Dokumen</div>
                                    </div>
                                </div>
                            </div>

                            <div class="mockup-stat-box" style="margin-top: 6px;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="mockup-stat-label">Integrasi Host-to-Host</span>
                                    <span class="badge badge-success px-2 py-1" style="font-size: 0.7rem; background: #059669;">Terkoneksi</span>
                                </div>
                                <div class="small text-muted d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-building-columns text-warning"></i>
                                    <span>Bank Sulselbar Cabang Polewali & Kemendagri SIPD</span>
                                </div>
                            </div>

                            <div class="mockup-timeline">
                                <div class="timeline-step">
                                    <div class="timeline-icon-bubble"><i class="fa-solid fa-file-lines"></i></div>
                                    <span class="timeline-label">1. OPD NPD</span>
                                </div>
                                <div class="timeline-step">
                                    <div class="timeline-icon-bubble"><i class="fa-solid fa-user-check"></i></div>
                                    <span class="timeline-label">2. Verif 1</span>
                                </div>
                                <div class="timeline-step">
                                    <div class="timeline-icon-bubble"><i class="fa-solid fa-stamp"></i></div>
                                    <span class="timeline-label">3. Verif 2</span>
                                </div>
                                <div class="timeline-step">
                                    <div class="timeline-icon-bubble" style="border-color: #f59e0b; color: #f59e0b;"><i class="fa-solid fa-money-bill-transfer"></i></div>
                                    <span class="timeline-label">4. SP2D Cair</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Partners / Integrated Ecosystem -->
        <section class="partner-section">
            <div class="container">
                <div class="partner-title">Ekosistem Resmi Pengelolaan Keuangan Daerah</div>
                <div class="partner-logos">
                    <div class="partner-item">
                        <img src="<?= base_url('assets/LogoPolman.png') ?>" alt="Pemkab Polman">
                        <div>
                            <div class="partner-name">Pemerintah Kab. Polewali Mandar</div>
                            <div class="partner-sub">Badan Keuangan dan Aset Daerah</div>
                        </div>
                    </div>

                    <div class="partner-item">
                        <img src="<?= base_url('assets/BSSBLogo.png') ?>" alt="Bank Sulselbar">
                        <div>
                            <div class="partner-name">PT Bank Sulselbar</div>
                            <div class="partner-sub">Bank Operasional Kas Daerah (Kasda)</div>
                        </div>
                    </div>

                    <div class="partner-item">
                        <img src="<?= base_url('assets/ojk.png') ?>" alt="Otoritas Jasa Keuangan">
                        <div>
                            <div class="partner-name">Otoritas Jasa Keuangan</div>
                            <div class="partner-sub">Lembaga Pengawas Perbankan</div>
                        </div>
                    </div>

                    <div class="partner-item">
                        <div style="font-size: 1.8rem; color: #38bdf8;"><i class="fa-solid fa-landmark"></i></div>
                        <div>
                            <div class="partner-name">SIPD-RI Kemendagri</div>
                            <div class="partner-sub">Sistem Informasi Pemerintahan Daerah</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Key Metrics Section -->
        <section class="stats-section" id="statistik">
            <div class="container">
                <div class="row g-4">
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="glass-card stat-card">
                            <span class="stat-icon text-success"><i class="fa-solid fa-landmark-dome"></i></span>
                            <div class="stat-number"><?= esc($skpdCount) ?></div>
                            <div class="stat-title">SKPD Terintegrasi</div>
                            <div class="stat-desc">Organisasi Perangkat Daerah aktif terhubung</div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="glass-card stat-card">
                            <span class="stat-icon text-info"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                            <div class="stat-number"><?= esc($spmCount) ?>+</div>
                            <div class="stat-title">SPM Diproses</div>
                            <div class="stat-desc">Surat Perintah Membayar digital tervalidasi</div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="glass-card stat-card">
                            <span class="stat-icon text-warning"><i class="fa-solid fa-receipt"></i></span>
                            <div class="stat-number"><?= esc($npdCount) ?>+</div>
                            <div class="stat-title">NPD Terverifikasi</div>
                            <div class="stat-desc">Nota Pencairan Dana dengan berkas terenkripsi</div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="glass-card stat-card">
                            <span class="stat-icon text-primary"><i class="fa-solid fa-bolt-lightning"></i></span>
                            <div class="stat-number">100%</div>
                            <div class="stat-title">Real-Time SP2D</div>
                            <div class="stat-desc">Pencairan langsung via API Bank Sulselbar</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-5" id="fitur">
            <div class="container">
                <div class="section-header">
                    <span class="section-tag">Keunggulan Sistem</span>
                    <h2 class="section-heading">Fitur Lengkap untuk Pengelolaan Keuangan Modern</h2>
                    <p class="section-subheading">Dirancang untuk memudahkan koordinasi antara bendahara pengeluaran SKPD, verifikator teknis, dan Kuasa BUD BPKAD.</p>
                </div>

                <div class="row g-4">
                    <!-- Feature 1 -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="glass-card feature-card">
                            <div class="feature-icon-wrapper icon-emerald">
                                <i class="fa-solid fa-file-signature"></i>
                            </div>
                            <h3 class="feature-title">Pengajuan NPD Digital</h3>
                            <p class="feature-text">Input rincian belanja sub-kegiatan OPD dengan mudah. Unggah berkas pendukung PDF secara langsung dengan sistem proteksi keamanan bebas celah.</p>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="glass-card feature-card">
                            <div class="feature-icon-wrapper icon-blue">
                                <i class="fa-solid fa-file-circle-check"></i>
                            </div>
                            <h3 class="feature-title">Penerbitan SPM Otomatis</h3>
                            <p class="feature-text">Konversi NPD yang telah disetujui menjadi SPM secara otomatis dengan penomoran unik sistem yang tertata rapi tanpa risiko penomoran ganda.</p>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="glass-card feature-card">
                            <div class="feature-icon-wrapper icon-purple">
                                <i class="fa-solid fa-user-shield"></i>
                            </div>
                            <h3 class="feature-title">Verifikasi 2 Tahap & BPKAD</h3>
                            <p class="feature-text">Alur verifikasi berjenjang oleh Verifikator 1 dan Verifikator 2 sebelum diteruskan untuk persetujuan akhir Kepala Badan Keuangan dan Aset Daerah.</p>
                        </div>
                    </div>

                    <!-- Feature 4 -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="glass-card feature-card">
                            <div class="feature-icon-wrapper icon-gold">
                                <i class="fa-solid fa-building-columns"></i>
                            </div>
                            <h3 class="feature-title">Integrasi SP2D Bank Sulselbar</h3>
                            <p class="feature-text">Koneksi Host-to-Host dengan core banking Bank Sulselbar memungkinkan penerbitan SP2D langsung memicu pemindahbukuan ke rekening tujuan secara real-time.</p>
                        </div>
                    </div>

                    <!-- Feature 5 -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="glass-card feature-card">
                            <div class="feature-icon-wrapper icon-cyan">
                                <i class="fa-solid fa-chart-pie"></i>
                            </div>
                            <h3 class="feature-title">Kendali Pagu Anggaran</h3>
                            <p class="feature-text">Monitoring sisa pagu belanja SKPD secara visual melalui grafik dan tabel dinamis, mencegah pengeluaran melebihi plafon anggaran APBD.</p>
                        </div>
                    </div>

                    <!-- Feature 6 -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="glass-card feature-card">
                            <div class="feature-icon-wrapper icon-rose">
                                <i class="fa-solid fa-network-wired"></i>
                            </div>
                            <h3 class="feature-title">Sinkronisasi SIPD-RI</h3>
                            <p class="feature-text">Pemantauan transaksi belanja dan penerimaan kas daerah yang tersinkronisasi dengan SIPD-RI Kemendagri demi keselarasan data laporan keuangan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Lifecycle / Workflow Section -->
        <section class="py-5" id="alur-kerja" style="background: rgba(10, 16, 32, 0.5);">
            <div class="container">
                <div class="section-header">
                    <span class="section-tag">Alur Kerja Sistem</span>
                    <h2 class="section-heading">4 Langkah Cepat Menuju Pencairan SP2D</h2>
                    <p class="section-subheading">Standar Operasional Prosedur digital yang transparan, terukur, dan memangkas birokrasi manual.</p>
                </div>

                <div class="row g-4">
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="glass-card workflow-step-card">
                            <span class="step-badge">01</span>
                            <div class="step-indicator">1</div>
                            <h4 class="step-title">Pengajuan NPD (OPD)</h4>
                            <p class="step-desc">Bendahara OPD menginput uraian belanja, memilih rekening anggaran, dan mengunggah berkas PDF kelengkapan secara digital.</p>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="glass-card workflow-step-card">
                            <span class="step-badge">02</span>
                            <div class="step-indicator">2</div>
                            <h4 class="step-title">Verifikasi Tahap 1</h4>
                            <p class="step-desc">Verifikator 1 memeriksa keabsahan administrasi berkas pendukung dan kesesuaian nilai belanja secara teliti.</p>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="glass-card workflow-step-card">
                            <span class="step-badge">03</span>
                            <div class="step-indicator">3</div>
                            <h4 class="step-title">Verifikasi Tahap 2</h4>
                            <p class="step-desc">Verifikator 2 melakukan validasi sub-kegiatan, ketersediaan sisa pagu anggaran, serta kelayakan penerbitan SPM.</p>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="glass-card workflow-step-card">
                            <span class="step-badge">04</span>
                            <div class="step-indicator" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">4</div>
                            <h4 class="step-title">Persetujuan & SP2D</h4>
                            <p class="step-desc">Persetujuan final oleh Kuasa BUD BPKAD dan sistem menerbitkan SP2D yang langsung disalurkan via Bank Sulselbar.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Security & Compliance Banner -->
        <section class="container">
            <div class="security-banner">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h3 class="font-bold text-white mb-3" style="font-size: 1.8rem;">
                            <i class="fa-solid fa-lock text-success mr-2"></i> Keamanan Data & Standar Kepatuhan Tinggi
                        </h3>
                        <p class="text-muted mb-4" style="font-size: 0.96rem;">
                            SIPEKDA ASSAMI mengadopsi standar perlindungan siber berlapis untuk melindungi data keuangan dan integritas transaksi pemerintah daerah:
                        </p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="security-badge-item">
                                    <i class="fa-solid fa-shield-check"></i>
                                    <div>
                                        <strong class="text-white">Anti-WebShell & Sandbox Upload</strong>
                                        <div class="small text-muted">Pemblokiran eksekusi script asing pada direktori berkas.</div>
                                    </div>
                                </div>
                                <div class="security-badge-item">
                                    <i class="fa-solid fa-user-lock"></i>
                                    <div>
                                        <strong class="text-white">Role-Based Access Control (RBAC)</strong>
                                        <div class="small text-muted">Akses dibatasi ketat berdasarkan wewenang masing-masing peran.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="security-badge-item">
                                    <i class="fa-solid fa-fingerprint"></i>
                                    <div>
                                        <strong class="text-white">Audit Trail Activity Log</strong>
                                        <div class="small text-muted">Setiap aksi login, edit, verifikasi, dan penolakan tercatat detail.</div>
                                    </div>
                                </div>
                                <div class="security-badge-item">
                                    <i class="fa-solid fa-database"></i>
                                    <div>
                                        <strong class="text-white">Prepared Statements & Anti SQLi</strong>
                                        <div class="small text-muted">Database terlindungi penuh dari injeksi kueri berbahaya.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 text-center d-none d-lg-block">
                        <div style="font-size: 6.5rem; color: rgba(16, 185, 129, 0.4); text-shadow: 0 0 40px rgba(16, 185, 129, 0.4);">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="py-5" id="faq">
            <div class="container">
                <div class="section-header">
                    <span class="section-tag">Pusat Informasi</span>
                    <h2 class="section-heading">Pertanyaan yang Sering Diajukan (FAQ)</h2>
                    <p class="section-subheading">Temukan jawaban cepat seputar penggunaan aplikasi SIPEKDA ASSAMI.</p>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-9">
                        <div class="faq-item active">
                            <div class="faq-question">
                                <span>Bagaimana cara bendahara OPD memperoleh akun login?</span>
                                <i class="fa-solid fa-chevron-down faq-icon"></i>
                            </div>
                            <div class="faq-answer">
                                Akun login untuk masing-masing SKPD/OPD dikelola dan diterbitkan secara resmi oleh Administrator BPKAD Polewali Mandar. Hubungi bidang perbendaharaan BPKAD untuk pembuatan atau aktivasi akun dinas Anda.
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question">
                                <span>Format dan batas ukuran berkas apa saja yang diizinkan untuk lampiran?</span>
                                <i class="fa-solid fa-chevron-down faq-icon"></i>
                            </div>
                            <div class="faq-answer">
                                Sistem menerima berkas lampiran berformat <strong>PDF, JPG, JPEG, dan PNG</strong> dengan ukuran maksimal <strong>15 MB</strong> per berkas. Sistem telah dilengkapi validasi tipe konten asli untuk menjamin keamanan penyimpanan dokumen.
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question">
                                <span>Bagaimana cara memantau apakah SP2D sudah berhasil disalurkan oleh Bank Sulselbar?</span>
                                <i class="fa-solid fa-chevron-down faq-icon"></i>
                            </div>
                            <div class="faq-answer">
                                Setelah SPM disetujui pada menu Persetujuan BPKAD, sistem akan langsung mengirim instruksi ke Bank Sulselbar. Status transaksi beserta Nomor Referensi Bank dan Tanggal SP2D dapat dipantau langsung pada menu <strong>Monitoring SP2D</strong>.
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question">
                                <span>Apakah berkas lampiran dari aplikasi lama tetap bisa diakses?</span>
                                <i class="fa-solid fa-chevron-down faq-icon"></i>
                            </div>
                            <div class="faq-answer">
                                Ya, seluruh berkas lampiran digital lama yang tersimpan pada sistem tetap aman dan terhubung dengan nomor ID Pengajuan bersangkutan, sehingga verifikator dapat meninjaunya kapan saja.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Bottom CTA Box -->
        <section class="py-5">
            <div class="container">
                <div class="cta-box">
                    <h2 class="cta-title">Siap Memulai Pengelolaan Keuangan Digital?</h2>
                    <p class="cta-subtitle">
                        Masuk ke akun Anda sekarang untuk memproses Nota Pencairan Dana, verifikasi SPM, atau memantau realisasi pagu belanja daerah secara real-time.
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="<?= base_url('user/login') ?>" class="btn-glow-primary btn-lg">
                            <i class="fa-solid fa-right-to-bracket"></i> Masuk ke Portal Aplikasi
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Modern Footer -->
        <footer class="footer-custom">
            <div class="container">
                <div class="row">
                    <div class="col-lg-5 mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <img src="<?= base_url('assets/LogoPolman.png') ?>" alt="Logo Polman" style="height: 40px;">
                            <img src="<?= base_url('assets/Assami.png') ?>" alt="Assami" style="height: 40px;">
                            <span class="font-bold text-white h5 m-0 ml-2">SIPEKDA ASSAMI</span>
                        </div>
                        <p style="max-width: 400px;">
                            Sistem Informasi Pengelolaan Keuangan Daerah Kabupaten Polewali Mandar. Mewujudkan tata kelola keuangan yang <strong>A</strong>kuntabel, <strong>S</strong>inergis, <strong>S</strong>istematis, <strong>A</strong>kurat, <strong>M</strong>andiri, dan <strong>I</strong>ntegratif.
                        </p>
                        <div class="small text-muted">
                            <i class="fa-solid fa-location-dot text-danger mr-1"></i> Kompleks Kantor Bupati Polewali Mandar, Jl. Manunggal No. 11, Pekkabata, Polewali Mandar, Sulawesi Barat
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <h5 class="footer-title">Tautan Cepat</h5>
                        <ul class="footer-links">
                            <li><a href="#beranda">Beranda</a></li>
                            <li><a href="#fitur">Fitur Unggulan</a></li>
                            <li><a href="#alur-kerja">Alur Kerja Sistem</a></li>
                            <li><a href="#statistik">Statistik Keuangan</a></li>
                            <li><a href="<?= base_url('user/login') ?>">Masuk ke Akun</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-4 col-md-6 mb-4">
                        <h5 class="footer-title">Mitra & Integrasi</h5>
                        <ul class="footer-links">
                            <li><a href="https://polmankab.go.id" target="_blank"><i class="fa-solid fa-external-link-alt mr-1"></i> Portal Pemkab Polewali Mandar</a></li>
                            <li><a href="https://banksulselbar.co.id" target="_blank"><i class="fa-solid fa-external-link-alt mr-1"></i> PT Bank Sulselbar</a></li>
                            <li><a href="https://sipd.kemendagri.go.id" target="_blank"><i class="fa-solid fa-external-link-alt mr-1"></i> SIPD-RI Kemendagri</a></li>
                            <li><a href="https://ojk.go.id" target="_blank"><i class="fa-solid fa-external-link-alt mr-1"></i> Otoritas Jasa Keuangan (OJK)</a></li>
                        </ul>
                    </div>
                </div>

                <div class="text-center pt-4 mt-4" style="border-top: 1px solid rgba(255, 255, 255, 0.05);">
                    <p class="m-0 small">
                        &copy; <?= date('Y') ?> Badan Keuangan dan Aset Daerah (BKAD) Kabupaten Polewali Mandar. Seluruh hak cipta dilindungi undang-undang.
                    </p>
                </div>
            </div>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="<?= base_url('assets/lib/jquery/dist/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/lib/bootstrap/dist/js/bootstrap.bundle.min.js') ?>"></script>

    <script>
        $(document).ready(function() {
            // Interactive FAQ Accordion
            $('.faq-question').on('click', function() {
                var parent = $(this).closest('.faq-item');
                if (parent.hasClass('active')) {
                    parent.removeClass('active');
                } else {
                    $('.faq-item').removeClass('active');
                    parent.addClass('active');
                }
            });

            // Smooth scrolling for navigation links
            $('a[href^="#"]').on('click', function(e) {
                var target = $(this.getAttribute('href'));
                if (target.length) {
                    e.preventDefault();
                    $('html, body').stop().animate({
                        scrollTop: target.offset().top - 80
                    }, 600);

                    // Close mobile navbar if open
                    $('.navbar-collapse').collapse('hide');
                }
            });
        });
    </script>
</body>
</html>
