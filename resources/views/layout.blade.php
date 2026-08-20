<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FleetOps Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=20260814c" />
    <style>
      /* ── THEME OVERRIDE ───────────────────────────── */
      :root {
        --sidebar: #1a2747;
        --sidebar-dark: #0f1b38;
        --bg: #f4f6fb;
        --surface: #ffffff;
        --text: #14213d;
        --muted: #6c7a93;
        --accent: #4361ee;
        --accent-light: rgba(67,97,238,0.12);
        --teal: #4cc9f0;
        --orange: #f4a261;
        --blue: #4361ee;
        --border: #e4e9f4;
        --shadow: 0 4px 24px rgba(67,97,238,0.08), 0 1px 4px rgba(20,33,61,0.06);
        --sidebar-shadow: 4px 0 24px rgba(15,27,56,0.22);
      }

      /* ── Dark Mode ─────────────────────────────────── */
      [data-theme="dark"] {
        --bg: #0d1421;
        --surface: #162032;
        --text: #e4eaf5;
        --muted: #7a8faa;
        --border: #1e2e45;
        --shadow: 0 4px 24px rgba(0,0,0,0.35), 0 1px 4px rgba(0,0,0,0.25);
      }
      [data-theme="dark"] body { background: #0d1421 !important; }
      [data-theme="dark"] .main-panel { background: #0d1421; }
      [data-theme="dark"] .stat-card,
      [data-theme="dark"] .panel,
      [data-theme="dark"] .hero-card,
      [data-theme="dark"] .search-bar,
      [data-theme="dark"] .profile-pill,
      [data-theme="dark"] .modal-card,
      [data-theme="dark"] .panel-card {
        background: #162032 !important;
        border-color: #1e2e45 !important;
        color: #e4eaf5 !important;
      }
      [data-theme="dark"] .tb-icon-btn {
        background: #1a2840 !important;
        color: #8a9fc0 !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3) !important;
      }
      [data-theme="dark"] .tb-icon-btn:hover { color: #4361ee !important; }
      [data-theme="dark"] .profile-pill .pp-info strong { color: #e4eaf5 !important; }
      [data-theme="dark"] .profile-pill .pp-info small  { color: #7a8faa !important; }
      [data-theme="dark"] .profile-pill .pp-chevron { color: #7a8faa !important; }
      [data-theme="dark"] .profile-pill .pp-avatar {
        background: linear-gradient(135deg,#4361ee,#7b2ff7) !important;
      }
      [data-theme="dark"] .pp-menu,
      [data-theme="dark"] .sb-menu {
        background: #162032 !important;
        box-shadow: 0 12px 32px rgba(0,0,0,0.45) !important;
      }
      [data-theme="dark"] .pp-menu-item,
      [data-theme="dark"] .sb-menu-item { color: #e4eaf5 !important; }
      [data-theme="dark"] .pp-menu-item:hover,
      [data-theme="dark"] .sb-menu-item:hover { background: rgba(67,97,238,0.15) !important; }
      [data-theme="dark"] .search-bar input { color: #e4eaf5 !important; background: transparent !important; }
      [data-theme="dark"] .search-bar svg { color: #7a8faa !important; }
      [data-theme="dark"] .hero-card { background: linear-gradient(90deg, #162032 0%, #1a2840 100%) !important; }
      [data-theme="dark"] .hero-card h1 { color: #e4eaf5 !important; }
      [data-theme="dark"] .hero-copy { color: #7a8faa !important; }
      [data-theme="dark"] .eyebrow { color: #4cc9f0 !important; }
      [data-theme="dark"] .hero-badge { background: rgba(67,97,238,0.2) !important; color: #7ba8ff !important; }
      [data-theme="dark"] .stat-heading { color: #7a8faa !important; }
      [data-theme="dark"] .stat-value { color: #e4eaf5 !important; }
      [data-theme="dark"] .negative-card { background: linear-gradient(135deg, #162032 0%, #1d2516 100%) !important; }
      [data-theme="dark"] th { color: #7a8faa !important; }
      [data-theme="dark"] td { color: #c5d0e0 !important; border-color: #1e2e45 !important; }
      [data-theme="dark"] .panel-header h3 { color: #e4eaf5 !important; }
      [data-theme="dark"] .list-item h4 { color: #e4eaf5 !important; }
      [data-theme="dark"] .list-item p { color: #7a8faa !important; }
      [data-theme="dark"] .legend-row { border-color: #1e2e45 !important; color: #c5d0e0 !important; }
      [data-theme="dark"] .legend-table { border-color: #1e2e45 !important; }
      [data-theme="dark"] .quick-item { background: #1a2840 !important; color: #c5d0e0 !important; }
      [data-theme="dark"] .dispatch-item { background: #1a2840 !important; border-color: #1e2e45 !important; }
      [data-theme="dark"] .dispatch-item:hover, [data-theme="dark"] .dispatch-item.selected { background: #1e3258 !important; }
      [data-theme="dark"] .pill-button { background: rgba(67,97,238,0.2) !important; color: #7ba8ff !important; }
      [data-theme="dark"] .notif-card { background: #1a2840 !important; }
      [data-theme="dark"] .btn-secondary { background: #1a2840 !important; border-color: #1e2e45 !important; color: #c5d0e0 !important; }
      [data-theme="dark"] .dark-mode-btn { background: #1a2840 !important; color: #e4eaf5 !important; }
      /* Dark mode topbar sun icon to indicate switch to light */
      [data-theme="dark"] #darkModeBtn svg { stroke: #f6c24f; }

      /* ── Sidebar Shell ── */
      .sidebar {
        background: linear-gradient(180deg, #1a2747 0%, #0f1b38 100%) !important;
        box-shadow: 4px 0 24px rgba(15,27,56,0.22) !important;
        display: flex !important;
        flex-direction: column !important;
        padding: 0 !important;
        width: 240px !important;
        min-width: 240px !important;
        height: 100vh !important;
        position: sticky !important;
        top: 0 !important;
        z-index: 100 !important;
      }

      /* ── Brand Header (top) ── */
      .sb-brand {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 16px 14px 14px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        text-decoration: none;
        user-select: none;
      }
      .sb-brand-full-wrap {
        display: flex;
        align-items: center;
        width: 100%;
      }
      .sb-brand-svg {
        width: 100%;
        max-width: 210px;
        height: auto;
        display: block;
      }

      /* ── Nav Links ── */
      .nav-links {
        flex: 1;
        display: flex !important;
        flex-direction: column !important;
        gap: 2px !important;
        padding: 14px 12px !important;
        overflow-y: auto;
      }
      .nav-item {
        display: flex !important;
        align-items: center !important;
        gap: 11px !important;
        padding: 9px 12px !important;
        border-radius: 10px !important;
        color: rgba(255,255,255,0.65) !important;
        text-decoration: none !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        transition: background 0.18s ease, color 0.18s ease !important;
        position: relative !important;
        white-space: nowrap !important;
      }
      .nav-item svg {
        width: 17px;
        height: 17px;
        flex-shrink: 0;
        opacity: 0.65;
        transition: opacity 0.18s ease;
      }
      .nav-item:hover {
        background: rgba(255,255,255,0.08) !important;
        color: #fff !important;
      }
      .nav-item:hover svg { opacity: 0.9; }
      .nav-item.active {
        background: #4361ee !important;
        color: #fff !important;
        font-weight: 600 !important;
        box-shadow: 0 4px 14px rgba(67,97,238,0.45) !important;
      }
      .nav-item.active svg { opacity: 1; }
      /* nav badges hidden — removed by design */
      .nav-badge { display: none !important; }

      /* ── Collapse Button ── */
      .sb-collapse {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        border: none;
        background: transparent;
        color: rgba(255,255,255,0.45);
        font-size: 0.82rem;
        font-weight: 500;
        cursor: pointer;
        transition: color 0.18s ease;
        border-top: 1px solid rgba(255,255,255,0.07);
        width: 100%;
        text-align: left;
        margin-top: auto !important;
      }
      .sb-collapse:hover { color: rgba(255,255,255,0.85); }
      .sb-collapse svg { width: 14px; height: 14px; flex-shrink: 0; }

      /* ── Sidebar User (bottom) ── */
      .sb-user {
        display: flex;
        align-items: center;
        gap: 10px;
        position: relative;
        padding: 14px 16px;
        border-top: 1px solid rgba(255,255,255,0.07);
        cursor: pointer;
        transition: background 0.18s ease;
      }
      .sb-user:hover { background: rgba(255,255,255,0.05); }
      .sb-user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg,#4361ee,#7b2ff7);
        color: #fff;
        font-weight: 700;
        font-size: 0.78rem;
        display: grid;
        place-items: center;
        flex-shrink: 0;
      }
      .sb-user-info { flex: 1; overflow: hidden; }
      .sb-user-info strong {
        display: block;
        font-size: 0.82rem;
        font-weight: 700;
        color: #fff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }
      .sb-user-info small {
        display: block;
        font-size: 0.7rem;
        color: rgba(255,255,255,0.45);
        white-space: nowrap;
      }
      .sb-user-chevron {
        color: rgba(255,255,255,0.4);
        flex-shrink: 0;
      }
      .sb-user-chevron svg { width: 14px; height: 14px; }

      /* ── Topbar ── */
      .topbar {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 16px !important;
        margin-bottom: 20px !important;
        flex-wrap: nowrap !important;
      }
      .topbar-actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

      /* Icon buttons */
      .tb-icon-btn {
        position: relative;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: none;
        background: var(--surface);
        box-shadow: var(--shadow);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--muted);
        transition: transform 0.18s ease, box-shadow 0.18s ease, color 0.18s ease;
      }
      .tb-icon-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(67,97,238,0.18);
        color: #4361ee;
      }
      .tb-icon-btn svg { width: 18px; height: 18px; }
      .tb-badge {
        position: absolute;
        top: -2px;
        right: -2px;
        min-width: 17px;
        height: 17px;
        border-radius: 999px;
        font-size: 0.65rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
        border: 2px solid var(--surface);
      }
      .tb-badge.red { background: #ef4444; color: #fff; }
      .tb-badge.blue { background: #4361ee; color: #fff; }

      /* Profile pill */
      .profile-pill {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        background: var(--surface) !important;
        padding: 7px 14px 7px 8px !important;
        border-radius: 999px !important;
        box-shadow: var(--shadow) !important;
        border: none !important;
        cursor: pointer !important;
        transition: box-shadow 0.18s ease, transform 0.18s ease !important;
        position: relative !important;
      }
      .profile-pill:hover { box-shadow: 0 8px 24px rgba(67,97,238,0.15) !important; transform: translateY(-1px) !important; }

      /* Profile / sidebar user dropdown menus */
      .pp-menu, .sb-menu {
        display: none;
        position: absolute;
        min-width: 170px;
        background: var(--surface);
        border-radius: 12px;
        box-shadow: 0 12px 32px rgba(0,0,0,0.15);
        padding: 6px;
        z-index: 200;
      }
      .pp-menu { top: calc(100% + 8px); right: 0; }
      .sb-menu { left: 0; bottom: calc(100% + 8px); }
      .profile-pill.open .pp-menu,
      .sb-user.open .sb-menu { display: block; }
      .pp-menu-item, .sb-menu-item {
        display: block;
        width: 100%;
        text-align: left;
        padding: 9px 10px;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text);
        background: none;
        border: none;
        cursor: pointer;
        text-decoration: none;
      }
      .pp-menu-item:hover, .sb-menu-item:hover { background: rgba(67,97,238,0.08); }
      .pp-menu-item.danger, .sb-menu-item.danger { color: #e5484d; }
      .pp-menu-item.danger:hover, .sb-menu-item.danger:hover { background: rgba(229,72,77,0.08); }
      .profile-pill .pp-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: linear-gradient(135deg,#4361ee,#7b2ff7);
        color: #fff;
        font-weight: 700;
        font-size: 0.78rem;
        display: grid;
        place-items: center;
        flex-shrink: 0;
      }
      .profile-pill .pp-info strong, .profile-pill .pp-info small { display: block; }
      .profile-pill .pp-info strong { font-size: 0.84rem; font-weight: 700; color: var(--text); }
      .profile-pill .pp-info small { font-size: 0.72rem; color: var(--muted); margin-top: 1px; }
      .profile-pill .pp-chevron { color: var(--muted); margin-left: 2px; }
      .profile-pill .pp-chevron svg { width: 14px; height: 14px; display: block; }

      /* Other theme overrides */
      body { background: #f4f6fb !important; }
      .eyebrow { color: #4361ee !important; }
      .hero-badge { background: rgba(67,97,238,0.12) !important; color: #4361ee !important; }
      .pill-button { background: rgba(67,97,238,0.12) !important; color: #4361ee !important; }
      .positive { color: #22c55e !important; }
      .list-icon { background: rgba(67,97,238,0.12) !important; color: #4361ee !important; }
      .status-approved { background: rgba(67,97,238,0.14) !important; color: #4361ee !important; }
      .btn-primary { background: linear-gradient(135deg,#4361ee,#3a0ca3) !important; box-shadow: 0 6px 14px rgba(67,97,238,0.35) !important; }
      .search-bar { box-shadow: 0 4px 24px rgba(67,97,238,0.08), 0 1px 4px rgba(20,33,61,0.06) !important; }
      .stat-card, .panel { box-shadow: 0 4px 24px rgba(67,97,238,0.07), 0 1px 4px rgba(20,33,61,0.05) !important; }

      /* Sidebar collapsed state */
      .sidebar.collapsed { width: 68px !important; min-width: 68px !important; }
      .sidebar.collapsed .sb-brand-info,
      .sidebar.collapsed .nav-item span:not(.nav-badge),
      .sidebar.collapsed .nav-badge,
      .sidebar.collapsed .sb-collapse span,
      .sidebar.collapsed .sb-user-info,
      .sidebar.collapsed .sb-user-chevron { display: none !important; }
      .sidebar.collapsed .sb-brand { justify-content: center; padding: 22px 12px 18px; }
      .sidebar.collapsed .nav-links { padding: 14px 10px !important; }
      .sidebar.collapsed .nav-item { justify-content: center !important; padding: 10px !important; }
      .sidebar.collapsed .nav-item svg { opacity: 0.7; }
      .sidebar.collapsed .sb-collapse { justify-content: center; padding: 12px; }
      .sidebar.collapsed .sb-collapse svg { transform: rotate(180deg); }
      .sidebar.collapsed .sb-user { justify-content: center; padding: 12px; }
      .sidebar.collapsed .sb-brand-logo-wrap { margin-right: 0 !important; }
    </style>
</head>
<body>
<div class="app-shell" id="appShell">
    <aside class="sidebar" id="mainSidebar">
        <script>
            if (localStorage.getItem('sbCollapsed') === '1') {
                document.getElementById('mainSidebar').classList.add('collapsed');
            }
        </script>

        <!-- Brand Header -->
        <a href="<?= htmlspecialchars($dashboard['basePath'] . '/dashboard') ?>" class="sb-brand" title="Archon Nell Incorporated">
            <div class="sb-brand-full-wrap">
                <svg class="sb-brand-svg" viewBox="0 0 215 54" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <!-- Rainbow Gradient for vertical orbit -->
                        <linearGradient id="anRainbowGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#4ade80" />
                            <stop offset="20%" stop-color="#facc15" />
                            <stop offset="50%" stop-color="#ec4899" />
                            <stop offset="80%" stop-color="#8b5cf6" />
                            <stop offset="100%" stop-color="#3b82f6" />
                        </linearGradient>
                        <!-- Blue/purple gradient for tilted ring -->
                        <linearGradient id="anBlueGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#38bdf8" />
                            <stop offset="60%" stop-color="#4f46e5" />
                            <stop offset="100%" stop-color="#8b5cf6" />
                        </linearGradient>
                        <!-- Magenta/orange gradient for other tilted ring -->
                        <linearGradient id="anPinkGrad" x1="0%" y1="100%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#a855f7" />
                            <stop offset="50%" stop-color="#ec4899" />
                            <stop offset="100%" stop-color="#f59e0b" />
                        </linearGradient>
                        <!-- Core lime green glow -->
                        <radialGradient id="anCoreGrad" cx="50%" cy="50%" r="50%">
                            <stop offset="0%" stop-color="#bbf7d0" />
                            <stop offset="55%" stop-color="#4ade80" />
                            <stop offset="90%" stop-color="#16a34a" />
                            <stop offset="100%" stop-color="#15803d" />
                        </radialGradient>
                        <filter id="anGlow" x="-20%" y="-20%" width="140%" height="140%">
                            <feGaussianBlur stdDeviation="1" result="blur"/>
                            <feComposite in="SourceGraphic" in2="blur" operator="over"/>
                        </filter>
                    </defs>

                    <!-- Atom Orbital System -->
                    <g transform="translate(2, 2)">
                        <!-- Ring 1: Almost vertical rainbow ring -->
                        <ellipse cx="23" cy="25" rx="23" ry="8" transform="rotate(-78 23 25)" stroke="url(#anRainbowGrad)" stroke-width="1.8" fill="none" opacity="0.95"/>
                        <!-- Ring 2: Diagonal blue/purple ring -->
                        <ellipse cx="23" cy="25" rx="23" ry="8" transform="rotate(-18 23 25)" stroke="url(#anBlueGrad)" stroke-width="1.8" fill="none" opacity="0.9"/>
                        <!-- Ring 3: Diagonal pink/orange ring -->
                        <ellipse cx="23" cy="25" rx="23" ry="8" transform="rotate(42 23 25)" stroke="url(#anPinkGrad)" stroke-width="1.8" fill="none" opacity="0.9"/>
                        
                        <!-- Glowing Center Core Oval with Gold Trim -->
                        <ellipse cx="23" cy="25" rx="9" ry="5.5" fill="url(#anCoreGrad)" stroke="#fef08a" stroke-width="1" filter="url(#anGlow)"/>
                        <!-- Inner core symbol / text -->
                        <text x="23" y="27.5" font-family="Arial, sans-serif" font-size="4.8" font-weight="900" fill="#14532d" text-anchor="middle" letter-spacing="0.5">ANI</text>
                        
                        <!-- Particle nodes -->
                        <circle cx="23" cy="3" r="1.4" fill="#facc15"/>
                        <circle cx="43" cy="18" r="1.4" fill="#38bdf8"/>
                        <circle cx="5" cy="32" r="1.4" fill="#ec4899"/>
                    </g>

                    <!-- Brand Typography -->
                    <g transform="translate(56, 0)">
                        <!-- Stylized ARCHON NELL in vivid red with tall peak A and N -->
                        <g fill="#ee1c25">
                            <!-- Tall pointed A -->
                            <path d="M 0,32 L 6,4 L 12,32 L 8.5,32 L 6.8,24 L 3.5,24 L 2.2,32 Z M 5.2,16.5 L 6,11.5 L 6.8,16.5 Z" />
                            <!-- R -->
                            <path d="M 14,32 L 14,8 L 21.5,8 C 24.5,8 26,9.8 26,13 C 26,15.5 24.5,17 22.5,17.5 L 26.5,32 L 23,32 L 19.5,18.5 L 17,18.5 L 17,32 Z M 17,15.5 L 21,15.5 C 22.2,15.5 23,14.8 23,13 C 23,11.2 22.2,10.5 21,10.5 L 17,10.5 Z" />
                            <!-- C -->
                            <path d="M 37.5,12.5 L 35,14.5 C 33.8,12.5 32,10.5 29.5,10.5 C 26.5,10.5 24,13 24,20 C 24,27 26.5,29.5 29.5,29.5 C 32,29.5 33.8,27.5 35,25.5 L 37.5,27.5 C 35.8,30.5 33,32 29.5,32 C 24,32 21,27.5 21,20 C 21,12.5 24,8 29.5,8 C 33,8 35.8,9.5 37.5,12.5 Z" />
                            <!-- H -->
                            <path d="M 39,32 L 39,8 L 42,8 L 42,18 L 48,18 L 48,8 L 51,8 L 51,32 L 48,32 L 48,21 L 42,21 L 42,32 Z" />
                            <!-- O -->
                            <path d="M 59.5,8 C 65,8 67.5,12.5 67.5,20 C 67.5,27.5 65,32 59.5,32 C 54,32 51.5,27.5 51.5,20 C 51.5,12.5 54,8 59.5,8 Z M 59.5,10.8 C 56.8,10.8 54.5,13.5 54.5,20 C 54.5,26.5 56.8,29.2 59.5,29.2 C 62.2,29.2 64.5,26.5 64.5,20 C 64.5,13.5 62.2,10.8 59.5,10.8 Z" />
                            <!-- N -->
                            <path d="M 69.5,32 L 69.5,8 L 72.8,8 L 78.5,23.5 L 78.5,8 L 81.5,8 L 81.5,32 L 78.2,32 L 72.5,16.5 L 72.5,32 Z" />
                            
                            <!-- NELL: Tall Pointed N -->
                            <path d="M 88,32 L 88,4 L 91.5,4 L 98,22.5 L 98,8 L 101,8 L 101,32 L 97.5,32 L 91,13.5 L 91,32 Z" />
                            <!-- E -->
                            <path d="M 103.5,32 L 103.5,8 L 114,8 L 114,10.8 L 106.5,10.8 L 106.5,18 L 113,18 L 113,20.8 L 106.5,20.8 L 106.5,29.2 L 114,29.2 L 114,32 Z" />
                            <!-- L -->
                            <path d="M 116.5,32 L 116.5,8 L 119.5,8 L 119.5,29.2 L 126.5,29.2 L 126.5,32 Z" />
                            <!-- L -->
                            <path d="M 128.5,32 L 128.5,8 L 131.5,8 L 131.5,29.2 L 138.5,29.2 L 138.5,32 Z" />
                        </g>

                        <!-- INCORPORATED in bold blue with wide letter tracking -->
                        <text x="70" y="44" font-family="'Inter', -apple-system, system-ui, sans-serif" font-size="8.5" font-weight="800" fill="#2563eb" text-anchor="middle" letter-spacing="4">
                            INCORPORATED
                        </text>
                    </g>
                </svg>
            </div>
        </a>

        <!-- Nav Links -->
        <nav class="nav-links">
            <?php
            $navItems = [
                'dashboard'       => ['label' => 'Dashboard',       'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>'],
                'vehicles'        => ['label' => 'Fleet Command',   'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>'],
                'reservations'    => ['label' => 'Dispatch Hub',    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>', 'badge' => 3],
                'driver-analytics'=> ['label' => 'Driver Analytics','icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>'],
                'fuel-logs'       => ['label' => 'Fuel Monitor',    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 22V9a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v13"/><path d="M14 7v4a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2V9l-3-5-3 3z"/><line x1="3" y1="22" x2="14" y2="22"/></svg>'],
                'cost-analytics'  => ['label' => 'Cost Analytics',  'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>'],
                'routes'          => ['label' => 'Smart Routing',   'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>'],
                'reports'         => ['label' => 'Reports',         'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>'],
                'notifications'   => ['label' => 'Notifications',   'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>', 'badge' => 3],
                'usermanagement'  => ['label' => 'User Management', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>'],
                'settings'        => ['label' => 'Settings',        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>'],
            ];
            foreach ($navItems as $route => $item):
                $href = $dashboard['basePath'] . ($route === 'dashboard' ? '/dashboard' : '/' . $route);
                $isActive = $dashboard['page'] === $route;
            ?>
            <a href="<?= htmlspecialchars($href) ?>" class="nav-item<?= $isActive ? ' active' : '' ?>" title="<?= htmlspecialchars($item['label']) ?>">
                <?= $item['icon'] ?>
                <span><?= htmlspecialchars($item['label']) ?></span>
                <?php if (!empty($item['badge'])): ?>
                    <span class="nav-badge"><?= $item['badge'] ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </nav>

        <!-- Collapse Button -->
        <button class="sb-collapse" id="sbCollapseBtn" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            <span>Collapse</span>
        </button>

        <!-- Bottom User Profile -->
        <div class="sb-user" id="sbUser">
            <div class="sb-user-avatar"><?= htmlspecialchars($dashboard['user']['initials']) ?></div>
            <div class="sb-user-info">
                <strong><?= htmlspecialchars($dashboard['user']['name']) ?></strong>
                <small><?= htmlspecialchars($dashboard['user']['title']) ?></small>
            </div>
            <span class="sb-user-chevron">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
            </span>
            <div class="sb-menu">
                <a href="{{ route('settings') }}" class="sb-menu-item">Settings</a>
                <button type="button" class="sb-menu-item danger sb-menu-logout">Log out</button>
            </div>
        </div>

    </aside>

    <main class="main-panel">
        <header class="topbar">
            <!-- Mobile toggle -->
            <button class="sidebar-toggle" type="button" aria-label="Open navigation" id="mobileToggle">
                <span></span><span></span><span></span>
            </button>

            <!-- Search bar -->
            <div class="search-bar" style="flex:1 1 320px; min-width:200px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--muted);flex-shrink:0"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="globalSearchInput" placeholder="Search fleet, routes, drivers..." style="border:0;outline:none;width:100%;background:transparent;font-size:0.9rem;" />
            </div>

            <!-- Right actions -->
            <div class="topbar-actions">
                <!-- Dark mode -->
                <button class="tb-icon-btn" id="darkModeBtn" title="Toggle dark mode" aria-label="Toggle dark mode">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                </button>
                <!-- Bell / notifications -->
                <button class="tb-icon-btn" title="Notifications">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    <span class="tb-badge red"></span>
                </button>
                <!-- Mail -->
                <button class="tb-icon-btn" title="Messages">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <span class="tb-badge blue">2</span>
                </button>
                <!-- Profile pill -->
                <div class="profile-pill" id="profilePill">
                    <div class="pp-avatar"><?= htmlspecialchars($dashboard['user']['initials']) ?></div>
                    <div class="pp-info">
                        <strong><?= htmlspecialchars($dashboard['user']['name']) ?></strong>
                        <small><?= htmlspecialchars($dashboard['user']['title']) ?></small>
                    </div>
                    <span class="pp-chevron">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </span>
                    <div class="pp-menu">
                        <a href="{{ route('settings') }}" class="pp-menu-item">Settings</a>
                        <button type="button" class="pp-menu-item danger pp-menu-logout">Log out</button>
                    </div>
                </div>
            </div>
        </header>

        <section class="hero-card">
            <div>
                <p class="eyebrow">Fleet operations overview</p>
                <h1><?= htmlspecialchars($dashboard['title']) ?></h1>
                <p class="hero-copy">You have 12 dispatches ready, 198 vehicles active, and 3 critical alerts to review.</p>
            </div>
            <div class="hero-badge">Live • 24/7 Operations</div>
        </section>

        @include('pages.'.$dashboard['page'], ['dashboard' => $dashboard])
    </main>
</div>

<!-- Hidden Logout Form -->
<form id="logoutForm" method="post" action="{{ route('logout') }}" style="display: none;">
    @csrf
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var sidebar  = document.getElementById('mainSidebar');
    var colBtn   = document.getElementById('sbCollapseBtn');
    var mobBtn   = document.getElementById('mobileToggle');
    var shell    = document.querySelector('.app-shell');
    var darkBtn  = document.getElementById('darkModeBtn');

    // Sidebar collapse toggle
    if (colBtn && sidebar) {
        colBtn.addEventListener('click', function () {
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sbCollapsed', sidebar.classList.contains('collapsed') ? '1' : '0');
        });
        if (localStorage.getItem('sbCollapsed') === '1') {
            sidebar.classList.add('collapsed');
        }
    }

    // Mobile toggle
    if (mobBtn && shell) {
        mobBtn.addEventListener('click', function () { shell.classList.toggle('sidebar-open'); });
        document.addEventListener('click', function (e) {
            if (!shell.classList.contains('sidebar-open')) return;
            if (sidebar && sidebar.contains(e.target)) return;
            if (mobBtn.contains(e.target)) return;
            shell.classList.remove('sidebar-open');
        });
    }

    // Profile dropdown handlers
    var logoutForm = document.getElementById('logoutForm');
    var sbUser     = document.getElementById('sbUser');
    var profPill   = document.getElementById('profilePill');

    function closeProfileMenus() {
        if (sbUser) sbUser.classList.remove('open');
        if (profPill) profPill.classList.remove('open');
    }

    [sbUser, profPill].forEach(function (el) {
        if (!el) return;
        el.addEventListener('click', function (e) {
            e.stopPropagation();
            var wasOpen = el.classList.contains('open');
            closeProfileMenus();
            if (!wasOpen) el.classList.add('open');
        });
    });

    document.addEventListener('click', closeProfileMenus);

    if (logoutForm) {
        document.querySelectorAll('.pp-menu-logout, .sb-menu-logout').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                logoutForm.submit();
            });
        });
    }

    // Dark mode toggle
    if (darkBtn) {
        var root = document.documentElement;
        var sunSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>';
        var moonSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';

        function updateDarkModeIcon(isDark) {
            darkBtn.innerHTML = isDark ? sunSvg : moonSvg;
        }

        if (localStorage.getItem('darkMode') === '1') {
            root.setAttribute('data-theme', 'dark');
            updateDarkModeIcon(true);
        } else {
            updateDarkModeIcon(false);
        }

        darkBtn.addEventListener('click', function () {
            var isDark = root.getAttribute('data-theme') === 'dark';
            var newDark = !isDark;
            root.setAttribute('data-theme', newDark ? 'dark' : '');
            localStorage.setItem('darkMode', newDark ? '1' : '0');
            updateDarkModeIcon(newDark);
        });
    }
});
</script>
</body>
</html>
