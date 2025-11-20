<?php

namespace Modules\Inventory\Models;

use Modules\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'address',
        'city',
        'country',
        'tax_number',
        'notes',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Searchable fields for the search scope.
     */
    protected $searchable = ['name', 'email', 'phone', 'company'];

    /**
     * Get the purchase orders for the supplier.
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Scope to get only active suppliers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
