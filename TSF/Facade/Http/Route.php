<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/10/13
 * Time: 21:59
 */
namespace TSF\Facade\Http;

use TSF\Contract\Facade;

class Route extends Facade
{
    static protected function getFacadeAccessor()
    {
        return 'TSF\Http\Route';
    }
}