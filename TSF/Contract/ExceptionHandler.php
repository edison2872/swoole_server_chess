<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/10/26
 * Time: 11:46
 */
namespace TSF\Contract;

use Exception;

interface ExceptionHandler
{
    public function render($request, Exception $e);
}