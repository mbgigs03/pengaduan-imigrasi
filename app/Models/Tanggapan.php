<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tanggapan extends Model
{
    protected $fillable = [
        'pengaduan_id',
        'user_id',
        'status',
        'catatan',
        'bukti_tanggapan', // ← pastikan ada
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pengaduan()
    {
        return $this->belongsTo(Pengaduan::class);
    }
}