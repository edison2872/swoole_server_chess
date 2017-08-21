<?php
/**
 * Created by PhpStorm.
 * User: alvinzhu
 * Date: 2016/12/12
 * Time: 20:09
 */

namespace TSF\Contract\Kernel;


class Datagram implements Base
{
    public function beforeServerStart($server)
    {
        // TODO: Implement beforeServerStart() method.
    }

    public function onConnect($server, $fd, $fromId)
    {
        // TODO: Implement onConnect() method.
    }

    public function onReceive($server, $fd, $fromId, $data)
    {
        // TODO: Implement onReceive() method.
    }

    public function onClose($server, $fd, $fromId)
    {
        // TODO: Implement onClose() method.
    }

    public function onPacket($server, $data, $addr)
    {
        // TODO: Implement onPacket() method.
    }

    public function onMasterStart($server)
    {
        // TODO: Implement onMasterStart() method.
    }

    public function onShutdown($server)
    {
        // TODO: Implement onShutdown() method.
    }

    public function onWorkerStart($server, $workerId)
    {
        // TODO: Implement onWorkerStart() method.
    }

    public function onWorkerStop($server, $workerId)
    {
        // TODO: Implement onWorkerStop() method.
    }

    public function onTask($server, $fd, $fromId, $data)
    {
        // TODO: Implement onTask() method.
    }

    public function onFinish($server, $taskId, $data)
    {
        // TODO: Implement onFinish() method.
    }

    public function onWorkerError($server, $workerId)
    {
        // TODO: Implement onWorkerError() method.
    }

    public function onManagerStart($server)
    {
        // TODO: Implement onManagerStart() method.
    }

    public function onManagerStop($server)
    {
        // TODO: Implement onManagerStop() method.
    }

    public function onRequest($request, $response)
    {
        // TODO: Implement onRequest() method.
    }

    public function onOpen($server,$request)
    {

    }
    public function onMessage($server,$frame){

    }
}