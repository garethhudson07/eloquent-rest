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
use GuzzleHttp\Psr7\Response;

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
     * Create a new Request instance.
     *
     * @param ModelInterface $model
     * @return void
     */
    public function __construct(ModelInterface $model)
    {
        $this->model = $model;
        $this->adapter = new Adapter($model);
    }

    /**
     * Execute a get request on the model.
     *
     * @return array
     */
    public function get(Query $query): array
    {
        try {
            $clauses = $query->getClauses();

            $response = $this->json($this->make()->get(
                Helpers::pull($clauses['where'], $this->model->getKeyName()) ?: '',
                ['query' => $this->adapter->formatClauses($clauses)]

            ));

            return $this->adapter->extract($response);
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
            $response = $this->json($this->make()->put(
                $this->model->getKey(),
                ['body' => $this->model->getAttributes()]

            ));

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
            $response = $this->json($this->make()->post('', ['body' => $this->model->getAttributes()]));

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
        return new Client([
            'base_url' => implode('/', Helpers::flatten([
                $this->model->getPrefix(),
                $this->model->getScopes(),
                $this->model->getEndpoint()
            ])) . '/',
            'defaults' => [
                'headers' => $this->adapter->getHeaders()
            ]
        ]);
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
        $error = $this->json($response);

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

    /**
     * @param Response $response
     * @return null|array
     */
    protected function json(Response $response): ?array
    {
        return json_decode($response->getBody()->getContents(), true);
    }
}
