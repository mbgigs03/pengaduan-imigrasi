{{-- resources/views/layouts/dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' — ' : '' }}RUMANGSA Imigrasi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        code, .font-mono { font-family: 'JetBrains Mono', monospace; }
        #sidebar { transition: width .25s cubic-bezier(.4,0,.2,1), transform .25s cubic-bezier(.4,0,.2,1); }
        #sidebar.collapsed { width: 68px; }
        #sidebar.collapsed .nav-label,
        #sidebar.collapsed .sidebar-logo-text,
        #sidebar.collapsed .user-meta,
        #sidebar.collapsed .nav-section-label { display: none; }
        #sidebar.collapsed .nav-item { justify-content: center; padding: 9px 0; }
        #sidebar.collapsed .sidebar-header { justify-content: center; padding: 0 12px; }
        .nav-item { display:flex; align-items:center; gap:10px; padding:9px 14px; border-radius:10px; font-size:13.5px; font-weight:500; color:#94a3b8; text-decoration:none; transition:all .15s ease; white-space:nowrap; }
        .nav-item:hover { background:rgba(255,255,255,.07); color:#e2e8f0; }
        .nav-item.active { background:rgba(59,130,246,.18); color:#93c5fd; }
        .nav-item.active .nav-icon { color:#60a5fa; }
        .nav-item.nav-logout:hover { background:rgba(239,68,68,.15); color:#f87171; }
        .nav-icon { width:18px; height:18px; flex-shrink:0; }
        .stat-card { background:#fff; border:1px solid #f1f5f9; border-radius:14px; padding:20px; transition:box-shadow .2s; }
        .stat-card:hover { box-shadow:0 4px 20px rgba(0,0,0,.07); }
        .chart-card { background:#fff; border:1px solid #f1f5f9; border-radius:14px; padding:20px; }
        .chart-title { font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.07em; margin-bottom:16px; }
        .data-table th { font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em; padding:10px 14px; border-bottom:1px solid #f1f5f9; }
        .data-table td { padding:11px 14px; border-bottom:1px solid #f8fafc; }
        .data-table tr:hover td { background:#fafafa; }
        .data-table tr:last-child td { border-bottom:none; }
        .badge { display:inline-flex; align-items:center; padding:2px 8px; border-radius:99px; font-size:11px; font-weight:600; }
        .badge-pending    { background:#fff8e1; color:#b45309; border:1px solid #fde68a; }
        .badge-proses     { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
        .badge-diteruskan { background:#f5f3ff; color:#6d28d9; border:1px solid #ddd6fe; }
        .badge-selesai    { background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; }
        ::-webkit-scrollbar { width:5px; height:5px; }
        ::-webkit-scrollbar-track { background:transparent; }
        ::-webkit-scrollbar-thumb { background:#e2e8f0; border-radius:99px; }
        @keyframes pageIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
        .page-content { animation: pageIn .3s ease both; }
        .dot-over { animation: pulse-red 1.5s infinite; }
        @keyframes pulse-red { 0%,100%{box-shadow:0 0 0 0 rgba(239,68,68,.5)} 50%{box-shadow:0 0 0 5px rgba(239,68,68,0)} }
        #sidebar-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:30; }
        @media(max-width:768px) {
            #sidebar { position:fixed; top:0; bottom:0; left:0; z-index:40; transform:translateX(-100%); }
            #sidebar.mobile-open { transform:translateX(0); }
            #sidebar-overlay.show { display:block; }
        }
    </style>
    {{ $styles ?? '' }}
</head>

<body class="h-full bg-slate-50"
      x-data="{ sidebarCollapsed: false, mobileOpen: false }"
      @keydown.escape.window="mobileOpen = false">

    <div id="sidebar-overlay" :class="mobileOpen ? 'show' : ''" @click="mobileOpen = false"></div>

    <div class="flex h-full min-h-screen">

        {{-- SIDEBAR --}}
        <aside id="sidebar"
               class="w-60 flex-shrink-0 flex flex-col bg-slate-900"
               :class="{ collapsed: sidebarCollapsed, 'mobile-open': mobileOpen }">

            {{-- Logo --}}
            <div class="sidebar-header flex items-center gap-3 h-16 px-5 border-b border-slate-800 flex-shrink-0">
                <div>   
                    <img src="../images/logo-png.png" class="w-6" alt="Logo">

                </div>
                <div class="sidebar-logo-text leading-tight">
                    <div class="text-white font-bold text-sm">RUMANGSA</div>
                    <div class="text-slate-400 text-[10px]">Imigrasi Madiun</div>
                </div>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">

                {{-- Utama --}}
                <p class="nav-section-label text-[10px] font-bold text-slate-600 uppercase tracking-widest px-3 mb-2 mt-1">Utama</p>

                <a href="{{ route('dashboard') }}"
                   class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="nav-label">Dashboard</span>
                </a>

                @if (Route::has('pengaduan.index'))
                    <a href="{{ route('pengaduan.index') }}"
                       class="nav-item {{ request()->routeIs('pengaduan.index') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="nav-label">Data Pengaduan</span>
                    </a>
                @endif

                {{-- SLA dengan badge counter --}}
                @php
                    $slaOverCount = \App\Models\Pengaduan::where('status','!=','selesai')
                        ->where('deadline_tindak_lanjut','<',now())->count();
                @endphp
                <a href="{{ route('pengaduan.sla') }}"
                   class="nav-item {{ request()->routeIs('pengaduan.sla') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="nav-label">Monitoring SLA</span>
                    @if ($slaOverCount > 0)
                        <span class="nav-label ml-auto inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold">
                            {{ $slaOverCount > 99 ? '99+' : $slaOverCount }}
                        </span>
                    @endif
                </a>

                @if (in_array(auth()->user()->profile->role, ['tikkim', 'seksi']))
                    <a href="{{ route('rekapitulasi.index') }}"
                       class="nav-item {{ request()->routeIs('rekapitulasi.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="nav-label">Rekapitulasi</span>
                    </a>
                @endif

                {{-- Layanan --}}
                <p class="nav-section-label text-[10px] font-bold text-slate-600 uppercase tracking-widest px-3 mb-2 mt-5">Layanan</p>

                <a href="{{ route('pengaduan.create') }}"
                   class="nav-item {{ request()->routeIs('pengaduan.create') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span class="nav-label">Form Pengaduan</span>
                </a>

                <a href="{{ route('pengaduan.track') }}"
                   class="nav-item {{ request()->routeIs('pengaduan.track') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span class="nav-label">Lacak Tiket</span>
                </a>

                {{-- Akun --}}
                <p class="nav-section-label text-[10px] font-bold text-slate-600 uppercase tracking-widest px-3 mb-2 mt-5">Akun</p>

                <a href="{{ route('profile.edit') }}"
                   class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="nav-label">Profil Saya</span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-item nav-logout w-full text-left">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="nav-label">Keluar</span>
                    </button>
                </form>
            </nav>

            {{-- User footer --}}
            <div class="border-t border-slate-800 p-4 flex-shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="user-meta min-w-0">
                        <div class="text-white text-xs font-semibold truncate">{{ auth()->user()->name }}</div>
                        <div class="text-slate-400 text-[10px] uppercase tracking-wide truncate">
                            {{ auth()->user()->profile->role ?? 'User' }}
                            @if (auth()->user()->profile->seksi)
                                · {{ auth()->user()->profile->seksi }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        {{-- MAIN --}}
        <div id="main-content" class="flex-1 flex flex-col min-w-0 overflow-hidden">

            {{-- TOPBAR --}}
            <header class="h-16 bg-white border-b border-slate-100 flex items-center justify-between px-5 flex-shrink-0 sticky top-0 z-20">
                <div class="flex items-center gap-3">
                    <button @click="sidebarCollapsed = !sidebarCollapsed"
                            class="hidden md:flex w-8 h-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <button @click="mobileOpen = !mobileOpen"
                            class="flex md:hidden w-8 h-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div class="hidden sm:block">
                        <h1 class="text-sm font-bold text-slate-800">{{ $header ?? 'Dashboard' }}</h1>
                        <p class="text-xs text-slate-400">{{ now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('pengaduan.sla') }}"
                       class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 transition relative">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @if ($slaOverCount > 0)
                            <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                        @endif
                    </a>
                    <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                </div>
            </header>

            {{-- Flash --}}
            @if (session('success'))
                <div class="mx-5 mt-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mx-5 mt-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            <main class="flex-1 overflow-y-auto">
                <div class="page-content">{{ $slot }}</div>
            </main>
        </div>
    </div>

    {{ $scripts ?? '' }}
</body>
</html>