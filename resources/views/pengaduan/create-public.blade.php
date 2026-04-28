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

                <form action="{{ route('pengaduan.store') }}" method="POST" enctype="multipart/form-data" x-data="{ jenis: '{{ old('jenis_layanan') }}' }">
                    @csrf

                    {{-- Data Diri Section --}}
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

                    {{-- Detail Aduan Section --}}
                    <div class="mb-8">
                        <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2 flex items-center gap-2">
                            <span class="bg-blue-100 text-blue-700 w-6 h-6 rounded-md flex items-center justify-center text-xs">2</span>
                            Detail Laporan
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                            <div>
                                <x-input-label for="jenis_layanan" :value="__('Jenis Layanan')" class="text-slate-700 font-semibold" />
                                <select x-model="jenis" name="jenis_layanan" class="mt-1 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm transition bg-white" required>
                                    <option value="">-- Pilih Jenis --</option>
                                    <option value="informasi"  {{ old('jenis_layanan') === 'informasi'  ? 'selected' : '' }}>Pemberian Informasi</option>
                                    <option value="penanganan" {{ old('jenis_layanan') === 'penanganan' ? 'selected' : '' }}>Penanganan Pengaduan</option>
                                </select>
                                
                                <div class="mt-2 p-3 bg-blue-50 rounded-xl text-xs text-blue-800 border border-blue-100 flex items-start gap-2 transition-all">
                                    <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <template x-if="jenis === 'informasi'">
                                        <span>Layanan bagi pemohon yang membutuhkan data atau kejelasan prosedur keimigrasian.</span>
                                    </template>
                                    <template x-if="jenis === 'penanganan'">
                                        <span>Layanan bagi pemohon yang ingin menyampaikan keluhan atau ketidakpuasan layanan.</span>
                                    </template>
                                    <template x-if="jenis === ''">
                                        <span class="text-slate-500 italic">Silakan pilih jenis layanan terlebih dahulu.</span>
                                    </template>
                                </div>
                            </div>
                            <div>
                                <x-input-label for="seksi_tujuan" :value="__('Kategori / Seksi Tujuan')" class="text-slate-700 font-semibold" />
                                <select name="seksi_tujuan" class="mt-1 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm transition bg-white" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <option value="Tikkim"         {{ old('seksi_tujuan') === 'Tikkim'         ? 'selected' : '' }}>Pelayanan Paspor (Tikkim)</option>
                                    <option value="Doklanintalkim" {{ old('seksi_tujuan') === 'Doklanintalkim' ? 'selected' : '' }}>[WNI] Dokumen Perjalanan (Doklanintal)</option>
                                    <option value="Doklanintalkim" {{ old('seksi_tujuan') === 'Doklanintalkim' ? 'selected' : '' }}>[WNA] Pelayanan Izin Tinggal (Doklanintal)</option>
                                    <option value="Inteldakim"     {{ old('seksi_tujuan') === 'Inteldakim'     ? 'selected' : '' }}>[WNA] Pengawasan Orang Asing (Inteldak)</option>
                                    <option value="Inteldakim"     {{ old('seksi_tujuan') === 'Inteldakim'     ? 'selected' : '' }}>Alur BAP (Inteldak)</option>
                                    <option value="Tata Usaha"     {{ old('seksi_tujuan') === 'Tata Usaha'     ? 'selected' : '' }}>Sarana Prasarana (Tata Usaha)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-5">
                            <x-input-label :value="__('Sumber / Kanal Pengaduan')" class="text-slate-700 font-semibold mb-2" />
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach(['Ruang Pengaduan', 'WhatsApp', 'Instagram', 'TikTok', 'Facebook', 'Lainnya'] as $k)
                                    <label class="group relative flex items-center cursor-pointer">
                                        <input type="radio" name="kanal" value="{{ $k }}" class="peer sr-only" {{ old('kanal') === $k ? 'checked' : '' }} required>
                                        <div class="w-full flex items-center gap-3 p-3 border border-slate-200 rounded-xl hover:bg-slate-50 peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:ring-1 peer-checked:ring-blue-500 transition duration-200">
                                            <div class="w-4 h-4 rounded-full border border-slate-300 peer-checked:border-blue-600 peer-checked:border-[4px] transition-all bg-white"></div>
                                            <span class="text-sm text-slate-700 font-medium peer-checked:text-blue-800">{{ $k }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <x-input-label for="aduan" :value="__('Uraian Laporan / Pertanyaan')" class="text-slate-700 font-semibold" />
                            <textarea id="aduan" name="aduan" rows="4" class="mt-1 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm transition" placeholder="Jelaskan secara detail dan kronologis terkait pengaduan atau informasi yang Anda butuhkan..." required>{{ old('aduan') }}</textarea>
                        </div>
                    </div>

                    {{-- Lampiran Section --}}
                    <div class="mb-8">
                        <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2 flex items-center gap-2">
                            <span class="bg-blue-100 text-blue-700 w-6 h-6 rounded-md flex items-center justify-center text-xs">3</span>
                            Dokumen Lampiran
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- KTP --}}
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-5">
                                <x-input-label :value="__('Foto KTP (Opsional)')" class="text-slate-800 font-bold" />
                                <p class="text-xs text-slate-500 mb-3">Maksimal 10MB (JPG, PNG). Membantu verifikasi identitas.</p>
                                <input type="file" name="foto_ktp" accept="image/*" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-white file:text-blue-700 file:shadow-sm hover:file:bg-blue-50 file:border file:border-slate-200 transition cursor-pointer">
                            </div>

                            {{-- Multi Bukti --}}
                            <div x-data="buktiUploader()" class="bg-slate-50 border border-slate-200 rounded-xl p-5">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <x-input-label :value="__('Bukti Pendukung (Opsional)')" class="text-slate-800 font-bold" />
                                        <p class="text-xs text-slate-500">Maks. 5 foto (10MB/foto).</p>
                                    </div>
                                    <span class="text-xs font-bold text-slate-400 bg-slate-200 px-2 py-1 rounded-md" x-text="files.length + '/5'"></span>
                                </div>

                                {{-- Drop zone --}}
                                <label class="mt-3 flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer bg-white hover:bg-blue-50 hover:border-blue-400 transition-all group"
                                    @dragover.prevent="dragging = true"
                                    @dragleave.prevent="dragging = false"
                                    @drop.prevent="handleDrop($event)"
                                    :class="dragging ? 'border-blue-400 bg-blue-50' : ''">
                                    <div class="flex flex-col items-center gap-1 text-slate-400 group-hover:text-blue-500 transition-colors">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="text-sm font-medium">Klik atau drag foto</span>
                                    </div>
                                    <input type="file" name="bukti[]" multiple accept="image/*" class="hidden" @change="handleFiles($event)">
                                </label>

                                {{-- Preview grid --}}
                                <div x-show="previews.length > 0" x-cloak class="mt-3 flex gap-2 overflow-x-auto custom-scroll pb-2">
                                    <template x-for="(src, i) in previews" :key="i">
                                        <div class="relative group w-16 h-16 flex-shrink-0 rounded-lg overflow-hidden border border-slate-200 bg-slate-100 shadow-sm">
                                            <img :src="src" class="w-full h-full object-cover">
                                            <button type="button" @click="removeFile(i)" class="absolute top-1 right-1 w-5 h-5 rounded-full bg-red-500 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity text-xs font-bold leading-none shadow-sm hover:bg-red-600">
                                                &times;
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Submit Action --}}
                    <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <p class="text-xs text-slate-500 max-w-sm">
                            <span class="font-bold text-slate-700">Pernyataan:</span> Dengan ini saya menyatakan bahwa data yang saya berikan adalah benar dan dapat dipertanggungjawabkan.
                        </p>
                        <button type="submit" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 px-8 rounded-xl shadow-lg shadow-blue-600/20 hover:shadow-blue-600/40 hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2">
                            <span>Kirim Pengaduan</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
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