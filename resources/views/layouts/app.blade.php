<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Painel de Gerenciamento de Hospedagem">
    <title>@yield('title', 'Painel') — HostManager</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg: #0f1117;
            --bg2: #161b27;
            --bg3: #1e2535;
            --border: #2a3248;
            --accent: #6c63ff;
            --accent2: #4ade80;
            --accent3: #f59e0b;
            --danger: #ef4444;
            --text: #e2e8f0;
            --muted: #64748b;
            --sidebar-w: 240px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            min-height: 100vh;
        }

        /* ── Sidebar ─────────────────── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--bg2);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
        }
        .sidebar-brand {
            padding: 24px 20px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .sidebar-brand h1 {
            font-size: 18px;
            font-weight: 700;
            color: var(--accent);
            letter-spacing: -0.3px;
        }
        .sidebar-brand p {
            font-size: 11px;
            color: var(--muted);
            margin-top: 2px;
        }
        .sidebar-nav {
            flex: 1;
            padding: 12px 10px;
            overflow-y: auto;
        }
        .nav-section {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
            padding: 16px 10px 6px;
        }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 8px;
            color: var(--muted);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all .15s;
            margin-bottom: 2px;
        }
        .nav-link:hover, .nav-link.active {
            background: var(--bg3);
            color: var(--text);
        }
        .nav-link.active { color: var(--accent); }
        .nav-icon { font-size: 16px; width: 20px; text-align: center; }

        /* ── Main ────────────────────── */
        .main {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .topbar {
            background: var(--bg2);
            border-bottom: 1px solid var(--border);
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .topbar-right-area {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .page-title { font-size: 18px; font-weight: 600; }
        .page-subtitle { font-size: 13px; color: var(--muted); margin-top: 2px; }
        .content { padding: 32px; flex: 1; min-width: 0; }

        /* ── Metas / Prêmios Topbar Widget ── */
        .metas-widget-container {
            position: relative;
        }
        .metas-widget-container:hover .metas-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .metas-widget {
            display: flex;
            flex-direction: column;
            gap: 5px;
            background: var(--bg3);
            border: 1px solid var(--border);
            padding: 8px 14px;
            border-radius: 10px;
            min-width: 320px;
            cursor: pointer;
            transition: border-color .15s;
        }
        .metas-widget:hover {
            border-color: var(--accent);
        }
        .metas-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            line-height: 1;
        }
        .metas-label {
            display: flex;
            align-items: center;
            gap: 4px;
            color: #818cf8;
            font-weight: 600;
        }
        .metas-info-icon {
            font-size: 11px;
            color: var(--muted);
            margin-left: 2px;
        }
        .metas-values {
            font-size: 12px;
            font-weight: 700;
            color: var(--text);
        }
        .metas-progress-track {
            width: 100%;
            height: 5px;
            background: var(--bg2);
            border-radius: 10px;
            overflow: hidden;
        }
        .metas-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #6c63ff, #a78bfa);
            border-radius: 10px;
            transition: width 0.4s ease;
        }
        .metas-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 8px;
            width: 280px;
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            padding: 12px;
            z-index: 200;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px);
            transition: all .2s ease;
        }
        .metas-dropdown-header {
            font-size: 13px;
            font-weight: 700;
            color: var(--text);
            padding-bottom: 8px;
            margin-bottom: 8px;
            border-bottom: 1px solid var(--border);
        }
        .metas-dropdown-list {
            max-height: 260px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .meta-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 12px;
            background: rgba(255,255,255,0.02);
        }
        .meta-item.alcancada {
            background: rgba(74,222,128,0.08);
            color: #4ade80;
        }
        .meta-item.atual {
            background: rgba(108,99,255,0.15);
            border: 1px solid var(--accent);
            color: #fff;
            font-weight: 600;
        }
        .meta-title { font-weight: 600; }
        .meta-status { font-size: 11px; opacity: 0.8; }

        /* ── Alerts ─────────────────── */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid transparent;
        }
        .alert-success { background: rgba(74,222,128,.08); border-color: rgba(74,222,128,.3); color: #4ade80; }
        .alert-error   { background: rgba(239,68,68,.08);  border-color: rgba(239,68,68,.3);  color: #f87171; }

        /* ── Cards ──────────────────── */
        .card {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            min-width: 0;
        }
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .card-title { font-size: 15px; font-weight: 600; }

        /* ── Buttons ────────────────── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all .15s;
            white-space: nowrap;
        }
        .btn-logout {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--muted);
            padding: 7px 12px;
            border-radius: 7px;
            font-size: 13px;
            cursor: pointer;
            transition: all .15s;
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }
        .btn-logout:hover {
            border-color: var(--danger);
            color: var(--danger);
            background: rgba(239,68,68,0.08);
        }
        .btn-primary { background: var(--accent); color: #fff; }
        .btn-primary:hover { background: #5a52d5; }
        .btn-secondary { background: var(--bg3); color: var(--text); border: 1px solid var(--border); }
        .btn-secondary:hover { border-color: var(--accent); color: var(--accent); }
        .btn-danger { background: rgba(239,68,68,.1); color: #ef4444; border: 1px solid rgba(239,68,68,.3); }
        .btn-danger:hover { background: #ef4444; color: #fff; }
        .btn-success { background: rgba(74,222,128,.1); color: #4ade80; border: 1px solid rgba(74,222,128,.3); }
        .btn-success:hover { background: #4ade80; color: #000; }
        .btn-sm { padding: 5px 10px; font-size: 12px; }

        /* ── Table ──────────────────── */
        .table-wrap { 
            overflow-x: auto; 
            -webkit-overflow-scrolling: touch; 
            width: 100%;
        }
        table { width: 100%; border-collapse: collapse; font-size: 14px; min-width: 500px; }
        th { text-align: left; padding: 10px 16px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: var(--muted); border-bottom: 1px solid var(--border); white-space: nowrap; }
        td { padding: 12px 16px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(255,255,255,.02); }

        /* ── Badges ─────────────────── */
        .badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }
        .badge-green  { background: rgba(74,222,128,.15); color: #4ade80; }
        .badge-yellow { background: rgba(245,158,11,.15); color: #f59e0b; }
        .badge-red    { background: rgba(239,68,68,.15);  color: #ef4444; }
        .badge-blue   { background: rgba(108,99,255,.15); color: #6c63ff; }
        .badge-gray   { background: rgba(100,116,139,.15); color: #94a3b8; }

        /* ── Forms ──────────────────── */
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 13px; font-weight: 500; color: var(--text); margin-bottom: 6px; }
        .form-control {
            width: 100%;
            background: var(--bg3);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 9px 13px;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color .15s;
        }
        .form-control:focus { outline: none; border-color: var(--accent); }
        .form-control::placeholder { color: var(--muted); }
        .form-error { color: #f87171; font-size: 12px; margin-top: 4px; }

        /* ── Grid ───────────────────── */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }

        /* ── Stat cards ─────────────── */
        .stat-card {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 22px;
            position: relative;
            overflow: hidden;
            min-width: 0;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: var(--accent-color, var(--accent));
        }
        .stat-label { font-size: 12px; font-weight: 500; color: var(--muted); text-transform: uppercase; letter-spacing: .5px; }
        .stat-value { font-size: 26px; font-weight: 700; margin-top: 8px; word-break: break-word; }
        .stat-icon { font-size: 28px; position: absolute; top: 18px; right: 20px; }

        /* ── Pagination ─────────────── */
        .pagination { display: flex; gap: 4px; margin-top: 20px; flex-wrap: wrap; }
        .pagination a, .pagination span {
            padding: 6px 11px;
            border-radius: 7px;
            font-size: 13px;
            border: 1px solid var(--border);
            color: var(--muted);
            text-decoration: none;
            transition: all .15s;
        }
        .pagination a:hover { border-color: var(--accent); color: var(--accent); }
        .pagination .active span { background: var(--accent); border-color: var(--accent); color: #fff; }

        /* ── Filters ────────────────── */
        .filters {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .filters .form-control { width: auto; min-width: 140px; }

        /* ── Empty state ────────────── */
        .empty-state { text-align: center; padding: 60px 20px; color: var(--muted); }
        .empty-state .icon { font-size: 48px; margin-bottom: 16px; opacity: .4; }
        .empty-state p { font-size: 15px; }

        /* ── Mobile Hamburger Toggle & Overlay ── */
        .mobile-toggle {
            display: none;
            background: var(--bg3);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 7px 12px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background .15s;
        }
        .mobile-toggle:hover {
            background: var(--border);
        }
        .sidebar-backdrop {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(2px);
            z-index: 99;
        }

        /* ── MEDIA QUERIES RESPONSIVIDADE ── */
        @media (max-width: 1024px) {
            .mobile-toggle {
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                box-shadow: 0 0 30px rgba(0,0,0,0.7);
            }
            body.sidebar-open .sidebar {
                transform: translateX(0);
            }
            body.sidebar-open .sidebar-backdrop {
                display: block;
            }
            .main {
                margin-left: 0;
                width: 100%;
            }
            .topbar {
                padding: 16px 20px;
                flex-wrap: wrap;
            }
            .topbar-right-area {
                width: 100%;
                justify-content: space-between;
                flex-wrap: wrap;
                margin-top: 8px;
                padding-top: 12px;
                border-top: 1px solid var(--border);
            }
            .metas-widget-container {
                width: 100%;
            }
            .metas-widget {
                min-width: 100% !important;
            }
            .metas-dropdown {
                width: 100% !important;
                left: 0; right: 0;
            }
        }

        @media (max-width: 768px) {
            .content {
                padding: 16px;
            }
            .topbar {
                padding: 12px 16px;
            }
            .grid-2, .grid-3, .grid-4 {
                grid-template-columns: 1fr !important;
            }
            .filters {
                flex-direction: column;
            }
            .filters .form-control {
                width: 100% !important;
            }
            .topbar-user {
                border-left: none !important;
                padding-left: 0 !important;
                width: 100%;
                justify-content: space-between;
            }
            .card {
                padding: 16px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

<!-- Backdrop para Mobile -->
<div class="sidebar-backdrop" onclick="document.body.classList.remove('sidebar-open')"></div>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <div>
            <h1>🌐 HostManager</h1>
            <p>Painel de Hospedagem</p>
        </div>
        <button class="mobile-toggle" onclick="document.body.classList.remove('sidebar-open')" style="display: none;" id="sidebarCloseBtn">
            ✕
        </button>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">Principal</div>
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="nav-icon">📊</span> Dashboard
        </a>
        <a href="{{ route('usuarios.index') }}" class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
            <span class="nav-icon">👤</span> Perfil & Acessos
        </a>

        <div class="nav-section">Gestão</div>
        <a href="{{ route('clientes.index') }}" class="nav-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
            <span class="nav-icon">👥</span> Clientes
        </a>
        <a href="{{ route('sites.index') }}" class="nav-link {{ request()->routeIs('sites.*') ? 'active' : '' }}">
            <span class="nav-icon">🌐</span> Sites
        </a>
        <a href="{{ route('planos.index') }}" class="nav-link {{ request()->routeIs('planos.*') ? 'active' : '' }}">
            <span class="nav-icon">📋</span> Planos
        </a>
        <a href="{{ route('pagamentos.index') }}" class="nav-link {{ request()->routeIs('pagamentos.*') ? 'active' : '' }}">
            <span class="nav-icon">💰</span> Pagamentos
        </a>
    </nav>
</aside>

<!-- Main -->
<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <button class="mobile-toggle" onclick="document.body.classList.toggle('sidebar-open')" title="Menu">
                ☰
            </button>
            <div>
                <div class="page-title">@yield('page-title', View::yieldContent('titulo', 'Painel'))</div>
                <div class="page-subtitle">@yield('page-subtitle', '')</div>
            </div>
        </div>

        <div class="topbar-right-area">
            @yield('topbar-actions')

            @if(isset($metasData))
            <div class="metas-widget-container">
                <div class="metas-widget">
                    <div class="metas-header">
                        <div class="metas-label">
                            <span>🎖️</span> Prêmios
                            <span class="metas-info-icon" title="Ver progresso de metas">ⓘ</span>
                        </div>
                        <div class="metas-values">
                            {{ $metasData['totalReceitaFormatada'] }} / <span style="color: var(--accent);">{{ $metasData['metaAtualFormatada'] }}</span>
                        </div>
                    </div>
                    <div class="metas-progress-track">
                        <div class="metas-progress-bar" style="width: {{ $metasData['porcentagem'] }}%;"></div>
                    </div>
                </div>

                <!-- Dropdown de Metas -->
                <div class="metas-dropdown">
                    <div class="metas-dropdown-header">
                        🏆 Progresso de Metas
                    </div>
                    <div class="metas-dropdown-list">
                        @foreach($metasData['metas'] as $valor => $label)
                            @php 
                                $alcancado = $metasData['totalReceita'] >= $valor;
                                $eAtual = $valor == $metasData['metaAtualValor'] && !$alcancado;
                            @endphp
                            <div class="meta-item {{ $alcancado ? 'alcancada' : ($eAtual ? 'atual' : '') }}">
                                <span class="meta-icon">{{ $alcancado ? '✅' : ($eAtual ? '🎯' : '🔒') }}</span>
                                <span class="meta-title">Meta {{ ($metasData['formatKMB'])($valor) }}</span>
                                <span class="meta-status">
                                    @if($alcancado)
                                        Conquistado
                                    @elseif($eAtual)
                                        {{ $metasData['porcentagem'] }}%
                                    @else
                                        Bloqueado
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Usuário / Perfil no Cabeçalho -->
            <div class="topbar-user" style="display: flex; align-items: center; gap: 12px; padding-left: 16px; border-left: 1px solid var(--border);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    @if(auth()->user()->avatar_url)
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->nome }}" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid var(--accent); flex-shrink: 0;">
                    @else
                        <div class="user-avatar" style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), #a78bfa); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; color: #fff; flex-shrink: 0;">
                            {{ strtoupper(substr(auth()->user()->nome ?? 'A', 0, 1)) }}
                        </div>
                    @endif
                    <div style="display: flex; flex-direction: column; justify-content: center;">
                        <span class="user-name" style="font-size: 13px; font-weight: 600; color: var(--text); line-height: 1.2;">{{ auth()->user()->nome ?? 'Usuário' }}</span>
                        <span class="user-role" style="font-size: 11px; color: var(--muted); line-height: 1.2;">{{ ucfirst(auth()->user()->nivel ?? 'admin') }}</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" style="margin-left: 8px;">
                    @csrf
                    <button type="submit" class="btn-logout" title="Sair do painel">
                        ⬅ Sair
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="content">
        @if(session('sucesso'))
            <div class="alert alert-success">✅ {{ session('sucesso') }}</div>
        @endif
        @if(session('erro'))
            <div class="alert alert-error">⚠️ {{ session('erro') }}</div>
        @endif
        @if(isset($errors) && $errors->any())
            <div class="alert alert-error">
                ⚠️ {{ $errors->first() }}
            </div>
        @endif

        @yield('content', View::yieldContent('conteudo'))
    </div>
</main>

@stack('scripts')
<script>
    // Fecha a sidebar no mobile ao clicar em um link
    document.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 1024) {
                document.body.classList.remove('sidebar-open');
            }
        });
    });
</script>
</body>
</html>
