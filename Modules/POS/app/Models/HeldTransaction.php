<?php

namespace Modules\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Customer\Models\Customer;
use App\Models\User;

class HeldTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'user_id',
        'customer_id',
        'cart_items',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'discount_code',
        'notes',
    ];

    protected $casts = [
        'cart_items' => 'array',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->reference)) {
                $transaction->reference = self::generateReference();
            }
        });
    }

    public static function generateReference(): string
    {
        return 'HOLD-' . strtoupper(uniqid());
    }
}
