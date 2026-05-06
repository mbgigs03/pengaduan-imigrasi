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
        'bukti_files',   // ← kolom baru: JSON array of URLs
        'foto_ktp',   // ← tambahkan setelah 'bukti_files'
        'status',
        'deadline_tindak_lanjut',
        'keterangan_admin',
        'pdf_url',
        'updated_by',
    ];

    protected $casts = [
        'tgl_pengaduan'          => 'date',
        'deadline_tindak_lanjut' => 'datetime',
        'bukti_files'            => 'array',  // ← otomatis encode/decode JSON
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

    public function tanggapans() {
        return $this->hasMany(Tanggapan::class)->latest();
    }

    // ── Helper: gabungkan bukti lama + bukti_files baru ──────
    // Dipakai di blade agar tidak perlu logic di view
    // ── Helper: gabungkan bukti lama + bukti_files baru ──────
    public function getAllBuktiAttribute(): array
    {
        $files = $this->bukti_files ?? [];

        // Jika bukti lama ada dan belum masuk di array baru, sertakan
        if ($this->bukti && !in_array($this->bukti, $files)) {
            array_unshift($files, $this->bukti);
        }

        // --- TAMBAHAN FIX: Bersihkan URL yang dobel dari database lama ---
        $cleanFiles = array_map(function($url) {
            // Jika ada string yang berulang, kita replace/potong menjadi satu saja
            return str_replace(
                '/storage/v1/object/public/pengaduan/storage/v1/object/public/pengaduan/', 
                '/storage/v1/object/public/pengaduan/', 
                $url
            );
        }, $files);

        return array_values(array_filter($cleanFiles));
    }

    // ── Auto-generate nomor tiket saat creating ──────────────
    protected static function booted(): void
    {
        static::creating(function ($pengaduan) {
            $today = now()->format('Ymd');

            $lastTicket = static::whereDate('created_at', now())->latest('id')->first();

            if ($lastTicket) {
                $lastSequence = (int) substr($lastTicket->nomor_tiket, -3);
                $sequence = $lastSequence + 1;
            } else {
                $sequence = 1;
            }

            $pengaduan->nomor_tiket = 'IMI-' . $today . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
        });
    }
}