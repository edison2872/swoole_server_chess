<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/10/28
 * Time: 12:58
 */
namespace TSF\Http;

use TSF\Facade\Http\HttpRequest;
use TSF\Exception\Http\RouteException;

class RouteHandler
{
    protected $rawHandler;

    protected $controller;
    protected $action;

    protected $beforeMiddlewares;
    protected $afterMiddlewares;

    public function __construct($handler, $middlewares)
    {
        list($this->controller, $this->action) = $this->parseHandler($handler);
        $middlewares = array_merge_recursive([
            'include' => ['before' => [], 'after' => []],
            'exclude' => ['before' => [], 'after' => []],
        ], $middlewares);
        $this->beforeMiddlewares = array_diff($middlewares['include']['before'], $middlewares['exclude']['before']);
        $this->afterMiddlewares  = array_diff($middlewares['include']['after'], $middlewares['exclude']['after']);
        $this->rawHandler = $handler;
    }

    public function getBeforeMiddlewares()
    {
        return $this->beforeMiddlewares;
    }

    public function getAfterMiddlewares()
    {
        return $this->afterMiddlewares;
    }

    public function resolve($requestMethod, $uriMatches)
    {
        if ($this->controller == '*') {
            if (count($uriMatches) < 2) {
                throw new RouteException("Invalid handler: {$this->rawHandler}");
            }
            $result   = ["App\\Controller\\".$uriMatches['controller']."Controller", $uriMatches['action']];
            $result[] = array_slice($uriMatches, 2);
            HttpRequest::facade()->setGet("controller",$uriMatches['controller']);
            HttpRequest::facade()->setGet("action",$uriMatches['action']);
            return $result;
        }

        if (empty($this->action)) {
            if (count($uriMatches) < 1) {
                throw new RouteException("Invalid handler: {$this->rawHandler}");
            }
            $result   = [$this->controller, $uriMatches['action']];
            $result[] = array_slice($uriMatches, 1);
            HttpRequest::facade()->setGet("controller",$this->controller);
            HttpRequest::facade()->setGet("action",$uriMatches['action']);
            return $result;
        }
        HttpRequest::facade()->setGet("controller",$this->controller);
        HttpRequest::facade()->setGet("action",$this->action);
        return [$this->controller, $this->action, $uriMatches];
    }

    protected function parseHandler($handler)
    {
        if ($handler == '*') {
            return ['*', ''];
        }
        if (strpos($handler, '@') > 0) {
            list($controller, $action) = explode('@', $handler);
            $controller = "App\\Controller\\{$controller}";
            if (empty($action)) {
                throw new RouteException("Invalid handler: {$handler}");
            }
        } else {
            $controller = "App\\Controller\\{$handler}";
            $action     = '';
        }

        $result = false;
        if (class_exists($controller)) {
            $result = true;
        }

        if ($result && !empty($action)) {
            if (method_exists($controller, $action)) {
                $result = true;
            } else {
                $result = false;
            }
        }

        if (!$result) {
            throw new RouteException("Invalid handler: {$handler}");
        }

        return [$controller, $action];
    }
}