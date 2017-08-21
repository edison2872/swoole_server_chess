<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/5/19
 * Time: 14:59
 */

class Server
{
    public function run()
    {
        $swooleServer = new Swoole_Websocket_Server('0.0.0.0', 9501);
        $swooleServer->on('Open', array($this, 'onOpen'));
        $swooleServer->on('Message', array($this, 'onMessage'));

        $swooleServer->on('Start', array($this, 'onMasterStart'));
        $swooleServer->on('ManagerStart', array($this, 'onManagerStart'));
        $swooleServer->on('WorkerStart', array($this, 'onWorkerStart'));
        $swooleServer->on('Close', array($this, 'onClose'));
        $swooleServer->on('WorkerStop', array($this, 'onWorkerStop'));
        $swooleServer->on('Shutdown', array($this, 'onShutdown'));
        $swooleServer->on('WorkerError', array($this, 'onWorkerError'));

        $swoolePort = $swooleServer->addlistener('0.0.0.0', 9502, SWOOLE_SOCK_TCP);

        $swoolePort->on('Connect', array($this, 'onConnect'));
        $swoolePort->on('Close', array($this, 'onClose'));

        $swoolePort->on('Receive', function ($server, $fd, $from_id, $data){
            echo "Receive-" . $data;
        });

        $swooleServer->set([
            'worker_num' => 1,
            'max_conn' => 1024,
            'daemonize' => false,
            'log_file' => '/tmp/swoole.log',
            'log_level' => 0
        ]);

        $swooleServer->start();

    }
    public function stop()
    {

    }

    public function shutdown()
    {

    }

    public function reload()
    {

    }

    public function onShutdown($server)
    {
    }

    public function onReceive($server, $fd, $fromId, $data)
    {
        echo "Receive-DD" . $data;
    }

    public function onConnect($server, $fd, $fromId)
    {
        echo "onConnect-".$fd.PHP_EOL;

    }

    public function onWorkerStart($server, $workerId)
    {

    }

    public function onWorkerStop($server, $workerId)
    {

    }

    public function onWorkerError($server, $workerId, $workerPid, $exitCode, $signal)
    {

    }

    public function onRequest($request, $response)
    {

    }

    public function onPacket($server, $data, $addr)
    {

    }

    public function onClose(Swoole_Websocket_Server $server, $fd, $fromId)
    {
        echo "onClose-" . $fd;
    }

    public function onManagerStart($server)
    {

    }

    public function onMasterStart($server)
    {

    }

    public function onOpen($server, $request)
    {

    }

    public function onMessage($server, $frame)
    {
        echo $frame->data.PHP_EOL;
        $server->push($frame->fd, "this is server");
        call_user_func(function(){
            call_user_func(function (){
                $db = new \Swoole\Coroutine\MySQL();
                $res = $db->connect(["host"=>'192.168.66.222', 'user'=>'root',
                    'password' => 'root123456', 'database' => 'localwar']);
                if ($res == false) {
                    echo "connect fail" . PHP_EOL;
                    return;
                }
                $db->setDefer();
                $rr = $db->query("select version()");
                echo "cor" . PHP_EOL;
                var_dump($db->recv());
            });
            echo "end2".PHP_EOL;
        });
        echo "end";
    }

    public function onHandShake($request, $response)
    {

    }

}

(new Server())->run();
//$test = new Server();
//$reflect = new ReflectionClass($test);
//$methods = $reflect->getMethod("onClose");
//$dependencies = $methods->getParameters();
//var_dump($dependencies);
//foreach ($dependencies as $parameter) {
//    $dependency = $parameter->getClass();
//    var_dump($dependency);
//}
