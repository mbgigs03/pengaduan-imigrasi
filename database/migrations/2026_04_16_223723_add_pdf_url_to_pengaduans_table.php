<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jalankan:
 * php artisan make:migration add_pdf_url_to_pengaduans_table
 * Lalu ganti isi dengan file ini.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengaduans', function (Blueprint $table) {
            // URL publik PDF yang sudah diupload ke Supabase
            $table->string('pdf_url')->nullable()->after('keterangan_admin');
        });
    }

    public function down(): void
    {
        Schema::table('pengaduans', function (Blueprint $table) {
            $table->dropColumn('pdf_url');
        });
    }
};