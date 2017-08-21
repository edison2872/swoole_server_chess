<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/5/18
 * Time: 15:51
 */
namespace TSF\WebSocket;

use TSF\Contract\Response;


class WSResponse extends Response
{
    protected $server;
    protected $response = ["result" => 0];
    protected $RContent;

    public function __construct(\Swoole\WebSocket\Server $server, $fd)
    {
        $this->fd  = $fd;
        $this->server = $server;
    }

    public function setRes($key, $value)
    {
        $this->response[$key] = $value;
        return $this;
    }

    public function status($code = 0)
    {
        $this->response["result"] = $code;
        return $this;
    }

    public function msg($msg)
    {
        $this->response["msg"] = $msg;
        return $this;
    }

    public function handleRes()
    {
        $this->RContent = json_encode($this->response);
    }

    public function send()
    {
        if (empty($this->RContent)) {
            $this->handleRes();
        }
        $this->server->push($this->fd, $this->RContent);
    }

}
