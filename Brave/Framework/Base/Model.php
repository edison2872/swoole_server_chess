<?php
/**
 * Created by PhpStorm.
 * User: edison
 * Date: 2017/6/8
 * Time: 下午6:04
 */

namespace App\Framework\Base;

use App\Framework\Driver\mysql;
use App\Framework\Facade\Connection;
class Model
{
    use ArrayableTrait;

    private $insert_id = 0;
    /**
     * @return string 当前类名字、或者
     */
    public function formName()
    {
        $reflector = new \ReflectionClass($this);
        return $reflector->getShortName();
    }

    public function load($data, $formName = null)
    {
        $scope = $formName === null ? $this->formName() : $formName;
        if ($scope === '' && !empty($data)) {
            $this->setAttributes($data);

            return true;
        } elseif (isset($data[$scope])) {
            $this->setAttributes($data[$scope]);

            return true;
        } else {
            return false;
        }
    }

    public function setAttributes($values)
    {
        if (is_array($values)) {
            $attributes = array_flip($this->attributes());
            foreach ($values as $name => $value) {
                if (isset($attributes[$name])) {
                    $this->$name = $value;
                }
            }
        }
    }

    public function attributes()
    {
        $class = new \ReflectionClass($this);
        $names = [];
        foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if (!$property->isStatic()) {
                $names[] = $property->getName();
            }
        }
        return $names;
    }

    public function hasAttribute($name)
    {
        return in_array($name, $this->attributes(), true);
    }


    protected $user_id = null;
    protected $role_id = null;
    protected $server_id = 0;

    public function __construct(int $server_id, int $user_id, string $role_id)
    {
        $this->server_id = $server_id;
        $this->user_id = $user_id;
        $this->role_id = $role_id;
    }

    protected static function mysql_fetch_one(string $sql)
    {
        $result = self::mysql_fetch_all($sql);
        return isset($result[0]) ? $result[0] : (is_bool($result) ? $result : []);
    }

    protected static function mysql_fetch_all(string $sql)
    {
        $mysql_connection = new mysql("read");
        $result = $mysql_connection->query($sql);
        var_dump($result);
//        $this->insert_id = $mysql_connection->getLastInsertId();
        $mysql_connection->__destruct();
//        $result = Connection::facade()->getMysqlReadInstance()->query($sql);
        return $result;
    }

    protected static function getInsertLastId()
    {
//        return $this->insert_id;
//        return Connection::facade()->getMysqlReadInstance()->getLastInsertId();
    }

    protected static function execReadRedisCommand(string $command, array $param)
    {
        return Connection::facade()->getRedisPublicInstance()->$command($param[0]);
//        return call_user_func_array([Connection::facade()->getRedisPublicInstance(),$command],$param);
    }

}