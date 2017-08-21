<?php
/**
 * @Author: winterswang
 * @Date:   2016-09-13 09:55:44
 * @Last Modified by:   winterswang
 * @Last Modified time: 2016-09-13 17:10:37
 */
namespace TSF\Contract;

abstract class Request
{
	protected $fd;
    protected $serverInfo;
    protected $remoteInfo;

    public function __get($name)
    {
        return isset($this->$name) ? $this->$name : null;
    }
}
