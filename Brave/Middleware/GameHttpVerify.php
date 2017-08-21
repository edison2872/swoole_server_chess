<?php
/**
 * Created by PhpStorm.
 * User: edison
 * Date: 2017/5/31
 * Time: 下午4:57
 */

namespace App\Middleware;

use TSF\Contract\Http\Middleware;
use TSF\Contract\Request;
use TSF\Facade\App;
use App\Model\User;
use TSF\Facade\Config;

class GameHttpVerify implements Middleware
{
    private $controller = "";
    private $action = "";

    public function handle(Request $request)
    {
        $this->controller = lcfirst($request->get("controller"));
        $this->action = $request->get("action");
        $this->verifyPost($request);
        $this->loginCheck($request);
    }

    private function verifyPost(Request $request)
    {
        $post_data = $request->postData("data");
        $result_post_data = [];
        if (is_array(($post_data = json_decode($post_data,true)))) {
            $module_param = Config::facade()->get("module.{$this -> controller}.{$this->action}.param", []);
            foreach ((is_array($module_param) ? $module_param : []) as $post_key) {
                if (!isset($post_data[$post_key])) {
                    throw new \Exception();
                } else
                    $result_post_data[$post_key] = $post_data[$post_key];
            }
        }
        var_dump($module_param);
        $request->setPostData($result_post_data);
    }

    private function loginCheck(Request $request)
    {
        if (true && $login_conifg = Config::facade()->get("module.{$this -> controller}.{$this->action}.login", false)) {
            $post_data = $request->postData("data");
            if (empty($post_data["userid"]) || empty($post_data["serverid"]) || ($login_conifg == 2 && empty($post_data["roleid"])))
                throw new \Exception();
            $user_m = new User($request->fd,intval($post_data["serverid"]),$post_data["userid"],$login_conifg == 2 ? $post_data["roleid"]:"");
            $user_online_info = $user_m->getUserLoginInfo();
            if (empty($user_online_info))
                throw new \Exception();

        }
    }

    private function banCheck()
    {

    }
}