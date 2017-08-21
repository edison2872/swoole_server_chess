<?php

namespace TSF\Contract\Http;

use TSF\Contract\Request;

interface Middleware
{
    /**
     * Handle an incoming request.
     * @param  $request
     * @return void
     */
    public function handle(Request $request);
}
