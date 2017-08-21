<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/8/20
 * Time: 12:48
 */
namespace TSF\Http\Session;

use TSF\Contract\Http\SessionHandler;
use TSF\Exception\Http\Session\SessionHandlerException;

class RedisSessionHandler implements SessionHandler
{
    protected $redis;
    protected $conf;

    public function __construct(array $config)
    {
        $this->conf = array_merge(['host' => '127.0.0.1', 'port' => 6379], $config);
    }

    public function open()
    {
        $this->redis = new \Swoole\Coroutine\Redis();
        if ($this->redis->connect($this->conf['host'], $this->conf['port'], true) == false)
            throw new SessionHandlerException("Cannot connect to redis server on {$this->conf['host']}:{$this->conf['port']}");
    }

    public function close()
    {
        $this->redis->close();
    }

    public function read($sessionId)
    {
        $ret = $this->redis->get($sessionId);
        if ($ret === false) {
            throw new SessionHandlerException('Get session from redis fail.');
        }

        return $ret;
    }

    public function write($sessionId, $data, $timeout = 0)
    {
        if ($timeout > 0) {
            $ret = $this->redis->set($sessionId, $data, ['px' => $timeout]);
        } else {
            $ret = $this->redis->set($sessionId, $data, ['xx']);
        }

        if ($ret === false) {
            throw new SessionHandlerException('Store session to redis fail.');
        }
    }

    public function gc() {}
}