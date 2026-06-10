<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id', 'id');
    }

    public function pengaduans()
    {
        return $this->hasMany(Pengaduan::class);
    }

    /**
     * 🔥 ACCESSOR UNTUK ROLE (Otomatis ambil dari profile)
     */
    public function getRoleAttribute()
    {
        if ($this->relationLoaded('profile') && $this->profile) {
            return $this->profile->role;
        }
        
        if ($this->profile) {
            return $this->profile->role;
        }
        
        return 'pemohon'; // default
    }
    
    /**
     * 🔥 ACCESSOR UNTUK SEKSI (Otomatis ambil dari profile)
     */
    public function getSeksiAttribute()
    {
        if ($this->relationLoaded('profile') && $this->profile) {
            return $this->profile->seksi;
        }
        
        if ($this->profile) {
            return $this->profile->seksi;
        }
        
        return null;
    }

    // Method helper (opsional)
    public function isPemohon()
    {
        return $this->role === 'pemohon';
    }

    public function isSeksi()
    {
        return $this->role === 'seksi';
    }

    public function isTikkim()
    {
        return $this->role === 'tikkim';
    }
    
    public function isKakanim()
    {
        return $this->role === 'kakanim';
    }
}