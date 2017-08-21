<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/7/17
 * Time: 16:48
 */

namespace App\Framework\db;


use App\Framework\Exception\CodeException;
use Swoole\Mysql\Exception;
use TSF\Core\Log;
use TSF\Core\Server;
use TSF\Pool\MySQL\MySQLLocal;

abstract class MySQLRecord extends BaseActiveRecord
{
    /**
     * @param mixed $condition primary key value or a set of column values
     * @return static ActiveRecord instance matching the condition, or `null` if nothing matches.
     * @throws \Exception
     */
    public static function findOne($condition)
    {
        $object = Server::$container->make(get_called_class(), [get_called_class()]);
        $attributes = $object->attributes();
        $sqlStr = "SELECT `" . implode("`,`", $attributes) . "` FROM `" . $object->getTableName() . "`";
        if (is_array($condition) && count($condition) > 0) {
            foreach ($condition as $cKey => $cValue) {
                $conditionArr[] = "`" . $cKey . "` = " . $cValue;
            }
            $conditionStr = implode(" AND ", $conditionArr);
        } else if (is_string($condition)) {
            $conditionStr = $condition;
        } else {
            throw new \Exception(get_called_class() . " The arguments must be arrays or string");
        }
        $sqlStr = $sqlStr . " WHERE " . $conditionStr;

        $db = MySQLLocal::fetch('read');
        $db->query($sqlStr);
        $res = $db->recv();
        $res = current($res);
        MySQLLocal::recycle($db);
        if (empty($res)) {
            throw new \Exception("Model does not exist!", CodeException::ModelNotExist);
        }
        $object->load($res, '');
        return $object;
    }

    /**
     * @param mixed $condition primary key value or a set of column values
     * @return array an array of ActiveRecord instance, or an empty array if nothing matches.
     */
    public static function findAll($condition)
    {
        // TODO: Implement findAll() method.
    }

    /**
     * Deletes the record from the database.
     *
     * @return integer|boolean the number of rows deleted, or `false` if the deletion is unsuccessful for some reason.
     * Note that it is possible that the number of rows deleted is 0, even though the deletion execution is successful.
     */
    public function delete()
    {
        // TODO: Implement delete() method.
    }


    protected function saveUpdate()
    {
        // TODO: Implement saveUpdate() method.
        $data = $this->getAttributesChanged();
        if (empty($data)) {
            return false;
        }

        $values = array();
        foreach ($data as $k => $v) {
            $values [] = str_ireplace("'NOW()'", "NOW()", "`" . $k . "` = '" . addslashes($v) . "'");  // 这边对数字和字符串，会不会有影响
        }
        $sqlStr = "UPDATE `{$this->getTableName()}` SET " . implode(",", $values);

        $updateConditionArr = $this->getUpdateRecordCondition();
        if (count($updateConditionArr['updateCondition']) < 1 && empty($updateConditionArr['updateConditionStr'])) {
            throw new Exception('update Table <' . $this->getTableName() . '> need update condition');
        }

        $conditionStr = "";
        if (count($updateConditionArr['updateCondition']) > 0) {
            $conditionArr = [];
            foreach ($updateConditionArr['updateCondition'] as $UpItem) {
                if (in_array($UpItem, array_keys($data))) {
                    throw new \Exception(__CLASS__ . " can not change <" . $UpItem . ">", CodeException::MySQLUpdateError);
                }
                $conditionArr[] = "`" . $UpItem . "` = '" . $this->$UpItem . "'";
            }
            $conditionStr = implode(" AND ", $conditionArr);
        }

        if (!empty($updateConditionArr['updateConditionStr'])) {
            if (empty($conditionStr)) {
                $conditionStr = $updateConditionArr['updateConditionStr'];
            } else {
                $conditionStr = $conditionStr . " AND " . $updateConditionArr['updateConditionStr'];
            }
        }
        $sqlStr = $sqlStr . " WHERE " . $conditionStr;

        Log::info($sqlStr);
        $db = MySQLLocal::fetch('write');
        $db->query($sqlStr);
        $res = $db->recv();
        if (!$res) {
            throw new \Exception($db->error, CodeException::MySQLUpdateError);
        }
        $affectedRows = $db->affected_rows;
        MySQLLocal::recycle($db);
        return $affectedRows;
    }

    protected function saveInsert()
    {
        // TODO: Implement saveInsert() method.
        $attributes = $this->attributes();

        $data = [];
        foreach ($attributes as $attr) {
            if (isset($this->$attr)) {
                $data[$attr] = $this->$attr;
            }
        }

        if (empty($data)) {
            return false;
        }
        $fields = $keys = array();
        foreach ($data as $k => $v) {
            $fields[] = ($v === "NOW()") ? "NOW()" : ("'" . $v . "'");
            $keys[] = $k;
        }
        $values = "(" . implode(",", $fields) . ")";
        $column = "(`" . implode("`,`", $keys) . "`)";
        $sqlStr = "INSERT INTO `{$this->getTableName()}` {$column} VALUES {$values}";

        Log::info($sqlStr);

        $db = MySQLLocal::fetch('write');
        $db->query($sqlStr);
        $res = $db->recv();
        if (!$res) {
            throw new \Exception($db->error, CodeException::MySQLInsertError);
        }
        $insertId = $db->insert_id;
        MySQLLocal::recycle($db);
        return $insertId;
    }


    /**
     * model对应的表名
     * @return mixed
     */
    abstract protected function getTableName();

    /**
     *  [
     * 'updateCondition' => ['attributeA', 'attributeB', 'attributeC'],  ---------相关的字段名
     * 'updateConditionStr' => " 1=1 AND 2=2"  ----------额外的条件
     * ]
     * @return array
     */
    abstract protected function getUpdateRecordCondition();

}