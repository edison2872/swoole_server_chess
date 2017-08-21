<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/7/25
 * Time: 16:35
 */

namespace App\Framework\Exception;


class ValidateWrongException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message, CodeException::ModelValidateError);
    }

}