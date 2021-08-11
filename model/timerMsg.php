<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/8/2 0002
 * Time: 9:46
 */

namespace model;


class timerMsg
{
    private $menuRow=array();

    public function __construct($row=array()){
        $this->menuRow = $row;
    }

    public function init(){
    }

    //检查新群员 notice_type==group_increase
    public function newUser(){
        $msgData = $GLOBALS["msgData"];
        if(key_exists("notice_type",$msgData)&&$msgData["notice_type"]=="group_increase"){
            goHttp::send_group_msg("[CQ:at,qq={$msgData['user_id']}] 欢迎新的RBQ~");
        }
    }

    //检查群禁言 notice_type==group_ban
    public function stopSpeak(){
        $msgData = $GLOBALS["msgData"];
        if(key_exists("notice_type",$msgData)&&$msgData["notice_type"]=="group_ban"&&!empty($msgData['duration'])){
            if($msgData["operator_id"]==$GLOBALS['Robot']["user_id"]){
                //被机器人禁言了
                goHttp::send_group_msg("[CQ:at,qq={$msgData['user_id']}] 被机器人禁言{$msgData['duration']}秒");
            }else{
                //被其它管理员禁言了
                goHttp::send_group_msg("[CQ:at,qq={$msgData['user_id']}] 被{$msgData['operator_id']}禁言{$msgData['duration']}秒");
            }
        }
    }

    //检查群消息撤回 notice_type==group_recall
    public function recallMsg(){
        $msgData = $GLOBALS["msgData"];
        if(key_exists("notice_type",$msgData)&&$msgData["notice_type"]=="group_recall"){
            if($msgData["operator_id"]==$GLOBALS['Robot']["user_id"]){
                //被机器人撤回了
                goHttp::send_group_msg("[CQ:at,qq={$msgData['user_id']}] 猜猜机器人为什么要撤回你的消息");
            }
        }
    }

    //检查群消息撤回 honor_type talkative(龙王)、performer(群聊之火)、emotion(快乐源泉)
    public function honorChange(){
        $msgData = $GLOBALS["msgData"];
        if(key_exists("notice_type",$msgData)&&$msgData["notice_type"]=="notify"){
            $list = array("talkative"=>"龙王","performer"=>"群聊之火","emotion"=>"快乐源泉");
            if(key_exists("honor_type",$msgData)&&key_exists($msgData["honor_type"],$list)){
                goHttp::send_group_msg("[CQ:at,qq={$msgData['user_id']}] 恭喜你获得 {$list[$msgData['honor_type']]} 称号");
            }
        }
    }

    //相关查询
    public function relevance(){
        $file="./data/{$GLOBALS['guid']}_{$GLOBALS['qq']}_search.php";
        if(file_exists($file)) {
            $list = include($file);
            $msg = $GLOBALS["msg"];
            $msg = is_numeric($msg)?$msg:0;
            $msg--;
            if(key_exists($msg,$list)){
                $list = $list[$msg];
                $msg = str_replace($list["name"],$list["user_id"],$list["search"]);
                $GLOBALS["msg"] = $msg;
            }
            unlink($file);
        }
    }

    //多个查詢
    public function moreSearch(){
        $msg = $GLOBALS["msg"];
        $name = current(explode(" ",$msg));
        if($name == "查询"&&!empty($GLOBALS["guid"])){
            if(is_array($GLOBALS["menu"])){
                $userList = array();
                foreach ($GLOBALS["menu"] as $menuRow){
                    $bool = $menuRow!=$this->menuRow;//需要排除自己
                    $groupList = key_exists("group",$menuRow)?$menuRow["group"]:$GLOBALS["config"]["group_guid"];
                    if($bool&&in_array($GLOBALS["guid"],$groupList)&&strpos($menuRow['msg'], $name) !== false){
                        $userList[] = array(
                            "search"=>$msg,
                            "name"=>$name,
                            "self_id"=>$GLOBALS['qq'],
                            "user_id"=>$menuRow['msg'],
                            "group_id"=>$GLOBALS['guid']
                        );
                    }
                }
                if(count($userList)==1){ //查询一条相关的群员
                    $GLOBALS["msg"] = str_replace($name,$userList[0]["user_id"],$msg);
                }elseif(count($userList)>1){ //查询很多条相关的群员
                    $echo = "请选择你需要的查询：";
                    foreach ($userList as $key=>$userRow){
                        $echo.="\n".($key+1)."、".$userRow['user_id'];
                    }
                    $file="./data/{$GLOBALS['guid']}_{$GLOBALS['qq']}_search.php";
                    $text='<?php return '.var_export($userList,true).';';
                    if(false!==fopen($file,'w+')){
                        file_put_contents($file,$text);
                        goHttp::send_group_msg($echo);
                    }else{
                        goHttp::send_group_msg("出問題了~~文件不存在");
                    }
                    die();
                }
            }
        }
    }
}