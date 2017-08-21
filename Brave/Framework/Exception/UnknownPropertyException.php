<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/7/17
 * Time: 16:32
 */

namespace App\Framework\Exception;


class UnknownPropertyException extends \Exception
{
    public function getName()
    {
        return 'Unknown Property';
    }
}