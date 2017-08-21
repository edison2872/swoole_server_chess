<?php
/**
 * @Author: winterswang
 * @Date:   2016-09-13 14:09:30
 * @Last Modified by:   winterswang
 * @Last Modified time: 2016-09-13 17:53:52
 */
namespace TSF\Http;

use TSF\Contract\Request;

class HttpRequest extends Request
{
    protected $swooleRequest;
    protected $header;
    protected $requestMethod;
    protected $queryString;
    protected $requestUri;
    protected $requestTime;
    protected $requestTimeFloat;
    protected $cookie;
    protected $get;
    protected $post;

    public function __construct(\Swoole\Http\Request $request)
    {
        $this->swooleRequest = $request;
        $this->fd = $request->fd;
        $this->serverInfo['addr'] = swoole_get_local_ip();
        $this->serverInfo['port'] = $request->server['server_port'];
        $this->serverInfo['protocol'] = $request->server['server_protocol'];
        $this->serverInfo['software'] = $request->server['server_software'];
        $this->remoteInfo['addr'] = $request->server['remote_addr'];
        $this->remoteInfo['port'] = $request->server['remote_port'];
        $this->header = $request->header;
        $this->requestMethod = $request->server['request_method'];
        $this->queryString = isset($request->server['query_string']) ? $request->server['query_string'] : '';
        $this->requestUri = $request->server['request_uri'];
        $this->requestTime = $request->server['request_time'];
        $this->requestTimeFloat = $request->server['request_time_float'];
        $this->cookie = isset($request->cookie) ? $request->cookie : [];
        $this->get = isset($request->get) ? $request->get : [];
        $this->post = isset($request->post) ? $request->post : [];
        $this->files = isset($request->files) ? $this->files : [];
    }

    public function cookie($key = '', $default = null)
    {
        if (empty($key)) {
            return $this->cookie;
        }

        return isset($this->cookie[$key]) ? $this->cookie[$key]
            : $default;
    }

    public function get($key = '', $default = null)
    {
        if (empty($key)) {
            return $this->get;
        }

        return isset($this->get[$key]) ? $this->get[$key]
            : $default;
    }

    public function setGet($key = '',$value = null)
    {
        $this->get[$key] = $value;
    }

    public function postData($key = '', $default = null)
    {
        if (empty($key)) {
            return $this->post;
        }

        return isset($this->post[$key]) ? $this->post[$key]
            : $default;
    }

    public function setPostData(array $post_data)
    {
        $this->post = $post_data;
    }

    public function rawcontent()
    {
        return $this->swooleRequest->rawcontent();
    }
}
