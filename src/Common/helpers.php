<?php

/**
 * Get an item from an array using "dot" notation.
 *
 * @param  array  $array
 * @param  string $key
 * @param  mixed  $default
 *
 * @return mixed
 */
if (! function_exists('array_get')) {
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  \ArrayAccess|array  $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function array_get($array, $key, $default = null)
    {
        return Arr::get($array, $key, $default);
    }
}

if (! function_exists('array_has')) {
    function array_has($array, $key)
    {
        if (empty($array) || is_null($key)) {
            return false;
        }

        if (array_key_exists($key, $array)) {
            return true;
        }

        foreach (explode('.', $key) as $segment) {
            if (! is_array($array) || ! array_key_exists($segment, $array)) {
                return false;
            }

            $array = $array[$segment];
        }

        return true;
    }
}

/**
 * Return the default value of the given value.
 *
 * @param  mixed $value
 *
 * @return mixed
 */
if (! function_exists('value')) {
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}
