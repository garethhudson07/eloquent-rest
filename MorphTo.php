<?php

namespace App\Models\Api;

class MorphTo extends BelongsTo {

    protected $morphKey;
    
    public function __construct(Model $model, array $related, $morphKey, $foreignKey = NULL)
    {
        $this->model = $model;
        $this->related = $related;
        $this->morphKey = $morphKey;
        $this->foreignKey = $foreignKey ?: $morphKey.'_id';
    }

    /**
     * Get the related model.
     *
     * @return Model
     */
    public function getRelated()
    {
        foreach($this->related as $related)
        {
            if(class_basename($related) == $this->model->type)
            {
                return $related;
            }
        }
    }

    /**
     * Get the relations name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->morphKey;
    }
}