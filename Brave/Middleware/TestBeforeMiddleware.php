<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/10/25
 * Time: 22:13
 */
namespace App\Middleware;

use TSF\Contract\Http\Middleware;
use TSF\Contract\Request;

class TestBeforeMiddleware implements Middleware
{
    public function handle(Request $request)
    {
        echo "before middleware test\n";
    }
}