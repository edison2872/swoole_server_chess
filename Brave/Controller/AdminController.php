<?php
/**
 * Created by PhpStorm.
 * User: edison
 * Date: 2017/7/4
 * Time: 下午11:08
 */

namespace App\Controller;


class AdminController extends \App\Framework\Base\Controller
{
    public function handle()
    {
        $class_name = "App\\Admin\\".$this->getRequestData("module")."Admin";
        if (class_exists($class_name)){
            $class_model = new \ReflectionClass($class_name);
            if (is_null($method = $class_model->getMethod($this->getRequestData("action")))) {
                echo json_encode(["result" => 403,"msg" => "action not exists"])."\n";
                throw new \Exception();
            }
            $action_parameter_list  = [];
            foreach ($method->getParameters() as $parameter_model) {
                if (is_null($this->getRequestData($parameter_model->name)) && !$parameter_model->isOptional()) {
                    echo json_encode(["result" => 403,"msg" => "parameter not complete."])."\n";
                    throw new \Exception();
                } else if (is_null($this->getRequestData($parameter_model->name)))
                    $action_parameter_list[] = $parameter_model->getDefaultValue();
                else
                    $action_parameter_list[] = $this->getRequestData($parameter_model->name);
//                $result = $method->invokeArgs(new $class_name(),$action_parameter_list);
                $result = \Swoole\Coroutine::call_user_func_array([new $class_name(),$this->getRequestData("action")],$action_parameter_list);
//                $result = call_user_func_array([new $class_name(),$this->getRequestData("action")],$action_parameter_list);
//                $result = (new $class_name())->{$this->getRequestData("action")}($action_parameter_list[0]);
                $this->setClientArrayParam($result);
            }
        } else {
            echo json_encode(["result" => 403,"msg" => "module not exists"])."\n";
            throw new \Exception();
        }

    }
}
