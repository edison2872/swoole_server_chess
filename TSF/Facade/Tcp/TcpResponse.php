<?php
namespace TSF\Facade\Tcp;

use TSF\Contract\Facade;

class TcpResponse extends Facade
{
    static protected function getFacadeAccessor()
    {
        return 'TSF\Tcp\TcpResponse';
    }

}
