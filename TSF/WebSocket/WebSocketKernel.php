<?php
/**
 * Created by PhpStorm.
 * User: LQB
 * Date: 2/28/17
 * Time: 11:27 AM
 */
namespace TSF\WebSocket;

use App\Framework\Base\MongoDBName;
use TSF\Contract\Kernel\WebSocket;
use TSF\Core\Log;
use TSF\Facade\App;
use TSF\Facade\Config;
use TSF\Facade\WebSocket\Route;

class WebSocketKernel extends WebSocket
{
    public function onWorkerStart($server, $workerId)
    {
        parent::onWorkerStart($server, $workerId); // TODO: Change the autogenerated stub

        Log::init(Config::facade()->get('app.log'));
        $route = new WSRoute();
        App::facade()->globalSingleton('TSF\WebSocket\Route', $route);
        Route::facade()->loadConfig();
        App::facade()->singleton('App\Framework\Facade\Connection',"App\\Framework\\Facade\\Connection");

        swoole_timer_tick(10000, function () use ($server) {
            $conn_list = $server->connection_list(0, 10);
            if($conn_list===false or count($conn_list) === 0)
            {
                echo "connection_list empty\n";
            } else {
                foreach($conn_list as $fd)
                {
                    $fdinfo = $server->connection_info($fd);
                    //$server->send($fd, "broadcast");
                    if ($fdinfo['server_port'] == 9501) {
                        $server->push($fd, "broadcast");
                    } else {
                        $server->send($fd, "broadcast");
                    }
                }
            }
        });
    }

    public function onOpen($server, $request)
    {
        // TODO: Implement onOpen() method.
        echo $request->server['remote_addr'] . ':' . $request->server['remote_port'] . "_已连接,fd:{$request->fd}" . PHP_EOL;
        // $server->push($request->fd, "You have been connect server");
        // echo "onOpen" . PHP_EOL;
    }

    public function onMessage($server, $frame)
    {
        // TODO: Implement onMessage() method.
        $request = new WSRequest($frame);
        $response = new WSResponse($server, $frame->fd);
        App::facade()->singleton('TSF\\WebSocket\\WSRequest', $request);
        App::facade()->singleton('TSF\\WebSocket\\WSResponse', $response);

        $MongoDBName = new MongoDBName();
        $MongoDBName->setMongoDBName("server1");
        App::facade()->singleton('App\\Framework\\Facade\\MongoDBName', $MongoDBName);

        try {
            Route::facade()->dispatch($request);
        } catch (\Exception $e) {
            App::facade()->make('TSF\\WebSocket\\WebSocketExceptionHandler')->render($request, $e);
        }
        $response->send();
        App::facade()->clearCurrentSingleton();
    }

    public function onHandShake($request, $response)
    {
        // TODO: Implement onHandShake() method.
    }

    public function onClose($server, $fd, $fromId)
    {
        // TODO: Implement onClose() method.
        echo "fd {$fd} 断开连接\n";
    }



    public function onManagerStart($server)
    {
        parent::onManagerStart($server);
        echo "webSocket Service is started\n";
        // var_dump(get_included_files());
    }

    public function onReceive($server, $fd, $fromId, $data)
    {
        parent::onReceive($server, $fd, $fromId, $data); // TODO: Change the autogenerated stub
        echo $data;
    }

    public function onWorkerStop($server, $workerId)
    {
        parent::onWorkerStop($server, $workerId); // TODO: Change the autogenerated stub
        echo "worker Stop -" . $workerId . PHP_EOL;
    }

    public function onManagerStop($server)
    {
        parent::onManagerStop($server); // TODO: Change the autogenerated stub
        echo "manager stop";
    }

    public function onWorkerError($server, $workerId, $workerPid, $exitCode, $signal)
    {
        parent::onWorkerError($server, $workerId, $workerPid, $exitCode, $signal); // TODO: Change the autogenerated stub
        echo "WorkerError" . $workerId . PHP_EOL;
        Log::warning(" swManager_check_exit_status: worker#$workerId abnormal exit, status=$exitCode, signal=$signal");
    }

    public function onConnect($server, $fd, $fromId)
    {
        parent::onConnect($server, $fd, $fromId); // TODO: Change the autogenerated stub
        // var_dump($server->getLastError());
    }
}