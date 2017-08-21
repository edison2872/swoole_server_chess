<?php
/**
 * Created by PhpStorm.
 * User: edison
 * Date: 2017/6/23
 * Time: 下午4:32
 */

namespace App\Framework\Base;

use App\Model\TranscationModel;
use App\Model\RoleModel;
use App\Model\UserModel;

abstract class Sdk
{
    protected $request_param = [];
    protected $transcation_m = null;
    private $new_transcation = false;
    protected $order_lost = false;
    protected $role_id = null;

    protected static function requestUrl($url,array $param,$method = "GET")
    {
        $curl_obj = curl_init();
        switch (strtolower($method)) {
            case "get":
                $param_list_temp = [];
                foreach ($param as $key => $value)
                    $param_list_temp[] = "$key=$value";
                $url .= count($param_list_temp)>0?"?".implode("&",$param_list_temp):"";
                curl_setopt($curl_obj,CURLOPT_URL,$url);
                break;
            case "post":
                curl_setopt($curl_obj,CURLOPT_URL,$url);
                curl_setopt($curl_obj,CURLOPT_POST , 1);
                curl_setopt($curl_obj,CURLOPT_POSTFIELDS,http_build_query($param));
                break;
            default:
                curl_close($curl_obj);
                throw new \Exception();
        }

        curl_setopt($curl_obj,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl_obj,CURLOPT_HEADER,0);
        $result = curl_exec($curl_obj);
        $error = curl_errno($curl_obj);
        curl_close($curl_obj);
        return $error?null:(is_array($result)?json_decode($result,true):$result);
    }

    public function __construct(array $request_param)
    {
        $this->request_param = $request_param;
    }

    protected function getTranscationModelByMark(string $mark)
    {
        if (is_null($this->transcation_m) || !($this->transcation_m instanceof TranscationModel))
            $this->transcation_m = TranscationModel::getTranscationByMark($mark);
        return $this->transcation_m;
    }

    protected function checkParam(string $method)
    {
        foreach (is_array($this->getParamList()[$method])?$this->getParamList()[$method]:[] as $param) {
            if (!array_key_exists($param,$this->request_param) || empty($this->request_param[$param]))
                return false;
        }
        return true;
    }

    protected function getUserIdByAccount(string $account)
    {
        return UserModel::getUserIdByAccount($account,static::getSdkChannel());

    }

    protected function isRoleExists(int $user_id,int $server_id,string $role_id)
    {
        $role_m = new RoleModel($server_id,$user_id,$role_id);
        return $role_m->checkRoleExists();
    }

    protected static function isOrderDeadline(int $order_create_expire)
    {
        return $order_create_expire > 60*60 ? true:false;
    }

    protected function commitTranscation(string $mark,int $status,int $money,int $game_money,string $deception,string $order_id = null,bool $mark_underline = false,int $action_id = 0 ,string $item_log = "")
    {
        echo "commit_transcation===\n";
        if (($transcation_m = $this->getTranscationModelByMark($mark)) === false)
        {
            $user_order_info = $this->getUserInfo();
            var_dump($user_order_info);
            $transcation_m = TranscationModel::addTranscationModel($user_order_info['uid'], $user_order_info['server_id'], $user_order_info["role_id"], $money, time(), 0, $user_order_info["channel"]);
        }
        if ($this->new_transcation)
            $transcation_m ->setNewOrder();

        $transcation_update = ["status" => $status , "money" => $money , "gamemoney" => $game_money , "deception" => $deception];
        if ($status === 1) {
            $transcation_update["transcation_count"] = $transcation_m->get_transcation_count();
            $transcation_update["action_id"] = $action_id;
            $transcation_update["item"] = $item_log;
        }
        if (!is_null($order_id))
            $transcation_update["orderid"] = $order_id;
        if ($mark_underline)
            $transcation_update["mark"] = "_".$mark;

        $result = $transcation_m->updateMultiOrder($transcation_update)->save();
        if ($result === false) {
            $transcation_update["deception"] = "order_lost($order_id).";
            unset($transcation_update["orderid"]);
            $user_order_info = $this->getUserInfo();
            var_dump($user_order_info);
            TranscationModel::addTranscationModel($user_order_info['uid'], $user_order_info['server_id'], $user_order_info["role_id"], $money, time(), 0, $user_order_info["channel"])->updateMultiOrder($transcation_update)->save();
            $this ->order_lost = true;
        }
    }

    protected function createNewTranscation()
    {
        $this -> new_transcation = true;
    }

    protected function sendReward($money,$sub,&$item_log)
    {
        $item_log = "111";
        return 1;
    }

    abstract static function loginAuth(string $user_account,array $loginParram);
    abstract static function autoRegister();
    protected abstract static function getSdkChannel();
    protected abstract function getParamList();
    protected abstract function getUserInfo();
}