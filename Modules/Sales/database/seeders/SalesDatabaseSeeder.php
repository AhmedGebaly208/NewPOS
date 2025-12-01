<?php

namespace Modules\Sales\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleItem;
use Modules\Sales\Models\Payment;
use Modules\Product\Models\Product;
use Modules\Customer\Models\Customer;
use App\Models\User;

class SalesDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::take(10)->get();
        $customers = Customer::take(5)->get();
        $user = User::first();

        if ($products->isEmpty() || !$user) {
            $this->command->warn('Please seed products and users first');
            return;
        }

        // Create 20 sample sales
        for ($i = 1; $i <= 20; $i++) {
            $customer = $customers->random();
            $numItems = rand(1, 5);
            
            $subtotal = 0;
            $items = [];

            for ($j = 0; $j < $numItems; $j++) {
                $product = $products->random();
                $quantity = rand(1, 3);
                $itemSubtotal = $product->selling_price * $quantity;
                $subtotal += $itemSubtotal;

                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'unit_price' => $product->selling_price,
                    'quantity' => $quantity,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'subtotal' => $itemSubtotal,
                    'total' => $itemSubtotal,
                ];
            }

            $discountAmount = rand(0, 1) ? rand(5, 20) : 0;
            $totalAmount = $subtotal - $discountAmount;

            $sale = Sale::create([
                'customer_id' => rand(0, 1) ? $customer->id : null,
                'user_id' => $user->id,
                'status' => 'completed',
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => 0,
                'total_amount' => $totalAmount,
                'paid_amount' => $totalAmount,
                'change_amount' => 0,
                'completed_at' => now()->subDays(rand(0, 30)),
            ]);

            foreach ($items as $itemData) {
                SaleItem::create(array_merge(['sale_id' => $sale->id], $itemData));
            }

            $paymentMethod = collect(['cash', 'card', 'mobile_money'])->random();
            Payment::create([
                'sale_id' => $sale->id,
                'payment_method' => $paymentMethod,
                'amount' => $totalAmount,
                'reference' => $paymentMethod !== 'cash' ? 'REF-' . strtoupper(uniqid()) : null,
            ]);
        }

        $this->command->info('Sales seeded successfully');
    }
}
