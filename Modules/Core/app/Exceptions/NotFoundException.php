<?php

namespace Modules\Core\Exceptions;

class NotFoundException extends CoreException
{
    public function __construct(string $message = 'Resource not found', array $errors = [])
    {
        parent::__construct($message, 404, $errors);
    }
}
