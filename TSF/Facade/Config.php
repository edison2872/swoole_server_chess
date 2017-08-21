<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/10/14
 * Time: 19:27
 */
namespace TSF\Facade;

use TSF\Contract\Facade;

class Config extends Facade
{
    static protected function getFacadeAccessor()
    {
        return 'TSF\Core\Config';
    }
}
