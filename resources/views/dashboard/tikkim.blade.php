{{-- resources/views/dashboard/tikkim.blade.php --}}
<x-layouts.dashboard>
    <x-slot name="header">Dashboard TIKKIM</x-slot>

    <x-slot name="styles">
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    </x-slot>

    <div class="p-6 space-y-6 max-w-[1400px]">

        {{-- ═══════════════════════════════════════════
             STAT CARDS
        ═══════════════════════════════════════════ --}}
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">

            {{-- Card: Total --}}
            <div class="stat-card flex items-start gap-4">
                <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Pengaduan</p>
                    <p class="text-2xl font-bold text-slate-800 mt-0.5">{{ number_format($totalBulanIni) }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">bulan ini</p>
                </div>
            </div>

            {{-- Card: Selesai --}}
            <div class="stat-card flex items-start gap-4">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Selesai</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-0.5">{{ number_format($selesai) }}</p>
                    <div class="flex items-center gap-1.5 mt-1">
                        <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full transition-all"
                                 style="width:{{ $totalBulanIni > 0 ? round($selesai/$totalBulanIni*100) : 0 }}%"></div>
                        </div>
                        <span class="text-[10px] font-bold text-emerald-600">{{ $totalBulanIni > 0 ? round($selesai/$totalBulanIni*100) : 0 }}%</span>
                    </div>
                </div>
            </div>

            {{-- Card: Over SLA --}}
            <div class="stat-card flex items-start gap-4 {{ $slaOver > 0 ? 'border-red-100 bg-red-50/30' : '' }}">
                <div class="w-11 h-11 rounded-xl {{ $slaOver > 0 ? 'bg-red-100' : 'bg-slate-100' }} flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 {{ $slaOver > 0 ? 'text-red-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Melebihi SLA</p>
                    <p class="text-2xl font-bold {{ $slaOver > 0 ? 'text-red-600' : 'text-slate-800' }} mt-0.5">{{ $slaOver }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">melewati 3 hari</p>
                </div>
            </div>

            {{-- Card: H-1 --}}
            <div class="stat-card flex items-start gap-4 {{ $slaHMinus1 > 0 ? 'border-amber-100 bg-amber-50/30' : '' }}">
                <div class="w-11 h-11 rounded-xl {{ $slaHMinus1 > 0 ? 'bg-amber-100' : 'bg-slate-100' }} flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 {{ $slaHMinus1 > 0 ? 'text-amber-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">H-1 Deadline</p>
                    <p class="text-2xl font-bold {{ $slaHMinus1 > 0 ? 'text-amber-600' : 'text-slate-800' }} mt-0.5">{{ $slaHMinus1 }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">perlu tindak lanjut</p>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════
             ROW 2: PIE + BAR HORIZONTAL
        ═══════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            {{-- PIE: Status --}}
            <div class="chart-card">
                <p class="chart-title">Distribusi Status</p>
                <div class="flex items-center gap-6">
                    <div style="width:176px;height:176px;flex-shrink:0">
                        <canvas id="chartStatus"></canvas>
                    </div>
                    <div class="space-y-2.5 flex-1">
                        @php
                            $statusMeta  = [
                                'pending'    => ['Pending',    '#f59e0b'],
                                'proses'     => ['Proses',     '#3b82f6'],
                                'diteruskan' => ['Diteruskan', '#8b5cf6'],
                                'selesai'    => ['Selesai',    '#10b981'],
                            ];
                            $totalStatus = $statusStats->sum('jumlah') ?: 1;
                        @endphp
                        @foreach ($statusStats as $s)
                            @php [$slabel, $scolor] = $statusMeta[$s->status] ?? [ucfirst($s->status), '#94a3b8']; @endphp
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:{{ $scolor }}"></span>
                                        <span class="text-slate-600 font-medium">{{ $slabel }}</span>
                                    </div>
                                    <span class="font-bold text-slate-800">{{ $s->jumlah }}
                                        <span class="font-normal text-slate-400">({{ round($s->jumlah/$totalStatus*100) }}%)</span>
                                    </span>
                                </div>
                                <div class="h-1 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full" style="width:{{ round($s->jumlah/$totalStatus*100) }}%;background:{{ $scolor }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- BAR HORIZONTAL: Penyelesaian Seksi --}}
            <div class="chart-card">
                <p class="chart-title">Penyelesaian per Seksi</p>
                <canvas id="chartSeksi" style="max-height:196px"></canvas>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════
             ROW 3: BAR KANAL + DOUGHNUT SLA
        ═══════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            <div class="chart-card lg:col-span-2">
                <p class="chart-title">Volume per Kanal Pengaduan</p>
                <canvas id="chartKanal" style="max-height:196px"></canvas>
            </div>

            <div class="chart-card flex flex-col">
                <p class="chart-title">SLA Health</p>
                @php $slaOnTrack = max(0, $totalBulanIni - $selesai - $slaOver - $slaHMinus1); @endphp
                <div class="flex-1 flex flex-col items-center justify-center gap-3">
                    <div style="width:148px;height:148px">
                        <canvas id="chartSla"></canvas>
                    </div>
                    <div class="grid grid-cols-2 gap-x-5 gap-y-2 w-full">
                        @foreach([
                            ['On Track', $slaOnTrack,  '#10b981'],
                            ['Selesai',  $selesai,     '#3b82f6'],
                            ['H-1',      $slaHMinus1,  '#f59e0b'],
                            ['Over SLA', $slaOver,     '#ef4444'],
                        ] as [$l, $v, $c])
                            <div class="flex items-center gap-2 text-xs">
                                <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:{{ $c }}"></span>
                                <span class="text-slate-500">{{ $l }}</span>
                                <span class="font-bold text-slate-700 ml-auto">{{ $v }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════
             ROW 4: STACKED BAR
        ═══════════════════════════════════════════ --}}
        <div class="chart-card">
            <p class="chart-title">Breakdown Status per Seksi</p>
            <canvas id="chartSeksiStacked" style="max-height:196px"></canvas>
        </div>

        {{-- ═══════════════════════════════════════════
             LAPORAN SLA
        ═══════════════════════════════════════════ --}}
        <div class="bg-white rounded-[14px] border border-slate-100 overflow-hidden">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Laporan SLA</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Pengaduan yang belum selesai diproses</p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    @foreach([
                        ['w-2 h-2 rounded-full bg-emerald-500', 'On track'],
                        ['w-2 h-2 rounded-full bg-amber-500',   'H-1'],
                        ['w-2 h-2 rounded-full bg-red-500',     'Terlambat'],
                    ] as [$dotCls, $lbl])
                        <span class="flex items-center gap-1.5 text-slate-500">
                            <span class="{{ $dotCls }} flex-shrink-0"></span>{{ $lbl }}
                        </span>
                    @endforeach
                </div>
            </div>

            {{-- Filter --}}
            <form method="GET" action="{{ route('dashboard') }}"
                  class="flex flex-wrap items-center gap-2.5 px-5 py-3 bg-slate-50/70 border-b border-slate-100">
                <input type="text" name="keyword" value="{{ request('keyword') }}"
                    placeholder="Cari nama / nomor tiket…"
                    class="text-sm border border-slate-200 rounded-xl px-3 py-2
                           focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400
                           bg-white w-52 placeholder:text-slate-400">

                @foreach([
                    ['status', '', 'Semua Status', ['pending' => 'Pending', 'proses' => 'Proses', 'diteruskan' => 'Diteruskan', 'selesai' => 'Selesai']],
                    ['kanal',  '', 'Semua Kanal',  array_combine($kanalList, $kanalList)],
                    ['sla',    '', 'Semua SLA',    ['ok' => 'On Track', 'warn' => 'H-1', 'over' => 'Terlambat']],
                ] as [$name, $default, $placeholder, $opts])
                    <select name="{{ $name }}"
                            class="text-sm border border-slate-200 rounded-xl px-3 py-2
                                   focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400
                                   bg-white text-slate-600">
                        <option value="{{ $default }}">{{ $placeholder }}</option>
                        @foreach ($opts as $v => $l)
                            <option value="{{ $v }}" {{ request($name) === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                @endforeach

                <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
                    Filter
                </button>
                <a href="{{ route('dashboard') }}"
                   class="text-sm text-slate-400 hover:text-slate-600 transition">Reset</a>
            </form>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full data-table">
                    <thead>
                        <tr>
                            @foreach(['Tiket', 'Nama', 'Seksi', 'Aduan', 'Status', 'Deadline', 'Aksi'] as $h)
                                <th>{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($laporanSla as $p)
                            @php
                                $slaMeta = match($p->sla_status) {
                                    'over'  => ['Terlambat',   'bg-red-500 dot-over', 'bg-red-50 text-red-700 border-red-200'],
                                    'warn'  => ['H-1',         'bg-amber-500',        'bg-amber-50 text-amber-700 border-amber-200'],
                                    default => ['On Track',    'bg-emerald-500',      'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                };
                                $tlId = optional($p->tindakLanjut)->id;
                            @endphp
                            <tr>
                                {{-- Tiket --}}
                                <td>
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $slaMeta[1] }}"></span>
                                        <span class="font-mono text-xs text-slate-500">{{ $p->nomor_tiket }}</span>
                                    </div>
                                </td>

                                {{-- Nama --}}
                                <td class="font-semibold text-slate-800 text-sm">{{ $p->nama }}</td>

                                {{-- Seksi --}}
                                <td>
                                    <span class="text-xs font-medium text-slate-500 bg-slate-100 px-2 py-1 rounded-lg">
                                        {{ $p->seksi_tujuan }}
                                    </span>
                                </td>

                                {{-- Aduan --}}
                                <td>
                                    <a href="{{ route('pengaduan.show', $p->id) }}"
                                       class="text-xs px-2.5 py-1.5 rounded-lg font-semibold
                                              bg-indigo-50 text-indigo-700 border border-indigo-200
                                              hover:bg-indigo-100 transition whitespace-nowrap">
                                        Lihat Detail
                                    </a>
                                </td>

                                {{-- Status --}}
                                <td>
                                    <span class="badge badge-{{ $p->status }}">{{ ucfirst($p->status) }}</span>
                                </td>

                                {{-- Deadline --}}
                                <td>
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold uppercase
                                                 border {{ $slaMeta[2] }} mb-0.5">
                                        {{ $slaMeta[0] }}
                                    </span>
                                    <div class="text-xs {{ $p->sla_status === 'over' ? 'text-red-600 font-bold' : 'text-slate-500' }}">
                                        {{ \Carbon\Carbon::parse($p->deadline_tindak_lanjut)->format('d M Y') }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        {{ \Carbon\Carbon::parse($p->deadline_tindak_lanjut)->diffForHumans() }}
                                    </div>
                                </td>

                                {{-- Aksi --}}
                                <td>
                                    <div class="flex items-center gap-1.5">

                                        {{-- TL Button --}}
                                        <button onclick="openModalTL(
                                                '{{ $p->id }}','{{ $p->nomor_tiket }}',
                                                '{{ addslashes($p->nama) }}','{{ $p->status }}',
                                                '{{ addslashes($p->keterangan_admin ?? '') }}','{{ $tlId }}')"
                                            class="text-xs px-2.5 py-1.5 rounded-lg font-bold transition whitespace-nowrap
                                            {{ $tlId
                                                ? 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100'
                                                : 'bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100' }}">
                                            {{ $tlId ? 'Edit TL' : 'Tindak Lanjut' }}
                                        </button>

                                        {{-- Download Dropdown --}}
                                        <div class="relative" x-data="{ open: false }">
                                            <button @click="open = !open" @keydown.escape="open = false"
                                                    class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200
                                                           text-slate-400 hover:bg-slate-50 hover:text-slate-600 transition"
                                                    title="Unduh">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                </svg>
                                            </button>

                                            <div x-show="open" @click.outside="open = false"
                                                 x-transition:enter="transition ease-out duration-100"
                                                 x-transition:enter-start="opacity-0 scale-95"
                                                 x-transition:enter-end="opacity-100 scale-100"
                                                 x-transition:leave="transition ease-in duration-75"
                                                 x-transition:leave-end="opacity-0 scale-95"
                                                 class="absolute right-0 z-30 mt-1 w-44 bg-white rounded-xl border border-slate-100 shadow-xl py-1"
                                                 style="display:none">

                                                <a href="{{ route('dashboard.pengaduan.downloadPdf', $p->nomor_tiket) }}"
                                                   class="flex items-center gap-2.5 px-3.5 py-2.5 text-xs text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
                                                    <span class="w-5 h-5 rounded-md bg-red-50 flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                                    </span>
                                                    <div>
                                                        <div class="font-semibold">Unduh PDF</div>
                                                        <div class="text-[10px] {{ $p->pdf_url ? 'text-emerald-500' : 'text-slate-400' }}">
                                                            {{ $p->pdf_url ? 'Tersedia' : 'Generate otomatis' }}
                                                        </div>
                                                    </div>
                                                </a>

                                                <a href="{{ route('dashboard.pengaduan.downloadDocx', $p->nomor_tiket) }}"
                                                   class="flex items-center gap-2.5 px-3.5 py-2.5 text-xs text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition">
                                                    <span class="w-5 h-5 rounded-md bg-blue-50 flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    </span>
                                                    <div>
                                                        <div class="font-semibold">Unduh DOCX</div>
                                                        <div class="text-[10px] text-slate-400">Dokumen arsip</div>
                                                    </div>
                                                </a>

                                                @if ($p->pdf_url)
                                                    <div class="border-t border-slate-100 my-1"></div>
                                                    <a href="{{ $p->pdf_url }}" target="_blank"
                                                       class="flex items-center gap-2.5 px-3.5 py-2.5 text-xs text-slate-500 hover:bg-slate-50 transition">
                                                        <span class="w-5 h-5 rounded-md bg-slate-50 flex items-center justify-center flex-shrink-0">
                                                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                        </span>
                                                        Buka Tab Baru
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-14 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                        <p class="text-sm font-semibold text-slate-600">Semua pengaduan on track</p>
                                        <p class="text-xs text-slate-400">Tidak ada yang mendekati atau melewati batas SLA</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-3 border-t border-slate-100">
                {{ $laporanSla->links() }}
            </div>
        </div>

        {{-- ═══════════════════════════════════════════
             SEMUA PENGADUAN
        ═══════════════════════════════════════════ --}}
        <div class="bg-white rounded-[14px] border border-slate-100 overflow-hidden">

            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Semua Pengaduan</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Seluruh data pengaduan bulan ini</p>
                </div>
            </div>

            {{-- Filter --}}
            <form method="GET" action="{{ route('dashboard') }}"
                  class="flex flex-wrap items-center gap-2.5 px-5 py-3 bg-slate-50/70 border-b border-slate-100">
                <input type="text" name="keyword_all" value="{{ request('keyword_all') }}"
                    placeholder="Cari nama / nomor tiket…"
                    class="text-sm border border-slate-200 rounded-xl px-3 py-2
                           focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400
                           bg-white w-52 placeholder:text-slate-400">

                @foreach([
                    ['status_all', '', 'Semua Status', ['pending'=>'Pending','proses'=>'Proses','diteruskan'=>'Diteruskan','selesai'=>'Selesai']],
                    ['kanal_all',  '', 'Semua Kanal',  array_combine($kanalList, $kanalList)],
                    ['sla_all',    '', 'Semua SLA',    ['ok'=>'On Track','warn'=>'H-1','over'=>'Terlambat']],
                ] as [$name, $default, $placeholder, $opts])
                    <select name="{{ $name }}"
                            class="text-sm border border-slate-200 rounded-xl px-3 py-2
                                   focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400
                                   bg-white text-slate-600">
                        <option value="{{ $default }}">{{ $placeholder }}</option>
                        @foreach ($opts as $v => $l)
                            <option value="{{ $v }}" {{ request($name) === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                @endforeach

                <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
                    Filter
                </button>
                <a href="{{ route('dashboard') }}"
                   class="text-sm text-slate-400 hover:text-slate-600 transition">Reset</a>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full data-table">
                    <thead>
                        <tr>
                            @foreach(['Tiket', 'Nama / Seksi', 'Kanal', 'Aduan', 'Status', 'Deadline', 'Aksi'] as $h)
                                <th>{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pengaduans as $p)
                            @php $tlId = optional($p->tindakLanjut)->id; @endphp
                            <tr>
                                <td class="font-mono text-xs text-slate-400">{{ $p->nomor_tiket }}</td>
                                <td>
                                    <div class="font-semibold text-slate-800 text-sm">{{ $p->nama }}</div>
                                    <span class="text-[10px] font-medium text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded-md">
                                        {{ $p->seksi_tujuan }}
                                    </span>
                                </td>
                                <td class="text-xs text-slate-500">{{ $p->kanal_pengaduan }}</td>
                                <td>
                                    <a href="{{ route('pengaduan.show', $p->id) }}"
                                       class="text-xs px-2.5 py-1.5 rounded-lg font-semibold
                                              bg-indigo-50 text-indigo-700 border border-indigo-200
                                              hover:bg-indigo-100 transition whitespace-nowrap">
                                        Lihat Detail
                                    </a>
                                </td>
                                <td><span class="badge badge-{{ $p->status }}">{{ ucfirst($p->status) }}</span></td>
                                <td class="text-xs text-slate-500">
                                    {{ \Carbon\Carbon::parse($p->deadline_tindak_lanjut)->format('d M Y') }}
                                </td>
                                <td>
                                    <div class="flex items-center gap-1.5">
                                        <button onclick="openModalTL('{{ $p->id }}','{{ $p->nomor_tiket }}','{{ addslashes($p->nama) }}','{{ $p->status }}','{{ addslashes($p->keterangan_admin ?? '') }}','{{ $tlId }}')"
                                            class="text-xs px-2.5 py-1.5 rounded-lg font-bold transition whitespace-nowrap
                                            {{ $tlId ? 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100' : 'bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100' }}">
                                            {{ $tlId ? 'Edit TL' : 'TL' }}
                                        </button>
                                        <a href="{{ route('dashboard.pengaduan.downloadPdf', $p->nomor_tiket) }}"
                                           title="PDF"
                                           class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200
                                                  text-slate-400 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        </a>
                                        <a href="{{ route('dashboard.pengaduan.downloadDocx', $p->nomor_tiket) }}"
                                           title="DOCX"
                                           class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200
                                                  text-slate-400 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-14 text-center text-slate-400 text-sm">
                                    Belum ada pengaduan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-3 border-t border-slate-100">
                {{ $pengaduans->links() }}
            </div>
        </div>

    </div>{{-- end p-6 --}}

    {{-- MODAL TINDAK LANJUT --}}
    <x-modal-tindak-lanjut />

    <x-slot name="scripts">
    <script>
    Chart.defaults.font.family = "'Plus Jakarta Sans','sans-serif'";
    Chart.defaults.font.size   = 12;
    Chart.defaults.color       = '#94a3b8';
    const G = { color:'#f1f5f9', drawBorder:false };
    const T = { color:'#94a3b8' };

    new Chart(document.getElementById('chartStatus'), {
        type:'pie',
        data:{
            labels:{!! json_encode($statusStats->pluck('status')->map(fn($s)=>ucfirst($s))->values()) !!},
            datasets:[{
                data:{!! json_encode($statusStats->pluck('jumlah')->values()) !!},
                backgroundColor:{!! json_encode($statusStats->pluck('status')->map(fn($s)=>match($s){'pending'=>'#f59e0b','proses'=>'#3b82f6','diteruskan'=>'#8b5cf6','selesai'=>'#10b981',default=>'#94a3b8'})->values()) !!},
                borderWidth:2,borderColor:'#fff',hoverOffset:6,
            }],
        },
        options:{responsive:true,maintainAspectRatio:true,plugins:{legend:{display:false}}},
    });

    new Chart(document.getElementById('chartSeksi'), {
        type:'bar',
        data:{
            labels:{!! json_encode($performaSeksi->pluck('nama')->values()) !!},
            datasets:[{
                label:'Selesai (%)',
                data:{!! json_encode($performaSeksi->pluck('pct')->values()) !!},
                backgroundColor:{!! json_encode($performaSeksi->pluck('pct')->map(fn($v)=>$v>=80?'#10b981':($v>=50?'#3b82f6':'#f59e0b'))->values()) !!},
                borderRadius:6,borderSkipped:false,
            }],
        },
        options:{
            indexAxis:'y',responsive:true,maintainAspectRatio:false,
            scales:{x:{grid:G,ticks:{...T,callback:v=>v+'%'},min:0,max:100},y:{grid:{display:false},ticks:T}},
            plugins:{legend:{display:false}},
        },
    });

    const kP=['#3b82f6','#10b981','#8b5cf6','#f59e0b','#ef4444','#06b6d4'];
    const kL={!! json_encode($kanalStats->pluck('kanal_pengaduan')->values()) !!};
    new Chart(document.getElementById('chartKanal'), {
        type:'bar',
        data:{
            labels:kL,
            datasets:[{label:'Jumlah',data:{!! json_encode($kanalStats->pluck('jumlah')->values()) !!},
            backgroundColor:kL.map((_,i)=>kP[i%kP.length]),borderRadius:6,borderSkipped:false}],
        },
        options:{
            responsive:true,maintainAspectRatio:false,
            scales:{x:{grid:{display:false},ticks:T},y:{grid:G,ticks:{...T,stepSize:1},beginAtZero:true}},
            plugins:{legend:{display:false}},
        },
    });

    new Chart(document.getElementById('chartSla'), {
        type:'doughnut',
        data:{
            labels:['On Track','Selesai','H-1','Over SLA'],
            datasets:[{data:[{{ $slaOnTrack }},{{ $selesai }},{{ $slaHMinus1 }},{{ $slaOver }}],
            backgroundColor:['#10b981','#3b82f6','#f59e0b','#ef4444'],borderWidth:2,borderColor:'#fff',hoverOffset:5}],
        },
        options:{cutout:'72%',responsive:true,maintainAspectRatio:true,plugins:{legend:{display:false}}},
    });

    const ssl={!! json_encode($performaSeksi->pluck('nama')->values()) !!};
    const ssd={!! json_encode($performaSeksi->map(fn($s)=>['pending'=>$s['pending']??0,'proses'=>$s['proses']??0,'diteruskan'=>$s['diteruskan']??0,'selesai'=>$s['selesai']??0])->values()) !!};
    new Chart(document.getElementById('chartSeksiStacked'), {
        type:'bar',
        data:{
            labels:ssl,
            datasets:[
                {label:'Pending',   data:ssd.map(s=>s.pending),    backgroundColor:'#f59e0b',borderSkipped:false},
                {label:'Proses',    data:ssd.map(s=>s.proses),     backgroundColor:'#3b82f6',borderSkipped:false},
                {label:'Diteruskan',data:ssd.map(s=>s.diteruskan), backgroundColor:'#8b5cf6',borderSkipped:false},
                {label:'Selesai',   data:ssd.map(s=>s.selesai),    backgroundColor:'#10b981',borderSkipped:false,borderRadius:{topLeft:4,topRight:4}},
            ],
        },
        options:{
            responsive:true,maintainAspectRatio:false,
            scales:{x:{stacked:true,grid:{display:false},ticks:T},y:{stacked:true,grid:G,ticks:{...T,stepSize:1},beginAtZero:true}},
            plugins:{legend:{display:true,position:'bottom',labels:{boxWidth:10,padding:14,color:'#64748b',usePointStyle:true}}},
        },
    });
    </script>
    </x-slot>

</x-layouts.dashboard>