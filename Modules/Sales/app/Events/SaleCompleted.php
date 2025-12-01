<?php

namespace Modules\Sales\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Sales\Models\Sale;

class SaleCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Sale $sale
    ) {}
}
