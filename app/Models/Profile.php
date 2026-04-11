<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'nama',
        'nip',
        'role',
        'seksi'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
