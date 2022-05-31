<?php

namespace EloquentRest;

use EloquentRest\Exceptions\ModelNotFoundException;
use EloquentRest\Http\Request;
use EloquentRest\Models\Contracts\ModelInterface;

class Query
{
    /**
     * The model to be queried
     *
     * @var ModelInterface
     */
    protected $model;

    /**
     * The relations to expand.
     *
     * @var array
     */
    protected $expand = [];

    /**
     * Conditional clauses
     *
     * @var array
     */
    protected $where = [];

    /**
     * Sort clauses.
     *
     * @var string
     */
    protected $sort = [];

    /**
     * The number of result to retrieve.
     *
     * @var int
     */
    protected $limit;

    /**
     * Create a new Query instance.
     *
     * @param  ModelInterface $model
     * @return void
     */
    public function __construct(ModelInterface $model)
    {
        $this->model = $model;
    }

    /**
     * Add a where clause to the query.
     *
     * @param  string $key
     * @param  string $value
     * @return $this
     */
    public function where($key, $value)
    {
        $this->where[$key] = $value;

        return $this;
    }

    /**
     * Add a take clause
     *
     * @param  int amount
     * @return $this
     */
    public function take($amount)
    {
        $this->limit = $amount;

        return $this;
    }

    /**
     * Add an order by clause
     *
     * @param  string field
     * @param  string direction
     * @return $this
     */
    public function orderBy($field, $direction = 'asc')
    {
        $this->sort[$field] = $direction;

        return $this;
    }

    /**
     * Eager load models.
     *
     * @param  string  $nestedResource
     * @return $this
     */
    public function with($relations)
    {
        if (is_string($relations)) {
            $relations = func_get_args();
        }

        // "With" expects the name(s) of the function(s) that define the relation as it's parameter(s)
        // Nested function names are delimited by the period character
        // Convert these function names into model names
        $relations = array_map(function ($key) {
            $names = [];
            $relation = null;
            $hierarchy = explode('.', $key);

            while (count($hierarchy)) {
                $model = $relation ? $relation->getRelated() : $this->model;
                $relation = call_user_func([$model, array_shift($hierarchy)]);
                $names[] = $relation->getName();
            }

            return implode('.', $names);
        }, $relations);

        $this->expand = array_unique(array_merge($this->expand, $relations));

        return $this;
    }

    /**
     * Execute a query
     *
     * @return ModelInterface|Collection
     */
    public function get()
    {
        $response = (new Request($this->model))->get($this);

        return (new Factory($this->model))->make($response);
    }

    /**
     * Find a model by it's primary key.
     *
     * @param  int id
     * @return static
     */
    public function find($id)
    {
        $this->where($this->model->getKeyName(), $id);

        return $this->get();
    }

    /**
     * Execute the query and get the first result.
     *
     * @param  array  $columns
     * @return static
     */
    public function first()
    {
        $result = $this->get();

        return isset($result[0]) ? $result[0] : null;
    }

    /**
     * Execute the query and get the first result.
     *
     * @param  array  $columns
     * @return static
     */
    public function firstOrFail()
    {
        if (!$result = $this->first()) {
            throw new ModelNotFoundException($this->model);
        }

        return $result;
    }

    /**
     * Get the model that is being queried
     *
     * @return ModelInterface
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get query clauses as a plain array
     *
     * @return array
     */
    public function getClauses()
    {
        return [
            'expand' => $this->expand,
            'where' => $this->where,
            'sort' => $this->sort,
            'limit' => $this->limit
        ];
    }
}
