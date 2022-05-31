<?php

namespace App\Models\Api;

class BelongsTo extends Relation {

    protected $foreignKey;
    
    public function __construct(Model $model, Model $related, $foreignKey = NULL)
    {
        parent::__construct($model, $related);
        
        $this->foreignKey = $foreignKey ?: $related->getForeignKey();
    }
    
    /**
     * Get the relations name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getRelated()->getName();
    }
    
    /**
     * Get a new query instance.
     *
     * @return Query
     */
    public function newQuery()
    {
        $related = $this->getRelated();
        
        return $related->newQuery()->where($related->getKeyName(), $this->model->getAttribute($this->foreignKey));
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
     * @return Model
     */
    public function fill(array $data)
    {
        return $this->getRelated()->newInstance($data);
    }
}