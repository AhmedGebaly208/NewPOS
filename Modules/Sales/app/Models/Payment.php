<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'payment_method',
        'amount',
        'reference',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function isCash(): bool
    {
        return $this->payment_method === 'cash';
    }

    public function isCard(): bool
    {
        return $this->payment_method === 'card';
    }
}
