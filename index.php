<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/8/2 0002
 * Time: 8:51
 */
namespace model;
error_reporting(-1);
global $config,$msgData,$_echo, $stime, $host,$menu, $API_KEY, $Robot, $msg, $type, $qq, $guid; //定义全局变量

require './model/ZhConvertService.php';
require './function.php';
require "./model/goHttp.php";

$config = include("./config/Config.php");
$menu = include("./config/Menu.php");
$Robot = goHttp::get_login_info();
if(key_exists("debug",$config)&&$config["debug"]){
    $Robot = array("user_id"=>$config["Robot"],"nickname"=>"测试");
    $content = $config["debugInfoJson"];
}else{
    if(!empty($Robot)){
        $Robot = $Robot["data"];
    }else{
        echo "您的go-http没有启动或者配置异常";
        die();
    }
    $content = file_get_contents('php://input', 'r');
    if(!empty($content)){
        file_put_contents("./log/all_qq.log", ''.$content . "\r\n", FILE_APPEND);
    }
}

$msgData = json_decode($content,true);
$msgData = is_array($msgData)?$msgData:array();
$msg = key_exists("message",$msgData)?ZhConvertService::zh_auto($msgData["message"],"zh-hant"):"";
$qq = key_exists("user_id",$msgData)?$msgData["user_id"]:"";
$guid = key_exists("group_id",$msgData)?$msgData["group_id"]:"";
$type = key_exists("message_type",$msgData)?$msgData["message_type"]:"";
$API_KEY = key_exists("API_KEY",$config)?$config["API_KEY"]:"";

$groupBool = false;
//群聊验证
if($type=="group"){
    $groupBool = true;
    if(!in_array($guid,$config["group_guid"])){
        return false;
    }
}
if(!empty($guid)&&!in_array($guid,$config["group_guid"])){
    return false;
}

if(!empty($menu)&&is_array($menu)){
    foreach ($menu as $row){
        if(key_exists("groupBool",$row)&&$row["groupBool"]&&!$groupBool){
            continue;//必须是群聊
        }
        //群验证（group=array())
        $groupList = key_exists("group",$row)?$row["group"]:$config["group_guid"];
        if(!in_array($guid,$groupList)){
            continue;//每条菜单限制了群号
        }
        $row['groupBool'] = $groupBool;
        if(key_exists("className",$row)&&!empty($row["className"])){
            $className = $row["className"];
            $bool = myAutoload($className);
            if($bool){
                $className="\\model\\$className";
                $model = new $className($row);
                if(key_exists("fun",$row)&&!empty($row["fun"])){
                    $funName = $row["fun"];
                    $model->$funName();
                }else{
                    $model->init();
                }
            }
        }else{
            $funName = $row["fun"];
            $funName($row);
        }
        if(!empty($GLOBALS["_echo"])){
            sendAllMsg();
            die();
        }
    }
}

if($config["web"]){ //开启网页
    require './web/index.php';
}