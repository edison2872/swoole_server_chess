<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/10/26
 * Time: 12:19
 */
namespace TSF\Http;

use TSF\Contract\ExceptionHandler;
use TSF\Facade\Http\HttpResponse as Response;
use Exception;

class HttpExceptionHandler implements ExceptionHandler
{
    public function render($request, Exception $e)
    {
    	$rsp = Response::facade()->status(500);
    	if ($rsp) {
    		$rsp->body($e->getMessage());
    	}
    }
}