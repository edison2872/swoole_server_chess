<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/8/1
 * Time: 15:06
 */

namespace App\Framework\Facade;


use TSF\Contract\Facade;

class MongoDBName extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'App\Framework\Facade\MongoDBName';
    }

}