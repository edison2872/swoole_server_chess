<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/9/22
 * Time: 20:19
 */
namespace TSF\Pool\MySQL;

use TSF\Exception\Pool\MySQLPoolException;

class MySQLProxyClient
{
    protected $connName;
    protected $connId;
    protected $unixStreamClient;
    protected $unixSocket;
    protected $defer = false;
    protected $iowait;

    public $connect_error;
    public $connect_errno;
    public $error;
    public $errno;
    public $conntected;
    public $affected_rows;
    public $insert_id;

    const CLOSED = 0;
    const READY = 1;
    const WAIT = 2;

    public function __construct($connName, $unixSocket)
    {
        $this->connName = $connName;
        $this->unixStreamClient = NULL;
        $this->iowait = self::CLOSED;
        $this->unixSocket = $unixSocket;
    }

    public function query($sql, $timeout = 0)
    {
        if ($this->iowait != self::READY) {
            throw new MySQLPoolException('Client doesn\'t connect to MySQL Proxy Server.');
        }
        $cmd = [
            'cmd' => 'query',
            'params' => [
                'connName' => $this->connName,
                'connId' => $this->connId,
                'query' => $sql,
                'timeout' => $timeout
            ]
        ];
        $cmd = serialize($cmd);
        if ($this->unixStreamClient->send(pack('N', strlen($cmd)) . $cmd)) {
            if ($this->defer) {
                $this->iowait = self::WAIT;
                return true;
            }
            $res = $this->unixStreamClient->recv();
            if ($res) {
                $data_length = unpack('N', substr($res, 0, 4));
                $data_length = $data_length[1];
                $recv_length = strlen($res) - 4;
                while (1) {
                    if ($data_length == $recv_length) {
                        $data = unserialize(substr($res, 4));
                        $this->affected_rows = $data['affected_rows'];
                        $this->insert_id = $data['insert_id'];
                        $this->connected = $data['connected'];
                        if ($data['res'] == false) {
                            $this->error = $data['error'];
                            $this->errno = $data['errno'];
                            $this->connect_error = $data['connect_error'];
                            $this->connect_errno = $data['connect_errno'];
                        }
                        return $data['res'];
                    } else {
                        $tmp = $this->unixStreamClient->recv();
                        $res .= $tmp;
                        $recv_length += strlen($tmp);
                    }
                }
            }
        }

        throw new MySQLPoolException("Query Fail.");
    }

    public function setDefer($is_defer = true)
    {
        if ($this->iowait > self::READY) {
            return (boolean)$is_defer;
        }

        $this->defer = (boolean)$is_defer;
        return true;
    }

    public function getDefer()
    {
        return $this->defer;
    }

    public function recv()
    {
        if ($this->defer) {
            if ($this->iowait != self::WAIT) {
                return false;
            }
            $this->iowait = self::READY;
            $res = $this->unixStreamClient->recv();
            if ($res) {
                $data_length = unpack('N', substr($res, 0, 4));
                $data_length = $data_length[1];
                $recv_length = strlen($res) - 4;
                while (1) {
                    if ($data_length == $recv_length) {
                        $data = unserialize(substr($res, 4));
                        $this->affected_rows = $data['affected_rows'];
                        $this->insert_id = $data['insert_id'];
                        $this->connected = $data['connected'];
                        if ($data['res'] == false) {
                            $this->error = $data['error'];
                            $this->errno = $data['errno'];
                            $this->connect_error = $data['connect_error'];
                            $this->connect_errno = $data['connect_errno'];
                        }
                        return $data['res'];
                    } else {
                        $tmp = $this->unixStreamClient->recv();
                        $res .= $tmp;
                        $recv_length += strlen($tmp);
                    }
                }
            }
            throw new MySQLPoolException('Query Fail.');
        }
        trigger_error('You should not use recv without defer', E_USER_WARNING);

        return false;
    }

    public function fetch()
    {
        $cmd = [
            'cmd' => 'fetch',
            'params' => [
                'connName' => $this->connName,
            ]
        ];
        $cmd = serialize($cmd);
        if ($this->unixStreamClient == NULL || !$this->unixStreamClient->isConnected()) {
            $this->unixStreamClient = new \Swoole\Coroutine\Client(SWOOLE_UNIX_STREAM);
            if ($this->unixStreamClient->connect($this->unixSocket, 0, 0) == false)
                throw new MySQLPoolException("Cann't fetch {$this->connName} connection. MySQL Proxy Server maybe down or connect timeout.");
        } elseif ($this->iowait != self::CLOSED) {
            throw new MySQLPoolException('Duplicate fetch.');
        }
        $errMessage = 'Connection abort.';
        if ($this->unixStreamClient->send(pack('N', strlen($cmd)) . $cmd)
            && ($res = $this->unixStreamClient->recv())
        ) {
            $data = unserialize(substr($res, 4));
            if ($data['res']) {
                $this->iowait = self::READY;
                $this->connId = $data['connId'];
                return true;
            }
            $errMessage = $data['errMessage'];
        }

        throw new MySQLPoolException("Cann't fetch {$this->connName} connection. Error message from pool server: {$errMessage}.");
    }

    public function recycle()
    {
        $this->iowait = self::CLOSED;
        if ($this->unixStreamClient && $this->unixStreamClient->isConnected()) {
            $cmd = [
                'cmd' => 'recycle',
                'params' => [
                    'connName' => $this->connName,
                    'connId' => $this->connId,
                ]
            ];
            $cmd = serialize($cmd);
            return $this->unixStreamClient->send(pack('N', strlen($cmd)) . $cmd);
        }

        return false;
    }
}
