<?php
/**
 * Created by PhpStorm.
 * User: hotpoint
 * Date: 2017/7/17
 * Time: 17:00
 */

namespace App\Model;

use App\Framework\db\MySQLRecord;
use App\Framework\Exception\ValidateWrongException;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator as v;

class Server extends MySQLRecord
{

    public $serverid;
    public $serverip;
    public $status;
    public $opentime;
    public $ip;
    public $port;
    public $serverv;
    public $remark;

    protected function getTableName()
    {
        // TODO: Implement getTableName() method.
        return 'serverlist';
    }



    /**
     * 验证model的数据合理性
     * @return bool
     * @throws ValidateWrongException
     */
    public function validate()
    {
        // TODO: Implement validate() method.
        $userValidator = v::attribute('port', v::numeric())
            ->attribute('opentime', v::date())
            ->attribute('status', v::intVal()->between(0, 2));
        try {
            return $userValidator->check($this);
        } catch (ValidationException $exception) {
            throw new ValidateWrongException(__CLASS__ . ": " . $exception->getMainMessage());
        }
    }

    /**
     *  [
     * 'updateCondition' => ['attributeA', 'attributeB', 'attributeC'],  ---------相关的字段名
     * 'updateConditionStr' => " 1=1 AND 2=2"  ----------额外的条件
     * ]
     * @return array
     */
    protected function getUpdateRecordCondition()
    {
        // TODO: Implement getUpdateRecordCondition() method.
        return ['updateCondition' => ["serverid"]];
    }
}