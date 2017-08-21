<?php
/**
 * Created by PhpStorm.
 * User: edison
 * Date: 2017/6/15
 * Time: 下午5:29
 */

namespace App\Model;


class ServerModel extends \App\Framework\Base\Model
{
    static private $server_list = null;

    public static function getServerList()
    {
        if (is_null(self::$server_list))
        {
            $server_list_temp = parent::mysql_fetch_all("SELECT `serverid`,`serverip`,`opentime`,`status`,`ip`,`port`,`serverv`,`remark` FROM serverlist ORDER BY `serverid` ASC");
//            parent::getMysqlReadInstance("public");
//            $server_list_temp = [];
            var_dump($server_list_temp);
            if (is_array($server_list_temp)) {
                self::$server_list = [];
                foreach ($server_list_temp as $server_info) {
                    self::$server_list[strval($server_info["serverid"])] = $server_info;
                }
            }
        }
        return is_null(self::$server_list)?[]:self::$server_list;
    }

    /**
     * 获取单个服务区信息
     * @param $server_id
     */
    public static function getServerInfo(int $server_id)
    {
        $server_list = self::getServerList();
        return isset($server_list[$server_id])?$server_list[$server_id]:[];
    }

    /**
     * 获取服务区的闲忙状态
     * @param $server_id
     * @return int
     */
    public static function getServerBusyStatus(int $server_id)
    {
        $online_user_num = self::getServerOnlineCount($server_id);
        if ($online_user_num < 10)
            return 0;
        else if($online_user_num < 100)
            return 1;
        else
            return 2;
    }

    /**
     * 获取服务区在线人数
     * @param $server_id
     * @return int
     */
    private static function getServerOnlineCount(int $server_id)
    {
        return count(parent::execReadRedisCommand("getKeys",["server$server_id:role:*"]));
    }

    /**
     * 获取服务器开关状态
     * @return mixed
     */
    public static function getAllServerRunningStatus()
    {
        return parent::execReadRedisCommand("read",["GAME_RUNNING"]);
    }

    /**
     * 获取服务区登录权限
     * @param $server_id
     */
    public static function getServerPermissionStatus(int $server_id)
    {
        $server_info = self::getServerInfo($server_id);
        return isset($server_info["status"])?intval($server_info["status"]):0;
    }
}