<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/5/22
 * Time: 14:33
 */
namespace App\Controller;

use App\Framework\Facade\MongoDBName;
use App\Logic\RoleLogic;
use App\Model\RoleModel;
use App\Model\Server;
use TSF\Facade\App;
use TSF\Facade\WebSocket\WSRequest;
use TSF\Facade\WebSocket\WSResponse;
use TSF\Pool\MySQL\MySQLRemote as MySQLPool;
//use TSF\Pool\MySQL\MySQLLocal as MySQLPool;
use TSF\Facade\Config;
use TSF\Core\Log;
use App\Model\ServerModel;
use App\Model\AnnouncementModel;
use App\Model\UserModel;
use App\Model\GameConfig;
use App\Framework\Base\Controller;
use Respect\Validation\Validator as v;

class UserController extends Controller
{
    public function createRole()
    {
        $res = WSRequest::facade()->get("m");
        var_dump($res);
        var_dump(Config::facade()->get('app.log', null));
        WSResponse::facade()->setRes('UI', "uiui")->status(0);

        echo "createRole-" . PHP_EOL;
        $roleLogic = RoleLogic::findOne(["_id" => "585a6cded9f8871a82b941fa"]);

        /*
        echo v::numeric()->validate(123) . PHP_EOL;
        $aa = Server::findOne(['serverId' => 2]);

        $aa->setAttribute("ip", "111.23.4.54");

        $aa->setAttribute("serverid", "3");

        $aa->update();
        */
    }

    public function randName()
    {
        $sex = trim($this->getRequestData("sex"));
        if (in_array($sex,GameConfig::SEX_ALLOW_LIST,true))
        {
            $rand_name = "";
            for ($i= 1;$i <= 3;$i++) {
                $rand_name = RoleModel::randRoleName($sex,$this->getRequestData("serverid"));
                if (!empty($rand_name))
                    break;
            }
            $this->setClientArrayParam(["result" => 0,"name" => $rand_name]);
        } else
            throw new \Exception();
    }

    public function serverList()
    {
        $result["result"] = 0;
        if ($this->getRequestData('res_option') != 2)
        {
            $server_list = ServerModel::getServerList();
            foreach ($server_list as $server_id => &$server_info) {
                if ($server_info["status"] == 2) {
                    $server_info["status"] = ServerModel::getServerBusyStatus($server_id);
                } else {
                    $server_info["status"] = 3;
                }
            }
            $result['serverList'] = $server_list;
        }

        if ($this->getRequestData('res_option') != 1) {
            $now_time_stamp = time();
            $result["noticeList"] = AnnouncementModel::getGameAnnouncementListByTime($now_time_stamp,$now_time_stamp);
        }
        $this->setClientArrayParam($result);
    }

    /**
     * 登录到角色选择界面
     */
    public function loginToRoleList()
    {
        $user_id = UserModel::getUserIdByAccount($this->getRequestData("user_account"));
        $sdk_class = "App\\Sdk\\api".$this->getRequestData("channel");
        if (is_null($user_id)) {
            if ($sdk_class::autoRegister()) {
                $user_id = UserModel::addUser($this->getRequestData("user_account"), "0000", $this->getRequestData("channel"));
                if ($user_id === false)
                    throw new \Exception();
            }
            else
                throw new \Exception();
        }
        $user_m = new UserModel($user_id,$this->getRequestData("channel"));
        //游戏是否开服
        if (false &&!$user_m -> isUserAllowLogin()){
            throw new \Exception();
        }

        if (false &&!$user_m->isUserAllowLoginInServer(intval($this->getRequestData("serverid")))) {
            throw new \Exception();
        }

        if (class_exists($sdk_class)) {
            $result = $sdk_class::loginAuth($this->getRequestData("user_account"),$this->getRequestData("loginParam"));
            if ($result === 0) {
                $role_list = RoleModel::getRoleList($user_id,$this->getRequestData("serverid"));
                $this->setClientArrayParam(["result" => 0,"serverid" => intval($this->getRequestData("serverid")), "userid" => $user_id , "roleList" => $role_list]);
            }else {
                throw new \Exception();
            }
        } else {
            throw new \Exception();
        }
    }

    public function PCLoginRegister()
    {
        if (!GameConfig::PC_CNANNEL)
        {
            throw new \Exception();
        }

        if (UserModel::checkUserExistsByAccount($this->getRequestData("account"),"PC"))
        {
            throw new \Exception();
        }
        else if (UserModel::addUser($this->getRequestData("account"),$this->getRequestData("password"),"PC")){
            $this->setClientArrayParam(["result" => 0]);
        }else{
            throw new \Exception();
        }
    }

}