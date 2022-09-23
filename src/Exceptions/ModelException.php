<?php

namespace EloquentRest\Exceptions;

use EloquentRest\Models\Contracts\ModelInterface;
use Exception;
use RuntimeException;

class ModelException extends RuntimeException
{
    /**
     * The model.
     *
     * @var ModelInterface
     */
    protected ModelInterface $model;

    /**
     * Create a new InvalidModelException instance.
     *
     *
     * @param string    $message
     * @param array     $errors
     * @param int       $code
     * @param Exception $previous
     * @return void
     */
    public function __construct(ModelInterface $model, ?string $message = null, int $code = 0, Exception $previous = null)
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
