<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/10/28
 * Time: 20:30
 */
return [
    'default' => "read",
    //set this when use mysql proxy
    /*
    'unixDomainSocket' => env('APP_COMPONENT_MYSQLPOOL_SOCK', '/tmp/TSF-mysqlproxy.sock'),
    'maxProxyConns' => 10,
    'maxSpareProxyConns' => 5
    */
    'conns' => [
        "read" =>[
            "maxSpareConns" => 50,
            "maxConns" => 50,
            "serverInfo" => [
                "host" => "192.168.66.221",
                "user" => "root",
                "password" => "root123456",
                "database" =>"war",
                "port" => 3306 ,
                "timeout" => 5,
                "charset" => "utf8"
            ],
        ],
        "write" =>[
            "maxSpareConns" => 50,
            "maxConns" => 50,
            "serverInfo" => [
                "host" => "192.168.66.221",
                "user" => "root",
                "password" => "root123456",
                "database" =>"war",
                "port" => 3306 ,
                "timeout" => 5,
                "charset" => "utf8"
            ],
        ],
    ],
];