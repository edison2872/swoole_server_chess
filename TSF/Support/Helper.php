<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/8/20
 * Time: 13:28
 */

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Container\Container;


if (!function_exists('env')) {
    function env($key, $default = null)
    {
        if (!isset($_ENV[$key])) {
            return $default;
        }

        $value = $_ENV[$key];

        switch (strtolower($value)) {
            case 'true':
                return true;
            case 'false':
                return false;
            case 'null':
                return null;
            case 'empty':
                return '';
        }

        if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}

/*
 * @see Illuminate\Support\Str
 */
if (!function_exists('str_random')) {
    function str_random($length = 16)
    {
        if (!function_exists('openssl_random_pseudo_bytes'))
        {
            throw new HelperException('OpenSSL extension is required.');
        }

        $bytes = openssl_random_pseudo_bytes($length * 2);

        if ($bytes === false)
        {
            throw new HelperException('Unable to generate random string.');
        }

        return substr(str_replace(array('/', '+', '='), '', base64_encode($bytes)), 0, $length);
    }
}

if (!function_exists('array_get')) {
    function array_get($array, $key, $default = null)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }
}
