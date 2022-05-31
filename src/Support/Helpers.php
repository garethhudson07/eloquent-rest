<?php

namespace EloquentRest\Support;

use ArrayAccess;
use Closure;
use EloquentRest\Collection;

class Helpers
{
    /**
     * The cache of camel-cased words.
     *
     * @var array
     */
    protected static $camelCache = [];

    /**
     * The cache of studly-cased words.
     *
     * @var array
     */
    protected static $studlyCache = [];

    /**
     * Get the class "basename" of the given object / class.
     *
     * Based on class_basename function within the Laravel Framework
     *
     * @param  string|object  $class
     * @return string
     */
    public static function classBasename($class): string
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }

    /**
     * Determine whether the given value is array accessible.
     *
     * Based on Illuminate\Support\Arr::accessible() method within the Laravel Framework
     *
     * @param  mixed  $value
     * @return bool
     */
    public static function accessible($value)
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * Based on Illuminate\Support\Arr::exists() method within the Laravel Framework
     *
     * @param  \ArrayAccess|array  $array
     * @param  string|int  $key
     * @return bool
     */
    public static function exists($array, $key)
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        if (is_float($key)) {
            $key = (string) $key;
        }

        return array_key_exists($key, $array);
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * Based on Illuminate\Support\Arr::get() method within the Laravel Framework
     *
     * @param  \ArrayAccess|array  $array
     * @param  string|int|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        if (!static::accessible($array)) {
            return static::value($default);
        }

        if (is_null($key)) {
            return $array;
        }

        if (static::exists($array, $key)) {
            return $array[$key];
        }

        if (!str_contains($key, '.')) {
            return $array[$key] ?? static::value($default);
        }

        foreach (explode('.', $key) as $segment) {
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return static::value($default);
            }
        }

        return $array;
    }

    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * Based on Illuminate\Support\Arr::forget() method within the Laravel Framework
     *
     * @param  array  $array
     * @param  array|string|int|float  $keys
     * @return void
     */
    public static function forget(&$array, $keys)
    {
        $original = &$array;

        $keys = (array) $keys;

        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            // if the exact key exists in the top-level, remove it
            if (static::exists($array, $key)) {
                unset($array[$key]);

                continue;
            }

            $parts = explode('.', $key);

            // clean up before each pass
            $array = &$original;

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && static::accessible($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }

            unset($array[array_shift($parts)]);
        }
    }

    /**
     * Get a value from the array, and remove it.
     *
     * Based on Illuminate\Support\Arr::pull() method within the Laravel Framework
     *
     * @param  array  $array
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function pull(&$array, $key, $default = null)
    {
        $value = static::get($array, $key, $default);

        static::forget($array, $key);

        return $value;
    }

    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * Based on Illuminate\Support\Arr::flatten() method within the Laravel Framework
     *
     * @param  iterable  $array
     * @param  int  $depth
     * @return array
     */
    public static function flatten($array, $depth = INF)
    {
        $result = [];

        foreach ($array as $item) {
            $item = $item instanceof Collection ? $item->all() : $item;

            if (!is_array($item)) {
                $result[] = $item;
            } else {
                $values = $depth === 1
                    ? array_values($item)
                    : static::flatten($item, $depth - 1);

                foreach ($values as $value) {
                    $result[] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Convert a value to camel case.
     *
     * Based on Illuminate\Support\Str::camel() method within the Laravel Framework
     *
     * @param  string  $value
     * @return string
     */
    public static function camel($value)
    {
        if (isset(static::$camelCache[$value])) {
            return static::$camelCache[$value];
        }

        return static::$camelCache[$value] = lcfirst(static::studly($value));
    }

    /**
     * Replace the given value in the given string.
     *
     * Based on Illuminate\Support\Str::replace() method within the Laravel Framework
     *
     * @param  string|string[]  $search
     * @param  string|string[]  $replace
     * @param  string|string[]  $subject
     * @return string
     */
    public static function replace($search, $replace, $subject)
    {
        return str_replace($search, $replace, $subject);
    }

    /**
     * Returns the portion of the string specified by the start and length parameters.
     *
     * Based on Illuminate\Support\Str::substr() method within the Laravel Framework
     *
     * @param  string  $string
     * @param  int  $start
     * @param  int|null  $length
     * @return string
     */
    public static function substr($string, $start, $length = null)
    {
        return mb_substr($string, $start, $length, 'UTF-8');
    }

    /**
     * Convert the given string to upper-case.
     *
     * Based on Illuminate\Support\Str::upper() method within the Laravel Framework
     *
     * @param  string  $value
     * @return string
     */
    public static function upper($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    /**
     * Make a string's first character uppercase.
     *
     * Based on Illuminate\Support\Str::ucfirst() method within the Laravel Framework
     *
     * @param  string  $string
     * @return string
     */
    public static function ucfirst($string)
    {
        return static::upper(static::substr($string, 0, 1)) . static::substr($string, 1);
    }

    /**
     * Convert a value to studly caps case.
     *
     * Based on Illuminate\Support\Str::studly() method within the Laravel Framework
     *
     * @param  string  $value
     * @return string
     */
    public static function studly($value)
    {
        $key = $value;

        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }

        $words = explode(' ', static::replace(['-', '_'], ' ', $value));

        $studlyWords = array_map(fn ($word) => static::ucfirst($word), $words);

        return static::$studlyCache[$key] = implode($studlyWords);
    }

    /**
     * Return the default value of the given value.
     *
     * Based on value function within the Laravel Framework
     *
     * @param  mixed  $value
     * @return mixed
     */
    function value($value, ...$args)
    {
        return $value instanceof Closure ? $value(...$args) : $value;
    }
}
