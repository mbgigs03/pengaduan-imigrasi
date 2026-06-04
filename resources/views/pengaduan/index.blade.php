{{-- resources/views/pengaduan/index.blade.php --}}
<x-layouts.dashboard>
    <x-slot name="header">Monitoring Pengaduan</x-slot>

    <div class="p-6 space-y-5 max-w-[1200px]">

        {{-- ── STAT CARDS ─────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

            {{-- Total Pengaduan (Penanganan) --}}
            <div class="stat-card flex items-start gap-4">
                <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0
                                 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Pengaduan</p>
                    <p class="text-2xl font-bold text-slate-800 mt-0.5">{{ $countPengaduan }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">kasus penanganan</p>
                </div>
            </div>

            {{-- Total Informasi --}}
            <div class="stat-card flex items-start gap-4">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Pemberian Informasi</p>
                    <p class="text-2xl font-bold text-slate-800 mt-0.5">{{ $countInformasi }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">permintaan informasi</p>
                </div>
            </div>

        </div>

        {{-- ── CONTAINER UTAMA ─────────────────────────────────────── --}}
        <div class="bg-white rounded-[14px] border border-slate-100 overflow-hidden">

            {{-- ── TAB NAVIGATION ──────────────────────────────────── --}}
            <div class="flex items-center gap-1 px-5 pt-4 border-b border-slate-100">

                @php
                    $tabs = [
                        'pengaduan' => [
                            'label' => 'Penanganan Pengaduan',
                            'count' => $countPengaduan,
                            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
                            'active_color' => 'text-blue-700 border-blue-600',
                            'badge_color'  => 'bg-blue-100 text-blue-700',
                        ],
                        'informasi' => [
                            'label' => 'Pemberian Informasi',
                            'count' => $countInformasi,
                            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                            'active_color' => 'text-emerald-700 border-emerald-600',
                            'badge_color'  => 'bg-emerald-100 text-emerald-700',
                        ],
                    ];
                @endphp

                @foreach($tabs as $tabKey => $tab)
                    <a href="{{ route('pengaduan.index', array_merge(request()->except(['tab', 'page']), ['tab' => $tabKey])) }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold border-b-2 -mb-px
                              transition-colors whitespace-nowrap
                              {{ $activeTab === $tabKey
                                    ? $tab['active_color']
                                    : 'text-slate-400 border-transparent hover:text-slate-600' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            {!! $tab['icon'] !!}
                        </svg>
                        {{ $tab['label'] }}
                        <span class="text-[10px] font-black px-1.5 py-0.5 rounded-full
                                     {{ $activeTab === $tabKey ? $tab['badge_color'] : 'bg-slate-100 text-slate-400' }}">
                            {{ $tab['count'] }}
                        </span>
                    </a>
                @endforeach
            </div>

            {{-- ── DESKRIPSI TAB ────────────────────────────────────── --}}
            <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/60">
                @if($activeTab === 'pengaduan')
                    <p class="text-xs text-slate-500">
                        <span class="font-bold text-slate-700">Penanganan Pengaduan</span>
                        — Laporan yang memerlukan tindak lanjut, investigasi, atau eskalasi ke atasan.
                        Setiap tiket wajib direspons dalam batas SLA yang ditetapkan.
                    </p>
                @else
                    <p class="text-xs text-slate-500">
                        <span class="font-bold text-slate-700">Pemberian Informasi</span>
                        — Permintaan informasi yang telah terjawab langsung oleh petugas.
                        Status otomatis <span class="font-semibold text-emerald-600">Selesai</span>
                        dan tidak memerlukan tindak lanjut lebih lanjut.
                    </p>
                @endif
            </div>

            {{-- ── FILTER FORM ──────────────────────────────────────── --}}
            <div class="px-5 py-4 bg-slate-50 border-b border-slate-100" x-data="filterPanel()" x-init="init()">
                <form method="GET" action="{{ route('pengaduan.index') }}">
                    <input type="hidden" name="tab" value="{{ $activeTab }}">

                    {{-- Baris 1: Preset Periode & Pencarian --}}
                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Periode</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach([
                                    ['', 'Semua'],
                                    ['daily', 'Hari Ini'],
                                    ['weekly', '7 Hari Terakhir'],
                                    ['monthly', 'Bulan Ini'],
                                    ['custom', 'Rentang Khusus'],
                                ] as [$val, $label])
                                    <label class="cursor-pointer">
                                        <input type="radio" name="periode" value="{{ $val }}"
                                            x-model="periode"
                                            class="sr-only"
                                            {{ (request('periode') === $val || (!request('periode') && $val === '')) ? 'checked' : '' }}>
                                        <span class="inline-block px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all"
                                            :class="periode === '{{ $val }}' ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'">
                                            {{ $label }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="w-full md:w-auto">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Pencarian</label>
                            <div class="relative">
                                <input type="text" name="keyword" value="{{ request('keyword') }}"
                                    placeholder="Nama pemohon / nomor tiket..."
                                    class="w-full md:w-64 pl-9 pr-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition">
                                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Baris 2: Rentang Kustom (Kondisional) --}}
                    <div x-show="periode === 'custom'" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="mb-4 p-3 bg-blue-50/50 border border-blue-100 rounded-xl grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                            <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" x-model="startDate" @change="validateDates()"
                                class="w-full text-sm bg-white border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                            <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" x-model="endDate" @change="validateDates()" :min="startDate"
                                class="w-full text-sm bg-white border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                :class="dateError ? 'border-red-400 focus:border-red-400 focus:ring-red-100' : ''">
                            <p x-show="dateError" class="text-red-500 text-[10px] mt-1 font-semibold" x-text="dateError"></p>
                        </div>
                    </div>

                    {{-- Baris 3: Filter Kategori & Status --}}
                    <div class="flex flex-col sm:flex-row items-end gap-3 border-t border-slate-200/60 pt-4">
                        <div class="w-full sm:w-1/3">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Seksi / Kategori</label>
                            <select name="seksi" class="w-full text-sm bg-white border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                                <option value="">Semua Seksi</option>
                                @foreach(['Tikkim','Doklanintalkim','Inteldakim','Tata Usaha'] as $s)
                                    <option value="{{ $s }}" {{ request('seksi') === $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if($activeTab === 'pengaduan')
                        <div class="w-full sm:w-1/3">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Status Tiket</label>
                            <select name="status" class="w-full text-sm bg-white border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                                <option value="">Semua Status</option>
                                @foreach(\App\Helpers\StatusHelper::options() as $val => $lbl)
                                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="w-full sm:w-auto sm:ml-auto flex gap-2">
                            <a href="{{ route('pengaduan.index', ['tab' => $activeTab]) }}"
                               class="flex-1 sm:flex-none px-4 py-2 bg-white border border-slate-200 text-slate-600 text-sm font-bold rounded-lg hover:bg-slate-50 transition text-center">
                                Reset
                            </a>
                            <button type="submit" :disabled="periode === 'custom' && !!dateError"
                                    class="flex-1 sm:flex-none px-5 py-2 text-white text-sm font-bold rounded-lg shadow-sm transition flex items-center justify-center gap-2
                                    {{ $activeTab === 'informasi' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-blue-600 hover:bg-blue-700' }} disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 010 2H4a1 1 0 01-1-1zM6 10h12M9 16h6"/></svg>
                                Terapkan
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- ══════════════════════════════════════════════════════ --}}
            {{-- TAB: PENANGANAN PENGADUAN                             --}}
            {{-- ══════════════════════════════════════════════════════ --}}
            @if($activeTab === 'pengaduan')
                <div class="overflow-x-auto">
                    <table class="w-full data-table">
                        <thead>
                            <tr>
                                @foreach(['Tiket','Nama / Seksi','Kanal','Status','Deadline','Aksi'] as $h)
                                    <th>{{ $h }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pengaduans as $p)
                                @php
                                    $isDitolak = $p->status === 'ditolak';
                                    $isSelesai = $p->status === 'selesai';
                                    $isClosed  = $isDitolak || $isSelesai;
                                    $tlId      = optional($p->tindakLanjut)->id;
                                    $isFaq     = $isSelesai && !$tlId;
                                    $over      = !$isClosed && \Carbon\Carbon::now()->gt($p->deadline_tindak_lanjut);
                                @endphp
                                <tr class="border-b border-slate-100 hover:bg-slate-50">

                                    {{-- Tiket --}}
                                    <td class="p-3 font-mono text-xs text-slate-500">
                                        {{ $p->nomor_tiket }}
                                    </td>

                                    {{-- Nama / Seksi --}}
                                    <td class="p-3">
                                        <div class="font-semibold text-slate-800 text-sm">{{ $p->nama }}</div>
                                        <span class="text-[10px] font-medium text-slate-400 bg-slate-100
                                                     px-1.5 py-0.5 rounded-md mt-1 inline-block">
                                            {{ $p->seksi_tujuan }}
                                        </span>
                                    </td>

                                    {{-- Kanal --}}
                                    <td class="p-3 text-xs text-slate-500">{{ $p->kanal_pengaduan }}</td>

                                    {{-- Status --}}
                                    <td class="p-3">
                                        <span class="badge {{ \App\Helpers\StatusHelper::badgeClass($p->status) }}">
                                            {{ \App\Helpers\StatusHelper::label($p->status) }}
                                        </span>
                                    </td>

                                    {{-- Deadline --}}
                                    <td class="p-3">
                                        @if($isClosed)
                                            <div class="text-xs font-bold {{ $isDitolak ? 'text-red-500' : 'text-emerald-600' }}">
                                                {{ $isDitolak ? 'Ditolak' : 'Tuntas' }}
                                            </div>
                                            <div class="text-[10px] text-slate-400">
                                                {{ $isDitolak ? 'Laporan tidak valid' : ($isFaq ? 'Diselesaikan sistem (FAQ)' : 'Telah ditindaklanjuti') }}
                                            </div>
                                        @else
                                            <div class="text-xs {{ $over ? 'text-red-600 font-bold' : 'text-slate-500' }}">
                                                {{ \Carbon\Carbon::parse($p->deadline_tindak_lanjut)->format('d M Y') }}
                                            </div>
                                            <div class="text-[10px] text-slate-400">
                                                {{ \Carbon\Carbon::parse($p->deadline_tindak_lanjut)->diffForHumans() }}
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="p-3">
                                        <div class="flex items-center gap-1.5">
                                            <a href="{{ route('pengaduan.show', $p->id) }}"
                                               class="text-xs px-2.5 py-1.5 rounded-lg font-semibold
                                                      bg-indigo-50 text-indigo-700 border border-indigo-200
                                                      hover:bg-indigo-100 transition whitespace-nowrap">
                                                Detail
                                            </a>

                                            {{-- Tombol Tindak Lanjut: nonaktif jika ditolak atau selesai --}}
                                            @if($isClosed)
                                                <span class="text-[11px] px-3 py-1.5 rounded-lg font-bold
                                                             cursor-not-allowed whitespace-nowrap
                                                             {{ $isDitolak
                                                                ? 'bg-red-50 text-red-400 border border-red-200'
                                                                : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                                    @if($isDitolak)
                                                        <svg class="w-3.5 h-3.5 inline mr-1" fill="none"
                                                             stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                        Ditolak
                                                    @else
                                                        <svg class="w-3.5 h-3.5 inline mr-1" fill="none"
                                                             stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                        Tuntas
                                                    @endif
                                                </span>
                                            @else
                                                <button
                                                    onclick="openModalTL(
                                                        '{{ $p->id }}',
                                                        '{{ $p->nomor_tiket }}',
                                                        '{{ addslashes($p->nama) }}',
                                                        '{{ $p->status }}',
                                                        '{{ addslashes($p->keterangan_admin ?? '') }}',
                                                        '{{ $tlId }}'
                                                    )"
                                                    class="text-xs px-2.5 py-1.5 rounded-lg font-bold
                                                           transition whitespace-nowrap
                                                           {{ $tlId
                                                               ? 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100'
                                                               : 'bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100' }}">
                                                    {{ $tlId ? 'Edit TL' : 'TL' }}
                                                </button>
                                            @endif

                                            {{-- PDF --}}
                                            <a href="{{ route('dashboard.pengaduan.downloadPdf', $p->nomor_tiket) }}"
                                               title="Unduh PDF" target="_blank"
                                               class="w-7 h-7 flex items-center justify-center rounded-lg
                                                      border border-slate-200 text-slate-400
                                                      hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414
                                                             A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-14 text-center">
                                        <div class="flex flex-col items-center gap-2">
                                            <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-slate-400" fill="none"
                                                     stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2
                                                             2 0 00-2-2h-2"/>
                                                </svg>
                                            </div>
                                            <p class="text-sm font-semibold text-slate-500">Belum ada pengaduan</p>
                                            <p class="text-xs text-slate-400">Coba ubah filter pencarian</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            {{-- ══════════════════════════════════════════════════════ --}}
            {{-- TAB: PEMBERIAN INFORMASI                              --}}
            {{-- ══════════════════════════════════════════════════════ --}}
            @else
                <div class="overflow-x-auto">
                    <table class="w-full data-table">
                        <thead>
                            <tr>
                                @foreach(['Tiket','Nama / Seksi','Topik / Pertanyaan','Status','Deadline','Aksi'] as $h)
                                    <th class="text-left">{{ $h }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pengaduans as $p)
                                @php
                                    $isDitolak = $p->status === 'ditolak';
                                    $isSelesai = $p->status === 'selesai';
                                    $isClosed  = $isDitolak || $isSelesai;
                                    $tlId      = optional($p->tindakLanjut)->id;
                                    $isFaq     = $isSelesai && !$tlId; // Tiket selesai tanpa ada TL = Dijawab FAQ
                                    $over      = !$isClosed && \Carbon\Carbon::now()->gt($p->deadline_tindak_lanjut);
                                @endphp
                                <tr class="border-b border-slate-100 hover:bg-slate-50">
                                    {{-- Tiket --}}
                                    <td class="p-3 font-mono text-xs text-slate-500">{{ $p->nomor_tiket }}</td>

                                    {{-- Nama / Seksi --}}
                                    <td class="p-3">
                                        <div class="font-semibold text-slate-800 text-sm">{{ $p->nama }}</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">{{ $p->kanal_pengaduan }}</div>
                                    </td>

                                    {{-- Preview Topik / Aduan --}}
                                    <td class="p-3 max-w-[220px]">
                                        @php
                                            $aduanLines = explode("\n", $p->aduan);
                                            $topikLine  = count($aduanLines) > 1 && str_starts_with($aduanLines[0], '[Kategori FAQ')
                                                          ? trim($aduanLines[0], '[]') : null;
                                            $preview    = $topikLine ? Str::limit(implode(' ', array_slice($aduanLines, 2)), 50) : Str::limit($p->aduan, 60);
                                        @endphp
                                        @if($topikLine)
                                            <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 mb-1">
                                                {{ $topikLine }}
                                            </span><br>
                                        @else
                                            <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200 mb-1">
                                                Pertanyaan Manual
                                            </span><br>
                                        @endif
                                        <span class="text-xs text-slate-500 leading-snug">{{ $preview }}</span>
                                    </td>

                                    {{-- Status --}}
                                    <td class="p-3">
                                        <span class="badge {{ \App\Helpers\StatusHelper::badgeClass($p->status) }}">
                                            {{ \App\Helpers\StatusHelper::label($p->status) }}
                                        </span>
                                    </td>

                                    {{-- Deadline --}}
                                    <td class="p-3">
                                        @if($isClosed)
                                            <div class="text-xs font-bold {{ $isDitolak ? 'text-red-500' : 'text-emerald-600' }}">
                                                {{ $isDitolak ? 'Ditolak' : 'Tuntas' }}
                                            </div>
                                            <div class="text-[10px] text-slate-400">
                                                {{ $isDitolak ? 'Laporan tidak valid' : ($isFaq ? 'Dijawab Otomatis (FAQ)' : 'Telah ditindaklanjuti') }}
                                            </div>
                                        @else
                                            <div class="text-xs {{ $over ? 'text-red-600 font-bold' : 'text-slate-500' }}">
                                                {{ \Carbon\Carbon::parse($p->deadline_tindak_lanjut)->format('d M Y') }}
                                            </div>
                                            <div class="text-[10px] text-slate-400">
                                                {{ \Carbon\Carbon::parse($p->deadline_tindak_lanjut)->diffForHumans() }}
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="p-3">
                                        <div class="flex items-center gap-1.5">
                                            @if($isClosed)
                                                <a href="{{ route('pengaduan.show', $p->id) }}" class="text-xs px-2.5 py-1.5 rounded-lg font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition whitespace-nowrap">Detail</a>
                                                @if($isFaq)
                                                    <span class="text-[11px] px-3 py-1.5 rounded-lg font-bold cursor-not-allowed bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        <svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> FAQ
                                                    </span>
                                                @endif
                                            @else
                                                <button onclick="openModalTL('{{ $p->id }}', '{{ $p->nomor_tiket }}', '{{ addslashes($p->nama) }}', '{{ $p->status }}', '{{ addslashes($p->keterangan_admin ?? '') }}', '{{ $tlId }}')"
                                                        class="text-xs px-2.5 py-1.5 rounded-lg font-bold transition whitespace-nowrap {{ $tlId ? 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100' : 'bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100' }}">
                                                    {{ $tlId ? 'Edit TL' : 'Tindak Lanjut' }}
                                                </button>
                                            @endif

                                            {{-- 🟢 TAMBAHAN: Tombol Download PDF --}}
                                            <a href="{{ route('dashboard.pengaduan.downloadPdf', $p->nomor_tiket) }}"
                                               title="Unduh PDF" target="_blank"
                                               class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414 A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-14 text-center">
                                        <div class="flex flex-col items-center gap-2">
                                            <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-emerald-400" fill="none"
                                                     stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                            <p class="text-sm font-semibold text-slate-500">
                                                Belum ada data pemberian informasi
                                            </p>
                                            <p class="text-xs text-slate-400">Coba ubah filter pencarian</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- ── PAGINATION ───────────────────────────────────────── --}}
            <div class="px-5 py-3 border-t border-slate-100">
                {{ $pengaduans->links() }}
            </div>
        </div>
    </div>

    {{-- Modal TL hanya relevan untuk tab pengaduan, tapi tidak masalah selalu di-render --}}
    <x-modal-tindak-lanjut />

    <script>
    function filterPanel() {
        return {
            periode:   '{{ request("periode", "") }}',
            startDate: '{{ request("start_date", "") }}',
            endDate:   '{{ request("end_date", "") }}',
            dateError: '',

            init() {
                this.validateDates();
            },

            validateDates() {
                if (this.periode !== 'custom') {
                    this.dateError = '';
                    return;
                }
                if (this.startDate && this.endDate) {
                    if (new Date(this.endDate) < new Date(this.startDate)) {
                        this.dateError = 'Tanggal akhir tidak boleh mendahului tanggal awal.';
                    } else {
                        this.dateError = '';
                    }
                } else {
                    this.dateError = '';
                }
            }
        };
    }
    </script>

</x-layouts.dashboard>