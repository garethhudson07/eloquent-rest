<?php

namespace App\Models\Api;

abstract class Relation {

    protected $model;
    
    protected $related;
    
    public function __construct(Model $model, Model $related)
    {
        $this->model = $model;
        $this->related = $related;
    }
    
    /**
     * Get the related model.
     *
     * @return Model
     */
    public function getRelated()
    {
        return $this->related;
    }
    
    /**
     * Get the relations name.
     *
     * @return string
     */
    public function getName()
    {
        $related = $this->getRelated();
        
        if($related->isSingleton())
        {
            return $related->getName();
        }
        
        return str_plural($related->getName());
    }
    
    /**
     * Dynamically handle query builder methods via the relation.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return App\Models\Api\QueryBuilder
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->newQuery(), $method], $parameters);
    }
    
    /**
     * Get a new query instance.
     *
     * @return Query
     */
    abstract public function newQuery();
    
    /**
     * Create a new relation.
     *
     * @param  array $attributes
     * @return Model
     */
    abstract public function create(array $attributes);
    
    /**
     * Fill the relation with an array of attributes.
     *
     * @param  array  $attributes
     * @return Collection
     */
    abstract public function fill(array $items);
}