<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaduan extends Model
{
    protected $fillable = [
        'nomor_tiket', 'nama', 'tgl_pengaduan', 'nik', 'alamat', 'whatsapp', 
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
    
    public function pemohon()
    {
        return $this->belongsTo(User::class, 'pemohon_id');
    }

     protected static function booted()
    {
        static::creating(function ($pengaduan) {
            $today = now()->format('Ymd'); // Menggunakan Carbon agar lebih konsisten dengan Laravel
            
            $lastTicket = static::whereDate('created_at', now())->latest()->first();
            
            $sequence = $lastTicket ? ((int) substr($lastTicket->nomor_tiket, -3)) + 1 : 1;
            
            $pengaduan->nomor_tiket = 'IMI-' . $today . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
        });
    }
}
