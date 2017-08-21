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
$classLoader->setPsr4('App\\', [__DIR__ . '/../../Brave']);

(new josegonzalez\Dotenv\Loader(__DIR__ . '/../../.env'))->parse()->toEnv();
var_dump(date_default_timezone_get());
date_default_timezone_set("Asia/Shanghai");
var_dump(date_default_timezone_get());
$http_server = new TSF\Core\Server('brave',"http");
var_dump($http_server);

$http_server->bind('TSF\Contract\Kernel\Base', 'TSF\Http\HttpKernel');

$http_server->singleton('TSF\Http\HttpExceptionHandler', 'App\Http\Exception\Handler');

var_dump($_SERVER);
echo "-------env-------\n";
var_dump($_ENV);
$http_server->start();
