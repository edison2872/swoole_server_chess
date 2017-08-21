<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/5/23
 * Time: 17:34
 */
namespace TSF\Pool\Redis;

use TSF\Exception\Pool\RedisPoolException;
use TSF\Facade\Config;

class RedisLocal
{
    static protected $connsConfig;
    static protected $init = false;
    static protected $spareConns = [];
    static protected $busyConns = [];
    static protected $connsNameMap = [];
    static protected $pendingFetchCount = [];
    static protected $resumeFetchCount = [];
    static protected $defaultConn;

    static public function init()
    {
        if (self::$init) {
            return;
        }
        $connsConfig = Config::facade()->get('redispool.conns',[]);
        foreach ($connsConfig as $name => $config) {
            self::$spareConns[$name] = [];
            self::$busyConns[$name] = [];
            self::$pendingFetchCount[$name] = 0;
            self::$resumeFetchCount[$name] = 0;
            if ($config['maxSpareConns'] <= 0 || $config['maxConns'] <= 0) {
                throw new RedisPoolException("Invalid maxSpareConns or maxConns in {$name}");
            }
            $connsConfig[$name]['serverInfo'] = array_merge(['host' => '127.0.0.1', 'port' => 6379], $config['serverInfo']);
        }
        self::$connsConfig = $connsConfig;
        self::$defaultConn = Config::facade()->get('redispool.default');
        unset($config);
        self::$init = true;
    }

    static public function recycle(\Swoole\Coroutine\Redis $conn)
    {
        if (!self::$init) {
            self::init();
        }

        $id = spl_object_hash($conn);
        $connName = self::$connsNameMap[$id];
        if (isset(self::$busyConns[$connName][$id])) {
            unset(self::$busyConns[$connName][$id]);
        } else {
            throw new RedisPoolException('Unknow Redis connection.');
        }
        echo $id.PHP_EOL;
        echo $connName.PHP_EOL;

        $connsPool = &self::$spareConns[$connName];

        if (count($connsPool) >= self::$connsConfig[$connName]['maxSpareConns']) {
            unset(self::$connsNameMap[$id]);
            $conn->close();
        } else {
            $connsPool[] = $conn;
            var_dump(self::$pendingFetchCount);
            if (self::$pendingFetchCount[$connName] > 0) {
                self::$resumeFetchCount[$connName]++;
                self::$pendingFetchCount[$connName]--;
                \Swoole\Coroutine::resume('RedisPool::' . $connName);
            }
            return;
        }
    }

    /**
     * @param $connName
     * @return bool|mixed|\Swoole\Coroutine\Redis
     * @throws RedisPoolException
     */
    static public function fetch($connName = null)
    {
        if (!self::$init) {
            self::init();
        }

        if (empty($connName)) {
            $connName = self::$defaultConn;
        }

        if (!isset(self::$connsConfig[$connName])) {
            throw new RedisPoolException("Unvalid connName: {$connName}.");
        }

        $connsPool = &self::$spareConns[$connName];
        if (!empty($connsPool) && count($connsPool) > self::$resumeFetchCount[$connName]) {
            do {
                $conn = array_pop($connsPool);
                //
                var_dump($conn->ping());
                if ($conn->ping() == 'PONG') {
                    self::$busyConns[$connName][spl_object_hash($conn)] = $conn;
                    return $conn;
                }
            } while (count($connsPool) > 0);

        }

        if (count(self::$busyConns[$connName]) + count($connsPool) == self::$connsConfig[$connName]['maxConns']) {
            self::$pendingFetchCount[$connName]++;
            if (\Swoole\Coroutine::suspend('RedisPool::' . $connName) == false) {
                self::$pendingFetchCount[$connName]--;
                throw new RedisPoolException('Reach max connections! Cann\'t pending fetch!');
            }
            self::$resumeFetchCount[$connName]--;
            if (!empty($connsPool)) {
                $conn = array_pop($connsPool);
                self::$busyConns[$connName][spl_object_hash($conn)] = $conn;

                return $conn;
            } else {
                return false;//should not happen
            }
        }

        $conn = new \Swoole\Coroutine\Redis();
        $id = spl_object_hash($conn);
        self::$connsNameMap[$id] = $connName;
        self::$busyConns[$connName][$id] = $conn;
        if ($conn->connect(self::$connsConfig[$connName]['serverInfo']["host"], self::$connsConfig[$connName]['serverInfo']["port"]) == false) {
            unset(self::$busyConns[$connName][$id]);
            unset(self::$connsNameMap[$id]);
            $errMsg = empty($conn->error) ? $conn->connect_error : $conn->error;
            $host = self::$connsConfig[$connName]['serverInfo']['host'] . ':' . self::$connsConfig[$connName]['serverInfo']['port'];
            throw new RedisPoolException("Cann't connect to Redis Server[{$host}] errMsg: {$errMsg}");
        }
        if (isset(self::$connsConfig[$connName]['serverInfo']["password"])) {
            $conn->auth(self::$connsConfig[$connName]['serverInfo']["password"]);
        }
        if (isset(self::$connsConfig[$connName]['serverInfo']["database"])) {
            $conn->select(self::$connsConfig[$connName]['serverInfo']["database"]);
        }

        return $conn;
    }
}
