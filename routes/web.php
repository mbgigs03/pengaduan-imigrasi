<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PengaduanController;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Hapus atau comment route dashboard yang lama, ganti jadi ini:
Route::get('/dashboard', [PengaduanController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Route untuk menampilkan form (Hanya GET)
    Route::get('/pengaduan/create', [PengaduanController::class, 'create'])->name('pengaduan.create');

    // Route untuk memproses data form (Hanya POST)
    Route::post('/pengaduan/store', [PengaduanController::class, 'store'])->name('pengaduan.store');
});

require __DIR__.'/auth.php';
