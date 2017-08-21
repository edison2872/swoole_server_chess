<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/9/20
 * Time: 22:38
 */
namespace TSF\Component\MySQL;

use TSF\Contract\Kernel\Stream;
use TSF\Facade\Config;

class MySQLProxyKernel extends Stream
{
    public function onWorkerStart($serv, $workerId)
    {
        MySQLPool::init(Config::facade()->get('app.mysqlpool.conns'));
    }

    public function onConnect($serv, $fd, $from_id)
    {
        MySQLPool::prepare($fd);
    }

    public function onReceive($serv, $fd, $from_id, $data)
    {
        $data = unserialize(substr($data, 4));

        try {
            switch ($data['cmd']) {
                case 'fetch':
                    $res = MySQLPool::fetch($fd, $data['params']['connName']);
                    if ($res) {
                        $data = [
                            'res' => true,
                            'connId' => $res
                        ];
                        $data = serialize($data);
                        $serv->send($fd, pack('N', strlen($data)) . $data);
                    }
                    break;
                case 'recycle':
                    MySQLPool::recycle($fd, $data['params']['connName'], $data['params']['connId']);
                    break;
                case 'query':
                    $conn = MySQLPool::instance($fd, $data['params']['connName'], $data['params']['connId']);
                    $res = $conn->query($data['params']['query'], $data['params']['timeout']);
                    $data = [
                        'res' => $res,
                        'affected_rows' => $conn->affected_rows,
                        'insert_id' => $conn->insert_id,
                        'connected' => $conn->connected
                    ];
                    if ($res == false) {
                        $data['connect_error'] = $conn->connect_error;
                        $data['connect_errno'] = $conn->connect_errno;
                        $data['error'] = $conn->error;
                        $data['errno'] = $conn->errno;
                    }
                    $data = serialize($data);
                    $serv->send($fd, pack('N', strlen($data)) . $data);
                    break;
                default :
                    $serv->close($fd);
            }
        } catch (\Exception $e) {
            $data = [
                'res' => false,
                'errMessage' => $e->getMessage(),
            ];
            $data = serialize($data);
            $serv->send($fd, pack('N', strlen($data)) . $data);
        }
    }

    public function onClose($serv, $fd, $from_id)
    {
        MySQLPool::recycle($fd);
    }
}
