<?php
namespace TSF\Facade\WebSocket;

use TSF\Contract\Facade;

class WSResponse extends Facade
{
    static protected function getFacadeAccessor()
    {
        return 'TSF\WebSocket\WSResponse';
    }

}
