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
        Schema::create('pengaduans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->date('tgl_pengaduan');
            $table->string('nomor_tiket');
            $table->string('nik', 16);
            $table->text('alamat');
            $table->string('whatsapp');
            $table->enum('jenis_layanan', ['informasi', 'penanganan']);
            $table->string('seksi_tujuan'); // paspor, intal, dll
            $table->string('kanal_pengaduan'); // wa, tiktok, dll
            $table->string('bukti')->nullable();
            $table->enum('status', ['pending', 'proses', 'diteruskan', 'selesai'])->default('pending');
            $table->dateTime('deadline_tindak_lanjut');
            $table->text('keterangan_admin')->nullable();
            $table->timestamps();
        });
        Schema::table('pengaduans', function (Blueprint $table) {
        $table->unique('nomor_tiket');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaduans');
    }
};
