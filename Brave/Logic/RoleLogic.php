<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/8/1
 * Time: 14:42
 */
namespace App\Logic;

use App\Framework\db\MongoRecord;
use App\Framework\db\ValidateWrongException;

class RoleLogic extends MongoRecord
{

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

    /**
     * 验证model的数据合理性
     * @return bool
     * @throws ValidateWrongException
     */
    public function validate()
    {
        // TODO: Implement validate() method.
    }



    function getCollectionName()
    {
        // TODO: Implement getCollectionName() method.
        return "roles";
    }
}