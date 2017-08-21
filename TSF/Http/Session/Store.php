<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/8/20
 * Time: 12:39
 */
namespace TSF\Http\Session;

use TSF\Contract\Http\SessionHandler;
use TSF\Exception\Http\Session\StoreException;

class Store
{
    protected $id;
    protected $conf;
    protected $handler;
    protected $data;
    protected $started = false;

    public function __construct(SessionHandler $handler, array $config)
    {
        $this->handler = $handler;
        $this->conf = array_merge([
            'name' => ini_get('session.name'),
            'cookie_lifetime' => ini_get('session.cookie_lifetime'),
            'cookie_domain'   => ini_get('session.cookie_domain'),
            'cookie_path'     => ini_get('session.cookie_path'),
            'cookie_secure'   => ini_get('session.cookie_secure'),
            'cookie_httponly' => ini_get('session.cookie_httponly'),
            'gc_maxlifetime'  => ini_get('session.gc_maxlifetime'),
        ], $config);
    }

    protected function reGenerateSession(\TSF\Http\HttpResponse $response)
    {
        $this->id = $this->generateId();
        $response->cookie($this->conf['name'], $this->id, $this->conf['cookie_lifetime'],
            $this->conf['cookie_path'], $this->conf['cookie_domain'], $this->conf['cookie_secure'],
            $this->conf['cookie_httponly']
        );
        $this->handler->write($this->id, [], $this->conf['gc_maxlifetime']);
    }

    public function start(\TSF\Http\HttpRequest $request, \TSF\Http\HttpResponse $response)
    {
        if ($this->started) {
            return;
        }

        $this->handler->open();
        $this->id = $request->cookie($this->conf['name'], '');
        if (empty($this->id)) {
            $this->reGenerateSession($response);
        } else {
            $this->data = $this->handler->read($this->id);
            if (!is_array($this->data)) {
                $this->reGenerateSession($response);
            }
        }
        $this->started = true;
    }

    public function stop()
    {
        if ($this->started) {
            $this->handler->write($this->id, $this->data);
            $this->handler->close();
            $this->started = false;
        } else {
            throw new StoreException("Session hasn't been start.");
        }
    }

    protected function generateId()
    {
        return sha1(uniqid('', true) . str_random(25) . microtime(true));
    }

    public function set($name, $value)
    {
        if (!$this->started) {
            throw new StoreException("Session hasn't been start.");
        }
        $this->data[$name] = $value;
    }

    public function get($name, $default = null)
    {
        if (!$this->started) {
            throw new StoreException("Session hasn't been start.");
        }
        return isset($this->data[$name]) ? $this->data[$name] : $default;
    }

    public function clear()
    {
        if (!$this->started) {
            throw new StoreException("Session hasn't been start.");
        }
        $this->data = [];
    }
}