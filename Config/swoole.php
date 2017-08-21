<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/10/14
 * Time: 21:01
 */
return [
    'name' => env('APP_SERVER_NAME', 'TSF'),
//    'mode' => env('APP_SERVER_MODE', SWOOLE_PROCESS),
    'type' => env('APP_SERVER_TYPE', 'WEBSOCKET'),
    'host' => env('APP_SERVER_HOST', '0.0.0.0'),
    'port' => env('APP_SERVER_PORT', 9501),
    //'pidDir' => env('APP_SERVER_PID_DIR', realpath(__DIR__ . '/../Storage/Pid')),
    'swoole' => [
        'worker_num' => 1,
        'daemonize' => false,
        'log_file' => '/tmp/swoole.log'
    ],
];