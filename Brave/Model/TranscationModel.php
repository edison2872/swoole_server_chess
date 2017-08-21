<?php
/**
 * Created by PhpStorm.
 * User: edison
 * Date: 2017/6/30
 * Time: 下午3:31
 */

namespace App\Model;

/**
 * transction表操作
 * @author edison
 *
 */
use ReflectionClass;
class TranscationModel extends \App\Framework\Base\Model {

    //transction表字段
    private $transcation_id;
    private $uid;
    private $channel = "PC";
    private $serverid;
    private $transcation_count = 0;
    private $orderid;
    private $mark;
    private $roleid;
    private $time;
    private $money;
    private $gamemoney;
    private $status;
    private $item = "";
    private $deception = "";
    private $action_id = 0;

    private $lasttime;
    //----------
    private $_new_order_flag = false;
    private $_update_list = [];

    function __construct()
    {

    }

    public function __set(string $name,$value)
    {
        if ($name == "_id")
            $name = "transcation_id";
        if (substr($name, 0,1) == "_")
            return;

        if (property_exists($this, $name))
        {
            if (method_exists($this, "set".ucfirst($name)))
            {
                $method = "set".ucfirst($name);
                $this-> $method($value);
            }
            else {
                $this->$name = $value;
            }
        }
    }

    public function __get(string $name)
    {
        if ($name == "_id")
            $name = "transcation_id";
        if (substr($name, 0,1) == "_")
            return null;
        if (property_exists($this, $name))
        {
            return $this->$name;
        }
        return null;
    }

    /**
     * 转化时间格式
     * @param unknown $time
     */
    private function setTime($time)
    {
        if (is_numeric($time))
        {
            $this->time = $time;
        }
        else
        {
            $this->time = strtotime($time);
        }
    }

    /**
     * 获取mark
     */
    public function getMark()
    {
        return $this->mark;
    }

    /**
     * 检查订单是否属于该账号
     * @param unknown $channel
     * @param unknown $uid
     * @param unknown $serverid
     */
    public function checkOrderIsAccount(string $channel,string $account,int $server_id)
    {
        if ($channel == $this->channel && $account == $this->uid && $server_id == $this->serverid )
            return true;
        return false;
    }

    /**
     * 检查订单是否未完成
     */
    public function isOrderUncomplete()
    {
        if (is_null($this->order_id) && $this->status == -1)
            return true;
        return false;
    }

    /**
     * 写去数据库
     */
    public function save()
    {
        if ($this->_new_order_flag)
        {
            $insert_list = $this->getWriteList();
            if (count($insert_list))
            {
                self::mysql_fetch_one("INSERT INTO `transcation` (".implode(',',array_keys($insert_list)).") VALUES (".implode(',',array_values($insert_list)).")");
                return self::getInsertLastId()>0?true:false;
            }
        }
        elseif (count($this->_update_list))
        {
            $update_list = [];
            $data_list = $this->getWriteList();
            foreach ($this->_update_list as $key_name)
            {
                if (isset($data_list["`".$key_name."`"]))
                    $update_list[] = "`".$key_name."` = ".$data_list["`".$key_name."`"];
            }
            if (count($update_list) && $this->transcation_id > 0)
            {
                return self::mysql_fetch_one("UPDATE `transcation` SET ".implode(',',$update_list)." WHERE `_id` = {$this->transcation_id} AND `status` = -1");
            }
        }
        return false;
    }

    /**
     * 获取写入的字段内容
     */
    private function getWriteList()
    {
        $write_list = [];
        $ref = new ReflectionClass($this);
        foreach ($ref -> getProperties() as $property_object)
        {
            $property_name = $property_object->name;
            if (substr($property_name, 0,1) != "_" && $property_name != "lasttime" && !is_null($this->$property_name) && $property_object->class == __CLASS__)
            {
                if ($property_name == "transcation_id")
                    continue;
                if ($property_name == "time")
                    $write_list["`".$property_name."`"] = "'".date('Y-m-d h:i:s',$this->$property_name)."'";
                else
                    $write_list["`".$property_name."`"] = "'".$this->$property_name."'";
            }

        }
        if (count($write_list))
        {
            $write_list['`lasttime`'] = "NOW()";
        }
        return $write_list;
    }

    /**
     * 设定为新订单
     */
    public function setNewOrder()
    {
        $this->_new_order_flag = true;
        return $this;
    }

    /**
     * 更新订单信息，预写入数据库
     * @param unknown $name
     * @param unknown $value
     */
    public function updateOrder(string $name,$value)
    {
        if ($name != "_id" && $name != "lasttime" && $name != 'transcation_id')
        {
            $this->__set($name, $value);
            $this->_update_list[] = $name;
        }
        return $this;
    }

    /**
     * 批量更新订单信息，预写入数据库
     * @param array $value_list
     */
    public function updateMultiOrder(array $value_list)
    {
        foreach ($value_list as $key => $value)
        {
            $this->updateOrder($key,$value);
        }
        return $this;
    }

    /**
     * 获取角色id
     */
    public function getRoleid()
    {
        return $this->roleid;
    }

    /**
     * 获取订单剩余时间
     */
    public function getOrderExpire()
    {
        var_dump($this->time,(time() - $this->time));
        return (time() - $this->time);
    }

    /**
     * 取消订单操作
     * @param string $decept
     */
    public function doCancelUncompleteOrder(string $decept = "user operate")
    {
        $this->updateOrder("deception", $decept);
        $this->updateOrder("status", 3);
        $this->save();
    }

    public function get_transcation_count()
    {
        $order_list = self::mysql_fetch_one("SELECT count(*) AS `count` FROM `transcation` WHERE `status` = 1 AND `uid` = '{$this->uid}' AND `roleid` ='{$this->roleid}' AND `serverid` = {$this->serverid} AND `channel` = '{$this->channel}'");
        return isset($order_list["count"])?$order_list["count"]:0;
    }

    /**
     * 创建订单类，追加到实例化堆
     * @param unknown $order_info
     */
    private static function createTranscationModel(array $order_info)
    {
        $transcation_m = new TranscationModel();
        foreach ($order_info as $key => $value)
        {
            $transcation_m ->__set($key, $value);
        }

        return $transcation_m;
    }


    /**
     * 检测是否有流程未完毕的订单
     * @author edison
     */
    public static function getUncompleteOrder(string $uid, string $roleid, int $serverid = 1,string $sdk = 'PC')
    {
        $order_list = self::mysql_fetch_all("SELECT * FROM `transcation` WHERE `status` = -1 AND `uid` = '$uid' AND `roleid` ='$roleid' AND `serverid` = $serverid AND `channel` = '$sdk'");
        $transcation_list = [];
        foreach ($order_list as $order)
        {
            $transcation_list[] = self::createTranscationModel($order);
        }
        return $transcation_list;
    }

    /**
     * 新增充值
     * @author edison
     */
    public static function addTranscationModel(string $uid, int $serverid, string $roleid, int $money, int $time, int $sub = 0, string $sdk = '4399')
    {
        $mark = microtime(true) . '.' . rand(100, 999);
        $mark = str_replace(".", "", $mark);

        $insert_list = array(
            'uid' => $uid,
            'serverid' => $serverid,
            'roleid' => $roleid,
            'money' => $money,
// 				'gamemoney' => $coupon,
// 				'item' => $item,
            'mark' => $mark,
            'deception' => 'create order',
            'status' => -1,
            'time' => $time,
// 				'sub' => $sub,
            'channel' => $sdk,
        );

        $transcation = self::createTranscationModel($insert_list);
        $transcation ->updateOrder("transcation_count", $transcation->get_transcation_count());
        $transcation ->setNewOrder();

        return $transcation;
    }

    /**
     * 获取订单类id根据订单id
     * @param unknown $channel
     * @param unknown $order_id
     */
    public static function getTranscationByOrderId(string $channel,string $order_id)
    {
        $orderInfo = self::mysql_fetch_one("SELECT * FROM transcation WHERE orderid = '$order_id' AND channel = '$channel'");
        if(!is_array($orderInfo) || count($orderInfo) == 0)
            return false;
        else{
            return self::createTranscationModel($orderInfo);
        }
    }

    /**
     * 获取订单类id根据订单mark
     * @param unknown $mark
     */
    public static function getTranscationByMark(string $mark)
    {
        $orderInfo = self::mysql_fetch_one("SELECT * FROM transcation WHERE mark = '$mark'");
        if(!is_array($orderInfo) || count($orderInfo) == 0)
            return false;
        else{
            return self::createTranscationModel($orderInfo);
        }
    }

    /**
     * 获取订单类id集根据角色id
     * @param unknown $roleid
     * @param number $startDate
     * @param number $endDate
     */
    public static function getTranscationByRole($roleid,$startDate = 0,$endDate = 0,$page=1,$page_size = 20)
    {
        $start_flag = count(self::$transcation_list);
        $sql = "SELECT TS.`time`,TS.`orderid`,TS.`uid`,TS.`money`,TS.`gamemoney`,TS.`status`,TS.`item`,TS.`transcation_count`,TS.`lasttime`,TS.`action_id` FROM `transcation` AS TS WHERE TS.`status` IN (0,1,2) AND TS.`roleid` = '$roleid'";
        $count_sql = "SELECT COUNT(*) AS count FROM `transcation` AS TS WHERE TS.`status` IN (0,1,2) AND TS.`roleid` = '$roleid'";
        if ($startDate > 0) {
            $sql .= " AND TS.`time` >= '".date('Y-m-d 00:00:00',$startDate)."'";
            $count_sql .= " AND TS.`time` >= '".date('Y-m-d 00:00:00',$startDate)."'";
        }
        if ($endDate > 0){
            $sql .= " AND TS.`time` <= '".date('Y-m-d 23:59:59',$endDate)."'";
            $count_sql .= " AND TS.`time` <= '".date('Y-m-d 23:59:59',$endDate)."'";
        }
        $sql .= " ORDER BY TS.time desc LIMIT ".(($page-1)*20).",".$page_size;
        $order_list = self::mysql_fetch_all($sql);
        $order_count = intval(self::mysql_fetch_one($count_sql)["count"]);
        if($order_count > 0)
        {
            $end_flag = $start_flag - 1;
            foreach ($order_list as $order_info)
            {
                $end_flag++;
                self::$transcation_list[$end_flag] = transction_model::createTranscationModel($order_info);
            }
            return [$start_flag,$end_flag,$order_count];
        }
        return false;
    }
}

?>