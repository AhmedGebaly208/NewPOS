<?php

namespace Modules\POS\Services;

use Modules\Sales\Models\Sale;

class ReceiptService
{
    public function generateReceipt(Sale $sale): array
    {
        $sale->load(['items', 'payments', 'customer', 'user']);

        return [
            'store_name' => config('app.name', 'POS Store'),
            'store_address' => config('pos.store_address', ''),
            'store_phone' => config('pos.store_phone', ''),
            'store_email' => config('pos.store_email', ''),
            'sale_number' => $sale->sale_number,
            'date' => $sale->completed_at ?? $sale->created_at,
            'cashier' => $sale->user->name,
            'customer' => $sale->customer ? [
                'name' => $sale->customer->name,
                'email' => $sale->customer->email,
                'phone' => $sale->customer->phone,
            ] : null,
            'items' => $sale->items->map(function ($item) {
                return [
                    'name' => $item->product_name,
                    'sku' => $item->product_sku,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total' => $item->total,
                ];
            })->toArray(),
            'subtotal' => $sale->subtotal,
            'discount_amount' => $sale->discount_amount,
            'discount_code' => $sale->discount_code,
            'tax_amount' => $sale->tax_amount,
            'total_amount' => $sale->total_amount,
            'paid_amount' => $sale->paid_amount,
            'change_amount' => $sale->change_amount,
            'payments' => $sale->payments->map(function ($payment) {
                return [
                    'method' => ucfirst(str_replace('_', ' ', $payment->payment_method)),
                    'amount' => $payment->amount,
                    'reference' => $payment->reference,
                ];
            })->toArray(),
            'notes' => $sale->notes,
        ];
    }

    public function generateHTML(Sale $sale): string
    {
        $data = $this->generateReceipt($sale);

        return view('pos::receipts.standard', compact('data'))->render();
    }

    public function generatePrintableHTML(Sale $sale): string
    {
        $data = $this->generateReceipt($sale);

        return view('pos::receipts.printable', compact('data'))->render();
    }
}
