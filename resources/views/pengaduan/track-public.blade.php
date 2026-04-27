{{-- resources/views/pengaduan/track.blade.php --}}
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
            padding: 11px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            background: #f8fafc;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: .05em;
            transition: border-color .2s;
        }
        .input-field:focus {
            border-color: #2563eb;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(37,99,235,.08);
        }
        .timeline-item { position: relative; padding-left: 28px; }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 6px; top: 18px;
            width: 1.5px;
            height: calc(100% - 4px);
            background: #e2e8f0;
        }
        .timeline-item:last-child::before { display: none; }
        .timeline-dot {
            position: absolute;
            left: 0; top: 4px;
            width: 14px; height: 14px;
            border-radius: 50%;
            border: 2.5px solid #e2e8f0;
            background: #fff;
        }
        .timeline-dot.active {
            border-color: #2563eb;
            background: #2563eb;
        }
        .timeline-dot.done {
            border-color: #10b981;
            background: #10b981;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .result-card { animation: fadeUp .4s ease both; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-10 px-4">
    @php use App\Helpers\StatusHelper; @endphp
    <div class="max-w-2xl mx-auto">

        {{-- BACK --}}
        <a href="{{ route('pengaduan.landing') }}"
            class="inline-flex items-center gap-1.5 text-slate-500 hover:text-slate-800 text-sm mb-6 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Beranda
        </a>

        {{-- HEADER CARD --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-4">
            <div class="bg-gradient-to-r from-blue-800 to-blue-600 px-7 py-6 text-white">
                <h1 class="text-xl font-extrabold mb-1">Lacak Status Pengaduan</h1>
                <p class="text-blue-200 text-sm">Masukkan nomor tiket yang Anda terima saat pengajuan aduan.</p>
            </div>

            <div class="p-6">
                <form action="{{ route('pengaduan.searchTrack') }}" method="POST">
                    @csrf
                    <div class="flex gap-3">
                        <input type="text" name="nomor_tiket"
                            placeholder="Contoh: IMI-20260414-001"
                            class="input-field"
                            value="{{ old('nomor_tiket', isset($pengaduan) ? $pengaduan->nomor_tiket : '') }}"
                            required>
                        <button type="submit"
                            class="px-6 py-2.5 bg-blue-700 hover:bg-blue-800 text-white font-bold rounded-xl text-sm transition flex-shrink-0">
                            Cari
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- HASIL PENCARIAN --}}
        @if(isset($pengaduan))
            @php
                $statusSteps = [
                    'pending'    => 0,
                    'proses'     => 1,
                    'diteruskan' => 2,
                    'selesai'    => 3,
                ];
                $currentStep = $statusSteps[$pengaduan->status] ?? 0;
            
                // Label timeline disesuaikan dengan SOP baru
                $steps = [
                    ['Menunggu Verifikasi',    'Aduan diterima, menunggu verifikasi petugas'],
                    ['Disposisi Kasi',         'Sedang didisposisi oleh Kepala Seksi'],
                    ['Sedang Ditindaklanjuti', 'Aduan sedang ditangani oleh seksi tujuan'],
                    ['Selesai',                'Aduan telah selesai ditindaklanjuti'],
                ];
            
                $statusColor = match($pengaduan->status) {
                    'selesai'    => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'proses'     => 'bg-blue-50 text-blue-700 border-blue-200',
                    'diteruskan' => 'bg-purple-50 text-purple-700 border-purple-200',
                    default      => 'bg-amber-50 text-amber-700 border-amber-200',
                };
            @endphp

            <div class="result-card bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

                {{-- HEADER TIKET --}}
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <div class="text-xs text-slate-500 mb-0.5">Nomor Tiket</div>
                        <div class="font-extrabold text-slate-800 text-lg tracking-wide">{{ $pengaduan->nomor_tiket }}</div>
                    </div>
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold border {{ $statusColor }}">
                        {{ strtoupper(StatusHelper::label($pengaduan->status)) }}
                    </span>
                </div>

                {{-- DETAIL --}}
                <div class="px-6 py-5">
                    <div class="grid grid-cols-2 gap-x-6 gap-y-4 mb-5 text-sm">
                        <div>
                            <div class="text-xs text-slate-500 mb-0.5">Nama Pemohon</div>
                            <div class="font-semibold text-slate-800">{{ $pengaduan->nama }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500 mb-0.5">Tanggal Masuk</div>
                            <div class="font-semibold text-slate-800">
                                {{ \Carbon\Carbon::parse($pengaduan->tgl_pengaduan)->translatedFormat('d F Y') }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500 mb-0.5">Seksi Tujuan</div>
                            <div class="font-semibold text-slate-800 capitalize">
                                {{ str_replace('_', ' ', $pengaduan->seksi_tujuan) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500 mb-0.5">Kanal</div>
                            <div class="font-semibold text-slate-800">{{ $pengaduan->kanal_pengaduan }}</div>
                        </div>
                        <div class="col-span-2">
                            <div class="text-xs text-slate-500 mb-0.5">Isi Aduan</div>
                            <div class="text-slate-700 bg-slate-50 rounded-xl p-3 text-sm leading-relaxed border border-slate-100">
                                {{ $pengaduan->aduan }}
                            </div>
                        </div>
                    </div>

                    {{-- TIMELINE PROGRES --}}
                    <div class="border-t border-slate-100 pt-5 mb-5">
                        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Progress Penanganan</div>
                        <div class="space-y-4">
                            @foreach($steps as $i => $step)
                                <div class="timeline-item">
                                    <div class="timeline-dot {{ $i < $currentStep ? 'done' : ($i === $currentStep ? 'active' : '') }}"></div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="text-sm font-semibold {{ $i <= $currentStep ? 'text-slate-800' : 'text-slate-400' }}">
                                                {{ $step[0] }}
                                            </div>
                                            <div class="text-xs {{ $i <= $currentStep ? 'text-slate-500' : 'text-slate-300' }}">
                                                {{ $step[1] }}
                                            </div>
                                        </div>
                                        @if ($i === $currentStep)
                                            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full">Saat ini</span>
                                        @elseif ($i < $currentStep)
                                            <span class="text-xs font-bold text-emerald-600">✓</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    {{-- UNDUH PDF Pemohon --}}
                    @if ($pengaduan->pdf_url)
                        <a href="{{ $pengaduan->pdf_url }}" target="_blank"
                            class="inline-flex items-center gap-2 text-sm px-4 py-2 bg-blue-50 text-blue-700 border border-blue-200 rounded-xl hover:bg-blue-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Unduh Bukti Pengaduan (PDF)
                        </a>
                    @endif

                    {{-- TANGGAPAN ADMIN --}}
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Tanggapan Petugas</div>
                        <p class="text-sm text-slate-700 leading-relaxed italic">
                            {{ $pengaduan->keterangan_admin ?? 'Belum ada tanggapan. Aduan Anda sedang dalam antrean penanganan.' }}
                        </p>
                        @if ($pengaduan->tindakLanjut)
                            <div class="mt-3 pt-3 border-t border-slate-200 text-xs text-slate-400">
                                Diselesaikan:
                                {{ \Carbon\Carbon::parse($pengaduan->tindakLanjut->tanggal_selesai)->translatedFormat('d F Y, H:i') }}
                            </div>
        
                        @endif
                    </div>
                </div>
            </div>

        @elseif(request()->isMethod('post'))
            <div class="bg-red-50 border border-red-200 rounded-2xl p-6 text-center">
                <div class="text-3xl mb-2">🔍</div>
                <p class="font-bold text-red-700 mb-1">Nomor Tiket Tidak Ditemukan</p>
                <p class="text-red-500 text-sm">Pastikan Anda memasukkan nomor tiket yang benar (contoh: <span class="font-mono">IMI-20260414-001</span>)</p>
            </div>
        @endif

        
    </div>

</body>
</html>