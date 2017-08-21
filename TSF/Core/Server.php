<?php
/**
 * Created by PhpStorm.
 * User: alvinzhu
 * Date: 2016/8/2
 * Time: 11:51
 */
namespace TSF\Core;

use TSF\Component\Reload\ReloadHelp;
use TSF\Exception\Core\ServerException;

class Server extends Container
{
    CONST SERVER_TYPE_HTTP = 'HTTP';
    CONST SERVER_TYPE_TCP = 'TCP';
    CONST SERVER_TYPE_UDP = 'UDP';
    CONST SERVER_TYPE_UNIXSTREAM = 'UNIXSTREAM';
    CONST SERVER_TYPE_WEBSOCKET = 'WEBSOCKET';

    protected $basePath;

    /**@var $kernel \TSF\Contract\Kernel\Base * */
    protected $kernel;
    protected $masterPidFile;
    protected $masterPidFileFD;
    protected $managerPidFile;
    protected $components;


    public $id;
    public $conf;
    public $isWorker = false;
    public $isTask = false;

    static public $container;
    static public $swooleServer;
    static public $swoolePort;

    public function __construct($appName, $loadConfig = "swoole",$basePath = '')
    {
        if ($basePath == '') {
            $this->basePath = realpath(__DIR__ . '/../../');
        } else {
            $this->basePath = $basePath;
        }

        self::$container = $this;
        $this->globalSingleton('App', $this);

        $c = new Config();
        $c->setAppName($appName);
        $this->globalSingleton('TSF\Core\Config', $c);
        $this->conf = array_merge([
            'name' => 'TSF',
            'mode' => SWOOLE_PROCESS,
            'type' => 'HTTP',
            'host' => '0.0.0.0',
            'port' => 9503,
            'pidDir' => $this->basePath . '/Storage/Pid',
            'swoole' => [
                'worker_num' => 1,
                'max_conn' => 1024,
                'daemonize' => true,
                'log_file' => '/data/log/swoole.log',
                'log_level' => 0
            ],
        ], $c->get($loadConfig));

        $this->masterPidFile = $this->conf['pidDir'] . DIRECTORY_SEPARATOR . $this->conf['name'] . '-master.pid';
        $this->managerPidFile = $this->conf['pidDir'] . DIRECTORY_SEPARATOR . $this->conf['name'] . '-manager.pid';
    }

    protected function startSwooleServer()
    {
        $this->kernel = $this->make('TSF\Contract\Kernel\Base');
        if ($this->conf['mode'] == SWOOLE_BASE) {
            swoole_async_set(['enable_reuse_port' => true]);
        }

        switch ($this->conf['type']) {
            case self::SERVER_TYPE_HTTP:
                self::$swooleServer = new \Swoole\Http\Server($this->conf['host'], $this->conf['port'], $this->conf['mode']);
                self::$swooleServer->on('Request', array($this, 'onRequest'));
                break;
            case self::SERVER_TYPE_TCP:
                self::$swooleServer = new \Swoole\Server($this->conf['host'], $this->conf['port'], $this->conf['mode'], SWOOLE_SOCK_TCP);
                self::$swooleServer->on('Connect', array($this, 'onConnect'));
                self::$swooleServer->on('Receive', array($this, 'onReceive'));
                break;
            case self::SERVER_TYPE_UDP:
                self::$swooleServer = new \Swoole\Server($this->conf['host'], $this->conf['port'], $this->conf['mode'], SWOOLE_SOCK_UDP);
                self::$swooleServer->on('Packet', array($this, 'onPacket'));
                break;
            case self::SERVER_TYPE_UNIXSTREAM:
                self::$swooleServer = new \Swoole\Server($this->conf['unixDomainSocket'], 0, $this->conf['mode'], SWOOLE_SOCK_UNIX_STREAM);
                self::$swooleServer->on('Connect', array($this, 'onConnect'));
                self::$swooleServer->on('Receive', array($this, 'onReceive'));
                break;
            case self::SERVER_TYPE_WEBSOCKET:
                self::$swooleServer = new \Swoole\Websocket\Server($this->conf['host'], $this->conf['port']);  // mode不能使用 BASE
                self::$swooleServer->on('Open', array($this, 'onOpen'));
                self::$swooleServer->on('Message', array($this, 'onMessage'));
                // self::$swooleServer->on('handshake', array($this, 'onHandShake'));
                break;
            default:
                error_log("server conf : " . print_r($this->conf, true) . PHP_EOL, 3, '/data/log/swoole.log');
                throw new ServerException('Unrecognize server type: ' . $this->conf['type']);
        }

        self::$swooleServer->on('Start', array($this, 'onMasterStart'));
        self::$swooleServer->on('ManagerStart', array($this, 'onManagerStart'));
        self::$swooleServer->on('WorkerStart', array($this, 'onWorkerStart'));
        self::$swooleServer->on('Close', array($this, 'onClose'));
        self::$swooleServer->on('WorkerStop', array($this, 'onWorkerStop'));
        self::$swooleServer->on('Shutdown', array($this, 'onShutdown'));
        self::$swooleServer->on('WorkerError', array($this, 'onWorkerError'));
        $this->kernel->beforeServerStart(self::$swooleServer);
        $config = $this->make('TSF\Core\Config');
        $components = $config->get('component', null);
        if ($components !== null and is_array($components)) {
            foreach ($components as $key => $component) {
                $instance = new $key;
                if ($instance instanceof \TSF\Component\Base) {
                    $instance->setConf($component);
                    $instance->beforeServerStart(self::$swooleServer);
                }
                $this->globalSingleton($key, $instance);
                $component['instance'] = $instance;
                $this->components[$key] = $component;
            }
        }
        // 可增加一个TCP或者UDP监听

        $addListener = $config->get("addlistener", null);

        if ($addListener !== null and is_array($addListener) && $addListener["type"] == $this->conf['type']) {
            $swoolePort = self::$swooleServer->addlistener($addListener["host"], $addListener["port"], $addListener["type"]);
            if (!empty($addListener["set"])) {
               $swoolePort->set($addListener["set"]);
            }
            $swoolePort->on('Connect', array($this, 'onConnect'));
            $swoolePort->on('Close', array($this, 'onClose'));
            if ($addListener["type"] == SWOOLE_SOCK_TCP) {
                $swoolePort->on('Receive', array($this, 'onReceive'));
            } else if ($addListener["type"] == SWOOLE_SOCK_UDP) {
                $swoolePort->on('Packet', array($this, 'onPacket'));
            }
        }

//        $relod = $config->get("reload", null);
//        if ($relod !== null and is_array($relod) ) {
//            if ($relod["enable"] == true) {
//                ReloadHelp::startProcess($relod["urls"]);
//            }
//        }
        self::$swooleServer->set($this->conf['swoole']);
        self::$swooleServer->start();
    }

    public function start()
    {
        $this->isRunning();
        $this->tryLockMasterPidFile();
        $this->startSwooleServer();
    }

    public function stop()
    {
        self::$swooleServer->stop();
    }

    public function shutdown()
    {
        $pid = $this->getMasterPid();
        posix_kill($pid, 15);
        return true;
    }

    public function reload()
    {
        $pid = $this->getManagerPid();
        posix_kill($pid, SIGUSR1);
        return true;
    }

    public function onShutdown($server)
    {
    }

    public function onReceive($server, $fd, $fromId, $data)
    {
        echo "onreceive-".$fd.PHP_EOL;
        $this->kernel->onReceive($server, $fd, $fromId, $data);
    }

    public function onConnect($server, $fd, $fromId)
    {
        echo "onConnect-".$fd.PHP_EOL;
        $this->kernel->onConnect($server, $fd, $fromId);
    }

    public function onWorkerStart($server, $workerId)
    {
        $this->id = $workerId;
        if ($workerId >= $server->setting['worker_num']) {
            $this->setProcessName($this->conf['name'] . '-task');
            $this->isTask = true;
        } else {
            $this->setProcessName($this->conf['name'] . '-worker');
            $this->isWorker = true;
        }
        $this->kernel->onWorkerStart($server, $workerId);
        if (empty($this->components)) {
            return;
        }
        foreach ($this->components as $key => $component) {
            $instance = $component['instance'];
            if ($instance instanceof \TSF\Component\Base) {
                $instance->onStart(self::$swooleServer);
            }
        }
    }

    public function onWorkerStop($server, $workerId)
    {
        if (!empty($this->components)) {
            foreach ($this->components as $key => $component) {
                $instance = $component['instance'];
                if ($instance instanceof \TSF\Component\Base) {
                    $instance->onStop(self::$swooleServer);
                }
            }
        }

        $this->kernel->onWorkerStop($server, $workerId);
    }

    public function onWorkerError($server, $workerId, $workerPid, $exitCode, $signal)
    {
        $this->kernel->onWorkerError($server, $workerId, $workerPid, $exitCode, $signal);
    }

    public function onRequest($request, $response)
    {
        $this->kernel->onRequest($request, $response);
    }

    public function onPacket($server, $data, $addr)
    {
        $this->kernel->onPacket($server, $data, $addr);
    }

    public function onClose($server, $fd, $fromId)
    {
        echo "onClose-". $fd.PHP_EOL;
        $this->kernel->onClose($server, $fd, $fromId);
    }

    public function onManagerStart($server)
    {
        $this->setProcessName($this->conf['name'] . '-manager');
        $this->genManagerPidFile();
        $this->kernel->onManagerStart($server);
    }

    public function onMasterStart($server)
    {
        $this->setProcessName($this->conf['name'] . '-master');
        $this->genMasterPidFile();
        $this->kernel->onMasterStart($server);
    }

    public function onOpen($server, $request)
    {
        $this->kernel->onOpen($server, $request);
    }

    public function onMessage($server, $frame)
    {
        $this->kernel->onMessage($server, $frame);
    }

    public function onHandShake($request, $response)
    {
        $this->kernel->onHandShake($request, $response);
    }

    protected function tryLockMasterPidFile()
    {
        if (!flock($this->masterPidFileFD, LOCK_EX | LOCK_NB)) {
            throw new ServerException("Cannot lock masterPidFile: {$this->masterPidFile}");
        }
    }

    protected function isRunning()
    {
        $this->masterPidFileFD = fopen($this->masterPidFile, "w+");
        if ($this->masterPidFileFD == false) {
            throw new ServerException("Cannot open masterPidFile for write: {$this->masterPidFile}");
        }
        if (flock($this->masterPidFileFD, LOCK_EX | LOCK_NB)) {
            flock($this->masterPidFileFD, LOCK_UN);
            return;
        }
        throw new ServerException('Server is running, cannot start again.');
    }

    protected function genMasterPidFile()
    {
        if (fwrite($this->masterPidFileFD, self::$swooleServer->master_pid) === false
            || fflush($this->masterPidFileFD) == false
        ) {
            throw new ServerException('Cannot write master pid to file.');
        }
    }

    protected function getMasterPid()
    {
        if (empty($this->masterPidFile)) {
            throw new ServerException('masterPidFile empty');
        }

        $ret = file_get_contents($this->masterPidFile);
        if ($ret === false or empty($ret)) {
            throw new ServerException('masterPidFile empty');
        }

        return $ret;
    }

    protected function genManagerPidFile()
    {
        if (file_put_contents($this->managerPidFile, self::$swooleServer->manager_pid) === false) {
            throw new ServerException('Cannot write manager pid to file');
        }
    }

    protected function getManagerPid()
    {
        if (empty($this->managerPidFile)) {
            throw new ServerException('managerPidFile empty');
        }

        $ret = file_get_contents($this->managerPidFile);
        if ($ret === false or empty($ret)) {
            throw new ServerException('managerPidFile empty');
        }

        return $ret;
    }

    protected function setProcessName($name)
    {
//        if (isDarwin())
//            return;

        if (function_exists('cli_set_process_title')) {
//            cli_set_process_title($name);
        } else if (function_exists('swoole_set_process_name')) {
            swoole_set_process_name($name);
        } else {
            throw new ServerException("Cannot ser process name.");
        }
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    public function setBasePath($path)
    {

        $this->basePath = $path;
    }
}
