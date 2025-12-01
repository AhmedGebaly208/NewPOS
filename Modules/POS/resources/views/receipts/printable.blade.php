<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $data['sale_number'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            max-width: 300px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .receipt {
            border: 1px solid #000;
            padding: 15px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        
        .store-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .store-info {
            font-size: 10px;
            color: #666;
        }
        
        .section {
            margin: 10px 0;
            padding: 5px 0;
        }
        
        .section-title {
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .info-line {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }
        
        .items {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 10px 0;
            margin: 10px 0;
        }
        
        .item {
            margin: 8px 0;
        }
        
        .item-name {
            font-weight: bold;
        }
        
        .item-details {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #666;
        }
        
        .totals {
            border-top: 2px solid #000;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .total-line {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }
        
        .total-line.grand-total {
            font-size: 16px;
            font-weight: bold;
            border-top: 2px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }
        
        .footer {
            text-align: center;
            border-top: 2px dashed #000;
            padding-top: 10px;
            margin-top: 10px;
            font-size: 10px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .receipt {
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="store-name">{{ $data['store_name'] }}</div>
            @if($data['store_address'])
            <div class="store-info">{{ $data['store_address'] }}</div>
            @endif
            @if($data['store_phone'])
            <div class="store-info">Tel: {{ $data['store_phone'] }}</div>
            @endif
            @if($data['store_email'])
            <div class="store-info">Email: {{ $data['store_email'] }}</div>
            @endif
        </div>

        <!-- Sale Info -->
        <div class="section">
            <div class="info-line">
                <span>Receipt #:</span>
                <strong>{{ $data['sale_number'] }}</strong>
            </div>
            <div class="info-line">
                <span>Date:</span>
                <span>{{ $data['date']->format('M d, Y H:i') }}</span>
            </div>
            <div class="info-line">
                <span>Cashier:</span>
                <span>{{ $data['cashier'] }}</span>
            </div>
            @if($data['customer'])
            <div class="info-line">
                <span>Customer:</span>
                <span>{{ $data['customer']['name'] }}</span>
            </div>
            @endif
        </div>

        <!-- Items -->
        <div class="items">
            @foreach($data['items'] as $item)
            <div class="item">
                <div class="item-name">{{ $item['name'] }}</div>
                <div class="item-details">
                    <span>{{ $item['quantity'] }} x ${{ number_format($item['unit_price'], 2) }}</span>
                    <strong>${{ number_format($item['total'], 2) }}</strong>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Totals -->
        <div class="totals">
            <div class="total-line">
                <span>Subtotal:</span>
                <span>${{ number_format($data['subtotal'], 2) }}</span>
            </div>
            
            @if($data['discount_amount'] > 0)
            <div class="total-line">
                <span>Discount {{ $data['discount_code'] ? '(' . $data['discount_code'] . ')' : '' }}:</span>
                <span>-${{ number_format($data['discount_amount'], 2) }}</span>
            </div>
            @endif
            
            @if($data['tax_amount'] > 0)
            <div class="total-line">
                <span>Tax:</span>
                <span>${{ number_format($data['tax_amount'], 2) }}</span>
            </div>
            @endif
            
            <div class="total-line grand-total">
                <span>TOTAL:</span>
                <span>${{ number_format($data['total_amount'], 2) }}</span>
            </div>
        </div>

        <!-- Payment -->
        <div class="section">
            <div class="section-title">Payment</div>
            @foreach($data['payments'] as $payment)
            <div class="info-line">
                <span>{{ $payment['method'] }}:</span>
                <span>${{ number_format($payment['amount'], 2) }}</span>
            </div>
            @endforeach
            
            <div class="info-line" style="margin-top: 8px;">
                <span>Paid:</span>
                <strong>${{ number_format($data['paid_amount'], 2) }}</strong>
            </div>
            
            @if($data['change_amount'] > 0)
            <div class="info-line">
                <span>Change:</span>
                <strong>${{ number_format($data['change_amount'], 2) }}</strong>
            </div>
            @endif
        </div>

        @if($data['notes'])
        <div class="section">
            <div class="section-title">Notes</div>
            <div style="font-size: 10px;">{{ $data['notes'] }}</div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your purchase!</p>
            <p>Please come again</p>
        </div>
    </div>

    <script>
        // Auto print when loaded
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
