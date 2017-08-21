<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/5/24
 * Time: 10:08
 */
return [
    'allowIPList' => ["192.168.66.222","192.168.66.136","192.168.66.20","192.168.66.223","192.168.66.234","127.0.0.1","192.168.66.71","192.168.66.12","192.168.66.225"],
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