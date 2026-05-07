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
    ];
    
    //
}
