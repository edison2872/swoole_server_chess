<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/7/17
 * Time: 16:13
 */

namespace App\Framework\db;


interface ActiveRecordInterface
{
    /**
    * @param mixed $condition primary key value or a set of column values
    * @return static ActiveRecord instance matching the condition, or `null` if nothing matches.
    */
    public static function findOne($condition);

    /**
    * @param mixed $condition primary key value or a set of column values
    * @return array an array of ActiveRecord instance, or an empty array if nothing matches.
    */
    public static function findAll($condition);

    /**
     * Updates records using the provided attribute values and conditions.
     * For example, to change the status to be 1 for all customers whose status is 2:
     *
     * ```php
     * Customer::updateAll(['status' => 1], ['status' => '2']);
     * ```
     *
     * @param array $attributes attribute values (name-value pairs) to be saved for the record.
     * Unlike [[update()]] these are not going to be validated.
     * @param array $condition the condition that matches the records that should get updated.
     * Please refer to [[QueryInterface::where()]] on how to specify this parameter.
     * An empty condition will match all records.
     * @return integer the number of rows updated
     */
    public static function updateAll($attributes, $condition = null);

    /**
     * Deletes records using the provided conditions.
     * WARNING: If you do not specify any condition, this method will delete ALL rows in the table.
     *
     * For example, to delete all customers whose status is 3:
     *
     * ```php
     * Customer::deleteAll([status = 3]);
     * ```
     *
     * @param array $condition the condition that matches the records that should get deleted.
     * Please refer to [[QueryInterface::where()]] on how to specify this parameter.
     * An empty condition will match all records.
     * @return integer the number of rows deleted
     */
    public static function deleteAll($condition = null);

    /**
     * @return boolean whether the attributes are valid and the record is inserted successfully.
     */
    public function insert();

    /**
     * @return integer|boolean the number of rows affected, or `false` if validation fails
     * or updating process is stopped for other reasons.
     * Note that it is possible that the number of rows affected is 0, even though the
     * update execution is successful.
     */
    public function update();

    /**
     * Deletes the record from the database.
     *
     * @return integer|boolean the number of rows deleted, or `false` if the deletion is unsuccessful for some reason.
     * Note that it is possible that the number of rows deleted is 0, even though the deletion execution is successful.
     */
    public function delete();

    /**
     * 验证model的数据合理性
     * @return bool
     * @throws ValidateWrongException
     */
    public function validate();

}