<?php
/**
 * Created by PhpStorm.
 * User: edison
 * Date: 2017/6/28
 * Time: 下午2:29
 */

namespace App\Model;


class RoleModel extends \App\Framework\Base\Model
{
    public static function getRoleList(int $user_id,int $server_id)
    {
        $role_list = parent::mysql_fetch_all("SELECT `delstatus`,`retime`,`roleId`,`num` FROM `roles` WHERE `userid` = '$user_id' AND `serverid` = $server_id AND `delstatus` <> 2 ORDER BY `retime` DESC");
        $role_info_list = [];
        if (is_array($role_list)) {
            foreach ($role_list as $role_info_temp) {
                $role_info = [];
                if ($role_info_temp["delstatus"] == 1) {
                    $del_time = $role_info_temp["retime"] + 10;  //删除延时
                    $del_expire = $del_time - time();
                    if ($del_expire <= 0) {
                        //时间到正式删除角色
                        continue;
                    }
                }

                $role_info["roleid"] = $role_info_temp["roleId"];
                $role_info["delStatus"] = $role_info_temp["delstatus"];
                $role_info["lastLoginTime"] = $role_info_temp["retime"];
                $role_info["num"] = $role_info_temp["num"];
                $role_info["delExpire"] = $del_expire > 0 ?$del_expire:-1;
                $role_info_list[] = $role_info;
            }
        }
        return $role_info_list;
    }

    private function affirmdelRole()
    {

    }

    public static function randRoleName(string $sex,int $server_id)
    {
        $start_flag = $sex == 'man' ? mt_rand(0, 64) : mt_rand(0, 106);
        $pkey = mt_rand(1, 499);
        $sql = "SELECT `name` FROM `randname` WHERE `pkey`={$pkey} AND `sex`='{$sex}' AND NOT EXISTS (SELECT `name` FROM `roles` WHERE `serverid`={$server_id} AND `name` = `randname`.`name`) LIMIT {$start_flag},1";
        $randName = parent::mysql_fetch_one($sql);

        return isset($randName['name'])?$randName['name']:null;
    }

    public function checkRoleExists()
    {
        $sql = "SELECT `roleId` FROM `roles` WHERE `userid`='{$this->user_id}' AND `serverid`={$this->server_id} AND `roleId` = '{$this->role_id}'";
        return empty(self::mysql_fetch_one($sql))?false:true;
    }

    public function getRoleNickName()
    {
        $sql = "SELECT `name` FROM `roles` WHERE `userid`='{$this->user_id}' AND `serverid`={$this->server_id} AND `roleId` = '{$this->role_id}'";
        $data = self::mysql_fetch_one($sql);
        return is_array($data) && isset($data["name"]) ? $data["name"]: null;
    }
}