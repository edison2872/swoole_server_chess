<?php
/**
 * Created by PhpStorm.
 * User: Skies
 * Date: 2/27/17
 * Time: 6:05 PM
 */
namespace TSF\Contract\Kernel;

abstract class WebSocket implements Base{
    public function beforeServerStart($server){}
    public function onConnect($server, $fd, $fromId) {}
    public function onReceive($server, $fd, $fromId, $data) {}

    public function onPacket($server, $data, $addr) {}
    public function onMasterStart($server) {}
    public function onShutdown($server) {}
    public function onWorkerStart($server, $workerId) {}
    public function onWorkerStop($server, $workerId) {}
    public function onTask($server, $fd, $fromId, $data) {}
    public function onFinish($server, $taskId, $data) {}
    public function onWorkerError($server, $workerId, $workerPid, $exitCode, $signal){}
    public function onManagerStart($server) {}
    public function onManagerStop($server) {}
    public function onRequest($request, $response){}

    abstract public function onOpen($server,$request);
    abstract public function onMessage($server,$frame);
    abstract public function onClose($server, $fd, $fromId);
    abstract public function onHandShake($request, $response);
}