<?php
/**
 * Created by PhpStorm.
 * User: edison
 * Date: 2017/6/13
 * Time: 上午10:31
 */

namespace App\Framework\Driver;


class mysql extends \TSF\Pool\MySQL\MySQLLocal
{
    private $mysql_connection = false;
    private $conncetion_excuted = false;
    private $time_out = 0;
    private $query_buff = null;

    public function __construct($db_target)
    {
        do {
            $my_sql_connect = parent::fetch($db_target);
            $this->mysql_connection = $my_sql_connect;
        } while ($this->mysql_connection === false);
    }

    public function __destruct()
    {
        if ($this->mysql_connection !== false && $this->mysql_connection instanceof \Swoole\Coroutine\MySQL) {
            parent::recycle($this->mysql_connection);
            $this->mysql_connection = false;
        }
    }

    public function setTimeOut($time_out_second = 0)
    {
        $this->time_out = intval($time_out_second) > 0 ? intval($time_out_second) : 0;
    }

    public function transcationBegin()
    {

    }

    public function transcationCommit()
    {

    }

    public function getLastInsertId()
    {
        if ($this->conncetion_excuted === false)
            return false;
        return $this->mysql_connection->insert_id;
    }

    public function query($query)
    {
        if (!is_null($this->query_buff))
            free($this->query_buff);
        if (!$this->mysql_connection->connected)
            throw new \Exception();
        $result = $this->mysql_connection->query($query, $this->time_out);
        if ($result === false) {
            $error_no = $this->mysql_connection->errno;
            $error_msg = $this->mysql_connection->error;
            $log_query = str_replace("\"", "\"\"", $query);
//            throw new \Exception("MySQL:DBQuery($log_query),$error_msg($error_no)");
            $this->mysql_connection->recv();
            return [];
        } else {
            $this->conncetion_excuted = true;
            return $this->mysql_connection->recv();
        }
    }
}