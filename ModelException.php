<?php

namespace App\Models\Api;

use RuntimeException;

class ModelException extends RuntimeException {
    
    /**
     * The model.
     *
     * @var Model
     */
    protected $model;
    
    /**
     * Create a new InvalidModelException instance.
     *
     *
     * @param  string    $message
     * @param  array     $errors
     * @param  int       $code
     * @paran  Exception $previous
     * @return void
     */
    public function __construct(Model $model, $message = '', $code = 0, Exception $previous = NULL)
    {
        parent::__construct($message, $code, $previous);
        
        $this->model = $model;
    }
    
    /**
     * Get the model.
     * 
     * @return array
     */
    public function getModel()
    {
        return $this->model;
    }
}