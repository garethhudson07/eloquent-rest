<?php

namespace EloquentRest\Support;

class Helpers
{
    /**
     * @param mixed $class
     * @return string
     */
    public static function classBasename($class): string
    {
        return '';
    }

    /**
     * @param array $array
     * @param string $field
     * @return mixed
     */
    public static function arrayPull(array $array, string $field)
    {
        return $array[$field] ?? null;
    }

    /**
     * @param array $array
     * @return array
     */
    public static function arrayFlatten(array $array): array
    {
        return [];
    }

    /**
     * @param string $string
     * @return string
     */
    public static function strCamelCase(string $string): string
    {
        return '';
    }
}
