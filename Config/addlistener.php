<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/5/17
 * Time: 15:43
 */
return [
    'type' => SWOOLE_SOCK_TCP,
    'host' => env('ADD_LISTENER_HOST', '0.0.0.0'),
    'port' => env('ADD_LISTENER_PORT', 9502),
    'set' => [
        /*
        "open_eof_check" => true,
        "open_eof_split" => true,
        "package_eof" => "\r\n",
        */
    ]
];