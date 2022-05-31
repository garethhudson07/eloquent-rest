<?php

namespace EloquentRest\Http;

use EloquentRest\Api\Adapter;
use EloquentRest\Exceptions\InvalidModelException;
use EloquentRest\Exceptions\ModelException;
use EloquentRest\Exceptions\ModelNotFoundException;
use EloquentRest\Models\Contracts\ModelInterface;
use EloquentRest\Query;
use EloquentRest\Support\Helpers;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Request
{
    /**
     * The model to be queried
     *
     * @var ModelInterface
     */
    protected ModelInterface $model;

    /**
     * The adapter instance
     *
     * @var Adapter
     */
    protected Adapter $adapter;

    /**
     * @var array
     */
    protected array $httpClientOptions;

    /**
     * Create a new Request instance.
     *
     * @param ModelInterface $model
     * @return void
     */
    public function __construct(ModelInterface $model, array $httpClientOptions = [], ?Adapter $adapter = null)
    {
        $this->model = $model;
        $this->httpClientOptions = $httpClientOptions;
        $this->adapter = $adapter ?? new Adapter($model);
    }

    /**
     * Execute a get request on the model.
     *
     * @return array|null
     */
    public function get(Query $query): ?array
    {
        try {
            $clauses = $query->getClauses();
            $resourceId = Helpers::pull($clauses['where'], $this->model->getKeyName());

            $response = $this->make()->get(
                $resourceId ?: '',
                ['query' => $this->adapter->formatClauses($clauses)],
            );

            $data = $this->adapter->extract($response);

            if (($this->model->isSingleton() || $resourceId) && !$data) {
                return null;
            }

            if (!$resourceId && !$data) {
                return [];
            }

            return $data;
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        }
    }

    /**
     * Execute a put request on the model.
     *
     * @return array
     */
    public function put(): array
    {
        try {
            $response = $this->make()->put(
                $this->model->getKey(),
                [$this->adapter->getBodyType() => $this->adapter->prepare($this->model)]
            );

            return $this->adapter->extract($response);
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        }
    }

    /**
     * Execute a post request on the model.
     *
     * @return array
     */
    public function post(): array
    {
        try {
            $response = $this->make()->post('', [
                $this->adapter->getBodyType() => $this->adapter->prepare($this->model)
            ]);

            return $this->adapter->extract($response);
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        }
    }

    /**
     * Execute a delete request on the model.
     *
     * @return bool
     */
    public function delete(): bool
    {
        try {
            $this->make()->delete($this->model->getKey());

            return true;
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        }

        return false;
    }

    /**
     * Generate a new request.
     *
     * @return Client
     */
    protected function make(): Client
    {
        return new Client(array_merge($this->httpClientOptions, [
            'base_uri' => implode('/', array_values(array_filter([
                $this->httpClientOptions['base_uri'] ?? null,
                $this->model->getPrefix(),
                $this->model->getScopes(),
                $this->model->getEndpoint()
            ]))) . '/',
            'headers' => array_merge($this->httpClientOptions['headers'] ?? [], $this->adapter->getHeaders()),
        ]));
    }

    /**
     * Handle a transfer exception.
     *
     * @return void
     * @throws InvalidModelException
     * @throws ModelNotFoundException
     * @throws ModelException
     */
    protected function handleRequestException(RequestException $e): void
    {
        $response = $e->getResponse();
        $error = $this->adapter->extractErrors($response);

        switch ($response->getStatusCode()) {
            case 400:
                throw new InvalidModelException($this->model, $error['errorDescription'], $error['errorDetails']);
                break;

            case 404:
                throw new ModelNotFoundException($this->model, $error['errorDescription']);
                break;

            default:
                throw new ModelException($this->model, $error['errorDescription']);
                break;
        }
    }
}
