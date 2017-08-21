<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/10/26
 * Time: 11:27
 */
namespace App\Http\Exception;

use TSF\Http\HttpExceptionHandler;
use Exception;

class Handler extends HttpExceptionHandler
{
    public function render($request, Exception $e)
    {
        parent::render($request, $e);
    }
}