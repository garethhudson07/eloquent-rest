<?php

namespace EloquentRest\Exceptions;

use EloquentRest\Models\Contracts\ModelInterface;

class UnknownOperatorException extends ModelException
{
    /**
     * @param ModelInterface $model
     * @param mixed $operator
     * @return void
     */
    public function __construct(ModelInterface $model, $operator)
    {
        $operator = (string) $operator;

        parent::__construct($model, "The supplied query operator '$operator' is not valid", 500);
    }
}
