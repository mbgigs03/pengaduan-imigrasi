{{-- resources/views/dashboard/seksi.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard — Seksi {{ $seksi }}
            </h2>
            <span class="text-sm text-gray-500">
                {{ now()->translatedFormat('l, d F Y') }}
            </span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- FLASH MESSAGE --}}
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            {{-- === KARTU STATISTIK SEKSI === --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl p-5 border border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">Pengaduan Masuk</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $totalMasuk }}</p>
                    <p class="text-xs text-gray-400 mt-1">bidang {{ $seksi }}</p>
                </div>
                <div class="bg-white rounded-xl p-5 border border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">Selesai</p>
                    <p class="text-2xl font-semibold text-green-600">{{ $selesai }}</p>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ $totalMasuk > 0 ? round($selesai / $totalMasuk * 100) : 0 }}%
                    </p>
                </div>
                <div class="bg-white rounded-xl p-5 border border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">Melebihi SLA</p>
                    <p class="text-2xl font-semibold {{ $slaOver > 0 ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $slaOver }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">segera ditangani</p>
                </div>
                <div class="bg-white rounded-xl p-5 border border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">Menunggu Aksi</p>
                    <p class="text-2xl font-semibold {{ $menunggu > 0 ? 'text-amber-500' : 'text-gray-900' }}">
                        {{ $menunggu }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">pending / proses</p>
                </div>
            </div>

            {{-- === DAFTAR PENGADUAN SEKSI === --}}
            <div class="bg-white rounded-xl border border-gray-100 p-5">

                {{-- FILTER & SEARCH (dari file 2) --}}
                <form method="GET" class="mb-4 flex flex-wrap gap-3 items-center" action="{{ route('dashboard') }}">
                    <!-- SEARCH -->
                    <input type="text" name="keyword" placeholder="Cari nama/nomor tiket..."
                        value="{{ request('keyword') }}"
                        class="border rounded-lg px-3 py-2 text-sm">

                    <!-- STATUS -->
                    <select name="status" class="border rounded-lg pl-3 px-10 py-2 text-sm">
                        <option value="">Semua Status</option>
                        <option value="pending"     {{ request('status') == 'pending'     ? 'selected' : '' }}>Pending</option>
                        <option value="proses"      {{ request('status') == 'proses'      ? 'selected' : '' }}>Proses</option>
                        <option value="diteruskan"  {{ request('status') == 'diteruskan'  ? 'selected' : '' }}>Diteruskan</option>
                        <option value="selesai"     {{ request('status') == 'selesai'     ? 'selected' : '' }}>Selesai</option>
                    </select>

                    <!-- KANAL -->
                    <select name="kanal" class="border rounded-lg px-3 py-2 text-sm">
                        <option value="">Semua Kanal</option>
                        @foreach($kanalList as $kanal)
                            <option value="{{ $kanal }}" {{ request('kanal') == $kanal ? 'selected' : '' }}>
                                {{ $kanal }}
                            </option>
                        @endforeach
                    </select>

                    <!-- SLA -->
                    <select name="sla" class="border rounded-lg pl-3 px-10 py-2 text-sm">
                        <option value="">Semua SLA</option>
                        <option value="ok"   {{ request('sla') == 'ok'   ? 'selected' : '' }}>On Track</option>
                        <option value="warn" {{ request('sla') == 'warn' ? 'selected' : '' }}>H-1</option>
                        <option value="over" {{ request('sla') == 'over' ? 'selected' : '' }}>Terlambat</option>
                    </select>

                    <!-- SUBMIT -->
                    <button type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                        Filter
                    </button>

                    <!-- RESET -->
                    <a href="{{ route('dashboard') }}"
                        class="text-sm text-gray-500 underline hover:text-gray-700">
                        Reset
                    </a>
                </form>

                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        Daftar Pengaduan — Seksi {{ $seksi }}
                    </h3>
                    <div class="flex gap-3 text-xs text-gray-400">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-green-400 inline-block"></span> On track
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span> H-1
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-red-400 inline-block"></span> Terlambat
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="text-left py-2 px-3 text-xs text-gray-400 font-semibold uppercase">Tiket</th>
                                <th class="text-left py-2 px-3 text-xs text-gray-400 font-semibold uppercase">Nama</th>
                                <th class="text-left py-2 px-3 text-xs text-gray-400 font-semibold uppercase">Aduan</th>
                                <th class="text-left py-2 px-3 text-xs text-gray-400 font-semibold uppercase">Kanal</th>
                                <th class="text-left py-2 px-3 text-xs text-gray-400 font-semibold uppercase">Status</th>
                                <th class="text-left py-2 px-3 text-xs text-gray-400 font-semibold uppercase">Deadline</th>
                                <th class="text-left py-2 px-3 text-xs text-gray-400 font-semibold uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pengaduans as $p)

                                @php
                                    $dotColor = match($p->sla_status) {
                                        'over'  => 'bg-red-400',
                                        'warn'  => 'bg-amber-400',
                                        default => 'bg-green-400',
                                    };

                                    $rowBg = match($p->sla_status) {
                                        'over'  => 'bg-red-50/40',
                                        'warn'  => 'bg-amber-50/40',
                                        default => '',
                                    };

                                    $tlId = optional($p->tindakLanjut)->id;
                                @endphp

                                <tr class="border-b border-gray-50 hover:bg-gray-50 {{ $rowBg }}">

                                    {{-- TIKET + DOT --}}
                                    <td class="py-2 px-3">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full {{ $dotColor }}
                                                {{ $p->sla_status === 'over' ? 'animate-pulse' : '' }}">
                                            </span>
                                            <span class="text-xs font-mono text-gray-600">
                                                {{ $p->nomor_tiket }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- NAMA --}}
                                    <td class="py-2 px-3 font-medium text-gray-800">
                                        {{ $p->nama }}
                                    </td>

                                    {{-- ADUAN --}}
                                    <td class="py-2 px-3">
                                        <a href="{{ route('pengaduan.show', $p->id) }}"
                                            class="text-xs px-3 py-1.5 rounded-lg font-medium
                                                    bg-indigo-50 text-indigo-700 border border-indigo-200
                                                    hover:bg-indigo-100 transition">
                                            Detail
                                        </a>
                                    </td>

                                    {{-- KANAL --}}
                                    <td class="py-2 px-3 text-gray-500 text-xs">
                                        {{ $p->kanal_pengaduan }}
                                    </td>

                                    {{-- STATUS --}}
                                    <td class="py-2 px-3">
                                        <x-status-pill :status="$p->status" />
                                    </td>

                                    {{-- DEADLINE --}}
                                    <td class="py-2 px-3 text-xs">
                                        <div class="{{ $p->sla_status === 'over' ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                                            {{ \Carbon\Carbon::parse($p->deadline_tindak_lanjut)->format('d M Y') }}
                                        </div>
                                        <div class="text-[10px] text-gray-400">
                                            {{ \Carbon\Carbon::parse($p->deadline_tindak_lanjut)->diffForHumans() }}
                                        </div>
                                    </td>

                                    {{-- AKSI (dari file 1: tombol TL + dropdown download) --}}
                                    <td class="py-3 px-3">
                                        <div class="flex items-center gap-1.5" x-data="{ open: false }">

                                            {{-- Tombol Tindak Lanjut --}}
                                            <button
                                                onclick="openModalTL(
                                                    '{{ $p->id }}',
                                                    '{{ $p->nomor_tiket }}',
                                                    '{{ addslashes($p->nama) }}',
                                                    '{{ $p->status }}',
                                                    '{{ addslashes($p->keterangan_admin ?? '') }}',
                                                    '{{ $tlId }}'
                                                )"
                                                class="text-xs px-3 py-1.5 rounded-lg font-bold transition shadow-sm whitespace-nowrap
                                                    {{ $tlId
                                                        ? 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100'
                                                        : 'bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100' }}">
                                                {{ $tlId ? 'Edit TL' : 'Tindak Lanjut' }}
                                            </button>

                                            {{-- Dropdown Download --}}
                                            <div class="relative" x-data="{ open: false }">

                                                {{-- Trigger --}}
                                                <button
                                                    @click="open = !open"
                                                    @keydown.escape="open = false"
                                                    class="inline-flex items-center gap-1 text-xs px-2.5 py-1.5
                                                        border border-gray-200 rounded-lg text-gray-500
                                                        hover:bg-gray-50 hover:text-gray-700 transition"
                                                    title="Unduh dokumen">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1
                                                               m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                    </svg>
                                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </button>

                                                {{-- Menu Dropdown --}}
                                                <div
                                                    x-show="open"
                                                    @click.outside="open = false"
                                                    x-transition:enter="transition ease-out duration-100"
                                                    x-transition:enter-start="opacity-0 scale-95"
                                                    x-transition:enter-end="opacity-100 scale-100"
                                                    x-transition:leave="transition ease-in duration-75"
                                                    x-transition:leave-start="opacity-100 scale-100"
                                                    x-transition:leave-end="opacity-0 scale-95"
                                                    class="absolute right-0 z-30 mt-1 w-44 bg-white rounded-xl
                                                        border border-gray-100 shadow-lg py-1"
                                                    style="display:none">

                                                    {{-- Download PDF --}}
                                                    <a href="{{ route('dashboard.pengaduan.downloadPdf', $p->nomor_tiket) }}"
                                                        class="flex items-center gap-2.5 px-4 py-2.5 text-xs text-gray-700
                                                                hover:bg-red-50 hover:text-red-700 transition group">
                                                        <span class="w-6 h-6 rounded-md bg-red-50 group-hover:bg-red-100
                                                                    flex items-center justify-center flex-shrink-0 transition">
                                                            <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707
                                                                       l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                            </svg>
                                                        </span>
                                                        <div>
                                                            <div class="font-semibold">Unduh PDF</div>
                                                            @if ($p->pdf_url)
                                                                <div class="text-[10px] text-green-500">Sudah tersedia</div>
                                                            @else
                                                                <div class="text-[10px] text-gray-400">Generate otomatis</div>
                                                            @endif
                                                        </div>
                                                    </a>

                                                    {{-- Download DOCX --}}
                                                    <a href="{{ route('dashboard.pengaduan.downloadDocx', $p->nomor_tiket) }}"
                                                        class="flex items-center gap-2.5 px-4 py-2.5 text-xs text-gray-700
                                                                hover:bg-blue-50 hover:text-blue-700 transition group">
                                                        <span class="w-6 h-6 rounded-md bg-blue-50 group-hover:bg-blue-100
                                                                    flex items-center justify-center flex-shrink-0 transition">
                                                            <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                                                                       a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                            </svg>
                                                        </span>
                                                        <div>
                                                            <div class="font-semibold">Unduh DOCX</div>
                                                            <div class="text-[10px] text-gray-400">Dokumen arsip</div>
                                                        </div>
                                                    </a>

                                                    <div class="border-t border-gray-100 my-1"></div>

                                                    {{-- Preview di tab baru --}}
                                                    @if ($p->pdf_url)
                                                        <a href="{{ $p->pdf_url }}" target="_blank"
                                                            class="flex items-center gap-2.5 px-4 py-2.5 text-xs text-gray-500
                                                                    hover:bg-gray-50 transition">
                                                            <span class="w-6 h-6 rounded-md bg-gray-50 flex items-center justify-center flex-shrink-0">
                                                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4
                                                                           M14 4h6m0 0v6m0-6L10 14"/>
                                                                </svg>
                                                            </span>
                                                            Buka di Tab Baru
                                                        </a>
                                                    @endif

                                                </div>
                                            </div>
                                            {{-- End Dropdown --}}

                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-400 text-sm">
                                        Belum ada pengaduan untuk seksi ini
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $pengaduans->links() }}</div>
            </div>

        </div>
    </div>

    {{-- MODAL TINDAK LANJUT (component) --}}
    <x-modal-tindak-lanjut />

</x-app-layout>