<?php

namespace App\Models\Api;

use App\Support\Collection as BaseCollection;

class Collection extends BaseCollection {
    
    /**
     * Get an item by it's primary key.
     *
     * @param  int   $id
     * @return mixed
     */
    public function find($id)
    {
        foreach($this->items as $item)
        {
            if($item->getKey() == $id)
            {
                return $item;
            }
        }
        
        return NULL;
    }
}