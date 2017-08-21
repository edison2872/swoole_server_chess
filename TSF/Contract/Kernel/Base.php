<?php

/**
 * Created by PhpStorm.
 * User: alvinzhu
 * Date: 2016/8/18
 * Time: 17:31
 */
namespace TSF\Contract\Kernel;

interface Base
{
    public function beforeServerStart($server);
    public function onConnect($server, $fd, $fromId);
    public function onReceive($server, $fd, $fromId, $data);
    public function onClose($server, $fd, $fromId);
    public function onPacket($server, $data, $addr);
    public function onMasterStart($server);
    public function onShutdown($server);
    public function onWorkerStart($server, $workerId);
    public function onWorkerStop($server, $workerId);
    public function onTask($server, $fd, $fromId, $data);
    public function onFinish($server, $taskId, $data);
    public function onWorkerError($server, $workerId, $workerPid, $exitCode, $signal);
    public function onManagerStart($server);
    public function onManagerStop($server);
    public function onRequest($request, $response);
    public function onOpen($server,$request);
    public function onMessage($server,$frame);
    public function onHandShake($request, $response);
}