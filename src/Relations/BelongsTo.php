<?php

namespace EloquentRest\Relations;

use EloquentRest\Models\Contracts\ModelInterface;
use EloquentRest\Query;

class BelongsTo extends Relation
{
    /**
     * @var string|null
     */
    protected ?string $foreignKey;

    public function __construct(ModelInterface $model, ModelInterface $related, ?string $foreignKey = null)
    {
        parent::__construct($model, $related);

        $this->foreignKey = $foreignKey ?: $related->getForeignKey();
    }

    /**
     * Get the relations name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->getRelated()->getName();
    }

    /**
     * Get a new query instance.
     *
     * @return Query
     */
    public function newQuery(): Query
    {
        $related = $this->getRelated();

        return $related->newQuery()->where($related->getKeyName(), $this->model->getAttribute($this->foreignKey));
    }

    /**
     * Create a new relation.
     *
     * @param array $attributes
     * @return ModelInterface
     */
    public function create(array $attributes): ModelInterface
    {
        return $this->getRelated()->create($attributes);
    }

    /**
     * Fill the relation with an array of attributes.
     *
     * @param array $attributes
     * @return ModelInterface
     */
    public function fill(array $attributes): ModelInterface
    {
        return $this->getRelated()->newInstance($attributes);
    }
}
