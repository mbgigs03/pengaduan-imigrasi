<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jalankan: php artisan make:migration add_petugas_id_to_tindak_lanjuts_table
 * Lalu ganti isi up() dan down() dengan yang ada di sini.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tindak_lanjuts', function (Blueprint $table) {
            // Kolom petugas yang melakukan tindak lanjut
            $table->foreignId('petugas_id')
                  ->nullable()
                  ->after('tanggal_selesai')
                  ->constrained('users')
                  ->nullOnDelete();
        });

        // Juga pastikan kolom updated_by ada di pengaduans
        if (!Schema::hasColumn('pengaduans', 'updated_by')) {
            Schema::table('pengaduans', function (Blueprint $table) {
                $table->foreignId('updated_by')
                      ->nullable()
                      ->after('keterangan_admin')
                      ->constrained('users')
                      ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('tindak_lanjuts', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\User::class, 'petugas_id');
            $table->dropColumn('petugas_id');
        });

        Schema::table('pengaduans', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\User::class, 'updated_by');
            $table->dropColumn('updated_by');
        });
    }
};