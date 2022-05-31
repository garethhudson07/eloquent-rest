<?php

namespace EloquentRest\Api;

use EloquentRest\Models\Contracts\ModelInterface;
use EloquentRest\Support\Helpers;

class Adapter
{

    /**
     * The model instance
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
     * Get headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return ['Authorization' => 'Bearer ' . $this->model->getToken()->getValue()];
    }

    /**
     * Generate query parameters.
     *
     * @return array
     */
    public function formatClauses(array $clauses)
    {
        $clauses['expand'] = implode(',', $clauses['expand']);


        $sort = [];

        foreach ($clauses['sort'] as $field => $direction) {
            $sort[] .= strtolower($direction) == 'desc' ? '-' . $field : $field;
        }

        $clauses['sort'] = implode(',', $sort);


        // We want to add our where clauses as additional parameters
        // However we want to exclude any clauses on the primary key as this will be handled via the url
        $conditions = array_map(function ($value) {
            return $value === null ? 'null' : $value;
        }, Helpers::arrayPull($clauses, 'where'));

        $clauses = array_merge($clauses, $conditions);

        // Remove empty keys and return
        return array_filter($clauses);
    }

    /**
     * Extract resource data from a raw server response
     *
     * @param  array $response
     * @return array
     */
    public function extract($data)
    {
        if (array_key_exists($this->model->getName(), $data)) {
            return $data[$this->model->getName()];
        }

        return $data[Helpers::strCamelCase($this->model->getEndpoint())];
    }
}