{{-- resources/views/dashboard/kakanim.blade.php --}}
<x-layouts.dashboard>
<x-slot name="header">Dashboard Pimpinan</x-slot>

<x-slot name="styles">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
    :root {
        --red:    #dc2626;
        --red-lt: #fef2f2;
        --red-bd: #fecaca;
        --amb:    #d97706;
        --amb-lt: #fffbeb;
        --amb-bd: #fde68a;
        --grn:    #059669;
        --grn-lt: #f0fdf4;
        --grn-bd: #bbf7d0;
        --ink:    #0f172a;
        --muted:  #64748b;
        --ghost:  #f8fafc;
        --border: #e2e8f0;
        --card:   #ffffff;
        --radius: 18px;
        --radius-sm: 10px;
        --font-head: 'DM Sans', sans-serif;
        --font-body: 'DM Sans', sans-serif;
    }

    * { font-family: var(--font-body) }

    /* ── KPI cards ── */
    .kpi-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 22px;
        transition: box-shadow .2s, transform .2s;
    }
    .kpi-card:hover { box-shadow: 0 8px 28px rgba(0,0,0,.07); transform: translateY(-2px) }
    .kpi-val { font-family: var(--font-head); font-size: 2.4rem; font-weight: 800; line-height: 1; letter-spacing: -.03em }

    /* ── Chart wrapper ── */
    .chart-wrap {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 24px;
    }
    .chart-head { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 20px; gap: 12px }
    .chart-head h3 { font-family: var(--font-head); font-size: 14px; font-weight: 700; color: var(--ink) }
    .chart-head p  { font-size: 12px; color: var(--muted); margin-top: 3px }

    /* ── Seksi row ── */
    .seksi-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--ghost) }
    .seksi-row:last-child { border-bottom: none }
    .seksi-bar-bg { flex: 1; height: 5px; background: var(--ghost); border-radius: 99px; overflow: hidden }
    .seksi-bar-fill { height: 100%; border-radius: 99px; transition: width .6s cubic-bezier(.4,0,.2,1) }

    /* ── Status badges ── */
    .badge { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 700;
             letter-spacing: .04em; text-transform: uppercase; border-radius: 8px; padding: 3px 10px; border: 1px solid }
    .badge-ok   { background: var(--grn-lt); color: var(--grn);   border-color: var(--grn-bd) }
    .badge-warn { background: var(--amb-lt); color: var(--amb);   border-color: var(--amb-bd) }
    .badge-over { background: var(--red-lt); color: var(--red);   border-color: var(--red-bd) }

    /* ── SLA Alert cards ── */
    .sla-card {
        background: var(--card);
        border: 1.5px solid var(--border);
        border-radius: 20px;
        padding: 22px;
        cursor: pointer;
        transition: all .2s ease;
        position: relative;
        overflow: hidden;
    }
    .sla-card::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 20px;
        opacity: 0;
        transition: opacity .2s;
    }
    .sla-card:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(0,0,0,.1) }
    .sla-card-danger { border-color: var(--red-bd) }
    .sla-card-danger:hover { box-shadow: 0 12px 32px rgba(220,38,38,.15) }
    .sla-card-warn   { border-color: var(--amb-bd) }
    .sla-card-warn:hover { box-shadow: 0 12px 32px rgba(217,119,6,.12) }

    .metric-pill {
        display: flex; flex-direction: column;
        border-radius: 14px; padding: 14px 16px;
        border: 1px solid;
    }
    .metric-pill-danger { background: var(--red-lt); border-color: var(--red-bd) }
    .metric-pill-warn   { background: var(--amb-lt); border-color: var(--amb-bd) }
    .metric-num { font-family: var(--font-head); font-size: 2rem; font-weight: 800; line-height: 1 }

    /* ── Modal base ── */
    .modal-overlay {
        position: fixed; inset: 0; z-index: 60;
        background: rgba(15,23,42,.55);
        backdrop-filter: blur(4px);
        display: flex; align-items: center; justify-content: center;
        padding: 20px;
    }
    .modal-box {
        background: var(--card);
        border-radius: 24px;
        width: 100%;
        max-width: 860px;
        max-height: 90vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 32px 80px rgba(0,0,0,.2);
    }
    .modal-header {
        padding: 22px 28px;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
        flex-shrink: 0;
    }
    .modal-body {
        overflow-y: auto;
        flex: 1;
        padding: 24px 28px;
    }

    /* ── Ticket row in modal ── */
    .ticket-row {
        display: flex; align-items: center; gap: 14px;
        padding: 14px 16px;
        border: 1.5px solid var(--border);
        border-radius: 14px;
        cursor: pointer;
        transition: all .15s;
        background: white;
    }
    .ticket-row:hover { border-color: #94a3b8; box-shadow: 0 4px 14px rgba(0,0,0,.08); transform: translateX(2px) }
    .ticket-row-danger { border-left: 4px solid var(--red) }
    .ticket-row-warning { border-left: 4px solid var(--amb) }

    /* ── Slide-over (ticket detail) ── */
    .slideover {
        position: fixed;
        inset-y: 0; right: 0; z-index: 80;
        width: min(600px, 100vw);
        background: var(--card);
        box-shadow: -8px 0 48px rgba(0,0,0,.15);
        overflow-y: auto;
        display: flex; flex-direction: column;
    }
    .slideover-header {
        padding: 22px 28px;
        border-bottom: 1px solid var(--border);
        position: sticky; top: 0;
        background: white; z-index: 1;
        display: flex; align-items: center; justify-content: space-between;
    }

    /* ── Alert form modal ── */
    .alert-form-modal {
        position: fixed; inset: 0; z-index: 90;
        background: rgba(15,23,42,.6);
        backdrop-filter: blur(6px);
        display: flex; align-items: center; justify-content: center;
        padding: 20px;
    }
    .alert-form-box {
        background: white;
        border-radius: 24px;
        width: 100%;
        max-width: 540px;
        box-shadow: 0 24px 64px rgba(0,0,0,.18);
        overflow: hidden;
    }

    /* ── Icon close button ── */
    .btn-close {
        width: 36px; height: 36px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        background: var(--ghost); border: 1px solid var(--border);
        font-size: 16px; color: var(--muted); cursor: pointer;
        transition: all .15s; flex-shrink: 0;
    }
    .btn-close:hover { background: var(--border); color: var(--ink) }

    /* ── Tren indicators ── */
    .tren-up   { color: var(--red) }
    .tren-down { color: var(--grn) }
    .tren-flat { color: var(--muted) }

    /* ── Riwayat rows ── */
    .riwayat-row { padding: 10px 0; border-bottom: 1px solid var(--ghost); display: flex; gap: 10px; align-items: flex-start }
    .riwayat-row:last-child { border-bottom: none }

    /* ── Loading skeleton ── */
    .skeleton { background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
                background-size: 200% 100%; animation: shimmer 1.4s infinite; border-radius: 10px }
    @keyframes shimmer { 0% { background-position: 200% 0 } 100% { background-position: -200% 0 } }

    /* ── Send warning button ── */
    .btn-warn-danger { background: var(--red); color: white; border: none; border-radius: 10px;
                       padding: 8px 16px; font-size: 12px; font-weight: 700; cursor: pointer;
                       transition: all .15s; letter-spacing: .02em }
    .btn-warn-danger:hover { background: #b91c1c; transform: translateY(-1px) }
    .btn-warn-amber  { background: var(--amb); color: white; border: none; border-radius: 10px;
                       padding: 8px 16px; font-size: 12px; font-weight: 700; cursor: pointer;
                       transition: all .15s; letter-spacing: .02em }
    .btn-warn-amber:hover { background: #b45309; transform: translateY(-1px) }

    @media print {
        #sidebar, header, .no-print { display: none !important }
        #main-content { margin: 0 !important }
    }
</style>
</x-slot>

{{-- ══════════════════════════════════════════════════════════════
     ROOT ALPINE COMPONENT
══════════════════════════════════════════════════════════════ --}}
<div
    x-data="kakanimDashboard()"
    class="p-6 space-y-6 max-w-[1400px]"
>

    {{-- HEADER ─────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">
                Laporan Strategis Layanan Pengaduan
            </h2>
            <p class="text-xs text-slate-400 mt-1">
                Kantor Imigrasi Kelas II Non TPI Madiun &nbsp;·&nbsp; Periode {{ $tahun }}
            </p>
        </div>
        <div class="flex items-center gap-3 no-print">
            <form method="GET" action="{{ route('kakanim.dashboard') }}">
                <div class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-slate-500">Tahun:</label>
                    <select name="tahun" onchange="this.form.submit()"
                        class="text-sm border border-slate-200 rounded-xl px-3 py-2 bg-white font-medium
                               focus:outline-none focus:ring-2 focus:ring-blue-100 transition-all">
                        @foreach ($tahunList as $t)
                            <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
            <button onclick="window.print()"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold
                       border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-50 transition active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak
            </button>
        </div>
    </div>

    {{-- ────────────────────────────────────────────────────────
         SUCCESS BANNER
    ──────────────────────────────────────────────────────────── --}}
    @if (session('success'))
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 rounded-2xl px-5 py-4 text-sm text-emerald-700 font-medium mt-4">
            <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- ────────────────────────────────────────────────────────
         KPI CARDS
    ──────────────────────────────────────────────────────────── --}}
    @php
        $avgHari   = round($avgWaktu->avg_hari ?? 0, 1);
        $slaStatus = $avgHari <= 3 ? 'ok' : ($avgHari <= 4 ? 'warn' : 'over');
        $slaLabel  = ['ok'=>'Sesuai Target','warn'=>'Mendekati Batas','over'=>'Melampaui Target'][$slaStatus];
        $trenClass = $tren > 0 ? 'tren-up' : ($tren < 0 ? 'tren-down' : 'tren-flat');
        $trenArrow = $tren > 0 ? '↑' : ($tren < 0 ? '↓' : '→');
    @endphp

    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mt-6">

        {{-- Total --}}
        <div class="kpi-card">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                    </svg>
                </div>
                <span class="text-xs font-bold {{ $trenClass }}">{{ $trenArrow }} {{ abs($tren) }}%</span>
            </div>
            <div class="kpi-val text-slate-800">{{ number_format($scorecard->total) }}</div>
            <p class="text-xs text-slate-400 mt-1.5 font-medium">Total pengaduan masuk</p>
            <p class="text-[10px] text-slate-300 mt-0.5">{{ $scorecard->masuk_bulan_ini }} aduan bulan ini</p>
        </div>

        {{-- Penyelesaian --}}
        <div class="kpi-card">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center mb-4">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="kpi-val text-emerald-600">{{ $scorecard->pct_selesai ?? 0 }}%</div>
            <p class="text-xs text-slate-400 mt-1.5 font-medium">Tingkat penyelesaian</p>
            <p class="text-[10px] text-slate-300 mt-0.5">{{ number_format($scorecard->selesai) }} dari {{ number_format($scorecard->total) }} aduan</p>
        </div>

        {{-- Avg waktu --}}
        <div class="kpi-card">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-xl {{ $slaStatus==='ok'?'bg-emerald-50':($slaStatus==='warn'?'bg-amber-50':'bg-red-50') }} flex items-center justify-center">
                    <svg class="w-5 h-5 {{ $slaStatus==='ok'?'text-emerald-600':($slaStatus==='warn'?'text-amber-600':'text-red-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="badge badge-{{ $slaStatus }}">{{ $slaLabel }}</span>
            </div>
            <div class="kpi-val text-{{ $slaStatus==='ok'?'emerald':($slaStatus==='warn'?'amber':'red') }}-600">{{ $avgHari }}</div>
            <p class="text-xs text-slate-400 mt-1.5 font-medium">Rata-rata hari penyelesaian</p>
            <p class="text-[10px] text-slate-300 mt-0.5">Target ≤3 hari · {{ number_format($avgWaktu->sampel ?? 0) }} sampel</p>
        </div>

        {{-- Over SLA --}}
        <div class="kpi-card {{ $scorecard->over_sla > 0 ? 'border-red-100' : '' }}">
            <div class="w-10 h-10 rounded-xl {{ $scorecard->over_sla>0?'bg-red-50':'bg-slate-100' }} flex items-center justify-center mb-4">
                <svg class="w-5 h-5 {{ $scorecard->over_sla>0?'text-red-600':'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="kpi-val {{ $scorecard->over_sla>0?'text-red-600':'text-slate-800' }}">{{ $scorecard->over_sla }}</div>
            <p class="text-xs text-slate-400 mt-1.5 font-medium">Pengaduan melewati SLA</p>
            <p class="text-[10px] text-slate-300 mt-0.5">Belum selesai &amp; &gt;3 hari</p>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════
         LEADERSHIP SLA MONITOR
    ════════════════════════════════════════════════════════════ --}}
    @if (count($slaAlert) > 0)
    <div class="chart-wrap mt-6">

        {{-- Panel header --}}
        <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-red-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800 tracking-tight">Leadership SLA Monitor</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Seksi yang membutuhkan tindakan segera. Klik kartu untuk melihat detail tiket.</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-center px-4 py-2 rounded-2xl bg-red-50 border border-red-200">
                    <div class="text-[10px] font-bold text-red-500 uppercase tracking-widest">Total Over SLA</div>
                    <div class="text-2xl font-black text-red-700 kpi-val">{{ collect($slaAlert)->sum('jumlah_over') }}</div>
                </div>
                <div class="text-center px-4 py-2 rounded-2xl bg-amber-50 border border-amber-200">
                    <div class="text-[10px] font-bold text-amber-600 uppercase tracking-widest">Approaching</div>
                    <div class="text-2xl font-black text-amber-700 kpi-val">{{ collect($slaAlert)->sum('jumlah_warn') }}</div>
                </div>
            </div>
        </div>

        {{-- Section cards grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach ($slaAlert as $s)
                @php $isDanger = $s->jumlah_over > 0; @endphp

                <div
                    class="sla-card {{ $isDanger ? 'sla-card-danger' : 'sla-card-warn' }}"
                    @click="openSectionModal('{{ $s->seksi }}')"
                >
                    {{-- Card header --}}
                    <div class="flex items-start justify-between mb-4">
                        <div class="min-w-0">
                            <p class="text-[10px] uppercase tracking-[.18em] text-slate-400 font-bold mb-0.5">Seksi</p>
                            <h4 class="text-base font-bold text-slate-800 tracking-tight font-sans truncate">
                                {{ $s->seksi }}
                            </h4>
                        </div>
                        <span class="badge {{ $isDanger ? 'badge-over' : 'badge-warn' }} flex-shrink-0">
                            {{ $isDanger ? 'HIGH RISK' : 'WARNING' }}
                        </span>
                    </div>

                    {{-- Metric pills --}}
                    <div class="flex gap-3 mb-5">
                        @if ($s->jumlah_over > 0)
                            <div class="metric-pill metric-pill-danger flex-1">
                                <span class="metric-num text-red-700">{{ $s->jumlah_over }}</span>
                                <span class="text-[11px] font-bold text-red-600 mt-1">Over SLA</span>
                            </div>
                        @endif
                        @if ($s->jumlah_warn > 0)
                            <div class="metric-pill metric-pill-warn flex-1">
                                <span class="metric-num text-amber-700">{{ $s->jumlah_warn }}</span>
                                <span class="text-[11px] font-bold text-amber-600 mt-1">Approaching</span>
                            </div>
                        @endif
                    </div>

                    {{-- Card footer --}}
                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <span class="text-[11px] text-slate-400 flex items-center gap-1.5 font-medium">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Detail Tiket
                        </span>
                        <button
                            class="{{ $isDanger ? 'btn-warn-danger' : 'btn-warn-amber' }}"
                            @click.stop="openAlertForm('{{ $s->seksi }}', '{{ $isDanger ? 'over_sla' : 'warn_sla' }}')"
                        >
                            ✉ Peringatan
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ────────────────────────────────────────────────────────
         TREN BULANAN
    ──────────────────────────────────────────────────────────── --}}
    <div class="chart-wrap mt-6">
        <div class="chart-head">
            <div>
                <h3 class="text-base font-bold text-slate-800">Tren Pengaduan Bulanan {{ $tahun }}</h3>
                <p class="text-xs text-slate-400 mt-0.5">Pengaduan masuk vs terselesaikan per bulan</p>
            </div>
            <div class="flex items-center gap-4 text-xs font-semibold text-slate-500">
                <span class="flex items-center gap-1.5"><span class="inline-block w-4 h-1 rounded bg-blue-500"></span>Masuk</span>
                <span class="flex items-center gap-1.5"><span class="inline-block w-4 h-1 rounded bg-emerald-500"></span>Selesai</span>
            </div>
        </div>
        <canvas id="chartTren" style="max-height:240px"></canvas>
    </div>

    {{-- ────────────────────────────────────────────────────────
         PERFORMA SEKSI + AVG WAKTU
    ──────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">

        <div class="chart-wrap">
            <div class="chart-head">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Performa per Seksi</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Volume dan tingkat penyelesaian</p>
                </div>
            </div>
            @php $maxTotal = collect($distribusiSeksi)->max('total') ?: 1; @endphp
            <div class="space-y-1">
                @forelse ($distribusiSeksi as $s)
                    @php
                        $pct    = (float) $s->pct_selesai;
                        $barClr = $pct >= 80 ? '#10b981' : ($pct >= 50 ? '#3b82f6' : '#f59e0b');
                        $barW   = round($s->total / $maxTotal * 100);
                    @endphp
                    <div class="seksi-row">
                        <div style="width:140px; flex-shrink:0;">
                            <div class="text-xs font-bold text-slate-700 truncate font-sans">{{ $s->seksi }}</div>
                            <div class="text-[10px] font-medium text-slate-400 mt-0.5">{{ $s->total }} aduan</div>
                        </div>
                        <div class="seksi-bar-bg">
                            <div class="seksi-bar-fill" style="width:{{ $barW }}%; background:{{ $barClr }}"></div>
                        </div>
                        <span class="text-xs font-extrabold" style="width:46px; text-align:right; color:{{ $barClr }}">{{ $pct }}%</span>
                        @if ($s->over_sla > 0)
                            <span class="text-[10px] font-bold text-red-500 ml-1 bg-red-50 px-1.5 py-0.5 rounded-md border border-red-100">{{ $s->over_sla }}⚠</span>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-6 text-center font-medium">Belum ada data distribusi seksi</p>
                @endforelse
            </div>
            <div class="mt-5"><canvas id="chartSeksi" style="max-height:160px"></canvas></div>
        </div>

        <div class="chart-wrap">
            <div class="chart-head">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Rata-rata Waktu Penyelesaian</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Dalam satuan hari · Target internal ≤ 3 hari</p>
                </div>
                <span class="badge badge-{{ $slaStatus }} flex-shrink-0">{{ $avgHari }} hari</span>
            </div>
            <canvas id="chartAvgWaktu" style="max-height:200px"></canvas>
            <div class="mt-4 pt-4 border-t border-slate-100">
                <div class="grid grid-cols-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-50">
                    <span>Seksi</span><span class="text-center">Avg</span><span class="text-right">Sampel (n)</span>
                </div>
                @foreach ($avgWaktuSeksi as $s)
                    @php $hari = round($s->avg_hari,1); $w=$hari<=3?'text-emerald-600':($hari<=4?'text-amber-600':'text-red-600'); @endphp
                    <div class="grid grid-cols-3 py-2 border-b border-slate-50 text-xs items-center">
                        <span class="text-slate-700 font-bold truncate font-sans">{{ $s->seksi }}</span>
                        <span class="text-center font-extrabold {{ $w }}">{{ $hari }} hr</span>
                        <span class="text-right text-slate-400 font-medium">{{ $s->sampel }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ────────────────────────────────────────────────────────
         KANAL + JENIS + RINGKASAN
    ──────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">

        <div class="chart-wrap">
            <div class="chart-head">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Distribusi Kanal Pengaduan</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Saluran masuk pengaduan terbanyak</p>
                </div>
            </div>
            <div class="flex items-center gap-6">
                <div style="width:170px; height:170px; flex-shrink:0;"><canvas id="chartKanal"></canvas></div>
                <div class="flex-1 space-y-2.5">
                    @php $kanalClr=['#3b82f6','#10b981','#8b5cf6','#f59e0b','#ef4444','#06b6d4','#ec4899']; @endphp
                    @foreach ($distribusiKanal as $i => $k)
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:{{ $kanalClr[$i%count($kanalClr)] }}"></span>
                                <span class="text-slate-600 font-semibold truncate">{{ $k->kanal }}</span>
                            </div>
                            <span class="font-bold text-slate-800 ml-2 flex-shrink-0">{{ $k->total }} <span class="font-normal text-slate-400">({{ $k->pct }}%)</span></span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="chart-wrap">
            <div class="chart-head">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Jenis Layanan &amp; Ringkasan Eksekutif</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Klasifikasi penanganan berkas pengaduan</p>
                </div>
            </div>
            <div class="flex items-center gap-6 mb-5">
                <div style="width:120px; height:120px; flex-shrink:0;"><canvas id="chartJenis"></canvas></div>
                <div class="flex-1 space-y-3.5">
                    @php $jenisClr=['#3b82f6','#f59e0b']; $totalJenis=collect($distribusiJenis)->sum('total')?:1; @endphp
                    @foreach ($distribusiJenis as $i => $j)
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full" style="background:{{ $jenisClr[$i]??'#94a3b8' }}"></span>
                                    <span class="text-slate-600 font-bold capitalize">{{ $j->jenis_layanan }}</span>
                                </div>
                                <span class="font-extrabold text-slate-800">{{ $j->total }}</span>
                            </div>
                            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500" style="width:{{ round($j->total/$totalJenis*100) }}%; background:{{ $jenisClr[$i]??'#94a3b8' }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100/80 space-y-2">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Ringkasan Eksekutif pimpinan</p>
                <div class="space-y-2 text-xs text-slate-600 leading-relaxed font-medium">
                    <p>📊 Pada tahun {{ $tahun }}, tercatat total <strong>{{ number_format($scorecard->total) }}</strong> berkas pengaduan. Tingkat penyelesaian mencapai <strong class="{{ ($scorecard->pct_selesai??0)>=80?'text-emerald-600':'text-amber-600' }}">{{ $scorecard->pct_selesai??0 }}%</strong>.</p>
                    <p>⏱ Kecepatan penanganan rata-rata <strong class="text-{{ $slaStatus==='ok'?'emerald':($slaStatus==='warn'?'amber':'red') }}-600">{{ $avgHari }} hari</strong> per berkas, status ini dinyatakan <strong>{{ $slaStatus==='ok'?'memenuhi target':'melebihi ambang batas' }}</strong> ketentuan target RAP (≤3 hari).</p>
                    @if ($scorecard->over_sla > 0)
                        <p>⚠️ Ditemukan <strong class="text-red-600">{{ $scorecard->over_sla }}</strong> berkas pengaduan aktif yang terdeteksi melewati batas waktu SLA. Diperlukan tindakan intervensi segera.</p>
                    @else
                        <p>✅ Seluruh berkas berjalan terpantau aman tanpa ada kendala keterlambatan SLA.</p>
                    @endif
                    @php $top = collect($distribusiSeksi)->first(); @endphp
                    @if ($top)
                        <p>🏢 Unit Kerja dengan volume laporan tertinggi berada pada <strong>{{ $top->seksi }}</strong> (Menerima {{ $top->total }} aduan, dengan rasio penyelesaian berkas {{ $top->pct_selesai }}%).</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ────────────────────────────────────────────────────────
         RIWAYAT PERINGATAN
    ──────────────────────────────────────────────────────────── --}}
    @if ($riwayatAlert->isNotEmpty())
    <div class="chart-wrap mt-6">
        <div class="chart-head">
            <div>
                <h3 class="text-base font-bold text-slate-800">Riwayat Peringatan Terkirim</h3>
                <p class="text-xs text-slate-400 mt-0.5">Log notifikasi instruksi pimpinan kepada Kepala Seksi terkait</p>
            </div>
        </div>
        <div class="space-y-0.5 max-h-60 overflow-y-auto pr-1">
            @foreach ($riwayatAlert as $notif)
                <div class="riwayat-row items-center">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 bg-red-50 border border-red-100">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0 ml-1">
                        <div class="text-xs font-bold text-slate-700 truncate">{{ $notif->judul }}</div>
                        <div class="text-[10px] font-medium text-slate-400 mt-0.5 truncate">{{ Str::limit($notif->pesan, 90) }}</div>
                    </div>
                    <div class="text-[10px] text-slate-400 flex-shrink-0 text-right pl-3">
                        <div class="font-bold text-slate-600 font-sans">{{ $notif->target_seksi }}</div>
                        <div class="font-medium text-slate-400/80 mt-0.5">{{ $notif->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ════════════════════════════════════════════════════════
         LAYER 1 — SECTION TICKET LIST MODAL (FIXED OVER SCREEN)
         Triggered when a section card is clicked.
         Shows a clean, scrollable list of problematic tickets.
         ════════════════════════════════════════════════════════════ --}}
    <template x-teleport="body">
        <div
            x-show="showSectionModal"
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="display:none"
            {{-- Perbaikan Utama: Mengunci viewport penuh layar dan z-index di atas sidebar --}}
            class="fixed inset-0 z-[9990] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 w-screen h-screen overflow-hidden select-none"
            @click.self="closeSectionModal()"
        >
            <div
                x-transition:enter="transition ease-out duration-250"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                {{-- Mengatur tinggi maksimal box agar scrollable secara internal saja, tidak merusak halaman luar --}}
                class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl max-h-[85vh] flex flex-col overflow-hidden"
            >
                {{-- Header --}}
                <div class="px-8 py-6 border-b border-slate-100 flex items-start justify-between gap-4 flex-shrink-0">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Seksi</span>
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                                :class="sectionSeverity === 'danger' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700'"
                                x-text="sectionSeverity === 'danger' ? 'HIGH RISK' : 'WARNING'"
                            ></span>
                        </div>
                        <h3 class="text-2xl font-black text-slate-800" style="font-family:'DM',sans-serif" x-text="selectedSection"></h3>
                        <p class="text-sm text-slate-500 mt-0.5">
                            Tiket pengaduan yang memerlukan perhatian pimpinan.
                            <span x-show="!loading" x-text="`(${tickets.length} tiket)`" class="font-semibold text-slate-700"></span>
                        </p>
                    </div>
                    <button class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition flex items-center justify-center font-bold text-sm" @click="closeSectionModal()">✕</button>
                </div>

                {{-- Filter tabs --}}
                <div class="px-8 pt-4 pb-0 flex items-center gap-4 border-b border-slate-100 flex-shrink-0 bg-slate-50/50">
                    <button
                        @click="ticketFilter = 'all'"
                        :class="ticketFilter === 'all' ? 'border-b-2 border-slate-800 text-slate-800 font-bold' : 'text-slate-400 hover:text-slate-600'"
                        class="pb-3 text-sm transition-all"
                    >
                        Semua <span x-text="`(${tickets.length})`" class="text-xs opacity-70"></span>
                    </button>
                    <button 
                        @click="ticketFilter = 'danger'" 
                        :class="ticketFilter === 'danger' ? 'border-b-2 border-red-600 text-red-600 font-bold' : 'text-slate-400 hover:text-red-500'"
                        class="pb-3 text-sm transition-all"
                    >
                        Over SLA (<span x-text="tickets.filter(t => t.severity === 'danger').length" class="text-xs"></span>)
                    </button>
                    <button
                        @click="ticketFilter = 'warning'"
                        :class="ticketFilter === 'warning' ? 'border-b-2 border-amber-600 text-amber-600 font-bold' : 'text-slate-400 hover:text-amber-500'"
                        class="pb-3 text-sm transition-all"
                    >
                        Approaching (<span x-text="tickets.filter(t => t.severity === 'warning').length" class="text-xs"></span>)
                    </button>
                </div>

                {{-- Body (Scrollable Container) --}}
                <div class="p-8 overflow-y-auto flex-1 space-y-4 select-text">

                    {{-- Loading skeleton --}}
                    <template x-if="loading">
                        <div class="space-y-3">
                            <template x-for="i in 4" :key="i">
                                <div class="w-full h-20 rounded-2xl bg-slate-100 animate-pulse"></div>
                            </template>
                        </div>
                    </template>

                    {{-- Empty state --}}
                    <template x-if="!loading && filteredTickets.length === 0">
                        <div class="py-16 text-center text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm font-medium">Tidak ada tiket pada filter ini.</p>
                        </div>
                    </template>

                    {{-- Ticket list --}}
                    <template x-if="!loading && filteredTickets.length > 0">
                        <div class="space-y-3">
                            <template x-for="ticket in filteredTickets" :key="ticket.id">
                                <div
                                    class="flex items-center justify-between p-5 rounded-2xl border transition-all cursor-pointer transform hover:-translate-y-0.5 hover:shadow-md"
                                    :class="ticket.severity === 'danger' ? 'bg-red-50/40 border-red-100 hover:bg-red-50' : 'bg-amber-50/40 border-amber-100 hover:bg-amber-50'"
                                    @click="openTicketDetail(ticket.id)"
                                >
                                    {{-- Left side: Status badge & Info text --}}
                                    <div class="flex items-center gap-4 min-w-0 flex-1">
                                        <div class="flex-shrink-0">
                                            <span
                                                class="px-2.5 py-1 rounded-xl text-[10px] font-bold uppercase tracking-wider"
                                                :class="ticket.severity === 'danger' ? 'bg-red-200 text-red-800' : 'bg-amber-200 text-amber-800'"
                                                x-text="ticket.sla_status"
                                            ></span>
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="font-mono text-sm font-black text-slate-800 tracking-tight" x-text="ticket.nomor_tiket"></div>
                                            <div class="text-xs font-semibold text-slate-600 mt-0.5 truncate" x-text="ticket.nama"></div>
                                            <div class="text-[10px] text-slate-400 mt-0.5" x-text="ticket.kategori_pengaduan"></div>
                                        </div>
                                    </div>

                                    {{-- Right side: Deadline dynamic counter --}}
                                    <div class="text-right flex-shrink-0 flex items-center gap-4 ml-4">
                                        <div>
                                            <div class="text-xs font-bold" :class="ticket.severity === 'danger' ? 'text-red-600' : 'text-amber-600'" x-text="ticket.deadline_human"></div>
                                            <div class="text-[10px] text-slate-400 mt-0.5">
                                                <span x-show="ticket.days_over" x-text="`${ticket.days_over} hari lewat`" class="text-red-500 font-bold"></span>
                                                <span x-show="ticket.hours_left" x-text="`${ticket.hours_left}j tersisa`" class="text-amber-500 font-bold"></span>
                                            </div>
                                        </div>

                                        {{-- Arrow icon indicator --}}
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>

    {{-- ════════════════════════════════════════════════════════
         LAYER 2 — TICKET DETAIL SLIDE-OVER (TELEPORTED & FIXED)
         Triggered when a ticket row in the list modal is clicked.
         Shows full complaint details + SLA reasoning.
         ════════════════════════════════════════════════════════════ --}}
    <template x-teleport="body">
        <div
            x-show="showTicketDetail"
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="opacity-0 translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            style="display:none"
            {{-- Mengunci posisi di kanan layar, h-screen penuh, dan z-index [9995] (di atas Layer 1) --}}
            class="fixed inset-y-0 right-0 z-[9995] w-full max-w-lg bg-white shadow-2xl border-l border-slate-200 flex flex-col h-screen overflow-hidden select-none"
        >
            {{-- Slideover Header --}}
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between flex-shrink-0 bg-slate-50/50">
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-slate-400 font-bold">Detail Pengaduan</p>
                    <p class="font-mono text-lg font-black text-slate-800" x-text="activeTicket.nomor_tiket ?? '—'"></p>
                </div>
                <div class="flex items-center gap-2">
                    {{-- Back to list --}}
                    <button
                        @click="showTicketDetail = false"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition px-3 py-2 rounded-xl hover:bg-slate-100 border border-slate-200"
                    >
                        ← Kembali ke daftar
                    </button>
                    <button class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 transition flex items-center justify-center text-xs" @click="closeAll()">✕</button>
                </div>
            </div>

            {{-- Body Scroll Container --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-6 select-text">
                
                {{-- Skeleton while loading --}}
                <template x-if="detailLoading">
                    <div class="space-y-4 animate-pulse">
                        <div class="h-16 bg-slate-100 rounded-2xl w-full"></div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="h-12 bg-slate-100 rounded-xl"></div>
                            <div class="h-12 bg-slate-100 rounded-xl"></div>
                        </div>
                        <div class="h-20 bg-slate-100 rounded-2xl w-full"></div>
                        <div class="h-32 bg-slate-100 rounded-2xl w-full"></div>
                    </div>
                </template>

                <template x-if="!detailLoading && activeTicket.id">
                    <div class="space-y-6">
                        {{-- SLA banner --}}
                        <div
                            class="rounded-2xl p-4 border flex items-start gap-3"
                            :class="activeTicket.severity === 'danger' ? 'bg-red-50 border-red-200' : 'bg-amber-50 border-amber-200'"
                        >
                            <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0"
                                 :class="activeTicket.severity === 'danger' ? 'bg-red-500' : 'bg-amber-500'"></div>
                            <div>
                                <p class="text-sm font-bold"
                                   :class="activeTicket.severity === 'danger' ? 'text-red-800' : 'text-amber-800'"
                                   x-text="activeTicket.sla_status"></p>
                                <p class="text-xs mt-1 leading-relaxed"
                                   :class="activeTicket.severity === 'danger' ? 'text-red-700' : 'text-amber-700'"
                                   x-text="activeTicket.sla_reason"></p>
                            </div>
                        </div>
                        {{-- ── LAYER 2: GRID DETAIL INFO BERKAS (SINKRON DENGAN KAKANIMCONTROLLER) ── --}}
                        <div class="space-y-4">
                            
                            {{-- Grid Atas: Info Utama (Pemohon, Status, Deadline) --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                                    <p class="text-[10px] uppercase tracking-widest text-slate-400 font-bold mb-1">Pemohon</p>
                                    <p class="font-semibold text-slate-800 text-sm truncate" x-text="activeTicket.nama ?? '—'"></p>
                                </div>
                                
                                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                                    <p class="text-[10px] uppercase tracking-widest text-slate-400 font-bold mb-1">Status</p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold capitalize mt-0.5" 
                                        :class="{
                                            'bg-amber-50 text-amber-700 border border-amber-100': activeTicket.status === 'pending',
                                            'bg-blue-50 text-blue-700 border border-blue-100': activeTicket.status === 'proses',
                                            'bg-purple-50 text-purple-700 border border-purple-100': activeTicket.status === 'diteruskan',
                                            'bg-green-50 text-green-700 border border-green-100': activeTicket.status === 'selesai'
                                        }"
                                        x-text="activeTicket.status ?? '—'"></span>
                                </div>
                                
                                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                                    <p class="text-[10px] uppercase tracking-widest text-slate-400 font-bold mb-1">Deadline</p>
                                    <p class="font-bold text-sm mt-0.5"
                                    :class="activeTicket.severity === 'danger' ? 'text-red-600' : 'text-amber-600'"
                                    x-text="activeTicket.deadline_human ?? '—'"></p>
                                </div>
                            </div>

                            {{-- Grid Tengah: Klasifikasi Sumber (Kanal & Kategori) --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kanal Pengaduan</span>
                                    <div class="text-xs font-bold text-slate-700 mt-1 font-sans" x-text="activeTicket.kanal_pengaduan || '—'"></div>
                                </div>
                                
                                <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kategori / Jenis Layanan</span>
                                    <div class="text-xs font-bold text-slate-700 mt-1 font-sans" x-text="activeTicket.jenis_layanan || '—'"></div>
                                </div>
                            </div>

                            {{-- Bagian Bawah: Isi Aduan Masyarakat --}}
                            <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Isi Aduan Masyarakat</span>
                                <p class="text-xs text-slate-600 leading-relaxed mt-2 font-medium bg-slate-50/80 p-3 rounded-lg border border-dashed border-slate-200" 
                                x-text="activeTicket.aduan || 'Tidak ada rincian isi aduan.'"></p>
                            </div>

                        </div>
                        {{-- Action button --}}
                        <button
                            class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3.5 px-4 text-sm rounded-2xl transition shadow-lg shadow-slate-900/10"
                            @click="openAlertForm(activeTicket.seksi_tujuan, activeTicket.severity === 'danger' ? 'over_sla' : 'warn_sla')">
                            ✉ Kirim Peringatan ke Seksi <span x-text="activeTicket.seksi_tujuan"></span>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </template>

    {{-- ════════════════════════════════════════════════════════
         LAYER 3 — SEND ALERT MODAL (TELEPORTED & FIXED OVER ALL)
         ════════════════════════════════════════════════════════════ --}}
    <template x-teleport="body">
        <div
            x-show="showAlertForm"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="display:none"
            {{-- Menggunakan z-[10000] tertinggi agar menutupi Layer 2 --}}
            class="fixed inset-0 z-[10000] bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4 w-screen h-screen overflow-hidden select-none"
            @click.self="showAlertForm = false"
        >
            <div
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="bg-white rounded-3xl shadow-2xl w-full max-w-xl overflow-hidden flex flex-col"
            >
                {{-- Header --}}
                <div class="px-6 py-5 border-b border-slate-100 flex items-start justify-between gap-4 bg-slate-50/50">
                    <div>
                        <h3 class="font-black text-slate-800 text-lg" style="font-family:'',sans-serif" x-text="alertFormTitle"></h3>
                        <p class="text-xs text-slate-500 mt-0.5" x-text="alertFormSubtitle"></p>
                    </div>
                    <button class="w-7 h-7 rounded-full bg-white shadow-sm text-slate-400 hover:text-slate-700 transition flex items-center justify-center text-xs border border-slate-100" @click="showAlertForm = false">✕</button>
                </div>

                {{-- Form Content --}}
                <form method="POST" action="{{ route('kakanim.alert') }}" class="p-6 space-y-5 select-text">
                    @csrf
                    <input type="hidden" name="target_seksi" :value="alertTargetSeksi">
                    <input type="hidden" name="tipe_alert"   :value="alertTipe">

                    {{-- Quick templates --}}
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Template Cepat</p>
                        <div class="flex flex-wrap gap-2">
                            <button type="button"
                                @click="alertPesan = 'Mohon segera ditangani — terdapat aduan yang telah melewati batas SLA. Harap berikan laporan penanganan hari ini.'"
                                class="text-xs px-3 py-2 rounded-xl border border-slate-200 text-slate-600 hover:border-slate-400 hover:bg-slate-50 transition font-medium">
                                Over SLA
                            </button>
                            <button type="button"
                                @click="alertPesan = 'Pengingat: terdapat aduan yang akan melewati tenggat dalam 24 jam. Mohon segera lakukan tindak lanjut.'"
                                class="text-xs px-3 py-2 rounded-xl border border-slate-200 text-slate-600 hover:border-slate-400 hover:bg-slate-50 transition font-medium">
                                Mendekati Deadline
                            </button>
                            <button type="button"
                                @click="alertPesan = 'Pimpinan meminta update progres penanganan aduan yang sedang berlangsung. Mohon segera dilaporkan.'"
                                class="text-xs px-3 py-2 rounded-xl border border-slate-200 text-slate-600 hover:border-slate-400 hover:bg-slate-50 transition font-medium">
                                Minta Update
                            </button>
                        </div>
                    </div>

                    {{-- Message textarea --}}
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest block mb-2">Pesan Peringatan</label>
                        <textarea
                            name="pesan"
                            x-model="alertPesan"
                            rows="5"
                            required minlength="10" maxlength="1000"
                            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 leading-relaxed
                                   focus:outline-none focus:ring-2 focus:ring-slate-800 focus:border-slate-800 resize-none bg-slate-50/50"
                            placeholder="Tulis pesan peringatan untuk seksi..."></textarea>
                        <div class="flex justify-between mt-1.5 text-[10px] text-slate-400">
                            <span>Min. 10 karakter</span>
                            <span x-text="`${alertPesan.length}/1000`" class="font-mono"></span>
                        </div>
                    </div>

                    {{-- Submit & Cancel --}}
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showAlertForm = false"
                            class="flex-1 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 transition">
                            Batal
                        </button>
                        <button type="submit"
                            :disabled="alertPesan.length < 10"
                            :class="alertTipe === 'over_sla'
                                ? 'bg-red-600 hover:bg-red-700 disabled:bg-red-200'
                                : 'bg-amber-500 hover:bg-amber-600 disabled:bg-amber-200'"
                            class="flex-1 py-3 rounded-2xl text-sm font-bold text-white transition disabled:cursor-not-allowed">
                            ✉ Kirim Peringatan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>{{-- end x-data --}}

<x-slot name="scripts">
<script>
// ── Alpine component ──────────────────────────────────────────
// ── Alpine component ──────────────────────────────────────────
function kakanimDashboard() {
    return {
        // ── Layer 1: Section modal ──────────────────────────
        showSectionModal: false,
        selectedSection:  '',
        sectionSeverity:  'danger',
        tickets:          [],
        loading:          false,
        ticketFilter:     'all',

        get filteredTickets() {
            if (this.ticketFilter === 'all') return this.tickets;
            
            const sekarang = new Date();

            if (this.ticketFilter === 'danger' || this.ticketFilter === 'over_sla') {
                return this.tickets.filter(t => {
                    return t.severity === 'danger' || (t.deadline_tindak_lanjut && new Date(t.deadline_tindak_lanjut) < sekarang);
                });
            }
            
            if (this.ticketFilter === 'warning' || this.ticketFilter === 'approaching') {
                return this.tickets.filter(t => {
                    if (t.severity === 'warning') return true;
                    
                    if (t.deadline_tindak_lanjut) {
                        const deadline = new Date(t.deadline_tindak_lanjut);
                        const sisaWaktu = deadline - sekarang;
                        return sisaWaktu > 0 && sisaWaktu < (24 * 60 * 60 * 1000);
                    }
                    return false;
                });
            }

            return this.tickets;
        },

        async openSectionModal(section) {
            this.selectedSection = section;
            this.ticketFilter     = 'all';
            this.showSectionModal = true;
            this.loading = true;
            this.tickets = []; 

            try {
                const res = await fetch(`/kakanim/section/${encodeURIComponent(section)}`);
                if (!res.ok) throw new Error('Network response was not ok');
                
                const resData = await res.json();
                this.tickets = resData;
                
                console.log('Tickets Loaded:', this.tickets);

                if (this.tickets.length > 0) {
                    this.sectionSeverity = this.tickets.some(t => t.severity === 'danger')
                        ? 'danger' : 'warning';
                } else {
                    this.sectionSeverity = 'warning';
                }

            } catch (err) {
                console.error('Failed to fetch tickets:', err);
                this.tickets = [];
            } finally {
                this.loading = false;
            }
        },

        closeSectionModal() {
            this.showSectionModal  = false;
            this.showTicketDetail  = false;
            this.tickets = [];
            this.activeTicket = {};
        },

        // ── Layer 2: Ticket detail slide-over ───────────────
        showTicketDetail: false,
        activeTicket:     {},
        detailLoading:    false,

        async openTicketDetail(ticketId) {
            // Evaluasi dini jika ticketId tidak valid
            if (!ticketId) {
                console.error('Peringatan: ticketId tidak ditemukan.');
                return;
            }

            this.showTicketDetail = true;
            this.detailLoading    = true;
            
            // Siapkan struktur dasar objek agar HTML tidak render 'undefined' saat loading
            this.activeTicket     = {
                nomor_tiket: 'Memuat...',
                nama: 'Memuat...',
                status: 'Memuat...',
                aduan: 'Sedang mengambil rincian data dari server...',
                kanal_pengaduan: '...',
                jenis_layanan: '...'
            };

            try {
                // Fetch data rincian dari server backend
                const res = await fetch(`/kakanim/ticket/${ticketId}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    }
                });
                
                if (!res.ok) {
                    throw new Error(`Server merespon dengan status: ${res.status} ${res.statusText}`);
                }
                
                const detailData = await res.json();
                console.log('Data Mentah dari Server:', detailData);
                
                // PETA UTAMA: Menyelaraskan nama properti dari Eloquent/PostgreSQL ke komponen Alpine
                this.activeTicket = {
                    id: detailData.id,
                    nomor_tiket: detailData.nomor_tiket || '—',
                    status: detailData.status || '—',
                    severity: detailData.severity || 'warning',
                    
                    // Cek fallback variasi nama kolom database jika ada perbedaan penamaan
                    nama: detailData.nama || detailData.pemohon || '—', 
                    aduan: detailData.aduan || detailData.isi_pengaduan || detailData.deskripsi || 'Tidak ada rincian isi aduan.',
                    kanal_pengaduan: detailData.kanal_pengaduan || detailData.kanal || '—',
                    jenis_layanan: detailData.jenis_layanan || detailData.kategori || '—',
                    seksi_tujuan: detailData.seksi_tujuan || detailData.seksi || this.selectedSection || '—',
                    
                    deadline_human: detailData.deadline_human || detailData.deadline_tindak_lanjut || '—',
                    sla_reason: detailData.sla_reason || '—'
                };
                
            } catch (err) {
                console.error('Gagal mengambil data detail via API. Detail Error:', err.message);
                
                // Ambil data yang sudah ada di list Layer 1 berdasarkan ID
                const localFallback = this.tickets.find(t => t.id == ticketId);
                if (localFallback) {
                    console.log('Mengaktifkan Fallback Lokal:', localFallback);
                    this.activeTicket = { 
                        id: localFallback.id,
                        nomor_tiket: localFallback.nomor_tiket || '—',
                        nama: localFallback.nama || localFallback.pemohon || '—',
                        status: localFallback.status || '—',
                        severity: localFallback.severity || 'warning',
                        deadline_human: localFallback.deadline_human || localFallback.deadline_tindak_lanjut || '—',
                        
                        // Perbaikan penamaan properti di list lokal sesuai response seksi
                        aduan: localFallback.aduan || localFallback.isi_pengaduan || localFallback.deskripsi || 'Tidak ada rincian isi aduan.',
                        kanal_pengaduan: localFallback.kanal_pengaduan || localFallback.kanal || '—',
                        jenis_layanan: localFallback.jenis_layanan || localFallback.kategori || '—',
                        sla_reason: localFallback.sla_reason || localFallback.sla_status || '—'
                    };
                } else {
                    this.activeTicket = {
                        nomor_tiket: 'Error',
                        nama: 'Gagal Memuat',
                        aduan: `Koneksi gagal atau data tidak ditemukan. (${err.message})`
                    };
                }
            } finally {
                this.detailLoading = false;
            }
        },

        closeAll() {
            this.showTicketDetail  = false;
            this.showSectionModal  = false;
            this.showAlertForm     = false;
            this.activeTicket      = {};
            this.tickets           = [];
        },

        // ── Layer 3: Alert form modal ────────────────────────
        showAlertForm:   false,
        alertTargetSeksi: '',
        alertTipe:        'over_sla',
        alertFormTitle:   '',
        alertFormSubtitle: '',
        alertPesan:  '',

        openAlertForm(seksi, tipe) {
            this.alertTargetSeksi  = seksi;
            this.alertTipe         = tipe;
            this.alertPesan        = '';

            this.alertFormTitle = tipe === 'over_sla'
                ? `🚨 Kirim Peringatan — Seksi ${seksi}`
                : `⚠️ Kirim Pengingat — Seksi ${seksi}`;

            this.alertFormSubtitle = tipe === 'over_sla'
                ? 'Aduan telah melewati batas SLA'
                : 'Aduan mendekati deadline SLA';

            this.showAlertForm = true;
        },
    };
}

// ── Chart.js defaults ─────────────────────────────────────────
Chart.defaults.font.family = "'DM Sans', sans-serif";
Chart.defaults.font.size   = 12;
Chart.defaults.color       = '#94a3b8';

const G = { color: 'rgba(241,245,249,.8)', drawBorder: false };
const T = { color: '#94a3b8' };
const P = ['#3b82f6','#10b981','#8b5cf6','#f59e0b','#ef4444','#06b6d4','#ec4899'];

const bL  = {!! json_encode($bulanLabel) !!};
const mPB = {!! json_encode($masukPerBulan) !!};
const sPB = {!! json_encode($selesaiPerBulan) !!};
const sLb = {!! json_encode(collect($distribusiSeksi)->pluck('seksi')->values()) !!};
const sTo = {!! json_encode(collect($distribusiSeksi)->pluck('total')->values()) !!};
const aWL = {!! json_encode(collect($avgWaktuSeksi)->pluck('seksi')->values()) !!};
const aWD = {!! json_encode(collect($avgWaktuSeksi)->pluck('avg_hari')->map(fn($v)=>round($v,1))->values()) !!};
const kL  = {!! json_encode(collect($distribusiKanal)->pluck('kanal')->values()) !!};
const kD  = {!! json_encode(collect($distribusiKanal)->pluck('total')->values()) !!};
const jL  = {!! json_encode(collect($distribusiJenis)->pluck('jenis_layanan')->map(fn($s)=>ucfirst($s))->values()) !!};
const jD  = {!! json_encode(collect($distribusiJenis)->pluck('total')->values()) !!};

// Line: tren bulanan
new Chart(document.getElementById('chartTren'), {
    type: 'line',
    data: { labels: bL, datasets: [
        { label: 'Masuk', data: mPB, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,.08)',
          borderWidth: 2.5, fill: true, tension: 0.4,
          pointRadius: 4, pointHoverRadius: 6, pointBackgroundColor: '#3b82f6', pointBorderColor: '#fff', pointBorderWidth: 2 },
        { label: 'Selesai', data: sPB, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,.06)',
          borderWidth: 2.5, fill: true, tension: 0.4,
          pointRadius: 4, pointHoverRadius: 6, pointBackgroundColor: '#10b981', pointBorderColor: '#fff', pointBorderWidth: 2 },
    ]},
    options: { responsive: true, maintainAspectRatio: false, interaction: { intersect: false, mode: 'index' },
        scales: {
            x: { grid: { display: false }, ticks: T, border: { display: false } },
            y: { grid: G, ticks: { ...T, stepSize: 1 }, beginAtZero: true, border: { display: false } }
        },
        plugins: {
            legend: { display: false },
            tooltip: { backgroundColor: '#1e293b', titleColor: '#94a3b8', bodyColor: '#f1f5f9',
                       padding: 12, cornerRadius: 10,
                       callbacks: { label: c => ` ${c.dataset.label}: ${c.parsed.y} aduan` } }
        },
    },
});

// Bar: distribusi seksi
new Chart(document.getElementById('chartSeksi'), {
    type: 'bar',
    data: { labels: sLb, datasets: [{
        label: 'Jumlah', data: sTo,
        backgroundColor: sLb.map((_, i) => P[i % P.length]),
        borderRadius: 6, borderSkipped: false
    }]},
    options: { responsive: true, maintainAspectRatio: false,
        scales: {
            x: { grid: { display: false }, ticks: T, border: { display: false } },
            y: { grid: G, ticks: { ...T, stepSize: 1 }, beginAtZero: true, border: { display: false } }
        },
        plugins: { legend: { display: false },
                   tooltip: { backgroundColor: '#1e293b', bodyColor: '#f1f5f9', padding: 10, cornerRadius: 8,
                              callbacks: { label: c => ` ${c.parsed.y} aduan` } } },
    },
});

// Bar horizontal: avg waktu per seksi
new Chart(document.getElementById('chartAvgWaktu'), {
    type: 'bar',
    data: { labels: aWL, datasets: [{
        label: 'Rata-rata (hari)', data: aWD,
        backgroundColor: aWD.map(v => v <= 3 ? '#10b981' : v <= 4 ? '#f59e0b' : '#ef4444'),
        borderRadius: 6, borderSkipped: false
    }]},
    options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false,
        scales: {
            x: { grid: G, ticks: { ...T, callback: v => v + ' hr' }, beginAtZero: true, border: { display: false } },
            y: { grid: { display: false }, ticks: T, border: { display: false } }
        },
        plugins: { legend: { display: false },
                   tooltip: { backgroundColor: '#1e293b', bodyColor: '#f1f5f9', padding: 10, cornerRadius: 8,
                              callbacks: { label: c => ` ${c.parsed.x} hari rata-rata` } } },
    },
});

// Doughnut: kanal
new Chart(document.getElementById('chartKanal'), {
    type: 'doughnut',
    data: { labels: kL, datasets: [{ data: kD, backgroundColor: P, borderWidth: 2, borderColor: '#fff', hoverOffset: 5 }] },
    options: { cutout: '68%', responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false } } },
});

// Doughnut: jenis
new Chart(document.getElementById('chartJenis'), {
    type: 'doughnut',
    data: { labels: jL, datasets: [{ data: jD, backgroundColor: ['#3b82f6', '#f59e0b'], borderWidth: 2, borderColor: '#fff', hoverOffset: 4 }] },
    options: { cutout: '65%', responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false } } },
});
</script>
</x-slot>

</x-layouts.dashboard>