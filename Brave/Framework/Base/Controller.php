<?php
/**
 * Created by PhpStorm.
 * User: edison
 * Date: 2017/6/19
 * Time: 下午5:15
 */

namespace App\Framework\Base;

use App\Framework\Facade\Connection;
use TSF\Facade\Http\HttpResponse;
use TSF\Contract\Request;
use TSF\Facade\Config;

abstract class Controller
{
    private $request_data = [];
    private $response_param = [];

    public function __construct()
    {
         $this->request_data = Connection::facade()->getRequestData();
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
         Connection::facade()->sendToClient($this->response_param);
    }

    protected function getConfig(string $key)
    {
        return Config::facade()->get($key, null);
    }

    protected function getRequestData(string $key = "")
    {
        if (empty($key))
            return $this->request_data;
        return isset($this->request_data[$key]) ? $this->request_data[$key] : null;
    }

    protected function setClientParam(string $key, $value)
    {
        $this->response_param[$key] = $value;
    }

    protected function setClientArrayParam($param)
    {
        if (is_array($param) && is_array($this->response_param))
            $this->response_param = array_merge($this->response_param, $param);
        elseif (is_array($param))
            $this->response_param = $param;
        else
            $this->response_param = $param;
//        if ($this->getConfig("module.".$this ->controller.".".$this->action.".condition"))
//        {
//
//        }else{
//
//        }
    }

    protected function getRemoteIp()
    {
        return Connection::facade()->getRequestModel()->remoteInfo['addr'];
    }
}