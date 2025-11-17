<?php

namespace Modules\Core\Traits;

use Illuminate\Support\Facades\Auth;

trait Auditable
{
    /**
     * Boot the auditable trait for a model.
     */
    public static function bootAuditable()
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        static::deleting(function ($model) {
            if (Auth::check() && !$model->isForceDeleting()) {
                $model->deleted_by = Auth::id();
                $model->save();
            }
        });
    }

    /**
     * Get the user who created the record.
     */
    public function creator()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    /**
     * Get the user who last updated the record.
     */
    public function updater()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'updated_by');
    }

    /**
     * Get the user who deleted the record.
     */
    public function deleter()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'deleted_by');
    }
}
