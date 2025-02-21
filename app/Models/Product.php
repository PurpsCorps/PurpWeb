<?php

namespace App\Models;

use App\Models\Ingredient;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'productcategory_id',
        'productcategory_label',
        'name',
        'label',
        'image',
        'price',
        'stock',
        'status',
        'slug',
        'tos',
        'duration',
    ];

    protected static function booted()
    {
        static::created(function ($product) {
            Log::info('Product created: ' . $product->id);
        });

        static::updated(function ($product) {
            Log::info('Product updated: ' . $product->id);
        });
    }

    public function save(array $options = [])
    {
        Log::info('Saving product with data: ' . json_encode($this->attributes));

        try {
            parent::save($options);
            Log::info('Product saved successfully');
        } catch (\Exception $e) {
            Log::error('Error saving product: ' . $e->getMessage());
        }
    }
}