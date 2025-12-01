<div class="min-h-screen bg-gray-100 dark:bg-gray-900">
    <div class="flex h-screen">
        <!-- Left Panel - Product Search & Cart -->
        <div class="flex-1 flex flex-col bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700">
            <!-- Header -->
            <div class="bg-primary-600 text-white p-4">
                <h1 class="text-2xl font-bold">Point of Sale</h1>
                <p class="text-sm opacity-90">{{ auth()->user()->name }}</p>
            </div>

            <!-- Product Search -->
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <input
                    type="text"
                    wire:model.live="productSearch"
                    placeholder="Search products by name or SKU..."
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-primary-500"
                    autofocus
                >

                <!-- Search Results Dropdown -->
                @if(count($searchResults) > 0)
                <div class="mt-2 bg-white dark:bg-gray-700 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 max-h-64 overflow-y-auto">
                    @foreach($searchResults as $product)
                    <button
                        wire:click="addToCart({{ $product->id }})"
                        class="w-full text-left px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-600 border-b border-gray-100 dark:border-gray-600 last:border-0"
                    >
                        <div class="font-medium text-gray-900 dark:text-white">{{ $product->name }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            SKU: {{ $product->sku }} | ${{ number_format($product->selling_price, 2) }}
                        </div>
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-4">
                @if(count($cart) == 0)
                <div class="text-center text-gray-500 dark:text-gray-400 mt-20">
                    <svg class="mx-auto h-16 w-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <p class="text-lg">Cart is empty</p>
                    <p class="text-sm">Search and add products to start</p>
                </div>
                @else
                <div class="space-y-2">
                    @foreach($cart as $index => $item)
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-900 dark:text-white">{{ $item['name'] }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $item['sku'] }}</p>
                                <p class="text-sm font-medium text-primary-600 dark:text-primary-400">${{ number_format($item['price'], 2) }}</p>
                            </div>
                            <button
                                wire:click="removeFromCart({{ $index }})"
                                class="text-red-500 hover:text-red-700"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="mt-2 flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <button
                                    wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] - 1 }})"
                                    class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded hover:bg-gray-300 dark:hover:bg-gray-500"
                                >
                                    -
                                </button>
                                <span class="px-4 py-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded">{{ $item['quantity'] }}</span>
                                <button
                                    wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})"
                                    class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded hover:bg-gray-300 dark:hover:bg-gray-500"
                                >
                                    +
                                </button>
                            </div>
                            <div class="text-lg font-bold text-gray-900 dark:text-white">
                                ${{ number_format($item['total'], 2) }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Totals -->
            <div class="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                    <span class="font-medium text-gray-900 dark:text-white">${{ number_format($subtotal, 2) }}</span>
                </div>
                @if($discountAmount > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Discount:</span>
                    <span class="font-medium text-green-600">-${{ number_format($discountAmount, 2) }}</span>
                </div>
                @endif
                @if($taxAmount > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Tax:</span>
                    <span class="font-medium text-gray-900 dark:text-white">${{ number_format($taxAmount, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between text-lg font-bold border-t pt-2">
                    <span class="text-gray-900 dark:text-white">Total:</span>
                    <span class="text-primary-600 dark:text-primary-400">${{ number_format($totalAmount, 2) }}</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="border-t border-gray-200 dark:border-gray-700 p-4 grid grid-cols-3 gap-2">
                <button
                    wire:click="clearCart"
                    class="px-4 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 font-medium"
                >
                    Clear
                </button>
                <button
                    wire:click="holdTransaction"
                    class="px-4 py-3 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 font-medium"
                >
                    Hold
                </button>
                <button
                    wire:click="openPaymentModal"
                    class="px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium"
                >
                    Pay
                </button>
            </div>
        </div>

        <!-- Right Panel - Customer & Held Transactions -->
        <div class="w-80 bg-white dark:bg-gray-800 p-4 space-y-4 overflow-y-auto">
            <!-- Customer Section -->
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                <h2 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">Customer</h2>
                @if($selectedCustomer)
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $selectedCustomer->name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $selectedCustomer->email }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $selectedCustomer->phone }}</p>
                        </div>
                        <button
                            wire:click="clearCustomer"
                            class="text-red-500 hover:text-red-700"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                @else
                <button
                    wire:click="$set('showCustomerModal', true)"
                    class="w-full px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700"
                >
                    Select Customer
                </button>
                @endif
            </div>

            <!-- Discount Section -->
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                <h2 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">Discount</h2>
                @if($appliedDiscount)
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-medium text-green-900 dark:text-green-400">{{ $discountCode }}</p>
                            <p class="text-sm text-green-700 dark:text-green-500">-${{ number_format($discountAmount, 2) }}</p>
                        </div>
                        <button
                            wire:click="removeDiscount"
                            class="text-red-500 hover:text-red-700"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                @else
                <div class="flex space-x-2">
                    <input
                        type="text"
                        wire:model="discountCode"
                        placeholder="Discount code"
                        class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                    <button
                        wire:click="applyDiscount"
                        class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700"
                    >
                        Apply
                    </button>
                </div>
                @endif
            </div>

            <!-- Held Transactions -->
            <div>
                <h2 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">Held Transactions</h2>
                <div class="space-y-2">
                    @forelse($heldTransactions as $held)
                    <button
                        wire:click="resumeTransaction({{ $held->id }})"
                        class="w-full text-left bg-gray-50 dark:bg-gray-700 rounded-lg p-3 hover:bg-gray-100 dark:hover:bg-gray-600"
                    >
                        <p class="font-medium text-gray-900 dark:text-white">{{ $held->reference }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ count($held->cart_items) }} items</p>
                        <p class="text-sm font-medium text-primary-600 dark:text-primary-400">${{ number_format($held->total_amount, 2) }}</p>
                        <p class="text-xs text-gray-400">{{ $held->created_at->diffForHumans() }}</p>
                    </button>
                    @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No held transactions</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    @if($showPaymentModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
            <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">Complete Payment</h2>

            <div class="space-y-4">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="flex justify-between text-xl font-bold">
                        <span class="text-gray-900 dark:text-white">Total Amount:</span>
                        <span class="text-primary-600 dark:text-primary-400">${{ number_format($totalAmount, 2) }}</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Method</label>
                    <select
                        wire:model="paymentMethod"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                        @foreach($paymentMethods as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Amount Paid</label>
                    <input
                        type="number"
                        wire:model.live="paidAmount"
                        step="0.01"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-lg font-bold"
                    >
                </div>

                @if($changeAmount > 0)
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                    <div class="flex justify-between text-xl font-bold">
                        <span class="text-green-900 dark:text-green-400">Change:</span>
                        <span class="text-green-600 dark:text-green-400">${{ number_format($changeAmount, 2) }}</span>
                    </div>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes (Optional)</label>
                    <textarea
                        wire:model="notes"
                        rows="2"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    ></textarea>
                </div>
            </div>

            <div class="mt-6 flex space-x-3">
                <button
                    wire:click="$set('showPaymentModal', false)"
                    class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 font-medium"
                >
                    Cancel
                </button>
                <button
                    wire:click="completeSale"
                    class="flex-1 px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium"
                    :disabled="paidAmount < totalAmount"
                >
                    Complete Sale
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Customer Search Modal -->
    @if($showCustomerModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
            <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">Select Customer</h2>

            <input
                type="text"
                wire:model.live="customerSearch"
                placeholder="Search by name, email, or phone..."
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white mb-4"
            >

            <div class="space-y-2 max-h-64 overflow-y-auto">
                @forelse($customers as $customer)
                <button
                    wire:click="selectCustomer({{ $customer->id }})"
                    class="w-full text-left bg-gray-50 dark:bg-gray-700 rounded-lg p-3 hover:bg-gray-100 dark:hover:bg-gray-600"
                >
                    <p class="font-medium text-gray-900 dark:text-white">{{ $customer->name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $customer->email }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $customer->phone }}</p>
                </button>
                @empty
                <p class="text-center text-gray-500 dark:text-gray-400 py-4">
                    @if(strlen($customerSearch) >= 2)
                    No customers found
                    @else
                    Start typing to search customers
                    @endif
                </p>
                @endforelse
            </div>

            <div class="mt-4">
                <button
                    wire:click="$set('showCustomerModal', false)"
                    class="w-full px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Flash Messages -->
    @if (session()->has('success'))
    <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
        {{ session('success') }}
    </div>
    @endif

    @if (session()->has('error'))
    <div class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
        {{ session('error') }}
    </div>
    @endif
</div>

@push('scripts')
<script>
    // Barcode Scanner Support
    let barcodeBuffer = '';
    let barcodeTimeout = null;

    document.addEventListener('keypress', function(e) {
        // Ignore if typing in an input field (except product search)
        if (document.activeElement.tagName === 'INPUT' && 
            !document.activeElement.classList.contains('product-search')) {
            return;
        }

        // Clear timeout
        clearTimeout(barcodeTimeout);

        // Add character to buffer
        if (e.key !== 'Enter') {
            barcodeBuffer += e.key;
        }

        // Set timeout to clear buffer
        barcodeTimeout = setTimeout(() => {
            if (barcodeBuffer.length > 3 && e.key === 'Enter') {
                // Trigger search with barcode
                @this.set('productSearch', barcodeBuffer);
                @this.call('searchProducts');
            }
            barcodeBuffer = '';
        }, 100);
    });

    // Receipt Printing
    document.addEventListener('livewire:initialized', () => {
        @this.on('print-receipt', (event) => {
            const saleId = event.saleId;
            window.open(`/pos/receipt/${saleId}/print`, '_blank', 'width=400,height=600');
        });
    });

    // Keyboard Shortcuts
    document.addEventListener('keydown', function(e) {
        // F2 - Focus search
        if (e.key === 'F2') {
            e.preventDefault();
            document.querySelector('input[wire\\:model\\.live="productSearch"]')?.focus();
        }

        // F3 - Open customer modal
        if (e.key === 'F3') {
            e.preventDefault();
            @this.set('showCustomerModal', true);
        }

        // F4 - Focus discount input
        if (e.key === 'F4') {
            e.preventDefault();
            document.querySelector('input[wire\\:model="discountCode"]')?.focus();
        }

        // F5 - Hold transaction
        if (e.key === 'F5') {
            e.preventDefault();
            @this.call('holdTransaction');
        }

        // F6 - Clear cart
        if (e.key === 'F6') {
            e.preventDefault();
            if (confirm('Clear cart?')) {
                @this.call('clearCart');
            }
        }

        // F9 - Open payment
        if (e.key === 'F9') {
            e.preventDefault();
            @this.call('openPaymentModal');
        }
    });

    // Auto-hide flash messages
    setTimeout(() => {
        document.querySelectorAll('.fixed.top-4').forEach(el => {
            el.style.transition = 'opacity 0.5s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 500);
        });
    }, 3000);
</script>
@endpush
