<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form Pengaduan - Kantor Imigrasi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

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
                    const input = this.$el.querySelector('input[type=file]');
                    const dt = new DataTransfer();
                    this.files.forEach(f => dt.items.add(f));
                    input.files = dt.files;
                }
            }
        }
    </script>
</head>

<body class="bg-gray-100 min-h-screen py-12 px-4 sm:px-6 lg:px-8">

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <a href="{{ route('pengaduan.landing') }}"
           class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-6 font-medium">
            &larr; Kembali ke Beranda
        </a>

        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-8 border border-gray-200">

            <div class="mb-8 border-b pb-4">
                <h2 class="text-3xl font-bold text-gray-800">Form Pengaduan Masyarakat</h2>
                <p class="text-gray-500 mt-1">Silakan isi formulir di bawah ini dengan data yang valid dan sesuai.</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
                    <h3 class="text-red-800 font-bold mb-2">Gagal Mengirim! Periksa isian berikut:</h3>
                    <ul class="list-disc list-inside text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('pengaduan.store') }}" method="POST" enctype="multipart/form-data"
                  x-data="{ jenis: '{{ old('jenis_layanan') }}' }">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="nama" :value="__('Nama Lengkap')" />
                        <x-text-input id="nama" name="nama" type="text"
                                      value="{{ old('nama') }}"
                                      class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <x-input-label for="nik" :value="__('NIK (16 Digit)')" />
                        <x-text-input id="nik" name="nik" type="text"
                                      value="{{ old('nik') }}"
                                      placeholder="35..." maxlength="16"
                                      class="mt-1 block w-full" required />
                    </div>

                    <div>
                        <x-input-label for="tgl_pengaduan" :value="__('Tanggal Pengaduan')" />
                        <x-text-input id="tgl_pengaduan" name="tgl_pengaduan" type="date"
                                      value="{{ date('Y-m-d') }}"
                                      class="mt-1 block w-full bg-gray-50" readonly />
                    </div>

                    <div>
                        <x-input-label for="whatsapp" :value="__('No. WhatsApp Aktif')" />
                        <x-text-input id="whatsapp" name="whatsapp" type="text"
                                      value="{{ old('whatsapp') }}"
                                      placeholder="08..."
                                      class="mt-1 block w-full" required />
                    </div>
                </div>

                <div class="mt-6">
                    <x-input-label for="alamat" :value="__('Alamat Lengkap')" />
                    <textarea id="alamat" name="alamat" rows="3"
                              class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                              required>{{ old('alamat') }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <x-input-label for="jenis_layanan" :value="__('Jenis Layanan')" />
                        <select x-model="jenis" name="jenis_layanan"
                                class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="informasi"  {{ old('jenis_layanan') === 'informasi'  ? 'selected' : '' }}>Pemberian Informasi</option>
                            <option value="penanganan" {{ old('jenis_layanan') === 'penanganan' ? 'selected' : '' }}>Penanganan Pengaduan</option>
                        </select>
                        <div class="mt-2 p-3 bg-blue-50 rounded-lg text-xs text-blue-700 border border-blue-100">
                            <template x-if="jenis === 'informasi'">
                                <span>*Layanan bagi pemohon yang membutuhkan data atau kejelasan prosedur.</span>
                            </template>
                            <template x-if="jenis === 'penanganan'">
                                <span>*Layanan bagi pemohon yang ingin menyampaikan keluhan atau ketidakpuasan layanan.</span>
                            </template>
                            <template x-if="jenis === ''">
                                <span class="text-gray-500 italic">Silakan pilih jenis layanan terlebih dahulu.</span>
                            </template>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="seksi_tujuan" :value="__('Kategori Pengaduan (Seksi Tujuan)')" />
                        <select name="seksi_tujuan"
                                class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                required>
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

                <div class="mt-6">
                    <x-input-label :value="__('Kanal Pengaduan (Darimana aduan datang?)')" />
                    <div class="mt-3 flex flex-wrap gap-4">
                        @foreach(['Ruang Pengaduan', 'WhatsApp', 'Instagram', 'TikTok', 'Facebook', 'Lainnya'] as $k)
                            <label class="inline-flex items-center p-2 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                <input type="radio" name="kanal" value="{{ $k }}"
                                       class="text-blue-600 focus:ring-blue-500 border-gray-300"
                                       {{ old('kanal') === $k ? 'checked' : '' }}
                                       required>
                                <span class="ml-2 text-sm text-gray-700">{{ $k }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6">
                    <x-input-label for="aduan" :value="__('Isi Aduan / Pertanyaan')" />
                    <textarea id="aduan" name="aduan" rows="4"
                              class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                              placeholder="Jelaskan secara detail pengaduan atau informasi yang Anda butuhkan..."
                              required>{{ old('aduan') }}</textarea>
                </div>

                <div class="mt-6">
                    <x-input-label :value="__('Foto KTP (Opsional)')" />
                    <input type="file" name="foto_ktp" accept="image/*"
                        class="mt-2 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                                file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                                hover:file:bg-blue-100 transition cursor-pointer">
                    <p class="text-xs text-gray-400 mt-1">Maksimal 10MB (JPG, PNG)</p>
                </div>

                {{-- Upload foto — multiple, pakai drag-drop (dari versi teman) --}}
                <div class="mt-6" x-data="buktiUploader()">
                    <x-input-label :value="__('Foto / Bukti Pendukung (Opsional, maks. 5 foto)')" />

                    {{-- Drop zone --}}
                    <label
                        class="mt-2 flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300
                               rounded-xl cursor-pointer bg-gray-50 hover:bg-blue-50 hover:border-blue-400 transition-all group"
                        @dragover.prevent="dragging = true"
                        @dragleave.prevent="dragging = false"
                        @drop.prevent="handleDrop($event)"
                        :class="dragging ? 'border-blue-400 bg-blue-50' : ''">
                        <div class="flex flex-col items-center gap-1 text-gray-400 group-hover:text-blue-500 transition-colors">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-sm font-medium">Klik atau drag foto ke sini</span>
                            <span class="text-xs">JPG, PNG — maks. 10MB/foto, hingga 5 foto</span>
                        </div>
                        {{-- name="bukti[]" agar konsisten dengan controller --}}
                        <input type="file" name="bukti[]" multiple accept="image/*"
                               class="hidden" @change="handleFiles($event)">
                    </label>

                    {{-- Preview grid --}}
                    <div x-show="previews.length > 0" class="mt-3 grid grid-cols-3 sm:grid-cols-5 gap-2">
                        <template x-for="(src, i) in previews" :key="i">
                            <div class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200 bg-gray-100">
                                <img :src="src" class="w-full h-full object-cover">
                                <button type="button"
                                        @click="removeFile(i)"
                                        class="absolute top-1 right-1 w-5 h-5 rounded-full bg-red-500 text-white
                                               flex items-center justify-center opacity-0 group-hover:opacity-100
                                               transition-opacity text-xs font-bold leading-none">
                                    &times;
                                </button>
                                <div class="absolute bottom-0 left-0 right-0 bg-black/40 text-white text-[9px]
                                            text-center py-0.5 truncate px-1"
                                     x-text="'Foto ' + (i+1)"></div>
                            </div>
                        </template>
                    </div>

                    <p class="text-xs text-gray-400 mt-1.5">Foto akan diunggah ke server setelah formulir dikirim.</p>
                </div>

                <div class="mt-10 flex justify-end">
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg
                                   shadow-lg hover:shadow-xl transition duration-200 flex items-center gap-2">
                        <span>Kirim Pengaduan</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>