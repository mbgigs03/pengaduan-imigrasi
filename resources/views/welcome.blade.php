<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Layanan Pengaduan — Kantor Imigrasi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        * { font-family: 'Plus Jakarta Sans', sans-serif; }

        .hero-bg {
            background: linear-gradient(135deg, #0f3460 0%, #1a5276 40%, #1f618d 70%, #2874a6 100%);
            position: relative;
            overflow: hidden;
        }
        .hero-bg::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle at 20% 50%, rgba(255,255,255,.05) 0%, transparent 60%),
                              radial-gradient(circle at 80% 20%, rgba(255,255,255,.04) 0%, transparent 50%);
        }
        .hero-bg::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 60px;
            background: #f1f5f9;
            clip-path: ellipse(55% 100% at 50% 100%);
        }
        .card-action {
            transition: all 0.2s ease;
        }
        .card-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 40px rgba(0,0,0,.12);
        }
        .badge-sla {
            background: rgba(255,255,255,.15);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,.2);
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fadeUp 0.5s ease both; }
        .fade-up-1 { animation-delay: .1s; }
        .fade-up-2 { animation-delay: .2s; }
        .fade-up-3 { animation-delay: .3s; }
        .fade-up-4 { animation-delay: .4s; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen">

    {{-- NAVBAR --}}
    <nav class="bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 h-14 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-700 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"/>
                    </svg>
                </div>
                <span class="font-bold text-slate-800 text-sm">Imigrasi</span>
                <span class="text-slate-300 text-sm">|</span>
                <span class="text-slate-500 text-xs">Layanan Pengaduan Masyarakat</span>
            </div>
            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 px-4 py-1.5 rounded-lg transition">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-slate-600 hover:text-slate-900 px-3 py-1.5 rounded-lg transition">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="text-sm font-semibold text-white bg-blue-700 hover:bg-blue-800 px-4 py-1.5 rounded-lg transition">
                        Daftar Petugas
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- HERO --}}
    <div class="hero-bg py-16 pb-24 px-4">
        <div class="max-w-5xl mx-auto text-center relative z-10">
            <div class="fade-up fade-up-1 inline-flex items-center gap-2 badge-sla text-white/90 text-xs font-semibold px-4 py-1.5 rounded-full mb-6">
                <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse inline-block"></span>
                Sistem berjalan normal · SLA 3 hari kerja
            </div>
            <h1 class="fade-up fade-up-2 text-4xl md:text-5xl font-extrabold text-white leading-tight mb-4">
                Layanan Pengaduan<br>
                <span class="text-blue-200">Masyarakat Imigrasi</span>
            </h1>
            <p class="fade-up fade-up-3 text-blue-100 text-base max-w-xl mx-auto leading-relaxed">
                Sampaikan keluhan, saran, atau pertanyaan Anda kepada kami. Setiap aduan ditangani secara transparan dan terukur.
            </p>
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="max-w-5xl mx-auto px-4 -mt-10 pb-16 relative z-10">

        {{-- KARTU AKSI UTAMA --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8 fade-up fade-up-4">

            <a href="{{ route('pengaduan.create') }}" class="card-action bg-white rounded-2xl p-7 border border-slate-100 shadow-sm flex items-start gap-5">
                <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-blue-50 flex items-center justify-center">
                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-slate-800 text-lg mb-1">Buat Aduan Baru</div>
                    <p class="text-slate-500 text-sm leading-relaxed">Sampaikan keluhan atau pertanyaan Anda. Tidak perlu login.</p>
                    <div class="mt-3 inline-flex items-center gap-1 text-blue-600 text-sm font-semibold">
                        Mulai sekarang
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </div>
            </a>

            <a href="{{ route('pengaduan.track') }}" class="card-action bg-white rounded-2xl p-7 border border-slate-100 shadow-sm flex items-start gap-5">
                <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-emerald-50 flex items-center justify-center">
                    <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-slate-800 text-lg mb-1">Cek Status Aduan</div>
                    <p class="text-slate-500 text-sm leading-relaxed">Pantau perkembangan aduan menggunakan nomor tiket Anda.</p>
                    <div class="mt-3 inline-flex items-center gap-1 text-emerald-600 text-sm font-semibold">
                        Lacak tiket
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </div>
            </a>
        </div>

        {{-- INFO ALUR --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-7 mb-6">
            <h2 class="font-bold text-slate-800 text-base mb-5">Alur Pengaduan</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach([
                    ['01', 'Isi Formulir', 'Data diri & uraian aduan', 'bg-blue-50 text-blue-600'],
                    ['02', 'Terima Tiket', 'Nomor tiket otomatis dikirim', 'bg-amber-50 text-amber-600'],
                    ['03', 'Diproses', 'Tim seksi menindaklanjuti', 'bg-purple-50 text-purple-600'],
                    ['04', 'Selesai', 'Konfirmasi dalam 3 hari kerja', 'bg-emerald-50 text-emerald-600'],
                ] as $step)
                    <div class="flex flex-col gap-2">
                        <div class="w-9 h-9 rounded-xl {{ $step[3] }} flex items-center justify-center font-extrabold text-sm">
                            {{ $step[0] }}
                        </div>
                        <div class="font-semibold text-slate-800 text-sm">{{ $step[1] }}</div>
                        <div class="text-slate-500 text-xs leading-relaxed">{{ $step[2] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- FOOTER NOTE --}}
        <p class="text-center text-slate-400 text-xs">
            Layanan ini dikelola oleh Kantor Imigrasi · Kerahasiaan data pemohon dijaga sepenuhnya
        </p>
    </div>

    {{-- SWEETALERT TIKET --}}
    @if (session('tiket'))
    <script>
        let timerInterval;
        let secondsLeft = 10;

        Swal.fire({
            title: 'Berhasil!',
            html: `
                <p class="mb-2">Aduan Anda telah diterima.</p>
                
                <div class="my-4 p-4 bg-gray-100 rounded-xl border border-blue-200 relative group">
                    <small class="text-gray-500 uppercase font-semibold text-[10px] tracking-wider">Nomor Tiket Anda:</small>
                    <div id="no-tiket" class="text-3xl font-bold text-blue-600 tracking-widest my-1">{{ session('tiket') }}</div>
                    
                    <button onclick="copyTicket()" id="btn-copy" 
                        class="mt-2 inline-flex items-center px-3 py-1 bg-white border border-blue-600 text-blue-600 text-xs font-bold rounded-lg hover:bg-blue-600 hover:text-white transition-all active:scale-95">
                        <span id="copy-icon" class="mr-1">📋</span> 
                        <span id="copy-text">Salin Nomor Tiket</span>
                    </button>
                </div>
                
                <div class="p-3 bg-emerald-50 border border-emerald-100 rounded-lg mb-4">
                    <p class="text-sm text-emerald-800 font-medium">
                        📍 <strong>Estimasi:</strong> 3 Hari Kerja
                    </p>
                </div>

                <p class="text-sm text-red-600 font-bold italic">
                    ⚠️ Wajib: Simpan/Catat nomor tiket ini!
                </p>
                
                <p class="mt-4 text-[10px] text-gray-400">
                    Tombol lanjut aktif dalam <b id="countdown-text" class="text-blue-600">10</b> detik...
                </p>
            `,
            icon: 'success',
            allowOutsideClick: false,
            allowEscapeKey: false,
            confirmButtonText: 'Oke, Saya Paham (10)',
            confirmButtonColor: '#2563eb',
            didOpen: () => {
                const b = Swal.getConfirmButton();
                b.disabled = true;

                timerInterval = setInterval(() => {
                    secondsLeft--;
                    const content = Swal.getHtmlContainer().querySelector('#countdown-text');
                    if (content) content.textContent = secondsLeft;
                    b.textContent = `Oke, Saya Paham (${secondsLeft})`;

                    if (secondsLeft <= 0) {
                        clearInterval(timerInterval);
                        b.disabled = false;
                        b.textContent = 'Oke, Saya Paham';
                    }
                }, 1000);
            },
            willClose: () => {
                clearInterval(timerInterval);
            }
        });

        // Fungsi Salin Nomor Tiket
        function copyTicket() {
            const text = document.getElementById('no-tiket').innerText;
            const btnText = document.getElementById('copy-text');
            const btnIcon = document.getElementById('copy-icon');

            navigator.clipboard.writeText(text).then(() => {
                // Efek visual saat berhasil copy
                btnText.innerText = 'Tersalin!';
                btnIcon.innerText = '✅';
                document.getElementById('btn-copy').classList.add('bg-blue-600', 'text-white');
                
                // Kembalikan ke asal setelah 2 detik
                setTimeout(() => {
                    btnText.innerText = 'Salin Nomor Tiket';
                    btnIcon.innerText = '📋';
                    document.getElementById('btn-copy').classList.remove('bg-blue-600', 'text-white');
                }, 2000);
            }).catch(err => {
                console.error('Gagal menyalin: ', err);
            });
        }
    </script>
@endif

</body>
</html>