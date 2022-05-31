<?php

namespace EloquentRest\Relations;

use EloquentRest\Models\Contracts\ModelInterface;
use EloquentRest\Collection;
use EloquentRest\Query;
use EloquentRest\Support\Helpers;

abstract class Relation
{
    /**
     * @var ModelInterface
     */
    protected $model;

    /**
     * @var ModelInterface
     */
    protected $related;

    public function __construct(ModelInterface $model, ModelInterface $related)
    {
        $this->model = $model;
        $this->related = $related;
    }

    /**
     * Get the related model.
     *
     * @return ModelInterface
     */
    public function getRelated(): ModelInterface
    {
        return $this->related;
    }

    /**
     * Get the relations name.
     *
     * @return string
     */
    public function getName(): string
    {
        return Helpers::camel($this->getRelated()->getEndpoint());
    }

    /**
     * Dynamically handle query builder methods via the relation.
     *
     * @param string $method
     * @param array $parameters
     * @return Query
     */
    public function __call(string $method, array $parameters)
    {
        return call_user_func_array([$this->newQuery(), $method], $parameters);
    }

    /**
     * Get a new query instance.
     *
     * @return Query
     */
    abstract public function newQuery(): Query;

    /**
     * Create a new relation.
     *
     * @param array $attributes
     * @return ModelInterface
     */
    abstract public function create(array $attributes): ModelInterface;

    /**
     * Fill the relation with an array of attributes.
     *
     * @param array $attributes
     * @return ModelInterface|Collection
     */
    abstract public function fill(array $attributes);
}
