<?php
/**
 * Created by PhpStorm.
 * User: edison
 * Date: 2017/6/28
 * Time: 下午3:05
 */

namespace App\Sdk;
use App\Model\GameConfig;
use App\Model\UserModel;
use TSF\Facade\Http\HttpRequest;
use App\Model\RoleModel;
use App\Model\TranscationModel;
class apiPC extends \App\Framework\Base\Sdk
{
    const ORDER_PAY_STATUS_ERROR = 1;
    const ORDER_PAY_SUATUS_SUCCESS = 2;
    const ORDER_PAY_SUATUS_FAILED = 3;

    protected static function getSdkChannel()
    {
        return "PC";
    }

    public static function loginAuth(string $user_account,array $loginParram)
    {
        if (!GameConfig::PC_CNANNEL)
        {
            throw new \Exception();
        }
        $password = isset($loginParram["password"])?strval($loginParram["password"]):"";
        $request = HttpRequest::facade();
        $user_id = UserModel::getUserIdByAccount($user_account,"PC");
        if ($user_id === false)
            return 20005;
        $user_m = new UserModel($user_id);
        if ($user_m->checkPassword($password))
            return 0;
        else
            return 20006;
    }

    public static function autoRegister()
    {
        return false;
    }

    public function callback_pay_result()
    {
        if (!$this->checkParam("callback_pay_result")) {
            //参数不正确
//            $this->commitTranscation($this->request_param["mark"],0,$this->request_param["money"],$this->request_param["gamemoney"],"other_error_param_uncomplete.",isset($this->request_param["orderid"])?$this->request_param["orderid"]:null);
            return $this->returnToClient(self::ORDER_PAY_STATUS_ERROR, isset($this->request_param["money"]) ? $this->request_param["money"] : 0, isset($this->request_param["game_money"]) ? $this->request_param["game_money"] : 0, '传参错误', 'other_error');
        }

        if ($this->auth()) {
            $transcation_m = $this->getTranscationModelByMark($this->request_param["mark"]);
            if ($transcation_m === false || !$transcation_m->isOrderUncomplete() || $transcation_m->uid != $this->request_param["user_id"]) {
                //不存在该用户在该服的未支付订单
                $this->createNewTranscation();
                $this->commitTranscation($this->request_param["mark"], 0, $this->request_param["money"], $this->request_param["game_money"], "other_error_no_unpayed_order.", $this->request_param["order_id"], true);
                return $this->returnToClient(self::ORDER_PAY_STATUS_ERROR, $this->request_param["money"], $this->request_param["game_money"], '不存在该用户在该服的未支付订单', 'other_error');
            }

            $this->role_id = $transcation_m->getRoleid();
            if (TranscationModel::getTranscationByOrderId("PC", $this->request_param['order_id']) !== false) {
                //订单号重复
                $this->commitTranscation($this->request_param["mark"], 0, $this->request_param["money"], $this->request_param["game_money"], "orderid_exist({$this->request_param["order_id"]})");
                return $this->returnToClient(self::ORDER_PAY_STATUS_ERROR, $this->request_param["money"], $this->request_param["game_money"], '订单号不允许重复', 'orderid_exist');
            } else if (!is_numeric($this->request_param['user_id']) || is_null((new UserModel($this->request_param['user_id'],"PC")) ->getUserAccount())) {
                //uid不存在
                $this->commitTranscation($this->request_param["mark"], 0, $this->request_param["money"], $this->request_param["game_money"], "user_not_exist({$this->request_param["user_id"]})", $this->request_param["order_id"]);
                return $this->returnToClient(self::ORDER_PAY_STATUS_ERROR, $this->request_param["money"], $this->request_param["game_money"], '用户id不存在', 'user_not_exist');
            } else if (empty($this->role_id) || !$this->isRoleExists($this->request_param['user_id'], $this->request_param['server_id'], $this->role_id)) {
                //角色不存在或数据异常
                $this->commitTranscation($this->request_param["mark"], 0, $this->request_param["money"], $this->request_param["game_money"], "role_not_exist", $this->request_param["order_id"]);
                return $this->returnToClient(self::ORDER_PAY_STATUS_ERROR, $this->request_param["money"], $this->request_param["game_money"], '角色不存在', 'user_not_exist');
            } else if (parent::isOrderDeadline($transcation_m->getOrderExpire()) === true) {
                //支付操作是否超时
                $this->commitTranscation($this->request_param["mark"], 2, $this->request_param["money"], $this->request_param["game_money"], "recharge_time_out.", $this->request_param["order_id"]);
                return $this->returnToClient(self::ORDER_PAY_SUATUS_FAILED, $this->request_param["money"], $this->request_param["game_money"], '充值操作超时，代币将返还');
            } else {
                $item_log = "";
                $action_id = $this->sendReward($this->request_param['money'], 0, $item_log);
                if ($action_id > 0) {
                    $this->commitTranscation($this->request_param["mark"], 1, $this->request_param["money"], $this->request_param["game_money"], "recharge_success", $this->request_param["order_id"], false, $action_id, $item_log);
                    return $this->returnToClient(self::ORDER_PAY_SUATUS_SUCCESS, $this->request_param["money"], $this->request_param["game_money"]);
                } elseif ($action_id == -1) {
                    $this->commitTranscation($this->request_param["mark"], 2, $this->request_param["money"], $this->request_param["game_money"], "reward_send_failed", $this->request_param["order_id"]);
                    return $this->returnToClient(self::ORDER_PAY_SUATUS_FAILED, $this->request_param["money"], $this->request_param["game_money"], "奖励发送失败,代币将返还");
                } else {
                    $this->commitTranscation($this->request_param["mark"], 0, $this->request_param["money"], $this->request_param["game_money"], "money_error", $this->request_param["order_id"]);
                    return $this->returnToClient(self::ORDER_PAY_STATUS_ERROR, $this->request_param["money"], $this->request_param["game_money"], "该价位的商品未开放");
                }
            }
        } else {
            //认证错误
            $this->createNewTranscation();
            $this->commitTranscation($this->request_param["mark"], 0, $this->request_param["money"], $this->request_param["game_money"], "pc_sign_error", $this->request_param["order_id"], true);
            return $this->returnToClient(self::ORDER_PAY_STATUS_ERROR, $this->request_param["money"], $this->request_param["game_money"], "PC验证不通过", "sign_error");
        }
    }

    protected function getParamList()
    {
        return [
            "callback_pay_result" => ['order_id', 'user_id', 'money', 'game_money', 'server_id', 'mark', 'time'],
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

    private function auth()
    {
        $request = HttpRequest::facade();
        return true && ($request -> remoteInfo["addr"] == "127.0.0.1" || $request -> remoteInfo["addr"] == "localhost") ? true:false;
    }

    protected function getUserInfo()
    {
        // TODO: Implement getUserInfo() method.
        return [
            "channel" => static::getSdkChannel(),
            "uid" => $this->request_param["user_id"],
            "server_id" => $this->request_param["server_id"],
            "role_id" => is_null($this->role_id)?"?":$this->role_id,
        ];
    }
}