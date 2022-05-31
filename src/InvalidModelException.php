<?php

namespace App\Models\Api;

class InvalidModelException extends ModelException {
    
    /**
     * A list of the exceptions errors.
     *
     * @var array
     */
    protected $errors = [];
    
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
    public function __construct(Model $model, $message = '', array $errors = [], $code = 0, Exception $previous = NULL)
    {
        parent::__construct($model, $message, $code, $previous);
        
        $this->errors = $errors;
    }
    
    /**
     * Get the exceptions errors array.
     * 
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}