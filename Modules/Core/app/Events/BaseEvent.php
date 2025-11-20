<?php

namespace Modules\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class BaseEvent
{
    use Dispatchable, SerializesModels;

    public $data;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct($data = null)
    {
        $this->data = $data;
        $this->timestamp = now();
    }

    /**
     * Get the event data.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the event timestamp.
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
}
