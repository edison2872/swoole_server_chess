<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/5/24
 * Time: 10:51
 */
return [
    'conns' => [
        'server0' => [
            'serverInfo' => [
                'host' => '127.0.0.1',
                'port'=> 6379,
                // 'password' => 'root123456',
                // 'database' => 2
            ],
            'maxSpareConns' => 10,
            'maxConns' => 20
        ],
        'read' => [
            'serverInfo' => [
                'host' => '127.0.0.1',
                'port'=> 6379,
                // 'password' => 'root123456',
                // 'database' => 2
            ],
            'maxSpareConns' => 10,
            'maxConns' => 20
        ]
    ],
    'default' => "read"
];