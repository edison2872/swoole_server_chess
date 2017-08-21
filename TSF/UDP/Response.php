<?php

/**
 * Created by PhpStorm.
 * User: alvinzhu
 * Date: 2016/12/12
 * Time: 20:03
 */
namespace TSF\UDP;

class Response extends \TSF\Contract\Response
{
    protected $server;
    protected $clientInfo;

    public function __construct($serv, $client)
    {
        $this->server = $serv;
        $this->clientInfo = $client;
    }

    public function send($content)
    {
        $this->server->sendto($this->clientInfo['address'], $this->clientInfo['port'], $content);
    }

    public function getClientInfo()
    {
        return $this->clientInfo;
    }
}