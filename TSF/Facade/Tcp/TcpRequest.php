<?php
namespace TSF\Facade\Tcp;

use TSF\Contract\Facade;

class TcpRequest extends Facade
{
    static protected function getFacadeAccessor()
    {
        return 'TSF\Tcp\TcpRequest';
    }
}
