<?php

/**
 * Created by PhpStorm.
 * User: alvinzhu
 * Date: 2016/12/29
 * Time: 21:24
 */
namespace TSF\Component\Task;

use TSF\Component\Base as BaseComponent;
use TSF\Core\Server;
use TSF\Facade\App;
use TSF\Facade\Config;

class TaskManager extends BaseComponent
{
    private $namespace;
    private $taskNumber;

    public function beforeServerStart($server)
    {
        parent::beforeServerStart($server);
        $server->on("Task", [$this, "onTask"]);
        $server->on("Finish", [$this, "onFinish"]);
        $this->namespace = isset($this->conf['namespace']) ? $this->conf['namespace'] : "";
        $this->taskNumber = isset($this->conf['task_worker_num']) ? $this->conf['task_worker_num'] : 1;
        App::facade()->conf['swoole']['task_worker_num'] = $this->taskNumber;
    }

    public function onTask($serv, $task_id, $src_worker_id, $data)
    {
        $package= json_decode($data, true);
        $service = $package['service'];
        $cmd = $package['cmd'];
        $data = $package['data'];

        $className = "{$this->namespace}\\{$service}Task";
        $instance = new $className();
        $instance->handle($cmd, $data);
    }

    public function onFinish()
    {
    }

    static public function send($data, $workerID = -1){
        return Server::$swooleServer->task($data, $workerID);
    }

    static public function sendto($service, $cmd, $data, $workerID = -1) {
        $tmp = [
            'service' => $service,
            'cmd' => $cmd,
            'data' => $data,
        ];
        return Server::$swooleServer->task(json_encode($tmp), $workerID);
    }
}