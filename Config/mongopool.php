<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/7/26
 * Time: 16:00
 */
return [
    'default' => "read",
    'conns' => [
        "read" =>[
            "uri" => "mongodb://192.168.66.222:27017",
            "options" => [
                /*"username" => "root",
                "password" => "root123456",*/
                "serverSelectionTryOnce" => false,
                "serverSelectionTimeoutMS" => 1000
            ],
        ],
        "write" =>[
            "uri" => "mongodb://192.168.66.222:27017",
            "options" => [
                /*"username" => "root",
                "password" => "root123456", */
                "serverSelectionTryOnce" => false,
                "serverSelectionTimeoutMS" => 1000
            ],
        ],
    ],
];