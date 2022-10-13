<?php

namespace EloquentRest\Exceptions;

use EloquentRest\Models\Contracts\ModelInterface;
use RuntimeException;
use Throwable;

class ModelException extends RuntimeException
{
    /**
     * The model.
     *
     * @var ModelInterface
     */
    protected ModelInterface $model;

    /**
     * Create a new ModelException instance.
     *
     * @param ModelInterface $model
     * @param null|string $message
     * @param int $code
     * @param null|Throwable $previous
     * @return void
     */
    public function __construct(ModelInterface $model, ?string $message = null, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message ?? '', $code, $previous);

        $this->model = $model;
    }

    /**
     * Get the model.
     *
     * @return ModelInterface
     */
    public function getModel(): ModelInterface
    {
        return $this->model;
    }
}
