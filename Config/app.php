<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/8/20
 * Time: 12:11
 */
return [
    'debug' => env('APP_DEBUG', false),
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
