<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/10/13
 * Time: 21:38
 */
namespace TSF\Contract;

use TSF\Exception\Contract\FacadeException;
use TSF\Core\Server as App;

abstract class Facade
{
    static protected function getFacadeAccessor()
    {
        throw new FacadeException('Facade does not implement getFacadeAccessor method.');
    }

    /**
     * 所有变量都放到Server里的静态变量($container)容器中
     * @return object
     */
    static public function facade()
    {
        return App::$container->make(static::getFacadeAccessor());
    }
}
