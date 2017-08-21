<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/5/18
 * Time: 17:17
 */
namespace TSF\Facade\WebSocket;

use TSF\Contract\Facade;

class Route extends Facade
{
    static protected function getFacadeAccessor()
    {
        return 'TSF\WebSocket\Route';
    }
}