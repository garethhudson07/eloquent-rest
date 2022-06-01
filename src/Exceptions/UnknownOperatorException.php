<?php

namespace EloquentRest\Exceptions;

use EloquentRest\Models\Contracts\ModelInterface;

class UnknownOperatorException extends ModelException
{
    public function __construct(ModelInterface $model, $operator)
    {
        $operator = (string) $operator;

        parent::__construct($model, "The supplied query operator '$operator' is not valid", 500);
    }
}
