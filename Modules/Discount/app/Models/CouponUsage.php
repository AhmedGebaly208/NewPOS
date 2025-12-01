<?php

namespace Modules\Discount\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Customer\Models\Customer;

/**
 * CouponUsage Model
 * 
 * Tracks when and by whom coupons were used
 */
class CouponUsage extends Model
{
    protected $table = 'coupon_usage';

    protected $fillable = [
        'coupon_id',
        'customer_id',
        'sale_id',
        'discount_amount',
        'used_at',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'used_at' => 'datetime',
    ];

    /**
     * Get the coupon
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
