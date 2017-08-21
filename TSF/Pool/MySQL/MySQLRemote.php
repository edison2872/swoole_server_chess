<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/9/22
 * Time: 20:00
 */
namespace TSF\Pool\MySQL;

use TSF\Exception\Pool\MySQLPoolException;
use TSF\Facade\Config;

class MySQLRemote
{
    static protected $maxProxyConns = 0;
    static protected $maxSpareProxyConns = 0;
    static protected $unixDomainSocket;
    static protected $init = false;
    static protected $busyConns = [];
    static protected $spareConns = [];

    static public function init()
    {
        if (self::$init) {
            return;
        }
        $connsConfig = Config::facade()->get('app.mysqlpool');
        if (isset($connsConfig['maxProxyConns'])) {
            self::$maxProxyConns = $connsConfig['maxProxyConns'];
        }
        if (isset($connsConfig['maxSpareProxyConns'])) {
            self::$maxSpareProxyConns = $connsConfig['maxSpareProxyConns'];
        }
        if (self::$maxProxyConns != 0 && self::$maxSpareProxyConns > self::$maxProxyConns) {
            throw new MySQLPoolException('maxSpareProxyConns must less than maxProxyConns.');
        }
        if (!isset($connsConfig['unixDomainSocket'])) {
            throw new MySQLPoolException('Must provide MySQL Proxy Server unixDomainSocket.');
        }
        self::$unixDomainSocket = $connsConfig['unixDomainSocket'];
        self::$init = true;
    }

    static public function fetch($connName)
    {
        if (!self::$init) {
            self::init();
        }
        if (empty(self::$spareConns)) {
            if (self::$maxProxyConns > 0 && (count(self::$busyConns) + count(self::$spareConns)) >= self::$maxProxyConns) {
                throw new MySQLPoolException('Reach maxProxyConns limit.');
            }
            $conn = new MySQLProxyClient($connName, self::$unixDomainSocket);
        } else {
            $conn = array_pop(self::$spareConns);
        }
        $conn->fetch();
        self::$busyConns[spl_object_hash($conn)] = $conn;

        return $conn;
    }

    static public function recycle($conn)
    {
        if (!self::$init) {
            self::init();
        }
        $id = spl_object_hash($conn);
        if ($conn->recycle()) {
            if (self::$maxSpareProxyConns == 0 || count(self::$spareConns) < self::$maxSpareProxyConns) {
                self::$spareConns[$id] = $conn;
            }
        }
        unset(self::$busyConns[$id]);
    }
}