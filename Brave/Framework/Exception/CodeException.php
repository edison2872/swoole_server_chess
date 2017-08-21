<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/7/25
 * Time: 16:38
 */

namespace App\Framework\Exception;

/**
 * 放系统级别的错误代码
 * Class CodeException
 * @package App\Framework\Exception
 */
class CodeException
{
    const ModelValidateError = 10001; // 插入、更新时验证失败
    const ModelNotExist = 10002;      // 这个条件下的模型不存在
    const MySQLInsertError = 10003;
    const MySQLUpdateError = 10004;

}