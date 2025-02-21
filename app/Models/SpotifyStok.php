<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpotifyStok extends Model
{
    use HasFactory;

    protected $fillable = [
        'link',
        'alamat',
        'usage',
    ];
}
