<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/5/18
 * Time: 15:51
 */
namespace TSF\Tcp;

use TSF\Contract\Response;


class TcpResponse extends Response
{
    protected $server;
    protected $body = "";

    public function __construct(\Swoole\Server\Server $server,int $fd)
    {
        $this->setFd($fd);
        $this->server = $server;
    }

    public function setFd(int $fd)
    {
        $this->fd = $fd;
    }

    public function json(array $data)
    {
        $data = json_encode($data);
        if ($data == false)
            throw new \Exception('Invalid json data.');
        $this->body = $data;
        return $this;
    }

    public function setJsonKey(string $key, $value)
    {
        $data = json_decode($this->body,true);
        if ($data === false)
            $data = [];
        $data[$key] = $value;
        $this->body(json_encode($data));
        return $this;
    }

    public function body($content)
    {
        $this->body = (string)$content;
        return $this;
    }

    public function send()
    {
//        sizeof($this->body);
        if ($this->server->exists($this->fd))
            $this->server->send($this->fd,$this->body);
        if ($this->server->getLastError() > 0){
            throw new \Exception('Sent data to client fail.');
        }
        return true;
    }

    public function send_file(string $file_path)
    {
        if (file_exists($file_path)) {
            if (!$this->server->exists($this->fd) || !$this->server->sendfile($this->fd,$file_path)) {
                throw new \Exception('Sent file to client fail.');
            }
            return true;
        } else {
            throw new \Exception('file not exists.');
        }
    }
}
