<?php

namespace Modules\Inventory\Models;

use Modules\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'reference_number',
        'supplier_id',
        'order_date',
        'expected_date',
        'received_date',
        'status',
        'subtotal',
        'tax',
        'shipping',
        'discount',
        'total',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
        'received_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Searchable fields for the search scope.
     */
    protected $searchable = ['reference_number', 'notes'];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($po) {
            if (empty($po->reference_number)) {
                $po->reference_number = self::generateReferenceNumber();
            }
        });
    }

    /**
     * Get the supplier for the purchase order.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the items for the purchase order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Generate a unique reference number.
     */
    public static function generateReferenceNumber(): string
    {
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', now())->count() + 1;
        return 'PO-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Check if purchase order is editable.
     */
    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'pending']);
    }

    /**
     * Check if purchase order can be received.
     */
    public function canBeReceived(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Mark as received.
     */
    public function markAsReceived(): void
    {
        $this->update([
            'status' => 'received',
            'received_date' => now(),
        ]);
    }

    /**
     * Scope to get purchase orders by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
