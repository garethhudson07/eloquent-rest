<?php

namespace App\Models\Api;

class HasMany extends Relation {
    
    protected $foreignKey;
    
    public function __construct(Model $model, Model $related, $foreignKey = NULL)
    {
        parent::__construct($model, $related);
        
        $this->foreignKey = $foreignKey ?: $this->model->getForeignKey();
    }
    
    /**
     * Get a new query instance.
     *
     * @return Query
     */
    public function newQuery()
    {
        $query = $this->getRelated()->newQuery();
        
        $query->where($this->foreignKey, $this->model->getKey());
        
        return $query;
    }
    
    /**
     * Create a new relation.
     *
     * @param  array $attributes
     * @return Model
     */
    public function create(array $attributes)
    {
        return $this->getRelated()->create($this->associate($attributes));
    }
    
    /**
     * Fill the relation with an array of attributes.
     *
     * @param  array  $attributes
     * @return Collection
     */
    public function fill(array $data)
    {
        $data = array_map(function($item)
        {
            return $this->getRelated()->newInstance($this->associate($item));
            
        }, $data);
        
        return new Collection($data);
    }
    
    /**
     * Create a new instance of the relation.
     *
     * @param  array  $attributes
     * @return mixed
     */
    protected function associate(array $attributes = [])
    {
        $attributes[$this->foreignKey] = $this->model->getKey();
        
        return $attributes;
    }
}