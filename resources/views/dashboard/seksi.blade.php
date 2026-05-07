{{-- resources/views/dashboard/seksi.blade.php --}}
@php use App\Helpers\StatusHelper; @endphp
<x-layouts.dashboard>
    <x-slot name="header">Dashboard — Seksi {{ $seksi }}</x-slot>

    <div class="p-6 space-y-6 max-w-[1200px]">

        {{-- STAT CARDS --}}
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach([
                ['Pengaduan Masuk', $totalMasuk, 'bidang '.$seksi,        'text-slate-800',  'bg-blue-50',  '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>', 'text-blue-600'],
                ['Selesai',         $selesai,    ($totalMasuk > 0 ? round($selesai/$totalMasuk*100) : 0).'% tuntas', 'text-emerald-600','bg-emerald-50','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>','text-emerald-600'],
                ['Melebihi SLA',    $slaOver,    'segera ditangani',       $slaOver>0?'text-red-600':'text-slate-800',   'bg-red-50',   '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',   $slaOver>0?'text-red-600':'text-slate-400'],
                ['Menunggu Aksi',   $menunggu,   'pending / proses',       $menunggu>0?'text-amber-600':'text-slate-800', 'bg-amber-50', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',          $menunggu>0?'text-amber-600':'text-slate-400'],
            ] as [$label, $val, $sub, $valColor, $iconBg, $iconPath, $iconColor])
                <div class="stat-card flex items-start gap-4">
                    <div class="w-11 h-11 rounded-xl {{ $iconBg }} flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $iconPath !!}</svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">{{ $label }}</p>
                        <p class="text-2xl font-bold {{ $valColor }} mt-0.5">{{ number_format($val) }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $sub }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- TABEL PENGADUAN --}}
        <div class="bg-white rounded-[14px] border border-slate-100 overflow-hidden">

            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Daftar Pengaduan — Seksi {{ $seksi }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Hanya menampilkan pengaduan untuk seksi Anda</p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    @foreach([['bg-emerald-500','On track / Selesai'],['bg-amber-500','H-1'],['bg-red-500','Terlambat']] as [$dot,$lbl])
                        <span class="flex items-center gap-1.5 text-slate-500">
                            <span class="w-2 h-2 rounded-full {{ $dot }} flex-shrink-0"></span>{{ $lbl }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full data-table">
                    <thead>
                        <tr>
                            @foreach(['Tiket', 'Nama', 'Aduan', 'Kanal', 'Status', 'Deadline', 'Aksi'] as $h)
                                <th class="text-left p-3 text-xs font-bold text-slate-500 uppercase tracking-wide">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pengaduans as $p)
                            @php
                                $isSelesai = $p->status === 'selesai';
                                $tlId = optional($p->tindakLanjut)->id;
                                
                                // Jika selesai tapi nggak ada riwayat Tindak Lanjut = Itu otomatis ditutup dari FAQ Publik
                                $isFaq = $isSelesai && !$tlId;

                                // Titik warna indikator
                                $dot = $isSelesai ? 'bg-emerald-500' : match($p->sla_status ?? 'ok') {
                                    'over'  => 'bg-red-500 dot-over',
                                    'warn'  => 'bg-amber-500',
                                    default => 'bg-emerald-500',
                                };
                            @endphp
                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                <td class="p-3">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $dot }}"></span>
                                        <span class="font-mono text-xs text-slate-500">{{ $p->nomor_tiket }}</span>
                                    </div>
                                </td>
                                <td class="p-3 font-semibold text-slate-800 text-sm">{{ $p->nama }}</td>
                                <td class="p-3">
                                    <a href="{{ route('pengaduan.show', $p->id) }}"
                                       class="text-xs px-2.5 py-1.5 rounded-lg font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition whitespace-nowrap">
                                        Lihat Detail
                                    </a>
                                </td>
                                <td class="p-3 text-xs text-slate-500">{{ $p->kanal_pengaduan }}</td>
                                <td class="p-3">
                                    <span class="badge {{ StatusHelper::badgeClass($p->status) }}">{{ StatusHelper::label($p->status) }}</span>
                                </td>
                                
                                {{-- 🟢 PERBAIKAN LOGIKA DEADLINE --}}
                                <td class="p-3">
                                    @if($isSelesai)
                                        <div class="text-xs font-bold text-emerald-600">Tuntas</div>
                                        <div class="text-[10px] text-slate-400">{{ $isFaq ? 'Diselesaikan sistem (FAQ)' : 'Telah ditindaklanjuti' }}</div>
                                    @else
                                        <div class="text-xs {{ ($p->sla_status ?? '') === 'over' ? 'text-red-600 font-bold' : 'text-slate-500' }}">
                                            {{ \Carbon\Carbon::parse($p->deadline_tindak_lanjut)->format('d M Y') }}
                                        </div>
                                        <div class="text-[10px] text-slate-400">
                                            {{ \Carbon\Carbon::parse($p->deadline_tindak_lanjut)->diffForHumans() }}
                                        </div>
                                    @endif
                                </td>
                                
                                {{-- 🟢 PERBAIKAN LOGIKA AKSI --}}
                                <td>
                                    <div class="flex items-center gap-1.5">
                                        {{-- LOGIKA BARU: Jika selesai, tombol dimatikan --}}
                                        @if($p->status === 'selesai')
                                            <span class="text-[11px] px-3 py-1.5 rounded-lg font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 cursor-not-allowed whitespace-nowrap">
                                                <svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                Tuntas
                                            </span>
                                        @else
                                            <button onclick="openModalTL('{{ $p->id }}','{{ $p->nomor_tiket }}','{{ addslashes($p->nama) }}','{{ $p->status }}','{{ addslashes($p->keterangan_admin ?? '') }}','{{ $tlId }}')"
                                                class="text-xs px-2.5 py-1.5 rounded-lg font-bold transition whitespace-nowrap
                                                {{ $tlId ? 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100' : 'bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100' }}">
                                                {{ $tlId ? 'Edit TL' : 'Tindak Lanjut' }}
                                            </button>
                                        @endif
                                        
                                        <a href="{{ route('dashboard.pengaduan.downloadPdf', $p->nomor_tiket) }}"
                                           title="Unduh PDF" target="_blank"
                                           class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200
                                                  text-slate-400 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-14 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/></svg>
                                        </div>
                                        <p class="text-sm font-semibold text-slate-500">Belum ada pengaduan</p>
                                        <p class="text-xs text-slate-400">Belum ada pengaduan untuk seksi {{ $seksi }}</p>
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