<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/5/22
 * Time: 14:30
 */
namespace App\Middleware;

use TSF\Contract\Http\Middleware;
use TSF\Contract\Request;

class conditionMiddleware implements Middleware
{
    public function handle(Request $request)
    {
        echo "condition middleware test\n";
    }
}
