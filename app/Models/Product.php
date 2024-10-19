<?php

namespace App\Models;

use App\Models\Ingredient;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'label',
        'image',
        'price',
        'stock',
        'status',
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

    public function ingredients()
    {
        $relation = $this->belongsToMany(Ingredient::class, 'ingredient_product', 'product_id', 'ingredient_id')->withPivot('amount');
        Log::info("Loading ingredients for product {$this->id}: " . json_encode($relation));
        return $relation;
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

    public function saveIngredients($ingredients)
    {
        Log::info('Saving ingredients for product: ' . $this->id);
        Log::info('Ingredients data:', $ingredients);

        $ingredientsData = collect($ingredients)->mapWithKeys(function ($item) {
            return [$item['ingredient_id'] => ['amount' => $item['amount']]];
        })->toArray();

        $this->ingredients()->sync($ingredientsData);

        Log::info('Ingredients saved successfully');
    }
}