<?php

namespace Modules\Customer\Models;

use Modules\Core\Models\BaseModel;

class Customer extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'phone_secondary',
        'date_of_birth',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'notes',
        'total_spent',
        'total_orders',
        'last_order_date',
        'loyalty_points',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'last_order_date' => 'date',
        'total_spent' => 'decimal:2',
        'total_orders' => 'integer',
        'loyalty_points' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Searchable fields for the search scope.
     */
    protected $searchable = ['name', 'email', 'phone'];

    /**
     * Calculate average order value.
     */
    public function getAverageOrderValueAttribute(): float
    {
        if ($this->total_orders == 0) {
            return 0;
        }
        
        return $this->total_spent / $this->total_orders;
    }

    /**
     * Scope to get active customers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get VIP customers (top spenders).
     */
    public function scopeVip($query, float $minimumSpent = 1000)
    {
        return $query->where('total_spent', '>=', $minimumSpent)
                     ->orderBy('total_spent', 'desc');
    }

    /**
     * Scope to get customers with recent purchases.
     */
    public function scopeRecentlyActive($query, int $days = 30)
    {
        return $query->where('last_order_date', '>=', now()->subDays($days));
    }

    /**
     * Scope to get inactive customers.
     */
    public function scopeInactive($query, int $days = 90)
    {
        return $query->where('last_order_date', '<', now()->subDays($days))
                     ->orWhereNull('last_order_date');
    }

    /**
     * Check if customer is VIP.
     */
    public function isVip(float $threshold = 1000): bool
    {
        return $this->total_spent >= $threshold;
    }

    /**
     * Get customer tier based on spending.
     */
    public function getTierAttribute(): string
    {
        if ($this->total_spent >= 5000) {
            return 'platinum';
        } elseif ($this->total_spent >= 2000) {
            return 'gold';
        } elseif ($this->total_spent >= 500) {
            return 'silver';
        }
        
        return 'bronze';
    }
}
