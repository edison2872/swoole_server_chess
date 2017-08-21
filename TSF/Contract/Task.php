<?php
/**
 * Created by PhpStorm.
 * User: alvinzhu
 * Date: 2016/12/30
 * Time: 16:36
 */

namespace TSF\Contract;


interface Task
{
    public function handle($cmd, $data);
}