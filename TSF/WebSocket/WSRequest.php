<?php
namespace TSF\WebSocket;

use TSF\Contract\Request;

class WSRequest extends Request
{
    protected $swooleFrame;
    protected $data;
    protected $request;

    public function __construct(\Swoole\WebSocket\Frame $frame)
    {
        $this->swooleFrame = $frame;
        $this->fd = $frame->fd;
        $this->data = $frame->data;
        $this->handleRequest();
    }

    public function handleRequest()
    {
        $this->request = json_decode($this->data, true);
    }

    /**
     * @param string $key
     * @param null $default
     * @return null
     */
    public function get($key = '', $default = null)
    {
        if (empty($key)) {
            return $this->request;
        }
        return isset($this->request[$key]) ? $this->request[$key] : $default;
    }
}