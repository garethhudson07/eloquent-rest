<?php

namespace EloquentRest\Relations;

use EloquentRest\Models\Contracts\ModelInterface;
use EloquentRest\Support\Helpers;

class MorphTo extends BelongsTo
{

    protected $morphKey;

    public function __construct(ModelInterface $model, array $related, $morphKey, $foreignKey = null)
    {
        $this->model = $model;
        $this->related = $related;
        $this->morphKey = $morphKey;
        $this->foreignKey = $foreignKey ?: $morphKey . '_id';
    }

    /**
     * Get the related model.
     *
     * @return Model
     */
    public function getRelated()
    {
        foreach ($this->related as $related) {
            if (Helpers::classBasename($related) == $this->model->type) {
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
