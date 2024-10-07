<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Division extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'category',
        'head_division'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
