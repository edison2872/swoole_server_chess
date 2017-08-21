<?php
/**
 * Created by PhpStorm.
 * User: edison
 * Date: 2017/6/29
 * Time: 下午6:05
 */

namespace App\Controller;

use App\Framework\Base\Controller;

class SDKController extends Controller
{
    public function handle()
    {
        $sdk_direct_action = $this ->directSDKAction();
        if (is_null($sdk_direct_action)) {
            $this->setClientArrayParam([]);
        } else {
            $class_name = "\\App\\Sdk\\api".$sdk_direct_action[0];
            $method_name = $sdk_direct_action[1];
            $class = new $class_name($this->getRequestData());
            $resopnse = $class->$method_name();
            $this->setClientArrayParam($resopnse);
        }
    }

    private function directSDKAction()
    {
        $sdkJudgeList = array(
            "4399,callback_pay_result" => ["orderid" => null],
            "4399,callback_order_info" => ["order" => null],
            "PC,callback_pay_result" => ["channel" => "PC"],
        );
        foreach ($sdkJudgeList as $sdk_direct_action => $param_list) {
            foreach ($param_list as $key => $value) {
                if ((is_null($value) && !is_null($this->getRequestData($key))) || (!is_null($value) && $this->getRequestData($key) == $value))
                    return explode(',',$sdk_direct_action);
            }
        }
        return null;
    }
}