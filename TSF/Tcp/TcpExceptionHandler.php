<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/10/26
 * Time: 12:19
 */
namespace TSF\Tcp;

use TSF\Contract\ExceptionHandler;
use TSF\Facade\Tcp\TcpResponse as Response;
use Exception;

class TcpExceptionHandler implements ExceptionHandler
{
    public function render($request, Exception $e)
    {
    	$rsp = Response::facade()->status($e->getCode());
    	if ($rsp) {
    		$rsp->msg($e->getMessage());
    	}
    }
}