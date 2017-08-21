<?php
/**
 * Created by PhpStorm.
 * User: edison
 * Date: 2017/6/29
 * Time: 上午11:53
 */

namespace App\Framework\Facade;

use App\Framework\Driver\mysql;
use TSF\Contract\Facade;
use TSF\Facade\WebSocket\WSRequest;
use TSF\Facade\WebSocket\WSResponse;
use TSF\Facade\Http\HttpRequest;
use TSF\Facade\Http\HttpResponse;
use TSF\Contract\Request;
use TSF\Facade\App;
use App\Framework\Driver\redis;

class Connection extends Facade
{
    private static $protocol = "";
    private $fd = 0;
    private $request = null;
    private $mysql_read_instance = null;
    private $redis_public_instance = null;
    private $request_data = [];

    public static function getFacadeAccessor()
    {
        return "App\\Framework\\Facade\\Connection";
    }

    public static function setProtocol($protocol)
    {
        self::$protocol = $protocol;
    }


    public function __construct()
    {
        switch (self::$protocol) {
            case "HTTP":
                $this->loadHttpReqest();
                break;
            default:
                throw new \Exception();
        }
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        if (is_object($this->mysql_read_instance) && $this->mysql_read_instance instanceof mysql)
            $this->mysql_read_instance ->__destruct();
        if (is_object($this->redis_public_instance) && $this->redis_public_instance instanceof redis)
            $this->redis_public_instance ->__destruct();
    }

    private function loadHttpReqest()
    {
        $request = HttpRequest::facade();
        switch ($request -> requestMethod) {
            case "GET":
                $this->request_data = $request -> get();
                break;
            case "POST":
                $this->request_data = $request -> postData();
                break;
        }
        $this->fd = $request->fd;
    }

    public function getRequestModel()
    {
        return $this->request;
    }

    public function getRequestData()
    {
        return $this->request_data;
    }

    public function sendToClient($response_data)
    {
        if (1&&is_array($response_data))
            $response_data = self::translateKeyPrefix($response_data);

        switch (self::$protocol) {
            case "HTTP":
                if (is_array($response_data))
                    HttpResponse::facade()->json($response_data);
                else
                    HttpResponse::facade()->body($response_data);
        }

    }

    public function getMysqlReadInstance()
    {
        if (is_null($this->mysql_read_instance))
            $this->mysql_read_instance = new mysql("read");
        return $this->mysql_read_instance;
    }

    public function getRedisPublicInstance()
    {
        if (is_null($this->redis_public_instance)){
            $this->redis_public_instance = new redis();
        }
        return $this->redis_public_instance;
    }

    private static function translateKeyPrefix(array $param)
    {
        $result = [];
        foreach ($param as $key => $value)
        {
            if (is_array($value)) {
                $prefix = "t";
                $value = self::translateKeyPrefix($value);
            } else if (is_float($value)) {
                $prefix = "n";
            } else if (is_numeric($value) && (strlen($value) == strlen(intval($value)))) {
                $prefix = "n";
                $value = intval($value);
            } else if (is_bool($value)) {
                $prefix = "b";
            } else {
                $prefix = "s";
                $value = strval($value);
            }

            if (is_numeric($key))
                $result[strval($key)] = $value;
            else
                $result[$prefix.ucfirst($key)] = $value;
        }
        return $result;
    }
}