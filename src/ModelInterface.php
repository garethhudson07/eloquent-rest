<?php

namespace App\Models;

interface ModelInterface {
    
    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray();
    
    /**
     * Save the model
     *
     * @return boolean
     */
    public function save();
}