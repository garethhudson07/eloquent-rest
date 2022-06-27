<?php

namespace EloquentRest\Api;

use EloquentRest\Models\Contracts\ModelInterface;
use EloquentRest\Support\Helpers;
use Psr\Http\Message\ResponseInterface;

class Adapter
{
    /**
     * The model instance
     *
     * @var ModelInterface
     */
    protected ModelInterface $model;

    /**
     * Create a new Response instance.
     *
     * @param array $data
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
    public function getHeaders(): array
    {
        return ['Authorization' => 'Bearer ' . $this->model->getToken()->getToken()];
    }

    /**
     * Get the request body type ('body', 'json').
     *
     * @return string
     */
    public function getBodyType(): string
    {
        return 'body';
    }

    /**
     * Generate query parameters.
     *
     * @param array $clauses
     * @return array
     */
    public function formatClauses(array $clauses): array
    {
        $clauses['expand'] = implode(',', $clauses['expand']);


        $sort = [];

        foreach ($clauses['sort'] as $field => $direction) {
            $sort[] .= strtolower($direction) === 'desc' ? '-' . $field : $field;
        }

        $clauses['sort'] = implode(',', $sort);


        // We want to add our where clauses as additional parameters
        // However we want to exclude any clauses on the primary key as this will be handled via the url
        $conditions = Helpers::pull($clauses, 'where');

        foreach ($conditions as $condition) {
            if ($condition['field'] === $this->model->getKeyName()) {
                continue;
            }

            $clauses[$condition['field']] = $condition['value'] ?? 'null';
        }

        // Remove empty keys and return
        return array_filter($clauses);
    }

    /**
     * Extract resource data from a raw server response
     *
     * @param ResponseInterface $response
     * @return array|null
     */
    public function extract(ResponseInterface $response): ?array
    {
        return json_decode($response->getBody()->getContents(), true) ?? null;
    }

    /**
     * Extract error data from a raw server response
     *
     * @param ResponseInterface $response
     * @return array
     */
    public function extractErrors(ResponseInterface $response): array
    {
        $data = json_decode($response->getBody()->getContents(), true) ?? [];

        return [
            'errorDescription' => $data['errorDescription'] ?? null,
            'errorDetails' => $data['errorDetails'] ?? null,
        ];
    }

    /**
     * @param ModelInterface $model
     * @return array|string
     */
    public function prepare(ModelInterface $model)
    {
        return $model->getAttributes();
    }
}
