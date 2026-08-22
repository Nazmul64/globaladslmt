<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>App Approval Status | Global Money Ltd</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --navy:         #0f3460;
            --navy-dark:    #16213e;
            --ink:          #1a1a2e;
            --accent:       #ff6b4a;
            --accent-gradient: linear-gradient(135deg, #ff6b4a 0%, #ff5436 100%);
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

        .pp-hero {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, var(--ink) 0%, var(--navy-dark) 55%, var(--navy) 100%);
            padding: 80px 0 60px;
        }
        .pp-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 15% 55%, rgba(255,107,74,.13) 0%, transparent 50%),
                radial-gradient(circle at 85% 15%, rgba(79,70,229,.16) 0%, transparent 45%);
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
            margin-bottom: 20px;
        }
        .pp-hero__title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.2rem, 5vw, 3.5rem);
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

        .pp-main {
            padding: 60px 0 90px;
        }

        .pp-card {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow:
                0 2px 4px rgba(0,0,0,.04),
                0 12px 30px rgba(15,52,96,.09),
                0 40px 80px rgba(15,52,96,.05);
        }

        .pp-card__head {
            background: linear-gradient(135deg, var(--navy) 0%, var(--ink) 100%);
            padding: 30px 48px;
            position: relative;
            overflow: hidden;
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
            margin: 0;
        }

        .pp-card__body {
            padding: 44px 48px;
        }

        .pp-content {
            font-size: 1rem;
            color: var(--text);
            line-height: 1.95;
            width: 100%;
            white-space: pre-wrap;
        }

        .pp-divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 40px 0;
        }

        .pp-foot {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }
        .pp-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 26px;
            background: var(--accent-gradient);
            color: #fff !important;
            font-size: .9rem;
            font-weight: 600;
            border-radius: 25px;
            text-decoration: none !important;
            box-shadow: 0 6px 20px rgba(255, 107, 74, 0.3);
            transition: all 0.2s;
        }
        .pp-back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 74, 0.4);
        }

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

        .fade-up {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity .5s ease, transform .5s ease;
        }
        .fade-up.visible { opacity: 1; transform: translateY(0); }

        @media (max-width: 576px) {
            .pp-card__head  { padding: 24px 20px; }
            .pp-card__body  { padding: 28px 20px; }
            .pp-foot        { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>



<section class="pp-main">
    <div class="container-fluid px-4 px-lg-5">
        @if($approval)
        <div class="pp-card fade-up">
            <div class="pp-card__head">
                <div class="d-flex align-items-center gap-3">
                    <div class="pp-icon-box">
                        <i class="bi bi-patch-check text-white fs-5"></i>
                    </div>

                </div>
            </div>
            <div class="pp-card__body">
                <div class="pp-content">
                    {!! nl2br(e($approval->approval_text)) !!}
                </div>
            </div>
        </div>
        @else
        <div class="pp-empty fade-up">
            <div class="pp-empty__ico">
                <i class="bi bi-info-circle"></i>
            </div>
            <h4>No Approval Data Found</h4>
            <p>Google Ads Approval configuration is not set by the administrator yet.</p>
            <a href="{{ route('user.login') }}" class="pp-back-btn" style="display:inline-flex;">
                <i class="bi bi-arrow-left"></i>
                Back to Login
            </a>
        </div>
        @endif
    </div>
</section>

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
