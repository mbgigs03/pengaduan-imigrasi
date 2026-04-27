{{-- resources/views/pengaduan/index.blade.php --}}
<x-layouts.dashboard>
    <x-slot name="header">Monitoring Pengaduan</x-slot>

    <div class="p-6 space-y-5 max-w-[1200px]">

        {{-- ── STAT CARD ────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="stat-card flex items-start gap-4">
                <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2
                                 M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Aduan</p>
                    <p class="text-2xl font-bold text-slate-800 mt-0.5">{{ $pengaduans->total() }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">semua data</p>
                </div>
            </div>
        </div>

        {{-- ── TABEL + FILTER ───────────────────────────────────── --}}
        <div class="bg-white rounded-[14px] border border-slate-100 overflow-hidden">

            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Semua Pengaduan</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Seluruh data pengaduan yang masuk ke sistem</p>
                </div>
            </div>

            {{-- Filter --}}
            <form method="GET" action="{{ route('pengaduan.index') }}"
                  class="flex flex-wrap items-center gap-2.5 px-5 py-3 bg-slate-50/70 border-b border-slate-100">

                <input type="text" name="keyword" value="{{ request('keyword') }}"
                       placeholder="Cari nama / nomor tiket…"
                       class="text-sm border border-slate-200 rounded-xl px-3 py-2 bg-white w-52
                              focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400
                              placeholder:text-slate-400">

                <select name="status"
                        class="text-sm border border-slate-200 rounded-xl px-3 py-2 bg-white text-slate-600
                               focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400">
                    <option value="">Semua Status</option>
                    @foreach (\App\Helpers\StatusHelper::options() as $val => $lbl)
                        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>
                            {{ $lbl }}
                        </option>
                    @endforeach
                </select>

                <select name="seksi"
                        class="text-sm border border-slate-200 rounded-xl px-3 py-2 bg-white text-slate-600
                               focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400">
                    <option value="">Semua Seksi</option>
                    @foreach(['Tikkim','Doklanintalkim','Inteldakim','Tata Usaha'] as $s)
                        <option value="{{ $s }}" {{ request('seksi') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>

                <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
                    Filter
                </button>
                <a href="{{ route('pengaduan.index') }}"
                   class="text-sm text-slate-400 hover:text-slate-600 transition">Reset</a>
            </form>

            {{-- Table --}}
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
                        @forelse ($pengaduans as $p)
                            @php
                                $tlId = optional($p->tindakLanjut)->id;
                                $over = \Carbon\Carbon::now()->gt($p->deadline_tindak_lanjut);
                            @endphp
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
                                    <span class="badge {{ \App\Helpers\StatusHelper::badgeClass($p->status) }}">
                                        {{ \App\Helpers\StatusHelper::label($p->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="text-xs {{ $over ? 'text-red-600 font-bold' : 'text-slate-500' }}">
                                        {{ \Carbon\Carbon::parse($p->deadline_tindak_lanjut)->format('d M Y') }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        {{ \Carbon\Carbon::parse($p->deadline_tindak_lanjut)->diffForHumans() }}
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ route('pengaduan.show', $p->id) }}"
                                           class="text-xs px-2.5 py-1.5 rounded-lg font-semibold
                                                  bg-indigo-50 text-indigo-700 border border-indigo-200
                                                  hover:bg-indigo-100 transition whitespace-nowrap">
                                            Detail
                                        </a>
                                        <button onclick="openModalTL('{{ $p->id }}','{{ $p->nomor_tiket }}','{{ addslashes($p->nama) }}','{{ $p->status }}','{{ addslashes($p->keterangan_admin ?? '') }}','{{ $tlId }}')"
                                            class="text-xs px-2.5 py-1.5 rounded-lg font-bold transition whitespace-nowrap
                                            {{ $tlId
                                                ? 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100'
                                                : 'bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100' }}">
                                            {{ $tlId ? 'Edit TL' : 'TL' }}
                                        </button>
                                        <a href="{{ route('dashboard.pengaduan.downloadPdf', $p->nomor_tiket) }}"
                                           title="PDF"
                                           class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200
                                                  text-slate-400 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition">
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
                                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
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

            <div class="px-5 py-3 border-t border-slate-100">
                {{ $pengaduans->links() }}
            </div>
        </div>
    </div>

    <x-modal-tindak-lanjut />

</x-layouts.dashboard>