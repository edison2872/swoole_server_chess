<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/5/18
 * Time: 16:21
 */
namespace TSF\Tcp;

use TSF\Core\Server;
use TSF\Facade\App;
use TSF\Tcp\TcpRequest;

class TcpRoute
{
    protected static $groupMiddlewares = [];

    public function loadConfig()
    {
        \App\Tcp\routes::loadRoutes();
    }

    public function dispatch(TcpRequest $request)
    {
        $controller = $request->get('Controller', null);
        $action = $request->get('Action', null);

        if (empty($controller) || empty($action)) {
            throw new \Exception('Module or interface does not exist!', 10001);
        }

        foreach ($this->getBeforeMiddleWare($controller,$action) as $middleware) {
            //TODO: container make middleware
            $middlewareModel = Server::$container->make("App\\Middleware\\$middleware" . "Middleware");
            $middlewareModel->handle($request);
        }

        $controller_m = Server::$container->make("App\\Controller\\$controller" . "Controller");
        if (is_callable([$controller_m,$action])) {
            // $params     = Server::$container->resolveParametersForMethod($controller, $ac, []);
            \Swoole\Coroutine::call_user_func(array($controller_m, $action));
            // \Swoole\Coroutine::call_user_func_array([$controller, $ac], $params);
            // call_user_func_array([$controller, $ac], $params);
            // $controller->$ac();
            echo "call_end" . PHP_EOL;
        } else {
            throw new \Exception('Module or interface does not exist!', 10001);
        }

        foreach ($this->getAfterMiddleWare($controller,$action) as $middleware) {
            //TODO: container make middleware
            $middlewareModel = Server::$container->make("App\\Middleware\\$middleware" . "Middleware");
            $middlewareModel->handle($request);
        }
    }

    public function controller(string $handler,array $middlewares = [])
    {
        list($controller,$action) = explode("@",$handler);
        self::$groupMiddlewares[$controller][$action] = $middlewares;
    }

    private function getBeforeMiddleWare(string $controller,string $action)
    {
        if (isset(self::$groupMiddlewares[$controller])) {
            if (isset(self::$groupMiddlewares[$controller][$action])) {
                return isset(self::$groupMiddlewares[$controller][$action]["beforeMiddleWare"])?self::$groupMiddlewares[$controller][$action]["beforeMiddleWare"]:[];
            } else if (isset(self::$groupMiddlewares[$controller]["*"])) {
                return isset(self::$groupMiddlewares[$controller]["*"]["beforeMiddleWare"])?self::$groupMiddlewares[$controller]["*"]["beforeMiddleWare"]:[];
            }
        } else if (isset(self::$groupMiddlewares["*"])) {
            if (isset(self::$groupMiddlewares["*"][$action])) {
                return isset(self::$groupMiddlewares["*"][$action]["beforeMiddleWare"])?self::$groupMiddlewares["*"][$action]["beforeMiddleWare"]:[];
            } else if (isset(self::$groupMiddlewares["*"]["*"])) {
                return isset(self::$groupMiddlewares["*"]["*"]["beforeMiddleWare"])?self::$groupMiddlewares["*"]["*"]["beforeMiddleWare"]:[];
            }
        }
        return [];
    }

    private function getAfterMiddleWare(string $controller,string $action)
    {
        if (isset(self::$groupMiddlewares[$controller])) {
            if (isset(self::$groupMiddlewares[$controller][$action])) {
                return isset(self::$groupMiddlewares[$controller][$action]["afterMiddleWare"])?self::$groupMiddlewares[$controller][$action]["afterMiddleWare"]:[];
            } else if (isset(self::$groupMiddlewares[$controller]["*"])) {
                return isset(self::$groupMiddlewares[$controller]["*"]["afterMiddleWare"])?self::$groupMiddlewares[$controller]["*"]["afterMiddleWare"]:[];
            }
        } else if (isset(self::$groupMiddlewares["*"])) {
            if (isset(self::$groupMiddlewares["*"][$action])) {
                return isset(self::$groupMiddlewares["*"][$action]["afterMiddleWare"])?self::$groupMiddlewares["*"][$action]["afterMiddleWare"]:[];
            } else if (isset(self::$groupMiddlewares["*"]["*"])) {
                return isset(self::$groupMiddlewares["*"]["*"]["afterMiddleWare"])?self::$groupMiddlewares["*"]["*"]["afterMiddleWare"]:[];
            }
        }
        return [];
    }
}