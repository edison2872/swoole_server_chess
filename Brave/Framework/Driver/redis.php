<?php
/**
 * Created by PhpStorm.
 * User: edison
 * Date: 2017/6/8
 * Time: 下午4:36
 */

namespace App\Framework\Driver;

use \TSF\Exception\Component\RedisHandlerException;
class redis extends \TSF\Pool\Redis\RedisLocal
{
    private $redis_connection = false;

    /**
     * 从redis连接池中获取链接
     * redis constructor.
     * @param int $server_id
     */
    public function __construct($server_id = 0)
    {
        $server_id = intval($server_id);
        do {
            $this->redis_connection = parent::fetch("server".$server_id);
        }while($this->redis_connection === false);
        $this->redis_connection ->select($server_id);
    }

    /**
     * redis连接用完回收至连接池
     */
    public function __destruct()
    {
        if ($this -> redis_connection !== false && $this -> redis_connection instanceof \Swoole\Coroutine\Redis){
            parent::recycle($this -> redis_connection);
            $this ->redis_connection = false;
        }
    }

    public function read($sessionId)
    {
        $ret = $this->redis_connection->get($sessionId);
        if ($ret === false) {
            throw new RedisHandlerException('Get $sessionId from redis fail.');
        }

        return $ret;
    }

    public function write($sessionId, $data, $timeout = 0)
    {
        if ($timeout > 0) {
            $ret = $this->redis_connection->set($sessionId, $data, ['px' => $timeout]);
        } else {
            $ret = $this->redis_connection->set($sessionId, $data);
        }

        if ($ret === false) {
            throw new RedisHandlerException('Store $data by $sessionId to redis fail.');
        }

        return $ret;
    }

    public function delete($sessionId){

        $ret = $this->redis_connection->del($sessionId);
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

        $ret = $this ->redis_connection ->incr($key);
        if ($ret === false) {
            throw new RedisHandlerException('incr $sessionId from redis fail.');
        }

        return $ret;
    }

    public function exists($sessionId) {
        $ret = $this->redis_connection->exists($sessionId);
        return boolval($ret);
    }

    public function getKeys($sessionId)
    {
        $ret = $this->redis_connection->getKeys($sessionId);
        if ($ret === false) {
            throw new RedisHandlerException('getKeys $sessionId from redis fail.');
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

        $ret = $this ->redis_connection ->hExists($hkey,$member);
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

        $ret = $this ->redis_connection ->scard($hkey);
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

        $ret = $this ->redis_connection ->sismember($skey,$member);
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

        $ret = $this ->redis_connection ->sMembers($skey);
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

        $ret = $this ->redis_connection ->sPop($skey);
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

        $ret = $this ->redis_connection ->hset($hkey, $member, $value);
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

        $ret = $this ->redis_connection ->hget($hkey, $member);
        if ($ret === false) {
            throw new RedisHandlerException('hget hkey : $hkey member : $member from redis fail.');
        }

        return $ret;
    }

    public function hReadAll($key)
    {
        $ret = $this->redis_connection->hGetAll($key);
        if ($ret === false) {
            throw new RedisHandlerException('hget hkey : $hkey from redis fail.');
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

        $ret = $this ->redis_connection ->hlen($hkey);
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

        $ret = $this ->redis_connection ->zAdd($zkey, $score, $member);
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

        $ret = $this ->redis_connection ->sAdd($skey, $member);
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

        $ret = $this ->redis_connection ->zRem($skey, $member);
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
        $ret = $this ->redis_connection ->zRangeByScore($zkey,$s,$e,$opt);
        if ($ret === false) {
            throw new RedisHandlerException('hset hkey : $hkey member : $member from redis fail.');
        }

        return $ret;
    }
}