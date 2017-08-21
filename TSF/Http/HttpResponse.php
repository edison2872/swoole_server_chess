<?php
/**
 * @Author: derekcheng
 * @Date:   2016-09-13 14:09:30
 * @Last Modified by:   winterswang
 * @Last Modified time: 2017-01-10 15:55:27
 */
namespace TSF\Http;

use TSF\Contract\Response;
use TSF\Exception\Http\HttpResponseException;
use TSF\Facade\App;

class HttpResponse extends Response
{
	protected $response;
    protected $body;


	public function __construct(\Swoole\Http\Response $response)
    {
        $this->fd  = $response->fd;
        $this->response = $response;
        $this->body = '';
	}

	public function header($headKey, $headVal)
    {
		if ($this->response->header($headKey, $headVal) === false)
            throw new HttpResponseException('Set header fail.');

        return $this;
	}	

	public function cookie($key, $value = '', $expire = 0 , $path = '/', $domain  = '', $secure = false , $httponly = false)
    {
		if ($this->response->cookie($key, $value, $expire, $path, $domain, $secure, $httponly) === false)
            throw new HttpResponseException('Set cookie fail.');

        return $this;
	}

	public function status($code = 200)
    {
		if ($this->response->status($code) === false)
            //throw new HttpResponseException('Set status fail.');

        return $this;
	}

	public function json(array $data)
    {
        $data = json_encode($data);
        if ($data == false)
            throw new HttpResponseException('Invalid json data.');
        $this->body = $data;
        $this->header('Content-type', 'application/json');

        return $this;
	}

    public function view($view, $data)
    {
        $blade = App::facade()->make('Philo\Blade\Blade');
        $this->body = $blade->view()->make($view, $data)->render();
        return $this;
    }

    public function body($content)
    {
        $this->body = (string)$content;
        return $this;
    }

	public function send()
    {
		if ($this->response->end($this->body) === false){

            throw new HttpResponseException('Sent data to client fail.');
        }

        $this->body = '';
	}
}
