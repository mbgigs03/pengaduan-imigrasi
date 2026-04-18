<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaduan extends Model
{
    protected $fillable = [
        'nomor_tiket',
        'nama',
        'tgl_pengaduan',
        'nik',
        'alamat',
        'whatsapp',
        'jenis_layanan',
        'seksi_tujuan',
        'kanal_pengaduan',
        'aduan',
        'bukti',
        'status',
        'deadline_tindak_lanjut',
        'keterangan_admin',
        'pdf_url',       // ← tambahan: URL PDF di Supabase Storage
        'updated_by',
    ];

    protected $casts = [
        'tgl_pengaduan'          => 'date',
        'deadline_tindak_lanjut' => 'datetime',
    ];

    // ── Relasi ───────────────────────────────────────────────
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

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ── Auto-generate nomor tiket saat creating ──────────────
    protected static function booted(): void
{
    static::creating(function ($pengaduan) {
        $today = now()->format('Ymd');
        
        // Ambil data terakhir khusus hari ini
        $lastTicket = static::whereDate('created_at', now())->latest('id')->first();
        
        if ($lastTicket) {
            // Ambil 3 angka terakhir, contoh: IMI-20260417-007 -> ambil 007
            $lastSequence = (int) substr($lastTicket->nomor_tiket, -3);
            $sequence = $lastSequence + 1;
        } else {
            $sequence = 1;
        }

        // Format: IMI-20260417-007-XXXX (XXXX adalah random string agar tidak Unique Violation)
        // Atau jika ingin tetap 007, tambahkan pengecekan loop
        $pengaduan->nomor_tiket = 'IMI-' . $today . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
        
        // OPTIONAL: Jika ingin SANGAT AMAN dari error duplicate, tambahkan suffix random
        // $pengaduan->nomor_tiket .= '-' . strtoupper(Str::random(3));
    });
}
}