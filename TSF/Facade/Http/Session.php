<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/10/23
 * Time: 17:03
 */
namespace TSF\Facade\Http;

use TSF\Contract\Facade;

class Session extends Facade
{
    static protected function getFacadeAccessor()
    {
        return 'TSF\Http\Session\Store';
    }
}
