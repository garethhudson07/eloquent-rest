<?php

namespace EloquentRest\Models;

use ArrayAccess;
use EloquentRest\Exceptions\InvalidModelException;
use EloquentRest\Http\Request;
use EloquentRest\Models\Contracts\ModelInterface;
use EloquentRest\Query;
use EloquentRest\Relations\BelongsTo;
use EloquentRest\Relations\HasMany;
use EloquentRest\Relations\Nested;
use EloquentRest\Support\Helpers;
use JsonSerializable;
use League\OAuth2\Client\Token\AccessTokenInterface;

abstract class Model implements ModelInterface, JsonSerializable, ArrayAccess
{
    /**
     * The name of the resource
     *
     * @var string
     */
    protected string $name;

    /**
     * The API endpoint of the resource
     *
     * @var string
     */
    protected string $endpoint;

    /**
     * Flag to indicate whether or not the resource is a singleton
     *
     * @var bool
     */
    protected bool $singleton = false;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected array $attributes = [];

    /**
     * The model's relations.
     *
     * @var array
     */
    protected array $relations = [];

    /**
     * The model's scopes.
     *
     * @var array
     */
    protected array $scopes = [];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected string $primaryKey = 'id';

    /**
     * Root API location
     *
     * @var string|null
     */
    protected ?string $prefix = null;

    /**
     * AccessTokenInterface implementation
     *
     * @var AccessTokenInterface
     */
    protected AccessTokenInterface $token;

    /**
     * Create a new Model instance.
     *
     * @param AccessTokenInterface  $token
     * @return void
     */
    public function __construct(AccessTokenInterface $token)
    {
        $this->token = $token;

        // If the name has not been set manually use the name of the extending class
        if (!isset($this->name)) {
            $this->name = Helpers::lower(Helpers::classBasename($this));
        }

        // If the endpoint has not been set manually use the name of the extending class
        if (!isset($this->endpoint)) {
            throw new InvalidModelException($this, 'A valid API endpoint has not been set for this model instance');
        }
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->attributes[$offset]) || isset($this->relations[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getAttribute((string) $offset) ?? $this->getRelation((string) $offset) ?? null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (isset($this->relations[$offset])) {
            $this->relations[$offset] = $value;
        }

        $this->attributes[$offset] = $value;
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        if (isset($this->relations[$offset])) {
            unset($this->relations[$offset]);
        }

        if (isset($this->attributes[$offset])) {
            unset($this->attributes[$offset]);
        }
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        if (($attribute = $this->getAttribute($key)) !== false) {
            return $attribute;
        }

        return $this->getRelation($key);
    }

    /**
     * Get the model's attributes.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get an attribute from the model.
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Get the model's relations.
     *
     * @return array
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * Get a relation from the model.
     *
     * @param string $key
     * @return mixed
     */
    public function getRelation(string $key)
    {
        return $this->relations[$key] ?? null;
    }

    /**
     * Get token.
     *
     * @return AccessTokenInterface
     */
    public function getToken(): AccessTokenInterface
    {
        return $this->token;
    }

    /**
     * Get scopes.
     *
     * @return array
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * Get the model's name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the model's prefix.
     *
     * @return string|null
     */
    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    /**
     * Get the value of the model's primary key.
     *
     * @return mixed
     */
    public function getKey()
    {
        return $this->getAttribute($this->getKeyName());
    }

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName(): string
    {
        return $this->primaryKey;
    }

    /**
     * Get the model's foreign key.
     *
     * @return string
     */
    public function getForeignKey(): string
    {
        return $this->name . ucfirst($this->getKeyName());
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Set a relation.
     *
     * @param string $key
     * @return void
     */
    public function setRelation($key, $relation): void
    {
        $this->relations[$key] = $relation;
    }

    /**
     * Determine if an attribute exists on the model.
     *
     * @param string $key
     * @return bool
     */
    public function __isset($key): bool
    {
        return (isset($this->attributes[$key]) || isset($this->relations[$key]));
    }

    /**
     * Dynamically handle query builder methods via the model.
     *
     * @param string $method
     * @param array $parameters
     * @return Query
     */
    public function __call($method, $parameters): Query
    {
        return call_user_func_array([$this->newQuery(), $method], $parameters);
    }

    /**
     * Determine whether the model exists.
     *
     * @return bool
     */
    public function exists(): bool
    {
        return (array_key_exists($this->getKeyName(), $this->getAttributes()) || $this->isSingleton());
    }

    /**
     * Determine whether the model exists.
     *
     * @return bool
     */
    public function isSingleton(): bool
    {
        return $this->singleton;
    }

    /**
     * Create a new instance of the model.
     *
     * @param array $attributes
     * @return static
     */
    public function newInstance(array $attributes = []): self
    {
        return (new static($this->getToken()))->scope($this->getScopes())->fill($attributes);
    }

    /**
     * Create a new model.
     *
     * @param array $attributes
     * @return static
     */
    public function create(array $attributes): self
    {
        return $this->newInstance($attributes)->save();
    }


    /**
     * Update an existing resource in the API
     *
     * @param array $attributes
     * @return static
     */
    public function update(array $attributes): self
    {
        return $this->newInstance($attributes)->save();
    }

    /**
     * Save the model
     *
     * @return static
     */
    public function save(): self
    {
        $response = $this->exists() ? $this->newRequest()->put() : $this->newRequest()->post();

        return $this->fill($response);
    }

    /**
     * Delete the model
     *
     * @return bool
     */
    public function delete(): bool
    {
        return $this->newRequest()->delete();
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param array $attributes
     * @return static
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if (method_exists($this, $key)) {
                $relation = call_user_func([$this, $key])->fill($value);

                $this->setRelation($key, $relation);

                continue;
            }

            $this->attributes[$key] = $value;
        }

        return $this;
    }

    /**
     * Add scope to the model.
     *
     * @param mixed $context
     * @param mixed $key
     * @return static
     */
    public function scope($context, $key = null): self
    {
        if (is_array($context)) {
            $this->scopes = array_merge($this->scopes, $context);
        } else {
            $this->scopes[] = compact('context', 'key');
        }

        return $this;
    }

    /**
     * Get a new instance of the Request class
     *
     * @return Request
     */
    public function newRequest(): Request
    {
        return new Request($this);
    }

    /**
     * Get a new instance of the query builder
     *
     * @return Query
     */
    public function newQuery(): Query
    {
        return new Query($this);
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_merge($this->getAttributes(), $this->relationsToArray());
    }

    /**
     * Convert the model's relationships to an array.
     *
     * @return array
     */
    public function relationsToArray(): array
    {
        $relations = [];

        foreach ($this->getRelations() as $key => $value) {
            $relations[$key] = $value->toArray();
        }

        return $relations;
    }

    /**
     * Convert the model instance to JSON.
     *
     * @return string
     */
    public function jsonSerialize(): string
    {
        return $this->toJson();
    }

    /**
     * Convert the model instance to JSON.
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Convert the model to its string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Nest a model.
     *
     * @param string|ModelInterface $model
     * @return Nested
     */
    protected function nest($model): Nested
    {
        if (is_string($model)) {
            $model = new $model($this->getToken());
        }

        return new Nested($this, $model);
    }

    /**
     * Apply a "belongs to" relationship.
     *
     * @param string|ModelInterface $model
     * @param string $foreignKey
     * @return BelongsTo
     */
    protected function belongsTo($model, string $foreignKey = null): BelongsTo
    {
        if (is_string($model)) {
            $model = new $model($this->getToken());
        }

        return new BelongsTo($this, $model, $foreignKey);
    }

    /**
     * Apply a "has many" relationship.
     *
     * @param string|ModelInterface $model
     * @param string $foreignKey
     * @return HasMany
     */
    protected function hasMany($model, string $foreignKey = null)
    {
        if (is_string($model)) {
            $model = new $model($this->getToken());
        }

        return new HasMany($this, $model, $foreignKey);
    }
}
