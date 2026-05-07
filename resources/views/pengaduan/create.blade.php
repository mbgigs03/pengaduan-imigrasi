{{-- resources/views/pengaduan/create.blade.php --}}
<x-layouts.dashboard>
    <x-slot name="header">Input Pengaduan Manual</x-slot>

    <x-slot name="styles">
        <style>
            .step-connector { flex: 1; height: 2px; transition: background .3s; }
            [x-cloak] { display: none !important; }
            .custom-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
            .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        </style>
    </x-slot>

    <div class="p-6 max-w-3xl mx-auto w-full" x-data="pengaduanForm()">

        {{-- ── HEADER INFO ─────────────────────────────────────── --}}
        <div class="bg-blue-50 border border-blue-200 rounded-2xl px-5 py-4 flex items-start gap-3 mb-6">
            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-blue-800">Input Aduan Manual</p>
                <p class="text-xs text-blue-600 mt-0.5">
                    Formulir ini digunakan petugas untuk mencatat aduan dari pemohon (walk-in).
                    Sistem akan otomatis membuatkan tiket pengaduan baru di antrean.
                </p>
            </div>
        </div>

        {{-- Error dari server --}}
        @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-xl flex gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="font-bold text-red-800 text-sm">Gagal menyimpan — periksa isian berikut:</p>
                    <ul class="mt-1 text-xs text-red-700 list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- STEP INDICATOR --}}
            <div class="px-6 pt-6 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-0">
                    <template x-for="(s, i) in steps" :key="i">
                        <div class="flex items-center" :class="i < steps.length - 1 ? 'flex-1' : ''">
                            <button type="button" @click="goToStep(i)" :disabled="!canGoToStep(i)"
                                    class="flex flex-col items-center gap-1 group focus:outline-none"
                                    :class="!canGoToStep(i) ? 'cursor-not-allowed' : 'cursor-pointer'">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 border-2"
                                     :class="{
                                         'bg-blue-600 border-blue-600 text-white shadow-md shadow-blue-200': currentStep === i,
                                         'bg-emerald-500 border-emerald-500 text-white': isStepDone(i),
                                         'bg-white border-slate-300 text-slate-400': !isStepDone(i) && currentStep !== i
                                     }">
                                    <template x-if="isStepDone(i)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </template>
                                    <template x-if="!isStepDone(i)">
                                        <span x-text="i + 1"></span>
                                    </template>
                                </div>
                                <span class="text-[10px] font-semibold hidden sm:block transition-colors"
                                      :class="{'text-blue-600': currentStep === i, 'text-emerald-600': isStepDone(i), 'text-slate-400': !isStepDone(i) && currentStep !== i}"
                                      x-text="s.label"></span>
                            </button>
                            <div x-show="i < steps.length - 1" class="step-connector mx-1 sm:mx-2"
                                 :class="isStepDone(i) ? 'bg-emerald-400' : 'bg-slate-200'"></div>
                        </div>
                    </template>
                </div>
            </div>

            <form action="{{ route('pengaduan.store') }}" method="POST" enctype="multipart/form-data" id="mainForm" @submit="isSubmitting = true">
                @csrf

                {{-- ══ STEP 0: Jenis Layanan + Kategori ══════════════════════ --}}
                <div class="p-6" x-show="currentStep === 0" x-transition>
                    <h2 class="text-base font-bold text-slate-800 mb-1">Jenis Layanan & Kategori</h2>
                    <p class="text-xs text-slate-500 mb-6">Pilih jenis layanan dan kategori tujuan tiket pengaduan.</p>

                    <div class="space-y-5">
                        {{-- Jenis Layanan --}}
                        <div>
                            <label class="text-xs font-bold text-slate-600 uppercase tracking-wide mb-2 block">Jenis Layanan <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="cursor-pointer">
                                    <input type="radio" name="jenis_layanan" value="informasi" x-model="jenis" class="sr-only">
                                    <div class="p-4 border-2 rounded-xl transition-all" :class="jenis === 'informasi' ? 'border-blue-500 bg-blue-50' : 'border-slate-200 hover:border-blue-300 bg-white'">
                                        <div class="flex items-start gap-3">
                                            <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5" :class="jenis === 'informasi' ? 'bg-blue-100' : 'bg-slate-100'">
                                                <svg class="w-5 h-5" :class="jenis === 'informasi' ? 'text-blue-600' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </div>
                                            <div>
                                                <p class="font-bold text-sm" :class="jenis === 'informasi' ? 'text-blue-800' : 'text-slate-700'">Pemberian Informasi</p>
                                                <p class="text-xs text-slate-500 mt-0.5">Pemohon butuh kejelasan prosedur atau data</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>

                                <label class="cursor-pointer">
                                    <input type="radio" name="jenis_layanan" value="penanganan" x-model="jenis" class="sr-only">
                                    <div class="p-4 border-2 rounded-xl transition-all" :class="jenis === 'penanganan' ? 'border-amber-500 bg-amber-50' : 'border-slate-200 hover:border-amber-300 bg-white'">
                                        <div class="flex items-start gap-3">
                                            <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5" :class="jenis === 'penanganan' ? 'bg-amber-100' : 'bg-slate-100'">
                                                <svg class="w-5 h-5" :class="jenis === 'penanganan' ? 'text-amber-600' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            </div>
                                            <div>
                                                <p class="font-bold text-sm" :class="jenis === 'penanganan' ? 'text-amber-800' : 'text-slate-700'">Penanganan Pengaduan</p>
                                                <p class="text-xs text-slate-500 mt-0.5">Keluhan atau ketidakpuasan layanan</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Seksi Tujuan --}}
                        <div>
                            <label class="text-xs font-bold text-slate-600 uppercase tracking-wide mb-2 block">Kategori / Seksi Tujuan <span class="text-red-500">*</span></label>
                            <select name="seksi_tujuan" x-model="seksi" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition" required>
                                <option value="">— Pilih kategori —</option>
                                <option value="Tikkim">Pelayanan Paspor (Tikkim)</option>
                                <option value="Doklanintalkim">[WNI] Dokumen Perjalanan (Doklanintal)</option>
                                <option value="Doklanintalkim">[WNA] Pelayanan Izin Tinggal (Doklanintal)</option>
                                <option value="Inteldakim">[WNA] Pengawasan Orang Asing (Inteldak)</option>
                                <option value="Inteldakim">Alur BAP (Inteldak)</option>
                                <option value="Tata Usaha">Sarana Prasarana (Tata Usaha)</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ══ STEP 1: Informasi Pemohon + Kanal ══════════════════════ --}}
                <div class="p-6" x-show="currentStep === 1" x-transition>
                    <h2 class="text-base font-bold text-slate-800 mb-1">Informasi Pemohon</h2>
                    <p class="text-xs text-slate-500 mb-6">Lengkapi data diri pemohon dan pilih sumber aduan.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-slate-600 uppercase tracking-wide">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input name="nama" type="text" x-model="nama" placeholder="Nama sesuai KTP" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition" required>
                        </div>

                        <div class="flex flex-col gap-1.5" x-show="jenis === 'informasi'" x-transition>
                            <label class="text-xs font-bold text-slate-600 uppercase tracking-wide">NIK (16 Digit) <span class="text-red-500">*</span></label>
                            <input name="nik" type="text" maxlength="16" x-model="nik" placeholder="3501xxxxxxxxxxxxxxx" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 font-mono focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition" :required="jenis === 'informasi'">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-slate-600 uppercase tracking-wide">No. WhatsApp <span class="text-red-500">*</span></label>
                            <input name="whatsapp" type="text" x-model="whatsapp" placeholder="08xxxxxxxxxx" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition" required>
                        </div>

                        <div class="flex flex-col gap-1.5 md:col-span-2">
                            <label class="text-xs font-bold text-slate-600 uppercase tracking-wide">Alamat Lengkap <span class="text-red-500">*</span></label>
                            <textarea name="alamat" rows="2" x-model="alamat" placeholder="Jl. Contoh No. 1..." class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition resize-none" required></textarea>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-600 uppercase tracking-wide mb-2 block">Sumber / Kanal Pengaduan <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach(['Ruang Pengaduan', 'WhatsApp', 'Instagram', 'TikTok', 'Facebook', 'Lainnya'] as $k)
                                <label class="cursor-pointer">
                                    <input type="radio" name="kanal" value="{{ $k }}" x-model="kanal" class="sr-only" required>
                                    <div class="flex items-center gap-2.5 p-3 border-2 rounded-xl transition-all text-sm font-medium" :class="kanal === '{{ $k }}' ? 'border-blue-500 bg-blue-50 text-blue-800' : 'border-slate-200 hover:border-blue-200 text-slate-600 bg-white'">
                                        <div class="w-3.5 h-3.5 rounded-full border-2 flex-shrink-0 transition-all" :class="kanal === '{{ $k }}' ? 'border-blue-500 bg-blue-500' : 'border-slate-300'"></div>
                                        {{ $k }}
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- ══ STEP 2: Topik & Detail Aduan ═══════════════════════════ --}}
                <div class="p-6" x-show="currentStep === 2" x-transition>
                    <template x-if="jenis === 'penanganan'">
                        <div>
                            <h2 class="text-base font-bold text-slate-800 mb-1">Uraian Aduan</h2>
                            <p class="text-xs text-slate-500 mb-6">Catat keluhan pemohon secara detail.</p>
                            <label class="text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5 block">Aduan <span class="text-red-500">*</span></label>
                            <textarea id="aduan_penanganan" rows="6" x-model="aduan" placeholder="Jelaskan secara detail pengaduan..." class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition resize-none" :required="jenis === 'penanganan'"></textarea>
                        </div>
                    </template>

                    <template x-if="jenis === 'informasi'">
                        <div>
                            <h2 class="text-base font-bold text-slate-800 mb-1">Topik Pertanyaan</h2>
                            <p class="text-xs text-slate-500 mb-6">Pilih template jawaban FAQ agar lebih cepat, atau ketik manual.</p>
                            
                            <div class="flex flex-col gap-1.5 mb-4">
                                <label class="text-xs font-bold text-slate-600 uppercase tracking-wide">Topik</label>
                                <select name="topik_faq" x-model="topik" @change="applyTemplate()" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition">
                                    <option value="">— Pilih topik —</option>
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
                            </div>

                            <div x-show="topik !== '' && topik !== 'lainnya'" x-cloak class="mb-4">
                                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                    <p class="text-xs font-bold text-blue-700 uppercase tracking-wide mb-2 flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Template Prosedur</p>
                                    <textarea rows="10" readonly x-model="currentTemplate" class="w-full bg-white/70 border-0 text-sm text-slate-700 focus:ring-0 resize-none rounded-lg p-3"></textarea>
                                </div>
                            </div>

                            <div x-show="topik === 'lainnya'" x-cloak>
                                <label class="text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5 block">Uraian Pertanyaan <span class="text-red-500">*</span></label>
                                <textarea id="aduan_manual" rows="6" x-model="aduan" placeholder="Jelaskan pertanyaan pemohon..." class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition resize-none" :required="topik === 'lainnya'"></textarea>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- ══ STEP 3: Lampiran & Penyelesaian Tiket ══════════════════ --}}
                <div class="p-6" x-show="currentStep === 3" x-transition>
                    <h2 class="text-base font-bold text-slate-800 mb-1">Lampiran & Status Tiket</h2>
                    <p class="text-xs text-slate-500 mb-6">Unggah dokumen jika diperlukan, lalu simpan tiket.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
                        <div x-show="jenis === 'informasi'" x-cloak class="bg-slate-50 border border-slate-200 rounded-xl p-5">
                            <p class="text-xs font-bold text-slate-700 mb-1">Foto KTP</p>
                            <p class="text-xs text-slate-500 mb-3">Wajib untuk verifikasi identitas (10MB, JPG/PNG)</p>
                            <input type="file" name="foto_ktp" accept="image/*" class="block w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                        </div>

                        <div x-data="buktiUploader()" class="bg-slate-50 border border-slate-200 rounded-xl p-5">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="text-xs font-bold text-slate-700">Bukti Foto Tambahan</p>
                                    <p class="text-xs text-slate-500">Maks. 5 foto</p>
                                </div>
                                <span class="text-xs font-bold text-slate-400 bg-slate-200 px-2 py-1 rounded-md" x-text="files.length + '/5'"></span>
                            </div>
                            <label class="mt-2 flex flex-col items-center justify-center w-full h-20 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer bg-white hover:bg-blue-50 hover:border-blue-400 transition-all" @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="handleDrop($event)" :class="dragging ? 'border-blue-400 bg-blue-50' : ''">
                                <span class="text-xs text-slate-400 pointer-events-none">Klik atau drag foto</span>
                                <input type="file" name="bukti[]" multiple accept="image/*" class="hidden" @change="handleFiles($event)">
                            </label>
                            <div x-show="previews.length > 0" class="mt-2 flex gap-2 overflow-x-auto custom-scroll pb-1">
                                <template x-for="(src, i) in previews" :key="i">
                                    <div class="relative group w-14 h-14 flex-shrink-0 rounded-lg overflow-hidden border border-slate-200">
                                        <img :src="src" class="w-full h-full object-cover">
                                        <button type="button" @click="removeFile(i)" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition text-white text-xs">✕</button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- 🟢 FITUR KHUSUS PETUGAS: TANDAI LANGSUNG SELESAI --}}
                    <div class="mb-8 border border-emerald-200 bg-emerald-50 rounded-xl p-4 transition-all hover:bg-emerald-100">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="faq_terjawab" value="1" x-model="faqTerjawab"
                                   class="w-5 h-5 mt-0.5 rounded border-emerald-400 text-emerald-600 focus:ring-emerald-500 shadow-sm cursor-pointer">
                            <div>
                                <p class="text-sm font-bold text-emerald-800">Tandai Langsung Selesai</p>
                                <p class="text-xs text-emerald-700 mt-1 leading-relaxed">
                                    Centang kotak ini jika aduan/informasi sudah dijawab tuntas saat ini juga. Status tiket akan otomatis menjadi <strong>Selesai</strong> dan tidak akan masuk ke dalam antrean merah.
                                </p>
                            </div>
                        </label>
                    </div>

                    <div class="pt-6 border-t border-slate-100 flex justify-end">
                        <button type="submit" :disabled="isSubmitting" :class="isSubmitting ? 'opacity-70 cursor-wait' : 'hover:bg-blue-700 active:scale-95'" class="w-full sm:w-auto bg-blue-600 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition flex items-center justify-center gap-2">
                            <span x-text="isSubmitting ? 'Memproses...' : 'Simpan Pengaduan'"></span>
                            <svg x-show="!isSubmitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- MASTER HIDDEN INPUTS --}}
                <input type="hidden" name="aduan" :value="jenis === 'informasi' && topik !== 'lainnya' && topik !== '' ? currentTemplate : aduan">
                <input type="hidden" name="topik_faq" :value="topik">
            </form>

            {{-- NAVIGATION BUTTONS --}}
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-between items-center">
                <button type="button" @click="prevStep()" x-show="currentStep > 0" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold border border-slate-200 text-slate-600 hover:bg-slate-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg> Kembali
                </button>
                <div x-show="currentStep === 0" class="text-xs text-slate-400">Langkah 1 dari 4</div>
                <button type="button" @click="nextStep()" x-show="currentStep < 3" :disabled="!canProceed()" class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl text-sm font-bold transition" :class="canProceed() ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm' : 'bg-slate-200 text-slate-400 cursor-not-allowed'">
                    Lanjut <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
        const FAQ_TEMPLATES = {
            paspor_baru_dewasa: `Persyaratan permohonan paspor baru (dewasa) dengan membawa dokumen ASLI:\n1. e-KTP\n2. Kartu Keluarga (KK)\n3. Akte Lahir / Buku Nikah / Ijazah SD-SMA\n4. Paspor lama (jika memiliki)\n\nBiaya paspor:\n- Elektronik masa berlaku 5 tahun: Rp 650.000\n- Elektronik masa berlaku 10 tahun: Rp 950.000`,
            paspor_anak: `Persyaratan permohonan paspor anak (belum memiliki e-KTP) dengan dokumen ASLI:\n1. e-KTP kedua orang tua kandung\n2. Kartu Keluarga (KK)\n3. Akta Lahir anak\n4. Buku / Surat Nikah orang tua\n5. Paspor kedua orang tua / Paspor lama anak`,
            paspor_umroh_haji: `Persyaratan paspor untuk umroh / haji dengan dokumen ASLI:\n1. e-KTP\n2. Kartu Keluarga (KK)\n3. Akte Lahir / Buku Nikah / Ijazah SD-SMA\n4. Paspor lama (jika memiliki)\n\nCatatan nama satu kata — wajib tambahan dokumen:\n- Surat rekomendasi dari travel umroh / haji`,
            paspor_cpmi: `Persyaratan paspor untuk bekerja ke luar negeri (CPMI) dengan dokumen ASLI:\n1. e-KTP\n2. Kartu Keluarga (KK)\n3. Akta Lahir / Ijazah SD-SMA / Buku Nikah\n4. Paspor lama (jika memiliki)`,
            paspor_rusak: `Prosedur penggantian paspor RUSAK:\nDatang langsung ke Kantor Imigrasi TANPA mendaftar M-Paspor untuk proses BAP.\nBiaya: Denda Rp 500.000 + biaya paspor.`,
            paspor_hilang: `Prosedur penggantian paspor HILANG:\n1. Urus Surat Keterangan Kehilangan di kantor kepolisian terdekat\n2. Datang ke Kantor Imigrasi mulai pukul 08.00 WIB TANPA daftar M-Paspor untuk BAP.\nBiaya: Denda Rp 1.000.000 + biaya paspor.`,
            pengambilan_diwakilkan: `Pengambilan paspor DIWAKILKAN:\n\nA. Beda KK: Surat kuasa bermaterai Rp 10.000, e-KTP asli pengambil, Fotokopi e-KTP pemilik, Struk.\nB. Satu KK: KK asli, e-KTP asli pengambil, Struk.`,
            pembatalan_paspor: `Permohonan pembatalan paspor.\nMohon lengkapi data berikut:\n- Nama Lengkap Pemohon :\n- Nomor WhatsApp :\n- Alasan Pembatalan :`,
            kekurangan_berkas: `Perihal kekurangan berkas / catatan dari petugas.\nMohon informasikan:\n- Nama lengkap pemohon :\n- Tanggal kunjungan :\n- Berkas yang kurang :`,
            lainnya: '',
        };

        function pengaduanForm() {
            return {
                currentStep: 0, isSubmitting: false,
                steps: [{ label: 'Layanan' }, { label: 'Pemohon' }, { label: 'Topik' }, { label: 'Lampiran' }],
                jenis: @json(old('jenis_layanan', '')), seksi: @json(old('seksi_tujuan', '')),
                nama: @json(old('nama', '')), nik: @json(old('nik', '')),
                whatsapp: @json(old('whatsapp', '')), alamat: @json(old('alamat', '')),
                kanal: @json(old('kanal', '')), aduan: @json(old('aduan', '')),
                topik: '', currentTemplate: '', faqTerjawab: false,

                applyTemplate() { this.currentTemplate = FAQ_TEMPLATES[this.topik] ?? ''; },
                goToStep(i) { if (this.canGoToStep(i)) this.currentStep = i; },
                nextStep() { if (this.canProceed() && this.currentStep < 3) this.currentStep++; },
                prevStep() { if (this.currentStep > 0) this.currentStep--; },
                isStep0Valid() { return this.jenis !== '' && this.seksi !== ''; },
                isStep1Valid() {
                    const base = this.nama.trim() !== '' && this.whatsapp.trim() !== '' && this.alamat.trim() !== '' && this.kanal !== '';
                    return this.jenis === 'informasi' ? base && this.nik.trim() !== '' : base;
                },
                isStep2Valid() {
                    if (this.jenis === 'penanganan') return this.aduan.trim() !== '';
                    if (this.jenis === 'informasi') {
                        if (this.topik === '') return false;
                        if (this.topik === 'lainnya') return this.aduan.trim() !== '';
                        return true;
                    } return false;
                },
                isStepDone(i) {
                    if (i === 0) return this.isStep0Valid();
                    if (i === 1) return this.isStep1Valid();
                    if (i === 2) return this.isStep2Valid();
                    return false;
                },
                canGoToStep(i) {
                    if (i === 0) return true;
                    if (i === 1) return this.isStep0Valid();
                    if (i === 2) return this.isStep0Valid() && this.isStep1Valid();
                    if (i === 3) return this.isStep0Valid() && this.isStep1Valid() && this.isStep2Valid();
                    return false;
                },
                canProceed() {
                    if (this.currentStep === 0) return this.isStep0Valid();
                    if (this.currentStep === 1) return this.isStep1Valid();
                    if (this.currentStep === 2) return this.isStep2Valid();
                    return true;
                }
            };
        }

        function buktiUploader() {
            return {
                previews: [], files: [], dragging: false,
                handleFiles(e) { this.addFiles(Array.from(e.target.files)); },
                handleDrop(e) { this.dragging = false; this.addFiles(Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'))); },
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
                removeFile(i) { this.files.splice(i, 1); this.previews.splice(i, 1); this.syncInput(); },
                syncInput() {
                    const dt = new DataTransfer();
                    this.files.forEach(f => dt.items.add(f));
                    this.$el.querySelector('input[type=file][multiple]').files = dt.files;
                }
            };
        }
        </script>
    </x-slot>
</x-layouts.dashboard>