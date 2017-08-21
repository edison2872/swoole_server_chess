<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/8/1
 * Time: 15:02
 */

namespace App\Framework\Base;


use App\Framework\Exception\NotSupportedException;

class MongoDBName
{
    private $DBName = null;

    /**
     * @return string
     * @throws NotSupportedException
     */
    public function getMongoDBName()
    {
        if (is_null($this->DBName)) {
            throw new NotSupportedException("MongoDB`s name is null");
        }
        return $this->DBName;
    }

    public function setMongoDBName($DBName)
    {
        $this->DBName = $DBName;
    }

}