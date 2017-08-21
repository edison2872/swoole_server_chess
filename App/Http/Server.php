<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/9/6
 * Time: 20:12
 */

//引入框架的autoload文件
$classLoader = require __DIR__ . '/../../vendor/autoload.php';

//配置业务代码能够被自动加载
$classLoader->setPsr4('App\\', [__DIR__ . '/../../App']);

(new josegonzalez\Dotenv\Loader(__DIR__ . '/../../.env'))->parse()->toEnv();

$server = new TSF\Core\Server('server');

$server->bind('TSF\Contract\Kernel\Base', 'TSF\WebSocket\WebSocketKernel');

$server->singleton('TSF\Http\HttpExceptionHandler', 'App\Http\Exception\Handler');

$server->start();
