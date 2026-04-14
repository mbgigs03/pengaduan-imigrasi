<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cek Status Pengaduan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    
    <div class="max-w-2xl mx-auto">
        <a href="{{ route('pengaduan.landing') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-6 font-medium">
            &larr; Kembali ke Beranda
        </a>

        <div class="bg-white shadow-md rounded-lg p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Lacak Status Pengaduan</h2>
            
            <form action="{{ route('pengaduan.searchTrack') }}" method="POST" class="mb-8">
                @csrf
                <div class="flex gap-4">
                    <input type="text" name="nomor_tiket" placeholder="Masukkan Nomor Tiket (Contoh: TIK-20260414-...)" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-lg uppercase" required value="{{ old('nomor_tiket') }}">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-md font-bold shadow transition">
                        Cari
                    </button>
                </div>
            </form>

            @if(isset($pengaduan))
                <div class="border-t pt-6 mt-6">
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <span class="text-sm text-gray-500 block">Nama Pemohon</span>
                                <span class="font-semibold text-gray-800">{{ $pengaduan->nama }}</span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 block">Tanggal Masuk</span>
                                <span class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($pengaduan->tgl_pengaduan)->format('d F Y') }}</span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 block">Seksi Dituju</span>
                                <span class="font-semibold text-gray-800 uppercase">{{ str_replace('_', ' ', $pengaduan->seksi_tujuan) }}</span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 block">Status Saat Ini</span>
                                <span class="inline-flex mt-1 px-3 py-1 text-xs font-bold rounded-full 
                                    {{ $pengaduan->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                      ($pengaduan->status == 'selesai' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800') }}">
                                    {{ strtoupper($pengaduan->status) }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <span class="text-sm text-gray-500 block mb-1">Tanggapan/Keterangan Admin:</span>
                            <p class="text-gray-800 bg-white p-3 rounded border italic">
                                {{ $pengaduan->keterangan_admin ?? 'Belum ada tanggapan. Pengaduan Anda sedang dalam antrean.' }}
                            </p>
                        </div>
                    </div>
                </div>
            @elseif(request()->isMethod('post'))
                <div class="mt-6 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-md text-center">
                    Nomor Tiket tidak ditemukan. Pastikan Anda memasukkan nomor yang benar.
                </div>
            @endif

        </div>
    </div>

</body>
</html>