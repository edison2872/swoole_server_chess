<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/7/17
 * Time: 15:29
 */

namespace App\Framework\Base;


trait ArrayableTrait
{
    public function fields()
    {
        $fields = array_keys(get_object_vars($this));
        return array_combine($fields, $fields);
    }


}