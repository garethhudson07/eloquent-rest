<?php

namespace App\Models\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

class Request {

    /**
     * The model to be queried
     *
     * @var string
     */
    protected $model;
    
    /**
     * The adapter instance
     *
     * @var Adapter
     */
    protected $adapter;

    /**
     * Create a new Request instance.
     *
     * @param  App\Models\Rest\Model $model
     * @return void
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->adapter = new Adapter($model);
    }
    
    /**
     * Execute a get request on the model.
     *
     * @return string
     */
    public function get(Query $query)
    {
        try
        {
            $clauses = $query->getClauses();
            
            $response = $this->make()->get
            (
                array_pull($clauses['where'], $this->model->getKeyName()) ?: '',
                ['query' => $this->adapter->formatClauses($clauses)]
                
            )->json();
            
            return $this->adapter->extract($response);
        }
        catch(TransferException $e)
        {
            $this->handleTransferException($e);
        }
    }
    
    /**
     * Execute a put request on the model.
     *
     * @return string
     */
    public function put()
    {
        try
        {
            $response = $this->make()->put
            (
                $this->model->getKey(),
                ['body' => $this->model->getAttributes()]
                
            )->json();
            
            return $this->adapter->extract($response);
        }
        catch(TransferException $e)
        {
            $this->handleTransferException($e);
        }
    }
    
    /**
     * Execute a post request on the model.
     *
     * @return string
     */
    public function post()
    {
        try
        {
            $response = $this->make()->post('', ['body' => $this->model->getAttributes()])->json();
            
            return $this->adapter->extract($response);
        }
        catch(TransferException $e)
        {
            $this->handleTransferException($e);
        }
    }
    
    /**
     * Execute a delete request on the model.
     *
     * @return boolean
     */
    public function delete()
    {
        try
        {
            $this->make()->delete($this->model->getKey());
            
            return TRUE;
        }
        catch(TransferException $e)
        {
            $this->handleTransferException($e);
        }
        
        return FALSE;
    }
    
    /**
     * Generate a new request.
     *
     * @return string
     */
    protected function make()
    {
        return new Client([
            'base_url' => implode('/', array_flatten
            ([
                $this->model->getPrefix(),
                $this->model->getScopes(),
                $this->model->isSingleton() ? $this->model->getName() : str_plural($this->model->getName())
            ])).'/',
            'defaults' => [
                'headers' => $this->adapter->getHeaders()
            ]
        ]);
    }
    
    /**
     * Handle a transfer exception.
     *
     * @return string
     */
    protected function handleTransferException(TransferException $e)
    {
        $response = $e->getResponse();
        
        $error = $response->json();
        
        switch($response->getStatusCode())
        {
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