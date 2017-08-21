<?php
namespace TSF\Facade\WebSocket;

use TSF\Contract\Facade;

class WSRequest extends Facade
{
    static protected function getFacadeAccessor()
    {
        return 'TSF\WebSocket\WSRequest';
    }
}
