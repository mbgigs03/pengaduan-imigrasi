<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tanggapans', function (Blueprint $row) {
            $row->id();
            $row->foreignId('pengaduan_id')->constrained('pengaduans')->onDelete('cascade');
            $row->foreignId('user_id')->constrained('users'); // Petugas yang memberi tanggapan
            $row->string('status'); // Menunggu Verifikasi, Sedang Ditindaklanjuti, dll
            $row->text('catatan');
            $row->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tanggapans');
    }
};
