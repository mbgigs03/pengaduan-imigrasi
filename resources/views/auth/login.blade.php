<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — Sistem Pengaduan Imigrasi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        .split-left {
            background: linear-gradient(160deg, #0f3460 0%, #1a5276 50%, #1f618d 100%);
            position: relative;
            overflow: hidden;
        }
        .split-left::before {
            content: '';
            position: absolute;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: rgba(255,255,255,.04);
            top: -80px; right: -80px;
        }
        .split-left::after {
            content: '';
            position: absolute;
            width: 200px; height: 200px;
            border-radius: 50%;
            background: rgba(255,255,255,.03);
            bottom: 60px; left: -60px;
        }
        .input-field {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            background: #f8fafc;
            color: #1e293b;
            transition: border-color .2s, background .2s;
        }
        .input-field:focus {
            border-color: #2563eb;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(37,99,235,.08);
        }
        .btn-primary {
            width: 100%;
            padding: 11px;
            background: #1d4ed8;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background .2s, transform .1s;
        }
        .btn-primary:hover { background: #1e40af; }
        .btn-primary:active { transform: scale(.98); }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fadeUp 0.45s ease both; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center p-4">

    <div class="w-full max-w-4xl bg-white rounded-2xl shadow-xl overflow-hidden flex min-h-[520px]">

        {{-- KIRI: Branding RUMANGSA --}}
        <div class="split-left hidden md:flex flex-col justify-between p-10 w-2/5 text-white relative z-10">
            <div>
                <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center mb-8 backdrop-blur-sm">
                    <img src="../images/logo-png.png" class="w-6" alt="Logo">
                </div>
                <h2 class="text-3xl font-extrabold leading-tight mb-3">
                    RUMANGSA
                </h2>
                <p class="text-blue-100 text-sm leading-relaxed opacity-90">
                    Ruang Manajemen Pengaduan dengan Integrasi Sistem Digital.
                    <br><span class="font-semibold text-white">Kantor Imigrasi Kelas II Non TPI Madiun.</span>
                </p>
            </div>

            <div class="space-y-3">
                {{-- Fitur Utama sesuai RAP --}}
                @foreach([
                    ['Pusat Kendali', 'Dashboard monitoring real-time Kakanim', '🏢'],
                    ['Manajemen Seksi', 'Disposisi aduan ke tiap sub-seksi', '📑'],
                    ['SLA Tracking', 'Pengawasan batas waktu tindak lanjut', '⚖️'],
                ] as $f)
                    <div class="flex items-center gap-3 bg-white/10 border border-white/10 rounded-xl px-4 py-3 backdrop-blur-md">
                        <span class="text-lg">{{ $f[2] }}</span>
                        <div>
                            <div class="text-xs font-bold text-white">{{ $f[0] }}</div>
                            <div class="text-xs text-blue-200">{{ $f[1] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- KANAN: Form Login --}}
        <div class="flex-1 flex flex-col justify-center p-8 md:p-12">
            <div class="fade-up max-w-sm w-full mx-auto">

                <div class="mb-8">
                    <h1 class="text-2xl font-extrabold text-slate-800 mb-1">Selamat datang</h1>
                    <p class="text-slate-500 text-sm">Masuk ke akun petugas Anda</p>
                </div>

                {{-- Session Status --}}
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">
                            Email
                        </label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                            class="input-field @error('email') border-red-400 @enderror"
                            placeholder="nama@imigrasi.go.id" required autofocus autocomplete="username">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-semibold text-slate-600 uppercase tracking-wide">
                                Password
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs text-blue-600 hover:underline">
                                    Lupa password?
                                </a>
                            @endif
                        </div>
                        <input id="password" type="password" name="password"
                            class="input-field @error('password') border-red-400 @enderror"
                            placeholder="••••••••" required autocomplete="current-password">
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="remember_me" name="remember"
                            class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <label for="remember_me" class="text-sm text-slate-600">Ingat saya</label>
                    </div>

                    <button type="submit" class="btn-primary">
                        Masuk ke Dashboard
                    </button>
                </form>

                <p class="text-center text-sm text-slate-500 mt-6">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="text-blue-600 font-semibold hover:underline">
                        Daftar sebagai petugas
                    </a>
                </p>

                <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                    <a href="{{ route('pengaduan.landing') }}" class="inline-flex items-center gap-1 text-xs text-slate-400 hover:text-slate-600 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Kembali ke halaman publik
                    </a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>