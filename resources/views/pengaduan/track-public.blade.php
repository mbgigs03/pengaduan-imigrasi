{{-- resources/views/pengaduan/track-public.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cek Status Aduan — Imigrasi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        * { font-family: 'Plus Jakarta Sans', sans-serif; }

        .input-field {
            flex: 1;
            padding: 12px 16px 12px 44px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            outline: none;
            background: #f8fafc;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: .05em;
            transition: all .3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .input-field:focus {
            border-color: #2563eb;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(37,99,235,.1);
        }
        .timeline-item { position: relative; padding-left: 32px; }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 6.5px; top: 24px;
            width: 2px;
            height: calc(100% - 12px);
            background: #e2e8f0;
        }
        .timeline-item:last-child::before { display: none; }
        .timeline-dot {
            position: absolute;
            left: 0; top: 4px;
            width: 16px; height: 16px;
            border-radius: 50%;
            border: 3px solid #e2e8f0;
            background: #fff;
            z-index: 10;
            transition: all 0.3s;
        }
        .timeline-dot.active {
            border-color: #dbeafe;
            background: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.2);
            animation: pulse 2s infinite;
        }
        .timeline-dot.active-red {
            border-color: #fee2e2;
            background: #ef4444;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.2);
        }
        .timeline-dot.done {
            border-color: #10b981;
            background: #10b981;
        }
        @keyframes pulse {
            0%   { transform: scale(1); }
            50%  { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        .btn-loading {
            position: relative;
            color: transparent !important;
            pointer-events: none;
        }
        .btn-loading::after {
            content: "";
            position: absolute;
            width: 20px; height: 20px;
            top: 0; left: 0; right: 0; bottom: 0;
            margin: auto;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .result-card { animation: fadeUp .5s ease-out both; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen py-10 px-4">

    <div class="max-w-2xl mx-auto">

        <a href="{{ route('pengaduan.landing') }}"
            class="inline-flex items-center gap-2 text-slate-500 hover:text-blue-700
                   font-semibold text-sm mb-6 transition-colors group">
            <svg class="w-5 h-5 transition-transform group-hover:-translate-x-1"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                      d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Beranda
        </a>

        {{-- ── SEARCH CARD ──────────────────────────────────────────── --}}
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/60 border border-slate-100 overflow-hidden mb-6">
            <div class="bg-gradient-to-br from-blue-900 via-blue-800 to-blue-700 px-8 py-8 text-white relative">
                <div class="relative z-10">
                    <h1 class="text-2xl font-extrabold mb-1 tracking-tight">Lacak Aduan Anda</h1>
                    <p class="text-blue-100/80 text-sm">Pantau sejauh mana laporan Anda ditindaklanjuti.</p>
                </div>
                <svg class="absolute right-6 top-6 w-20 h-20 text-white/10"
                     fill="currentColor" viewBox="0 0 24 24">
                    <path d="M10 20l4-4m0 0l-4-4m4 4H3m18-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h3"/>
                </svg>
            </div>
            <div class="p-8">
                <form action="{{ route('pengaduan.searchTrack') }}" method="POST" id="trackForm">
                    @csrf
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="relative flex-1">
                            <div class="absolute left-4 top-3.5 text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1
                                             0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                            </div>
                            <input type="text" name="nomor_tiket" id="nomor_tiket"
                                placeholder="CONTOH: IMI-20260414-001"
                                class="input-field w-full"
                                value="{{ old('nomor_tiket', isset($pengaduan) ? $pengaduan->nomor_tiket : '') }}"
                                required>
                        </div>
                        <button type="submit" id="btnSubmit"
                            class="px-8 py-3 bg-blue-700 hover:bg-blue-800 text-white font-bold
                                   rounded-xl text-sm transition-all active:scale-95 shadow-lg shadow-blue-200">
                            Cari Status
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if(isset($pengaduan))
            @php
                $isDitolak = $pengaduan->status === 'ditolak';

                $statusSteps = [
                    'pending'    => 0,
                    'proses'     => 1,
                    'diteruskan' => 2,
                    'selesai'    => 3,
                    'ditolak'    => 1,
                ];
                $currentStep = $statusSteps[$pengaduan->status] ?? 0;

                $steps = [
                    ['Menunggu Verifikasi',    'Aduan diterima, menunggu pengecekan berkas oleh petugas.'],
                    ['Sedang Diproses',        'Tim teknis sedang memproses solusi atas aduan Anda.'],
                    ['Disposisi Kasi',         'Laporan diteruskan ke Kepala Seksi terkait untuk arahan.'],
                    ['Selesai',                'Aduan telah selesai dan solusi telah diberikan.'],
                ];

                $statusColor = match($pengaduan->status) {
                    'selesai'    => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'proses'     => 'bg-blue-50 text-blue-700 border-blue-200',
                    'diteruskan' => 'bg-purple-50 text-purple-700 border-purple-200',
                    'ditolak'    => 'bg-red-50 text-red-700 border-red-200',
                    default      => 'bg-amber-50 text-amber-700 border-amber-200',
                };
            @endphp

            <div class="result-card space-y-4">

                {{-- ── INFO TIKET ──────────────────────────────────────── --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6
                            flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">
                            Status Pengaduan
                        </p>
                        <span class="inline-flex items-center px-4 py-1.5 rounded-full
                                     text-xs font-black border {{ $statusColor }}">
                            {{ strtoupper(\App\Helpers\StatusHelper::label($pengaduan->status)) }}
                        </span>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">
                            Nomor Tiket
                        </p>
                        <p class="font-extrabold text-slate-900 text-lg">#{{ $pengaduan->nomor_tiket }}</p>
                    </div>
                </div>

                {{-- ── BANNER DITOLAK ── --}}
                @if($isDitolak)
                    <div class="bg-red-50 border border-red-200 rounded-3xl p-6 flex gap-4 items-start">
                        <div class="w-10 h-10 rounded-2xl bg-red-100 flex items-center
                                    justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0
                                         015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-black text-red-800 text-sm mb-1">Laporan Tidak Dapat Diproses</p>
                            <p class="text-red-600/80 text-xs leading-relaxed">
                                Aduan Anda telah ditinjau oleh petugas dan dinyatakan tidak valid atau tidak
                                sesuai dengan kewenangan kantor kami. Silakan hubungi petugas untuk informasi
                                lebih lanjut jika Anda merasa ini adalah kesalahan.
                            </p>
                        </div>
                    </div>
                @endif

                {{-- ── DETAIL + TIMELINE ───────────────────────────────── --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">

                        {{-- Kiri: Info pemohon --}}
                        <div class="space-y-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400
                                                  uppercase tracking-wider mb-1">Pemohon</label>
                                    <p class="text-sm font-bold text-slate-800">{{ $pengaduan->nama }}</p>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400
                                                  uppercase tracking-wider mb-1">Tanggal</label>
                                    <p class="text-sm font-bold text-slate-800">
                                        {{ \Carbon\Carbon::parse($pengaduan->tgl_pengaduan)->translatedFormat('d F Y') }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400
                                                  uppercase tracking-wider mb-1">Kategori</label>
                                    <p class="text-sm font-bold text-slate-800">
                                        @php
                                            $labelKategori = [
                                                'Tikkim'        => 'Pelayanan Paspor',
                                                'Doklan_Paspor' => 'Dokumen Perjalanan',
                                                'Doklan_Izin'   => 'Pelayanan Izin Tinggal [WNA]',
                                                'Intel_WNA'     => 'Pengawasan Orang Asing [WNA]',
                                                'Intel_BAP'     => 'Alur BAP',
                                                'Tata Usaha'    => 'Sarana Prasarana',
                                            ];
                                            echo $labelKategori[$pengaduan->seksi_tujuan] ?? $pengaduan->seksi_tujuan;
                                        @endphp
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400
                                                  uppercase tracking-wider mb-1">Kanal</label>
                                    <p class="text-sm font-bold text-slate-800">{{ $pengaduan->kanal_pengaduan }}</p>
                                </div>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                <label class="block text-[10px] font-bold text-slate-400
                                              uppercase tracking-wider mb-2">Isi Aduan</label>
                                <p class="text-sm text-slate-700 leading-relaxed italic">
                                    "{{ $pengaduan->aduan }}"
                                </p>
                            </div>
                        </div>

                        {{-- Kanan: Timeline progress --}}
                        <div class="border-l-0 md:border-l border-slate-100 md:pl-8">
                            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-6">
                                Progress Tracking
                            </h3>
                            <div class="space-y-6">
                                @foreach($steps as $i => $step)
                                    @php
                                        $dotClass = 'timeline-dot';
                                        if ($i < $currentStep) {
                                            $dotClass .= ' done';
                                        } elseif ($i === $currentStep) {
                                            $dotClass .= $isDitolak ? ' active-red' : ' active';
                                        }
                                    @endphp
                                    <div class="timeline-item">
                                        <div class="{{ $dotClass }}">
                                            @if($i < $currentStep)
                                                <svg class="w-2.5 h-2.5 text-white m-auto mt-0.5"
                                                     fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0
                                                             01-1.414 0l-4-4a1 1 0 011.414-1.414L8
                                                             12.586l7.293-7.293a1 1 0 011.414 0z"/>
                                                </svg>
                                            @elseif($i === $currentStep && $isDitolak)
                                                <svg class="w-2.5 h-2.5 text-white m-auto mt-0.5"
                                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold
                                                {{ $i <= $currentStep ? ($isDitolak && $i === $currentStep ? 'text-red-700' : 'text-slate-800') : 'text-slate-300' }}">
                                                {{ ($i === $currentStep && $isDitolak) ? 'Laporan Ditolak' : $step[0] }}
                                            </p>
                                            <p class="text-[11px] leading-snug
                                                {{ $i <= $currentStep ? 'text-slate-500' : 'text-slate-300' }}">
                                                {{ ($i === $currentStep && $isDitolak) ? 'Aduan dinyatakan tidak valid oleh petugas.' : $step[1] }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- ── TIMELINE TANGGAPAN PETUGAS ──────────────────────── --}}
<div class="bg-white rounded-[14px] border border-slate-100 p-5">
    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">
        Timeline Tanggapan Petugas
    </p>

    @if($pengaduan->tindakLanjut)
        @php 
            $tl = $pengaduan->tindakLanjut; 
            $isDitolak = $pengaduan->status === 'ditolak';
        @endphp

        <div class="relative pl-10 group">
            {{-- Dot status --}}
            <div class="absolute left-0 top-1 w-6 h-6 rounded-full border-4 bg-white z-10
                        {{ $isDitolak ? 'border-red-500' : 'border-blue-600 ring-4 ring-blue-50' }}">
            </div>

            <div class="bg-slate-50/50 rounded-2xl p-6 border border-slate-100
                        group-hover:border-blue-200 group-hover:bg-white transition-all">
                <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-lg text-[10px] font-black
                                     uppercase tracking-widest text-white
                                     {{ $isDitolak ? 'bg-red-500' : 'bg-blue-600' }}">
                            {{ \App\Helpers\StatusHelper::label($pengaduan->status) }}
                        </span>
                        <span class="text-xs font-bold text-slate-400">
                            {{ $tl->updated_at->translatedFormat('d F Y • H:i') }}
                        </span>
                    </div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase
                                 tracking-widest bg-slate-100 px-2 py-1 rounded">
                        Oleh: {{ optional($tl->petugas)->name ?? 'Admin' }}
                    </span>
                </div>

                <div class="prose prose-sm max-w-none text-slate-600 italic leading-relaxed">
                    "{!! nl2br(e($tl->catatan_petugas)) !!}"
                </div>

                @if($tl->bukti_gambar)
                    <div class="mt-4 p-2 bg-white rounded-xl border border-slate-100 inline-block"
                        x-data="lightbox(['https://crgjblwavebvnvvdnzbk.supabase.co/storage/v1/object/public/pengaduan/{{ $tl->bukti_gambar }}'])">
                        
                        <p class="text-[9px] font-black text-slate-400 uppercase mb-2 ml-1">
                            Lampiran Bukti:
                        </p>
                        
                        <button type="button" @click="open(0)" class="focus:outline-none">
                            <img src="https://crgjblwavebvnvvdnzbk.supabase.co/storage/v1/object/public/pengaduan/{{ $tl->bukti_gambar }}"
                                alt="Bukti tindak lanjut"
                                class="h-32 w-auto rounded-lg object-cover shadow-sm cursor-pointer hover:opacity-90 transition">
                        </button>

                        <div x-show="visible"
                            class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
                            style="display:none;"
                            @keydown.escape.window="close()">
                            <div class="absolute inset-0" @click="close()"></div>
                            <div class="relative z-10 max-w-4xl w-full flex justify-center">
                                <img :src="images[current]" class="max-h-[85vh] max-w-full rounded-xl object-contain shadow-2xl">
                                <button type="button" @click="close()" class="absolute -top-12 right-0 text-white font-bold bg-white/10 hover:bg-white/25 px-4 py-2 rounded-lg transition backdrop-blur-sm">
                                    TUTUP
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    @else
        <div class="flex items-center gap-3 p-4 bg-slate-50 border border-dashed border-slate-200 rounded-xl">
            <svg class="w-4 h-4 text-slate-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <p class="text-sm text-slate-400 italic">
                Belum ada tanggapan resmi dari petugas.
            </p>
        </div>
    @endif
</div>

                    {{-- FOOTER --}}
                    <div class="px-8 py-4 bg-slate-50 border-t border-slate-100
                                flex justify-between items-center">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                            Update Terakhir: {{ $pengaduan->updated_at->diffForHumans() }}
                        </p>
                        <button onclick="window.print()"
                                class="text-[10px] font-black text-blue-600 uppercase
                                       hover:text-blue-800 transition-colors">
                            Cetak Bukti
                        </button>
                    </div>
                </div>
            </div>

        @elseif(request()->isMethod('post'))
            <div class="result-card bg-red-50 border-2 border-dashed border-red-200
                        rounded-3xl p-10 text-center">
                <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full
                            flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                    </svg>
                </div>
                <p class="font-black text-red-800 text-lg mb-1 tracking-tight">
                    Nomor Tiket Tidak Ditemukan
                </p>
                <p class="text-red-600/70 text-sm max-w-xs mx-auto">
                    Kami tidak menemukan data untuk tiket tersebut. Pastikan format penulisan sudah benar.
                </p>
            </div>
        @endif
    </div>

    <script>
        const form = document.getElementById('trackForm');
        const btn  = document.getElementById('btnSubmit');

        // Pengecekan aman dari teman Anda
        if (form) {
            form.addEventListener('submit', function () {
                btn.classList.add('btn-loading');
                btn.disabled = true;
            });
        }
        
        window.onload = () => {
            const input = document.getElementById('nomor_tiket');
            if (input) input.focus();
        };

        // Fungsi Lightbox dari Anda
        function lightbox(images) {
            return {
                images,
                visible: false,
                current: 0,
                open(index) { this.current = index; this.visible = true; document.body.style.overflow = 'hidden'; },
                close() { this.visible = false; document.body.style.overflow = ''; }
            }
        }
    </script>
</body>
</html>