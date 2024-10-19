<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class ReceiptController extends Controller
{
    public function print(Order $order)
    {
        $pdf = Pdf::loadView('receipt', ['order' => $order]);
        return $pdf->stream('receipt.pdf');
    }
}