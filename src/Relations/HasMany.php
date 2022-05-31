<?php

namespace EloquentRest\Relations;

use EloquentRest\Collection;
use EloquentRest\Models\Contracts\ModelInterface;
use EloquentRest\Query;

class HasMany extends Relation
{
    /**
     * @var null|string
     */
    protected ?string $foreignKey;

    public function __construct(ModelInterface $model, ModelInterface $related, ?string $foreignKey = null)
    {
        parent::__construct($model, $related);

        $this->foreignKey = $foreignKey ?: $this->model->getForeignKey();
    }

    /**
     * Get a new query instance.
     *
     * @return Query
     */
    public function newQuery(): Query
    {
        $query = $this->getRelated()->newQuery();

        $query->where($this->foreignKey, $this->model->getKey());

        return $query;
    }

    /**
     * Create a new relation.
     *
     * @param array $attributes
     * @return ModelInterface
     */
    public function create(array $attributes): ModelInterface
    {
        return $this->getRelated()->create($this->associate($attributes));
    }

    /**
     * Fill the relation with an array of attributes.
     *
     * @param array $attributes
     * @return Collection
     */
    public function fill(array $data): Collection
    {
        $data = array_map(function ($item) {
            return $this->getRelated()->newInstance($this->associate($item));
        }, $data);

        return (new Collection)->fill($data);
    }

    /**
     * Create a new instance of the relation.
     *
     * @param array $attributes
     * @return mixed
     */
    protected function associate(array $attributes = []): array
    {
        $attributes[$this->foreignKey] = $this->model->getKey();

        return $attributes;
    }
}
