<?php

namespace EloquentRest\Models\Contracts;

interface ModelInterface
{
    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray();

    /**
     * Save the model
     *
     * @return boolean
     */
    public function save();

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName();

    /**
     * Determine whether the model exists.
     *
     * @return boolean
     */
    public function isSingleton();

    /**
     * Create a new instance of the model.
     *
     * @param  array  $attributes
     * @return static
     */
    public function newInstance(array $attributes = []);

    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key);

    /**
     * Get the model's name.
     *
     * @return string
     */
    public function getName();

    /**
     * Get token.
     *
     * @param  string  $key
     * @return AccessTokenInterface
     */
    public function getToken();

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
    public function getForeignKey();

    /**
     * Get the model's attributes.
     *
     * @return mixed
     */
    public function getAttributes();

    /**
     * Get the model's prefix.
     *
     * @return string
     */
    public function getPrefix();

    /**
     * Get scopes.
     *
     * @return array
     */
    public function getScopes();

    /**
     * Add scope to the model.
     *
     * @param  mixed  $context
     * @param  mixed  $key
     * @return $this
     */
    public function scope($context, $key = null);

    /**
     * Get the API resource endpoint.
     *
     * @return string
     */
    public function getEndpoint();
}
