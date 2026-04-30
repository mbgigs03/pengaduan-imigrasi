<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form Pengaduan - Kantor Imigrasi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        * { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* Konsistensi Hero Background dari halaman Index */
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
            height: 40px;
            background: #f1f5f9; /* Slate-100 */
            clip-path: ellipse(60% 100% at 50% 100%);
        }

        /* Animasi */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fadeUp 0.5s ease both; }
        .fade-up-1 { animation-delay: .1s; }
        .fade-up-2 { animation-delay: .2s; }

        /* Custom Scrollbar untuk Drag & Drop grid */
        .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    </style>

    <script>
        function buktiUploader() {
            return {
                previews: [],
                files: [],
                dragging: false,

                handleFiles(e) {
                    this.addFiles(Array.from(e.target.files));
                },
                handleDrop(e) {
                    this.dragging = false;
                    const dropped = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
                    this.addFiles(dropped);
                },
                addFiles(newFiles) {
                    const remaining = 5 - this.files.length;
                    newFiles.slice(0, remaining).forEach(file => {
                        this.files.push(file);
                        const reader = new FileReader();
                        reader.onload = (e) => this.previews.push(e.target.result);
                        reader.readAsDataURL(file);
                    });
                    this.$nextTick(() => this.syncInput());
                },
                removeFile(index) {
                    this.files.splice(index, 1);
                    this.previews.splice(index, 1);
                    this.syncInput();
                },
                syncInput() {
                    const input = this.$el.querySelector('input[type=file][multiple]');
                    const dt = new DataTransfer();
                    this.files.forEach(f => dt.items.add(f));
                    input.files = dt.files;
                }
            }
        }

        function faqPicker() {
            const templates = {
                paspor_baru_dewasa: `Persyaratan permohonan paspor baru (dewasa) dengan membawa dokumen ASLI:
                    1. e-KTP
                    2. Kartu Keluarga (KK)
                    3. Akte Lahir / Buku Nikah / Ijazah SD-SMA (pilih salah satu; nama, tempat tanggal lahir, dan nama ayah harus sama dengan e-KTP dan KK)
                    4. Paspor lama (jika memiliki)

                    Pendaftaran:
                    - Pemohon usia di bawah 60 tahun wajib mendaftar online melalui aplikasi M-Paspor (Playstore/Appstore)
                    - Setelah mendaftar dan melakukan pembayaran, datang langsung ke kantor sesuai lokasi dan waktu yang dipilih
                    - Bawa semua dokumen ASLI untuk proses foto dan wawancara

                    Biaya paspor:
                    - Elektronik masa berlaku 5 tahun: Rp 650.000
                    - Elektronik masa berlaku 10 tahun: Rp 950.000

                    Layanan:
                    - Reguler: paspor jadi 3 hari kerja setelah pembayaran, foto, dan wawancara
                    - Percepatan: paspor jadi 4 jam (berkas diterima sebelum 10.00 WIB), biaya tambahan Rp 1.000.000`,

                            paspor_anak: `Persyaratan permohonan paspor anak (belum memiliki e-KTP) dengan dokumen ASLI:
                    1. e-KTP kedua orang tua kandung
                    2. Kartu Keluarga (KK)
                    3. Akta Lahir anak
                    4. Buku / Surat Nikah orang tua; jika bercerai lampirkan surat perceraian
                    5. Paspor kedua orang tua (jika memiliki)
                    6. Paspor lama anak (jika memiliki)

                    Ketentuan kehadiran orang tua:
                    - Kedua orang tua wajib hadir saat proses permohonan
                    - Jika salah satu tidak bisa hadir, wajib melampirkan surat kuasa bermaterai beserta alasan ketidakhadirannya
                    - Jika orang tua bercerai dan memiliki hak asuh tertulis dari pengadilan, salah satu orang tua dapat mengurus tanpa kehadiran yang lain

                    Pendaftaran:
                    - Anak usia di atas 3 tahun wajib daftar online melalui M-Paspor
                    - Datang sesuai lokasi dan waktu yang dipilih, bawa semua dokumen ASLI

                    Biaya paspor anak:
                    - Elektronik masa berlaku 5 tahun: Rp 650.000

                    Layanan:
                    - Reguler: paspor jadi 3 hari kerja setelah pembayaran, foto, dan wawancara
                    - Percepatan: paspor jadi 4 jam (berkas diterima sebelum 10.00 WIB), biaya tambahan Rp 1.000.000`,

                            paspor_umroh_haji: `Persyaratan permohonan paspor untuk umroh / haji dengan dokumen ASLI:
                    1. e-KTP
                    2. Kartu Keluarga (KK)
                    3. Akte Lahir / Buku Nikah / Ijazah SD-SMA (pilih salah satu)
                    4. Paspor lama (jika memiliki)

                    Catatan khusus nama satu kata:
                    Pemohon yang hanya memiliki 1 kata pada nama wajib membawa dokumen tambahan:
                    - Surat rekomendasi dari travel umroh / haji
                    - Izin operasional travel umroh
                    - BPIH bagi calon jamaah haji

                    Pendaftaran:
                    - Wajib daftar online melalui M-Paspor untuk pemohon usia di bawah 60 tahun
                    - Datang sesuai lokasi dan waktu yang dipilih dengan membawa semua dokumen ASLI

                    Biaya:
                    - Elektronik masa berlaku 5 tahun: Rp 650.000
                    - Elektronik masa berlaku 10 tahun: Rp 950.000

                    Layanan:
                    - Reguler: paspor jadi 3 hari kerja
                    - Percepatan: paspor jadi 4 jam (sebelum 10.00 WIB), tambahan Rp 1.000.000`,

                            paspor_cpmi: `Persyaratan permohonan paspor untuk bekerja ke luar negeri (CPMI) dengan dokumen ASLI:
                    1. e-KTP
                    2. Kartu Keluarga (KK)
                    3. Akta Lahir / Ijazah SD-SMA / Buku Nikah
                    4. Paspor lama (jika memiliki)

                    Kebijakan paspor gratis bagi CPMI:
                    Berlaku bagi CPMI yang baru pertama kali membuat paspor, datang langsung ke kantor dengan tambahan dokumen:
                    - ID CPMI yang dikeluarkan oleh BP2MI, ATAU
                    - Kontrak kerja yang telah ditandatangani secara sah / sertifikat kelulusan program G to G

                    Pendaftaran (bagi yang tidak termasuk paspor gratis):
                    - Wajib daftar online melalui M-Paspor untuk pemohon usia di bawah 60 tahun
                    - Datang sesuai lokasi dan waktu yang dipilih dengan membawa semua dokumen ASLI

                    Biaya:
                    - Elektronik masa berlaku 5 tahun: Rp 650.000
                    - Elektronik masa berlaku 10 tahun: Rp 950.000

                    Layanan:
                    - Reguler: paspor jadi 3 hari kerja
                    - Percepatan: paspor jadi 4 jam (sebelum 10.00 WIB), tambahan Rp 1.000.000`,

                            paspor_rusak: `Prosedur penggantian paspor RUSAK:

                    Datang langsung ke Kantor Imigrasi tanpa mendaftar melalui M-Paspor untuk proses BAP (Berita Acara Pemeriksaan).

                    Dokumen ASLI yang harus dibawa:
                    1. e-KTP
                    2. Kartu Keluarga (KK)
                    3. Akta Lahir / Ijazah SD-SMA / Buku Nikah
                    4. Paspor yang rusak

                    Biaya:
                    - Denda paspor rusak: Rp 500.000
                    - Ditambah biaya jenis paspor yang dipilih:
                    - Elektronik masa berlaku 5 tahun: Rp 650.000
                    - Elektronik masa berlaku 10 tahun: Rp 950.000`,

                            paspor_hilang: `Prosedur penggantian paspor HILANG:

                    Langkah pertama: urus Surat Keterangan Kehilangan di kantor kepolisian terdekat.

                    Setelah memiliki surat keterangan kehilangan, datang langsung ke Kantor Imigrasi mulai pukul 08.00 WIB TANPA mendaftar melalui M-Paspor untuk proses BAP.

                    Dokumen ASLI yang harus dibawa:
                    1. e-KTP
                    2. Kartu Keluarga (KK)
                    3. Akta Lahir / Ijazah SD-SMA / Buku Nikah
                    4. Surat Keterangan Kehilangan dari kepolisian

                    Biaya:
                    - Denda paspor hilang: Rp 1.000.000
                    - Ditambah biaya jenis paspor yang dipilih:
                    - Elektronik masa berlaku 5 tahun: Rp 650.000
                    - Elektronik masa berlaku 10 tahun: Rp 950.000`,

                            pengambilan_diwakilkan: `Ketentuan pengambilan paspor yang DIWAKILKAN:

                    A. Diwakilkan kepada orang yang BERBEDA Kartu Keluarga:
                    1. Surat kuasa pengambilan paspor (ditandatangani pemilik paspor di atas materai Rp 10.000)
                    2. Lembar pengambilan paspor dari petugas foto/wawancara
                    3. Struk bukti pembayaran paspor
                    4. e-KTP asli pengambil paspor
                    5. Fotokopi e-KTP pemilik paspor

                    B. Diwakilkan kepada keluarga dalam SATU Kartu Keluarga:
                    1. Lembar pengambilan paspor dari petugas foto/wawancara
                    2. Struk bukti pembayaran paspor
                    3. Kartu Keluarga asli
                    4. e-KTP asli pengambil paspor`,

                            pembatalan_paspor: `Permohonan pembatalan paspor.

                    Mohon lengkapi data berikut agar dapat kami teruskan kepada petugas:

                    - Nama Lengkap Pemohon Paspor    :
                    - Alamat                          :
                    - Nomor WhatsApp                  :
                    - Tanggal Permohonan Paspor       :
                    - Lokasi Foto & Wawancara         :
                    - Alasan Pembatalan               :`,

                            kekurangan_berkas: `Perihal kekurangan berkas / catatan dari petugas.

                    Mohon informasikan:
                    - Nama lengkap pemohon            :
                    - Tanggal kunjungan ke kantor     :
                    - Jenis layanan yang diajukan     :
                    - Catatan / berkas yang kurang    :

                    Jika ada lembar catatan kekurangan berkas dari petugas, mohon lampirkan foto lembar tersebut pada kolom bukti di bawah.`,

                            lainnya: '',
                        };

                        return {
                            topik: '',
                            currentTemplate: '',
                            applyTemplate() {
                                this.currentTemplate = templates[this.topik] ?? '';
                                
                                // Sync ke textarea asli jika perlu
                                const textarea = document.getElementById('aduan');
                                if (textarea) {
                                    textarea.value = this.currentTemplate;
                                    textarea.dispatchEvent(new Event('input'));
                                }
                            }
                        };
        }
    </script>
</head>

<body class="bg-slate-100 min-h-screen flex flex-col">

    {{-- NAVBAR --}}
    <nav class="bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 h-14 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-700 flex items-center justify-center shadow-sm">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"/>
                    </svg>
                </div>
                <span class="font-bold text-slate-800 text-sm">Imigrasi</span>
                <span class="text-slate-300 text-sm">|</span>
                <span class="text-slate-500 text-xs hidden sm:inline">Layanan Pengaduan Masyarakat</span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('pengaduan.landing') }}" class="text-sm font-semibold text-slate-600 hover:text-blue-700 bg-slate-50 hover:bg-blue-50 border border-slate-200 px-4 py-1.5 rounded-lg transition inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali
                </a>
            </div>
        </div>
    </nav>

    {{-- MINI HERO --}}
    <div class="hero-bg py-12 pb-20 px-4">
        <div class="max-w-4xl mx-auto text-center relative z-10 fade-up">
            <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-3 tracking-tight">Formulir Pengaduan</h1>
            <p class="text-blue-100 text-sm md:text-base max-w-2xl mx-auto">
                Silakan lengkapi formulir di bawah ini. Pastikan data yang Anda berikan valid agar kami dapat menindaklanjuti laporan dengan akurat.
            </p>
        </div>
    </div>

    {{-- MAIN FORM CONTAINER --}}
    <div class="max-w-4xl w-full mx-auto px-4 -mt-12 relative z-10 pb-16 flex-grow fade-up fade-up-1">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 md:p-8">
                @if ($errors->any())
                    <div class="mb-8 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <h3 class="text-red-800 font-bold text-sm mb-1">Gagal Mengirim! Periksa isian berikut:</h3>
                            <ul class="list-disc list-inside text-sm text-red-700">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form action="{{ route('pengaduan.store') }}" method="POST" enctype="multipart/form-data" 
                    x-data="{ topik: '', jenis: '{{ old('jenis_layanan') }}', currentTemplate: '', ...faqPicker() }">
                    @csrf

                    {{-- 1. Informasi Pemohon (Selalu Muncul) --}}
                    <div class="mb-8">
                        <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2 flex items-center gap-2">
                            <span class="bg-blue-100 text-blue-700 w-6 h-6 rounded-md flex items-center justify-center text-xs">1</span>
                            Informasi Pemohon
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <x-input-label for="nama" :value="__('Nama Lengkap')" class="text-slate-700 font-semibold" />
                                <x-text-input id="nama" name="nama" type="text" value="{{ old('nama') }}" class="mt-1 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm transition" required />
                            </div>
                            <div>
                                <x-input-label for="nik" :value="__('NIK (16 Digit)')" class="text-slate-700 font-semibold" />
                                <x-text-input id="nik" name="nik" type="text" value="{{ old('nik') }}" placeholder="Contoh: 35..." maxlength="16" class="mt-1 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm transition" required />
                            </div>
                            <div>
                                <x-input-label for="tgl_pengaduan" :value="__('Tanggal Pengaduan')" class="text-slate-700 font-semibold" />
                                <x-text-input id="tgl_pengaduan" name="tgl_pengaduan" type="date" value="{{ date('Y-m-d') }}" class="mt-1 block w-full rounded-xl border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed shadow-none" readonly />
                            </div>
                            <div>
                                <x-input-label for="whatsapp" :value="__('No. WhatsApp Aktif')" class="text-slate-700 font-semibold" />
                                <x-text-input id="whatsapp" name="whatsapp" type="text" value="{{ old('whatsapp') }}" placeholder="Contoh: 0812..." class="mt-1 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm transition" required />
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label for="alamat" :value="__('Alamat Lengkap')" class="text-slate-700 font-semibold" />
                                <textarea id="alamat" name="alamat" rows="2" class="mt-1 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm transition" placeholder="Tuliskan alamat domisili Anda saat ini..." required>{{ old('alamat') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Gatekeeper: Topik Pertanyaan (FAQ Picker) --}}
                    <div class="mb-8">
                        <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2 flex items-center gap-2">
                            <span class="bg-blue-100 text-blue-700 w-6 h-6 rounded-md flex items-center justify-center text-xs">2</span>
                            Topik Pertanyaan
                        </h3>
                        
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Pilih Topik Terlebih Dahulu</label>
                            <select x-model="topik" @change="applyTemplate()"
                                    class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition shadow-sm">
                                <option value="">— Pilih topik atau isi manual —</option>
                                <optgroup label="Permohonan Paspor">
                                    <option value="paspor_baru_dewasa">Persyaratan paspor baru (dewasa)</option>
                                    <option value="paspor_anak">Persyaratan paspor anak</option>
                                    <option value="paspor_umroh_haji">Paspor untuk umroh / haji</option>
                                    <option value="paspor_cpmi">Paspor untuk bekerja ke luar negeri (CPMI)</option>
                                </optgroup>
                                <optgroup label="Masalah Paspor">
                                    <option value="paspor_rusak">Penggantian paspor rusak</option>
                                    <option value="paspor_hilang">Penggantian paspor hilang</option>
                                </optgroup>
                                <optgroup label="Layanan Lain">
                                    <option value="pengambilan_diwakilkan">Pengambilan paspor diwakilkan</option>
                                    <option value="pembatalan_paspor">Pembatalan permohonan paspor</option>
                                    <option value="kekurangan_berkas">Kekurangan berkas / catatan petugas</option>
                                </optgroup>
                                <option value="lainnya">Lainnya (Aduan Manual)</option>
                            </select>
                        </div>

                        {{-- Kotak Jawaban Otomatis (Muncul jika pilih topik FAQ selain "Lainnya") --}}
                        <div x-show="topik !== '' && topik !== 'lainnya'" x-cloak class="mt-5 transition-all duration-300">
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 shadow-sm">
                                <div class="flex items-center gap-2 mb-3 text-blue-800 font-bold">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Informasi Prosedur:</span>
                                </div>
                                {{-- Area display template --}}
                                <textarea id="aduan_display" rows="10" readonly
                                        class="w-full bg-white/60 border-none text-sm text-slate-700 focus:ring-0 resize-none rounded-lg p-3 font-medium leading-relaxed"
                                        x-model="currentTemplate"></textarea>
                                <div class="mt-4 p-3 bg-amber-50 border border-amber-100 rounded-lg">
                                    <p class="text-xs text-amber-800 leading-relaxed">
                                        <span class="font-bold">Belum menjawab pertanyaan Anda?</span><br>
                                        Silakan ganti pilihan topik ke <span class="font-bold">"Lainnya (Aduan Manual)"</span> untuk mengisi formulir pengaduan secara manual.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Detail Aduan & Tombol Kirim (Hanya muncul jika pilih "Lainnya") --}}
                    <div x-show="topik === 'lainnya'" x-cloak class="transition-all duration-500">
                        <div class="mb-8">
                            <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2 flex items-center gap-2">
                                <span class="bg-blue-100 text-blue-700 w-6 h-6 rounded-md flex items-center justify-center text-xs">3</span>
                                Detail Laporan Manual
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                                <div>
                                    <x-input-label for="jenis_layanan" :value="__('Jenis Layanan')" class="text-slate-700 font-semibold" />
                                    <select x-model="jenis" name="jenis_layanan" class="mt-1 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm transition bg-white" :required="topik === 'lainnya'">
                                        <option value="">-- Pilih Jenis --</option>
                                        <option value="informasi"  {{ old('jenis_layanan') === 'informasi'  ? 'selected' : '' }}>Pemberian Informasi</option>
                                        <option value="penanganan" {{ old('jenis_layanan') === 'penanganan' ? 'selected' : '' }}>Penanganan Pengaduan</option>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="seksi_tujuan" :value="__('Kategori / Seksi Tujuan')" class="text-slate-700 font-semibold" />
                                    <select name="seksi_tujuan" class="mt-1 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm transition bg-white" :required="topik === 'lainnya'">
                                        <option value="">-- Pilih Kategori --</option>
                                        <option value="Tikkim">Pelayanan Paspor (Tikkim)</option>
                                        <option value="Doklanintalkim">[WNI] Dokumen Perjalanan (Doklanintal)</option>
                                        <option value="Doklanintalkim">[WNA] Pelayanan Izin Tinggal (Doklanintal)</option>
                                        <option value="Inteldakim">[WNA] Pengawasan Orang Asing (Inteldak)</option>
                                        <option value="Inteldakim">Alur BAP (Inteldak)</option>
                                        <option value="Tata Usaha">Sarana Prasarana (Tata Usaha)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-5">
                                <x-input-label :value="__('Sumber / Kanal Pengaduan')" class="text-slate-700 font-semibold mb-2" />
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    @foreach(['Ruang Pengaduan', 'WhatsApp', 'Instagram', 'TikTok', 'Facebook', 'Lainnya'] as $k)
                                        <label class="group relative flex items-center cursor-pointer">
                                            <input type="radio" name="kanal" value="{{ $k }}" class="peer sr-only" {{ old('kanal') === $k ? 'checked' : '' }} :required="topik === 'lainnya'">
                                            <div class="w-full flex items-center gap-3 p-3 border border-slate-200 rounded-xl hover:bg-slate-50 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition duration-200">
                                                <div class="w-4 h-4 rounded-full border border-slate-300 peer-checked:border-blue-600 peer-checked:border-[4px] bg-white"></div>
                                                <span class="text-sm text-slate-700 font-medium peer-checked:text-blue-800">{{ $k }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <x-input-label for="aduan" :value="__('Uraian Laporan / Pertanyaan')" class="text-slate-700 font-semibold" />
                                <textarea id="aduan" name="aduan" rows="4" class="mt-1 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm transition" placeholder="Jelaskan secara detail pengaduan Anda..." :required="topik === 'lainnya'">{{ old('aduan') }}</textarea>
                            </div>
                        </div>

                        {{-- 4. Lampiran Section --}}
                        <div class="mb-8">
                            <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2 flex items-center gap-2">
                                <span class="bg-blue-100 text-blue-700 w-6 h-6 rounded-md flex items-center justify-center text-xs">4</span>
                                Dokumen Lampiran
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- KTP --}}
                                <div class="bg-slate-50 border border-slate-200 rounded-xl p-5">
                                    <x-input-label :value="__('Foto KTP (Opsional)')" class="text-slate-800 font-bold" />
                                    <input type="file" name="foto_ktp" accept="image/*" class="mt-2 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-white file:text-blue-700 file:shadow-sm">
                                </div>

                                {{-- Multi Bukti --}}
                                <div x-data="buktiUploader()" class="bg-slate-50 border border-slate-200 rounded-xl p-5">
                                    <x-input-label :value="__('Bukti Pendukung (Opsional)')" class="text-slate-800 font-bold" />
                                    <label class="mt-3 flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer bg-white hover:bg-blue-50 transition-all">
                                        <span class="text-sm text-slate-400">Klik atau drag foto bukti</span>
                                        <input type="file" name="bukti[]" multiple accept="image/*" class="hidden" @change="handleFiles($event)">
                                    </label>
                                    <div x-show="previews.length > 0" class="mt-3 flex gap-2 overflow-x-auto pb-2">
                                        <template x-for="(src, i) in previews" :key="i">
                                            <div class="relative w-16 h-16 rounded-lg overflow-hidden border">
                                                <img :src="src" class="w-full h-full object-cover">
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Submit Action --}}
                        <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                            <p class="text-xs text-slate-500 max-w-sm italic">
                                Pastikan data yang Anda isi sudah benar sebelum menekan tombol kirim.
                            </p>
                            <button type="submit" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 px-8 rounded-xl shadow-lg transition-all active:scale-95 flex items-center justify-center gap-2">
                                <span>Kirim Pengaduan</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- FOOTER NOTE --}}
        <p class="text-center text-slate-400 text-xs mt-8">
            Layanan ini dikelola oleh Kantor Imigrasi · Kerahasiaan data pemohon dijaga sepenuhnya
        </p>

    </div>

</body>
</html>