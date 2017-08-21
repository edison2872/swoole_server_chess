<?php
namespace App\Model;
/**
 * Created by PhpStorm.
 * User: edison
 * Date: 2017/6/16
 * Time: 下午6:58
 */
class AnnouncementModel extends \App\Framework\Base\Model
{
    public static function getGameAnnouncementListByTime(int $start_time_stamp, int $end_time_stamp)
    {
        $sql = "SELECT `id`,`ment` AS `content` 
FROM announcement 
WHERE `starttime` <= '" . date("Y-m-d H:i:s", $start_time_stamp) . "' 
AND `endtime` >= '" . date("Y-m-d H:i:s", $end_time_stamp) . "' 
AND `status` = 1 AND `type` = 120";
        $announcement_list = parent::mysql_fetch_all($sql);
        return is_array($announcement_list) ? $announcement_list : [];
    }
    
    public static function getAnnouncement(int $id)
    {
        $result = parent::mysql_fetch_one("SELECT * FROM `announcement` WHERE `id` = $id");
        return is_array($result)&&!empty($result)?$result:null;
    }
}
