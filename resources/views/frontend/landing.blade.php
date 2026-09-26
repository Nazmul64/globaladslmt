<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $landingSettings->hero_subtitle ?? 'Global Money Ltd - Secure Micro-earning, P2P USDT Trading & Digital Finance Platform. Download the official Android app now.' }}">
    <title>{{ $landingSettings->app_name ?? 'Global Money Ltd' }} | Official Platform & Mobile App</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary: #ff5436;
            --primary-dark: #e03e22;
            --primary-light: #ff7d66;
            --secondary: #7c3aed;
            --secondary-dark: #6d28d9;
            --accent: #00c853;
            --play-green: #01875f;
            --play-green-hover: #016e4d;
            --dark: #0f172a;
            --dark-surface: #1e293b;
            --dark-card: #182234;
            --light-bg: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-400: #94a3b8;
            --gray-600: #475569;
            --gray-800: #1e293b;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --radius-sm: 8px;
            --radius-md: 16px;
            --radius-lg: 24px;
            --radius-full: 9999px;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.04);
            --shadow-md: 0 10px 30px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 20px 50px rgba(0, 0, 0, 0.12);
            --shadow-glow: 0 0 40px rgba(255, 84, 54, 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--light-bg);
            color: var(--text-main);
            line-height: 1.6;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: all 0.25s ease;
        }

        img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .container {
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* ================= Header & Navbar ================= */
        .header {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--gray-200);
            transition: all 0.3s ease;
        }

        .nav-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 80px;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            font-size: 22px;
            color: var(--dark);
        }

        .brand-logo img {
            height: 46px;
            width: auto;
            border-radius: 10px;
            object-fit: contain;
        }

        .brand-logo span {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-family: 'Outfit', sans-serif;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            list-style: none;
            gap: 32px;
        }

        .nav-link {
            font-size: 15px;
            font-weight: 600;
            color: var(--gray-600);
            padding: 8px 0;
            position: relative;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--primary);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 22px;
            border-radius: var(--radius-full);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 14px rgba(255, 84, 54, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 84, 54, 0.45);
        }

        .btn-play {
            background: var(--play-green);
            color: white;
            padding: 13px 28px;
            font-size: 15px;
            font-weight: 700;
            box-shadow: 0 6px 20px rgba(1, 135, 95, 0.35);
        }

        .btn-play:hover {
            background: var(--play-green-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(1, 135, 95, 0.45);
        }

        .mobile-toggle {
            display: none;
            font-size: 22px;
            background: none;
            border: none;
            color: var(--dark);
            cursor: pointer;
        }

        /* ================= Hero Section ================= */
        .hero {
            position: relative;
            padding: 80px 0 60px;
            overflow: hidden;
            background: radial-gradient(circle at 80% 20%, rgba(255, 84, 54, 0.08) 0%, transparent 50%),
                        radial-gradient(circle at 10% 80%, rgba(124, 58, 237, 0.08) 0%, transparent 50%);
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 60px;
            align-items: center;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 84, 54, 0.1);
            color: var(--primary);
            padding: 7px 16px;
            border-radius: var(--radius-full);
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 20px;
        }

        .hero-title {
            font-size: 50px;
            line-height: 1.15;
            color: var(--dark);
            margin-bottom: 22px;
        }

        .hero-subtitle {
            font-size: 18px;
            color: var(--gray-600);
            line-height: 1.7;
            margin-bottom: 36px;
            max-width: 580px;
        }

        .hero-ctas {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 40px;
        }

        .hero-stats {
            display: flex;
            gap: 36px;
            padding-top: 30px;
            border-top: 1px solid var(--gray-200);
        }

        .stat-item h4 {
            font-size: 28px;
            color: var(--dark);
            margin-bottom: 4px;
        }

        .stat-item p {
            font-size: 13px;
            color: var(--gray-600);
            font-weight: 500;
        }

        /* ================= Google Play Card Showcase ================= */
        .playstore-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 36px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
            position: relative;
            transition: all 0.3s ease;
        }

        .playstore-card:hover {
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15);
        }

        .play-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 24px;
        }

        .app-icon {
            width: 88px;
            height: 88px;
            border-radius: 20px;
            box-shadow: var(--shadow-md);
            object-fit: cover;
            background: #000;
            flex-shrink: 0;
        }

        .app-info h3 {
            font-size: 24px;
            color: var(--dark);
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .app-developer {
            color: var(--play-green);
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .app-meta {
            font-size: 12px;
            color: var(--gray-600);
        }

        .play-metrics {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            padding: 18px 0;
            border-top: 1px solid var(--gray-100);
            border-bottom: 1px solid var(--gray-100);
            margin-bottom: 24px;
            text-align: center;
        }

        .metric-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .metric-val {
            font-size: 18px;
            font-weight: 800;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .metric-val i {
            color: #f59e0b;
            font-size: 14px;
        }

        .metric-lbl {
            font-size: 12px;
            color: var(--gray-600);
            margin-top: 2px;
        }

        .play-card-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .device-compat {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--gray-600);
            justify-content: center;
            margin-top: 6px;
        }

        /* ================= App Showcase Gallery ================= */
        .section {
            padding: 90px 0;
            position: relative;
        }

        .section-header {
            text-align: center;
            max-width: 680px;
            margin: 0 auto 56px;
        }

        .section-badge {
            display: inline-block;
            background: rgba(124, 58, 237, 0.1);
            color: var(--secondary);
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 6px 14px;
            border-radius: var(--radius-full);
            margin-bottom: 12px;
        }

        .section-title {
            font-size: 38px;
            color: var(--dark);
            margin-bottom: 16px;
        }

        .section-desc {
            font-size: 16px;
            color: var(--gray-600);
        }

        /* ================= Features Grid ================= */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }

        .feature-card {
            background: white;
            padding: 36px 28px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--gray-200);
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
            border-color: rgba(255, 84, 54, 0.3);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 22px;
        }

        .feature-icon.primary {
            background: rgba(255, 84, 54, 0.1);
            color: var(--primary);
        }

        .feature-icon.secondary {
            background: rgba(124, 58, 237, 0.1);
            color: var(--secondary);
        }

        .feature-icon.green {
            background: rgba(0, 200, 83, 0.1);
            color: var(--accent);
        }

        .feature-card h3 {
            font-size: 20px;
            margin-bottom: 12px;
            color: var(--dark);
        }

        .feature-card p {
            color: var(--gray-600);
            font-size: 15px;
            line-height: 1.6;
        }

        /* ================= Packages Section ================= */
        .packages-section {
            background: white;
        }

        .packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 28px;
        }

        .package-card {
            background: var(--light-bg);
            border-radius: var(--radius-lg);
            padding: 32px 24px;
            border: 2px solid var(--gray-200);
            text-align: center;
            position: relative;
            transition: all 0.3s ease;
        }

        .package-card.featured {
            border-color: var(--primary);
            background: white;
            box-shadow: var(--shadow-md);
            transform: scale(1.02);
        }

        .package-card:hover {
            transform: translateY(-6px);
            border-color: var(--primary);
        }

        .package-name {
            font-size: 22px;
            margin-bottom: 8px;
            color: var(--dark);
        }

        .package-price {
            font-size: 38px;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 18px;
        }

        .package-price span {
            font-size: 16px;
            font-weight: 500;
            color: var(--gray-600);
        }

        .package-features {
            list-style: none;
            margin: 20px 0 28px;
            text-align: left;
        }

        .package-features li {
            padding: 8px 0;
            color: var(--gray-600);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .package-features li i {
            color: var(--accent);
            font-size: 15px;
        }

        /* ================= CTA Banner ================= */
        .cta-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-radius: var(--radius-lg);
            padding: 60px 48px;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .cta-banner::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 380px;
            height: 380px;
            background: radial-gradient(circle, rgba(255, 84, 54, 0.25) 0%, transparent 70%);
            border-radius: 50%;
        }

        .cta-content {
            position: relative;
            z-index: 2;
            max-width: 620px;
        }

        .cta-content h2 {
            font-size: 38px;
            margin-bottom: 16px;
            line-height: 1.2;
        }

        .cta-content p {
            font-size: 17px;
            color: var(--gray-400);
            margin-bottom: 30px;
        }

        /* ================= Footer ================= */
        .footer {
            background: var(--dark);
            color: var(--gray-400);
            padding: 70px 0 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr 1.2fr;
            gap: 40px;
            margin-bottom: 50px;
        }

        .footer-brand h3 {
            font-size: 22px;
            color: white;
            margin-bottom: 14px;
        }

        .footer-brand p {
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 20px;
        }

        .footer-col h4 {
            color: white;
            font-size: 17px;
            margin-bottom: 18px;
        }

        .footer-links {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .footer-links a {
            font-size: 14px;
            color: var(--gray-400);
        }

        .footer-links a:hover {
            color: var(--primary);
        }

        .footer-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            font-size: 13px;
        }

        /* ================= Responsive ================= */
        @media (max-width: 1024px) {
            .hero-grid { grid-template-columns: 1fr; gap: 40px; }
            .hero-title { font-size: 42px; }
            .features-grid { grid-template-columns: repeat(2, 1fr); }
            .footer-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .nav-menu { display: none; }
            .mobile-toggle { display: block; }
            .hero-title { font-size: 34px; }
            .hero-stats { flex-wrap: wrap; gap: 20px; }
            .features-grid { grid-template-columns: 1fr; }
            .cta-banner { padding: 40px 24px; }
            .cta-content h2 { font-size: 28px; }
            .footer-grid { grid-template-columns: 1fr; }
            .footer-bottom { flex-direction: column; gap: 12px; text-align: center; }
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <header class="header">
        <div class="container">
            <div class="nav-wrapper">
                <a href="{{ route('home') }}" class="brand-logo">
                    @if($settingLogo && !empty($settingLogo->photo))
                        <img src="{{ filter_var($settingLogo->photo, FILTER_VALIDATE_URL) ? $settingLogo->photo : asset('uploads/logo/' . $settingLogo->photo) }}" alt="{{ $landingSettings->app_name ?? 'Global Money' }} Logo">
                    @else
                        <img src="https://cdn-icons-png.flaticon.com/512/847/847969.png" alt="{{ $landingSettings->app_name ?? 'Global Money' }} Logo">
                    @endif
                    <span>{{ $landingSettings->app_name ?? 'Global Money Ltd' }}</span>
                </a>

                <ul class="nav-menu">
                    <li><a href="#overview" class="nav-link">Overview</a></li>
                    <li><a href="#playstore" class="nav-link">Download App</a></li>
                    <li><a href="#features" class="nav-link">Features</a></li>
                    <li><a href="#packages" class="nav-link">Packages</a></li>
                    <li><a href="#support" class="nav-link">Support</a></li>
                </ul>

                <div class="nav-actions">
                    <a href="{{ $playStoreUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-play">
                        <i class="fa-brands fa-google-play"></i> Get App
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="overview">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-text">
                    <div class="hero-badge">
                        <i class="fa-solid fa-shield-halved"></i> {{ $landingSettings->hero_badge ?? 'Verified & Secure Platform' }}
                    </div>
                    <h1 class="hero-title">
                        {{ $landingSettings->hero_title ?? 'Next-Gen Micro-Earning & P2P Trading Ecosystem' }}
                    </h1>
                    <p class="hero-subtitle">
                        {{ $landingSettings->hero_subtitle ?? 'Welcome to Global Money Ltd. Earn daily rewards by completing engaging micro-tasks, trade USDT effortlessly with verified agents, and manage deposits & withdrawals with enterprise-level security.' }}
                    </p>

                    <div class="hero-ctas">
                        <a href="{{ $playStoreUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-play">
                            <i class="fa-brands fa-google-play fa-lg"></i> Download on Google Play
                        </a>
                    </div>

                    <div class="hero-stats">
                        <div class="stat-item">
                            <h4>{{ $landingSettings->stat_1_value ?? '100%' }}</h4>
                            <p>{{ $landingSettings->stat_1_label ?? 'Secure Transactions' }}</p>
                        </div>
                        <div class="stat-item">
                            <h4>{{ $landingSettings->stat_2_value ?? '24/7' }}</h4>
                            <p>{{ $landingSettings->stat_2_label ?? 'Live Agent Support' }}</p>
                        </div>
                        <div class="stat-item">
                            <h4>{{ $landingSettings->stat_3_value ?? '4.8 ★' }}</h4>
                            <p>{{ $landingSettings->stat_3_label ?? 'User Rating' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Google Play Showcase Card -->
                <div class="hero-card-col" id="playstore">
                    <div class="playstore-card">
                        <div class="play-header">
                            @if($settingLogo && !empty($settingLogo->photo))
                                <img src="{{ filter_var($settingLogo->photo, FILTER_VALIDATE_URL) ? $settingLogo->photo : asset('uploads/logo/' . $settingLogo->photo) }}" alt="{{ $landingSettings->app_name ?? 'Globalmoney ltd' }}" class="app-icon">
                            @else
                                <img src="https://cdn-icons-png.flaticon.com/512/847/847969.png" alt="{{ $landingSettings->app_name ?? 'Globalmoney ltd' }}" class="app-icon">
                            @endif

                            <div class="app-info">
                                <h3>{{ $landingSettings->app_name ?? 'Globalmoney ltd' }} <i class="fa-solid fa-circle-check" style="color: #00c853; font-size: 16px;"></i></h3>
                                <div class="app-developer">{{ $landingSettings->app_publisher ?? 'BD IT POINT' }}</div>
                                <div class="app-meta">{{ $landingSettings->app_meta ?? 'Contains ads · In-app purchases' }}</div>
                            </div>
                        </div>

                        <div class="play-metrics">
                            <div class="metric-item">
                                <div class="metric-val">{{ $landingSettings->rating_score ?? '4.8' }} <i class="fa-solid fa-star"></i></div>
                                <div class="metric-lbl">{{ $landingSettings->rating_count ?? '1K+ reviews' }}</div>
                            </div>
                            <div class="metric-item">
                                <div class="metric-val">{{ $landingSettings->downloads_count ?? '100+' }}</div>
                                <div class="metric-lbl">Downloads</div>
                            </div>
                            <div class="metric-item">
                                <div class="metric-val"><i class="fa-solid fa-certificate" style="color: #00c853;"></i> {{ str_replace('Rated for ', '', $landingSettings->content_rating ?? '3+') }}</div>
                                <div class="metric-lbl">{{ $landingSettings->content_rating ?? 'Rated for 3+' }}</div>
                            </div>
                        </div>

                        <div class="play-card-actions">
                            <a href="{{ $playStoreUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-play" style="width: 100%; text-align: center;">
                                <i class="fa-brands fa-google-play"></i> Install on Google Play
                            </a>
                            <div class="device-compat">
                                <i class="fa-solid fa-mobile-screen"></i> {{ $landingSettings->device_compatibility ?? 'This app is available for your Android devices' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- App Features Showcase Section -->
    <section class="section" id="features">
        <div class="container">
            <div class="section-header">
                <div class="section-badge">{{ $landingSettings->features_badge ?? 'Platform Highlights' }}</div>
                <h2 class="section-title">{{ $landingSettings->features_title ?? 'Designed for Ease, Built for Security' }}</h2>
                <p class="section-desc">{{ $landingSettings->features_subtitle ?? 'Experience cutting-edge finance and task management engineered with modern transparency and unmatched speed.' }}</p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon primary">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                    <h3>{{ $landingSettings->feat_1_title ?? 'P2P USDT Trading' }}</h3>
                    <p>{{ $landingSettings->feat_1_desc ?? 'Direct peer-to-peer cryptocurrency buying and selling with authorized agents with automated escrow protection.' }}</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon secondary">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <h3>{{ $landingSettings->feat_2_title ?? 'Daily Micro Earning' }}</h3>
                    <p>{{ $landingSettings->feat_2_desc ?? 'Earn steady income every single day by viewing promotional advertisements and completing verified daily challenges.' }}</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon green">
                        <i class="fa-solid fa-shield-check"></i>
                    </div>
                    <h3>{{ $landingSettings->feat_3_title ?? 'KYC & Verified Badges' }}</h3>
                    <p>{{ $landingSettings->feat_3_desc ?? 'Full identity verification ensures all users, agents, and community members in social feed maintain trusted reputations.' }}</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon primary">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                    <h3>{{ $landingSettings->feat_4_title ?? 'Social Feed & Agent Chat' }}</h3>
                    <p>{{ $landingSettings->feat_4_desc ?? 'Share posts, connect with fellow members, and receive 1-on-1 instant support from registered financial agents.' }}</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon secondary">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <h3>{{ $landingSettings->feat_5_title ?? 'Instant Withdrawals' }}</h3>
                    <p>{{ $landingSettings->feat_5_desc ?? 'Multiple mobile payment methods (bKash, Nagad, Rocket, USDT) with fast manual and automated verification.' }}</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon green">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <h3>{{ $landingSettings->feat_6_title ?? 'Multi-Level Referrals' }}</h3>
                    <p>{{ $landingSettings->feat_6_desc ?? 'Earn generous bonus commissions on every package purchase made by your invited direct and indirect referrals.' }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Packages / Memberships Section -->
    @if(isset($packages) && $packages->count() > 0)
    <section class="section packages-section" id="packages">
        <div class="container">
            <div class="section-header">
                <div class="section-badge">Membership Packages</div>
                <h2 class="section-title">Select Your Earning Plan</h2>
                <p class="section-desc">Choose a plan that fits your goals and maximize your daily task earning potential.</p>
            </div>

            <div class="packages-grid">
                @foreach($packages as $index => $pkg)
                <div class="package-card {{ $index == 1 ? 'featured' : '' }}">
                    <h3 class="package-name">{{ $pkg->package_name }}</h3>
                    <div class="package-price">${{ number_format($pkg->price, 2) }} <span>/ {{ $pkg->validity ?? '365' }} Days</span></div>
                    <ul class="package-features">
                        <li><i class="fa-solid fa-circle-check"></i> Daily Limit: {{ $pkg->daily_limit }} Tasks</li>
                        <li><i class="fa-solid fa-circle-check"></i> Daily Income: ${{ number_format($pkg->daily_income, 2) }}</li>
                        <li><i class="fa-solid fa-circle-check"></i> Task Break Cycle: {{ $pkg->ad_brack ?? 10 }} Ads</li>
                        <li><i class="fa-solid fa-circle-check"></i> 24/7 Agent Support</li>
                        <li><i class="fa-solid fa-circle-check"></i> Instant P2P Trading Access</li>
                    </ul>
                    <a href="{{ $playStoreUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary" style="width: 100%;">
                        <i class="fa-solid fa-cart-shopping"></i> Get Started in App
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Download CTA Banner -->
    <section class="section">
        <div class="container">
            <div class="cta-banner">
                <div class="cta-content">
                    <h2>{{ $landingSettings->cta_title ?? 'Start Earning Today with Global Money Ltd' }}</h2>
                    <p>{{ $landingSettings->cta_subtitle ?? 'Install the official Android application from Google Play to access daily tasks, and live P2P trading portal.' }}</p>
                    <a href="{{ $playStoreUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-play">
                        <i class="fa-brands fa-google-play fa-lg"></i> Download on Google Play Store
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="support">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <h3>{{ $landingSettings->app_name ?? 'Global Money Ltd' }}</h3>
                    <p>{{ $landingSettings->footer_about ?? 'Leading digital micro-earning, advertising and P2P financial technology ecosystem empowering users worldwide with trusted earning opportunities.' }}</p>
                    <div style="display: flex; gap: 12px; margin-top: 15px;">
                        <a href="{{ $playStoreUrl }}" target="_blank" style="color: white; font-size: 18px;"><i class="fa-brands fa-google-play"></i></a>
                    </div>
                </div>

                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="#overview">Overview</a></li>
                        <li><a href="#playstore">Download App</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#packages">Packages</a></li>
                        <li><a href="{{ route('privacy.terms') }}">Privacy Policy</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Policies & Compliance</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('privacy.terms') }}">Privacy Policy</a></li>
                        <li><a href="{{ route('child.safety.policy') }}">Child Safety Standards</a></li>
                        <li><a href="{{ route('app.approval') }}">App Ads Verification</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Publisher Info</h4>
                    <p style="font-size: 14px; margin-bottom: 8px;"><strong>Publisher:</strong> {{ $landingSettings->app_publisher ?? 'BD IT POINT' }}</p>
                    <p style="font-size: 14px; margin-bottom: 8px;"><strong>App Name:</strong> {{ $landingSettings->app_name ?? 'Globalmoney ltd' }}</p>
                    <p style="font-size: 14px;"><strong>Package:</strong> com.globalmoneyltd.globalmoneyltd</p>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} {{ $landingSettings->app_name ?? 'Global Money Ltd' }} & {{ $landingSettings->app_publisher ?? 'BD IT POINT' }}. All rights reserved.</p>
                <p>{{ $landingSettings->footer_copyright ?? 'Designed for High Performance & User Security' }}</p>
            </div>
        </div>
    </footer>

</body>
</html>
