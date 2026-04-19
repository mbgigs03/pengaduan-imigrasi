<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TindakLanjut extends Model
{
    protected $fillable = [
        'pengaduan_id',
        'catatan_petugas',
        'tanggal_selesai',
        'petugas_id',       // FK ke users (opsional, perlu kolom di migrasi)
        'bukti_gambar',
    ];

    protected $casts = [
        'tanggal_selesai' => 'datetime',
    ];

    /**
     * Relasi ke Pengaduan induk
     */
    public function pengaduans()
    {
        return $this->belongsTo(Pengaduan::class, 'pengaduan_id');
    }

    /**
     * Relasi ke User petugas yang menginput
     * (aktifkan jika sudah tambah kolom petugas_id)
     */
    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}