<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/9/21
 * Time: 21:28
 */
namespace TSF\Component\MySQL;

use TSF\Exception\Pool\MySQLPoolException;

class MySQLPool
{
    static protected $init = false;
    static protected $spareConns = [];
    static protected $busyConns = [];
    static protected $connsConfig;
    static protected $idConnsCount = [];
    static protected $pendingFetchCount = [];
    static protected $resumeFetchCount = [];
    static protected $idPendingFetchCount = [];
    static protected $idSkipResumeCount = [];

    static public $newCount = 0;

    static public function init(array $connsConfig)
    {
        if (self::$init) {
            return;
        }
        self::$connsConfig = $connsConfig;
        foreach ($connsConfig as $name => $config) {
            self::$spareConns[$name] = [];
            self::$busyConns[$name] = [];
            self::$pendingFetchCount[$name] = 0;
            self::$resumeFetchCount[$name] = 0;
            if ($config['maxConns'] <= 0 || $config['maxSpareConns'] <= 0) {
                throw new MySQLPoolException("Invalid maxSpareConns or maxConns in {$name}");
            }
        }
        self::$init = true;
    }

    static public function info()
    {
        var_dump(self::$busyConns['test']);
        echo "busyConns : " . count(self::$busyConns['test']) . "\n";
        echo "spareConns : " . count(self::$spareConns['test']) . "\n";
    }

    static public function prepare($id)
    {
        self::$idConnsCount[$id] = 0;
        self::$idPendingFetchCount[$id] = 0;
        self::$idSkipResumeCount[$id] = 0;
    }

    static public function fetch($id, $connName)
    {
        if (!isset(self::$connsConfig[$connName])) {
            throw new MySQLPoolException("Invalid connName: {$connName}.");
        }

        if (!isset(self::$busyConns[$connName][$id])) {
            self::$busyConns[$connName][$id] = [];
        }

        $connsPool = &self::$spareConns[$connName];
        if (!empty($connsPool)  && count($connsPool) > self::$resumeFetchCount[$connName]) {
            $conn = array_pop($connsPool);
            $connId = spl_object_hash($conn);
            self::$busyConns[$connName][$id][$connId] = $conn;
            self::$idConnsCount[$id]++;
            return $connId;
        }

        if (count(self::$busyConns[$connName]) + count($connsPool) == self::$connsConfig[$connName]['maxConns']) {
            //achieve max conns
            self::$pendingFetchCount[$connName]++;
            self::$idPendingFetchCount[$id]++;
            if (\Swoole\Coroutine::suspend('MySQLPool::' . $connName) == false) {
                self::$pendingFetchCount[$connName]--;
                self::$idPendingFetchCount[$id]--;
                throw new MySQLPoolException('Reach max connections! Cann\'t pending fetch!');
            }
            self::$resumeFetchCount[$connName]--;
            if (!empty($connsPool)) {
                //whether the conn has close
                if (self::$idSkipResumeCount[$id] == 0) {
                    self::$idPendingFetchCount[$id]--;
                    $conn = array_pop($connsPool);
                    $connId = spl_object_hash($conn);
                    self::$busyConns[$connName][$id][$connId] = $conn;
                    self::$idConnsCount[$id]++;
                    return $connId;
                } else {
                    self::$idSkipResumeCount[$id]--;
                    return false;
                }
            } else {
                //should not happen
                throw new MySQLPoolException('Unexpected resume!');
            }
        }

        self::$newCount++;
        $conn = new \Swoole\Coroutine\MySQL();
        $connId = spl_object_hash($conn);
        self::$busyConns[$connName][$id][$connId] = $conn;
        self::$idConnsCount[$id]++;
        if ($conn->connect(self::$connsConfig[$connName]['serverInfo']) == false) {
            self::$idConnsCount[$id]--;
            unset(self::$busyConns[$connName][$id][$connId]);
            $errMsg = empty($conn->error) ? $conn->connect_error : $conn->error;
            $host = self::$connsConfig[$connName]['serverInfo']['host'] . ':' . self::$connsConfig[$connName]['serverInfo']['port'];
            throw new MySQLPoolException("Cann't connect to MySQL Server[{$host}] errMsg: {$errMsg}");
        }
        return $connId;
    }

    static public function recycle($id, $connName = '', $connId = '')
    {
        if (empty($connName)) {
            self::$idSkipResumeCount[$id] += self::$idPendingFetchCount[$id];
            self::$idPendingFetchCount[$id] = 0;
            if (self::$idConnsCount[$id] > 0) {
                self::$idConnsCount[$id] = 0;
                foreach (self::$busyConns as $connName => $conns) {
                    if (isset($conns[$id])) {
                        $idConns = self::$busyConns[$connName][$id];
                        unset(self::$busyConns[$connName][$id]);
                        foreach ($idConns as $conn) {
                            if ($conn->connected) {
                                if (count(self::$spareConns[$connName]) >= self::$connsConfig[$connName]['maxSpareConns']) {
                                    $conn->close();
                                } else {
                                    array_push(self::$spareConns[$connName], $conn);
                                    if (self::$pendingFetchCount[$connName] > 0) {
                                        self::$resumeFetchCount[$connName]++;
                                        self::$pendingFetchCount[$connName]--;
                                        \Swoole\Coroutine::resume('MySQLPool::' . $connName);
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                foreach (self::$busyConns as &$conns) {
                    unset($conns[$id]);
                }
                unset($conns);
            }
        } else if (isset(self::$busyConns[$connName][$id][$connId])) {
            self::$idConnsCount[$id]--;
            $conn = self::$busyConns[$connName][$id][$connId];
            unset(self::$busyConns[$connName][$id][$connId]);
            if ($conn->connected) {
                if (count(self::$spareConns[$connName]) >=self::$connsConfig[$connName]['maxSpareConns']) {
                    $conn->close();
                } else {
                    array_push(self::$spareConns[$connName], $conn);
                    if (self::$pendingFetchCount[$connName] > 0) {
                        self::$resumeFetchCount[$connName]++;
                        self::$pendingFetchCount[$connName]--;
                        \Swoole\Coroutine::resume('MySQLPool::' . $connName);
                    }
                }
            }
        }
    }

    static public function instance($id, $connName, $connId)
    {
        return self::$busyConns[$connName][$id][$connId];
    }
}