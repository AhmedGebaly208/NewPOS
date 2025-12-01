# POS Module

## Overview
The POS (Point of Sale) module provides a comprehensive interface for processing sales transactions in real-time. It includes cart management, customer selection, discount application, payment processing, and receipt generation.

## Features

### 1. Product Management
- **Product Search**: Real-time search by product name or SKU
- **Add to Cart**: Quick add products with auto-complete
- **Cart Management**: Add, remove, update quantities
- **Stock Validation**: Only shows products with available stock

### 2. Cart Operations
- **Quantity Control**: Increase/decrease with +/- buttons
- **Remove Items**: Individual item removal
- **Clear Cart**: Reset entire cart (F6)
- **Real-time Calculations**: Automatic subtotal, discount, tax, and total

### 3. Customer Integration
- **Customer Search**: Search by name, email, or phone
- **Walk-in Customers**: Optional customer selection
- **Customer History**: Access to customer purchase data
- **Quick Selection**: Modal interface for fast customer lookup

### 4. Discount Application
- **Discount Codes**: Apply promotional codes
- **Real-time Validation**: Instant discount verification
- **Automatic Calculation**: Discount applied to totals
- **Remove Option**: Easy discount removal

### 5. Payment Processing
- **Multiple Methods**: Cash, Card, Mobile Money, Bank Transfer
- **Change Calculation**: Automatic change computation
- **Split Payments**: Support for multiple payment methods
- **Payment Confirmation**: Modal confirmation before completing

### 6. Transaction Management
- **Hold Transactions**: Save cart for later (F5)
- **Resume Transactions**: Continue held transactions
- **Transaction History**: View all held transactions
- **Auto-reference**: Unique reference number generation

### 7. Receipt Generation
- **Print Receipt**: Professional receipt layout
- **Auto-print Option**: Configurable auto-print
- **Receipt View**: View receipt in browser
- **Customer Copy**: Printable format

### 8. Keyboard Shortcuts
- **F2**: Focus product search
- **F3**: Open customer selection
- **F4**: Focus discount input
- **F5**: Hold transaction
- **F6**: Clear cart (with confirmation)
- **F9**: Open payment modal

### 9. Barcode Scanner Support
- **Hardware Integration**: USB barcode scanner support
- **Auto-search**: Automatic product search on scan
- **Buffer Management**: Handles rapid scans
- **Configurable**: Prefix/suffix settings

## Models

### HeldTransaction
Stores temporarily held transactions:
- **reference**: Unique identifier (HOLD-XXXXXXXX)
- **user_id**: Cashier who held transaction
- **customer_id**: Associated customer (optional)
- **cart_items**: JSON array of cart contents
- **subtotal, discount_amount, tax_amount, total_amount**: Financial data
- **discount_code**: Applied discount code
- **notes**: Additional notes

## Services

### ReceiptService
Handles receipt generation:
- `generateReceipt(Sale $sale)`: Generate receipt data array
- `generateHTML(Sale $sale)`: Generate HTML receipt
- `generatePrintableHTML(Sale $sale)`: Generate printable receipt

## Livewire Component

### POSInterface
Main POS interface component with properties:
- **Cart**: `$cart`, `$subtotal`, `$taxAmount`, `$totalAmount`
- **Customer**: `$customerId`, `$selectedCustomer`
- **Discount**: `$discountCode`, `$discountAmount`
- **Payment**: `$paymentMethod`, `$paidAmount`, `$changeAmount`
- **UI State**: `$showPaymentModal`, `$showCustomerModal`

#### Public Methods:
- `searchProducts()`: Search products
- `addToCart($productId)`: Add product to cart
- `removeFromCart($index)`: Remove item from cart
- `updateQuantity($index, $quantity)`: Update item quantity
- `applyDiscount()`: Apply discount code
- `selectCustomer($customerId)`: Select customer
- `openPaymentModal()`: Open payment interface
- `completeSale()`: Process sale transaction
- `holdTransaction()`: Hold current transaction
- `resumeTransaction($transactionId)`: Resume held transaction
- `clearCart()`: Clear entire cart

## Routes

```php
// Main POS interface
GET /pos

// Receipt routes
GET /pos/receipt/{sale}         // View receipt
GET /pos/receipt/{sale}/print   // Print receipt
```

## Configuration

Configuration file: `config/config.php`

```php
return [
    'store_name' => 'Your Store',
    'store_address' => 'Store Address',
    'store_phone' => 'Phone Number',
    'store_email' => 'Email Address',
    
    'barcode_enabled' => true,
    'barcode_prefix' => '',
    'barcode_suffix' => 'Enter',
    
    'auto_clear_cart' => true,
    'auto_print_receipt' => false,
    'show_out_of_stock' => false,
    'max_cart_items' => 100,
    
    'shortcuts' => [
        'search' => 'F2',
        'customer' => 'F3',
        'discount' => 'F4',
        'hold' => 'F5',
        'clear' => 'F6',
        'pay' => 'F9',
    ],
];
```

## Environment Variables

```env
STORE_NAME="My Store"
STORE_ADDRESS="123 Main St"
STORE_PHONE="+1234567890"
STORE_EMAIL="store@example.com"

POS_BARCODE_ENABLED=true
POS_BARCODE_PREFIX=""
POS_BARCODE_SUFFIX="Enter"
POS_AUTO_PRINT=false
```

## Database Schema

### held_transactions
- id
- reference (unique)
- user_id
- customer_id (nullable)
- cart_items (JSON)
- subtotal, discount_amount, tax_amount, total_amount
- discount_code (nullable)
- notes (nullable)
- timestamps
- Index: (user_id, created_at)

## Dependencies

**POS Module depends on:**
- **Core**: Foundation functionality
- **Product**: Product data and inventory
- **Customer**: Customer management
- **Sales**: Sale processing via SaleService
- **Discount**: Discount validation and application
- **Inventory**: Stock validation

## Usage

### Accessing POS
Navigate to `/pos` after authentication.

### Processing a Sale

1. **Search Products**: Use F2 or click search box
2. **Add to Cart**: Click product or scan barcode
3. **Select Customer**: Optional, use F3
4. **Apply Discount**: Optional, use F4
5. **Open Payment**: Click "Pay" or press F9
6. **Complete Sale**: Enter payment amount and complete

### Holding a Transaction

1. Add items to cart
2. Press F5 or click "Hold"
3. Transaction saved with unique reference
4. Resume later from sidebar

### Using Barcode Scanner

1. Ensure barcode scanner is configured as keyboard input
2. Scan product barcode
3. Product automatically added to cart
4. Works in background while on POS screen

### Keyboard Shortcuts Quick Reference

| Key | Action |
|-----|--------|
| F2  | Focus product search |
| F3  | Open customer selection |
| F4  | Focus discount input |
| F5  | Hold transaction |
| F6  | Clear cart |
| F9  | Open payment modal |

## Barcode Scanner Setup

### Hardware Requirements
- USB barcode scanner configured as keyboard wedge
- Scanner should send Enter key after scan

### Configuration
1. Set scanner to keyboard emulation mode
2. Configure suffix character (usually Enter)
3. Test with product SKU/barcode
4. Adjust `barcode_prefix` and `barcode_suffix` if needed

### Supported Formats
- EAN-13
- UPC-A
- Code-128
- QR Codes (if scanner supports)

## Receipt Printing

### Browser Printing
- Receipts open in new window
- Use browser print dialog (Ctrl+P)
- Optimized for 80mm thermal printers

### Auto-Print
- Enable in config: `POS_AUTO_PRINT=true`
- Automatically opens print dialog after sale
- Can be disabled per user preference

### Receipt Customization
Edit receipt template:
`Modules/POS/resources/views/receipts/printable.blade.php`

## Troubleshooting

### Products Not Showing
- Check product status (must be 'active')
- Verify inventory quantity > 0
- Check `show_out_of_stock` config

### Barcode Scanner Not Working
- Verify scanner is in keyboard emulation mode
- Check suffix character configuration
- Test scanner with text editor first
- Ensure focus is not on input field

### Discount Not Applying
- Verify discount code is active
- Check discount validity period
- Confirm customer eligibility
- Review minimum purchase requirements

### Receipt Not Printing
- Check browser popup blocker
- Verify printer connection
- Test browser print functionality
- Check receipt route permissions

## Security

- Authentication required for all POS routes
- Sale viewing restricted to creator or users with permission
- CSRF protection on all forms
- Validation on all inputs
- Transaction integrity with database transactions

## Performance Tips

1. **Product Search**: Limited to 10 results for performance
2. **Customer Search**: Requires minimum 2 characters
3. **Held Transactions**: Automatically cleaned up (can add cron job)
4. **Cart Limit**: Maximum 100 items (configurable)

## Future Enhancements

- [ ] Split payment UI
- [ ] Cash drawer integration
- [ ] Receipt email functionality
- [ ] Offline mode support
- [ ] Touch screen optimization
- [ ] Product images in cart
- [ ] Customer display (second screen)
- [ ] Refund processing from POS
- [ ] Day-end reporting
- [ ] Multiple cash registers

## API Integration

The POS module can be extended with API endpoints for:
- Mobile POS applications
- Self-service kiosks
- Online ordering integration
- Third-party POS hardware

## Testing

Run POS module tests:
```bash
php artisan test Modules/POS/Tests
```

## Notes

- POS interface is optimized for desktop/tablet use
- Minimum screen resolution: 1024x768
- Works best with Chrome/Firefox
- Touch-friendly for tablet POS systems
- Automatic session timeout prevention during active use
- Real-time validation for better UX
- Responsive design for various screen sizes

## Support

For issues or questions:
- Check documentation
- Review configuration
- Test with sample data
- Contact system administrator
