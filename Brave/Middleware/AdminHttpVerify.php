<?php
/**
 * Created by PhpStorm.
 * User: edison
 * Date: 2017/7/5
 * Time: 上午11:25
 */

namespace App\Middleware;
use TSF\Contract\Request;

use TSF\Contract\Http\Middleware;

class AdminHttpVerify implements Middleware
{
    public function handle(Request $request)
    {
        $this->verifyPost($request);
        $this->ipCheck($request);
    }

    private function verifyPost(Request $request)
    {
        $post_data = $request->postData("data");
        if (is_array(($post_data = json_decode($post_data,true))) && count($post_data)) {
            $ParamFilter = ["serverId","module","action"];
            foreach ($ParamFilter as $post_key) {
                if (!isset($post_data[$post_key])) {
                    echo "param not complete.\n";
                    throw new \Exception();
                }
            }
            $request->setPostData($post_data);
        } else {
            echo "param is empty.\n";
            throw new \Exception();
        }
    }

    private function ipCheck(Request $request)
    {
        $allowIPs = ["192.168.66.222","192.168.66.136","192.168.66.20","192.168.66.223","192.168.66.234","127.0.0.1","192.168.66.71","192.168.66.12","192.168.66.225"];
        if (in_array($request->remoteInfo['addr'] , $allowIPs))
        {
            return true;
        } else {
            echo "ip not allowed.\n";
            throw new \Exception();
        }
    }
}