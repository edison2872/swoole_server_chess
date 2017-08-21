<?php

/**
 * Created by PhpStorm.
 * User: alvinzhu
 * Date: 2016/12/29
 * Time: 21:54
 */
namespace TSF\Component;

class Base
{
    protected $conf;

    public function setConf($conf) {
        $this->conf = $conf;
    }

    public function getConf() {
        return $this->conf;
    }

    public function beforeServerStart($server){}
    public function onStart($server){}
    public function onStop($server){}
}