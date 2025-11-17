<?php

namespace Modules\Product\Models;

use Modules\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Product extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'category_id',
        'sku',
        'barcode',
        'price',
        'cost',
        'stock_quantity',
        'low_stock_threshold',
        'unit',
        'tax_rate',
        'is_active',
        'track_stock',
        'allow_backorder',
        'image',
        'images',
        'variants',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'is_active' => 'boolean',
        'track_stock' => 'boolean',
        'allow_backorder' => 'boolean',
        'images' => 'array',
        'variants' => 'array',
    ];

    /**
     * Searchable fields for the search scope.
     */
    protected $searchable = ['name', 'sku', 'barcode', 'description'];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            
            if (empty($product->sku)) {
                $product->sku = self::generateSKU();
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Generate a unique SKU.
     */
    public static function generateSKU(): string
    {
        do {
            $sku = 'PRD-' . strtoupper(Str::random(8));
        } while (self::where('sku', $sku)->exists());

        return $sku;
    }

    /**
     * Generate a barcode.
     */
    public static function generateBarcode(): string
    {
        do {
            $barcode = str_pad(mt_rand(1, 999999999999), 12, '0', STR_PAD_LEFT);
        } while (self::where('barcode', $barcode)->exists());

        return $barcode;
    }

    /**
     * Check if product is low on stock.
     */
    public function isLowStock(): bool
    {
        return $this->track_stock && $this->stock_quantity <= $this->low_stock_threshold;
    }

    /**
     * Check if product is out of stock.
     */
    public function isOutOfStock(): bool
    {
        return $this->track_stock && $this->stock_quantity <= 0;
    }

    /**
     * Check if product is available for sale.
     */
    public function isAvailable(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if (!$this->track_stock) {
            return true;
        }

        return $this->stock_quantity > 0 || $this->allow_backorder;
    }

    /**
     * Calculate price with tax.
     */
    public function getPriceWithTaxAttribute(): float
    {
        return $this->price + ($this->price * ($this->tax_rate / 100));
    }

    /**
     * Calculate profit margin.
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->cost == 0) {
            return 0;
        }

        return (($this->price - $this->cost) / $this->cost) * 100;
    }

    /**
     * Scope to get only in-stock products.
     */
    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->where('track_stock', false)
              ->orWhere('stock_quantity', '>', 0);
        });
    }

    /**
     * Scope to get low stock products.
     */
    public function scopeLowStock($query)
    {
        return $query->where('track_stock', true)
                     ->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
    }

    /**
     * Scope to get out of stock products.
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('track_stock', true)
                     ->where('stock_quantity', '<=', 0);
    }

    /**
     * Scope to get products by category.
     */
    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
}
