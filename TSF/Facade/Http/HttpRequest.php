<?php
/**
 * @Author: winterswang
 * @Date:   2016-11-10 14:10:03
 * @Last Modified by:   winterswang
 * @Last Modified time: 2016-11-10 14:10:23
 */
namespace TSF\Facade\Http;

use TSF\Contract\Facade;

class HttpRequest extends Facade
{
    static protected function getFacadeAccessor()
    {
        return 'TSF\Http\HttpRequest';
    }
}

?>