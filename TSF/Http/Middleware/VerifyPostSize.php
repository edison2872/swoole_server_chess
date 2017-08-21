<?php

namespace TSF\Http\Middleware;

use TSF\Exception\Http\PostTooLargeException;

class VerifyPostSize
{
    /**
     * Handle an incoming request.
     *
     * @param   $request
     * @return mixed
     *
     * @throws PostTooLargeException
     */
    public function handle($request)
    {
        if ($request->server('CONTENT_LENGTH') > $this->getPostMaxSize()) {
            throw new PostTooLargeException;
        }

        return true;
    }

    /**
     * Determine the server 'post_max_size' as bytes.
     *
     * @return int
     */
    protected function getPostMaxSize()
    {
        if (is_numeric($postMaxSize = ini_get('post_max_size'))) {
            return (int) $postMaxSize;
        }

        $metric = strtoupper(substr($postMaxSize, -1));

        switch ($metric) {
            case 'K':
                return (int) $postMaxSize * 1024;
            case 'M':
                return (int) $postMaxSize * 1048576;
            case 'G':
                return (int) $postMaxSize * 1073741824;
            default:
                return (int) $postMaxSize;
        }
    }
}
