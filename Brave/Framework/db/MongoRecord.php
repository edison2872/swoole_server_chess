<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/7/25
 * Time: 21:14
 */

namespace App\Framework\db;


use App\Framework\Facade\MongoDBName;
use MongoDB\BSON\ObjectID;
use MongoDB\Client;
use TSF\Core\Server;
use TSF\Facade\Config;

abstract class MongoRecord extends BaseActiveRecord
{
    public static function findOne($condition)
    {
        // TODO: Implement findOne() method.
        $object = Server::$container->make(get_called_class(), [get_called_class()]);
        $conn = self::getMongoReadConn($object);
        $collection = $conn->selectCollection($object->getDBName(), $object->getCollectionName());
        if (isset($condition["_id"])) {
            if (!is_object($condition["_id"])) {
                $condition["_id"] = new ObjectID($condition["_id"]);
            }
        }
        // var_dump($conn);
        $res = $collection->findOne($condition);
        // var_dump($res);
        return $object;
    }

    /**
     * 给不确定字段的用
     * @param $condition
     */
    public static function findOneAsRecord($condition)
    {

    }

    /**
     * @return Client
     */
    private static function getMongoReadConn()
    {
        $connConfigs = Config::facade()->get('mongopool.conns', []);
        $read = $connConfigs['read'];
        echo "get ReadConn" . PHP_EOL;
        retry:
        try {
            $conn = new Client($read['uri'], $read['options']);
            $cursor = $conn->selectDatabase('test')->command(["ping" => 1]);
        } catch (\Exception $e) {
            echo "get exception" . PHP_EOL;
            goto retry;
        } finally {
            echo "finally" . PHP_EOL;
        }
        echo "get ReadConn end " . PHP_EOL;
        var_dump($cursor->toArray()[0]);
        return $conn;
    }

    /**
     * @return string
     */
    private function getDBName()
    {
        return MongoDBName::facade()->getMongoDBName();
    }

    protected function saveUpdate()
    {
        // TODO: Implement saveUpdate() method.
    }

    protected function saveInsert()
    {
        // TODO: Implement saveInsert() method.
    }

    abstract function getCollectionName();

}