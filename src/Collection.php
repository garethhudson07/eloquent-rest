<?php

namespace EloquentRest;

use Aggregate\Set;

class Collection extends Set
{

    /**
     * Get an item by it's primary key.
     *
     * @param  int   $id
     * @return mixed
     */
    public function find($id)
    {
        foreach ($this->items as $item) {
            if ($item->getKey() == $id) {
                return $item;
            }
        }

        return null;
    }
}
