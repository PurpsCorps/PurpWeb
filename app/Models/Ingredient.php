<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'unit', 'stock', 'price_per_unit'];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($ingredient) {
            Log::info('Saving ingredient: ' . json_encode($ingredient->toArray()));
            // Hapus validasi nama untuk sementara
        });
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('amount');
    }

    public function reduceStock($amount)
    {
        if ($this->stock < $amount) {
            throw new \Exception('Not enough stock for ingredient: ' . $this->name);
        }
        $this->stock -= $amount;
        $this->save();
    }
}