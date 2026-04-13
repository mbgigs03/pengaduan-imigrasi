<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Form Pengaduan Masyarakat - Kantor Imigrasi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8">
                
                <form action="{{ route('pengaduan.store') }}" method="POST" enctype="multipart/form-data" x-data="{ jenis: '' }">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="nama" :value="__('Nama Lengkap')" />
                            <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full" required />
                        </div>
                        <div>
                            <x-input-label for="nik" :value="__('NIK (16 Digit)')" />
                            <x-text-input id="nik" name="nik" type="text" class="mt-1 block w-full" required />
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
                        <textarea id="alamat" name="alamat" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3" required></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <x-input-label for="jenis_layanan" :value="__('Jenis Layanan')" />
                            <select x-model="jenis" name="jenis_layanan" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">-- Pilih Jenis --</option>
                                <option value="informasi">Pemberian Informasi</option>
                                <option value="penanganan">Penanganan Pengaduan</option>
                            </select>
                            <div class="mt-2 p-2 bg-blue-50 rounded text-xs text-blue-700">
                                <template x-if="jenis === 'informasi'">
                                    <span>*Deskripsi: Layanan bagi pemohon yang membutuhkan data atau kejelasan prosedur.</span>
                                </template>
                                <template x-if="jenis === 'penanganan'">
                                    <span>*Deskripsi: Layanan bagi pemohon yang ingin menyampaikan keluhan atau ketidakpuasan layanan.</span>
                                </template>
                            </div>
                        </div>

                        <div>
                            <x-input-label for="seksi_tujuan" :value="__('Sasaran Pengaduan (Seksi Tujuan)')" />
                            <select name="seksi_tujuan" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="paspor">Pelayanan Paspor</option>
                                <option value="intal">Pelayanan Intal</option>
                                <option value="pengawasan">Pelayanan Pengawasan Orang Asing</option>
                                <option value="wna_wni">Pelayanan WNA/WNI</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-input-label :value="__('Kanal Pengaduan (Darimana aduan datang?)')" />
                        <div class="mt-2 flex flex-wrap gap-4">
                            @foreach(['Ruang Pengaduan', 'WhatsApp', 'Instagram', 'TikTok', 'Facebook', 'Lainnya'] as $k)
                                <label class="inline-flex items-center">
                                    <input type="radio" name="kanal" value="{{ $k }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-600">{{ $k }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-input-label for="bukti" :value="__('Foto KTP / Bukti Pendukung (Opsional)')" />
                        <input type="file" name="bukti" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                    </div>

                    <div class="mt-8 flex justify-end">
                        <x-primary-button>
                            {{ __('Kirim Pengaduan Sekarang') }}
                        </x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>