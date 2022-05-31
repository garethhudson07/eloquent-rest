<?php

namespace EloquentRest\Models\Contracts;

use EloquentRest\Http\Request;
use EloquentRest\Query;
use League\OAuth2\Client\Token\AccessTokenInterface;

interface ModelInterface
{
    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * Save the model
     *
     * @return ModelInterface
     */
    public function save(): ModelInterface;

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName(): string;

    /**
     * Determine whether the model exists.
     *
     * @return bool
     */
    public function isSingleton(): bool;

    /**
     * Create a new instance of the model.
     *
     * @param array $attributes
     * @return ModelInterface
     */
    public function newInstance(array $attributes = []): ModelInterface;

    /**
     * Get an attribute from the model.
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute(string $key);

    /**
     * Get the model's name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get token.
     *
     * @param string $key
     * @return AccessTokenInterface
     */
    public function getToken(): AccessTokenInterface;

    /**
     * Get the value of the model's primary key.
     *
     * @return mixed
     */
    public function getKey();

    /**
     * Get the model's foreign key.
     *
     * @return string
     */
    public function getForeignKey(): string;

    /**
     * Get the model's attributes.
     *
     * @return array
     */
    public function getAttributes(): array;

    /**
     * Get the model's prefix.
     *
     * @return string|null
     */
    public function getPrefix(): ?string;

    /**
     * Get scopes.
     *
     * @return array
     */
    public function getScopes(): array;

    /**
     * Add scope to the model.
     *
     * @param mixed  $context
     * @param mixed  $key
     * @return ModelInterface
     */
    public function scope($context, ?string $key = null): ModelInterface;

    /**
     * Get the API resource endpoint.
     *
     * @return string
     */
    public function getEndpoint(): string;

    /**
     * Save a new resource to the API
     *
     * @param array $attributes
     * @return ModelInterface
     */
    public function create(array $attributes): ModelInterface;

    /**
     * Update an existing resource in the API
     *
     * @param array $attributes
     * @return ModelInterface
     */
    public function update(array $attributes): ModelInterface;

    /**
     * Delete an existing resource from the API
     *
     * @return ModelInterface
     */
    public function delete(): bool;

    /**
     * @return Query
     */
    public function newQuery(): Query;

    /**
     * Get a new instance of the Request class
     *
     * @return Request
     */
    public function newRequest(): Request;
}
