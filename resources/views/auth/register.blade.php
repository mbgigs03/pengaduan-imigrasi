{{-- resources/views/auth/register.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Petugas — Sistem Pengaduan Imigrasi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
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
        .input-field.error { border-color: #f87171; }
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

        /* Role selector cards */
        .role-card { cursor: pointer; transition: all .15s ease; }
        .role-card input[type="radio"] { display: none; }
        .role-card .card-inner {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: #f8fafc;
            transition: all .15s ease;
        }
        .role-card input[type="radio"]:checked + .card-inner {
            border-color: #2563eb;
            background: #eff6ff;
        }
        .role-card:hover .card-inner {
            border-color: #93c5fd;
        }
        .role-card input[type="radio"]:checked + .card-inner .role-icon {
            background: #2563eb;
            color: white;
        }
        .role-icon {
            width: 36px; height: 36px;
            border-radius: 9px;
            background: #e2e8f0;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            color: #64748b;
            font-size: 16px;
            transition: all .15s;
        }

        /* Seksi dropdown (conditional) */
        #seksi-group {
            overflow: hidden;
            max-height: 0;
            opacity: 0;
            transition: max-height .3s ease, opacity .3s ease;
        }
        #seksi-group.visible {
            max-height: 100px;
            opacity: 1;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fadeUp 0.4s ease both; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center p-4 py-10">

    <div class="w-full max-w-lg bg-white rounded-2xl shadow-xl overflow-hidden fade-up">

        {{-- HEADER --}}
        <div class="bg-gradient-to-r from-blue-800 to-blue-600 px-8 py-7 text-white">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-blue-200 hover:text-white text-xs mb-4 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Sudah punya akun? Masuk
            </a>
            <h1 class="text-2xl font-extrabold mb-1">Daftar Akun Petugas</h1>
            <p class="text-blue-200 text-sm">Akun ini digunakan untuk mengakses dashboard pengaduan internal.</p>
        </div>

        {{-- FORM --}}
        <div class="p-8">
            @if ($errors->any())
                <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4">
                    <p class="text-red-700 text-sm font-semibold mb-1">Periksa isian berikut:</p>
                    <ul class="list-disc list-inside text-red-600 text-xs space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" id="register-form" class="space-y-5" x-data="{ role: '{{ old('role', '') }}' }">
                @csrf

                {{-- Nama & NIP --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="input-field @error('name') error @enderror"
                            placeholder="Nama sesuai NIP" required autofocus>
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">NIP</label>
                        <input type="text" name="nip" value="{{ old('nip') }}"
                            class="input-field @error('nip') error @enderror"
                            placeholder="18 digit">
                        @error('nip') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">Email Dinas</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="input-field @error('email') error @enderror"
                        placeholder="nama@imigrasi.go.id" required>
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- ROLE SELECTOR --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase tracking-wide">
                        Role / Jabatan
                    </label>
                    <div class="grid grid-cols-3 gap-3">

                        {{-- Card Kakanim --}}
                        <label class="role-card" onclick="setRole('kakanim')">
                            <input type="radio" name="role" value="kakanim" {{ old('role') == 'kakanim' ? 'checked' : '' }}>
                            <div class="card-inner">
                                <div class="role-icon">🏛️</div>
                                <div>
                                    <div class="text-sm font-bold text-slate-800">Kakanim</div>
                                    <div class="text-xs text-slate-500 leading-tight">Laporan Eksekutif</div>
                                </div>
                            </div>
                        </label>

                        <label class="role-card" onclick="setRole('tikkim')">
                            <input type="radio" name="role" value="tikkim" {{ old('role') == 'tikkim' ? 'checked' : '' }}>
                            <div class="card-inner">
                                <div class="role-icon">🔭</div>
                                <div>
                                    <div class="text-sm font-bold text-slate-800">TIKKIM</div>
                                    <div class="text-xs text-slate-500 leading-tight">Monitoring & Koordinasi</div>
                                </div>
                            </div>
                        </label>

                        <label class="role-card" onclick="setRole('seksi')">
                            <input type="radio" name="role" value="seksi" {{ old('role') == 'seksi' ? 'checked' : '' }}>
                            <div class="card-inner">
                                <div class="role-icon">📋</div>
                                <div>
                                    <div class="text-sm font-bold text-slate-800">Admin Seksi</div>
                                    <div class="text-xs text-slate-500 leading-tight">Penanganan aduan seksi</div>
                                </div>
                            </div>
                        </label>

                    </div>
                    @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- SEKSI (muncul jika role = seksi) --}}
                <div id="seksi-group" class="{{ old('role') == 'seksi' ? 'visible' : '' }}">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">
                        Pilih Seksi
                    </label>
                    <select name="seksi" id="seksi-select"
                        class="input-field @error('seksi') error @enderror">
                        <option value="">-- Pilih seksi Anda --</option>
                        <option value="Tikkim"{{ old('seksi') == 'Tikkim' ? 'selected' : '' }}>Pelayanan Paspor (Tikkim)</option>
                        <option value="Doklanintalkim"{{ old('seksi') == 'Doklanintalkim' ? 'selected' : '' }}>[WNI] Dokumen Perjalanan (Doklanintal)</option>
                        <option value="Doklanintalkim"{{ old('seksi') == 'Doklanintalkim' ? 'selected' : '' }}>[WNA] Izin Tinggal (Doklanintal)</option>
                        <option value="inteldakim"   {{ old('seksi') == 'inteldakim' ? 'selected' : '' }}>[WNA] Pengawasan (Inteldak)</option>
                        <option value="inteldakim"   {{ old('seksi') == 'inteldakim' ? 'selected' : '' }}>Alur BAP (Inteldak)</option>
                        <option value="Tata Usaha"   {{ old('seksi') == 'Tata Usaha' ? 'selected' : '' }}>Sarana Prasarana (Tata Usaha)</option>
                    </select>
                    @error('seksi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Password --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">Password</label>
                        <input type="password" name="password"
                            class="input-field @error('password') error @enderror"
                            placeholder="Min. 8 karakter" required>
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">Konfirmasi</label>
                        <input type="password" name="password_confirmation"
                            class="input-field" placeholder="Ulangi password" required>
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    Buat Akun Petugas
                </button>
            </form>

            <p class="text-center text-xs text-slate-400 mt-5">
                Pendaftaran memerlukan verifikasi dari administrator sistem.
            </p>
        </div>
    </div>

    <script>
        function setRole(role) {
            const seksiGroup = document.getElementById('seksi-group');
            if (role === 'seksi') {
                seksiGroup.classList.add('visible');
                document.getElementById('seksi-select').required = true;
            } else {
                seksiGroup.classList.remove('visible');
                document.getElementById('seksi-select').required = false;
                document.getElementById('seksi-select').value = '';
            }
        }
        // Init on load
        document.addEventListener('DOMContentLoaded', function() {
            const checked = document.querySelector('input[name="role"]:checked');
            if (checked) setRole(checked.value);
        });
    </script>
</body>
</html>