<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TindakLanjut extends Model
{
    protected $fillable = [
        'pengaduan_id',
        'catatan_petugas',
        'tanggal_selesai'
    ];
    
    public function pengaduan()
    {
        return $this->belongsTo(Pengaduan::class);
    }
}
