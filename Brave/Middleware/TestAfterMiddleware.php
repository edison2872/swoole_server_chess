<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/10/25
 * Time: 22:16
 */
namespace App\Middleware;

use TSF\Contract\Http\Middleware;
use TSF\Contract\Request;

class TestAfterMiddleware implements Middleware
{
    public function handle(Request $request)
    {
        echo "after middleware test\n";
    }
}
