<?php
namespace TSF\Component\Reload;
use TSF\Core\Server;
use TSF\Facade\App;

/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 17-3-10
 * Time: 上午10:13
 */
class ReloadHelp
{
    /**
     * 开启进程
     */
    public static function startProcess($urls)
    {
        $reload_process = new \swoole_process(function ($process) use ($urls){
//            $process->name(App::facade()->conf["name"] . '-reload');
//            new InotifyProcess(Server::$swooleServer, $urls);
        }, false, 2);
        Server::$swooleServer->addProcess($reload_process);
    }
}