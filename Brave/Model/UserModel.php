<?php

namespace App\Model;
/**
 * Created by PhpStorm.
 * User: edison
 * Date: 2017/6/9
 * Time: 下午2:03
 */
use \App\Model\ServerModel;

class UserModel extends \App\Framework\Base\Model
{
    private $channel = "";
    public function __construct(int $user_id,string $channel = "PC")
    {
        $this->user_id = $user_id;
        $this ->channel = $channel;
    }

    public function getUserLoginInfo(int $server_id)
    {
        $online_info = parent::execReadRedisCommand("hReadAll",["user:".$this->user_id]);
        if ($online_info["serverid"] != $server_id)
            throw new \Exception();
        if (!empty($this->role_id) && $this->role_id != $online_info["roleid"])
            throw new \Exception();
        return $online_info;
    }

    public function checkPassword(string $password)
    {
        $result = parent::mysql_fetch_one("SELECT `passwd` FROM users WHERE `userid` = '{$this->user_id}' AND `channel` = '{$this->channel}'");
        return isset($result["passwd"]) && $result["passwd"] === strval($password) ? true: false;
    }

    public function getUserAccount()
    {
        $result = parent::mysql_fetch_one("SELECT `account` FROM users WHERE `userid` = '{$this->user_id}' AND `channel` = '{$this->channel}'");
        return isset($result["account"])?strval($result["account"]):null;
    }

    public function isUserAllowLoginInServer(int $server_id)
    {
        switch (ServerModel::getServerPermissionStatus($server_id))
        {
            case 1:
                return $this->isWhiteNameUser();
            case 2:
                return true;
            default:
                return false;
        }
    }

    /**
     * 账号是否允许登录
     * @param $account
     * @param string $channel
     * @return bool
     */
    public function isUserAllowLogin()
    {
        echo ServerModel::getAllServerRunningStatus();
        switch (ServerModel::getAllServerRunningStatus()) {
            case 1:
                return self::isWhiteNameUser();
            case 2:
                return true;
            default:
                return false;
        }
    }

    /**
     * 是否为白名单账号
     */
    public function isWhiteNameUser()
    {
        if (!is_null($account = $this->getUserAccount()))
            return empty(self::getWhiteUserInfoByAccount($account,$this->channel))?false:true;
        return false;
    }

    public static function getUserIdByAccount(string $account,string $channel = "PC")
    {
        $result = parent::mysql_fetch_one("SELECT `userid` FROM users WHERE `account` = '$account' AND `channel` = '$channel'");
        return isset($result["userid"])?intval($result["userid"]):false;
    }

    public static function checkUserExistsByAccount(string $account,string $channel = "PC")
    {
        $account = trim($account);
        $data = parent::mysql_fetch_one("SELECT COUNT(*) AS `count` FROM users WHERE `account` = '$account' AND `channel` = '$channel'");
        return (isset($data["count"]) && $data["count"] > 0)?true:false;
    }

    public static function addUser(string $account,string $password = "0000",string $channel = "PC")
    {
        $account = trim($account);
        $time_stamp = time();
        parent::mysql_fetch_one("INSERT INTO users (`account`,`passwd`,`crtime`,`retime`,`channel`) VALUES ('$account','$password',$time_stamp,$time_stamp,'$channel')");
        $user_id = parent::getInsertLastId();
        return $user_id > 0?$user_id:false;
    }

    private static function getWhiteUserInfoByAccount(string $account,string $channel = "PC")
    {
        $account = str_replace([" ","　","\t","\n","\r"],["","","",""],$account);
        $white_info = parent::mysql_fetch_one("SELECT * FROM whitelist WHERE `account` = '$account' AND `channel` = '$channel'");
        return empty($white_info)?[]:$white_info;
    }




}