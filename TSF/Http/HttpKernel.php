<?php

/**
 * Created by PhpStorm.
 * User: alvinzhu
 * Date: 2016/8/18
 * Time: 17:29
 */
namespace TSF\Http;

use TSF\Contract\Kernel\Http as HttpContract;
use TSF\Core\Server;
use TSF\Http\Session\Store as Session;
use TSF\Component\Redis\RedisHandler;
use TSF\Http\Session\RedisSessionHandler;
use TSF\Facade\App;
use TSF\Facade\Http\Route;
use TSF\Facade\Config;
use App\Framework\Facade\Connection;

class HttpKernel extends HttpContract
{
    public function onWorkerStart($server, $workerId)
    {
        $route = new \TSF\Http\Route();
        \TSF\Core\Log::init(Config::facade()->get('app.log'));
        App::facade()->globalSingleton('TSF\Http\Route', $route);
        App::facade()->singleton("App\\Framework\\Facade\\Connection","App\\Framework\\Facade\\Connection");
        Route::facade()->loadConfig();
        Connection::setProtocol(Server::SERVER_TYPE_HTTP);
//        App::facade()->singleton("App\\Framework\\Connection", "App\\Framework\\Facade\\Connection");

//        $blade = new Blade(Config::facade()->get('view.paths'), Config::facade()->get('view.compiled'));
//        App::facade()->globalSingleton('Philo\Blade\Blade', $blade);
    }

    public function onRequest($request, $response)
    {
        $start_time_stamp = (double)microtime(true);
        $request = new HttpRequest($request);
        $response = new HttpResponse($response);
        App::facade()->singleton('TSF\Http\HttpRequest', $request);
        App::facade()->singleton('TSF\Http\HttpResponse', $response);
        if (Config::facade()->get('session.enable', true)) {
            $session = new Session(new RedisSessionHandler(Config::facade()->get('redis')), Config::facade()->get('session'));
            $session->start($request, $response);
        }
        App::facade()->singleton('TSF\Http\Session\Store', $session);

        try {
            Route::facade()->dispatch($request);
        } catch (\Exception $e) {
            App::facade()->make('TSF\Http\HttpExceptionHandler')->render($request, $e);
        }

        if (Config::facade()->get('session.enable', true)) {
            $session->stop();
        }
        $response->send();
        $end_time_stamp = (double)microtime(true);
        $request_time = $end_time_stamp - $start_time_stamp;
        echo "-----------request-time-----------\n{$request_time}s.\n";
        App::facade()->clearCurrentSingleton();
    }
}
