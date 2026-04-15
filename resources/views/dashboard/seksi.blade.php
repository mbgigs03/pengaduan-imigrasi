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
                                    // ID tindak lanjut jika sudah ada, null jika belum
                                    $tlId = optional($p->tindakLanjut)->id;
                                @endphp
                                <tr class="border-b border-gray-50 hover:bg-gray-50">
                                    <td class="py-2 px-3 text-xs text-gray-500">
                                        <span class="w-2 h-2 rounded-full {{ $dotColor }} inline-block mr-1"></span>
                                        {{ $p->nomor_tiket }}
                                    </td>
                                    <td class="py-2 px-3 font-medium text-gray-800">{{ $p->nama }}</td>
                                    <td class="py-2 px-3 text-gray-500 max-w-xs truncate">
                                        {{ Str::limit($p->aduan, 50) }}
                                    </td>
                                    <td class="py-2 px-3 text-gray-500 text-xs">{{ $p->kanal_pengaduan }}</td>
                                    <td class="py-2 px-3">
                                        <x-status-pill :status="$p->status" />
                                    </td>
                                    <td class="py-2 px-3 text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($p->deadline_tindak_lanjut)->format('d M Y') }}
                                    </td>
                                    <td class="py-2 px-3">
                                        {{--
                                            openModalTL(pengaduanId, tiket, nama, statusSaat, catatanLama, tindakLanjutId)
                                            - Jika sudah ada TL: mode UPDATE (form PUT)
                                            - Jika belum ada TL: mode STORE (form POST)
                                        --}}
                                        <button onclick="openModalTL(
                                                '{{ $p->id }}',
                                                '{{ $p->nomor_tiket }}',
                                                '{{ addslashes($p->nama) }}',
                                                '{{ $p->status }}',
                                                '{{ addslashes($p->keterangan_admin ?? '') }}',
                                                '{{ $tlId }}'
                                            )"
                                            class="text-xs px-3 py-1.5 rounded-lg font-medium transition
                                                {{ $tlId
                                                    ? 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100'
                                                    : 'bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100'
                                                }}">
                                            {{ $tlId ? 'Edit TL' : 'Tindak Lanjut' }}
                                        </button>
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