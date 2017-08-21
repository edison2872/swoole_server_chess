<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/7/17
 * Time: 16:12
 */

namespace App\Framework\db;


use App\Framework\Base\Model;
use App\Framework\Exception\NotSupportedException;
use App\Framework\Exception\UnknownPropertyException;

abstract class BaseActiveRecord extends Model implements ActiveRecordInterface
{
    // 记录变化标签
    private $AttributesChanged = [];

    public static function updateAll($attributes, $condition = '')
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported.');
    }

    public static function deleteAll($condition = '', $params = [])
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported.');
    }

    /**
     * 修改单个标签值，用于更新
     * @param $name
     * @param $value
     * @throws UnknownPropertyException
     */
    public function setAttribute($name, $value)
    {
        if ($this->hasAttribute($name)) {
            $this->AttributesChanged[$name] = $value;
            $this->$name = $value;
        } else {
            throw new UnknownPropertyException(__CLASS__ . ":" .$name . ' is not exist');
        }
    }

    public function getAttributesChanged()
    {
        return $this->AttributesChanged;
    }

    public function update()
    {
        if (!$this->validate()) {
            return false;
        }
        return $this->saveUpdate();
    }

    public function insert()
    {
        if (!$this->validate()) {
            return false;
        }
        return $this->saveInsert();
    }

    abstract protected function saveUpdate();

    abstract protected function saveInsert();

}