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
              x-data="{ jenis: '{{ old('jenis_layanan') }}' }">
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
    </script>
    </x-slot>

</x-layouts.dashboard>