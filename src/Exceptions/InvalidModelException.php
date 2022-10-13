<?php

namespace EloquentRest\Exceptions;

use EloquentRest\Models\Contracts\ModelInterface;
use Throwable;

class InvalidModelException extends ModelException
{
    /**
     * A list of the exceptions errors.
     *
     * @var array
     */
    protected array $errors = [];

    /**
     * Create a new InvalidModelException instance.
     *
     * @param ModelInterface $model
     * @param null|string $message
     * @param array $errors
     * @param int $code
     * @param null|Throwable $previous
     * @return void
     */
    public function __construct(ModelInterface $model, ?string $message = null, array $errors = [], int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($model, $message, $code, $previous);

        $this->errors = $errors;
    }

    /**
     * Get the exceptions errors array.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
