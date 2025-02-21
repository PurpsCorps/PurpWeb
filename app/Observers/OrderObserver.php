<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function created(Order $order)
    {
        Log::info('OrderObserver: created method called for order ' . $order->id);
        Log::info('New order created:', $order->toArray());
        Log::info('Order items:', $order->order_items);
        $this->updateProduct($order);
    }

    private function updateProduct(Order $order)
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