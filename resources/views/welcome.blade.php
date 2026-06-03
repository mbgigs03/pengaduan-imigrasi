<!DOCTYPE html>
<html lang="id">
<head>
    <!-- Untuk Browser Standar -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-rumangsa.png') }}">

    <!-- Untuk Ikon di Layar Utama iPhone/iPad -->
    <link rel="apple-touch-icon" href="{{ asset('images/logo-rumangsa.png') }}">
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

        .hero-bg {
        /* Warna Navy Khas Imigrasi */
        background: linear-gradient(135deg, #0d1b2a 0%, #1b263b 40%, #415a77 100%);
        }
        .badge-sla {
            /* Warna Aksen Emas tipis untuk kesan eksklusif */
            border: 1px solid rgba(229, 184, 11, 0.3);
            color: #e5b80b;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen">

    {{-- NAVBAR --}}
    <nav class="bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 h-14 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-white-700 flex items-center justify-center overflow-hidden">
                    <img src="{{ asset('images/logo-rumangsa.png') }}" alt="Logo Rumangsa" class="w-full h-full object-contain">
                </div>
                <span class="font-bold text-slate-800 text-lg tracking-tight">RUMANGSA</span>
                <span class="text-slate-300 text-sm">|</span>
                <span class="text-slate-500 text-xs">Ruang Manajemen Pengaduan dengan Integrasi Sistem Digital</span>
            </div>
            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 px-4 py-1.5 rounded-lg transition">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-white bg-blue-700 hover:bg-blue-800 px-4 py-1.5 rounded-lg transition">
                        Masuk
                    </a>
                    <!-- <a href="{{ route('register') }}" class="text-sm font-semibold text-white bg-blue-700 hover:bg-blue-800 px-4 py-1.5 rounded-lg transition">
                        Daftar Petugas
                    </a> -->
                @endauth
            </div>
        </div>
    </nav>

    {{-- HERO --}}
    <div class="hero-bg py-16 pb-24 px-4">
        <div class="max-w-5xl mx-auto text-center relative z-10">
            <div class="fade-up fade-up-1 inline-flex items-center gap-2 badge-sla text-white/90 text-xs font-semibold px-4 py-1.5 rounded-full mb-6">
                <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse inline-block"></span>
                Sistem berjalan normal · Waktu Penanganan Pengaduan 3 hari kerja
            </div>
            {{-- HERO UPDATE --}}
            <h1 class="fade-up fade-up-2 text-4xl md:text-6xl font-extrabold text-white leading-tight mb-4">
                Aplikasi <span class="text-blue-200">RUMANGSA</span>
            </h1>
            <p class="fade-up fade-up-3 text-blue-100 text-lg max-w-2xl mx-auto leading-relaxed">
                <strong>"Ruang Manajemen Pengaduan dengan Integrasi Sistem Digital"</strong><br>
                Transformasi Digital Peningkatan Kualitas Pelayanan<br>Kantor Imigrasi Kelas II Non TPI Madiun.
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
                    <div class="font-bold text-slate-800 text-lg mb-1">Buat Pengaduan Baru</div>
                    <p class="text-slate-500 text-sm leading-relaxed">Sampaikan pengaduan atau pertanyaan anda.<br>Tidak perlu login.</p>
                    <div class="mt-3 inline-flex items-center gap-1 text-blue-600 text-sm font-semibold">
                        Buat Pengaduan
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
                    <div class="font-bold text-slate-800 text-lg mb-1">Cek Status Pengaduan</div>
                    <p class="text-slate-500 text-sm leading-relaxed">Lihat perkembangan pengaduan menggunakan nomor tiket Anda.</p>
                    <div class="mt-3 inline-flex items-center gap-1 text-emerald-600 text-sm font-semibold">
                        Lihat Status Pengaduan
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

    </div>
    {{-- FOOTER --}}
    <footer class="bg-white border-t border-slate-200 pt-12 pb-8">
        <div class="max-w-5xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                
                {{-- Kolom 1: Branding --}}
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded bg-slate-800 flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="font-bold text-slate-800 tracking-tight">RUMANGSA</span>
                    </div>
                    <p class="text-slate-500 text-xs leading-relaxed">
                        Sistem informasi pelayanan pengaduan terpadu untuk mewujudkan keterbukaan informasi dan pelayanan prima di wilayah kerja Madiun.
                    </p>
                </div>

                {{-- Kolom 2: Kontak --}}
                <div class="space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Hubungi Kami</h3>
                    <ul class="space-y-2">
                        <li class="flex items-start gap-3 text-xs text-slate-600">
                            <svg class="w-4 h-4 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>kanim_madiun@imigrasi.go.id</span>
                        </li>
                        <li class="flex items-start gap-3 text-xs text-slate-600">
                            <svg class="w-4 h-4 text-green-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            <span>WhatsApp: 0811-3093-000</span>
                        </li>
                    </ul>
                </div>

                {{-- Kolom 3: Lokasi --}}
                <div class="space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Alamat Kantor</h3>
                    <div class="flex items-start gap-3 text-xs text-slate-600">
                        <svg class="w-4 h-4 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <p class="leading-relaxed">
                            <strong>Kantor Imigrasi Kelas II Non TPI Madiun</strong><br>
                            Jl. Panglima Sudirman, Caruban, Kab. Madiun, Jawa Timur
                        </p>
                    </div>
                </div>

            </div>

            {{-- Bottom Copyright --}}
            <div class="pt-8 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-[10px] text-slate-400 font-medium uppercase tracking-widest text-center md:text-left">
                    © {{ date('Y') }} Kantor Imigrasi Madiun · Proyek Perubahan RUMANGSA
                </p>
                <div class="flex items-center gap-4">
                    <img src="{{ asset('images/logo-imigrasi.png') }}" class="h-20" alt="KEMENIMIPAS">
                    <img src="{{ asset('images/logo-png.png') }}" class="h-20" alt="IMIGRASI">
                </div>
            </div>
        </div>
    </footer>

    {{-- Tambahkan library di layout utama atau sebelum script ini --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

@if (session('tiket'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let timerInterval;
        let secondsLeft = 10;

        // Data dari Session
        const rawCategory = "{{ session('seksi_tujuan') }}";

        // Log ini untuk mengecek apakah data dari session benar-benar masuk
        console.log("Raw Category dari Session:", rawCategory);

        const categoryMap = {
            'Tikkim': 'Pelayanan Paspor',
            'Doklan_Paspor': 'Dokumen Perjalanan',
            'Doklan_Izin': 'Pelayanan Izin Tinggal [WNA]',
            'Intel_WNA': 'Pengawasan Orang Asing [WNA]',
            'Intel_BAP': 'Alur BAP',
            'Tata Usaha': 'Sarana Prasarana'
        };

        const ticketData = {
            nomor: "{{ session('tiket') }}",
            tanggal: "{{ date('d/m/Y H:i') }}",
            // Jika rawCategory kosong atau tidak ada di map, akan menampilkan 'Kategori Tidak Diketahui'
            kategori: categoryMap[rawCategory] || 'Kategori Tidak Diketahui',
            instansi: "Kantor Imigrasi Kelas II Non TPI Madiun"
        };

        // --- FUNGSI DOWNLOAD PDF ---
        const downloadTicketPDF = () => {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({
                orientation: 'p',
                unit: 'mm',
                format: [80, 120] // Ukuran thermal/struk kustom
            });

            // Header - Garis Atas
            doc.setFillColor(37, 99, 235); // Blue-600
            doc.rect(0, 0, 80, 15, 'F');
            
            doc.setTextColor(255, 255, 255);
            doc.setFont("helvetica", "bold");
            doc.setFontSize(10);
            doc.text(ticketData.instansi, 40, 9, { align: 'center' });

            // Body
            doc.setTextColor(60, 60, 60);
            doc.setFontSize(8);
            doc.setFont("helvetica", "normal");
            doc.text("BUKTI REGISTRASI ADUAN", 40, 22, { align: 'center' });

            // Box Nomor Tiket
            doc.setDrawColor(200, 200, 200);
            doc.roundedRect(10, 28, 60, 20, 3, 3, 'S');
            
            doc.setFontSize(7);
            doc.text("NOMOR TIKET ANDA", 40, 33, { align: 'center' });
            doc.setFontSize(16);
            doc.setTextColor(37, 99, 235);
            doc.setFont("helvetica", "bold");
            doc.text(ticketData.nomor, 40, 42, { align: 'center' });

            // Informasi Detail
            doc.setTextColor(80, 80, 80);
            doc.setFontSize(8);
            doc.setFont("helvetica", "bold");
            doc.text("Detail Laporan:", 10, 58);
            
            doc.setLineWidth(0.1);
            doc.line(10, 59, 70, 59);

            doc.setFont("helvetica", "normal");
            doc.text("Tanggal", 10, 65);
            doc.text(": " + ticketData.tanggal, 25, 65);

            doc.text("Kategori", 10, 72);
            // Handle text wrapping untuk kategori panjang
            const splitKategori = doc.splitTextToSize(ticketData.kategori, 45);
            doc.text(":", 25, 72);
            doc.text(splitKategori, 27, 72);

            // Footer / Note
            doc.setFontSize(7);
            doc.setFont("helvetica", "italic");
            doc.setTextColor(150, 150, 150);
            const note = "Harap simpan tiket ini untuk melakukan pengecekan status aduan secara berkala.";
            const splitNote = doc.splitTextToSize(note, 60);
            doc.text(splitNote, 40, 95, { align: 'center' });

            doc.save(`Tiket_${ticketData.nomor}.pdf`);
        };

        // --- TAMPILAN SWEETALERT ---
        Swal.fire({
            title: '<span class="text-blue-600">Berhasil Terkirim!</span>',
            html: `
                <div class="text-left bg-gray-50 p-4 rounded-2xl border border-gray-100 shadow-inner">
                    <div class="flex justify-between mb-3 text-[11px] text-gray-500 uppercase tracking-widest font-bold">
                        <span>Detail Aduan</span>
                        <span>${ticketData.tanggal}</span>
                    </div>
                    
                    <div class="mb-4">
                        <label class="text-[10px] text-gray-400 block">Kategori Tujuan:</label>
                        <span class="text-sm font-semibold text-gray-700">${ticketData.kategori}</span>
                    </div>

                    <div class="p-4 bg-white rounded-xl border-2 border-dashed border-blue-200 text-center relative overflow-hidden">
                        <div class="absolute top-0 right-0 bg-blue-100 text-blue-600 text-[8px] px-2 py-1 rounded-bl-lg font-black">E-TICKET</div>
                        <small class="text-gray-400 uppercase text-[9px] font-bold">Nomor Tiket Anda</small>
                        <div id="no-tiket" class="text-3xl font-black text-blue-600 tracking-tighter my-1">${ticketData.nomor}</div>
                        
                        <button onclick="copyTicket()" id="btn-copy" 
                            class="mt-2 inline-flex items-center px-4 py-1.5 bg-blue-50 text-blue-600 text-xs font-bold rounded-full hover:bg-blue-600 hover:text-white transition-all active:scale-95 border border-blue-200">
                            <span id="copy-icon" class="mr-1">📋</span> 
                            <span id="copy-text">Salin Nomor Tiket</span>
                        </button>
                    </div>
                </div>

                <div class="mt-4 p-3 bg-emerald-50 rounded-xl border border-emerald-100 flex items-center gap-3">
                    <div class="bg-emerald-500 text-white p-2 rounded-lg animate-bounce">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    </div>
                    <p class="text-[11px] text-emerald-800 leading-tight text-left">
                        <strong>Auto-Download:</strong> Bukti aduan PDF sedang diunduh secara otomatis ke perangkat Anda.
                    </p>
                </div>

                <p class="mt-4 text-[10px] text-gray-400">
                    Tombol lanjut aktif dalam <b id="countdown-text" class="text-blue-600">10</b> detik...
                </p>
            `,
            icon: 'success',
            allowOutsideClick: false,
            confirmButtonText: `Oke, Saya Paham (${secondsLeft})`,
            confirmButtonColor: '#2563eb',
            didOpen: () => {
                const b = Swal.getConfirmButton();
                b.disabled = true;

                // Memicu download PDF saat popup terbuka
                downloadTicketPDF();

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
            }
        });
    });

    // Fungsi Salin (Scope Global agar bisa dipanggil onclick HTML SweetAlert)
    window.copyTicket = function() {
        const text = document.getElementById('no-tiket').innerText;
        const btnText = document.getElementById('copy-text');
        const btnIcon = document.getElementById('copy-icon');
        const btn = document.getElementById('btn-copy');

        navigator.clipboard.writeText(text).then(() => {
            btnText.innerText = 'Tersalin!';
            btnIcon.innerText = '✅';
            btn.classList.add('bg-blue-600', 'text-white');
            
            setTimeout(() => {
                btnText.innerText = 'Salin Nomor Tiket';
                btnIcon.innerText = '📋';
                btn.classList.remove('bg-blue-600', 'text-white');
            }, 2000);
        });
    }
</script>
@endif

</body>
</html>