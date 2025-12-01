<?php

namespace Modules\Discount\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\BaseModel;
use Modules\Customer\Models\Customer;

/**
 * Coupon Model
 * 
 * Manages coupon codes linked to discounts
 */
class Coupon extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'discount_id',
        'starts_at',
        'ends_at',
        'usage_limit',
        'usage_limit_per_customer',
        'times_used',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /**
     * Searchable fields
     */
    public static array $searchable = [
        'code',
    ];

    /**
     * Get the discount that owns this coupon
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    /**
     * Get usage records for this coupon
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Scope: Active coupons
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Currently valid coupons (within date range)
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
     * Scope: Available coupons
     */
    public function scopeAvailable($query)
    {
        return $query->active()->valid()->withinLimit();
    }

    /**
     * Scope: Find by code
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', strtoupper($code));
    }

    /**
     * Check if coupon is currently valid
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
     * Check if coupon has remaining uses
     */
    public function hasUsesRemaining(): bool
    {
        if (!$this->usage_limit) {
            return true;
        }

        return $this->times_used < $this->usage_limit;
    }

    /**
     * Check if customer can use this coupon
     */
    public function canBeUsedByCustomer(?Customer $customer = null): bool
    {
        if (!$this->usage_limit_per_customer || !$customer) {
            return true;
        }

        $customerUsages = $this->usages()
            ->where('customer_id', $customer->id)
            ->count();

        return $customerUsages < $this->usage_limit_per_customer;
    }

    /**
     * Check if coupon is available
     */
    public function isAvailable(?Customer $customer = null): bool
    {
        return $this->isValid() 
            && $this->hasUsesRemaining() 
            && $this->canBeUsedByCustomer($customer);
    }

    /**
     * Increment usage counter
     */
    public function incrementUsage(): void
    {
        $this->increment('times_used');
    }

    /**
     * Record usage
     */
    public function recordUsage(?int $customerId = null, ?int $saleId = null, float $discountAmount = 0): void
    {
        CouponUsage::create([
            'coupon_id' => $this->id,
            'customer_id' => $customerId,
            'sale_id' => $saleId,
            'discount_amount' => $discountAmount,
            'used_at' => now(),
        ]);

        $this->incrementUsage();
        $this->discount?->incrementUsage();
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

    /**
     * Automatically uppercase code on set
     */
    public function setCodeAttribute($value): void
    {
        $this->attributes['code'] = strtoupper($value);
    }
}
