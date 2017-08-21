<?php
/**
 * @Author: winterswang
 * @Date:   2016-11-07 17:23:22
 * @Last Modified by:   winterswang
 * @Last Modified time: 2016-11-25 16:49:19
 */
namespace TSF\Component\Redis;

use TSF\Exception\Component\RedisHandlerException;

class RedisHandler {

    protected $redis;
    protected $conf;

    public function __construct(array $config)
    {
        $this->conf = array_merge(['host' => '127.0.0.1', 'port' => 6379], $config);
        $this ->open();
    }

    public function open()
    {
        $this->redis = new \Swoole\Coroutine\Redis();
        if ($this->redis->connect($this->conf['host'], $this->conf['port'], true) == false)
            throw new RedisHandlerException("Cannot connect to redis server on {$this->conf['host']}:{$this->conf['port']}");
    }

    public function close()
    {
        $this->redis->close();
    }

    public function read($sessionId)
    {
        $ret = $this->redis->get($sessionId);
        if ($ret === false) {
            throw new RedisHandlerException('Get $sessionId from redis fail.');
        }

        return $ret;
    }

    public function write($sessionId, $data, $timeout = 0)
    {
        if ($timeout > 0) {
            $ret = $this->redis->set($sessionId, $data, ['px' => $timeout]);
        } else {
            $ret = $this->redis->set($sessionId, $data);
        }

        if ($ret === false) {
            throw new RedisHandlerException('Store $data by $sessionId to redis fail.');
        }

        return $ret;
    }

    public function delete($sessionId){

        $ret = $this->redis->del($sessionId);
        if ($ret === false) {
            throw new RedisHandlerException('Get $sessionId from redis fail.');
        }

        return $ret;        
    }

    /**
     * [increase 自增]
     * @param  [type] $key [description]
     * @return [type]      [description]
     */
    public function increase($key){

        $ret = $this ->redis ->incr($key);
        if ($ret === false) {
            throw new RedisHandlerException('incr $sessionId from redis fail.');
        }

        return $ret; 
    }

    /**
     * [hexist  hash 是否存在]
     * @param  [type] $hkey   [description]
     * @param  [type] $member [description]
     * @return [type]         [description]
     */
    public function hexist($hkey,$member){

        $ret = $this ->redis ->hExists($hkey,$member);
        if ($ret === false) {
            throw new RedisHandlerException('hexist hkey : $hkey and member : $member from redis fail.');
        }

        return $ret; 
    }

    /**
     * [scard 获取集合总数]
     * @param  [type] $hkey [description]
     * @return [type]       [description]
     */
    public function scard($hkey){

        $ret = $this ->redis ->scard($hkey);
        if ($ret === false) {
            throw new RedisHandlerException('scard hkey : $hkey from redis fail.');
        }

        return $ret;   
    }

    /**
     * [sismember 判断集合中的member存在否]
     * @param  [type] $skey [description]
     * @param  [type] $key  [description]
     * @return [type]       [description]
     */
    public function sismember($skey,$member){

        $ret = $this ->redis ->sismember($skey,$member);
        if ($ret === false) {
            throw new RedisHandlerException('sismember hkey : $hkey from redis fail.');
        }

        return $ret;   
    }   

    /**
     * [smembers 获取集合]
     * @param  [type] $skey   [description]
     * @param  [type] $member [description]
     * @return [type]         [description]
     */
    public function smembers($skey){

        $ret = $this ->redis ->sMembers($skey);
        if ($ret === false) {
            throw new RedisHandlerException('smembers skey : $skey from redis fail.');
        }

        return $ret;   
    }  
    /**
     * [spop 随机冒出一个Member并删除]
     * @param  [type] $skey [description]
     * @return [type]       [description]
     */
    public function spop($skey){

        $ret = $this ->redis ->sPop($skey);
        if ($ret === false) {
            throw new RedisHandlerException('sPop skey : $skey from redis fail.');
        }

        return $ret;           
    }

    /**
     * [hwrite hash写]
     * @param  [type] $hkey   [description]
     * @param  [type] $member [description]
     * @param  [type] $value  [description]
     * @return [type]         [description]
     */
    public function hwrite($hkey, $member, $value){

        $ret = $this ->redis ->hset($hkey, $member, $value);
        if ($ret === false) {
            throw new RedisHandlerException('hset hkey : $hkey member : $member and value : $value from redis fail.');
        }

        return $ret;  
    }

    /**
     * [hread hash read读]
     * @param  [type] $hkey   [description]
     * @param  [type] $member [description]
     * @return [type]         [description]
     */
    public function hread($hkey, $member){

        $ret = $this ->redis ->hget($hkey, $member);
        if ($ret === false) {
            throw new RedisHandlerException('hget hkey : $hkey member : $member from redis fail.');
        }

        return $ret;  
    }

    /**
     * [hlen hash 长度]
     * @param  [type] $hkey   [description]
     * @param  [type] $member [description]
     * @return [type]         [description]
     */
    public function hlen($hkey){

        $ret = $this ->redis ->hlen($hkey);
        if ($ret === false) {
            throw new RedisHandlerException('hlen hkey : $hkey member : $member from redis fail.');
        }

        return $ret;  
    }

    /**
     * [zadd 有序集合插入一个Member]
     * @return [type] [description]
     */
    public function zadd($zkey,$member,$score){

        $ret = $this ->redis ->zAdd($zkey, $score, $member);
        if ($ret === false) {
            throw new RedisHandlerException('hset hkey : $hkey member : $member from redis fail.');
        }

        return $ret;  
    }

    /**
     * [zadd 有序集合插入一个Member]
     * @return [type] [description]
     */
    public function sadd($skey,$member){

        $ret = $this ->redis ->sAdd($skey, $member);
        if ($ret === false) {
            throw new RedisHandlerException('hset hkey : $hkey member : $member from redis fail.');
        }

        return $ret;  
    }

    /**
     * [zrem 有序队列的删除]
     * @param  [type] $skey   [description]
     * @param  [type] $member [description]
     * @return [type]         [description]
     */
    public function zrem($skey,$member){

        $ret = $this ->redis ->zRem($skey, $member);
        if ($ret === false) {
            throw new RedisHandlerException('zrem hkey : $hkey member : $member from redis fail.');
        }

        return $ret;  
    }
    /**
     * [zrangeByScore 获取有序集合的顺序member]
     * @param  [type] $zkey   [description]
     * @param  [type] $s      [description]
     * @param  [type] $e      [description]
     * @param  [type] $offset [description]
     * @param  [type] $len    [description]
     * @return [type]         [description]
     */
    public function zrangeByScore($zkey,$s,$e,$limit_s,$limit_e){

        $opt = array(
            'limit' => array($limit_s, $limit_e)
            );
        $ret = $this ->redis ->zRangeByScore($zkey,$s,$e,$opt);
        if ($ret === false) {
            throw new RedisHandlerException('hset hkey : $hkey member : $member from redis fail.');
        }

        return $ret;  
    }

    public function gc() {}
}

