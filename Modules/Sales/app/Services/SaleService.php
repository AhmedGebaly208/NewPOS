<?php

namespace Modules\Sales\Services;

use Illuminate\Support\Facades\DB;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleItem;
use Modules\Sales\Models\Payment;
use Modules\Product\Models\Product;
use Modules\Discount\Services\DiscountService;
use Modules\Inventory\Events\StockUpdated;
use Modules\Sales\Events\SaleCompleted;
use Modules\Sales\Events\SaleVoided;

class SaleService
{
    public function __construct(
        protected DiscountService $discountService
    ) {}

    public function createSale(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            // Calculate totals
            $subtotal = 0;
            $items = [];

            foreach ($data['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                
                $quantity = $itemData['quantity'];
                $unitPrice = $product->selling_price;
                $itemSubtotal = $unitPrice * $quantity;

                $subtotal += $itemSubtotal;

                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal' => $itemSubtotal,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'total' => $itemSubtotal,
                ];
            }

            // Apply discount if provided
            $discountAmount = 0;
            $discountId = null;
            $discountCode = null;

            if (!empty($data['discount_code'])) {
                $discount = $this->discountService->applyDiscount(
                    $data['discount_code'],
                    $subtotal,
                    $data['customer_id'] ?? null,
                    $items
                );

                if ($discount) {
                    $discountAmount = $discount['amount'];
                    $discountId = $discount['discount_id'];
                    $discountCode = $data['discount_code'];
                }
            }

            // Calculate tax (assuming 0% for now, can be configured)
            $taxRate = config('sales.tax_rate', 0);
            $taxAmount = ($subtotal - $discountAmount) * ($taxRate / 100);

            $totalAmount = $subtotal - $discountAmount + $taxAmount;

            // Create sale
            $sale = Sale::create([
                'customer_id' => $data['customer_id'] ?? null,
                'user_id' => $data['user_id'],
                'status' => 'completed',
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $data['paid_amount'] ?? $totalAmount,
                'change_amount' => $data['change_amount'] ?? 0,
                'discount_id' => $discountId,
                'discount_code' => $discountCode,
                'notes' => $data['notes'] ?? null,
                'completed_at' => now(),
            ]);

            // Create sale items
            foreach ($items as $itemData) {
                $sale->items()->create($itemData);
            }

            // Create payment records
            if (!empty($data['payments'])) {
                foreach ($data['payments'] as $paymentData) {
                    $sale->payments()->create($paymentData);
                }
            }

            // Update inventory
            $this->updateInventory($sale);

            // Dispatch event
            event(new SaleCompleted($sale));

            return $sale->load(['items', 'payments', 'customer']);
        });
    }

    public function voidSale(int $saleId, int $userId, string $reason): Sale
    {
        return DB::transaction(function () use ($saleId, $userId, $reason) {
            $sale = Sale::findOrFail($saleId);

            if ($sale->isVoided()) {
                throw new \Exception('Sale is already voided');
            }

            $sale->update([
                'status' => 'voided',
                'voided_at' => now(),
                'voided_by' => $userId,
                'void_reason' => $reason,
            ]);

            // Restore inventory
            $this->restoreInventory($sale);

            // Dispatch event
            event(new SaleVoided($sale));

            return $sale;
        });
    }

    protected function updateInventory(Sale $sale): void
    {
        foreach ($sale->items as $item) {
            event(new StockUpdated(
                $item->product_id,
                -$item->quantity,
                'sale',
                "Sale #{$sale->sale_number}",
                $sale->user_id
            ));
        }
    }

    protected function restoreInventory(Sale $sale): void
    {
        foreach ($sale->items as $item) {
            event(new StockUpdated(
                $item->product_id,
                $item->quantity,
                'void',
                "Void sale #{$sale->sale_number}",
                $sale->voided_by
            ));
        }
    }

    public function getSaleDetails(int $saleId): Sale
    {
        return Sale::with(['items.product', 'payments', 'customer', 'user', 'discount'])
            ->findOrFail($saleId);
    }

    public function getDailySales(\DateTime $date = null): array
    {
        $date = $date ?? now();
        
        $sales = Sale::whereDate('created_at', $date)
            ->where('status', 'completed')
            ->with(['items', 'payments'])
            ->get();

        return [
            'date' => $date->format('Y-m-d'),
            'total_sales' => $sales->count(),
            'total_revenue' => $sales->sum('total_amount'),
            'total_discount' => $sales->sum('discount_amount'),
            'sales' => $sales,
        ];
    }
}
