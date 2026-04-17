<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Clockia | Motor de reservas para enoturismo')</title>
    <meta name="description" content="@yield('meta_description', 'Clockia impulsa reservas de enoturismo con calendario widget, chatbot widget, pagos y disponibilidad por experiencia.')">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <style>
        :root {
            --clockia-bg: #ffffff;
            --clockia-surface: #ffffff;
            --clockia-surface-soft: #f2f8f2;
            --clockia-border: #d7e4da;
            --clockia-border-strong: #c2d5c7;
            --clockia-text: #1d2b22;
            --clockia-text-soft: #57685d;
            --clockia-title: #102018;
            --clockia-primary: #0f7a5c;
            --clockia-primary-strong: #0b634a;
            --clockia-accent: #bf3c58;
            --clockia-accent-soft: #f7e5ea;
            --clockia-shadow: 0 18px 40px rgba(16, 32, 24, 0.08);
            --clockia-shell-shadow: 0 24px 48px rgba(16, 32, 24, 0.12);
            --clockia-radius: 8px;
            --clockia-container: 1180px;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            background: var(--clockia-bg);
            color: var(--clockia-text);
            font-family: 'Inter', sans-serif;
            line-height: 1.5;
            letter-spacing: 0;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        img {
            display: block;
            max-width: 100%;
        }

        button {
            font: inherit;
        }

        .container {
            width: min(var(--clockia-container), calc(100% - 2rem));
            margin: 0 auto;
        }

        .site-header {
            position: sticky;
            top: 0;
            z-index: 40;
            border-bottom: 1px solid rgba(255, 255, 255, 0.16);
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(14px);
        }

        .header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            min-height: 76px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--clockia-title);
        }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.2rem;
            height: 2.2rem;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--clockia-primary), #1ba07b);
            color: #ffffff;
            font-size: 0.95rem;
        }

        .site-nav {
            display: flex;
            align-items: center;
            gap: 1.4rem;
            color: var(--clockia-text-soft);
            font-size: 0.95rem;
            font-weight: 500;
        }

        .site-nav a {
            position: relative;
            padding: 0.25rem 0;
        }

        .site-nav a.is-active,
        .site-nav a:hover {
            color: var(--clockia-title);
        }

        .site-nav a.is-active::after {
            content: '';
            position: absolute;
            right: 0;
            bottom: -0.75rem;
            left: 0;
            height: 2px;
            background: var(--clockia-primary);
        }

        .site-actions {
            display: flex;
            align-items: center;
            gap: 0.9rem;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-height: 46px;
            padding: 0.8rem 1.1rem;
            border: 1px solid transparent;
            border-radius: var(--clockia-radius);
            font-size: 0.95rem;
            font-weight: 600;
            white-space: nowrap;
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
        }

        .button:hover {
            transform: translateY(-1px);
        }

        .button-primary {
            background: var(--clockia-primary);
            color: #ffffff;
        }

        .button-primary:hover {
            background: var(--clockia-primary-strong);
        }

        .button-secondary {
            border-color: var(--clockia-border-strong);
            background: rgba(255, 255, 255, 0.92);
            color: var(--clockia-title);
        }

        .button-secondary:hover {
            border-color: var(--clockia-primary);
            color: var(--clockia-primary);
        }

        .button-text {
            color: var(--clockia-text-soft);
            min-height: auto;
            padding: 0;
            border: 0;
            background: transparent;
        }

        .button-text:hover {
            color: var(--clockia-title);
            transform: none;
        }

        .menu-toggle {
            display: none;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            padding: 0;
            border: 1px solid var(--clockia-border);
            border-radius: 8px;
            background: #ffffff;
            color: var(--clockia-title);
        }

        .hero,
        .page-hero {
            position: relative;
            overflow: hidden;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            color: #ffffff;
        }

        .hero::before,
        .page-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(10, 20, 14, 0.52);
        }

        .hero-content,
        .page-hero-content {
            position: relative;
            z-index: 1;
        }

        .hero {
            min-height: 78vh;
            max-height: 860px;
        }

        .hero-content {
            padding: 8.5rem 0 5.5rem;
        }

        .page-hero-content {
            padding: 8rem 0 4.5rem;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            margin-bottom: 1.1rem;
            font-size: 0.88rem;
            font-weight: 700;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.88);
        }

        .eyebrow::before {
            content: '';
            width: 0.55rem;
            height: 0.55rem;
            border-radius: 999px;
            background: #ffffff;
        }

        .hero h1,
        .page-hero h1 {
            max-width: 13ch;
            margin: 0;
            color: #ffffff;
            font-size: 3.8rem;
            line-height: 1.02;
        }

        .hero p,
        .page-hero p {
            max-width: 46rem;
            margin: 1.35rem 0 0;
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.15rem;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.85rem;
            margin-top: 2rem;
        }

        .hero-pills,
        .integration-rail {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .hero-pills {
            margin-top: 2rem;
        }

        .hero-pill,
        .integration-pill,
        .tiny-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            min-height: 38px;
            padding: 0.5rem 0.9rem;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .hero-pill::before,
        .tiny-pill::before,
        .integration-pill::before {
            content: '';
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 999px;
            background: currentColor;
            opacity: 0.7;
        }

        .showcase-overlap {
            position: relative;
            z-index: 2;
            margin-top: -4.5rem;
        }

        .showcase-shell {
            overflow: hidden;
            border: 1px solid var(--clockia-border);
            border-radius: var(--clockia-radius);
            background: #ffffff;
            box-shadow: var(--clockia-shell-shadow);
        }

        .showcase-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.2rem;
            border-bottom: 1px solid var(--clockia-border);
            background: #fbfdfb;
        }

        .showcase-dots {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
        }

        .showcase-dots span {
            width: 0.7rem;
            height: 0.7rem;
            border-radius: 999px;
            background: #d6e6db;
        }

        .showcase-heading {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            color: var(--clockia-title);
            font-weight: 700;
        }

        .showcase-label {
            color: var(--clockia-text-soft);
            font-size: 0.9rem;
            font-weight: 600;
        }

        .tiny-pill {
            min-height: 34px;
            border-color: var(--clockia-border);
            background: var(--clockia-surface-soft);
            color: var(--clockia-primary);
        }

        .showcase-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(0, 0.95fr);
            gap: 1.2rem;
            padding: 1.2rem;
        }

        .showcase-panel {
            display: flex;
            flex-direction: column;
            min-height: 100%;
            gap: 1rem;
            padding: 1.2rem;
            border: 1px solid var(--clockia-border);
            border-radius: var(--clockia-radius);
            background: #ffffff;
        }

        .showcase-panel--chat {
            background: var(--clockia-surface-soft);
        }

        .panel-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
        }

        .panel-head h3 {
            margin: 0.35rem 0 0;
            color: var(--clockia-title);
            font-size: 1.1rem;
        }

        .panel-kicker {
            margin: 0;
            color: var(--clockia-text-soft);
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .panel-chip {
            display: inline-flex;
            align-items: center;
            min-height: 32px;
            padding: 0.3rem 0.7rem;
            border-radius: 999px;
            background: var(--clockia-accent-soft);
            color: var(--clockia-accent);
            font-size: 0.8rem;
            font-weight: 700;
            text-align: center;
        }

        .day-strip {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 0.6rem;
        }

        .day-pill {
            padding: 0.65rem 0.45rem;
            border: 1px solid var(--clockia-border);
            border-radius: var(--clockia-radius);
            background: var(--clockia-surface-soft);
            color: var(--clockia-text-soft);
            font-size: 0.82rem;
            font-weight: 600;
            text-align: center;
        }

        .day-pill.is-active {
            border-color: var(--clockia-primary);
            background: rgba(15, 122, 92, 0.1);
            color: var(--clockia-primary);
        }

        .slot-list {
            display: grid;
            gap: 0.8rem;
        }

        .slot-item {
            padding: 0.9rem;
            border: 1px solid var(--clockia-border);
            border-radius: var(--clockia-radius);
            background: #fbfdfb;
        }

        .slot-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 0.45rem;
            color: var(--clockia-title);
            font-size: 0.92rem;
            font-weight: 700;
        }

        .slot-subtitle {
            color: var(--clockia-text-soft);
            font-size: 0.84rem;
        }

        .meter {
            width: 100%;
            height: 0.5rem;
            border-radius: 999px;
            background: #e5efe7;
            overflow: hidden;
        }

        .meter span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--clockia-primary), #24a87f);
        }

        .panel-note {
            margin-top: auto;
            padding-top: 0.35rem;
            color: var(--clockia-text-soft);
            font-size: 0.86rem;
        }

        .chat-thread {
            display: grid;
            gap: 0.8rem;
            margin-top: 0.2rem;
        }

        .chat-message {
            display: flex;
        }

        .chat-message span {
            display: inline-flex;
            max-width: 100%;
            padding: 0.8rem 0.95rem;
            border-radius: 8px;
            font-size: 0.92rem;
        }

        .chat-message.is-assistant {
            justify-content: flex-start;
        }

        .chat-message.is-assistant span {
            background: #ffffff;
            color: var(--clockia-title);
            border: 1px solid var(--clockia-border);
        }

        .chat-message.is-user {
            justify-content: flex-end;
        }

        .chat-message.is-user span {
            background: var(--clockia-primary);
            color: #ffffff;
        }

        .choice-row,
        .cta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
        }

        .choice-chip {
            display: inline-flex;
            align-items: center;
            min-height: 34px;
            padding: 0.45rem 0.75rem;
            border: 1px solid var(--clockia-border);
            border-radius: 999px;
            background: #ffffff;
            color: var(--clockia-title);
            font-size: 0.82rem;
            font-weight: 600;
        }

        .showcase-footer {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.9rem;
            padding: 0 1.2rem 1.2rem;
        }

        .footer-stat {
            padding: 1rem;
            border: 1px solid var(--clockia-border);
            border-radius: var(--clockia-radius);
            background: #fbfdfb;
        }

        .footer-stat span {
            display: block;
            color: var(--clockia-text-soft);
            font-size: 0.82rem;
            font-weight: 600;
        }

        .footer-stat strong {
            display: block;
            margin-top: 0.35rem;
            color: var(--clockia-title);
            font-size: 0.98rem;
            line-height: 1.35;
        }

        .section {
            padding: 5.75rem 0;
        }

        .section-soft {
            background: var(--clockia-surface-soft);
        }

        .section-intro {
            max-width: 46rem;
            margin-bottom: 2.25rem;
        }

        .section-intro.centered {
            margin-right: auto;
            margin-left: auto;
            text-align: center;
        }

        .section-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            color: var(--clockia-primary);
            font-size: 0.85rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .section-kicker::before {
            content: '';
            width: 0.45rem;
            height: 0.45rem;
            border-radius: 999px;
            background: var(--clockia-accent);
        }

        .section-title {
            margin: 0.85rem 0 0;
            color: var(--clockia-title);
            font-size: 2.4rem;
            line-height: 1.08;
        }

        .section-lead {
            margin: 1rem 0 0;
            color: var(--clockia-text-soft);
            font-size: 1.05rem;
        }

        .feature-grid,
        .metric-grid,
        .service-grid,
        .timeline {
            display: grid;
            gap: 1rem;
        }

        .feature-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .metric-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .service-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .timeline {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .feature-card,
        .metric-card,
        .service-card,
        .timeline-step {
            min-height: 100%;
            padding: 1.35rem;
            border: 1px solid var(--clockia-border);
            border-radius: var(--clockia-radius);
            background: #ffffff;
            box-shadow: var(--clockia-shadow);
        }

        .feature-card h3,
        .metric-card h3,
        .service-card h3,
        .timeline-step h3 {
            margin: 0 0 0.7rem;
            color: var(--clockia-title);
            font-size: 1.05rem;
        }

        .feature-card p,
        .metric-card p,
        .service-card p,
        .timeline-step p,
        .split-copy p {
            margin: 0;
            color: var(--clockia-text-soft);
            font-size: 0.96rem;
        }

        .card-bullets,
        .bullet-list {
            display: grid;
            gap: 0.75rem;
            padding: 0;
            margin: 1rem 0 0;
            list-style: none;
        }

        .card-bullets li,
        .bullet-list li {
            display: grid;
            grid-template-columns: 0.65rem 1fr;
            gap: 0.8rem;
            align-items: start;
            color: var(--clockia-text-soft);
        }

        .card-bullets li::before,
        .bullet-list li::before {
            content: '';
            width: 0.65rem;
            height: 0.65rem;
            margin-top: 0.28rem;
            border-radius: 999px;
            background: var(--clockia-primary);
        }

        .split-section {
            display: grid;
            grid-template-columns: minmax(0, 0.95fr) minmax(0, 1.05fr);
            align-items: center;
            gap: 2rem;
        }

        .split-section--reverse {
            grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
        }

        .image-panel {
            overflow: hidden;
            border-radius: var(--clockia-radius);
            border: 1px solid var(--clockia-border);
            box-shadow: var(--clockia-shadow);
            min-height: 420px;
        }

        .image-panel img {
            width: 100%;
            height: 100%;
            min-height: 420px;
            object-fit: cover;
        }

        .metric-card strong {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--clockia-accent);
            font-size: 1.7rem;
            line-height: 1;
        }

        .timeline-step {
            position: relative;
            padding-top: 3.2rem;
        }

        .timeline-step span {
            position: absolute;
            top: 1.1rem;
            left: 1.2rem;
            color: var(--clockia-primary);
            font-size: 0.9rem;
            font-weight: 800;
        }

        .cta-band {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) auto;
            gap: 1.5rem;
            align-items: center;
            padding: 2.1rem;
            border: 1px solid var(--clockia-border);
            border-radius: var(--clockia-radius);
            background: #ffffff;
            box-shadow: var(--clockia-shadow);
        }

        .cta-band h2 {
            margin: 0;
            color: var(--clockia-title);
            font-size: 2rem;
            line-height: 1.08;
        }

        .cta-band p {
            margin: 0.85rem 0 0;
            color: var(--clockia-text-soft);
            font-size: 1rem;
        }

        .site-footer {
            padding: 4rem 0 2.5rem;
            border-top: 1px solid var(--clockia-border);
            background: #ffffff;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) repeat(2, minmax(0, 0.8fr));
            gap: 1.75rem;
        }

        .footer-grid h3 {
            margin: 0 0 0.9rem;
            color: var(--clockia-title);
            font-size: 0.98rem;
        }

        .footer-grid p,
        .footer-grid li {
            color: var(--clockia-text-soft);
            font-size: 0.94rem;
        }

        .footer-links {
            display: grid;
            gap: 0.7rem;
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .footer-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 2.5rem;
            padding-top: 1.3rem;
            border-top: 1px solid var(--clockia-border);
            color: var(--clockia-text-soft);
            font-size: 0.88rem;
        }

        @media (max-width: 1180px) {
            .hero h1,
            .page-hero h1 {
                font-size: 3.3rem;
            }

            .section-title {
                font-size: 2.1rem;
            }
        }

        @media (max-width: 980px) {
            .menu-toggle {
                display: inline-flex;
            }

            .site-nav,
            .site-actions {
                display: none;
            }

            body.public-nav-open .site-nav,
            body.public-nav-open .site-actions {
                display: flex;
            }

            .header-inner {
                position: relative;
                flex-wrap: wrap;
                padding: 0.8rem 0;
            }

            .site-nav,
            .site-actions {
                width: 100%;
            }

            .site-nav {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.9rem;
                padding-top: 0.8rem;
            }

            .site-nav a.is-active::after {
                display: none;
            }

            .site-actions {
                flex-direction: column;
                align-items: stretch;
                padding: 0.35rem 0 0.6rem;
            }

            .hero {
                min-height: auto;
            }

            .hero-content,
            .page-hero-content {
                padding: 7rem 0 4.5rem;
            }

            .showcase-overlap {
                margin-top: -2.75rem;
            }

            .showcase-grid,
            .showcase-footer,
            .feature-grid,
            .metric-grid,
            .service-grid,
            .timeline,
            .split-section,
            .footer-grid,
            .cta-band {
                grid-template-columns: 1fr;
            }

            .cta-band {
                padding: 1.6rem;
            }
        }

        @media (max-width: 720px) {
            .container {
                width: min(var(--clockia-container), calc(100% - 1.2rem));
            }

            .hero h1,
            .page-hero h1 {
                font-size: 2.35rem;
            }

            .hero p,
            .page-hero p {
                font-size: 1rem;
            }

            .section {
                padding: 4.5rem 0;
            }

            .section-title {
                font-size: 1.9rem;
            }

            .day-strip {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .image-panel,
            .image-panel img {
                min-height: 300px;
            }

            .cta-band h2 {
                font-size: 1.65rem;
            }

            .footer-bottom {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body class="public-site @yield('body_class')">
    @php
        $navigation = [
            ['route' => 'public.enotourism', 'label' => 'Enoturismo'],
            ['route' => 'public.features', 'label' => 'Funcionalidades'],
            ['route' => 'public.widgets', 'label' => 'Widgets'],
            ['route' => 'public.integrations', 'label' => 'Integraciones'],
            ['route' => 'public.services', 'label' => 'Servicios'],
        ];
        $accessRoute = auth()->check() ? route('dashboard') : route('login');
        $accessLabel = auth()->check() ? 'Ir al panel' : 'Entrar';
    @endphp

    <header class="site-header">
        <div class="container header-inner">
            <a class="brand" href="{{ route('public.home') }}">
                <span class="brand-mark">C</span>
                <span>Clockia</span>
            </a>

            <nav class="site-nav" data-site-nav>
                @foreach ($navigation as $item)
                    <a href="{{ route($item['route']) }}" class="{{ request()->routeIs($item['route']) ? 'is-active' : '' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="site-actions" data-site-actions>
                <a class="button button-text" href="{{ $accessRoute }}">{{ $accessLabel }}</a>
                <a class="button button-primary" href="{{ route('public.widgets') }}">Ver widgets</a>
            </div>

            <button class="menu-toggle" type="button" aria-label="Abrir navegación" data-menu-toggle>
                Menu
            </button>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <a class="brand" href="{{ route('public.home') }}">
                        <span class="brand-mark">C</span>
                        <span>Clockia</span>
                    </a>
                    <p style="margin: 1rem 0 0; max-width: 30rem;">
                        Motor de reservas para bodegas y propuestas de enoturismo con calendario widget, chatbot widget, pagos y disponibilidad por experiencia.
                    </p>
                </div>

                <div>
                    <h3>Producto</h3>
                    <ul class="footer-links">
                        @foreach ($navigation as $item)
                            <li><a href="{{ route($item['route']) }}">{{ $item['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <div>
                    <h3>Acceso</h3>
                    <ul class="footer-links">
                        <li><a href="{{ route('public.home') }}">Inicio</a></li>
                        <li><a href="{{ route('public.widgets') }}">Calendario + chatbot</a></li>
                        <li><a href="{{ $accessRoute }}">{{ $accessLabel }}</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <span>Clockia para enoturismo</span>
                <span>Experiencias, aforo, franjas y reservas en una sola capa pública.</span>
            </div>
        </div>
    </footer>

    <script>
        const menuToggle = document.querySelector('[data-menu-toggle]');

        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                document.body.classList.toggle('public-nav-open');
            });
        }
    </script>
</body>
</html>
