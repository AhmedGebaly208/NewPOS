<?php

namespace Modules\Core\Events;

class ModelUpdated extends BaseEvent
{
    public $model;
    public $modelClass;
    public $changes;

    public function __construct($model)
    {
        parent::__construct($model);
        $this->model = $model;
        $this->modelClass = get_class($model);
        $this->changes = $model->getChanges();
    }
}
