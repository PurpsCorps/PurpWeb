<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Ingredient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function created(Order $order)
    {
        Log::info('OrderObserver: created method called for order ' . $order->id);
        Log::info('New order created:', $order->toArray());
        Log::info('Order items:', $order->order_items);
        $this->updateProductAndIngredientStock($order);
    }

    private function updateProductAndIngredientStock(Order $order)
    {
        Log::info('Starting stock update for order ' . $order->id);
        DB::beginTransaction();

        try {
            foreach ($order->order_items as $orderItem) {
                $product = Product::findOrFail($orderItem['product_id']);
                $quantity = $orderItem['quantity'];

                Log::info("Processing Product ID: {$product->id}, Quantity: {$quantity}");

                // Kurangi stok produk
                $beforeProductStock = $product->stock;
                $product->decrement('stock', $quantity);
                $product->refresh();
                Log::info("Product {$product->id} stock: before={$beforeProductStock}, after={$product->stock}");

                // Explicitly load the ingredients
                $product->load('ingredients');

                // Log debugging information
                Log::info("Ingredients relation loaded: " . ($product->relationLoaded('ingredients') ? 'Yes' : 'No'));
                Log::info("Product {$product->id} ingredients: " . json_encode($product->getRelation('ingredients')));

                if (!$product->relationLoaded('ingredients')) {
                    Log::warning("Failed to load ingredients for product {$product->id}. Skipping ingredient stock update.");
                    continue; // Skip to the next product if ingredients can't be loaded
                }

                $ingredients = $product->getRelation('ingredients');
                if (is_null($ingredients)) {
                    Log::warning("Ingredients relation is null for product {$product->id}. Skipping ingredient stock update.");
                    continue;
                }

                if ($ingredients->isEmpty()) {
                    Log::info("Product {$product->id} has no ingredients. Skipping ingredient stock update.");
                } else {
                    foreach ($ingredients as $ingredient) {
                        $amountToReduce = $ingredient->pivot->amount * $quantity;

                        Log::info("Processing Ingredient ID: {$ingredient->id}, Amount to reduce: {$amountToReduce}");

                        if ($ingredient->stock < $amountToReduce) {
                            throw new \Exception("Not enough stock for ingredient: {$ingredient->name}");
                        }

                        $beforeIngredientStock = $ingredient->stock;
                        $ingredient->decrement('stock', $amountToReduce);
                        $ingredient->refresh();
                        Log::info("Ingredient {$ingredient->id} stock: before={$beforeIngredientStock}, after={$ingredient->stock}");
                    }
                }
            }

            DB::commit();
            Log::info('Successfully committed stock updates for order ' . $order->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating stock: ' . $e->getMessage());
            throw $e;
        }
    }
}