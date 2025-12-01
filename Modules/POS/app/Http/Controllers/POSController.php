<?php

namespace Modules\POS\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Sales\Models\Sale;
use Modules\POS\Services\ReceiptService;

class POSController extends Controller
{
    public function __construct(
        protected ReceiptService $receiptService
    ) {}

    public function printReceipt($saleId)
    {
        $sale = Sale::findOrFail($saleId);
        
        // Check if user has permission to view this sale
        if ($sale->user_id !== auth()->id() && !auth()->user()->can('view_any_sales')) {
            abort(403);
        }
        
        return $this->receiptService->generatePrintableHTML($sale);
    }

    public function viewReceipt($saleId)
    {
        $sale = Sale::findOrFail($saleId);
        
        // Check if user has permission to view this sale
        if ($sale->user_id !== auth()->id() && !auth()->user()->can('view_any_sales')) {
            abort(403);
        }
        
        $data = $this->receiptService->generateReceipt($sale);
        
        return view('pos::receipts.printable', compact('data'));
    }
}
