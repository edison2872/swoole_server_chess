<?php
namespace App\Http;
use \TSF\Facade\Http\Route;

class routes {
    static public function loadRoutes()
    {
        //any,get,post,option等方法的调用，第二个参数为   "控制器@动作"   形式
        Route::facade()->get('//transcation',"SDKController@handle");
//        Route::facade()->any('/home/{id:\d+}', 'HomeController@index', [
//            'include' => [
//                'before' => ['TestBeforeMiddleware'],
//                'after'  => ['TestAfterMiddleware']
//            ]
//        ]);

        //第二个参数为某个控制器时，第一个参数必须为   "/home/{action:\w+}/用户自定义"   形式
        //Route::facade()->controller('/home/{action:\w+}/{who:\w+}', 'HomeController');

        //第二个参数为*时，第一个参数必须为   "/{controller:\w+}/{action:\w+}/用户自定义"   形式
        Route::facade()->controller('/{controller:\w+}/{action:\w+}/{env:\w+}', '*');
                Route::facade()->controller('/{controller:\w+}/{action:\w+}', '*', [
                    'include' => [
                        'before' => ['GameHttpVerify'],
        //        'after'  => ['TestAfterMiddleware']
                    ]
                ]);
        Route::facade()->post('//admin',"AdminController@handle",[
            'include' => [
                'before' => ['AdminHttpVerify']]]);
    }
}
