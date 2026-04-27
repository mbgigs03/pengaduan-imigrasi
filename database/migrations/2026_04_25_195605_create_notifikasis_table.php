<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jalankan:
 *   php artisan make:migration create_notifikasis_table
 * Lalu ganti isi up()/down() dengan file ini.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasis', function (Blueprint $table) {
            $table->id();

            // Pengirim (Kakanim)
            $table->foreignId('from_user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Penerima (User admin seksi tertentu, nullable jika broadcast ke seksi)
            $table->foreignId('to_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Target seksi (string, lebih fleksibel daripada FK)
            $table->string('target_seksi')->nullable()->index();

            // Tipe notifikasi: 'alert' | 'info' | 'reminder'
            $table->string('tipe', 30)->default('alert');

            // Judul singkat untuk preview notif di sidebar
            $table->string('judul', 200);

            // Isi pesan lengkap dari Kakanim
            $table->text('pesan');

            // Metadata tambahan (opsional, misalnya nomor tiket terkait)
            $table->json('meta')->nullable();

            // Sudah dibaca atau belum
            $table->timestamp('dibaca_at')->nullable();

            $table->timestamps();

            // Index untuk query cepat notif belum dibaca per user/seksi
            $table->index(['to_user_id', 'dibaca_at']);
            $table->index(['target_seksi', 'dibaca_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasis');
    }
};