<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/10/26
 * Time: 12:19
 */
namespace TSF\WebSocket;

use TSF\Contract\ExceptionHandler;
use TSF\Facade\WebSocket\WSResponse as Response;
use Exception;

class WebSocketExceptionHandler implements ExceptionHandler
{
    public function render($request, Exception $e)
    {
    	$rsp = Response::facade()->status($e->getCode());
    	if ($rsp) {
    		$rsp->msg($e->getMessage());
    	}
    }
}