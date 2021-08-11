<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/8/2 0002
 * Time: 9:46
 */

namespace model;


class goHttp
{
    public function echoTest(){
        echo "test";
    }

    //發送群消息
    public static function send_group_msg($message,$auto_escape=false){
        $url = $GLOBALS["config"]["host"]."/send_group_msg?";
        $urlDate = array(
            "group_id"=>$GLOBALS['guid'],
            "auto_escape"=>$auto_escape,
            "message"=>$message
        );
        $url.=http_build_query($urlDate);
        curl_get($url);
    }

    //获取登录号信息
    public static function get_login_info(){
        $url = $GLOBALS["config"]["host"]."/get_login_info";
        $data = curl_get($url);
        return json_decode($data,true);
    }
    //获取群列表
    public static function get_group_list(){
        $url = $GLOBALS["config"]["host"]."/get_group_list";
        $data = curl_get($url);
        return json_decode($data,true);
    }
    //获取群成员信息
    public static function get_group_member_info($qq,$no_cache=false){
        $url = $GLOBALS["config"]["host"]."/get_group_list";
        $urlDate = array(
            "group_id"=>$GLOBALS['guid'],
            "user_id"=>$qq,
            "no_cache"=>$no_cache
        );
        $url.=http_build_query($urlDate);
        $data = curl_get($url);
        return json_decode($data,true);
    }

    //获取群成员列表
    public static function get_group_member_list(){
        $url = $GLOBALS["config"]["host"]."/get_group_member_list?";
        $urlDate = array(
            "group_id"=>$GLOBALS['guid']
        );
        $url.=http_build_query($urlDate);
        $data = curl_get($url);
        return json_decode($data,true);
    }

    //根据名片及昵称查群友
    public static function getGroupOnlyForName($str){
        $name = explode(" ",$str);
        $name = key_exists(1,$name)?$name[1]:2;
        if(is_numeric($name)){ //数字不需要查询
            return $str;
        }else{
            $groupAll = self::get_group_member_list();
            $userList = array();
            if (is_array($groupAll)&&!empty($groupAll["data"])){
                foreach ($groupAll["data"] as $row){
                    if(strpos($row["title"],$name) !== false||strpos($row["nickname"],$name) !== false||strpos($row["card"],$name) !== false) {
                        $userList[] = array(
                            "search"=>$str,
                            "name"=>$name,
                            "self_id"=>$GLOBALS['qq'],
                            "user_id"=>$row["user_id"],
                            "group_id"=>$GLOBALS['guid'],
                            "user_name"=>!empty($row["card"])?$row["card"]:$row["nickname"]
                        );
                    }
                }
            }
            if(empty($userList)){ //没有查询到相关的群员
                return $str;
            }elseif(count($userList)==1){ //查询一条相关的群员
                return str_replace($name,$userList[0]["user_id"],$str);
            }else{ //查询很多条相关的群员
                $echo = "请选择你需要查询的群员：";
                foreach ($userList as $key=>$userRow){
                    $echo.="\n".($key+1)."、{$userRow['user_name']}（{$userRow['user_id']}）";
                }
                $file="./data/{$GLOBALS['guid']}_{$GLOBALS['qq']}_search.php";
                $text='<?php return '.var_export($userList,true).';';
                if(false!==fopen($file,'w+')){
                    file_put_contents($file,$text);
                    self::send_group_msg($echo);
                }else{
                    self::send_group_msg("出問題了~~文件不存在");
                }
                die();
            }
        }
    }

    //撤回消息
    public static function recallMsg($msg_id){
        $url = $GLOBALS["config"]["host"]."/delete_msg?";
        $urlDate = array(
            "message_id"=>$msg_id
        );
        $url.=http_build_query($urlDate);
        curl_get($url);
    }

    //禁言
    public static function set_group_ban($user_id,$duration=0){
        $url = $GLOBALS["config"]["host"]."/set_group_ban?";
        $urlDate = array(
            "group_id"=>$GLOBALS["guid"],//群组
            "user_id"=>$user_id,//要禁言的 QQ 号
            "duration"=>$duration,//禁言时长, 单位秒, 0 表示取消禁言
        );
        $url.=http_build_query($urlDate);
        curl_get($url);
    }

    //获取消息
    public static function get_msg($message_id){
        $url = $GLOBALS["config"]["host"]."/get_msg?";
        $urlDate = array(
            "message_id"=>$message_id,//
        );
        $url.=http_build_query($urlDate);
        $data = curl_get($url);
        $data = json_decode($data,true);
        return $data; //real_id
    }

    //获取群消息历史记录
    public static function get_group_msg_history($message_seq){
        $url = $GLOBALS["config"]["host"]."/get_group_msg_history?";
        $urlDate = array(
            "group_id"=>$GLOBALS["guid"],//群组
            "message_seq"=>$message_seq,//起始消息序号, 可通过 get_msg 获得
        );
        $url.=http_build_query($urlDate);
        $data = curl_get($url);
        $data = json_decode($data,true);
        return $data;
    }
}