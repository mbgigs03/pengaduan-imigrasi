<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class RegisteredUserController extends Controller
{
    /**
     * Tampilkan halaman registrasi.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Tangani permintaan registrasi masuk.
     * Menggunakan Database Transaction untuk memastikan User dan Profile tersimpan bersamaan.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'nip'      => ['nullable', 'string', 'max:20'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'role'     => ['required', 'in:tikkim,seksi,kakanim'],
            'seksi'    => ['required_if:role,seksi', 'nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'role.required'     => 'Pilih role jabatan Anda.',
            'seksi.required_if' => 'Pilih seksi Anda jika mendaftar sebagai Admin Seksi.',
        ]);

        // Gunakan DB Transaction agar jika salah satu gagal, data tidak "setengah jadi" di database
        DB::beginTransaction();

        try {
            // 1. Buat User baru
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // 2. Hubungkan/Update Profile
            // updateOrCreate akan mencegah ID user_id yang sama memiliki 2 baris
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id], // Pencarian berdasarkan user_id
                [
                    'nama'  => $request->name,
                    'nip'   => $request->nip,
                    'role'  => $request->role,
                    'seksi' => $request->role === 'seksi' ? $request->seksi : null,
                ]
            );

            DB::commit();

            event(new Registered($user));

            Auth::login($user);

            // Pastikan DashboardController kamu sudah melakukan redirect berdasarkan role
            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan saat pendaftaran. Silakan coba lagi.']);
        }
    }
}