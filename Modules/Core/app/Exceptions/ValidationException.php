<?php

namespace Modules\Core\Exceptions;

class ValidationException extends CoreException
{
    public function __construct(string $message = 'Validation failed', array $errors = [])
    {
        parent::__construct($message, 422, $errors);
    }
}
