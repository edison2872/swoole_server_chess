<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/10/14
 * Time: 19:51
 */
namespace TSF\Facade;

use TSF\Contract\Facade;

class App extends Facade
{
    static protected function getFacadeAccessor()
    {
        return 'App';
    }
}
