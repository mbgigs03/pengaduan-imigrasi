<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaduan extends Model
{
    protected $fillable = [
        'nama', 'tgl_pengaduan', 'nik', 'alamat', 'whatsapp', 
        'jenis_layanan', 'seksi_tujuan', 'kanal_pengaduan', 
        'bukti', 'status', 'deadline_tindak_lanjut', 'keterangan_admin'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tindakLanjut()
    {
        return $this->hasOne(TindakLanjut::class);
    }
}
