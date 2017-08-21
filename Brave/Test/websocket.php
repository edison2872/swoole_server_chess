<?php
/**
 * Created by PhpStorm.
 * User: edison
 * Date: 2017/5/27
 * Time: 上午10:41
 */

$swooleServer = new \Swoole\Websocket\Server("0.0.0.0", 9510);

$swooleServer->on('Open', function($server,$request) {
    echo $request->fd." is connect.\n";
});
$swooleServer->on('Message', function ($server,$frame){
    $server->push($frame->fd,"receive message");
});
$swooleServer->on('Close', function ($server,$frame){
    echo "client {$frame->fd} is offline.\n";
});
$swooleServer->set([
    'worker_num' => 1,
    'max_conn' => 1024,
    'daemonize' => false,
    'log_file' => '/data/log/swoole.log',
    'log_level' => 0
]);
$swooleServer->start();
