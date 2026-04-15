{{-- resources/views/dashboard/tikkim.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard TIKKIM
            </h2>
            <span class="text-sm text-gray-500">
                {{ now()->translatedFormat('l, d F Y') }}
            </span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- FLASH MESSAGE --}}
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- === KARTU STATISTIK GLOBAL === --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl p-5 border border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">Total Pengaduan</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $totalBulanIni }}</p>
                    <p class="text-xs text-gray-400 mt-1">bulan ini</p>
                </div>
                <div class="bg-white rounded-xl p-5 border border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">Selesai</p>
                    <p class="text-2xl font-semibold text-green-600">{{ $selesai }}</p>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ $totalBulanIni > 0 ? round($selesai / $totalBulanIni * 100) : 0 }}% dari total
                    </p>
                </div>
                <div class="bg-white rounded-xl p-5 border border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">Melebihi SLA</p>
                    <p class="text-2xl font-semibold text-red-600">{{ $slaOver }}</p>
                    <p class="text-xs text-gray-400 mt-1">melewati 3 hari</p>
                </div>
                <div class="bg-white rounded-xl p-5 border border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">H-1 Deadline</p>
                    <p class="text-2xl font-semibold text-amber-500">{{ $slaHMinus1 }}</p>
                    <p class="text-xs text-gray-400 mt-1">perlu tindak lanjut</p>
                </div>
            </div>

            {{-- === BARIS TENGAH: PERFORMA SEKSI + SEBARAN === --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">

                {{-- Performa Per Seksi --}}
                <div class="bg-white rounded-xl border border-gray-100 p-5">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">
                        Performa per Seksi
                    </h3>
                    <div class="space-y-4">
                        @foreach ($performaSeksi as $s)
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-medium text-gray-700 truncate">{{ $s['nama'] }}</span>
                                        <span class="text-xs text-gray-400 ml-2">{{ $s['total'] }} aduan</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                                        <div class="bg-blue-500 h-1.5 rounded-full transition-all"
                                             style="width: {{ $s['pct'] }}%"></div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 ml-4">
                                    <span class="text-sm font-semibold text-green-600">{{ $s['pct'] }}%</span>
                                    @if ($s['sla_over'] > 0)
                                        <span class="text-xs font-semibold text-red-500">
                                            {{ $s['sla_over'] }} over
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Sebaran Kanal & Status --}}
                <div class="bg-white rounded-xl border border-gray-100 p-5">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">
                        Sebaran Kanal Pengaduan
                    </h3>
                    @php $maxKanal = $kanalStats->max('jumlah') ?: 1; @endphp
                    <div class="space-y-2 mb-5">
                        @foreach ($kanalStats as $k)
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-gray-500 w-32 truncate">{{ $k->kanal_pengaduan }}</span>
                                <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                                    <div class="bg-blue-400 h-1.5 rounded-full"
                                         style="width: {{ round($k->jumlah / $maxKanal * 100) }}%"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-700 w-5 text-right">{{ $k->jumlah }}</span>
                            </div>
                        @endforeach
                    </div>

                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
                        Sebaran Status
                    </h3>
                    @php
                        $statusColors = [
                            'pending'     => 'bg-amber-400',
                            'proses'      => 'bg-blue-400',
                            'diteruskan'  => 'bg-purple-400',
                            'selesai'     => 'bg-green-400',
                        ];
                        $maxStatus = $statusStats->max('jumlah') ?: 1;
                    @endphp
                    <div class="space-y-2">
                        @foreach ($statusStats as $s)
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-gray-500 w-24 capitalize">{{ $s->status }}</span>
                                <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                                    <div class="{{ $statusColors[$s->status] ?? 'bg-gray-400' }} h-1.5 rounded-full"
                                         style="width: {{ round($s->jumlah / $maxStatus * 100) }}%"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-700 w-5 text-right">{{ $s->jumlah }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- === LAPORAN SLA === --}}
            <div class="bg-white rounded-xl border border-gray-100 p-5 mb-6">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">
                    Laporan SLA — Pengaduan Belum Selesai
                </h3>
                <div class="flex gap-4 text-xs text-gray-500 mb-3">
                    <span class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-green-400 inline-block"></span> On track
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span> H-1
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-red-400 inline-block"></span> Terlambat
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="text-left py-2 px-3 text-xs text-gray-400 font-semibold uppercase">Tiket</th>
                                <th class="text-left py-2 px-3 text-xs text-gray-400 font-semibold uppercase">Nama</th>
                                <th class="text-left py-2 px-3 text-xs text-gray-400 font-semibold uppercase">Seksi</th>
                                <th class="text-left py-2 px-3 text-xs text-gray-400 font-semibold uppercase">Status</th>
                                <th class="text-left py-2 px-3 text-xs text-gray-400 font-semibold uppercase">Deadline</th>
                                <th class="text-left py-2 px-3 text-xs text-gray-400 font-semibold uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($laporanSla as $p)
                                @php
                                    $dotColor = match($p->sla_status) {
                                        'over' => 'bg-red-400',
                                        'warn' => 'bg-amber-400',
                                        default => 'bg-green-400',
                                    };
                                @endphp
                                <tr class="border-b border-gray-50 hover:bg-gray-50">
                                    <td class="py-2 px-3 text-gray-500 text-xs">
                                        <span class="w-2 h-2 rounded-full {{ $dotColor }} inline-block mr-1"></span>
                                        {{ $p->nomor_tiket }}
                                    </td>
                                    <td class="py-2 px-3 font-medium text-gray-800">{{ $p->nama }}</td>
                                    <td class="py-2 px-3 text-gray-500">{{ $p->seksi_tujuan }}</td>
                                    <td class="py-2 px-3">
                                        <x-status-pill :status="$p->status" />
                                    </td>
                                    <td class="py-2 px-3 text-gray-500 text-xs">
                                        {{ \Carbon\Carbon::parse($p->deadline_tindak_lanjut)->format('d M Y') }}
                                    </td>
                                    <td class="py-2 px-3">
                                        <button
                                            onclick="openModal('{{ $p->id }}', '{{ $p->nomor_tiket }}', '{{ $p->nama }}', '{{ $p->status }}')"
                                            class="text-xs px-3 py-1 border border-gray-200 rounded-md hover:bg-gray-50 text-gray-600">
                                            Update
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-gray-400 text-sm">
                                        Semua pengaduan dalam batas SLA
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- === DAFTAR SEMUA PENGADUAN === --}}
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">
                    Semua Pengaduan
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="text-left py-2 px-3 text-xs text-gray-400 font-semibold uppercase">Tiket</th>
                                <th class="text-left py-2 px-3 text-xs text-gray-400 font-semibold uppercase">Nama / Seksi</th>
                                <th class="text-left py-2 px-3 text-xs text-gray-400 font-semibold uppercase">Kanal</th>
                                <th class="text-left py-2 px-3 text-xs text-gray-400 font-semibold uppercase">Status</th>
                                <th class="text-left py-2 px-3 text-xs text-gray-400 font-semibold uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pengaduans as $p)
                                <tr class="border-b border-gray-50 hover:bg-gray-50">
                                    <td class="py-2 px-3 text-gray-500 text-xs">{{ $p->nomor_tiket }}</td>
                                    <td class="py-2 px-3">
                                        <div class="font-medium text-gray-800">{{ $p->nama }}</div>
                                        <div class="text-xs text-gray-400">{{ $p->seksi_tujuan }}</div>
                                    </td>
                                    <td class="py-2 px-3 text-gray-500">{{ $p->kanal_pengaduan }}</td>
                                    <td class="py-2 px-3">
                                        <x-status-pill :status="$p->status" />
                                    </td>
                                    <td class="py-2 px-3">
                                        <button
                                            onclick="openModal('{{ $p->id }}', '{{ $p->nomor_tiket }}', '{{ $p->nama }}', '{{ $p->status }}')"
                                            class="text-xs px-3 py-1 border border-gray-200 rounded-md hover:bg-gray-50 text-gray-600">
                                            Update
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400 text-sm">Belum ada pengaduan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $pengaduans->links() }}</div>
            </div>

        </div>
    </div>

    {{-- === MODAL UPDATE STATUS === --}}
    <div id="modal-overlay" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl border border-gray-100 p-6 w-80 max-w-full mx-4">
            <h3 class="font-semibold text-gray-800 mb-1" id="modal-tiket">Update Status</h3>
            <p class="text-xs text-gray-400 mb-4" id="modal-nama">—</p>

            <form method="POST" id="modal-form" action="">
                @csrf
                <div class="mb-3">
                    <label class="text-xs text-gray-500 mb-1 block">Status Baru</label>
                    <select name="status" id="modal-status-select"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-300">
                        <option value="pending">Pending</option>
                        <option value="proses">Proses</option>
                        <option value="diteruskan">Diteruskan ke Atasan</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="text-xs text-gray-500 mb-1 block">Keterangan</label>
                    <textarea name="keterangan" rows="3"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-300"
                        placeholder="Catatan tindak lanjut..."></textarea>
                </div>
                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="closeModal()"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-gray-900 text-white rounded-lg hover:bg-gray-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id, tiket, nama, status) {
            document.getElementById('modal-tiket').textContent = 'Update — ' + tiket;
            document.getElementById('modal-nama').textContent = nama;
            document.getElementById('modal-status-select').value = status;
            document.getElementById('modal-form').action = '/dashboard/pengaduan/' + id + '/status';
            document.getElementById('modal-overlay').classList.remove('hidden');
        }
        function closeModal() {
            document.getElementById('modal-overlay').classList.add('hidden');
        }
        document.getElementById('modal-overlay').addEventListener('click', function (e) {
            if (e.target === this) closeModal();
        });
    </script>
</x-app-layout>