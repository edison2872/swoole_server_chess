<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/5/18
 * Time: 16:21
 */
namespace TSF\WebSocket;

use TSF\Core\Server;
use TSF\Facade\App;

class WSRoute
{

    protected $Routes;

    public function loadConfig()
    {
        $arr_app_module = [];
        require App::facade()->getBasePath() . '/Brave/WebSocket/routes.php';
        $this->Routes = $arr_app_module;
    }

    public function dispatch(WSRequest $request)
    {
        $m = $request->get('m', null);
        $ac = $request->get('ac', null);

        if (empty($m) || empty($ac)) {
            throw new \Exception('Module or interface does not exist!', 10001);
        }

        if (!isset($this->Routes[$m][$ac])) {
            throw new \Exception('Module or interface does not exist', 10001);
        }

        if (count($this->Routes[$m][$ac]['bf']) > 0) {
            foreach ($this->Routes[$m][$ac]['bf'] as $middleware) {
                //TODO: container make middleware
                $middlewareModel = Server::$container->make("App\\Middleware\\$middleware" . "Middleware");
                $middlewareModel->handle($request);
            }
        }

        $controller = Server::$container->make("App\\Controller\\$m" . "Controller");

        // $params     = Server::$container->resolveParametersForMethod($controller, $ac, []);
        \Swoole\Coroutine::call_user_func(array($controller, $ac));
        // \Swoole\Coroutine::call_user_func_array([$controller, $ac], $params);
        // call_user_func_array([$controller, $ac], $params);
        // $controller->$ac();
        echo "call_end" . PHP_EOL;

        if (count($this->Routes[$m][$ac]['af']) > 0) {
            foreach ($this->Routes[$m][$ac]['af'] as $middleware) {
                $middlewareModel = Server::$container->make("App\\Middleware\\$middleware" . "Middleware");
                $middlewareModel->handle($request);
            }
        }
    }
}