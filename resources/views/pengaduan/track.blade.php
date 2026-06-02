{{-- resources/views/pengaduan/track.blade.php --}}
<x-layouts.dashboard>
    <x-slot name="header">Lacak Tiket</x-slot>

    @php
        // ✅ SINKRON: urutan status linear, ditolak memetakan ke step 1 (proses)
        $statusSteps = [
            'pending'    => 0,
            'proses'     => 1,
            'diteruskan' => 2,
            'selesai'    => 3,
            'ditolak'    => 1, // ✅ Cabang dari step proses, bukan step tersendiri
        ];
        $steps = [
            ['Menunggu Verifikasi',    'Aduan diterima, menunggu verifikasi petugas'],
            ['Sedang Diproses',        'Aduan sedang ditangani oleh seksi tujuan'],
            ['Disposisi Kasi',         'Sedang didisposisi oleh Kepala Seksi'],
            ['Selesai',                'Aduan telah selesai ditindaklanjuti'],
        ];
    @endphp

    <div class="p-6 max-w-[720px] space-y-5">

        {{-- ── SEARCH CARD ─────────────────────────────────────────── --}}
        <div class="bg-white rounded-[14px] border border-slate-100 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    Cari Nomor Tiket
                </p>
            </div>
            <div class="p-5">
                <form action="{{ route('pengaduan.searchTrack') }}" method="POST">
                    @csrf
                    <div class="flex gap-3">
                        <input type="text"
                               name="nomor_tiket"
                               placeholder="Contoh: IMI-20260417-001"
                               value="{{ old('nomor_tiket', isset($pengaduan) ? $pengaduan->nomor_tiket : '') }}"
                               class="flex-1 px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50
                                      focus:outline-none focus:border-blue-400 focus:bg-white focus:ring-2
                                      focus:ring-blue-100 transition font-mono uppercase tracking-wider
                                      placeholder:normal-case placeholder:tracking-normal
                                      placeholder:font-sans placeholder:text-slate-300"
                               required>
                        <button type="submit"
                                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold
                                       rounded-xl text-sm transition flex-shrink-0 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Cari
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if (isset($pengaduan))
            @php
                $isDitolak   = $pengaduan->status === 'ditolak';
                $currentStep = $statusSteps[$pengaduan->status] ?? 0;
                $slaStatus   = $pengaduan->sla_status ?? 'ok';
                $deadline    = \Carbon\Carbon::parse($pengaduan->deadline_tindak_lanjut);

                $statusColor = \App\Helpers\StatusHelper::badgeClass($pengaduan->status);

                $slaColor = match($slaStatus) {
                    'over'  => 'text-red-600 bg-red-50 border-red-200',
                    'warn'  => 'text-amber-600 bg-amber-50 border-amber-200',
                    default => 'text-emerald-600 bg-emerald-50 border-emerald-200',
                };
                $slaDot   = match($slaStatus) {
                    'over'  => 'bg-red-500',
                    'warn'  => 'bg-amber-500',
                    default => 'bg-emerald-500',
                };
                $slaLabel = match($slaStatus) {
                    'over'  => 'Melewati SLA',
                    'warn'  => 'H-1 Deadline',
                    default => 'On Track',
                };
            @endphp

            {{-- ── HEADER TIKET ──────────────────────────────────── --}}
            <div class="bg-white rounded-[14px] border border-slate-100 px-5 py-4
                        flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="font-mono text-base font-bold text-slate-800 tracking-wide">
                            {{ $pengaduan->nomor_tiket }}
                        </span>
                        <span class="badge {{ $statusColor }}">
                            {{ \App\Helpers\StatusHelper::label($pengaduan->status) }}
                        </span>
                        {{-- ✅ SLA badge disembunyikan jika sudah ditolak (tidak relevan) --}}
                        @unless($isDitolak)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full
                                         text-[11px] font-semibold border {{ $slaColor }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $slaDot }}
                                             {{ $slaStatus === 'over' ? 'animate-pulse' : '' }}"></span>
                                {{ $slaLabel }}
                            </span>
                        @endunless
                    </div>
                    <p class="text-xs text-slate-400 mt-1">
                        Diajukan {{ \Carbon\Carbon::parse($pengaduan->tgl_pengaduan)->translatedFormat('d F Y') }}
                        @unless($isDitolak)
                            · Deadline
                            <span class="{{ $slaStatus === 'over' ? 'text-red-500 font-semibold' : '' }}">
                                {{ $deadline->translatedFormat('d F Y') }}
                            </span>
                            <span class="text-slate-300">({{ $deadline->diffForHumans() }})</span>
                        @endunless
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="{{ route('pengaduan.show', $pengaduan->id) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold
                              border border-indigo-200 text-indigo-700 bg-indigo-50
                              hover:bg-indigo-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
                                     -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Lihat Detail
                    </a>
                    @if ($pengaduan->pdf_url)
                        <a href="{{ $pengaduan->pdf_url }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold
                                  border border-slate-200 text-slate-500 hover:bg-red-50
                                  hover:text-red-600 hover:border-red-200 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414
                                         A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            PDF
                        </a>
                    @endif
                </div>
            </div>

            {{-- ── BANNER DITOLAK ────────────────────────────────── --}}
            @if($isDitolak)
                <div class="bg-red-50 border border-red-200 rounded-[14px] p-5 flex gap-3 items-start">
                    <div class="w-8 h-8 rounded-xl bg-red-100 flex items-center
                                justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0
                                     015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-red-800 mb-0.5">Laporan Tidak Dapat Diproses</p>
                        <p class="text-xs text-red-600/80 leading-relaxed">
                            Aduan ini telah ditinjau dan dinyatakan tidak valid atau di luar kewenangan
                            kantor. Silakan hubungi petugas jika Anda merasa ini adalah kesalahan.
                        </p>
                    </div>
                </div>
            @endif

            {{-- ── INFO PEMOHON ──────────────────────────────────── --}}
            <div class="bg-white rounded-[14px] border border-slate-100 p-5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">
                    Informasi Pemohon
                </p>
                <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                    @foreach([
                        ['Nama Pemohon', $pengaduan->nama,            true],
                        ['Seksi Tujuan', $pengaduan->seksi_tujuan,    false],
                        ['No. WhatsApp', $pengaduan->whatsapp,        false],
                        ['Kanal',        $pengaduan->kanal_pengaduan, false],
                    ] as [$lbl, $val, $bold])
                        <div>
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">
                                {{ $lbl }}
                            </p>
                            <p class="text-sm {{ $bold ? 'font-semibold text-slate-800' : 'text-slate-600' }}">
                                {{ $val }}
                            </p>
                        </div>
                    @endforeach
                    <div class="col-span-2">
                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-1.5">
                            Isi Aduan
                        </p>
                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-3.5 text-sm
                                    text-slate-700 leading-relaxed whitespace-pre-wrap">
                            {{ $pengaduan->aduan }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── PROGRESS TIMELINE ─────────────────────────────── --}}
            <div class="bg-white rounded-[14px] border border-slate-100 p-5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-5">
                    Progress Penanganan
                </p>
                <div class="space-y-0">
                    @foreach ($steps as $i => $step)
                        @php
                            $isDone    = $i < $currentStep;
                            $isCurrent = $i === $currentStep;
                            $isPending = $i > $currentStep;
                        @endphp
                        <div class="flex gap-4 {{ !$loop->last ? 'pb-5' : '' }}">
                            <div class="flex flex-col items-center flex-shrink-0">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                                    {{ $isDone    ? 'bg-emerald-500' : '' }}
                                    {{ $isCurrent ? ($isDitolak ? 'bg-red-500 ring-4 ring-red-100' : 'bg-blue-600 ring-4 ring-blue-100') : '' }}
                                    {{ $isPending ? 'bg-slate-100' : '' }}">
                                    @if($isDone)
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @elseif($isCurrent && $isDitolak)
                                        {{-- ✅ Ikon X merah untuk ditolak --}}
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    @elseif($isCurrent)
                                        <span class="w-2.5 h-2.5 rounded-full bg-white"></span>
                                    @else
                                        <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                                    @endif
                                </div>
                                @if (!$loop->last)
                                    <div class="w-0.5 flex-1 mt-1
                                        {{ $isDone ? 'bg-emerald-300' : 'bg-slate-100' }}"></div>
                                @endif
                            </div>
                            <div class="flex-1 pt-1 {{ !$loop->last ? 'pb-1' : '' }}">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold
                                        {{ $isPending   ? 'text-slate-300' : '' }}
                                        {{ $isCurrent && $isDitolak ? 'text-red-700' : '' }}
                                        {{ ($isCurrent && !$isDitolak) || $isDone ? 'text-slate-800' : '' }}">
                                        {{-- ✅ Label khusus saat step aktif = ditolak --}}
                                        {{ ($isCurrent && $isDitolak) ? 'Laporan Ditolak' : $step[0] }}
                                    </span>
                                    @if ($isCurrent && !$isDitolak)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full
                                                     text-[10px] font-bold bg-blue-100 text-blue-700">
                                            Saat ini
                                        </span>
                                    @elseif($isCurrent && $isDitolak)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full
                                                     text-[10px] font-bold bg-red-100 text-red-700">
                                            Ditutup
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs mt-0.5
                                    {{ $isPending ? 'text-slate-300' : '' }}
                                    {{ $isCurrent && $isDitolak ? 'text-red-400' : '' }}
                                    {{ ($isCurrent && !$isDitolak) || $isDone ? 'text-slate-400' : '' }}">
                                    {{ ($isCurrent && $isDitolak)
                                        ? 'Aduan dinyatakan tidak valid oleh petugas.'
                                        : $step[1] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ── TANGGAPAN PETUGAS ─────────────────────────────── --}}
            <div class="bg-white rounded-[14px] border border-slate-100 p-5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">
                    Tanggapan Petugas
                </p>
                @if ($pengaduan->tindakLanjut)
                    @php $tl = $pengaduan->tindakLanjut; @endphp

                    {{-- ✅ Warna card menyesuaikan status ditolak --}}
                    <div class="{{ $isDitolak
                                    ? 'bg-red-50 border border-red-100'
                                    : 'bg-emerald-50 border border-emerald-100' }}
                                rounded-xl p-4 text-sm text-slate-700 leading-relaxed">
                        {{-- ✅ PERBAIKAN: catatan_petugas (bukan catatan) --}}
                        {{ $tl->catatan_petugas }}
                    </div>

                    {{-- ✅ PERBAIKAN: bukti_gambar dengan URL Supabase langsung --}}
                    @if($tl->bukti_gambar)
                        <div class="mt-3 p-2 bg-white rounded-xl border border-slate-100 inline-block">
                            <p class="text-[9px] font-black text-slate-400 uppercase mb-2 ml-1">
                                Lampiran Bukti:
                            </p>
                            <a href="{{ $tl->bukti_gambar }}" target="_blank">
                                <img src="{{ $tl->bukti_gambar }}"
                                     alt="Bukti tindak lanjut"
                                     class="h-28 w-auto rounded-lg object-cover
                                            hover:opacity-90 transition-opacity shadow-sm">
                            </a>
                        </div>
                    @endif

                    <p class="text-xs text-slate-400 mt-2">
                        Ditindaklanjuti
                        {{-- ✅ PERBAIKAN: updated_at dari $tl bukan $pengaduan->tindakLanjut->... --}}
                        {{ $tl->updated_at->translatedFormat('d F Y, H:i') }}
                        · Oleh {{ optional($tl->petugas)->name ?? 'Admin' }}
                    </p>
                @else
                    <div class="flex items-center gap-3 p-4 bg-slate-50
                                border border-dashed border-slate-200 rounded-xl">
                        <svg class="w-4 h-4 text-slate-300 flex-shrink-0" fill="none"
                             stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863
                                     9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574
                                     3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <p class="text-sm text-slate-400 italic">
                            Belum ada catatan tindak lanjut dari petugas.
                        </p>
                    </div>
                @endif
            </div>

        @elseif (request()->isMethod('post'))
            <div class="bg-white rounded-[14px] border border-red-200 p-8
                        flex flex-col items-center gap-3 text-center">
                <div class="w-12 h-12 rounded-2xl bg-red-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-700">Nomor Tiket Tidak Ditemukan</p>
                    <p class="text-xs text-slate-400 mt-1">
                        Pastikan format tiket benar, contoh:
                        <span class="font-mono text-slate-600">IMI-20260417-001</span>
                    </p>
                </div>
            </div>
        @endif

    </div>
</x-layouts.dashboard>