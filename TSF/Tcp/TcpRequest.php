<?php
namespace TSF\Tcp;

use TSF\Contract\Request;

class TcpRequest extends Request
{
    protected $data;
    protected $request;
    protected $reactor_id;

    public function __construct(int $fd,int $reactor_id,string $data)
    {
        $this->fd = $fd;
        $this->reactor_id = $reactor_id;
        $this->data = $data;
        $this->handleRequest();
    }

    public function handleRequest()
    {
        $json_data = json_decode($this->data, true);
        $this->request = $json_data===false?$this->data:$json_data;
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