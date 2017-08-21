<?php

/**
 * Created by PhpStorm.
 * User: alvinzhu
 * Date: 2016/12/12
 * Time: 19:50
 */
namespace TSF\UDP;

use TSF\Facade\Config;
use TSF\Contract\Kernel\Datagram;

class Kernel extends Datagram
{
    public function onWorkerStart($server, $workerId)
    {
        parent::onWorkerStart($server, $workerId);
        \TSF\Core\Log::init(Config::facade()->get('app.log'));
    }
}