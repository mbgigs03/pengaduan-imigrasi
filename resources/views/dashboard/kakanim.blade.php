{{-- resources/views/dashboard/kakanim.blade.php --}}
<x-layouts.dashboard>
    <x-slot name="header">Dashboard Pimpinan</x-slot>

    <x-slot name="styles">
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <style>
            .sla-ok   { color:#059669 } .sla-warn { color:#d97706 } .sla-over { color:#dc2626 }
            .kpi-card { background:#fff; border:1px solid #f1f5f9; border-radius:16px; padding:22px; transition:box-shadow .2s,transform .2s }
            .kpi-card:hover { box-shadow:0 6px 24px rgba(0,0,0,.08); transform:translateY(-1px) }
            .kpi-val  { font-size:2.25rem; font-weight:800; line-height:1; letter-spacing:-.02em }
            .chart-wrap { background:#fff; border:1px solid #f1f5f9; border-radius:16px; padding:24px }
            .chart-head { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:20px; gap:12px }
            .chart-head h3 { font-size:14px; font-weight:700; color:#1e293b; line-height:1.3 }
            .chart-head p  { font-size:12px; color:#94a3b8; margin-top:2px }
            .seksi-row { display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid #f8fafc }
            .seksi-row:last-child { border-bottom:none }
            .seksi-bar-bg { flex:1; height:6px; background:#f1f5f9; border-radius:99px; overflow:hidden }
            .seksi-bar-fill { height:100%; border-radius:99px; transition:width .6s cubic-bezier(.4,0,.2,1) }
            .badge-sla-ok   { background:#f0fdf4; color:#166534; border:1px solid #bbf7d0 }
            .badge-sla-warn { background:#fffbeb; color:#92400e; border:1px solid #fde68a }
            .badge-sla-over { background:#fef2f2; color:#991b1b; border:1px solid #fecaca }
            .tren-up { color:#dc2626 } .tren-down { color:#059669 } .tren-flat { color:#94a3b8 }

            /* ── Panel Nudge ── */
            .nudge-card-over {
                background: linear-gradient(135deg, #fff1f2 0%, #fff5f5 100%);
                border: 1.5px solid #fecaca;
                border-radius: 14px;
                padding: 18px;
                position: relative;
                overflow: hidden;
            }
            .nudge-card-over::before {
                content: '';
                position: absolute;
                top: 0; left: 0;
                width: 4px; height: 100%;
                background: #ef4444;
            }
            .nudge-card-warn {
                background: linear-gradient(135deg, #fffbeb 0%, #fefce8 100%);
                border: 1.5px solid #fde68a;
                border-radius: 14px;
                padding: 18px;
                position: relative;
                overflow: hidden;
            }
            .nudge-card-warn::before {
                content: '';
                position: absolute;
                top: 0; left: 0;
                width: 4px; height: 100%;
                background: #f59e0b;
            }
            .pulse-dot {
                width: 8px; height: 8px; border-radius: 50%;
                animation: pulseDot 1.5s infinite;
            }
            @keyframes pulseDot {
                0%,100% { box-shadow: 0 0 0 0 rgba(239,68,68,.5) }
                50%      { box-shadow: 0 0 0 6px rgba(239,68,68,0) }
            }

            /* ── Riwayat notif ── */
            .riwayat-row { padding:10px 0; border-bottom:1px solid #f8fafc; display:flex; gap:10px; align-items:flex-start }
            .riwayat-row:last-child { border-bottom:none }

            @media print {
                #sidebar, header, .no-print { display:none!important }
                #main-content { margin:0!important }
            }
        </style>
    </x-slot>

    <div class="p-6 space-y-6 max-w-[1400px]">

        {{-- HEADER ──────────────────────────────────────────── --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-extrabold text-slate-800">Laporan Strategis Layanan Pengaduan</h2>
                <p class="text-xs text-slate-400 mt-1">
                    Kantor Imigrasi Kelas II Non TPI Madiun &nbsp;·&nbsp; Periode {{ $tahun }}
                </p>
            </div>
            <div class="flex items-center gap-3 no-print">
                <form method="GET" action="{{ route('kakanim.dashboard') }}">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-slate-500">Tahun:</label>
                        <select name="tahun" onchange="this.form.submit()"
                            class="text-sm border border-slate-200 rounded-xl px-3 py-2 bg-white
                                   focus:outline-none focus:ring-2 focus:ring-blue-100">
                            @foreach ($tahunList as $t)
                                <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
                <button onclick="window.print()"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold
                           border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak
                </button>
            </div>
        </div>

        {{-- KPI CARDS ───────────────────────────────────────── --}}
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">

            <div class="kpi-card">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                    </div>
                    @php $trenClass = $tren>0?'tren-up':($tren<0?'tren-down':'tren-flat'); $trenArrow = $tren>0?'↑':($tren<0?'↓':'→'); @endphp
                    <span class="text-xs font-bold {{ $trenClass }}">{{ $trenArrow }} {{ abs($tren) }}%</span>
                </div>
                <div class="kpi-val text-slate-800">{{ number_format($scorecard->total) }}</div>
                <p class="text-xs text-slate-400 mt-1.5">Total pengaduan masuk</p>
                <p class="text-[10px] text-slate-300 mt-0.5">{{ $scorecard->masuk_bulan_ini }} aduan bulan ini</p>
            </div>

            <div class="kpi-card">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="kpi-val text-emerald-600">{{ $scorecard->pct_selesai ?? 0 }}%</div>
                <p class="text-xs text-slate-400 mt-1.5">Tingkat penyelesaian</p>
                <p class="text-[10px] text-slate-300 mt-0.5">{{ number_format($scorecard->selesai) }} dari {{ number_format($scorecard->total) }} aduan</p>
            </div>

            @php
                $avgHari   = round($avgWaktu->avg_hari ?? 0, 1);
                $slaStatus = $avgHari <= 3 ? 'ok' : ($avgHari <= 4 ? 'warn' : 'over');
                $slaClass  = ['ok'=>'badge-sla-ok','warn'=>'badge-sla-warn','over'=>'badge-sla-over'][$slaStatus];
                $slaLabel  = ['ok'=>'Sesuai Target','warn'=>'Mendekati Batas','over'=>'Melampaui Target'][$slaStatus];
            @endphp
            <div class="kpi-card">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl {{ $slaStatus==='ok'?'bg-emerald-50':($slaStatus==='warn'?'bg-amber-50':'bg-red-50') }} flex items-center justify-center">
                        <svg class="w-5 h-5 {{ $slaStatus==='ok'?'text-emerald-600':($slaStatus==='warn'?'text-amber-600':'text-red-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $slaClass }}">{{ $slaLabel }}</span>
                </div>
                <div class="kpi-val sla-{{ $slaStatus }}">{{ $avgHari }}</div>
                <p class="text-xs text-slate-400 mt-1.5">Rata-rata hari penyelesaian</p>
                <p class="text-[10px] text-slate-300 mt-0.5">Target RAP ≤3 hari · Sampel: {{ number_format($avgWaktu->sampel ?? 0) }}</p>
            </div>

            <div class="kpi-card {{ $scorecard->over_sla > 0 ? 'border-red-100' : '' }}">
                <div class="w-10 h-10 rounded-xl {{ $scorecard->over_sla>0?'bg-red-50':'bg-slate-100' }} flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 {{ $scorecard->over_sla>0?'text-red-600':'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="kpi-val {{ $scorecard->over_sla>0?'text-red-600':'text-slate-800' }}">{{ $scorecard->over_sla }}</div>
                <p class="text-xs text-slate-400 mt-1.5">Pengaduan melewati SLA</p>
                <p class="text-[10px] text-slate-300 mt-0.5">Belum selesai &amp; >3 hari</p>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════
             PANEL PERINGATAN SLA (NUDGE) — fitur utama
        ════════════════════════════════════════════════════ --}}
        @if (count($slaAlert) > 0)
        <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden" x-data="nudgePanel()">

            {{-- Header panel --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-red-50 to-amber-50">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Peringatan SLA — Perlu Tindakan Pimpinan</h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ count($slaAlert) }} seksi memiliki aduan yang tenggat atau mendekati batas waktu</p>
                    </div>
                </div>
                <span class="text-xs font-bold px-3 py-1.5 rounded-full bg-red-100 text-red-700 border border-red-200 animate-pulse">
                    {{ collect($slaAlert)->sum('jumlah_over') }} Over SLA
                </span>
            </div>

            {{-- Kartu per seksi --}}
            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($slaAlert as $s)
                    @php
                        $isOver  = $s->jumlah_over > 0;
                        $cardCls = $isOver ? 'nudge-card-over' : 'nudge-card-warn';
                    @endphp

                    <div class="{{ $cardCls }}">
                        <div class="flex items-start justify-between mb-3 pl-3">
                            <div class="flex items-center gap-2">
                                @if ($isOver)
                                    <span class="pulse-dot bg-red-500 flex-shrink-0"></span>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></span>
                                @endif
                                <h4 class="text-sm font-bold {{ $isOver ? 'text-red-800' : 'text-amber-800' }}">
                                    Seksi {{ $s->seksi }}
                                </h4>
                            </div>
                            <button
                                @click="openForm('{{ $s->seksi }}', '{{ $isOver ? 'over_sla' : 'warn_sla' }}')"
                                class="text-xs font-bold px-3 py-1.5 rounded-lg transition
                                       {{ $isOver
                                           ? 'bg-red-600 text-white hover:bg-red-700 shadow-sm'
                                           : 'bg-amber-500 text-white hover:bg-amber-600 shadow-sm' }}">
                                Kirim Peringatan
                            </button>
                        </div>

                        <div class="pl-3 space-y-1.5">
                            @if ($s->jumlah_over > 0)
                                <div class="flex items-center gap-2 text-xs text-red-700">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>
                                    <strong>{{ $s->jumlah_over }}</strong> aduan sudah melewati deadline SLA
                                </div>
                            @endif
                            @if ($s->jumlah_warn > 0)
                                <div class="flex items-center gap-2 text-xs text-amber-700">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/></svg>
                                    <strong>{{ $s->jumlah_warn }}</strong> aduan tenggat dalam 24 jam ke depan
                                </div>
                            @endif
                            @if ($s->tiket_terdampak)
                                <div class="flex flex-wrap gap-1 mt-2">
                                    @foreach (array_slice((array)$s->tiket_terdampak, 0, 4) as $tiket)
                                        <span class="text-[10px] font-mono px-2 py-0.5 rounded-md
                                                     {{ $isOver ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ $tiket }}
                                        </span>
                                    @endforeach
                                    @if (count((array)$s->tiket_terdampak) > 4)
                                        <span class="text-[10px] text-slate-400 py-0.5">
                                            +{{ count((array)$s->tiket_terdampak) - 4 }} lainnya
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ── MODAL FORM PERINGATAN ─────────────────────── --}}
            <div x-show="showForm"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
                 @click.self="showForm = false"
                 style="display:none">

                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     @click.stop>

                    {{-- Modal Header --}}
                    <div class="bg-gradient-to-r from-red-600 to-rose-600 px-6 py-4 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-white text-base" x-text="modalTitle"></h3>
                            <p class="text-red-200 text-xs mt-0.5" x-text="modalSubtitle"></p>
                        </div>
                        <button @click="showForm = false" class="text-red-200 hover:text-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <form method="POST" action="{{ route('kakanim.alert') }}" class="p-6 space-y-4">
                        @csrf
                        <input type="hidden" name="target_seksi" x-model="targetSeksi">
                        <input type="hidden" name="tipe_alert"   x-model="tipeAlert">

                        {{-- Tipe alert (readonly, sudah di-set otomatis) --}}
                        <div class="flex items-center gap-3 p-3 rounded-xl" :class="tipeAlert==='over_sla'?'bg-red-50 border border-red-200':'bg-amber-50 border border-amber-200'">
                            <div x-show="tipeAlert==='over_sla'" class="pulse-dot bg-red-500 flex-shrink-0"></div>
                            <div x-show="tipeAlert==='warn_sla'" class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></div>
                            <span class="text-xs font-semibold" :class="tipeAlert==='over_sla'?'text-red-700':'text-amber-700'" x-text="tipeAlert==='over_sla'?'Peringatan: Aduan sudah melewati SLA':'Pengingat: Aduan mendekati tenggat SLA'"></span>
                        </div>

                        {{-- Template pesan cepat --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
                                Template Cepat
                            </label>
                            <div class="grid grid-cols-1 gap-2">
                                @foreach([
                                    ['over_sla', 'Saya minta agar pengaduan yang telah melewati batas waktu SLA segera ditindaklanjuti dan diselesaikan. Hal ini berpengaruh langsung pada penilaian kualitas pelayanan kantor.', 'Perintah Segera Selesaikan'],
                                    ['warn_sla', 'Perhatian! Beberapa pengaduan akan melewati batas waktu dalam 24 jam ke depan. Harap segera lakukan tindak lanjut sebelum tenggat waktu terlewati.', 'Peringatan H-1'],
                                    ['umum',     'Mohon tingkatkan koordinasi dan percepat penyelesaian pengaduan yang masuk. Pimpinan memantau perkembangan ini secara berkala.', 'Pesan Umum Pimpinan'],
                                ] as [$tipe, $teks, $label])
                                    <button type="button"
                                            @click="setTemplate('{{ $teks }}')"
                                            class="text-left text-xs px-3 py-2 rounded-lg border border-slate-200
                                                   hover:bg-blue-50 hover:border-blue-200 hover:text-blue-700
                                                   text-slate-600 transition font-medium">
                                        📝 {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Textarea pesan --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">
                                Pesan Peringatan <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                name="pesan"
                                x-model="pesan"
                                rows="5"
                                maxlength="1000"
                                placeholder="Tulis pesan peringatan kepada seksi terkait…"
                                class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-red-400
                                       resize-none placeholder:text-slate-400"
                                required></textarea>
                            <p class="text-[10px] text-slate-400 mt-1 text-right">
                                <span x-text="pesan.length"></span>/1000 karakter
                            </p>
                        </div>

                        {{-- Pratinjau penerima --}}
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-600">
                            <span class="font-semibold">Akan dikirim ke:</span>
                            Semua admin Seksi <span x-text="targetSeksi" class="font-bold text-slate-800"></span>
                            yang terdaftar di sistem
                        </div>

                        <div class="flex gap-3 pt-1">
                            <button type="button" @click="showForm = false"
                                class="flex-1 py-2.5 text-sm border border-slate-200 rounded-xl
                                       text-slate-500 hover:bg-slate-50 font-medium transition">
                                Batal
                            </button>
                            <button type="submit"
                                :disabled="pesan.length < 10"
                                class="flex-1 py-2.5 text-sm font-bold rounded-xl text-white transition
                                       bg-red-600 hover:bg-red-700 disabled:bg-slate-300 disabled:cursor-not-allowed">
                                Kirim Peringatan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
        @endif

        {{-- TREN BULANAN ─────────────────────────────────────── --}}
        <div class="chart-wrap">
            <div class="chart-head">
                <div>
                    <h3>Tren Pengaduan Bulanan {{ $tahun }}</h3>
                    <p>Perbandingan pengaduan masuk vs terselesaikan per bulan</p>
                </div>
                <div class="flex items-center gap-4 text-xs text-slate-500">
                    <span class="flex items-center gap-1.5"><span class="inline-block w-6 h-0.5 rounded bg-blue-500"></span>Masuk</span>
                    <span class="flex items-center gap-1.5"><span class="inline-block w-6 h-0.5 rounded bg-emerald-500"></span>Selesai</span>
                </div>
            </div>
            <canvas id="chartTren" style="max-height:240px"></canvas>
        </div>

        {{-- DISTRIBUSI SEKSI + AVG WAKTU ─────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            <div class="chart-wrap">
                <div class="chart-head"><div><h3>Performa per Seksi</h3><p>Volume dan tingkat penyelesaian</p></div></div>
                @php $maxTotal = collect($distribusiSeksi)->max('total') ?: 1; @endphp
                <div class="space-y-0.5">
                    @forelse ($distribusiSeksi as $s)
                        @php
                            $pct    = (float) $s->pct_selesai;
                            $barClr = $pct>=80?'#10b981':($pct>=50?'#3b82f6':'#f59e0b');
                            $barW   = round($s->total / $maxTotal * 100);
                        @endphp
                        <div class="seksi-row">
                            <div style="width:150px;flex-shrink:0">
                                <div class="text-xs font-semibold text-slate-700 truncate">{{ $s->seksi }}</div>
                                <div class="text-[10px] text-slate-400">{{ $s->total }} aduan</div>
                            </div>
                            <div class="seksi-bar-bg">
                                <div class="seksi-bar-fill" style="width:{{ $barW }}%;background:{{ $barClr }}"></div>
                            </div>
                            <span class="text-sm font-bold" style="width:52px;text-align:right;color:{{ $barClr }}">{{ $pct }}%</span>
                            @if ($s->over_sla > 0)
                                <span class="text-[10px] font-bold text-red-500 whitespace-nowrap">{{ $s->over_sla }}⚠</span>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-400 py-4 text-center">Belum ada data</p>
                    @endforelse
                </div>
                <div class="mt-5"><canvas id="chartSeksi" style="max-height:160px"></canvas></div>
            </div>

            <div class="chart-wrap">
                <div class="chart-head">
                    <div><h3>Rata-rata Waktu Penyelesaian</h3><p>Dalam hari · Batas target RAP = 3 hari</p></div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $slaClass }}">Rata-rata: {{ $avgHari }} hari</span>
                </div>
                <canvas id="chartAvgWaktu" style="max-height:200px"></canvas>
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <div class="grid grid-cols-3 text-[10px] font-bold text-slate-400 uppercase tracking-wide pb-2">
                        <span>Seksi</span><span class="text-center">Avg</span><span class="text-right">n</span>
                    </div>
                    @foreach ($avgWaktuSeksi as $s)
                        @php $hari = round($s->avg_hari,1); $w=$hari<=3?'text-emerald-600':($hari<=4?'text-amber-600':'text-red-600'); @endphp
                        <div class="grid grid-cols-3 py-1.5 border-b border-slate-50 text-xs">
                            <span class="text-slate-700 font-medium truncate">{{ $s->seksi }}</span>
                            <span class="text-center font-bold {{ $w }}">{{ $hari }}hr</span>
                            <span class="text-right text-slate-400">{{ $s->sampel }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- KANAL + JENIS + RINGKASAN ───────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            <div class="chart-wrap">
                <div class="chart-head"><div><h3>Distribusi Kanal Pengaduan</h3><p>Saluran paling banyak digunakan</p></div></div>
                <div class="flex items-center gap-6">
                    <div style="width:180px;height:180px;flex-shrink:0"><canvas id="chartKanal"></canvas></div>
                    <div class="flex-1 space-y-2">
                        @php $kanalClr=['#3b82f6','#10b981','#8b5cf6','#f59e0b','#ef4444','#06b6d4','#ec4899']; @endphp
                        @foreach ($distribusiKanal as $i => $k)
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:{{ $kanalClr[$i%count($kanalClr)] }}"></span>
                                    <span class="text-slate-600 font-medium">{{ $k->kanal }}</span>
                                </div>
                                <span class="font-bold text-slate-800">{{ $k->total }} <span class="font-normal text-slate-400">({{ $k->pct }}%)</span></span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="chart-wrap">
                <div class="chart-head"><div><h3>Jenis Layanan &amp; Ringkasan Eksekutif</h3><p>Informasi vs penanganan pengaduan</p></div></div>
                <div class="flex items-center gap-6 mb-5">
                    <div style="width:120px;height:120px;flex-shrink:0"><canvas id="chartJenis"></canvas></div>
                    <div class="flex-1 space-y-3">
                        @php $jenisClr=['#3b82f6','#f59e0b']; $totalJenis=collect($distribusiJenis)->sum('total')?:1; @endphp
                        @foreach ($distribusiJenis as $i => $j)
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full" style="background:{{ $jenisClr[$i]??'#94a3b8' }}"></span><span class="text-slate-600 font-medium capitalize">{{ $j->jenis_layanan }}</span></div>
                                    <span class="font-bold text-slate-800">{{ $j->total }}</span>
                                </div>
                                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full" style="width:{{ round($j->total/$totalJenis*100) }}%;background:{{ $jenisClr[$i]??'#94a3b8' }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 space-y-2">
                    <p class="text-xs font-bold text-slate-600 uppercase tracking-wide">Ringkasan Eksekutif</p>
                    <div class="space-y-1.5 text-xs text-slate-600 leading-relaxed">
                        <p>📊 Tahun {{ $tahun }}, total <strong>{{ number_format($scorecard->total) }}</strong> pengaduan. Tingkat penyelesaian <strong class="{{ ($scorecard->pct_selesai??0)>=80?'text-emerald-600':'text-amber-600' }}">{{ $scorecard->pct_selesai??0 }}%</strong>.</p>
                        <p>⏱ Rata-rata <strong class="sla-{{ $slaStatus }}">{{ $avgHari }} hari</strong> — {{ $slaStatus==='ok'?'sesuai':'melampaui' }} target RAP ≤3 hari.</p>
                        @if ($scorecard->over_sla > 0)
                            <p>⚠️ <strong class="text-red-600">{{ $scorecard->over_sla }}</strong> pengaduan melewati batas SLA — perlu tindakan segera.</p>
                        @else
                            <p>✅ Tidak ada pengaduan yang melewati SLA saat ini.</p>
                        @endif
                        @php $top = collect($distribusiSeksi)->first(); @endphp
                        @if ($top)
                            <p>🏢 Volume tertinggi: <strong>{{ $top->seksi }}</strong> ({{ $top->total }} aduan, {{ $top->pct_selesai }}% selesai).</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- RIWAYAT PERINGATAN YANG SUDAH DIKIRIM ──────────── --}}
        @if ($riwayatAlert->isNotEmpty())
        <div class="chart-wrap">
            <div class="chart-head">
                <div>
                    <h3>Riwayat Peringatan Terkirim</h3>
                    <p>Pesan yang sudah dikirim kepada seksi</p>
                </div>
            </div>
            <div class="space-y-0.5">
                @foreach ($riwayatAlert as $notif)
                    <div class="riwayat-row">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0
                                    {{ str_contains($notif->tipe, 'over') ? 'bg-red-50' : 'bg-amber-50' }}">
                            <svg class="w-4 h-4 {{ str_contains($notif->tipe,'over')?'text-red-500':'text-amber-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-xs font-semibold text-slate-700 truncate">{{ $notif->judul }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5 truncate">{{ Str::limit($notif->pesan, 80) }}</div>
                        </div>
                        <div class="text-[10px] text-slate-400 flex-shrink-0 text-right">
                            <div class="font-medium text-slate-600">Seksi {{ $notif->target_seksi }}</div>
                            <div>{{ $notif->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>{{-- end p-6 --}}

    <x-slot name="scripts">
    <script>
    // ── Alpine component untuk panel Nudge ──────────────────
    function nudgePanel() {
        return {
            showForm:    false,
            targetSeksi: '',
            tipeAlert:   'over_sla',
            pesan:       '',
            modalTitle:  '',
            modalSubtitle: '',

            openForm(seksi, tipe) {
                this.targetSeksi  = seksi;
                this.tipeAlert    = tipe;
                this.pesan        = '';
                this.modalTitle   = tipe === 'over_sla'
                    ? `🚨 Kirim Peringatan — Seksi ${seksi}`
                    : `⚠️ Kirim Pengingat — Seksi ${seksi}`;
                this.modalSubtitle = tipe === 'over_sla'
                    ? 'Aduan sudah melewati batas waktu SLA 3 hari'
                    : 'Aduan mendekati tenggat dalam 24 jam ke depan';
                this.showForm = true;
            },

            setTemplate(teks) { this.pesan = teks; },
        };
    }

    // ── Chart.js ─────────────────────────────────────────────
    Chart.defaults.font.family = "'Plus Jakarta Sans','sans-serif'";
    Chart.defaults.font.size   = 12;
    Chart.defaults.color       = '#94a3b8';
    const G = { color:'rgba(241,245,249,.8)', drawBorder:false };
    const T = { color:'#94a3b8' };
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
        type:'line',
        data:{ labels:bL, datasets:[
            { label:'Masuk', data:mPB, borderColor:'#3b82f6', backgroundColor:'rgba(59,130,246,.08)', borderWidth:2.5, fill:true, tension:0.4, pointRadius:4, pointHoverRadius:6, pointBackgroundColor:'#3b82f6', pointBorderColor:'#fff', pointBorderWidth:2 },
            { label:'Selesai', data:sPB, borderColor:'#10b981', backgroundColor:'rgba(16,185,129,.06)', borderWidth:2.5, fill:true, tension:0.4, pointRadius:4, pointHoverRadius:6, pointBackgroundColor:'#10b981', pointBorderColor:'#fff', pointBorderWidth:2 },
        ]},
        options:{ responsive:true, maintainAspectRatio:false, interaction:{intersect:false,mode:'index'},
            scales:{ x:{grid:{display:false},ticks:T,border:{display:false}}, y:{grid:G,ticks:{...T,stepSize:1},beginAtZero:true,border:{display:false}} },
            plugins:{ legend:{display:false}, tooltip:{backgroundColor:'#1e293b',titleColor:'#94a3b8',bodyColor:'#f1f5f9',padding:12,cornerRadius:10,callbacks:{label:c=>` ${c.dataset.label}: ${c.parsed.y} aduan`}} },
        },
    });

    // Bar: distribusi seksi
    new Chart(document.getElementById('chartSeksi'), {
        type:'bar',
        data:{ labels:sLb, datasets:[{ label:'Jumlah', data:sTo, backgroundColor:sLb.map((_,i)=>P[i%P.length]), borderRadius:6, borderSkipped:false }] },
        options:{ responsive:true, maintainAspectRatio:false,
            scales:{ x:{grid:{display:false},ticks:T,border:{display:false}}, y:{grid:G,ticks:{...T,stepSize:1},beginAtZero:true,border:{display:false}} },
            plugins:{ legend:{display:false}, tooltip:{backgroundColor:'#1e293b',bodyColor:'#f1f5f9',padding:10,cornerRadius:8,callbacks:{label:c=>` ${c.parsed.y} aduan`}} },
        },
    });

    // Bar horizontal: avg waktu per seksi
    new Chart(document.getElementById('chartAvgWaktu'), {
        type:'bar',
        data:{ labels:aWL, datasets:[{ label:'Rata-rata (hari)', data:aWD, backgroundColor:aWD.map(v=>v<=3?'#10b981':v<=4?'#f59e0b':'#ef4444'), borderRadius:6, borderSkipped:false }] },
        options:{ indexAxis:'y', responsive:true, maintainAspectRatio:false,
            scales:{ x:{grid:G,ticks:{...T,callback:v=>v+' hr'},beginAtZero:true,border:{display:false}}, y:{grid:{display:false},ticks:T,border:{display:false}} },
            plugins:{ legend:{display:false}, tooltip:{backgroundColor:'#1e293b',bodyColor:'#f1f5f9',padding:10,cornerRadius:8,callbacks:{label:c=>` ${c.parsed.x} hari rata-rata`}} },
        },
    });

    // Doughnut: kanal
    new Chart(document.getElementById('chartKanal'), {
        type:'doughnut',
        data:{ labels:kL, datasets:[{ data:kD, backgroundColor:P, borderWidth:2, borderColor:'#fff', hoverOffset:5 }] },
        options:{ cutout:'68%', responsive:true, maintainAspectRatio:true, plugins:{legend:{display:false}} },
    });

    // Doughnut: jenis
    new Chart(document.getElementById('chartJenis'), {
        type:'doughnut',
        data:{ labels:jL, datasets:[{ data:jD, backgroundColor:['#3b82f6','#f59e0b'], borderWidth:2, borderColor:'#fff', hoverOffset:4 }] },
        options:{ cutout:'65%', responsive:true, maintainAspectRatio:true, plugins:{legend:{display:false}} },
    });
    </script>
    </x-slot>

</x-layouts.dashboard>