<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Layanan Pengaduan Imigrasi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="max-w-3xl mx-auto p-6 lg:p-8 w-full">
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
            <div class="p-8 text-center bg-blue-600 text-white">
                <h1 class="text-3xl font-bold mb-2">Layanan Pengaduan Masyarakat</h1>
                <p class="text-blue-100">Kantor Imigrasi - Cepat, Transparan, dan Akuntabel</p>
            </div>
            
            <div class="p-10 grid grid-cols-1 md:grid-cols-2 gap-8">
                <a href="{{ route('pengaduan.create') }}" class="group flex flex-col items-center justify-center p-8 border-2 border-blue-500 rounded-xl hover:bg-blue-50 transition duration-300">
                    <svg class="w-16 h-16 text-blue-600 mb-4 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    <span class="text-xl font-semibold text-gray-800">Buat Aduan Baru</span>
                    <span class="text-sm text-gray-500 mt-2 text-center">Sampaikan keluhan atau pertanyaan Anda di sini.</span>
                </a>

                <a href="{{ route('pengaduan.track') }}" class="group flex flex-col items-center justify-center p-8 border-2 border-green-500 rounded-xl hover:bg-green-50 transition duration-300">
                    <svg class="w-16 h-16 text-green-600 mb-4 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    <span class="text-xl font-semibold text-gray-800">Cek Progress Aduan</span>
                    <span class="text-sm text-gray-500 mt-2 text-center">Pantau status aduan menggunakan Nomor Tiket.</span>
                </a>
            </div>
        </div>
    </div>

    @if (session('tiket'))
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            title: 'Berhasil!',
            html: `
                <p>Aduan Anda telah diterima.</p>
                <div class="my-4 p-4 bg-gray-100 rounded border">
                    <small class="text-gray-500">Nomor Tiket Anda:</small>
                    <div class="text-2xl font-bold text-blue-600 tracking-widest">{{ session('tiket') }}</div>
                </div>
                <p class="text-sm text-red-500 font-bold italic underline">Wajib: Simpan/Catat nomor tiket ini untuk cek status!</p>
            `,
            icon: 'success',
            confirmButtonText: 'Oke, Saya Paham',
            confirmButtonColor: '#2563eb'
        });
    </script>
    @endif

</body>
</html>