<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FaqTemplateController;
use App\Http\Controllers\KakanimController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PengaduanController;
use App\Http\Controllers\PengaduanSlaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RekapitulasiController;
use App\Http\Controllers\TindakLanjutController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES — No authentication required
|--------------------------------------------------------------------------
*/

// Landing page / public complaint form
Route::get('/', function () {
    return view('welcome');
})->name('pengaduan.landing');

// Complaint submission
Route::get('/pengaduan/buat', [PengaduanController::class, 'create'])->name('pengaduan.create');
Route::post('/pengaduan/buat', [PengaduanController::class, 'store'])->name('pengaduan.store');

// Ticket tracking
Route::get('/pengaduan/track', [PengaduanController::class, 'track'])->name('pengaduan.track');
Route::post('/pengaduan/track', [PengaduanController::class, 'searchTrack'])->name('pengaduan.searchTrack');

// Download complaint PDF
Route::get('/pengaduan/{nomorTiket}/pdf', [PengaduanController::class, 'downloadPdf'])
    ->name('pengaduan.pdf')
    ->where('nomorTiket', '[A-Z0-9\-]+');

/*
|--------------------------------------------------------------------------
| AUTH ROUTES — Login required
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |----------------------------------------------------------------------
    | DASHBOARD — Auto-redirect based on role
    |----------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/pengaduan', [PengaduanController::class, 'index'])->name('pengaduan.index');

    /*
    |----------------------------------------------------------------------
    | PROFILE
    |----------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |----------------------------------------------------------------------
    | TIKKIM ONLY — FAQ Management & SLA Reports (Exclusive)
    |----------------------------------------------------------------------
    */
    Route::middleware('role:tikkim')->group(function () {
        Route::get('/pengaduan/sla', [PengaduanController::class, 'sla'])->name('pengaduan.sla');
        
        // FAQ Management
        Route::get('/faq', [FaqTemplateController::class, 'index'])->name('faq.index');
        Route::post('/faq', [FaqTemplateController::class, 'store'])->name('faq.store');
        Route::put('/faq/{faq}', [FaqTemplateController::class, 'update'])->name('faq.update');
        Route::delete('/faq/{faq}', [FaqTemplateController::class, 'destroy'])->name('faq.destroy');
    });

    /*
    |----------------------------------------------------------------------
    | TIKKIM, SEKSI, DOKLANINTALKIM, INTELDAKIM — Follow-up & Recapitulation
    | TIKKIM has FULL ACCESS to all features in this group
    |----------------------------------------------------------------------
    */
    Route::middleware(['auth', 'role:tikkim,seksi,doklanintalkim,inteldakim'])->group(function () {
        
        // Complaint status update
        Route::patch('/admin/pengaduan/update-status', [PengaduanController::class, 'updateStatus'])
            ->name('admin.pengaduan.updateStatus');

        // Follow-up management (TIKKIM can also delete/edit any follow-up)
        Route::post('/tindak-lanjut', [TindakLanjutController::class, 'store'])->name('tindak-lanjut.store');
        Route::patch('/tindak-lanjut/{tindakLanjut}', [TindakLanjutController::class, 'update'])->name('tindak-lanjut.update');
        Route::delete('/tindak-lanjut/{tindakLanjut}', [TindakLanjutController::class, 'destroy'])->name('tindak-lanjut.destroy');

        // Complaint detail
        Route::get('/pengaduan/{id}', [PengaduanController::class, 'show'])->name('pengaduan.show');

        // Recapitulation & Export
        Route::get('/rekap', [RekapitulasiController::class, 'index'])->name('rekapitulasi.index');
        Route::get('/export/excel', [RekapitulasiController::class, 'exportExcel'])->name('rekapitulasi.export.excel');
        Route::get('/rekapitulasi/export/pdf', [RekapitulasiController::class, 'exportPdf'])->name('rekapitulasi.export.pdf');

        // Download complaint PDF (dashboard version)
        Route::get('/dashboard/pengaduan/{nomorTiket}/pdf', [PengaduanController::class, 'downloadPdf'])
            ->name('dashboard.pengaduan.downloadPdf')
            ->where('nomorTiket', '[A-Z0-9\-]+');

        // SLA Monitoring
        Route::get('/pengaduan/sla', [PengaduanSlaController::class, 'index'])->name('pengaduan.sla');

        // Notification routes
        Route::prefix('notifikasi')->group(function () {
            Route::get('/', [NotifikasiController::class, 'index'])->name('notifikasi.index');
            Route::get('/count', [NotifikasiController::class, 'count'])->name('notifikasi.count');
            Route::post('/{notifikasi}/read', [NotifikasiController::class, 'markRead'])->name('notifikasi.markRead');
            Route::post('/read-all', [NotifikasiController::class, 'markAllRead'])->name('notifikasi.markAllRead');
        });
    });

    /*
    |----------------------------------------------------------------------
    | KAKANIM ONLY — Dashboard & Section Management
    |----------------------------------------------------------------------
    */
    Route::prefix('kakanim')->middleware(['auth', 'role:kakanim'])->group(function () {
        
        Route::get('/', [KakanimController::class, 'index'])->name('kakanim.dashboard');
        
        // Section tickets
        Route::get('/section/{section}', [KakanimController::class, 'sectionTickets'])->name('kakanim.section.tickets');
        
        // Debug endpoint (remove before production)
        Route::get('/section/{section}/debug', [KakanimController::class, 'sectionDebug'])->name('kakanim.section.debug');
        
        // Ticket detail
        Route::get('/ticket/{id}', [KakanimController::class, 'ticketDetail'])->name('kakanim.ticket.detail');
        
        // Alert & notifications
        Route::post('/alert', [KakanimController::class, 'sendAlert'])->name('kakanim.alert');
        Route::post('/notif/{notifikasi}/baca', [KakanimController::class, 'markRead'])->name('kakanim.notif.read');
    });
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATION ROUTES — Laravel Breeze
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';