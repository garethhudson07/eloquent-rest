<?php

namespace EloquentRest\Relations;

use EloquentRest\Collection;
use EloquentRest\Models\Contracts\ModelInterface;
use EloquentRest\Query;

class Nested extends Relation
{

    public function __construct(ModelInterface $model, ModelInterface $related)
    {
        $related->scope($model->getScopes())
            ->scope($model->getEndpoint(), $model->getKey());

        parent::__construct($model, $related);
    }

    /**
     * Get a new query instance.
     *
     * @return Query
     */
    public function newQuery(): Query
    {
        return $this->getRelated()->newQuery();
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
     * @return ModelInterface|Collection
     */
    public function fill(array $data)
    {
        $related = $this->getRelated();

        if ($related->isSingleton()) {
            return $related->newInstance($data);
        } else {
            $items = array_map(function ($item) use ($related) {
                return $related->newInstance($item);
            }, $data);

            return (new Collection)->fill($items);
        }
    }
}
