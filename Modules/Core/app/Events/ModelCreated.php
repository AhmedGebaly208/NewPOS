<?php

namespace Modules\Core\Events;

class ModelCreated extends BaseEvent
{
    public $model;
    public $modelClass;

    public function __construct($model)
    {
        parent::__construct($model);
        $this->model = $model;
        $this->modelClass = get_class($model);
    }
}
