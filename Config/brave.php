<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/5/24
 * Time: 10:08
 */
return [
    'debug' => env('APP_DEBUG', true),
    'log' => [
        'logFileName' => env('APP_LOG_FILENAME', 'TSF'),
        'logDecorator' => false
    ],
    'mysqlpool' => [
        'conns' => [
            'test' => [
                'serverInfo' => [
                    'timeout' => 1, 'host' => '192.168.244.128', 'user' => 'mha_manager',
                    'password' => 'mhapass', 'database' => 'tt', 'charset' => 'utf8'
                ],
                'maxSpareConns' => 10,
                'maxConns' => 20
            ]
        ],
        //set this when use mysql proxy
        'unixDomainSocket' => env('APP_COMPONENT_MYSQLPOOL_SOCK', '/tmp/TSF-mysqlproxy.sock'),
        'maxProxyConns' => 10,
        'maxSpareProxyConns' => 5
    ],
];