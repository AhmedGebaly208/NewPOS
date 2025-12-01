# Sales Module

## Overview
The Sales module manages all sales transactions, payments, and related operations in the POS application.

## Features

### 1. Sales Management
- Complete sales transaction processing
- Multiple payment methods support
- Automatic sale number generation
- Customer association (optional)
- Discount application
- Tax calculation

### 2. Sale Items Tracking
- Product snapshot at time of sale
- Quantity and pricing tracking
- Item-level discounts
- Tax per item

### 3. Payment Processing
- Multiple payment methods:
  - Cash
  - Card
  - Mobile Money
  - Bank Transfer
  - Other
- Split payments support
- Payment reference tracking

### 4. Sale Operations
- View sale details
- Void sales (with reason)
- Refund processing
- Sale history

## Models

### Sale
Main transaction model containing:
- Sale number (auto-generated)
- Customer reference
- User/Cashier reference
- Status (pending, completed, voided, refunded)
- Financial totals (subtotal, discount, tax, total, paid, change)
- Timestamps and audit trail

### SaleItem
Individual line items in a sale:
- Product reference and snapshot
- Quantity and pricing
- Discounts and taxes
- Calculated totals

### Payment
Payment records for each sale:
- Payment method
- Amount
- Reference number
- Notes

## Events

### SaleCompleted
Dispatched when a sale is successfully completed.
- Triggers inventory updates
- Updates customer purchase history
- Records discount usage

### SaleVoided
Dispatched when a sale is voided.
- Restores inventory
- Reverses discount usage
- Creates audit trail

## Services

### SaleService
Main service class handling:
- `createSale()` - Create new sale transaction
- `voidSale()` - Void existing sale
- `getSaleDetails()` - Retrieve complete sale information
- `getDailySales()` - Get sales summary for a date

## Filament Resources

### SaleResource
Admin interface for viewing sales:
- List all sales with filters
- View detailed sale information
- Void sales action
- Sales statistics widgets

### Widgets
- **SalesStatsWidget**: Daily and monthly sales overview

## Configuration

Configuration file: `config/config.php`

```php
return [
    'tax_rate' => 0,                    // Tax rate percentage
    'currency' => 'USD',                // Currency code
    'currency_symbol' => '$',           // Currency symbol
    'sale_number_prefix' => 'SL',       // Sale number prefix
    'payment_methods' => [...],         // Available payment methods
];
```

## Database Schema

### sales
- id
- sale_number (unique)
- customer_id (nullable)
- user_id (cashier)
- status
- subtotal, discount_amount, tax_amount
- total_amount, paid_amount, change_amount
- discount_id, discount_code
- notes
- completed_at, voided_at
- voided_by, void_reason
- timestamps, soft deletes

### sale_items
- id
- sale_id
- product_id
- product_name, product_sku (snapshot)
- unit_price, quantity
- discount_amount, tax_amount
- subtotal, total
- timestamps

### payments
- id
- sale_id
- payment_method
- amount
- reference
- notes
- timestamps

## Dependencies

This module depends on:
- **Core**: Foundation functionality
- **Product**: Product references
- **Customer**: Customer associations
- **Inventory**: Stock management
- **Discount**: Discount applications

## Usage Examples

### Create a Sale

```php
use Modules\Sales\Services\SaleService;

$saleService = app(SaleService::class);

$sale = $saleService->createSale([
    'customer_id' => 1,
    'user_id' => auth()->id(),
    'items' => [
        [
            'product_id' => 1,
            'quantity' => 2,
        ],
        [
            'product_id' => 2,
            'quantity' => 1,
        ],
    ],
    'discount_code' => 'SAVE10',
    'paid_amount' => 100.00,
    'change_amount' => 5.00,
    'payments' => [
        [
            'payment_method' => 'cash',
            'amount' => 100.00,
        ],
    ],
]);
```

### Void a Sale

```php
$saleService->voidSale(
    saleId: $sale->id,
    userId: auth()->id(),
    reason: 'Customer request'
);
```

### Get Daily Sales

```php
$dailySales = $saleService->getDailySales(now());
// Returns: total_sales, total_revenue, total_discount, sales collection
```

## Testing

Run module tests:
```bash
php artisan test Modules/Sales/Tests
```

## Seeding

Seed sample sales data:
```bash
php artisan module:seed Sales
```

## API Endpoints

(To be implemented in future phases)

## Events & Listeners

### Published Events
- `SaleCompleted` - When sale is completed
- `SaleVoided` - When sale is voided

### Listens To
- (None currently)

## Permissions

Required permissions (via Spatie):
- `view_sales` - View sales list
- `view_any_sales` - View all sales
- `void_sales` - Void sales

## Notes

- Sales cannot be edited after creation
- Sales can only be voided, not deleted
- All sales must have at least one payment
- Inventory is automatically updated on sale creation
- Sale numbers are generated automatically with format: SL-YYYYMMDD-####
