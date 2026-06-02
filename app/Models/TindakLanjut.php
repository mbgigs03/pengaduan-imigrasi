<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TindakLanjut extends Model
{
    protected $fillable = [
        'pengaduan_id',
        'catatan_petugas',
        'tanggal_selesai',
        'petugas_id',
        'bukti_gambar',
    ];

    protected $casts = [
        'tanggal_selesai' => 'datetime',
    ];

    /**
     * Relasi ke Pengaduan induk (belongsTo → singular)
     */
    public function pengaduan()
    {
        return $this->belongsTo(Pengaduan::class, 'pengaduan_id');
    }

    /**
     * Relasi ke User petugas yang menginput
     */
    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}