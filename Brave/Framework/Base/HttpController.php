<?php
/**
 * Created by PhpStorm.
 * User: edison
 * Date: 2017/6/1
 * Time: 下午5:08
 */

namespace App\Framework\Base;



use TSF\Facade\App;

abstract class HttpController
{
    protected $post_data = [];

    public function __construct()
    {
        $request = App::facade()->make("TSF\\Http\\HttpResponse");
        $post_data = $request->postData("data");
        $controller = strstr(get_class(),"Controller");
        if (is_array($post_data)) {
            foreach (App::facade("TSF\\Core\\Config")->get("module.$controller") as $item) {

            }
        }
    }
}