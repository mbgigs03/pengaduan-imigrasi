<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PengaduanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TindakLanjutController;
use App\Http\Controllers\RekapitulasiController;
use App\Http\Controllers\PengaduanSlaController;
use App\Http\Controllers\KakanimController;
use App\Http\Controllers\NotifikasiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIK — Bisa diakses tanpa login
|--------------------------------------------------------------------------
*/

// Halaman landing / form pengaduan publik
Route::get('/', function () {
    return view('welcome');
})->name('pengaduan.landing');

// Form pengajuan pengaduan (guest)
Route::get('/pengaduan/buat', [PengaduanController::class, 'create'])->name('pengaduan.create');
Route::post('/pengaduan/buat', [PengaduanController::class, 'store'])->name('pengaduan.store');

// Tracking nomor tiket (guest)
Route::get('/pengaduan/track',  [PengaduanController::class, 'track'])->name('pengaduan.track');
Route::post('/pengaduan/track', [PengaduanController::class, 'searchTrack'])->name('pengaduan.searchTrack');

// Download/lihat PDF pengaduan
Route::get('/pengaduan/{nomorTiket}/pdf', [PengaduanController::class, 'downloadPdf'])
    ->name('pengaduan.pdf');

/*
|--------------------------------------------------------------------------
| PRIVAT — Butuh login
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {


    // // Opsi A: Jika ingin nama routenya 'dashboard.kakanim'
    
    // // Route Alert untuk Kakanim

    /*
    |----------------------------------------------------------------------
    | DASHBOARD — auto-redirect sesuai role (tikkim/seksi)
    |----------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Route::middleware('auth')->group(function () {
    //     Route::post('/tindak-lanjut', [TindakLanjutController::class, 'store'])
    //         ->name('tindaklanjut.store');
    // });

    Route::get('/pengaduan', [PengaduanController::class, 'index'])->name('pengaduan.index');

    /*
    |----------------------------------------------------------------------
    | TIKKIM ONLY — Laporan, st`Xatistik global, semua seksi
    |----------------------------------------------------------------------
    */
    Route::middleware('role:tikkim')->group(function () {
        // Semua route TIKKIM sudah ditangani oleh DashboardController::tikkim()
        // Tambahkan route eksklusif TIKKIM di sini jika diperlukan, contoh:
        // Route::get('/laporan/export', [LaporanController::class, 'export'])->name('laporan.export');
        Route::get('/pengaduan/sla', [PengaduanController::class, 'sla'])->name('pengaduan.sla');

        Route::get('/faq', [App\Http\Controllers\FaqTemplateController::class, 'index'])->name('faq.index');
        Route::post('/faq', [App\Http\Controllers\FaqTemplateController::class, 'store'])->name('faq.store');
        Route::put('/faq/{faq}', [App\Http\Controllers\FaqTemplateController::class, 'update'])->name('faq.update');
        Route::delete('/faq/{faq}', [App\Http\Controllers\FaqTemplateController::class, 'destroy'])->name('faq.destroy');
    });

        

    /*
|--------------------------------------------------------------------------
| TIKKIM + SEKSI — Manajemen Tindak Lanjut
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:tikkim,seksi'])->group(function () {
    
    // 1. Simpan catatan tindak lanjut (bisa dipanggil terpisah atau bareng update status)
    // Route::post('/pengaduan/{pengaduan}/tindak-lanjut', [TindakLanjutController::class, 'store'])
    //     ->name('tindaklanjut.store');

    Route::patch('/admin/pengaduan/update-status', [PengaduanController::class, 'updateStatus'])
        ->name('admin.pengaduan.updateStatus');

    // Route Resource untuk manajemen histori jika diperlukan (opsional)
    // Gunakan ini jika ingin mengedit atau menghapus poin histori tertentu saja
    // Tambahkan di dalam grup middleware auth
    Route::post('/tindak-lanjut',              [TindakLanjutController::class, 'store'])
        ->name('tindak-lanjut.store');

    Route::patch('/tindak-lanjut/{tindakLanjut}', [TindakLanjutController::class, 'update'])
        ->name('tindak-lanjut.update');

    Route::delete('/tindak-lanjut/{tindakLanjut}', [TindakLanjutController::class, 'destroy'])
        ->name('tindak-lanjut.destroy');

    Route::get('/pengaduan/{id}', [PengaduanController::class, 'show'])
        ->name('pengaduan.show');

         // Halaman utama dengan filter & tabel preview
    // Halaman utama dengan filter & tabel preview
    Route::get('/rekap', [RekapitulasiController::class, 'index'])
        ->name('rekapitulasi.index'); // Ubah dari 'index' menjadi 'rekapitulasi.index'

    // Ekspor ke Excel (.xlsx)
    Route::get('/export/excel', [RekapitulasiController::class, 'exportExcel'])
        ->name('rekapitulasi.export.excel');
    Route::get('/rekapitulasi/export/pdf', [RekapitulasiController::class, 'exportPdf'])->name('rekapitulasi.export.pdf');

        // Download PDF — redirect ke Supabase public URL
    Route::get(
        '/dashboard/pengaduan/{nomorTiket}/pdf',
        [PengaduanController::class, 'downloadPdf']
    )->name('dashboard.pengaduan.downloadPdf')
     ->where('nomorTiket', '[A-Z0-9\-]+');   // hanya huruf besar, angka, strip
 
    // Download DOCX — stream dari Supabase ke browser
    // Route::get(
    //     '/dashboard/pengaduan/{nomorTiket}/docx',
    //     [DashboardController::class, 'downloadDocx']
    // )->name('dashboard.pengaduan.downloadDocx')
    //  ->where('nomorTiket', '[A-Z0-9\-]+');

     // ── Monitoring SLA (halaman terpisah) ────────────────────
    Route::get('/pengaduan/sla', [PengaduanSlaController::class, 'index'])
        ->name('pengaduan.sla');

    Route::prefix('notifikasi')->group(function () {
        Route::get('/', [NotifikasiController::class, 'index'])->name('notifikasi.index');
        Route::get('/count', [NotifikasiController::class, 'count'])->name('notifikasi.count');
        
        // Perhatikan penamaan endpoint ini agar sesuai dengan fetch API di Javascript Anda
        Route::post('/{notifikasi}/read', [NotifikasiController::class, 'markRead'])->name('notifikasi.markRead');
        Route::post('/read-all', [NotifikasiController::class, 'markAllRead'])->name('notifikasi.markAllRead');
    });

});
    
    Route::prefix('kakanim')->middleware(['auth', 'role:kakanim'])->group(function () {
 
    Route::get('/', [KakanimController::class, 'index'])
        ->name('kakanim.dashboard');
 
    // Layer 1: ticket list per section
    Route::get('/section/{section}', [KakanimController::class, 'sectionTickets'])
        ->name('kakanim.section.tickets');
 
    // ── DEBUG ONLY: open this URL in your browser to inspect raw DB state ──
    // e.g. /kakanim/section/Tata%20Usaha/debug
    // Remove before deploying to production!
    Route::get('/section/{section}/debug', [KakanimController::class, 'sectionDebug'])
        ->name('kakanim.section.debug');
 
    // Layer 2: full ticket detail
    Route::get('/ticket/{id}', [KakanimController::class, 'ticketDetail'])
        ->name('kakanim.ticket.detail');
 
    Route::post('/alert', [KakanimController::class, 'sendAlert'])
        ->name('kakanim.alert');
 
    Route::post('/notif/{notifikasi}/baca', [KakanimController::class, 'markRead'])
        ->name('kakanim.notif.read');
});
/*
|--------------------------------------------------------------------------
| KHUSUS TIKKIM — Manajemen/Moderasi Tindak Lanjut
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:tikkim'])->group(function () {
    
    // TIKKIM bisa menghapus atau mengedit catatan jika ada kesalahan input
    Route::delete('/tindak-lanjut/{tindakLanjut}', [TindakLanjutController::class, 'destroy'])
        ->name('tindaklanjut.destroy');
});

    /*
    |----------------------------------------------------------------------
    | PROFILE
    |----------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| AUTH ROUTES (login, register, dll) — dari Laravel Breeze
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';