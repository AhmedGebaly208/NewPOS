<?php

namespace Modules\Core\Listeners;

use Illuminate\Support\Facades\Log;

abstract class BaseListener
{
    /**
     * Handle the event.
     */
    abstract public function handle($event): void;

    /**
     * Log event activity.
     */
    protected function logActivity(string $message, array $context = []): void
    {
        Log::info($message, array_merge([
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'timestamp' => now(),
        ], $context));
    }

    /**
     * Handle failed job.
     */
    public function failed($event, $exception): void
    {
        Log::error('Listener failed', [
            'listener' => static::class,
            'event' => get_class($event),
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
