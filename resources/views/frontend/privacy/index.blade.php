<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --navy:         #0f3460;
            --navy-dark:    #16213e;
            --ink:          #1a1a2e;
            --accent:       #e94560;
            --indigo:       #4f46e5;
            --indigo-light: #eef2ff;
            --text:         #374151;
            --muted:        #6b7280;
            --border:       #e5e7eb;
            --bg:           #f5f7fa;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        /* ════════════════════════
           HERO
        ════════════════════════ */
        .pp-hero {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, var(--ink) 0%, var(--navy-dark) 55%, var(--navy) 100%);
            padding: 90px 0 70px;
        }
        .pp-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 15% 55%, rgba(233,69,96,.13) 0%, transparent 50%),
                radial-gradient(circle at 85% 15%, rgba(79,70,229,.16) 0%, transparent 45%);
        }
        .pp-hero__blob {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            filter: blur(70px);
        }
        .pp-hero__blob--1 {
            width: 380px; height: 380px;
            top: -100px; right: -100px;
            background: rgba(79,70,229,.12);
        }
        .pp-hero__blob--2 {
            width: 240px; height: 240px;
            bottom: -70px; left: 3%;
            background: rgba(233,69,96,.09);
        }
        .pp-hero__inner {
            position: relative;
            z-index: 1;
            text-align: center;
        }
        .pp-hero__badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.14);
            backdrop-filter: blur(6px);
            color: rgba(255,255,255,.8);
            font-size: .75rem;
            letter-spacing: .1em;
            text-transform: uppercase;
            padding: 6px 18px;
            border-radius: 100px;
            margin-bottom: 22px;
        }
        .pp-hero__title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.4rem, 5vw, 3.8rem);
            font-weight: 700;
            color: #fff;
            line-height: 1.12;
            margin-bottom: 20px;
        }
        .pp-hero__title em {
            font-style: normal;
            color: var(--accent);
        }
        .pp-hero__crumb {
            font-size: .9rem;
            color: rgba(255,255,255,.5);
        }
        .pp-hero__crumb a {
            color: rgba(255,255,255,.75);
            text-decoration: none;
            transition: color .2s;
        }
        .pp-hero__crumb a:hover { color: #fff; }
        .pp-hero__crumb .sep { margin: 0 10px; opacity: .35; }

        /* ════════════════════════
           MAIN SECTION
        ════════════════════════ */
        .pp-main {
            padding: 60px 0 90px;
        }

        /* ════════════════════════
           CARD — full width
        ════════════════════════ */
        .pp-card {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow:
                0 2px 4px rgba(0,0,0,.04),
                0 12px 30px rgba(15,52,96,.09),
                0 40px 80px rgba(15,52,96,.05);
        }

        /* Card Header */
        .pp-card__head {
            background: linear-gradient(135deg, var(--navy) 0%, var(--ink) 100%);
            padding: 30px 48px;
            position: relative;
            overflow: hidden;
        }
        .pp-card__head::after {
            content: '';
            position: absolute;
            top: -50px; right: -50px;
            width: 200px; height: 200px;
            border-radius: 50%;
            background: rgba(255,255,255,.04);
        }
        .pp-card__head::before {
            content: '';
            position: absolute;
            bottom: -30px; left: 30%;
            width: 120px; height: 120px;
            border-radius: 50%;
            background: rgba(233,69,96,.07);
        }
        .pp-icon-box {
            width: 48px; height: 48px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.16);
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .pp-card__title {
            font-family: 'Playfair Display', serif;
            font-size: 1.35rem;
            font-weight: 600;
            color: #fff;
            margin: 0 0 4px;
        }
        .pp-card__meta {
            font-size: .78rem;
            color: rgba(255,255,255,.48);
        }
        .pp-card__meta i { margin-right: 4px; }

        /* Card Body */
        .pp-card__body {
            padding: 44px 48px;
        }

        /* Notice */
        .pp-notice {
            display: flex;
            gap: 14px;
            align-items: flex-start;
            background: var(--indigo-light);
            border-left: 4px solid var(--indigo);
            border-radius: 10px;
            padding: 18px 22px;
            margin-bottom: 38px;
        }
        .pp-notice__icon {
            flex-shrink: 0;
            margin-top: 2px;
            color: var(--indigo);
            font-size: 1.1rem;
        }
        .pp-notice p {
            font-size: .92rem;
            color: #3730a3;
            line-height: 1.72;
            margin: 0;
        }

        /* Content — 100% full width */
        .pp-content {
            font-size: .97rem;
            color: var(--text);
            line-height: 1.95;
            width: 100%;
        }

        /* Divider */
        .pp-divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 40px 0;
        }

        /* Footer Row */
        .pp-foot {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }
        .pp-foot__date {
            font-size: .84rem;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 0;
        }
        .pp-foot__date strong { color: var(--text); }
        .pp-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            background: linear-gradient(135deg, var(--navy), var(--ink));
            color: #fff !important;
            font-size: .85rem;
            font-weight: 500;
            border-radius: 10px;
            text-decoration: none !important;
            transition: opacity .2s, transform .2s;
        }
        .pp-back-btn:hover { opacity: .87; transform: translateY(-2px); }

        /* ════════════════════════
           EMPTY STATE
        ════════════════════════ */
        .pp-empty {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 40px rgba(0,0,0,.07);
            text-align: center;
            padding: 80px 40px;
        }
        .pp-empty__ico {
            width: 86px; height: 86px;
            background: var(--indigo-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 2.2rem;
            color: var(--indigo);
        }
        .pp-empty h4 {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            color: var(--ink);
            margin-bottom: 10px;
        }
        .pp-empty p {
            color: var(--muted);
            font-size: .93rem;
            max-width: 300px;
            margin: 0 auto 28px;
            line-height: 1.65;
        }

        /* ════════════════════════
           FOOTER
        ════════════════════════ */
        .pp-footer {
            background: var(--ink);
            padding: 26px 0;
            border-top: 1px solid rgba(255,255,255,.06);
        }
        .pp-footer p {
            font-size: .83rem;
            color: rgba(255,255,255,.38);
            margin: 0;
            text-align: center;
        }
        .pp-footer a {
            color: rgba(255,255,255,.55);
            text-decoration: none;
            transition: color .2s;
        }
        .pp-footer a:hover { color: #fff; }

        /* ════════════════════════
           ANIMATION
        ════════════════════════ */
        .fade-up {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity .5s ease, transform .5s ease;
        }
        .fade-up.visible { opacity: 1; transform: translateY(0); }

        /* ════════════════════════
           RESPONSIVE
        ════════════════════════ */
        @media (max-width: 576px) {
            .pp-card__head  { padding: 24px 20px; }
            .pp-card__body  { padding: 28px 20px; }
            .pp-foot        { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>

<!-- ══════════════════════════════
     HERO — NO NAVBAR
══════════════════════════════ -->
<section class="pp-hero">
    <div class="pp-hero__blob pp-hero__blob--1"></div>
    <div class="pp-hero__blob pp-hero__blob--2"></div>

    <div class="container">
        <div class="pp-hero__inner">

            <div class="pp-hero__badge">
                <i class="bi bi-shield-lock-fill"></i>
                Legal Document
            </div>

            <h1 class="pp-hero__title">
                Privacy <em>Policy</em>
            </h1>

            <p class="pp-hero__crumb">
                <a href="{{ route('frontend.index') }}">
                    <i class="bi bi-house-fill me-1"></i>Home
                </a>
                <span class="sep">/</span>
                <span>Privacy Policy</span>
            </p>

        </div>
    </div>
</section>


<!-- ══════════════════════════════
     MAIN — FULL WIDTH CONTENT
══════════════════════════════ -->
<section class="pp-main">
    <div class="container-fluid px-4 px-lg-5">

        @if($privacy_policy)

        <div class="pp-card fade-up">

            {{-- Card Header --}}
            <div class="pp-card__head">
                <div class="d-flex align-items-center gap-3 position-relative" style="z-index:1;">
                    <div class="pp-icon-box">
                        <i class="bi bi-shield-check text-white fs-5"></i>
                    </div>
                    <div>
                        <h2 class="pp-card__title">{{ $privacy_policy->title }}</h2>
                        <span class="pp-card__meta">
                            <i class="bi bi-clock"></i>
                            Last updated: {{ $privacy_policy->updated_at->format('d F, Y') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Card Body --}}
            <div class="pp-card__body">

                {{-- Notice --}}
                <div class="pp-notice">
                    <i class="bi bi-info-circle-fill pp-notice__icon"></i>
                    <p>
                        Please read this Privacy Policy carefully before using our services.
                        By accessing or using our platform, you agree to the terms described below.
                    </p>
                </div>

                {{-- DB Content — full width --}}
                <div class="pp-content">
                    {!! $privacy_policy->description !!}
                </div>

                <hr class="pp-divider">

                {{-- Footer Row --}}
                <div class="pp-foot">
                    <p class="pp-foot__date">
                        <i class="bi bi-calendar3"></i>
                        Effective from:
                        <strong>{{ $privacy_policy->created_at->format('d F, Y') }}</strong>
                    </p>
                    <a href="{{ route('frontend.index') }}" class="pp-back-btn">
                        <i class="bi bi-arrow-left"></i>
                        Back to Home
                    </a>
                </div>

            </div>
        </div>

        @else

        {{-- Empty State --}}
        <div class="pp-empty fade-up">
            <div class="pp-empty__ico">
                <i class="bi bi-shield-exclamation"></i>
            </div>
            <h4>No Privacy Policy Available</h4>
            <p>We are currently updating our privacy policy. Please check back later.</p>
            <a href="{{ route('frontend.index') }}" class="pp-back-btn" style="display:inline-flex;">
                <i class="bi bi-arrow-left"></i>
                Back to Home
            </a>
        </div>

        @endif

    </div>
</section>


<!-- ══════════════════════════════
     FOOTER
══════════════════════════════ -->
<footer class="pp-footer">
    <div class="container">
        <p>
            &copy; {{ date('Y') }} All rights reserved.
            &nbsp;|&nbsp;
            <a href="{{ route('privacy.terms') }}">Privacy Policy</a>
        </p>
    </div>
</footer>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('visible');
                observer.unobserve(e.target);
            }
        });
    }, { threshold: 0.08 });

    document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
</script>

</body>
</html>
