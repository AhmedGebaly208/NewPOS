<?php

namespace Modules\POS\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Modules\Product\Models\Product;
use Modules\Customer\Models\Customer;
use Modules\Sales\Services\SaleService;
use Modules\POS\Models\HeldTransaction;
use Modules\Discount\Services\DiscountService;

class POSInterface extends Component
{
    // Customer
    public $customerId = null;
    public $customerSearch = '';
    public $selectedCustomer = null;

    // Cart
    public $cart = [];
    public $subtotal = 0;
    public $discountAmount = 0;
    public $taxAmount = 0;
    public $totalAmount = 0;

    // Discount
    public $discountCode = '';
    public $appliedDiscount = null;

    // Payment
    public $paymentMethod = 'cash';
    public $paidAmount = 0;
    public $changeAmount = 0;
    public $paymentMethods = [];

    // Search
    public $productSearch = '';
    public $searchResults = [];

    // UI State
    public $showPaymentModal = false;
    public $showCustomerModal = false;
    public $notes = '';

    public function mount()
    {
        $this->paymentMethods = config('sales.payment_methods', [
            'cash' => 'Cash',
            'card' => 'Card',
            'mobile_money' => 'Mobile Money',
        ]);
        $this->calculateTotals();
    }

    public function searchProducts()
    {
        if (strlen($this->productSearch) < 2) {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = Product::where('name', 'like', '%' . $this->productSearch . '%')
            ->orWhere('sku', 'like', '%' . $this->productSearch . '%')
            ->where('status', 'active')
            ->whereHas('inventory', function ($query) {
                $query->where('quantity', '>', 0);
            })
            ->take(10)
            ->get();
    }

    public function addToCart($productId)
    {
        $product = Product::findOrFail($productId);

        // Check if product already in cart
        $existingIndex = collect($this->cart)->search(function ($item) use ($productId) {
            return $item['product_id'] == $productId;
        });

        if ($existingIndex !== false) {
            $this->cart[$existingIndex]['quantity']++;
            $this->cart[$existingIndex]['total'] = $this->cart[$existingIndex]['quantity'] * $this->cart[$existingIndex]['price'];
        } else {
            $this->cart[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->selling_price,
                'quantity' => 1,
                'total' => $product->selling_price,
            ];
        }

        $this->productSearch = '';
        $this->searchResults = [];
        $this->calculateTotals();
    }

    public function removeFromCart($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->calculateTotals();
    }

    public function updateQuantity($index, $quantity)
    {
        if ($quantity <= 0) {
            $this->removeFromCart($index);
            return;
        }

        $this->cart[$index]['quantity'] = $quantity;
        $this->cart[$index]['total'] = $quantity * $this->cart[$index]['price'];
        $this->calculateTotals();
    }

    public function applyDiscount()
    {
        if (empty($this->discountCode)) {
            return;
        }

        $discountService = app(DiscountService::class);
        $result = $discountService->validateDiscount(
            $this->discountCode,
            $this->subtotal,
            $this->customerId,
            $this->cart
        );

        if ($result['valid']) {
            $this->appliedDiscount = $result;
            $this->discountAmount = $result['amount'];
        } else {
            session()->flash('error', $result['message']);
        }

        $this->calculateTotals();
    }

    public function removeDiscount()
    {
        $this->discountCode = '';
        $this->appliedDiscount = null;
        $this->discountAmount = 0;
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->subtotal = collect($this->cart)->sum('total');
        
        $taxRate = config('sales.tax_rate', 0);
        $this->taxAmount = ($this->subtotal - $this->discountAmount) * ($taxRate / 100);
        
        $this->totalAmount = $this->subtotal - $this->discountAmount + $this->taxAmount;
        
        $this->changeAmount = max(0, $this->paidAmount - $this->totalAmount);
    }

    public function selectCustomer($customerId)
    {
        $customer = Customer::find($customerId);
        $this->customerId = $customerId;
        $this->selectedCustomer = $customer;
        $this->showCustomerModal = false;
        $this->customerSearch = '';
    }

    public function clearCustomer()
    {
        $this->customerId = null;
        $this->selectedCustomer = null;
    }

    public function openPaymentModal()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Cart is empty');
            return;
        }

        $this->paidAmount = $this->totalAmount;
        $this->calculateTotals();
        $this->showPaymentModal = true;
    }

    public function completeSale()
    {
        if ($this->paidAmount < $this->totalAmount) {
            session()->flash('error', 'Insufficient payment amount');
            return;
        }

        $saleService = app(SaleService::class);

        try {
            $sale = $saleService->createSale([
                'customer_id' => $this->customerId,
                'user_id' => auth()->id(),
                'items' => collect($this->cart)->map(function ($item) {
                    return [
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                    ];
                })->toArray(),
                'discount_code' => $this->discountCode,
                'paid_amount' => $this->paidAmount,
                'change_amount' => $this->changeAmount,
                'notes' => $this->notes,
                'payments' => [[
                    'payment_method' => $this->paymentMethod,
                    'amount' => $this->paidAmount,
                ]],
            ]);

            session()->flash('success', 'Sale completed successfully! Sale #' . $sale->sale_number);
            
            $this->clearCart();
            $this->showPaymentModal = false;
            
            // Open receipt in new window if auto-print enabled
            if (config('pos.auto_print_receipt', false)) {
                $this->dispatch('print-receipt', saleId: $sale->id);
            }
            
            // Emit event for any additional handling
            $this->dispatch('sale-completed', saleId: $sale->id);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function holdTransaction()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Cart is empty');
            return;
        }

        HeldTransaction::create([
            'user_id' => auth()->id(),
            'customer_id' => $this->customerId,
            'cart_items' => $this->cart,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discountAmount,
            'tax_amount' => $this->taxAmount,
            'total_amount' => $this->totalAmount,
            'discount_code' => $this->discountCode,
            'notes' => $this->notes,
        ]);

        session()->flash('success', 'Transaction held successfully');
        $this->clearCart();
    }

    public function resumeTransaction($transactionId)
    {
        $held = HeldTransaction::findOrFail($transactionId);
        
        $this->cart = $held->cart_items;
        $this->customerId = $held->customer_id;
        $this->selectedCustomer = $held->customer;
        $this->discountCode = $held->discount_code;
        $this->notes = $held->notes;
        
        $this->calculateTotals();
        
        $held->delete();
        
        session()->flash('success', 'Transaction resumed');
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->customerId = null;
        $this->selectedCustomer = null;
        $this->discountCode = '';
        $this->appliedDiscount = null;
        $this->notes = '';
        $this->paidAmount = 0;
        $this->calculateTotals();
    }

    public function render()
    {
        $heldTransactions = HeldTransaction::where('user_id', auth()->id())
            ->latest()
            ->get();

        $customers = [];
        if (strlen($this->customerSearch) >= 2) {
            $customers = Customer::where('name', 'like', '%' . $this->customerSearch . '%')
                ->orWhere('email', 'like', '%' . $this->customerSearch . '%')
                ->orWhere('phone', 'like', '%' . $this->customerSearch . '%')
                ->take(10)
                ->get();
        }

        return view('pos::livewire.pos-interface', [
            'heldTransactions' => $heldTransactions,
            'customers' => $customers,
        ]);
    }
}
