{{-- resources/views/rekapitulasi/index.blade.php --}}
<x-layouts.dashboard>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Rekapitulasi Pengaduan
            </h2>
            
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            {{-- ═══ FLASH MESSAGES ═══ --}}
            @if (session('success'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            {{-- ═══ PANEL FILTER ═══ --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6"
                 x-data="filterPanel()" x-init="init()">

                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-5">
                    Filter Data
                </h3>

                <form id="filter-form" action="{{ route('rekapitulasi.index') }}" method="GET">

                    {{-- BARIS 1: Pilihan Periode Preset ─────────────── --}}
                    <div class="mb-5">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                            Periode
                        </label>
                        <div class="flex flex-wrap gap-2">
                            @foreach([
                                ['', 'Semua'],
                                ['daily',   'Hari Ini'],
                                ['weekly',  '7 Hari Terakhir'],
                                ['monthly', 'Bulan Ini'],
                                ['yearly',  'Tahun Ini'],
                                ['custom',  'Rentang Khusus'],
                            ] as [$val, $label])
                                <label class="cursor-pointer">
                                    <input type="radio" name="periode" value="{{ $val }}"
                                        x-model="periode"
                                        class="sr-only"
                                        {{ (request('periode', 'monthly') === $val || (!request('periode') && $val === '')) ? 'checked' : '' }}>
                                    <span
                                        class="inline-block px-4 py-2 rounded-xl text-sm font-medium border transition-all"
                                        :class="periode === '{{ $val }}'
                                            ? 'bg-blue-700 text-white border-blue-700'
                                            : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300'">
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- BARIS 2: Custom Date Range (conditional) ──── --}}
                    <div x-show="periode === 'custom'"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="mb-5 p-4 bg-blue-50 border border-blue-100 rounded-xl">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                    Tanggal Awal <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="start_date" id="start_date"
                                    value="{{ request('start_date') }}"
                                    x-model="startDate"
                                    @change="validateDates()"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                           focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400
                                           bg-white">
                                @error('start_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                    Tanggal Akhir <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="end_date" id="end_date"
                                    value="{{ request('end_date') }}"
                                    x-model="endDate"
                                    @change="validateDates()"
                                    :min="startDate"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                           focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400
                                           bg-white"
                                    :class="dateError ? 'border-red-400' : 'border-gray-200'">
                                <p x-show="dateError" class="text-red-500 text-xs mt-1" x-text="dateError"></p>
                                @error('end_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- BARIS 3: Filter Tambahan ─────────────────── --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5"> {{-- Ubah ke md:grid-cols-3 --}}

                        {{-- Filter Seksi (Hanya muncul untuk role Tikkim / Super Admin) --}}
                        @if(auth()->user()->profile->role === 'tikkim')
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                                Unit / Seksi
                            </label>
                            <select name="seksi"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                    focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400 bg-white">
                                <option value="">Semua Seksi (Seluruh Kantor)</option>
                                @foreach([
                                    'Doklanintalkim', 
                                    'Inteldakim', 
                                    'Tata Usaha', 
                                    'Tikkim'
                                ] as $s)
                                    <option value="{{ $s }}" {{ request('seksi') === $s ? 'selected' : '' }}>
                                        {{ $s }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                                Status
                            </label>
                            <select name="status"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                    focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400 bg-white">
                                <option value="">Semua Status</option>
                                @foreach(['pending','proses','diteruskan','selesai'] as $s)
                                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                                        {{ ucfirst($s) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                                Kanal Pengaduan
                            </label>
                            <select name="kanal"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                    focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400 bg-white">
                                <option value="">Semua Kanal</option>
                                @foreach(['Ruang Pengaduan','WhatsApp','Instagram','TikTok','Facebook','Lainnya'] as $k)
                                    <option value="{{ $k }}" {{ request('kanal') === $k ? 'selected' : '' }}>
                                        {{ $k }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- BARIS 4: Tombol Aksi ─────────────────────── --}}
                    <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-gray-100">

                        {{-- Terapkan Filter --}}
                        <button type="submit"
                            :disabled="periode === 'custom' && !!dateError"
                            class="px-5 py-2.5 bg-blue-700 hover:bg-blue-800 disabled:bg-blue-300
                                   text-white text-sm font-semibold rounded-xl transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 4a1 1 0 011-1h16a1 1 0 010 2H4a1 1 0 01-1-1zM6 10h12M9 16h6"/>
                            </svg>
                            Terapkan Filter
                        </button>

                        {{-- Reset --}}
                        <a href="{{ route('rekapitulasi.index') }}"
                            class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-500
                                   hover:bg-gray-50 transition">
                            Reset
                        </a>

                        <div class="flex-1"></div>

                        {{-- TOMBOL EKSPOR ─────────────────────────── --}}
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-400">Ekspor:</span>

                            {{-- Ekspor Excel --}}
                            <button type="button"
                                @click="exportData('excel')"
                                :disabled="periode === 'custom' && !!dateError"
                                class="inline-flex items-center gap-2 px-4 py-2.5
                                       bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-300
                                       text-white text-sm font-semibold rounded-xl transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                                             a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Excel (.xlsx)
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Hidden form untuk export (method GET, action berbeda) --}}
                <form id="export-form" action="{{ route('rekapitulasi.export.excel') }}"
                      method="GET" class="hidden">
                </form>
            </div>

            {{-- ═══ KARTU RINGKASAN ═══ --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                @foreach([
                    ['Total',       $summary['total'],      'text-gray-900',  'bg-gray-50',   '📋'],
                    ['Pending',     $summary['pending'],    'text-amber-600', 'bg-amber-50',  '⏳'],
                    ['Proses',      $summary['proses'],     'text-blue-600',  'bg-blue-50',   '🔄'],
                    ['Diteruskan',  $summary['diteruskan'], 'text-purple-600','bg-purple-50', '📤'],
                    ['Selesai',     $summary['selesai'],    'text-green-600', 'bg-green-50',  '✅'],
                    ['Over SLA',    $summary['sla_over'],   'text-red-600',   'bg-red-50',    '🚨'],
                ] as [$label, $val, $textColor, $bgColor, $icon])
                    <div class="bg-white rounded-xl border border-gray-100 p-4 flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg {{ $bgColor }} flex items-center justify-center text-base flex-shrink-0">
                            {{ $icon }}
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">{{ $label }}</p>
                            <p class="text-xl font-bold {{ $textColor }}">{{ number_format($val) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ═══ LABEL PERIODE AKTIF ═══ --}}
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-400 uppercase tracking-wide font-semibold">Menampilkan:</span>
                <span class="text-xs bg-blue-50 text-blue-700 border border-blue-100 px-3 py-1 rounded-full font-medium">
                    {{ $periodeLabel }}
                </span>
                @if(auth()->user()->profile->role === 'seksi')
                    <span class="text-xs bg-green-50 text-green-700 border border-green-100 px-3 py-1 rounded-full font-medium">
                        Seksi: {{ auth()->user()->profile->seksi }}
                    </span>
                @endif
            </div>

            {{-- ═══ TABEL DATA ═══ --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50">
                                @foreach([
                                    'Tiket','Tanggal','Nama Pemohon','Seksi Tujuan',
                                    'Kanal','Status','Deadline','Keterangan'
                                ] as $h)
                                    <th class="text-left py-3 px-4 text-xs text-gray-500 font-semibold uppercase tracking-wide whitespace-nowrap">
                                        {{ $h }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pengaduans as $p)
                                @php
                                    $isOver  = $p->status !== 'selesai' && $p->deadline_tindak_lanjut < now();
                                    $isWarn  = !$isOver && $p->status !== 'selesai'
                                               && $p->deadline_tindak_lanjut <= now()->addDay();
                                    $rowBg   = $isOver ? 'bg-red-50/50' : ($isWarn ? 'bg-amber-50/50' : '');
                                @endphp
                                <tr class="border-b border-gray-50 hover:bg-gray-50/70 {{ $rowBg }}">
                                    <td class="py-3 px-4 text-xs text-gray-500 font-mono whitespace-nowrap">
                                        {{ $p->nomor_tiket }}
                                    </td>
                                    <td class="py-3 px-4 text-xs text-gray-600 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($p->tgl_pengaduan)->format('d/m/Y') }}
                                    </td>
                                    <td class="py-3 px-4 font-medium text-gray-800">{{ $p->nama }}</td>
                                    <td class="py-3 px-4 text-gray-600 text-xs">{{ $p->seksi_tujuan }}</td>
                                    <td class="py-3 px-4 text-gray-500 text-xs">{{ $p->kanal_pengaduan }}</td>
                                    <td class="py-3 px-4">
                                        <x-status-pill :status="$p->status" />
                                    </td>
                                    <td class="py-3 px-4 text-xs whitespace-nowrap {{ $isOver ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                                        {{ \Carbon\Carbon::parse($p->deadline_tindak_lanjut)->format('d/m/Y') }}
                                        @if ($isOver)
                                            <span class="ml-1">⚠️</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-gray-500 text-xs max-w-xs truncate">
                                        {{ Str::limit($p->keterangan_admin ?? '-', 50) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-12 text-center text-gray-400 text-sm">
                                        Tidak ada data untuk filter yang dipilih
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="px-4 py-3 border-t border-gray-100">
                    {{ $pengaduans->links() }}
                </div>
            </div>

        </div>
    </div>

    {{-- ═══ ALPINE.JS LOGIC ═══ --}}
    <script>
    function filterPanel() {
        return {
            periode:   '{{ request("periode", "monthly") }}',
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
                        this.dateError = 'Tanggal akhir tidak boleh sebelum tanggal awal.';
                    } else {
                        this.dateError = '';
                    }
                } else {
                    this.dateError = '';
                }
            },

            // Kumpulkan semua nilai filter dari form, lalu submit ke endpoint ekspor
            exportData(type) {
                if (this.periode === 'custom' && this.dateError) return;

                const filterForm = document.getElementById('filter-form');
                const exportForm = document.getElementById('export-form');

                // Clone semua input dari filter-form ke export-form
                exportForm.innerHTML = '';
                const formData = new FormData(filterForm);
                formData.forEach((value, key) => {
                    const input = document.createElement('input');
                    input.type  = 'hidden';
                    input.name  = key;
                    input.value = value;
                    exportForm.appendChild(input);
                });

                // Arahkan ke endpoint yang sesuai
                if (type === 'excel') {
                    exportForm.action = '{{ route("rekapitulasi.export.excel") }}';
                }

                exportForm.submit();
            },
        };
    }
    </script>
</x-layouts.dashboard>