{{-- resources/views/pengaduan/sla.blade.php --}}
<x-layouts.dashboard>
    <x-slot name="header">Monitoring SLA</x-slot>

    <div class="p-6 space-y-5 max-w-[1400px]">

        {{-- ═══ COUNTER CARDS ═══════════════════════════════════ --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- Total aktif --}}
            <div class="bg-white rounded-2xl border border-slate-100 p-5 flex items-start gap-4">
                <div class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Aktif</p>
                    <p class="text-2xl font-bold text-slate-800 mt-0.5">{{ number_format($counters['total']) }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">belum selesai</p>
                </div>
            </div>

            {{-- Over SLA --}}
            <div class="bg-white rounded-2xl border p-5 flex items-start gap-4
                        {{ $counters['over'] > 0 ? 'border-red-200 bg-red-50/40 shadow-[0_0_0_1px_rgba(239,68,68,.15),0_4px_24px_rgba(239,68,68,.10)]' : 'border-slate-100' }}">
                <div class="w-11 h-11 rounded-xl {{ $counters['over'] > 0 ? 'bg-red-100' : 'bg-slate-100' }} flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 {{ $counters['over'] > 0 ? 'text-red-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Melewati SLA</p>
                    <p class="text-2xl font-bold {{ $counters['over'] > 0 ? 'text-red-600' : 'text-slate-800' }} mt-0.5">
                        {{ $counters['over'] }}
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">sudah melewati 3 hari</p>
                </div>
            </div>

            {{-- H-1 --}}
            <div class="bg-white rounded-2xl border p-5 flex items-start gap-4
                        {{ $counters['warn'] > 0 ? 'border-amber-200 bg-amber-50/40' : 'border-slate-100' }}">
                <div class="w-11 h-11 rounded-xl {{ $counters['warn'] > 0 ? 'bg-amber-100' : 'bg-slate-100' }} flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 {{ $counters['warn'] > 0 ? 'text-amber-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">H-1 Deadline</p>
                    <p class="text-2xl font-bold {{ $counters['warn'] > 0 ? 'text-amber-600' : 'text-slate-800' }} mt-0.5">
                        {{ $counters['warn'] }}
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">dalam 24 jam ke depan</p>
                </div>
            </div>

            {{-- On Track --}}
            <div class="bg-white rounded-2xl border border-slate-100 p-5 flex items-start gap-4">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">On Track</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-0.5">{{ $counters['ok'] }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">lebih dari 24 jam tersisa</p>
                </div>
            </div>
        </div>

        {{-- ═══ TABEL PRIORITAS SLA ══════════════════════════════ --}}
        <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <div>
                    <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                        Antrian Prioritas SLA
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Diurutkan: Terlambat → H-1 → On Track · Hanya pengaduan belum selesai
                    </p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-50 text-red-600 border border-red-200 font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                        Terlambat
                    </span>
                    <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50 text-amber-600 border border-amber-200 font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        H-1
                    </span>
                    <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200 font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        On Track
                    </span>
                </div>
            </div>

            {{-- Filter Bar --}}
            <form method="GET" action="{{ route('pengaduan.sla') }}"
                  class="flex flex-wrap items-center gap-2.5 px-6 py-3 bg-slate-50/70 border-b border-slate-100">

                <input type="text" name="keyword" value="{{ request('keyword') }}"
                    placeholder="Cari nama / nomor tiket…"
                    class="text-sm border border-slate-200 rounded-xl px-3 py-2 w-52
                           focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400
                           bg-white placeholder:text-slate-400">

                @if (auth()->user()->profile->role === 'tikkim')
                    <select name="seksi"
                            class="text-sm border border-slate-200 rounded-xl px-3 py-2 bg-white text-slate-600
                                   focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400">
                        <option value="">Semua Seksi</option>
                        @foreach ($seksiList as $s)
                            <option value="{{ $s }}" {{ request('seksi') === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                @endif

                <select name="status"
                        class="text-sm border border-slate-200 rounded-xl px-3 py-2 bg-white text-slate-600
                               focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400">
                    <option value="">Semua Status</option>
                    @foreach (['pending'=>'Pending','proses'=>'Proses','diteruskan'=>'Diteruskan'] as $v => $l)
                        <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>

                <select name="kanal"
                        class="text-sm border border-slate-200 rounded-xl px-3 py-2 bg-white text-slate-600
                               focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400">
                    <option value="">Semua Kanal</option>
                    @foreach ($kanalList as $k)
                        <option value="{{ $k }}" {{ request('kanal') === $k ? 'selected' : '' }}>{{ $k }}</option>
                    @endforeach
                </select>

                <select name="sla"
                        class="text-sm border border-slate-200 rounded-xl px-3 py-2 bg-white text-slate-600
                               focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400">
                    <option value="">Semua SLA</option>
                    <option value="over" {{ request('sla') === 'over' ? 'selected' : '' }}>Terlambat</option>
                    <option value="warn" {{ request('sla') === 'warn' ? 'selected' : '' }}>H-1</option>
                    <option value="ok"   {{ request('sla') === 'ok'   ? 'selected' : '' }}>On Track</option>
                </select>

                <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
                    Filter
                </button>
                <a href="{{ route('pengaduan.sla') }}"
                   class="text-sm text-slate-400 hover:text-slate-600 transition">Reset</a>
            </form>

            {{-- Tabel --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            @foreach(['#', 'Tiket', 'Nama & Seksi', 'Status', 'Kanal', 'Deadline', 'Sisa Waktu', 'Aksi'] as $h)
                                <th class="text-left py-3 px-4 text-[11px] font-bold text-slate-400 uppercase tracking-wide whitespace-nowrap">
                                    {{ $h }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pengaduans as $i => $p)
                            @php
                                $isOver = $p->sla_status === 'over';
                                $isWarn = $p->sla_status === 'warn';
                                $tlId   = optional($p->tindakLanjut)->id;

                                // Row background + glow berdasarkan prioritas
                                $rowClass = match(true) {
                                    $isOver => 'bg-red-50/60 border-l-[3px] border-red-400',
                                    $isWarn => 'bg-amber-50/50 border-l-[3px] border-amber-400',
                                    default => 'border-l-[3px] border-transparent',
                                };

                                // Urutan visual di kolom pertama
                                $priorityBadge = match(true) {
                                    $isOver => '<span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-red-500 text-white text-[9px] font-black animate-pulse">!</span>',
                                    $isWarn => '<span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-500 text-white text-[9px] font-black">~</span>',
                                    default => '<span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-slate-200 text-slate-500 text-[9px] font-bold">' . ($pengaduans->firstItem() + $i) . '</span>',
                                };

                                // Countdown text
                                $deadline  = \Carbon\Carbon::parse($p->deadline_tindak_lanjut);
                                $diffHours = abs((int) now()->diffInHours($deadline, false));
                                $diffDays  = abs((int) now()->diffInDays($deadline, false));

                                if ($isOver) {
                                    $countdownText  = $diffDays > 0 ? "{$diffDays} hari" : "{$diffHours} jam";
                                    $countdownLabel = 'Terlambat ' . $countdownText;
                                    $countdownClass = 'text-red-600 font-bold';
                                } elseif ($isWarn) {
                                    $countdownText  = $diffHours > 0 ? "~{$diffHours} jam" : 'Kurang 1 jam';
                                    $countdownLabel = $countdownText . ' lagi';
                                    $countdownClass = 'text-amber-600 font-bold';
                                } else {
                                    $countdownLabel = $diffDays > 1 ? "{$diffDays} hari lagi" : "{$diffHours} jam lagi";
                                    $countdownClass = 'text-emerald-600 font-medium';
                                }
                            @endphp

                            <tr class="border-b border-slate-50 hover:bg-slate-50/80 transition {{ $rowClass }}">

                                {{-- Priority indicator --}}
                                <td class="py-3 px-4">
                                    {!! $priorityBadge !!}
                                </td>

                                {{-- Tiket --}}
                                <td class="py-3 px-4">
                                    <span class="font-mono text-xs text-slate-500 tracking-tight">
                                        {{ $p->nomor_tiket }}
                                    </span>
                                </td>

                                {{-- Nama & Seksi --}}
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-slate-800 text-sm leading-tight">{{ $p->nama }}</div>
                                    <span class="text-[10px] font-medium text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded-md mt-0.5 inline-block">
                                        {{ $p->seksi_tujuan }}
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td class="py-3 px-4">
                                    <x-status-pill :status="$p->status" />
                                </td>

                                {{-- Kanal --}}
                                <td class="py-3 px-4 text-xs text-slate-500">{{ $p->kanal_pengaduan }}</td>

                                {{-- Deadline (tanggal) --}}
                                <td class="py-3 px-4">
                                    <div class="text-xs {{ $isOver ? 'text-red-600 font-bold' : 'text-slate-600' }}">
                                        {{ $deadline->format('d M Y') }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        {{ $deadline->format('H:i') }} WIB
                                    </div>
                                </td>

                                {{-- Sisa waktu / countdown --}}
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-1.5">
                                        @if ($isOver)
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse flex-shrink-0"></span>
                                        @elseif ($isWarn)
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 flex-shrink-0"></span>
                                        @else
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 flex-shrink-0"></span>
                                        @endif
                                        <span class="text-xs {{ $countdownClass }} whitespace-nowrap">
                                            {{ $countdownLabel }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Aksi --}}
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-1.5">

                                        {{-- Tindak Lanjut --}}
                                        <button
                                            onclick="openModalTL(
                                                '{{ $p->id }}','{{ $p->nomor_tiket }}',
                                                '{{ addslashes($p->nama) }}','{{ $p->status }}',
                                                '{{ addslashes($p->keterangan_admin ?? '') }}',
                                                '{{ $tlId }}')"
                                            class="text-xs px-2.5 py-1.5 rounded-lg font-bold whitespace-nowrap transition
                                                {{ $isOver
                                                    ? 'bg-red-600 text-white hover:bg-red-700 shadow-sm'
                                                    : ($tlId
                                                        ? 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100'
                                                        : 'bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100') }}">
                                            {{ $isOver ? '⚡ Segera!' : ($tlId ? 'Edit TL' : 'Tindak Lanjut') }}
                                        </button>

                                        {{-- Download --}}
                                        <div class="relative" x-data="{ open: false }">
                                            <button @click="open = !open"
                                                    class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200
                                                           text-slate-400 hover:bg-slate-50 transition">
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
                                                 class="absolute right-0 z-30 mt-1 w-40 bg-white rounded-xl border border-slate-100 shadow-xl py-1"
                                                 style="display:none">
                                                <a href="{{ route('dashboard.pengaduan.downloadPdf', $p->nomor_tiket) }}"
                                                   class="flex items-center gap-2 px-3 py-2 text-xs text-slate-700 hover:bg-red-50 hover:text-red-700 transition">
                                                    <svg class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                                    <div>
                                                        <div class="font-semibold">Unduh PDF</div>
                                                        <div class="text-[10px] {{ $p->pdf_url ? 'text-emerald-500' : 'text-slate-400' }}">
                                                            {{ $p->pdf_url ? 'Tersedia' : 'Generate otomatis' }}
                                                        </div>
                                                    </div>
                                                </a>
                                                <a href="{{ route('dashboard.pengaduan.downloadDocx', $p->nomor_tiket) }}"
                                                   class="flex items-center gap-2 px-3 py-2 text-xs text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition">
                                                    <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    <div>
                                                        <div class="font-semibold">Unduh DOCX</div>
                                                        <div class="text-[10px] text-slate-400">Arsip dokumen</div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>

                                        {{-- Lihat Detail --}}
                                        <a href="{{ route('pengaduan.show', $p->id) }}"
                                           class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200
                                                  text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-200 transition"
                                           title="Lihat Detail">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>

                                    </div>
                                </td>

                            </tr>

                        @empty
                            <tr>
                                <td colspan="8" class="py-20 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center">
                                            <svg class="w-7 h-7 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-bold text-slate-600">Semua Aman</p>
                                        <p class="text-xs text-slate-400 max-w-xs text-center">
                                            Tidak ada pengaduan yang mendekati atau melewati batas SLA
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-3 border-t border-slate-100 bg-slate-50/50">
                {{ $pengaduans->links() }}
            </div>
        </div>

    </div>

    <x-modal-tindak-lanjut />

</x-layouts.dashboard>