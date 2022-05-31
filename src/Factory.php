<?php

namespace EloquentRest;

use EloquentRest\Models\Contracts\ModelInterface;

class Factory
{
    /**
     * The model to be queried
     *
     * @var ModelInterface
     */
    protected $model;

    /**
     * Create a new Response instance.
     *
     * @param  array $data
     * @return void
     */
    public function __construct(ModelInterface $model)
    {
        $this->model = $model;
    }

    /**
     * Make a resource from raw data
     *
     * @param  array $data
     * @return Resource|Collection
     */
    public function make(array $data)
    {
        if (array_key_exists($this->model->getKeyName(), $data) || $this->model->isSingleton()) {
            return $this->model($data);
        } else {
            $items = array_map(function ($item) {
                return $this->model($item);
            }, $data);

            return $this->collection($items);
        }
    }

    /**
     * Make a model from raw data
     *
     * @param  array $data
     * @return Resource|Collection
     */
    public function model(array $data)
    {
        return $this->model->newInstance($data);
    }

    /**
     * Make a collection from raw data
     *
     * @param  array $data
     * @return Resource|Collection
     */
    public function collection(array $items)
    {
        return (new Collection)->fill($items);
    }
}
