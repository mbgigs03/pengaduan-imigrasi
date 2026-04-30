{{-- resources/views/pengaduan/create.blade.php --}}
{{-- Untuk petugas (auth) — layout dashboard --}}
<x-layouts.dashboard>
    <x-slot name="header">Form Pengaduan</x-slot>

    <div class="p-6 max-w-[860px] space-y-5">

        {{-- ── HEADER INFO ─────────────────────────────────────── --}}
        <div class="bg-blue-50 border border-blue-200 rounded-[14px] px-5 py-4 flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-blue-800">Input Aduan Manual</p>
                <p class="text-xs text-blue-600 mt-0.5">
                    Formulir ini digunakan petugas untuk mencatat aduan dari pemohon yang datang langsung
                    (walk-in) atau melalui saluran lain. Data akan masuk ke sistem dan tiket otomatis dibuat.
                </p>
            </div>
        </div>

        {{-- ── FORM ────────────────────────────────────────────── --}}
        <form action="{{ route('pengaduan.store') }}" method="POST" enctype="multipart/form-data"
              x-data="{ jenis: '{{ old('jenis_layanan') }}', ...faqPicker() }">
            @csrf

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-[14px] px-5 py-4 flex items-start gap-3">
                    <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-red-700 mb-1">Gagal mengirim — periksa isian berikut:</p>
                        <ul class="text-xs text-red-600 space-y-0.5 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- ── SECTION 1: Data Pemohon ──────────────────────── --}}
            <div class="bg-white rounded-[14px] border border-slate-100 overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Data Pemohon</p>
                </div>
                <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">

                    {{-- Nama --}}
                    <div class="flex flex-col gap-1.5">
                        <label for="nama" class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                            Nama Lengkap <span class="text-red-400">*</span>
                        </label>
                        <input id="nama" name="nama" type="text"
                               value="{{ old('nama') }}"
                               placeholder="Nama sesuai KTP"
                               class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50
                                      focus:outline-none focus:border-blue-400 focus:bg-white focus:ring-2
                                      focus:ring-blue-100 transition placeholder-slate-300
                                      @error('nama') border-red-300 bg-red-50 @enderror"
                               required>
                        @error('nama')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- NIK --}}
                    <div class="flex flex-col gap-1.5">
                        <label for="nik" class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                            NIK (16 Digit) <span class="text-red-400">*</span>
                        </label>
                        <input id="nik" name="nik" type="text" maxlength="16"
                               value="{{ old('nik') }}"
                               placeholder="3501xxxxxxxxxxxxxxx"
                               class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50
                                      focus:outline-none focus:border-blue-400 focus:bg-white focus:ring-2
                                      focus:ring-blue-100 transition font-mono placeholder-slate-300
                                      @error('nik') border-red-300 bg-red-50 @enderror"
                               required>
                        @error('nik')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- WhatsApp --}}
                    <div class="flex flex-col gap-1.5">
                        <label for="whatsapp" class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                            No. WhatsApp Aktif <span class="text-red-400">*</span>
                        </label>
                        <input id="whatsapp" name="whatsapp" type="text"
                               value="{{ old('whatsapp') }}"
                               placeholder="08xxxxxxxxxx"
                               class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50
                                      focus:outline-none focus:border-blue-400 focus:bg-white focus:ring-2
                                      focus:ring-blue-100 transition placeholder-slate-300
                                      @error('whatsapp') border-red-300 bg-red-50 @enderror"
                               required>
                        @error('whatsapp')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal (readonly) --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                            Tanggal Pengaduan
                        </label>
                        <input type="date" name="tgl_pengaduan" value="{{ date('Y-m-d') }}" readonly
                               class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl
                                      bg-slate-100 text-slate-500 cursor-not-allowed">
                        <p class="text-[10px] text-slate-400">Otomatis diisi hari ini</p>
                    </div>

                    {{-- Alamat (full width) --}}
                    <div class="flex flex-col gap-1.5 md:col-span-2">
                        <label for="alamat" class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                            Alamat Lengkap
                        </label>
                        <textarea id="alamat" name="alamat" rows="2"
                                  placeholder="Jl. Contoh No. 1, Kelurahan, Kecamatan, Kota"
                                  class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50
                                         focus:outline-none focus:border-blue-400 focus:bg-white focus:ring-2
                                         focus:ring-blue-100 transition placeholder-slate-300 resize-none">{{ old('alamat') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ── SECTION 2: Detail Aduan ──────────────────────── --}}
            <div class="bg-white rounded-[14px] border border-slate-100 overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Detail Aduan</p>
                </div>
                <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">

                    {{-- Jenis Layanan --}}
                    <div class="flex flex-col gap-1.5">
                        <label for="jenis_layanan" class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                            Jenis Layanan <span class="text-red-400">*</span>
                        </label>
                        <select id="jenis_layanan" name="jenis_layanan" x-model="jenis"
                                class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50
                                       focus:outline-none focus:border-blue-400 focus:bg-white focus:ring-2
                                       focus:ring-blue-100 transition @error('jenis_layanan') border-red-300 @enderror"
                                required>
                            <option value="">— Pilih Jenis —</option>
                            <option value="informasi"  {{ old('jenis_layanan') === 'informasi'  ? 'selected' : '' }}>Pemberian Informasi</option>
                            <option value="penanganan" {{ old('jenis_layanan') === 'penanganan' ? 'selected' : '' }}>Penanganan Pengaduan</option>
                        </select>
                        <div x-show="jenis !== ''"
                             class="px-3 py-2 rounded-lg text-xs border"
                             :class="jenis === 'informasi'
                                 ? 'bg-blue-50 border-blue-200 text-blue-700'
                                 : 'bg-amber-50 border-amber-200 text-amber-700'">
                            <template x-if="jenis === 'informasi'">
                                <span>Pemohon membutuhkan data atau kejelasan prosedur keimigrasian.</span>
                            </template>
                            <template x-if="jenis === 'penanganan'">
                                <span>Pemohon menyampaikan keluhan atau ketidakpuasan terhadap layanan.</span>
                            </template>
                        </div>
                    </div>

                    {{-- Seksi Tujuan --}}
                    <div class="flex flex-col gap-1.5">
                        <label for="seksi_tujuan" class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                            Kategori / Seksi Tujuan <span class="text-red-400">*</span>
                        </label>
                        <select id="seksi_tujuan" name="seksi_tujuan"
                                class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50
                                       focus:outline-none focus:border-blue-400 focus:bg-white focus:ring-2
                                       focus:ring-blue-100 transition @error('seksi_tujuan') border-red-300 @enderror"
                                required>
                            <option value="">— Pilih Seksi —</option>
                            <option value="Tikkim"         {{ old('seksi_tujuan') === 'Tikkim'         ? 'selected' : '' }}>Pelayanan Paspor (Tikkim)</option>
                            <option value="Doklanintalkim" {{ old('seksi_tujuan') === 'Doklanintalkim' ? 'selected' : '' }}>[WNI] Dokumen Perjalanan (Doklanintal)</option>
                            <option value="Doklanintalkim" {{ old('seksi_tujuan') === 'Doklanintalkim' ? 'selected' : '' }}>[WNA] Pelayanan Izin Tinggal (Doklanintal)</option>
                            <option value="Inteldakim"     {{ old('seksi_tujuan') === 'Inteldakim'     ? 'selected' : '' }}>[WNA] Pengawasan Orang Asing (Inteldak)</option>
                            <option value="Inteldakim"     {{ old('seksi_tujuan') === 'Inteldakim'     ? 'selected' : '' }}>Alur BAP (Inteldak)</option>
                            <option value="Tata Usaha"     {{ old('seksi_tujuan') === 'Tata Usaha'     ? 'selected' : '' }}>Sarana Prasarana (Tata Usaha)</option>
                        </select>
                    </div>

                    {{-- Kanal Pengaduan (full width) --}}
                    <div class="flex flex-col gap-1.5 md:col-span-2">
                        <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                            Kanal Pengaduan <span class="text-red-400">*</span>
                        </label>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['Ruang Pengaduan', 'WhatsApp', 'Instagram', 'TikTok', 'Facebook', 'Lainnya'] as $k)
                                <label class="relative">
                                    <input type="radio" name="kanal" value="{{ $k }}"
                                           class="peer sr-only"
                                           {{ old('kanal') === $k ? 'checked' : '' }}
                                           required>
                                    <span class="inline-flex items-center px-3.5 py-2 rounded-xl text-sm font-medium
                                                 border border-slate-200 text-slate-500 bg-slate-50 cursor-pointer
                                                 hover:border-blue-300 hover:text-blue-600 hover:bg-blue-50
                                                 peer-checked:border-blue-500 peer-checked:text-blue-700
                                                 peer-checked:bg-blue-50 peer-checked:font-semibold transition">
                                        {{ $k }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Topik FAQ --}}
                    <div class="flex flex-col gap-1.5 md:col-span-2">
                        <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                            Topik Pertanyaan
                            <span class="font-normal text-slate-400 normal-case">(opsional)</span>
                        </label>
                        <select x-model="topik" @change="applyTemplate()"
                                class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50
                                    focus:outline-none focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100 transition">
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
                            <option value="lainnya">Lainnya (isi manual)</option>
                        </select>
                        <div x-show="topik !== '' && topik !== 'lainnya'"
                            class="flex items-center gap-2 text-xs text-blue-700 bg-blue-50 border border-blue-200 rounded-lg px-3 py-2">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Template otomatis diisi. Anda tetap bisa mengedit sesuai kebutuhan.</span>
                        </div>
                    </div>

                    {{-- Isi Aduan (full width) --}}
                    <div class="flex flex-col gap-1.5 md:col-span-2">
                        <label for="aduan" class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                            Isi Aduan / Pertanyaan <span class="text-red-400">*</span>
                        </label>
                        <textarea id="aduan" name="aduan" rows="5"
                                  placeholder="Jelaskan secara detail pengaduan atau informasi yang dibutuhkan pemohon..."
                                  class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50
                                         focus:outline-none focus:border-blue-400 focus:bg-white focus:ring-2
                                         focus:ring-blue-100 transition placeholder-slate-300 resize-none
                                         @error('aduan') border-red-300 bg-red-50 @enderror"
                                  required>{{ old('aduan') }}</textarea>
                        @error('aduan')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ── SECTION KTP ─────────────────────────────────── --}}
            <div class="bg-white rounded-[14px] border border-slate-100 overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Foto KTP</p>
                    <span class="text-[10px] text-slate-400">Opsional · JPG/PNG · 10MB</span>
                </div>
                <div class="p-5">
                    <input type="file" name="foto_ktp" accept="image/*"
                        class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 bg-slate-50
                                file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                                file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700
                                hover:file:bg-blue-100 transition cursor-pointer">
                    <p class="text-[10px] text-slate-400 mt-1.5">Foto KTP pemohon sebagai verifikasi identitas.</p>
                </div>
            </div>

            {{-- ── SECTION 3: Bukti Foto ────────────────────────── --}}
            <div class="bg-white rounded-[14px] border border-slate-100 overflow-hidden"
                 x-data="buktiUploader()">
                <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Bukti Foto</p>
                    <span class="text-[10px] text-slate-400">Opsional · maks 5 foto · JPG/PNG · 10MB/foto</span>
                </div>
                <div class="p-5">
                    <label
                        class="flex flex-col items-center justify-center gap-2 w-full h-28
                               border-2 border-dashed border-slate-200 rounded-xl cursor-pointer
                               hover:border-blue-400 hover:bg-blue-50/30 transition"
                        @dragover.prevent="dragging = true"
                        @dragleave.prevent="dragging = false"
                        @drop.prevent="handleDrop($event)"
                        :class="dragging ? 'border-blue-400 bg-blue-50/30' : ''">
                        <div class="flex flex-col items-center gap-1 pointer-events-none">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586
                                         a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6
                                         a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-xs text-slate-400 font-medium">Klik atau drag foto ke sini</span>
                        </div>
                        <input type="file" name="bukti[]" multiple accept="image/*"
                               class="hidden" @change="handleFiles($event)">
                    </label>

                    <div x-show="previews.length > 0" class="mt-3 grid grid-cols-5 gap-2">
                        <template x-for="(src, i) in previews" :key="i">
                            <div class="relative group aspect-square rounded-xl overflow-hidden border border-slate-100 bg-slate-50">
                                <img :src="src" class="w-full h-full object-cover">
                                <button type="button" @click="removeFile(i)"
                                        class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100
                                               flex items-center justify-center transition">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                                <span class="absolute bottom-1 right-1 text-[9px] bg-black/50 text-white rounded px-1 font-mono"
                                      x-text="'F'+(i+1)"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- ── SUBMIT ───────────────────────────────────────── --}}
            <div class="flex items-center justify-between">
                <a href="{{ url()->previous() }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold
                          border border-slate-200 text-slate-500 hover:bg-slate-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Batal
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold
                               bg-blue-600 text-white hover:bg-blue-700 active:scale-95 transition shadow-sm shadow-blue-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Kirim & Simpan Aduan
                </button>
            </div>
        </form>
    </div>

    <x-slot name="scripts">
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
                        this.addFiles(Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/')));
                    },
                    addFiles(newFiles) {
                        const remaining = 5 - this.files.length;
                        newFiles.slice(0, remaining).forEach(file => {
                            this.files.push(file);
                            const reader = new FileReader();
                            reader.onload = e => this.previews.push(e.target.result);
                            reader.readAsDataURL(file);
                        });
                        this.$nextTick(() => this.syncInput());
                    },
                    removeFile(i) {
                        this.files.splice(i, 1);
                        this.previews.splice(i, 1);
                        this.syncInput();
                    },
                    syncInput() {
                        const input = this.$el.querySelector('input[type=file]');
                        const dt = new DataTransfer();
                        this.files.forEach(f => dt.items.add(f));
                        input.files = dt.files;
                    },
                }
            }

            function faqPicker() {
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
                                applyTemplate() {
                                    const textarea = document.getElementById('aduan');
                                    if (!textarea) return;
                                    textarea.value = templates[this.topik] ?? '';
                                    // Trigger event supaya Alpine/Livewire/dsb ikut update jika perlu
                                    textarea.dispatchEvent(new Event('input'));
                                }
                            };
                }
            }
        </script>
    </x-slot>

</x-layouts.dashboard>