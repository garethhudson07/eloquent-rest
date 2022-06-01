<?php

namespace EloquentRest\Exceptions;

use EloquentRest\Models\Contracts\ModelInterface;

class InvalidQueryArgumentsException extends ModelException
{
    public function __construct(ModelInterface $model)
    {
        parent::__construct($model, "An invalid set of query arguments was provided", 500);
    }
}
