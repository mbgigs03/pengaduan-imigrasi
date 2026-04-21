<x-layouts.dashboard>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Monitoring Pengaduan - Kantor Imigrasi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- ═══════════════════════════════════════════
                 STATISTIK RINGKAS
            ═══════════════════════════════════════════ --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-6 rounded-[14px] shadow-sm border-l-4 border-blue-500">
                    <div class="text-sm font-medium text-gray-500 uppercase">Total Aduan</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $pengaduans->total() }}</div>
                </div>
                {{-- Anda bisa menambah card statistik lain di sini --}}
            </div>

            {{-- ═══════════════════════════════════════════
                 TABEL UTAMA & FILTER
            ═══════════════════════════════════════════ --}}
            <div class="bg-white rounded-[14px] border border-slate-100 shadow-sm overflow-hidden">
                
                {{-- Header Tabel --}}
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Semua Pengaduan</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Seluruh data pengaduan bulan ini</p>
                    </div>
                </div>

                {{-- Form Filter --}}
                <form method="GET" action="{{ route('dashboard') }}"
                      class="flex flex-wrap items-center gap-2.5 px-5 py-3 bg-slate-50/70 border-b border-slate-100">
                    <input type="text" name="keyword_all" value="{{ request('keyword_all') }}"
                        placeholder="Cari nama / nomor tiket…"
                        class="text-sm border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 bg-white w-52 placeholder:text-slate-400">

                    @foreach([
                        ['status_all', '', 'Semua Status', ['pending'=>'Pending','proses'=>'Proses','diteruskan'=>'Diteruskan','selesai'=>'Selesai']],
                        ['kanal_all',  '', 'Semua Kanal',  array_combine($kanalList, $kanalList)],
                        ['sla_all',    '', 'Semua SLA',    ['ok'=>'On Track','warn'=>'H-1','over'=>'Terlambat']],
                    ] as [$name, $default, $placeholder, $opts])
                        <select name="{{ $name }}"
                                class="text-sm border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 bg-white text-slate-600">
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

                {{-- Table Body --}}
                <div class="overflow-x-auto">
                    <table class="w-full data-table">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Tiket</th>
                                <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Nama / Seksi</th>
                                <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Kanal</th>
                                <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Deadline</th>
                                <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            @forelse ($pengaduans as $p)
                                @php $tlId = optional($p->tindakLanjut)->id; @endphp
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-5 py-4 font-mono text-xs text-slate-400">{{ $p->nomor_tiket }}</td>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-slate-800 text-sm">{{ $p->nama }}</div>
                                        <span class="text-[10px] font-medium text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded-md">
                                            {{ $p->seksi_tujuan }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-xs text-slate-500">{{ $p->kanal_pengaduan }}</td>
                                    <td class="px-5 py-4">
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $p->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                            {{ strtoupper($p->status) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-xs {{ \Carbon\Carbon::now()->gt($p->deadline_tindak_lanjut) ? 'text-red-600 font-bold' : 'text-slate-500' }}">
                                        {{ \Carbon\Carbon::parse($p->deadline_tindak_lanjut)->format('d M Y') }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-1.5">
                                            <a href="{{ route('pengaduan.show', $p->id) }}" class="text-xs px-2.5 py-1.5 rounded-lg font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition whitespace-nowrap">
                                                Detail
                                            </a>
                                            <button onclick="openModalTL('{{ $p->id }}','{{ $p->nomor_tiket }}','{{ addslashes($p->nama) }}','{{ $p->status }}','{{ addslashes($p->keterangan_admin ?? '') }}','{{ $tlId }}')"
                                                class="text-xs px-2.5 py-1.5 rounded-lg font-bold transition whitespace-nowrap
                                                {{ $tlId ? 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100' : 'bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100' }}">
                                                {{ $tlId ? 'Edit TL' : 'TL' }}
                                            </button>
                                            
                                            {{-- Download Icons --}}
                                            <div class="flex gap-1 ml-1">
                                                <a href="{{ route('dashboard.pengaduan.downloadPdf', $p->nomor_tiket) }}" title="PDF" class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                                </a>
                                                <a href="{{ route('dashboard.pengaduan.downloadDocx', $p->nomor_tiket) }}" title="DOCX" class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-14 text-center text-slate-400 text-sm">
                                        Belum ada pengaduan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="px-5 py-4 border-t border-slate-100">
                    {{ $pengaduans->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL TINDAK LANJUT --}}
    <x-modal-tindak-lanjut />
</x-layouts.dashboard>