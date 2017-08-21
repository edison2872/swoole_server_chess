<?
/**
 * @Author: winterswang
 * @Date:   2016-11-07 17:33:19
 * @Last Modified by:   winterswang
 * @Last Modified time: 2016-11-07 20:26:14
 */
namespace TSF\Facade;

use TSF\Contract\Facade;

class Redis extends Facade
{
    static protected function getFacadeAccessor()
    {
        return 'TSF\Component\Redis\RedisHandler';
    }
}
?>