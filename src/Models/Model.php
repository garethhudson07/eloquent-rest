<?php

namespace EloquentRest\Models;

use EloquentRest\Http\Request;
use EloquentRest\Models\Contracts\ModelInterface;
use EloquentRest\Query;
use EloquentRest\Relations\BelongsTo;
use EloquentRest\Relations\HasMany;
use EloquentRest\Relations\MorphTo;
use EloquentRest\Relations\Nested;
use EloquentRest\Support\Helpers;
use League\OAuth2\Client\Token\AccessTokenInterface;

abstract class Model implements ModelInterface
{

    /**
     * The name of the resource
     *
     * @var string
     */
    protected $name;

    /**
     * Flag to indicate whether or not the resource is a singleton
     *
     * @var boolean
     */
    protected $singleton = false;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The model's relations.
     *
     * @var array
     */
    protected $relations = [];

    /**
     * The model's scopes.
     *
     * @var array
     */
    protected $scopes = [];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Root API location
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * AccessTokenInterface implementation
     *
     * @var AccessTokenInterface
     */
    protected $token;

    /**
     * Create a new Model instance.
     *
     * @param  AccessTokenInterface  $token
     * @return void
     */
    public function __construct(AccessTokenInterface $token)
    {
        $this->token = $token;

        // If the name has not been set manually use the name of the extending class
        if (!$this->name) {
            $this->name = strtolower(Helpers::classBasename($this));
        }
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        if (($attribute = $this->getAttribute($key)) !== false) {
            return $attribute;
        }

        return $this->getRelation($key);
    }

    /**
     * Get the model's attributes.
     *
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        return false;
    }

    /**
     * Get the model's relations.
     *
     * @return mixed
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * Get a relation from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getRelation($key)
    {
        if (array_key_exists($key, $this->relations)) {
            return $this->relations[$key];
        }

        return false;
    }

    /**
     * Get token.
     *
     * @param  string  $key
     * @return AccessTokenInterface
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Get scopes.
     *
     * @return array
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * Get the model's name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the model's prefix.
     *
     * @return string
     */
    public function getPrefix()
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
    public function getKeyName()
    {
        return $this->primaryKey;
    }

    /**
     * Get the model's foreign key.
     *
     * @return string
     */
    public function getForeignKey()
    {
        return $this->name . ucfirst($this->getKeyName());
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Set a relation.
     *
     * @param  string  $key
     * @return mixed
     */
    public function setRelation($key, $relation)
    {
        $this->relations[$key] = $relation;
    }

    /**
     * Determine if an attribute exists on the model.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return (isset($this->attributes[$key]) || isset($this->relations[$key]));
    }

    /**
     * Dynamically handle query builder methods via the model.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return Query
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->newQuery(), $method], $parameters);
    }

    /**
     * Determine whether the model exists.
     *
     * @return boolean
     */
    public function exists()
    {
        return (array_key_exists($this->getKeyName(), $this->getAttributes()) || $this->isSingleton());
    }

    /**
     * Determine whether the model exists.
     *
     * @return boolean
     */
    public function isSingleton()
    {
        return $this->singleton;
    }

    /**
     * Create a new instance of the model.
     *
     * @param  array  $attributes
     * @return static
     */
    public function newInstance(array $attributes = [])
    {
        return (new static($this->getToken()))->scope($this->getScopes())->fill($attributes);
    }

    /**
     * Create a new model.
     *
     * @param  array $attributes
     * @return array
     */
    public function create(array $attributes)
    {
        return $this->newInstance($attributes)->save();
    }

    /**
     * Save the model
     *
     * @return this
     */
    public function save()
    {
        $response = $this->exists() ? $this->newRequest()->put() : $this->newRequest()->post();

        return $this->fill($response);
    }

    /**
     * Delete the model
     *
     * @return boolean
     */
    public function delete()
    {
        return $this->newRequest()->delete();
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param  array  $attributes
     * @return $this
     */
    public function fill(array $attributes)
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
     * @param  mixed  $context
     * @param  mixed  $key
     * @return $this
     */
    public function scope($context, $key = null)
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
    protected function newRequest()
    {
        return new Request($this);
    }

    /**
     * Get a new instance of the query builder
     *
     * @return Query
     */
    public function newQuery()
    {
        return new Query($this);
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->getAttributes(), $this->relationsToArray());
    }

    /**
     * Convert the model's relationships to an array.
     *
     * @return array
     */
    public function relationsToArray()
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
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * Convert the model to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Nest a model.
     *
     * @param  string  $model
     * @return Nested
     */
    protected function nest($model)
    {
        return new Nested($this, new $model($this->getToken()));
    }

    /**
     * Apply a "belongs to" relationship.
     *
     * @param  string  $model
     * @param  string  $foreignKey
     * @return BelongsTo
     */
    protected function belongsTo($model, $foreignKey = null)
    {
        return new BelongsTo($this, new $model($this->getToken()), $foreignKey);
    }

    /**
     * Apply a "morph to" relationship.
     *
     * @param  array   $models
     * @param  string  $morphKey
     * @param  string  $foreignKey
     * @return MorphTo
     */
    protected function morphTo(array $models, $morphKey = null, $foreignKey = null)
    {
        // If the morphKey is not set, we will use the name of the calling function
        if (!$morphKey) {
            $trace = debug_backtrace();
            $morphKey = $trace[1]['function'];
        }

        return new MorphTo(
            $this,
            array_map(function ($model) {
                return new $model($this->getToken());
            }, $models),
            $morphKey,
            $foreignKey
        );
    }

    /**
     * Apply a "has many" relationship.
     *
     * @param  string  $model
     * @param  string  $foreignKey
     * @return HasMany
     */
    protected function hasMany($model, $foreignKey = null)
    {
        return new HasMany($this, new $model($this->getToken()), $foreignKey);
    }
}
