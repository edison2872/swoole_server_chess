<?php
/**
 * Created by PhpStorm.
 * User: edison
 * Date: 2017/5/27
 * Time: 上午10:41
 */

$swooleServer = new \Swoole\Http\Server("0.0.0.0", 9508, SWOOLE_PROCESS);
$swooleServer->on('Request', function($request,$response) {
    $response->end("response");
});
$swooleServer->set([
    'worker_num' => 1,
    'max_conn' => 1024,
    'daemonize' => false,
    'log_file' => '/data/log/swoole.log',
    'log_level' => 0
]);
$swooleServer->start();
