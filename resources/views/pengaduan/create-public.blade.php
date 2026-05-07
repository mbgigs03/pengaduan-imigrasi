<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form Pengaduan - Kantor Imigrasi Madiun</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap');
        * { font-family: 'Plus Jakarta Sans', sans-serif; }

        .hero-bg {
            background: linear-gradient(135deg, #0f3460 0%, #1a5276 40%, #1f618d 70%, #2874a6 100%);
            position: relative; overflow: hidden;
        }
        .hero-bg::before {
            content: ''; position: absolute; inset: 0;
            background-image: radial-gradient(circle at 20% 50%, rgba(255,255,255,.05) 0%, transparent 60%),
                              radial-gradient(circle at 80% 20%, rgba(255,255,255,.04) 0%, transparent 50%);
        }
        .hero-bg::after {
            content: ''; position: absolute; bottom: -1px; left: 0; right: 0; height: 40px;
            background: #f1f5f9; clip-path: ellipse(60% 100% at 50% 100%);
        }

        /* Step indicator */
        .step-connector { flex: 1; height: 2px; transition: background .3s; }

        /* Card transition */
        .step-panel { transition: opacity .25s, transform .25s; }
        .step-panel.hidden { display: none; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fadeUp 0.4s ease both; }

        .custom-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="bg-slate-100 min-h-screen flex flex-col">

    {{-- NAVBAR --}}
    <nav class="bg-white/90 backdrop-blur-md border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-3xl mx-auto px-4 h-14 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-700 flex items-center justify-center shadow-sm">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"/>
                    </svg>
                </div>
                <span class="font-bold text-slate-800 text-sm">Imigrasi Madiun</span>
                <span class="text-slate-300 hidden sm:inline">|</span>
                <span class="text-slate-500 text-xs hidden sm:inline">Formulir Pengaduan</span>
            </div>
            <a href="{{ route('pengaduan.landing') }}"
               class="text-sm font-semibold text-slate-600 hover:text-blue-700 bg-slate-50 hover:bg-blue-50
                      border border-slate-200 px-3 py-1.5 rounded-lg transition inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
        </div>
    </nav>

    {{-- HERO --}}
    <div class="hero-bg pt-10 pb-20 px-4">
        <div class="max-w-3xl mx-auto text-center relative z-10 fade-up">
            <h1 class="text-2xl md:text-3xl font-extrabold text-white mb-2 tracking-tight">Formulir Pengaduan Layanan</h1>
            <p class="text-blue-200 text-sm">Ikuti langkah-langkah di bawah ini untuk menyampaikan aduan atau permintaan informasi.</p>
        </div>
    </div>

    {{-- MAIN --}}
    <div class="max-w-3xl w-full mx-auto px-4 -mt-10 relative z-10 pb-16 flex-grow fade-up"
         x-data="pengaduanForm()">

        {{-- Error dari server --}}
        @if ($errors->any())
            <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-xl flex gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="font-bold text-red-800 text-sm">Gagal mengirim — periksa isian berikut:</p>
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
                            {{-- Dot --}}
                            <button type="button"
                                    @click="goToStep(i)"
                                    :disabled="!canGoToStep(i)"
                                    class="flex flex-col items-center gap-1 group focus:outline-none"
                                    :class="!canGoToStep(i) ? 'cursor-not-allowed' : 'cursor-pointer'">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 border-2"
                                     :class="{
                                         'bg-blue-600 border-blue-600 text-white shadow-md shadow-blue-200': currentStep === i,
                                         'bg-emerald-500 border-emerald-500 text-white': isStepDone(i),
                                         'bg-white border-slate-300 text-slate-400': !isStepDone(i) && currentStep !== i
                                     }">
                                    <template x-if="isStepDone(i)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </template>
                                    <template x-if="!isStepDone(i)">
                                        <span x-text="i + 1"></span>
                                    </template>
                                </div>
                                <span class="text-[10px] font-semibold hidden sm:block transition-colors"
                                      :class="{
                                          'text-blue-600': currentStep === i,
                                          'text-emerald-600': isStepDone(i),
                                          'text-slate-400': !isStepDone(i) && currentStep !== i
                                      }"
                                      x-text="s.label"></span>
                            </button>
                            {{-- Connector --}}
                            <div x-show="i < steps.length - 1"
                                 class="step-connector mx-1 sm:mx-2"
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
                    <p class="text-xs text-slate-500 mb-6">Pilih jenis layanan dan kategori tujuan pengaduan Anda.</p>

                    <div class="space-y-5">
                        {{-- Jenis Layanan --}}
                        <div>
                            <label class="text-xs font-bold text-slate-600 uppercase tracking-wide mb-2 block">
                                Jenis Layanan <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="cursor-pointer">
                                    <input type="radio" name="jenis_layanan" value="informasi"
                                           x-model="jenis" class="sr-only"
                                           {{ old('jenis_layanan') === 'informasi' ? 'checked' : '' }}>
                                    <div class="p-4 border-2 rounded-xl transition-all"
                                         :class="jenis === 'informasi'
                                             ? 'border-blue-500 bg-blue-50'
                                             : 'border-slate-200 hover:border-blue-300 bg-white'">
                                        <div class="flex items-start gap-3">
                                            <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5"
                                                 :class="jenis === 'informasi' ? 'bg-blue-100' : 'bg-slate-100'">
                                                <svg class="w-5 h-5" :class="jenis === 'informasi' ? 'text-blue-600' : 'text-slate-400'"
                                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-bold text-sm" :class="jenis === 'informasi' ? 'text-blue-800' : 'text-slate-700'">
                                                    Pemberian Informasi
                                                </p>
                                                <p class="text-xs text-slate-500 mt-0.5">Butuh kejelasan prosedur atau data layanan imigrasi</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>

                                <label class="cursor-pointer">
                                    <input type="radio" name="jenis_layanan" value="penanganan"
                                           x-model="jenis" class="sr-only"
                                           {{ old('jenis_layanan') === 'penanganan' ? 'checked' : '' }}>
                                    <div class="p-4 border-2 rounded-xl transition-all"
                                         :class="jenis === 'penanganan'
                                             ? 'border-amber-500 bg-amber-50'
                                             : 'border-slate-200 hover:border-amber-300 bg-white'">
                                        <div class="flex items-start gap-3">
                                            <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5"
                                                 :class="jenis === 'penanganan' ? 'bg-amber-100' : 'bg-slate-100'">
                                                <svg class="w-5 h-5" :class="jenis === 'penanganan' ? 'text-amber-600' : 'text-slate-400'"
                                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-bold text-sm" :class="jenis === 'penanganan' ? 'text-amber-800' : 'text-slate-700'">
                                                    Penanganan Pengaduan
                                                </p>
                                                <p class="text-xs text-slate-500 mt-0.5">Keluhan atau ketidakpuasan terhadap layanan</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Seksi Tujuan --}}
                        <div>
                            <label class="text-xs font-bold text-slate-600 uppercase tracking-wide mb-2 block">
                                Kategori / Seksi Tujuan <span class="text-red-500">*</span>
                            </label>
                            <select name="seksi_tujuan" x-model="seksi"
                                    class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50
                                           focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition"
                                    required>
                                <option value="">— Pilih kategori —</option>
                                <option value="Tikkim">Pelayanan Paspor</option>
                                <option value="Doklan_Paspor">Dokumen Perjalanan</option>
                                <option value="Doklan_Izin">Pelayanan Izin Tinggal [WNA]</option>
                                <option value="Intel_WNA">Pengawasan Orang Asing [WNA]</option>
                                <option value="Intel_BAP">Alur BAP</option>
                                <option value="Tata Usaha">Sarana Prasarana</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ══ STEP 1: Informasi Pemohon + Kanal ══════════════════════ --}}
                <div class="p-6" x-show="currentStep === 1" x-transition>
                    <h2 class="text-base font-bold text-slate-800 mb-1">Informasi Pemohon</h2>
                    <p class="text-xs text-slate-500 mb-6">Lengkapi data diri dan pilih kanal pengaduan.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                        {{-- Nama --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-slate-600 uppercase tracking-wide">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input name="nama" type="text" value="{{ old('nama') }}"
                                   placeholder="Nama sesuai KTP"
                                   x-model="nama"
                                   class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50
                                          focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition
                                          placeholder-slate-300 @error('nama') border-red-300 @enderror"
                                   required>
                            @error('nama') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- NIK — hanya muncul jika informasi --}}
                        <div class="flex flex-col gap-1.5" x-show="jenis === 'informasi'" x-transition>
                            <label class="text-xs font-bold text-slate-600 uppercase tracking-wide">
                                NIK (16 Digit) <span class="text-red-500">*</span>
                            </label>
                            <input name="nik" type="text" maxlength="16" value="{{ old('nik') }}"
                                   placeholder="3501xxxxxxxxxxxxxxx"
                                   x-model="nik"
                                   :required="jenis === 'informasi'"
                                   class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50
                                          focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition
                                          font-mono placeholder-slate-300 @error('nik') border-red-300 @enderror">
                            @error('nik') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Whatsapp --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-slate-600 uppercase tracking-wide">
                                No. WhatsApp <span class="text-red-500">*</span>
                            </label>
                            <input name="whatsapp" type="text" value="{{ old('whatsapp') }}"
                                   placeholder="08xxxxxxxxxx"
                                   x-model="whatsapp"
                                   class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50
                                          focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition
                                          placeholder-slate-300 @error('whatsapp') border-red-300 @enderror"
                                   required>
                            @error('whatsapp') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Tanggal --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-slate-600 uppercase tracking-wide">Tanggal</label>
                            <input type="date" name="tgl_pengaduan" value="{{ date('Y-m-d') }}" readonly
                                   class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-100 text-slate-500 cursor-not-allowed">
                            <p class="text-[10px] text-slate-400">Otomatis hari ini</p>
                        </div>

                        {{-- Alamat --}}
                        <div class="flex flex-col gap-1.5 md:col-span-2">
                            <label class="text-xs font-bold text-slate-600 uppercase tracking-wide">
                                Alamat Lengkap <span class="text-red-500">*</span>
                            </label>
                            <textarea name="alamat" rows="2"
                                      placeholder="Jl. Contoh No. 1, Kelurahan, Kecamatan, Kota"
                                      x-model="alamat"
                                      class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50
                                             focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition
                                             placeholder-slate-300 resize-none"
                                      required>{{ old('alamat') }}</textarea>
                        </div>
                    </div>

                    {{-- Kanal --}}
                    <div>
                        <label class="text-xs font-bold text-slate-600 uppercase tracking-wide mb-2 block">
                            Sumber / Kanal Pengaduan <span class="text-red-500">*</span>
                        </label>
                        
                        {{-- Jika parameter ?kanal= ada di URL (Mode Terkunci/Otomatis) --}}
                        <template x-if="isKanalLocked">
                            <div class="px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl flex items-center justify-between">
                                <div class="flex items-center gap-2.5 text-blue-800 font-bold text-sm">
                                    <div class="w-2 h-2 rounded-full bg-blue-600"></div>
                                    <span x-text="kanal"></span>
                                </div>
                                <span class="text-[10px] bg-blue-200 text-blue-800 px-2 py-0.5 rounded-full font-bold tracking-wide">OTOMATIS</span>
                                {{-- Hidden input agar tetap terkirim ke server --}}
                                <input type="hidden" name="kanal" :value="kanal">
                            </div>
                        </template>

                        {{-- Jika user membuka web tanpa link parameter (Pilih Manual) --}}
                        <template x-if="!isKanalLocked">
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                @foreach(['Ruang Pengaduan', 'WhatsApp', 'Instagram', 'TikTok', 'Facebook', 'Lainnya'] as $k)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="kanal" value="{{ $k }}"
                                               x-model="kanal" class="sr-only"
                                               :required="!isKanalLocked">
                                        <div class="flex items-center gap-2.5 p-3 border-2 rounded-xl transition-all text-sm font-medium"
                                             :class="kanal === '{{ $k }}'
                                                 ? 'border-blue-500 bg-blue-50 text-blue-800'
                                                 : 'border-slate-200 hover:border-blue-200 text-slate-600 bg-white'">
                                            <div class="w-3.5 h-3.5 rounded-full border-2 flex-shrink-0 transition-all"
                                                 :class="kanal === '{{ $k }}'
                                                     ? 'border-blue-500 bg-blue-500'
                                                     : 'border-slate-300'"></div>
                                            {{ $k }}
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </template>
                    </div>
                </div>

                {{-- ══ STEP 2: Topik Pertanyaan / Uraian Aduan ��═══════════════════ --}}
                <div class="p-6" x-show="currentStep === 2" x-transition>

                    {{-- UNTUK PENANGANAN PENGADUAN: langsung uraian aduan --}}
                    <template x-if="jenis === 'penanganan'">
                        <div>
                            <h2 class="text-base font-bold text-slate-800 mb-1">Uraian Aduan</h2>
                            <p class="text-xs text-slate-500 mb-6">Jelaskan secara detail pengaduan atau keluhan Anda.</p>
                            
                            <div>
                                <label class="text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5 block">
                                    Aduan <span class="text-red-500">*</span>
                                </label>
                                <textarea id="aduan_penanganan" rows="6"
                                          x-model="aduan"
                                          placeholder="Jelaskan secara detail pengaduan atau keluhan Anda..."
                                          class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50
                                                 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition
                                                 placeholder-slate-300 resize-none @error('aduan') border-red-300 @enderror"
                                          :required="jenis === 'penanganan'"></textarea>
                                @error('aduan') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </template>

                    {{-- UNTUK PEMBERIAN INFORMASI: topik pilihan --}}
                    <template x-if="jenis === 'informasi'">
                        <div>
                            <h2 class="text-base font-bold text-slate-800 mb-1">Topik Pertanyaan</h2>
                            <p class="text-xs text-slate-500 mb-6">
                                Pilih topik yang sesuai — jawaban akan terisi otomatis.
                                Pilih <strong>"Lainnya"</strong> untuk mengisi pertanyaan secara manual.
                            </p>

                            <div class="flex flex-col gap-1.5 mb-4">
                                <label class="text-xs font-bold text-slate-600 uppercase tracking-wide">Topik</label>
                                <select x-model="topik" @change="applyTemplate()"
                                        class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50
                                               focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition">
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

                            {{-- Template answer (readonly) --}}
                            <div x-show="topik !== '' && topik !== 'lainnya'" x-cloak class="mb-4">
                                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                    <p class="text-xs font-bold text-blue-700 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Informasi Prosedur
                                    </p>
                                    <textarea rows="10" readonly x-model="currentTemplate"
                                              class="w-full bg-white/70 border-0 text-sm text-slate-700 focus:ring-0 resize-none rounded-lg p-3 leading-relaxed"></textarea>
                                </div>
                            </div>

                            {{-- Manual textarea --}}
                            <div x-show="topik === 'lainnya'" x-cloak>
                                <label class="text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5 block">
                                    Uraian Pertanyaan <span class="text-red-500">*</span>
                                </label>
                                <textarea id="aduan_manual" rows="6"
                                          x-model="aduan"
                                          placeholder="Jelaskan secara detail pertanyaan Anda..."
                                          class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50
                                                 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition
                                                 placeholder-slate-300 resize-none @error('aduan') border-red-300 @enderror"
                                          :required="topik === 'lainnya'"></textarea>
                                @error('aduan') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </template>
                </div>

                {{-- ══ STEP 3: Dokumen Lampiran / Konfirmasi ════════════════════ --}}
                <div class="p-6" x-show="currentStep === 3" x-transition>
                    
                    {{-- UNTUK PEMBERIAN INFORMASI dengan topik pilihan: Konfirmasi --}}
                    <template x-if="jenis === 'informasi' && topik !== '' && topik !== 'lainnya'">
                        <div x-data="{ showConfirm: true }" x-show="showConfirm" x-cloak>
                            <h2 class="text-base font-bold text-slate-800 mb-4">Verifikasi Pemahaman</h2>
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
                                <p class="text-sm text-slate-700 mb-4">
                                    Apakah informasi yang diberikan sudah menjawab pertanyaan Anda?
                                </p>
                                <div class="flex gap-3">
                                    <button type="button" 
                                            @click="faqTerjawab = true; isSubmitting = true; $nextTick(() => document.getElementById('mainForm').submit())"
                                            class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-6 rounded-xl transition active:scale-95 flex justify-center items-center gap-2">
                                        <span x-text="isSubmitting ? 'Memproses...' : 'Ya, Sudah Dipahami'"></span>
                                    </button>
                                    <button type="button" @click="goBackAndSelectLainnya()"
                                            class="flex-1 bg-slate-300 hover:bg-slate-400 text-slate-800 font-bold py-3 px-6
                                                   rounded-xl transition active:scale-95">
                                        Belum, Isi Manual
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div x-show="!showConfirm" x-cloak>
                            <p class="text-xs text-slate-500 italic mb-6 text-center">
                                Pertanyaan dan interaksi Anda telah tersimpan dalam database kami.
                            </p>
                            {{-- Submit untuk Sudah Paham (tidak perlu lampiran) --}}
                            <div class="mt-8 pt-6 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                                <p class="text-xs text-slate-500 italic max-w-sm">
                                    Terima kasih telah menghubungi kami.
                                </p>
                                <button type="submit"
                                        :disabled="isSubmitting"
                                        :class="isSubmitting ? 'opacity-70 cursor-wait' : 'hover:bg-blue-700 active:scale-95'"
                                        class="w-full sm:w-auto bg-blue-600 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition flex items-center justify-center gap-2">
                                    <span x-text="isSubmitting ? 'Memproses...' : 'Selesai'"></span>
                                    <svg x-show="!isSubmitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>

                    {{-- UNTUK PEMBERIAN INFORMASI (lainnya) dan PENANGANAN: Lampiran --}}
                    <template x-if="jenis === 'penanganan' || (jenis === 'informasi' && (topik === '' || topik === 'lainnya'))">
                        <div>
                            <h2 class="text-base font-bold text-slate-800 mb-1">Dokumen Lampiran</h2>
                            <p class="text-xs text-slate-500 mb-6">Unggah dokumen pendukung (opsional).</p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                                {{-- Foto KTP — hanya muncul jika informasi --}}
                                <div x-show="jenis === 'informasi'" x-cloak
                                     class="bg-slate-50 border border-slate-200 rounded-xl p-5">
                                    <p class="text-xs font-bold text-slate-700 mb-1">Foto KTP</p>
                                    <p class="text-xs text-slate-500 mb-3">Wajib untuk verifikasi identitas (10MB, JPG/PNG)</p>
                                    <input type="file" name="foto_ktp" accept="image/*"
                                           class="block w-full text-sm text-slate-500
                                                  file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0
                                                  file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700
                                                  hover:file:bg-blue-100 transition cursor-pointer">
                                </div>

                                {{-- Bukti foto pendukung --}}
                                <div x-data="buktiUploader()" class="bg-slate-50 border border-slate-200 rounded-xl p-5">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <p class="text-xs font-bold text-slate-700">Bukti Pendukung</p>
                                            <p class="text-xs text-slate-500">Maks. 5 foto (10MB/foto)</p>
                                        </div>
                                        <span class="text-xs font-bold text-slate-400 bg-slate-200 px-2 py-1 rounded-md"
                                              x-text="files.length + '/5'"></span>
                                    </div>
                                    <label class="mt-2 flex flex-col items-center justify-center w-full h-20 border-2 border-dashed border-slate-300
                                                  rounded-xl cursor-pointer bg-white hover:bg-blue-50 hover:border-blue-400 transition-all"
                                           @dragover.prevent="dragging = true"
                                           @dragleave.prevent="dragging = false"
                                           @drop.prevent="handleDrop($event)"
                                           :class="dragging ? 'border-blue-400 bg-blue-50' : ''">
                                        <span class="text-xs text-slate-400 pointer-events-none">Klik atau drag foto</span>
                                        <input type="file" name="bukti[]" multiple accept="image/*" class="hidden" @change="handleFiles($event)">
                                    </label>
                                    <div x-show="previews.length > 0" class="mt-2 flex gap-2 overflow-x-auto custom-scroll pb-1">
                                        <template x-for="(src, i) in previews" :key="i">
                                            <div class="relative group w-14 h-14 flex-shrink-0 rounded-lg overflow-hidden border border-slate-200">
                                                <img :src="src" class="w-full h-full object-cover">
                                                <button type="button" @click="removeFile(i)"
                                                        class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition text-white text-xs">✕</button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Submit --}}
                            <div class="mt-8 pt-6 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                                <p class="text-xs text-slate-500 italic max-w-sm">
                                    Pastikan semua data sudah benar sebelum mengirim.
                                </p>
                                <button type="submit"
                                        :disabled="isSubmitting"
                                        :class="isSubmitting ? 'opacity-70 cursor-wait' : 'hover:bg-blue-700 active:scale-95'"
                                        class="w-full sm:w-auto bg-blue-600 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition flex items-center justify-center gap-2">
                                    <span x-text="isSubmitting ? 'Mengirim...' : 'Kirim Pengaduan'"></span>
                                    <svg x-show="!isSubmitting" class="w-4 h-4" ...> ... </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
                {{-- MASTER HIDDEN INPUTS: Mengirim state Alpine ke Controller dengan aman --}}
                <input type="hidden" name="aduan" :value="jenis === 'informasi' && topik !== 'lainnya' && topik !== '' ? currentTemplate : aduan">
                <input type="hidden" name="faq_terjawab" :value="faqTerjawab ? '1' : '0'">
                <input type="hidden" name="topik_faq" :value="topik">
            </form>

            {{-- NAVIGATION BUTTONS --}}
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-between items-center">
                <button type="button" @click="prevStep()"
                        x-show="currentStep > 0"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold
                               border border-slate-200 text-slate-600 hover:bg-slate-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Kembali
                </button>
                <div x-show="currentStep === 0" class="text-xs text-slate-400">Langkah 1 dari 4</div>

                <button type="button" @click="nextStep()"
                        x-show="currentStep < 3"
                        :disabled="!canProceed()"
                        class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl text-sm font-bold transition"
                        :class="canProceed()
                            ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm'
                            : 'bg-slate-200 text-slate-400 cursor-not-allowed'">
                    Lanjut
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        <p class="text-center text-slate-400 text-xs mt-6">
            Layanan ini dikelola oleh Kantor Imigrasi · Kerahasiaan data pemohon dijaga
        </p>
    </div>

    <script>
    const FAQ_TEMPLATES = {
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

Pendaftaran:
- Anak usia di atas 3 tahun wajib daftar online melalui M-Paspor
- Bawa semua dokumen ASLI

Biaya paspor anak:
- Elektronik masa berlaku 5 tahun: Rp 650.000

Layanan:
- Reguler: 3 hari kerja | Percepatan: 4 jam (sebelum 10.00 WIB), tambahan Rp 1.000.000`,

        paspor_umroh_haji: `Persyaratan paspor untuk umroh / haji dengan dokumen ASLI:
1. e-KTP
2. Kartu Keluarga (KK)
3. Akte Lahir / Buku Nikah / Ijazah SD-SMA
4. Paspor lama (jika memiliki)

Catatan nama satu kata — wajib tambahan dokumen:
- Surat rekomendasi dari travel umroh / haji
- Izin operasional travel umroh
- BPIH bagi calon jamaah haji

Biaya: Elektronik 5 tahun Rp 650.000 | 10 tahun Rp 950.000
Layanan: Reguler 3 hari | Percepatan 4 jam + Rp 1.000.000`,

        paspor_cpmi: `Persyaratan paspor untuk bekerja ke luar negeri (CPMI) dengan dokumen ASLI:
1. e-KTP
2. Kartu Keluarga (KK)
3. Akta Lahir / Ijazah SD-SMA / Buku Nikah
4. Paspor lama (jika memiliki)

Paspor GRATIS untuk CPMI pertama kali membuat paspor, tambahan dokumen:
- ID CPMI dari BP2MI, ATAU
- Kontrak kerja yang telah ditandatangani / sertifikat kelulusan G to G

Biaya: Elektronik 5 tahun Rp 650.000 | 10 tahun Rp 950.000`,

        paspor_rusak: `Prosedur penggantian paspor RUSAK:

Datang langsung ke Kantor Imigrasi TANPA mendaftar M-Paspor untuk proses BAP.

Dokumen ASLI yang dibawa:
1. e-KTP
2. Kartu Keluarga (KK)
3. Akta Lahir / Ijazah SD-SMA / Buku Nikah
4. Paspor yang rusak

Biaya: Denda Rp 500.000 + biaya paspor (5 tahun Rp 650.000 | 10 tahun Rp 950.000)`,

        paspor_hilang: `Prosedur penggantian paspor HILANG:

1. Urus Surat Keterangan Kehilangan di kantor kepolisian terdekat
2. Datang ke Kantor Imigrasi mulai pukul 08.00 WIB TANPA daftar M-Paspor untuk BAP

Dokumen ASLI:
1. e-KTP
2. Kartu Keluarga (KK)
3. Akta Lahir / Ijazah SD-SMA / Buku Nikah
4. Surat Keterangan Kehilangan dari kepolisian

Biaya: Denda Rp 1.000.000 + biaya paspor (5 tahun Rp 650.000 | 10 tahun Rp 950.000)`,

        pengambilan_diwakilkan: `Pengambilan paspor DIWAKILKAN:

A. Diwakilkan ke orang BERBEDA KK:
1. Surat kuasa bermaterai Rp 10.000
2. Lembar pengambilan dari petugas
3. Struk pembayaran
4. e-KTP asli pengambil
5. Fotokopi e-KTP pemilik paspor

B. Diwakilkan ke keluarga SATU KK:
1. Lembar pengambilan dari petugas
2. Struk pembayaran
3. KK asli
4. e-KTP asli pengambil`,

        pembatalan_paspor: `Permohonan pembatalan paspor.

Mohon lengkapi data berikut:

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

Jika ada lembar catatan dari petugas, mohon lampirkan fotonya pada kolom bukti.`,

        lainnya: '',
    };

    function pengaduanForm() {
        // Tangkap parameter dari URL
        const urlParams = new URLSearchParams(window.location.search);
        const rawKanal = urlParams.get('kanal');
        
        // Daftar kanal resmi yang diizinkan (Whitelist)
        const validKanals = ['Ruang Pengaduan', 'WhatsApp', 'Instagram', 'TikTok', 'Facebook', 'Lainnya'];
        
        // Cek apakah parameter URL cocok dengan salah satu kanal resmi (bebas huruf besar/kecil)
        let matchedKanal = null;

        if (rawKanal) {
            // Normalisasi: Hapus semua spasi dan jadikan huruf kecil. 
            // Jadi 'RuangPengaduan' atau 'FaceBook' dari URL tetap cocok.
            const cleanRaw = rawKanal.replace(/\s+/g, '').toLowerCase();
            matchedKanal = validKanals.find(k => k.replace(/\s+/g, '').toLowerCase() === cleanRaw);
            
            // Jika input tidak ada di daftar (misal: 'lolo'), otomatis paksa jadi 'Lainnya'
            if (!matchedKanal) {
                matchedKanal = 'Lainnya';
            }

            // 🟢 PERBAIKAN BARU: Ubah URL di address bar browser secara otomatis!
            // Ini akan mengganti ?kanal=lolo menjadi ?kanal=Lainnya tanpa me-refresh halaman
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('kanal', matchedKanal);
            window.history.replaceState({}, '', newUrl);
        }

        return {
            currentStep: 0,
            isSubmitting: false,
            steps: [
                { label: 'Layanan' },
                { label: 'Pemohon' },
                { label: 'Topik' },
                { label: 'Lampiran' },
            ],

            jenis: @json(old('jenis_layanan', '')),
            seksi: @json(old('seksi_tujuan', '')),
            nama: @json(old('nama', '')),
            nik: @json(old('nik', '')),
            whatsapp: @json(old('whatsapp', '')),
            alamat: @json(old('alamat', '')),
            
            // 🟢 PERBAIKAN: Gunakan kanal yang sudah divalidasi (matchedKanal)
            kanal: matchedKanal || @json(old('kanal', '')),
            isKanalLocked: !!matchedKanal,
            
            aduan: @json(old('aduan', '')),
            topik: '', 
            currentTemplate: '', 
            showConfirm: false, 
            faqTerjawab: false,

            applyTemplate() {
                this.currentTemplate = FAQ_TEMPLATES[this.topik] ?? '';
            },

            // Reset confirm modal saat navigasi
            goToStep(i) {
                if (this.canGoToStep(i)) {
                    this.currentStep = i;
                    this.showConfirm = (i === 3 && this.jenis === 'informasi' && this.topik !== '' && this.topik !== 'lainnya');
                }
            },

            nextStep() {
                if (this.canProceed() && this.currentStep < 3) {
                    this.currentStep++;
                    // Trigger confirmation modal jika informasi dengan topik pilihan
                    if (this.currentStep === 3 && this.jenis === 'informasi' && this.topik !== '' && this.topik !== 'lainnya') {
                        this.showConfirm = true;
                    }
                }
            },

            // Step 0 valid jika jenis dan seksi sudah dipilih
            isStep0Valid() { return this.jenis !== '' && this.seksi !== ''; },

            // Step 1 valid jika nama, whatsapp, alamat, kanal sudah diisi
            // + NIK jika jenis informasi
            isStep1Valid() {
                const base = this.nama.trim() !== '' && this.whatsapp.trim() !== ''
                          && this.alamat.trim() !== '' && this.kanal !== '';
                if (this.jenis === 'informasi') return base && this.nik.trim() !== '';
                return base;
            },

            // Step 2 valid tergantung jenis layanan
            isStep2Valid() {
                if (this.jenis === 'penanganan') {
                    // Untuk penanganan: aduan harus diisi
                    return this.aduan.trim() !== '';
                } else if (this.jenis === 'informasi') {
                    // Untuk informasi: topik harus dipilih
                    if (this.topik === '') return false;
                    // Jika lainnya, aduan harus diisi
                    if (this.topik === 'lainnya') return this.aduan.trim() !== '';
                    return true; // template topik sudah terisi
                }
                return false;
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
            },

            prevStep() {
                if (this.currentStep > 0) {
                    this.currentStep--;
                    this.showConfirm = false; // Reset modal saat kembali
                }
            },

            goBackAndSelectLainnya() {
                this.currentStep = 2;
                this.topik = 'lainnya';
                this.currentTemplate = '';
                this.showConfirm = false;
                this.faqTerjawab = false;
            },
        };
    }

    function buktiUploader() {
        return {
            previews: [], files: [], dragging: false,
            handleFiles(e) { this.addFiles(Array.from(e.target.files)); },
            handleDrop(e) {
                this.dragging = false;
                this.addFiles(Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/')));
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
            removeFile(i) { this.files.splice(i, 1); this.previews.splice(i, 1); this.syncInput(); },
            syncInput() {
                const input = this.$el.querySelector('input[type=file][multiple]');
                const dt = new DataTransfer();
                this.files.forEach(f => dt.items.add(f));
                input.files = dt.files;
            },
        };
    }
    </script>

</body>
</html>
