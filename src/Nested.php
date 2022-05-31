<?php

namespace App\Models\Api;

class Nested extends Relation {
    
    public function __construct(Model $model, Model $related)
    {
        $related->scope($model->getScopes())
                ->scope(str_plural($model->getName()), $model->getKey());
                
        parent::__construct($model, $related);
    }
    
    /**
     * Get a new query instance.
     *
     * @return Query
     */
    public function newQuery()
    {
        return $this->getRelated()->newQuery();
    }
    
    /**
     * Create a new relation.
     *
     * @param  array $attributes
     * @return Model
     */
    public function create(array $attributes)
    {
        return $this->getRelated()->create($attributes);
    }
    
    /**
     * Fill the relation with an array of attributes.
     *
     * @param  array  $attributes
     * @return mixed
     */
    public function fill(array $data)
    {
        $related = $this->getRelated();
        
        if($related->isSingleton())
        {
            return $related->newInstance($data);
        }
        else
        {
            $items = array_map(function($item) use($related)
            {
                return $related->newInstance($item);
                
            }, $data);
            
            return new Collection($items);
        }
    }
}