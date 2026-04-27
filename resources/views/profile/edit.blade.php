<x-layouts.dashboard>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-slate-800 leading-tight">
            {{ __('Pengaturan Akun Petugas') }}
        </h2>
        <p class="text-sm text-slate-500">Kelola informasi profil dan keamanan akun RUMANGSA Anda.</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto space-y-8">
            
            {{-- Grid Layout untuk Informasi & Password agar lebih compact --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                {{-- Bagian 1: Informasi Profil --}}
                <div class="bg-white p-6 shadow-sm border border-slate-200 rounded-2xl transition-all hover:shadow-md">
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                        <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">Data Diri Petugas</h3>
                    </div>
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                {{-- Bagian 2: Update Password --}}
                <div class="bg-white p-6 shadow-sm border border-slate-200 rounded-2xl transition-all hover:shadow-md">
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                        <div class="p-2 bg-amber-50 text-amber-600 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">Keamanan Password</h3>
                    </div>
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

            </div>

            {{-- Bagian 3: Hapus Akun (Ditempatkan di bawah karena tindakan sensitif) --}}
            <div class="bg-red-50 p-6 border border-red-100 rounded-2xl">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-red-100 text-red-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-red-800">Zona Berbahaya</h3>
                </div>
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

        </div>
    </div>
</x-layouts.dashboard>