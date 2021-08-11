<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/8/2 0002
 * Time: 9:46
 */

namespace model;

class deleteMsg{

    private $keyWord="";

    private $menuRow;
    private $deleteQQ="";

    public function __construct($row=array()){
        $this->menuRow = $row;
    }

    public function init(){
        $msg = key_exists("msg",$this->menuRow)?$this->menuRow["msg"]:"";
        $bool = in_array($GLOBALS['qq'],$GLOBALS['config']["admin"]);//需要管理员权限
        if($bool&&!empty($msg)&&strpos($GLOBALS['msg'], $msg) === 0){ //检测到需要设置开关
            $mangerList = \model\goHttp::get_group_member_info($GLOBALS["Robot"]["user_id"]);
            if(is_array($mangerList)&&!empty($mangerList["data"])){
                if(in_array($mangerList["data"]["role"],array("owner","admin"))) { //机器人必须是管理员权限
                    $GLOBALS['msg'] = goHttp::getGroupOnlyForName($GLOBALS['msg']);
                    $this->keyWord = getSubstr($GLOBALS['msg'], $msg);
                    $this->setDeleteLog();
                }else{
                    $GLOBALS["_echo"] = "我还不是管理员，无法设置";
                }
            }
        }
    }

    //管理员设置"撤回开关"
    private function setDeleteLog(){
        $numberList = getAllNumber($GLOBALS['msg']);
        $qq = $numberList[0];
        $deleteNum = key_exists(1,$numberList)?$numberList[1]:1;
        $deleteNum = $deleteNum<1?1:intval($deleteNum);
        $deleteNum = $deleteNum>10?10:$deleteNum;
        if(strpos($GLOBALS['msg'],"上".$deleteNum) !== false){ //撤回上n条消息
            $this->deletePrevMsg($qq,$deleteNum);
        }elseif (strpos($GLOBALS['msg'],"第".$deleteNum) !== false){//撤回第n条消息
            $this->deleteNumberMsg($qq,$deleteNum);
        }elseif (strpos($GLOBALS['msg'],"下".$deleteNum) !== false){//撤回下n条消息
            $this->timeNextMsg($qq,$deleteNum);
        }
    }

    //撤回第n条消息
    private function deleteNumberMsg($qq,$num){
        $msg_this = goHttp::get_msg($GLOBALS['msgData']['message_id']);
        $msgList = goHttp::get_group_msg_history($msg_this["data"]['real_id']);
        if(is_array($msgList)&&!empty($msgList["data"])){
            $list = $msgList["data"]["messages"];
            for($i = count($list)-1;$i>=0;$i--){ //倒着循环
                $msgRow = $list[$i];
                if($num>0&&$msgRow["group_id"]==$GLOBALS['guid']&&$qq==$msgRow["sender"]["user_id"]){
                    if($num==1){
                        goHttp::recallMsg($msgRow["message_id"]);
                    }
                    $num--;
                }else{
                    $GLOBALS['msgData']['message_id'] = $msgRow["message_id"];
                }
            }
            if($num>0){
                $this->deletePrevMsg($qq,$num);
            }
        }
    }

    //撤回上n条消息
    private function deletePrevMsg($qq,$num){
        $msg_this = goHttp::get_msg($GLOBALS['msgData']['message_id']);
        $msgList = goHttp::get_group_msg_history($msg_this["data"]['real_id']);
        if(is_array($msgList)&&!empty($msgList["data"])){
            $list = $msgList["data"]["messages"];
            for($i = count($list)-1;$i>=0;$i--){ //倒着循环
                $msgRow = $list[$i];
                if($num>0&&$msgRow["group_id"]==$GLOBALS['guid']&&$qq==$msgRow["sender"]["user_id"]){
                    goHttp::recallMsg($msgRow["message_id"]);
                    $num--;
                }else{
                    $GLOBALS['msgData']['message_id'] = $msgRow["message_id"];
                }
            }
            if($num>0){
                $this->deletePrevMsg($qq,$num);
            }
        }
    }

    //定时撤回下n条消息
    private function timeNextMsg($qq,$num){
        if($qq != $GLOBALS['Robot']["user_id"]){
            $file="./data/{$GLOBALS['guid']}_{$qq}_recall.php";
            $array=array(
                "qq"=>$qq,
                "num"=>$num
            );
            $text='<?php return '.var_export($array,true).';';
            if(false!==fopen($file,'w+')){
                file_put_contents($file,$text);
                $GLOBALS["_echo"] = "机器人随时准备撤回{$qq}的下{$num}消息";
                //goHttp::send_group_msg("机器人随时准备撤回{$qq}的下{$num}消息");
            }else{
                goHttp::send_group_msg("出問題了~~文件不存在");
            }
        }else{
            goHttp::send_group_msg("不允许撤回机器人的消息o(╥﹏╥)o");
        }
    }

    //撤回下n条消息
    public function deleteNextMsg(){
        $file="./data/{$GLOBALS['guid']}_{$GLOBALS['qq']}_recall.php";
        if(file_exists($file)){
            $list = include($file);
            $num = 0;
            if(is_array($list)&&key_exists("num",$list)){
                $num = $list["num"];
            }
            if($num>0){
                goHttp::recallMsg($GLOBALS['msgData']["message_id"]);
                $num--;
                $array=array(
                    "qq"=>$GLOBALS['qq'],
                    "num"=>$num
                );
                $text='<?php return '.var_export($array,true).';';
                file_put_contents($file,$text);
                die();
                //$GLOBALS["_echo"] = "{$GLOBALS['qq']}的消息已撤回，还剩{$num}条消息";
            }
            if($num<=0){
                unlink($file);
            }
        }
    }

}