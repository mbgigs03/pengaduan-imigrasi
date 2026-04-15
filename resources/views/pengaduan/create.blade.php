<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form Pengaduan - Kantor Imigrasi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen py-12 px-4 sm:px-6 lg:px-8">

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <a href="{{ route('pengaduan.landing') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-6 font-medium">
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
            
            <form action="{{ route('pengaduan.store') }}" method="POST" enctype="multipart/form-data" x-data="{ jenis: '' }">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="nama" :value="__('Nama Lengkap')" />
                        <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <x-input-label for="nik" :value="__('NIK (16 Digit)')" />
                        <x-text-input id="nik" name="nik" type="text" maxlength="16" class="mt-1 block w-full" required />
                    </div>

                    <div>
                        <x-input-label for="tgl_pengaduan" :value="__('Tanggal Pengaduan')" />
                        <x-text-input id="tgl_pengaduan" name="tgl_pengaduan" type="date" value="{{ date('Y-m-d') }}" class="mt-1 block w-full bg-gray-50" readonly />
                    </div>

                    <div>
                        <x-input-label for="whatsapp" :value="__('No. WhatsApp Aktif')" />
                        <x-text-input id="whatsapp" name="whatsapp" type="text" placeholder="08..." class="mt-1 block w-full" required />
                    </div>
                </div>

                <div class="mt-6">
                    <x-input-label for="alamat" :value="__('Alamat Lengkap')" />
                    <textarea id="alamat" name="alamat" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm" rows="3" required></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <x-input-label for="jenis_layanan" :value="__('Jenis Layanan')" />
                        <select x-model="jenis" name="jenis_layanan" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="informasi">Pemberian Informasi</option>
                            <option value="penanganan">Penanganan Pengaduan</option>
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
                        <select name="seksi_tujuan" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm" required>
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

                <div class="mt-6">
                    <x-input-label :value="__('Kanal Pengaduan (Darimana aduan datang?)')" />
                    <div class="mt-3 flex flex-wrap gap-4">
                        @foreach(['Ruang Pengaduan', 'WhatsApp', 'Instagram', 'TikTok', 'Facebook', 'Lainnya'] as $k)
                            <label class="inline-flex items-center p-2 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                <input type="radio" name="kanal" value="{{ $k }}" class="text-blue-600 focus:ring-blue-500 border-gray-300" required>
                                <span class="ml-2 text-sm text-gray-700">{{ $k }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6">
                    <x-input-label for="aduan" :value="__('Isi Aduan / Pertanyaan')" />
                    <textarea id="aduan" name="aduan" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm" rows="4" placeholder="Jelaskan secara detail pengaduan atau informasi yang Anda butuhkan..." required></textarea>
                </div>

                <div class="mt-6">
                    <x-input-label for="bukti" :value="__('Foto KTP / Bukti Pendukung (Opsional)')" />
                    <input type="file" name="bukti" class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition cursor-pointer">
                    <p class="text-xs text-gray-400 mt-1">Maksimal ukuran file: 2MB (JPG, JPEG, PNG)</p>
                </div>

                <div class="mt-10 flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg hover:shadow-xl transition duration-200 flex items-center gap-2">
                        <span>Kirim Pengaduan</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </form>
            </div>
    </div>

</body>
</html>