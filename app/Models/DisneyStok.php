<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisneyStok extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'password',
        'profilepin',
        'usage',
    ];
}
