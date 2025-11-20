<?php

namespace Modules\Inventory\Services;

use Modules\Core\Services\BaseService;
use Modules\Inventory\Models\StockMovement;
use Modules\Product\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventoryService extends BaseService
{
    /**
     * Record a stock movement and update product quantity.
     */
    public function recordMovement(
        int $productId,
        string $type,
        int $quantity,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): StockMovement {
        return DB::transaction(function () use (
            $productId,
            $type,
            $quantity,
            $referenceType,
            $referenceId,
            $notes
        ) {
            $product = Product::findOrFail($productId);
            
            $quantityBefore = $product->stock_quantity;
            
            // Update product stock based on movement type
            if (in_array($type, ['purchase', 'return', 'adjustment']) && $quantity > 0) {
                $product->stock_quantity += $quantity;
            } elseif (in_array($type, ['sale', 'adjustment']) && $quantity < 0) {
                $product->stock_quantity += $quantity; // quantity is already negative
            } elseif ($type === 'initial') {
                $product->stock_quantity = $quantity;
            }
            
            $product->save();
            
            $quantityAfter = $product->stock_quantity;
            
            // Record the movement
            $movement = StockMovement::create([
                'product_id' => $productId,
                'type' => $type,
                'quantity' => $quantity,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'created_by' => Auth::id(),
            ]);
            
            return $movement;
        });
    }

    /**
     * Add stock (purchase or return).
     */
    public function addStock(
        int $productId,
        int $quantity,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): StockMovement {
        return $this->recordMovement(
            $productId,
            'purchase',
            abs($quantity),
            $referenceType,
            $referenceId,
            $notes
        );
    }

    /**
     * Remove stock (sale).
     */
    public function removeStock(
        int $productId,
        int $quantity,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): StockMovement {
        return $this->recordMovement(
            $productId,
            'sale',
            -abs($quantity),
            $referenceType,
            $referenceId,
            $notes
        );
    }

    /**
     * Adjust stock (can be positive or negative).
     */
    public function adjustStock(
        int $productId,
        int $quantity,
        ?string $notes = null
    ): StockMovement {
        return $this->recordMovement(
            $productId,
            'adjustment',
            $quantity,
            null,
            null,
            $notes
        );
    }

    /**
     * Set initial stock.
     */
    public function setInitialStock(
        int $productId,
        int $quantity,
        ?string $notes = null
    ): StockMovement {
        return $this->recordMovement(
            $productId,
            'initial',
            $quantity,
            null,
            null,
            $notes
        );
    }

    /**
     * Get low stock products.
     */
    public function getLowStockProducts()
    {
        return Product::lowStock()
            ->where('is_active', true)
            ->where('track_stock', true)
            ->with('category')
            ->get();
    }

    /**
     * Get out of stock products.
     */
    public function getOutOfStockProducts()
    {
        return Product::outOfStock()
            ->where('is_active', true)
            ->where('track_stock', true)
            ->with('category')
            ->get();
    }

    /**
     * Get stock movements for a product.
     */
    public function getProductMovements(int $productId, int $days = 30)
    {
        return StockMovement::where('product_id', $productId)
            ->where('created_at', '>=', now()->subDays($days))
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Calculate stock value for a product.
     */
    public function calculateStockValue(int $productId): float
    {
        $product = Product::findOrFail($productId);
        return $product->stock_quantity * $product->cost;
    }

    /**
     * Calculate total inventory value.
     */
    public function calculateTotalInventoryValue(): float
    {
        return Product::where('track_stock', true)
            ->where('is_active', true)
            ->get()
            ->sum(function ($product) {
                return $product->stock_quantity * $product->cost;
            });
    }

    /**
     * Check if product has sufficient stock.
     */
    public function hasSufficientStock(int $productId, int $quantity): bool
    {
        $product = Product::findOrFail($productId);
        
        if (!$product->track_stock) {
            return true;
        }
        
        if ($product->allow_backorder) {
            return true;
        }
        
        return $product->stock_quantity >= $quantity;
    }

    /**
     * Reserve stock (for pending orders).
     */
    public function reserveStock(int $productId, int $quantity): bool
    {
        // This can be implemented later with a separate reserved_stock column
        return $this->hasSufficientStock($productId, $quantity);
    }
}
