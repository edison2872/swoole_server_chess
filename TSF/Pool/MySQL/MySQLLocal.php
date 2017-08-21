<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/9/22
 * Time: 20:32
 */
namespace TSF\Pool\MySQL;

use TSF\Exception\Pool\MySQLPoolException;
use TSF\Facade\Config;

class MySQLLocal
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
        $connsConfig = Config::facade()->get('mysqlpool.conns',[]);
        foreach ($connsConfig as $name => &$config) {
            self::$spareConns[$name] = [];
            self::$busyConns[$name] = [];
            self::$pendingFetchCount[$name] = 0;
            self::$resumeFetchCount[$name] = 0;
            if ($config['maxSpareConns'] <= 0 || $config['maxConns'] <= 0) {
                throw new MySQLPoolException("Invalid maxSpareConns or maxConns in {$name}");
            }
            $config['serverInfo'] = array_merge(['host' => '127.0.0.1', 'port' => 3306], $config['serverInfo']);
        }
        self::$defaultConn = Config::facade()->get('mysqlpool.default');
        self::$connsConfig = $connsConfig;
        unset($config);
        self::$init = true;
    }

    static public function recycle(\Swoole\Coroutine\MySQL $conn)
    {
        if (!self::$init) {
            self::init();
        }

        $id = spl_object_hash($conn);
        $connName = self::$connsNameMap[$id];
        if (isset(self::$busyConns[$connName][$id])) {
            unset(self::$busyConns[$connName][$id]);
        } else {
            throw new MySQLPoolException('Unknow MySQL connection.');
        }

        $connsPool = &self::$spareConns[$connName];
        if ($conn->connected) {
            if (count($connsPool) >= self::$connsConfig[$connName]['maxSpareConns']) {
                unset(self::$connsNameMap[$id]);
                $conn->close();
            } else {
                $connsPool[] = $conn;
                if (self::$pendingFetchCount[$connName] > 0) {
                    self::$resumeFetchCount[$connName]++;
                    self::$pendingFetchCount[$connName]--;
                    \Swoole\Coroutine::resume('MySQLPool::' . $connName); // 继续; 重新开始;
                }
                return;
            }
        }
    }

    /**
     * @param $connName
     * @return bool|mixed|\Swoole\Coroutine\MySQL
     * @throws MySQLPoolException
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
            throw new MySQLPoolException("Unvalid connName: {$connName}.");
        }

        $connsPool = &self::$spareConns[$connName];
        if (!empty($connsPool) && count($connsPool) > self::$resumeFetchCount[$connName]) {
            do {
                $conn = array_pop($connsPool);
                if ($conn->connected) {
                    self::$busyConns[$connName][spl_object_hash($conn)] = $conn;
                    return $conn;
                }
            } while (count($connsPool) > 0);
        }

        if (count(self::$busyConns[$connName]) + count($connsPool) == self::$connsConfig[$connName]['maxConns']) {
            self::$pendingFetchCount[$connName]++;
            if (\Swoole\Coroutine::suspend('MySQLPool::' . $connName) == false) {  // 暂停; 悬; 挂; 延缓;
                self::$pendingFetchCount[$connName]--;
                throw new MySQLPoolException('Reach max connections! Cann\'t pending fetch!');
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

        $conn = new \Swoole\Coroutine\MySQL();
        $id = spl_object_hash($conn);
        self::$connsNameMap[$id] = $connName;
        self::$busyConns[$connName][$id] = $conn;
        if ($conn->connect(self::$connsConfig[$connName]['serverInfo']) == false) {
            unset(self::$busyConns[$connName][$id]);
            unset(self::$connsNameMap[$id]);
            $errMsg = empty($conn->error) ? $conn->connect_error : $conn->error;
            $host = self::$connsConfig[$connName]['serverInfo']['host'] . ':' . self::$connsConfig[$connName]['serverInfo']['port'];
            throw new MySQLPoolException("Cann't connect to MySQL Server[{$host}] errMsg: {$errMsg}");
        }
        $conn->setDefer();
        return $conn;
    }
}
