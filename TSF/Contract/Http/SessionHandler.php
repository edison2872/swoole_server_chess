<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/8/20
 * Time: 12:36
 */
namespace TSF\Contract\Http;

interface SessionHandler
{
    public function open();
    public function close();
    public function read($sessionId);
    public function write($sessionId, $data, $timeout);
    public function gc();
}