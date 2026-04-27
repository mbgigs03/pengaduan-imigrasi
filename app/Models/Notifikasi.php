<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notifikasi extends Model
{
    protected $table = 'notifikasis';

    protected $fillable = [
        'from_user_id',
        'to_user_id',
        'target_seksi',
        'tipe',
        'judul',
        'pesan',
        'meta',
        'dibaca_at',
    ];

    protected $casts = [
        'meta'      => 'array',
        'dibaca_at' => 'datetime',
    ];

    // ── Relasi ────────────────────────────────────────────────
    public function pengirim(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function penerima(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    // ── Scope: belum dibaca ───────────────────────────────────
    public function scopeBelumDibaca($query)
    {
        return $query->whereNull('dibaca_at');
    }

    // ── Helper: tandai sudah dibaca ───────────────────────────
    public function markAsRead(): void
    {
        if (!$this->dibaca_at) {
            $this->update(['dibaca_at' => now()]);
        }
    }
}