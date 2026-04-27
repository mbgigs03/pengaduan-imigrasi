<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengaduans', function (Blueprint $table) {
            // Kolom baru: JSON array URL foto (multiple upload)
            // Kolom lama `bukti` tetap ada untuk backward compatibility
            $table->json('bukti_files')->nullable()->after('bukti');
        });
    }

    public function down(): void
    {
        Schema::table('pengaduans', function (Blueprint $table) {
            $table->dropColumn('bukti_files');
        });
    }
};