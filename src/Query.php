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
    protected ModelInterface $model;

    /**
     * The relations to expand.
     *
     * @var array
     */
    protected array $expand = [];

    /**
     * Conditional clauses
     *
     * @var array
     */
    protected array $where = [];

    /**
     * Sort clauses.
     *
     * @var array
     */
    protected array $sort = [];

    /**
     * The number of result to retrieve.
     *
     * @var int|null
     */
    protected ?int $limit = null;

    /**
     * Create a new Query instance.
     *
     * @param ModelInterface $model
     * @return void
     */
    public function __construct(ModelInterface $model)
    {
        $this->model = $model;
    }

    /**
     * Add a where clause to the query.
     *
     * @param string $key
     * @param mixed $value
     * @return static
     */
    public function where(string $key, $value): self
    {
        $this->where[$key] = $value;

        return $this;
    }

    /**
     * Add a take clause
     *
     * @param int amount
     * @return static
     */
    public function take(int $amount): self
    {
        $this->limit = $amount;

        return $this;
    }

    /**
     * Add an order by clause
     *
     * @param string field
     * @param string direction
     * @return static
     */
    public function orderBy(string $field, string $direction = 'asc'): self
    {
        $this->sort[$field] = $direction;

        return $this;
    }

    /**
     * Eager load models.
     *
     * @param string|array $relations
     * @return static
     */
    public function with($relations): self
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
     * @return ModelInterface|Collection|null
     */
    public function get()
    {
        $response = $this->model->newRequest()->get($this);

        if (is_null($response)) {
            return null;
        }

        return (new Factory($this->model))->make($response);
    }

    /**
     * Find a model by it's primary key.
     *
     * @param mixed id
     * @return ModelInterface|null
     */
    public function find($id): ?ModelInterface
    {
        $this->where($this->model->getKeyName(), $id);

        return $this->get();
    }

    /**
     * Execute the query and get the first result.
     *
     * @return ModelInterface
     */
    public function first(): ?ModelInterface
    {
        $result = $this->get();

        return isset($result[0]) ? $result[0] : null;
    }

    /**
     * Execute the query and get the first result or throw an exception if it is not found.
     *
     * @return ModelInterface
     * @throws ModelNotFoundException
     */
    public function firstOrFail(): ModelInterface
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
    public function getModel(): ModelInterface
    {
        return $this->model;
    }

    /**
     * Get query clauses as a plain array
     *
     * @return array
     */
    public function getClauses(): array
    {
        return [
            'expand' => $this->expand,
            'where' => $this->where,
            'sort' => $this->sort,
            'limit' => $this->limit,
        ];
    }
}
