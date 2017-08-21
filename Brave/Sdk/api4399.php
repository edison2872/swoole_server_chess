<?php
namespace App\Sdk;
/**
 * Created by PhpStorm.
 * User: edison
 * Date: 2017/6/23
 * Time: 下午5:20
 */

use App\Model\RoleModel;
use App\Model\TranscationModel;
use App\Model\UserModel;

class api4399 extends \App\Framwork\Base\Sdk
{
    const authUrl = "http://m.4399api.com/openapi/oauth-check.html";
    const APP_SECRET = "3056fb441892a7bda4916f63ab53e63f";    //4399认证密钥
    const APP_ID = "108474";    //APP_ID
    const ORDER_PAY_STATUS_ERROR = 1;
    const ORDER_PAY_SUATUS_SUCCESS = 2;
    const ORDER_PAY_SUATUS_FAILED = 3;

    protected static function getSdkChannel()
    {
        return "4399";
    }

    public static function loginAuth(string $user_account, array $loginParram)
    {
        // TODO: Implement loginAuth() method.
        if (is_numeric($user_account) && isset($loginParram["token"]) && is_string($loginParram["token"])) {
            $result = parent::requestUrl(self::authUrl,["uid" => $user_account,"state" => $loginParram["token"]]);
            switch ($result["code"]) {
                case 100:
                    return 0;
                case 87:
                    return 10002;
                case 85:
                    return 20014;
                case 82:
                    return 20015;
            }
        } else
            return 10002;
    }

    public static function autoRegister()
    {
        return true;
    }

    public function callback_pay_result()
    {
        if (!$this->checkParam("callback_pay_result")) {
            //参数不正确
//            $this->commitTranscation($this->request_param["mark"],0,$this->request_param["money"],$this->request_param["gamemoney"],"other_error_param_uncomplete.",isset($this->request_param["orderid"])?$this->request_param["orderid"]:null);
            return $this->returnToClient(self::ORDER_PAY_STATUS_ERROR,isset($this->request_param["money"])?$this->request_param["money"]:0,isset($this->request_param["gamemoney"])?$this->request_param["gamemoney"]:0,'传参错误','other_error');
        }

        if ($this->auth("callback_pay_result")) {
            $transcation_m = $this->getTranscationModelByMark($this->request_param["mark"]);
            if ($transcation_m === false || !$transcation_m -> isOrderUncomplete() || !$transcation_m -> checkOrderIsAccount("4399",$this->request_param["uid"],$this->request_param["serverid"])) {
                //不存在该用户在该服的未支付订单
                $this->createNewTranscation();
                $this->commitTranscation($this->request_param["mark"],0,$this->request_param["money"],$this->request_param["gamemoney"],"other_error_no_unpayed_order.",$this->request_param["orderid"],true);
                return $this ->returnToClient(self::ORDER_PAY_STATUS_ERROR,$this->request_param["money"],$this->request_param["gamemoney"],'不存在该用户在该服的未支付订单','other_error');
            }

            $this->role_id = $transcation_m -> getRoleid();
            if (TranscationModel::getTranscationByOrderId("4399", $this->request_param['orderid']) !== false) {
                //订单号重复
                $this->commitTranscation($this->request_param["mark"],0,$this->request_param["money"],$this->request_param["gamemoney"],"orderid_exist({$this->request_param["orderid"]})");
                return $this ->returnToClient(self::ORDER_PAY_STATUS_ERROR,$this->request_param["money"],$this->request_param["gamemoney"],'订单号不允许重复','orderid_exist');
            } else if (!is_numeric($this->request_param['uid']) || ($userid = $this->getUserIdByAccount($this->request_param['uid'])) === false) {
                //uid不存在
                $this->commitTranscation($this->request_param["mark"],0,$this->request_param["money"],$this->request_param["gamemoney"],"user_not_exist({$this->request_param["uid"]})",$this->request_param["orderid"]);
                return $this->returnToClient(self::ORDER_PAY_STATUS_ERROR,$this->request_param["money"],$this->request_param["gamemoney"],'用户id不存在','user_not_exist');
            } else if (!$this->isRoleExists($userid, $this->request_param['serverid'],$this->role_id)) {
                //角色不存在或数据异常
                $this->commitTranscation($this->request_param["mark"],0,$this->request_param["money"],$this->request_param["gamemoney"],"role_not_exist",$this->request_param["orderid"]);
                return $this->returnToClient(self::ORDER_PAY_STATUS_ERROR,$this->request_param["money"],$this->request_param["gamemoney"],'角色不存在','user_not_exist');
            } else if (parent::isOrderDeadline($transcation_m->getOrderExpire()) === true) {
                //支付操作是否超时
                $this->commitTranscation($this->request_param["mark"],2,$this->request_param["money"],$this->request_param["gamemoney"],"recharge_time_out.",$this->request_param["orderid"]);
                return $this->returnToClient(self::ORDER_PAY_SUATUS_FAILED,$this->request_param["money"],$this->request_param["gamemoney"],'充值操作超时，代币将返还');
            } else {
                $item_log = "";
                $action_id = $this->sendReward($this->request_param['money'], 0, $item_log);
                if ($action_id > 0) {
                    $this->commitTranscation($this->request_param["mark"],1,$this->request_param["money"],$this->request_param["gamemoney"],"recharge_success",$this->request_param["orderid"],false,$action_id,$item_log);
                    return $this->returnToClient(self::ORDER_PAY_SUATUS_SUCCESS,$this->request_param["money"],$this->request_param["gamemoney"]);
                } elseif ($action_id == -1) {
                    $this->commitTranscation($this->request_param["mark"],2,$this->request_param["money"],$this->request_param["gamemoney"],"reward_send_failed",$this->request_param["orderid"]);
                    return $this->returnToClient(self::ORDER_PAY_SUATUS_FAILED,$this->request_param["money"],$this->request_param["gamemoney"],"奖励发送失败,代币将返还");
                } else {
                    $this->commitTranscation($this->request_param["mark"],0,$this->request_param["money"],$this->request_param["gamemoney"],"money_error",$this->request_param["orderid"]);
                    return $this->returnToClient(self::ORDER_PAY_STATUS_ERROR,$this->request_param["money"], $this->request_param["gamemoney"],"该价位的商品未开放");
                }
            }
        } else {
            //认证错误
            $this->createNewTranscation();
            $this->commitTranscation($this->request_param["mark"],0,$this->request_param["money"],$this->request_param["gamemoney"],"md5_sign_error",$this->request_param["orderid"],true);
            return $this->returnToClient(self::ORDER_PAY_STATUS_ERROR,$this->request_param["money"],$this->request_param["gamemoney"],"请求串的md5验证码错误","sign_error");
        }
    }

    public function callback_order_info()
    {
        if (!$this->checkParam('callback_order_info')) {
            //参数不正确
            return "1";
        }

        if (true || $this->auth("callback_order_info")) {
            $transcation_m = TranscationModel::getTranscationByOrderId("4399", $this->request_param["order"]);
            if ($transcation_m === false || !$transcation_m->checkOrderIsAccount("4399", $transcation_m->uid, $this->request_param["serverid"])) {
                //订单信息不存在
                return "-1";
            } else {
                $this->roleid = $transcation_m->getRoleid();
                $nickName = (new RoleModel($this->request_param["serverid"],UserModel::getUserIdByAccount($transcation_m->uid,"4399"),$this->roleid))->getRoleNickName();
                if (!is_null($nickName)) {
                    $result = [];
                    $result['order'] = strval($transcation_m->orderid);
                    $result['uid'] = strval($transcation_m->uid);
                    $result['nickname'] = strval($nickName);
                    $result['money'] = strval($transcation_m->money);
                    $result['gamemoney'] = strval($transcation_m->gamemoney);
                    $result['time'] = $transcation_m->lasttime;
                    $result['server_id'] = strval($transcation_m->serverid);
                    switch (intval($transcation_m->status)) {
                        case 1:
                            $result['status'] = "1";
                            break;
                        case 2:
                            $result['status'] = "-1";
                            break;
                        default:
                            $result['status'] = "0";
                            break;
                    }
                    return $result;
                } else {
                    return "0";
                }
            }
        } else {
            //认证错误
            return "2";
        }
    }

    protected function getParamList()
    {
        return [
            "callback_pay_result" => ['orderid', 'p_type', 'uid', 'money', 'gamemoney', 'serverid', 'mark', 'time', 'sign'],
            "callback_order_info" => ["order","time","serverid","flag"],
        ];
    }

    protected function returnToClient(int $status,int $money,int $game_money,string $msg = "" ,string $code = "")
    {
        if ($this->order_lost) {
            $status = self::ORDER_PAY_SUATUS_FAILED;
            $msg = "订单丢失";
        }
        $result = [];
        switch ($status) {
            case self::ORDER_PAY_SUATUS_SUCCESS:
                $result["status"] = self::ORDER_PAY_SUATUS_SUCCESS;
                $result["code"] = null;
                $result["msg"] = "充值成功";
                break;
            case self::ORDER_PAY_SUATUS_FAILED:
                $result["status"] = self::ORDER_PAY_SUATUS_FAILED;
                $result["code"] = null;
                $result["msg"] = empty($msg)?"unknown":$msg;
                break;
            default:
                $result["status"] = self::ORDER_PAY_STATUS_ERROR;
                $result["code"] = empty($code)?"other_error":$code;
                $result["msg"] = empty($msg)?"unknown":$msg;
                break;
        }
        $result["money"] = $money;
        $result["gamemoney"] = $game_money;
        return $result;
    }

    private function auth(string $action)
    {
        switch ($action) {
            case "callback_pay_result":
                $gameMd5 = md5($this->request_param['orderid'] . $this->request_param['uid'] . $this->request_param['money'] . $this->request_param['gamemoney'] . $this->request_param['serverid'] . self::APP_SECRET . $this->request_param['mark'] . $this->request_param['roleid'] . $this->request_param['time']);
                if ($this->request_param["sign"] == $gameMd5)
                    return true;
                break;
            case "callback_order_info":
                $gameMd5 = md5($this->request_param['order'] . $this->request_param['time'] . self::APP_SECRET);
                if ($this->request_param["flag"] == $gameMd5)
                    return true;
                break;
        }
        return false;
    }

    protected function getUserInfo()
    {
        // TODO: Implement getUserInfo() method.
        return [
            "channel" => self::getSdkChannel(),
            "uid" => $this->request_param["user_id"],
            "server_id" => $this->request_param["server_id"],
            "role_id" => is_null($this->role_id)?"?":$this->role_id,
        ];
    }
}