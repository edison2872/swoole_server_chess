<?php

namespace TSF\Http\Middleware;

//use Symfony\Component\HttpFoundation\Cookie;
use TSF\Contract\Encrypter;
use TSF\Exception\Http\Session\TokenMismatchException;

class VerifyCsrfToken implements Middleware
{

    const TOKEN_KEY = "XSRF-TOKEN";
    /**
     * The encrypter implementation.
     *
     */
    protected $encrypter;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [];

    /**
     * Create a new middleware instance.
     *
     * @param
     * @return void
     */
    public function __construct(Encrypter $encrypter)
    {
        $this->encrypter = $encrypter;
    }
    /**
     * Handle an incoming request.
     *
     * @param
     * @param
     * @return mixed
     *
     * @throws TokenMismatchException
     */
    public function handle($request)
    {
        if (
            $this->isReading($request) ||
            $this->tokensMatch($request) ||
            $this->shouldPassThrough($request)
            )
        {
            return $this->addCookieToResponse($request, $next($request));
        }
        throw new TokenMismatchException;
    }


    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     *
     * @param
     * @return bool
     */
    protected function shouldPassThrough($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }
            if ($request->is($except)) {
                return true;
            }
        }
        return false;
    }


    /**
     * Determine if the session and input CSRF tokens match.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function tokensMatch($request)
    {
        $sessionToken = $request->session()->token();
        $token = $request->input(self::TOKEN_KEY);
        // input为空，取post数据
        if(is_null($token)) {
            $token = $request->post(self::TOKEN_KEY) ;
        }

        if (! is_string($sessionToken) || ! is_string($token)) {
            return false;
        }

        return ($sessionToken === $token);
    }
    /**
     * Add the CSRF token to the response cookies.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function addCookieToResponse($request, $response)
    {
        $config = Config::get('app');
        $session = $config['session'];
        $response->headers->setCookie(
            new Cookie(
                self::TOKEN_KEY, $request->session()->token(), time() + 60 * $session['lifetime'],
                $session['path'], $session['domain'], $session['secure'], false
            )
        );
        return $response;
    }

    /**
     * Determine if the HTTP request uses a ‘read’ verb.
     *
     * @param
     * @return bool
     */
    protected function isReading($request)
    {
        return in_array($request->method(), ['HEAD', 'GET', 'OPTIONS']);
    }
}
