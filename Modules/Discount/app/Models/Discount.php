<?php

namespace Modules\Discount\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\BaseModel;
use Modules\Product\Models\Product;
use Modules\Product\Models\Category;
use Modules\Customer\Models\Customer;

/**
 * Discount Model
 * 
 * Manages discount rules and promotions
 */
class Discount extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'type',
        'value',
        'buy_quantity',
        'get_quantity',
        'applies_to',
        'minimum_purchase',
        'minimum_quantity',
        'customer_eligibility',
        'eligible_customer_tiers',
        'starts_at',
        'ends_at',
        'usage_limit',
        'usage_limit_per_customer',
        'times_used',
        'is_active',
        'priority',
        'is_combinable',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_purchase' => 'decimal:2',
        'eligible_customer_tiers' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'is_combinable' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /**
     * Searchable fields
     */
    public static array $searchable = [
        'name',
        'description',
        'type',
    ];

    /**
     * Get products associated with this discount
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'discount_product')
            ->withTimestamps();
    }

    /**
     * Get categories associated with this discount
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'discount_category')
            ->withTimestamps();
    }

    /**
     * Get customers associated with this discount
     */
    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'discount_customer')
            ->withTimestamps();
    }

    /**
     * Get coupons for this discount
     */
    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    /**
     * Scope: Active discounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Currently valid discounts (within date range)
     */
    public function scopeValid($query)
    {
        $now = now();
        return $query->where(function ($q) use ($now) {
            $q->where(function ($sq) use ($now) {
                $sq->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })->where(function ($sq) use ($now) {
                $sq->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $now);
            });
        });
    }

    /**
     * Scope: Not exceeded usage limit
     */
    public function scopeWithinLimit($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('usage_limit')
                ->orWhereRaw('times_used < usage_limit');
        });
    }

    /**
     * Scope: Available discounts (active, valid, within limit)
     */
    public function scopeAvailable($query)
    {
        return $query->active()->valid()->withinLimit();
    }

    /**
     * Scope: Order by priority (higher first)
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    /**
     * Check if discount is currently valid
     */
    public function isValid(): bool
    {
        $now = now();
        
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isAfter($now)) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isBefore($now)) {
            return false;
        }

        return true;
    }

    /**
     * Check if discount has remaining uses
     */
    public function hasUsesRemaining(): bool
    {
        if (!$this->usage_limit) {
            return true;
        }

        return $this->times_used < $this->usage_limit;
    }

    /**
     * Check if customer is eligible for this discount
     */
    public function isCustomerEligible(?Customer $customer = null): bool
    {
        if ($this->customer_eligibility === 'all') {
            return true;
        }

        if (!$customer) {
            return false;
        }

        if ($this->customer_eligibility === 'customer_tier') {
            $tiers = $this->eligible_customer_tiers ?? [];
            return in_array($customer->tier, $tiers);
        }

        if ($this->customer_eligibility === 'specific_customers') {
            return $this->customers()->where('customer_id', $customer->id)->exists();
        }

        return false;
    }

    /**
     * Check if discount applies to product
     */
    public function appliesToProduct(Product $product): bool
    {
        if ($this->applies_to === 'all') {
            return true;
        }

        if ($this->applies_to === 'specific_products') {
            return $this->products()->where('product_id', $product->id)->exists();
        }

        if ($this->applies_to === 'specific_categories' && $product->category_id) {
            return $this->categories()->where('category_id', $product->category_id)->exists();
        }

        return false;
    }

    /**
     * Calculate discount amount for a given total
     */
    public function calculateAmount(float $total, int $quantity = 1): float
    {
        if ($this->type === 'percentage') {
            return round($total * ($this->value / 100), 2);
        }

        if ($this->type === 'fixed') {
            return min($this->value, $total);
        }

        if ($this->type === 'buy_x_get_y' && $this->buy_quantity && $this->get_quantity) {
            $freeItems = floor($quantity / ($this->buy_quantity + $this->get_quantity)) * $this->get_quantity;
            $itemPrice = $total / $quantity;
            return round($freeItems * $itemPrice, 2);
        }

        return 0;
    }

    /**
     * Increment usage counter
     */
    public function incrementUsage(): void
    {
        $this->increment('times_used');
    }

    /**
     * Get usage percentage
     */
    public function getUsagePercentageAttribute(): ?float
    {
        if (!$this->usage_limit) {
            return null;
        }

        return round(($this->times_used / $this->usage_limit) * 100, 2);
    }
}
