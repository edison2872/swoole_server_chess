<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/8/20
 * Time: 12:15
 */
namespace App\Http\Controller;

use TSF\Pool\MySQL\MySQLRemote as MySQLPool;
// use TSF\Pool\MySQL\MySQLLocal as MySQLPool;
use TSF\Facade\Config;
use TSF\Facade\Http\Session;
use TSF\Facade\Http\HttpResponse;
use TSF\Core\Log;

class HomeController
{
    public function index($id)
    {
        //get things from session
        var_dump(Session::facade()->get('testsession'));

        //set thing to session
        Session::facade()->set('testsession', 'testvalue');

        //get config
        var_dump(Config::facade()->get('app.debug', true));

        try {
            $db = MySQLPool::fetch('test');
            $res = $db->query('select version()');
            var_dump($res);
            MySQLPool::recycle($db);
        } catch (\Exception $e) {
            echo "catch exception: " . $e->getMessage() . "\n";
        }

        //make a log
        Log::error('There is an error.', __LINE__, __FUNCTION__, __CLASS__);
        Log::info('From HomeController Message');

        //render view
        HttpResponse::facade()->view('Home.index', ['content' => 'Hello TSF!']);
    }

    public function getSay($who = 'nobody')
    {
        HttpResponse::facade()->json(['content' => "{$who} Hello TSF!"]);
    }
}