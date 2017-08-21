<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/8/18
 * Time: 21:05
 */
namespace TSF\Http;

use ReflectionClass;
use TSF\Exception\Http\RouteException;
use TSF\Facade\App;

class GroupCountBased extends \FastRoute\DataGenerator\GroupCountBased
{
    protected function parseUri($uri)
    {
        $parseUri = [];
        $state = 'L';
        $offset = 0;
        $leftBrace = 0;
        $rightBrace = 0;
        while (true) {
            switch ($state) {
                case 'L':
                    $leftBrace = strpos($uri, '{', $offset);
                    if ($leftBrace === false) {
                        $rawPartUri = substr($uri, $offset + 1);
                        if (!empty($rawPartUri)) {
                            $parseUri[] = $rawPartUri;
                        }
                        return $parseUri;
                    }
                    if ($offset == 0) {
                        $parseUri[] = substr($uri, 0, $leftBrace - $offset);
                    } else {
                        $parseUri[] = substr($uri, $offset + 1, $leftBrace - $offset - 1);
                    }
                    $offset = $leftBrace;
                    $state = 'R';
                    break;
                case 'R':
                    $rightBrace = strpos($uri, '}', $offset);
                    if ($rightBrace === false) {
                        throw new RouteException('Wrong uriPattern: ' . $uri);
                    }
                    $varRegex = explode(':', substr($uri, $leftBrace + 1, $rightBrace - $leftBrace - 1));
                    if (count($varRegex) != 2) {
                        throw new RouteException('Wrong uriPattern: ' . $uri);
                    }
                    $parseUri[] = $varRegex;
                    $offset = $rightBrace;
                    $state = 'L';
                    break;
            }
        }
    }

    public function addRoute($httpMethod, $uriPattern, $handler)
    {
        parent::addRoute($httpMethod, $this->parseUri($uriPattern), $handler);
    }
}

class Route
{
    protected $gcbRoute;
    protected $gcbDispatcher;
    protected $middlewares;

    protected $groupMiddlewares;
    protected $groupCall;

    public function __construct()
    {
        $this->gcbRoute = new GroupCountBased();
        $this->middlewares = [];
        $this->groupMiddlewares = [];
        $this->groupCall = false;
    }

    public function loadConfig()
    {
//        require App::facade()->getBasePath() . '/App/Http/routes.php';
        \App\Http\routes::loadRoutes();
        $this->gcbDispatcher = new \FastRoute\Dispatcher\GroupCountBased($this->gcbRoute->getData());
    }

    public function dispatch(HttpRequest $request)
    {
        $method = $request->requestMethod;
        $match = $this->gcbDispatcher->dispatch($method, $request->requestUri);

        if ($match[0] === \FastRoute\Dispatcher::NOT_FOUND) {
            throw new RouteException('Route not found.');
        }

        if ($match[0] === \FastRoute\Dispatcher::METHOD_NOT_ALLOWED) {
            throw new RouteException('Method not allowed.');
        }

        $handler = $match[1];
        list($controller, $action, $uriParams) = $handler->resolve($request->requestMethod, $match[2]);
        foreach ($handler->getBeforeMiddlewares() as $m) {
            //TODO: container make middleware
            $m = \TSF\Core\Server::$container->make("App\\Middleware\\$m");
            $m->handle($request);
        }

        $controller = \TSF\Core\Server::$container->make($controller);
        $params     = \TSF\Core\Server::$container->resolveParametersForMethod($controller, $action, $uriParams);

         \Swoole\Coroutine::call_user_func_array([$controller, $action], $params);
//        call_user_func_array([$controller, $action], $params);

        foreach ($handler->getAfterMiddlewares() as $m) {
            $m = \TSF\Core\Server::$container->make("App\\Middleware\\$m");
            $m->handle($request);
        }
    }

    public function get($uriPattern, $handler, $middlewares = [])
    {
        $middlewares = array_merge_recursive($this->groupMiddlewares, $middlewares);
        $handler = new RouteHandler($handler, $middlewares);
//        $this->bindMiddlewares('GET', $handler, $this->groupMiddlewares);
//        $this->bindMiddlewares('GET', $handler, $middlewares);
        $this->gcbRoute->addRoute('GET', $uriPattern, $handler);
    }

    public function post($uriPattern, $handler, $middlewares = [])
    {
        $middlewares = array_merge_recursive($this->groupMiddlewares, $middlewares);
        $handler = new RouteHandler($handler, $middlewares);
//        $this->bindMiddlewares('POST', $handler, $this->groupMiddlewares);
//        $this->bindMiddlewares('POST', $handler, $middlewares);
        $this->gcbRoute->addRoute('POST', $uriPattern, $handler);
    }

    public function put($uriPattern, $handler, $middlewares = [])
    {
        $middlewares = array_merge_recursive($this->groupMiddlewares, $middlewares);
        $handler = new RouteHandler($handler, $middlewares);
        $this->bindMiddlewares('PUT', $handler, $this->groupMiddlewares);
        $this->bindMiddlewares('PUT', $handler, $middlewares);
        $this->gcbRoute->addRoute('PUT', $uriPattern, $handler);
    }

    public function patch($uriPattern, $handler, $middlewares = [])
    {
        $middlewares = array_merge_recursive($this->groupMiddlewares, $middlewares);
        $handler = new RouteHandler($handler, $middlewares);
        $this->bindMiddlewares('PATCH', $handler, $this->groupMiddlewares);
        $this->bindMiddlewares('PATCH', $handler, $middlewares);
        $this->gcbRoute->addRoute('PATCH', $uriPattern, $handler);
    }

    public function delete($uriPattern, $handler, $middlewares = [])
    {
        $middlewares = array_merge_recursive($this->groupMiddlewares, $middlewares);
        $handler = new RouteHandler($handler, $middlewares);
        $this->bindMiddlewares('DELETE', $handler, $this->groupMiddlewares);
        $this->bindMiddlewares('DELETE', $handler, $middlewares);
        $this->gcbRoute->addRoute('DELETE', $uriPattern, $handler);
    }

    public function options($uriPattern, $handler, $middlewares = [])
    {
        $middlewares = array_merge_recursive($this->groupMiddlewares, $middlewares);
        $handler = new RouteHandler($handler, $middlewares);
        $this->bindMiddlewares('OPTIONS', $handler, $this->groupMiddlewares);
        $this->bindMiddlewares('OPTIONS', $handler, $middlewares);
        $this->gcbRoute->addRoute('OPTIONS', $uriPattern, $handler);
    }

    public function any($uriPattern, $handler, $middlewares = [])
    {
        $middlewares = array_merge_recursive($this->groupMiddlewares, $middlewares);
        $handler = new RouteHandler($handler, $middlewares);
        $this->gcbRoute->addRoute('GET', $uriPattern, $handler);
        $this->gcbRoute->addRoute('POST', $uriPattern, $handler);
        $this->gcbRoute->addRoute('PUT', $uriPattern, $handler);
        $this->gcbRoute->addRoute('PATH', $uriPattern, $handler);
        $this->gcbRoute->addRoute('DELETE', $uriPattern, $handler);
        $this->gcbRoute->addRoute('OPTIONS', $uriPattern, $handler);
    }

    public function group($middlewares, $closure)
    {
        if ($this->groupCall) {
            throw new RouteException('Cannot nest call group().');
        }
        $this->groupCall = true;
        $this->groupMiddlewares = $middlewares;
        $closure();
        $this->groupMiddlewares = [];
        $this->groupCall = false;
    }

    public function controller($uriPattern, $handler, $middlewares = [])
    {
        $middlewares = array_merge_recursive($this->groupMiddlewares, $middlewares);
        $handler = new RouteHandler($handler, $middlewares);
        $this->gcbRoute->addRoute('GET', $uriPattern, $handler);
        $this->gcbRoute->addRoute('POST', $uriPattern, $handler);
        $this->gcbRoute->addRoute('PUT', $uriPattern, $handler);
        $this->gcbRoute->addRoute('PATH', $uriPattern, $handler);
        $this->gcbRoute->addRoute('DELETE', $uriPattern, $handler);
        $this->gcbRoute->addRoute('OPTIONS', $uriPattern, $handler);
    }
}