<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocument;

class ReceiptService
{
    /**
     * Build a PDF receipt for an order from its immutable snapshot data.
     */
    public function pdf(Order $order): PdfDocument
    {
        $order->loadMissing('items', 'payment', 'user');

        return Pdf::loadView('receipts.pdf', ['order' => $order])
            ->setPaper('a4');
    }
}
