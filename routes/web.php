<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReceiptController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/receipt/{order}', [ReceiptController::class, 'print'])->name('receipt.print');

Route::get('/print/{orderId}', function($orderId) {
    return view('print', ['orderId' => $orderId]);
})->name('print.receipt');
