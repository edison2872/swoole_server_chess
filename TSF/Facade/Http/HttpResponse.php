<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/10/25
 * Time: 21:11
 */
namespace TSF\Facade\Http;

use TSF\Contract\Facade;

class HttpResponse extends Facade
{
    static protected function getFacadeAccessor()
    {
        return 'TSF\Http\HttpResponse';
    }
}
