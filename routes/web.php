<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PengaduanController;

/*
|--------------------------------------------------------------------------
| ROUTE PUBLIK (Tidak Perlu Login)
|--------------------------------------------------------------------------
*/

// Halaman Landing (2 Tombol)
Route::get('/', function () {
    return view('pengaduan.landing');
})->name('pengaduan.landing');

// Halaman Buat Aduan
Route::get('/buat-aduan', [PengaduanController::class, 'create'])->name('pengaduan.create');
Route::post('/buat-aduan', [PengaduanController::class, 'store'])->name('pengaduan.store');

// Halaman Cek Status Pengaduan
Route::get('/cek-status', [PengaduanController::class, 'track'])->name('pengaduan.track');
Route::post('/cek-status', [PengaduanController::class, 'searchTrack'])->name('pengaduan.searchTrack');


/*
|--------------------------------------------------------------------------
| ROUTE ADMIN (Wajib Login)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard Admin (Tabel Pengaduan)
    Route::get('/dashboard', [PengaduanController::class, 'index'])->name('dashboard');
    
    // Update Status oleh Admin
    Route::patch('/pengaduan/{pengaduan}/status', [PengaduanController::class, 'updateStatus'])->name('pengaduan.updateStatus');

    // Profile Bawaan Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';