<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/5/17
 * Time: 15:43
 */
return [
    'user' =>[
        'serverList' =>['param' => ['res_option'],"protocol" => "HTTP"],
        'loginToRoleList' => ['param' => ['channel' , 'serverid' , 'user_account' ,'loginParam'],"protocol" => "HTTP"],
        'PCLoginRegister' => ['param' => ['account','password','serverid'],"protocol" => "HTTP"],
        'randName' => ['param' => ['sex','userid','serverid'],"protocol" => "HTTP"],
    ],
];