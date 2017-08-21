<?php
/**
 * Created by PhpStorm.
 * User: edison
 * Date: 2017/7/11
 * Time: 上午10:33
 */

namespace App\Admin;


use App\Model\AnnouncementModel;
use App\Model\ServerModel;

class announcementAdmin
{
    public function sendAnnouncement(int $id)
    {
        $announcement_info = AnnouncementModel::getAnnouncement($id);
        if(empty($announcement_info)){
            return ["result" => false];
        }
        $server_list = [];
        if($announcement_info["server"] == 0){
            $server_list = array_keys(ServerModel::getServerList());
        } else {
            $server_list[] = $announcement_info["server"];
        }

//        $msg = array("m" => "world", "ac" => "bulletin", "result" => ["errorCode" => 0],
//            "data" =>[
//                "Bulletin" => $announcement_info["ment"], "id" => $id, "type" => $announcement_info["type"],
//                "level" => $announcement_info["level"], "interval" => $announcement_info["interval"],
//                "loop" => $announcement_info["loop"],"buTime" => $announcement_info["buTime"],
//                "displaytime" => 0,
//            ]);
//        foreach ($server_list as $value){
//            C("serverid",$value);
//            common::sendMission($msg, "sendBulletin");
//            log::info("send msg to server $value,msg:".json_encode($msg));
//        }
        return ["result" => true];
    }

    public function getAnnouncement(int $id)
    {
        return AnnouncementModel::getAnnouncement($id);
    }
}