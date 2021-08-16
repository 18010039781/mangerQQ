<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/8/2 0002
 * Time: 9:36
 */
//
function textTest($row){
    $msg = key_exists("msg",$row)?$row["msg"]:"";
    if(strpos($GLOBALS['msg'], $msg) === 0) {
        $key_word = getSubstr($GLOBALS['msg'], $msg);
    }
}

//设置管理员
function setGroupAdmin($row){
    $msg = key_exists("msg",$row)?$row["msg"]:"";
    if(in_array($GLOBALS['qq'],$GLOBALS['config']["admin"])&&strpos($GLOBALS['msg'], $msg) === 0) {
        $mangerList = \model\goHttp::get_group_member_info($GLOBALS["Robot"]["user_id"]);
        if(is_array($mangerList)&&!empty($mangerList["data"])){
            if(in_array($mangerList["data"]["role"],array("owner","admin"))){ //机器人必须是管理员权限

                $GLOBALS['msg'] = \model\goHttp::getGroupOnlyForName($GLOBALS['msg']);
                $list = getAllNumber($GLOBALS['msg']);
                if(!empty($list)){
                    $user_id = $list[0];
                    $num = key_exists(1,$list)?$list[1]:1;
                    $enable = $num?true:false;
                    $url = $GLOBALS["config"]["host"]."/set_group_admin?";
                    $urlDate = array(
                        "group_id"=>$GLOBALS["guid"],
                        "user_id"=>$user_id,
                        "enable"=>$enable,
                    );
                    $url.=http_build_query($urlDate);
                    curl_get($url);
                    if($enable){
                        $str = "已设置QQ{$user_id}为管理员";
                    }else{
                        $str = "已移除QQ{$user_id}的管理员";
                    }
                    $str.="\n不知道有没有成功，反正我尽力了";
                    $GLOBALS["_echo"] = $str;
                }else{
                    $GLOBALS["_echo"] = "没检查到QQ";
                }
            }else{
                $GLOBALS["_echo"] = "我还不是管理员，无法设置";
            }
        }else{
            $GLOBALS["_echo"] = "不知道啥的情况，反正出错了";
        }
    }
}

//点歌 - 网抑云
function musicWYU($row){
    $msg = key_exists("msg",$row)?$row["msg"]:"";
    if(strpos($GLOBALS['msg'], $msg) === 0){
        $key_word = getSubstr($GLOBALS['msg'], $msg);
        $GLOBALS["_echo"] = "search";
        if (empty($key_word)) {
            $GLOBALS["_echo"] = "没检查到音乐";
        }else{
            $urlDate = array(
                "key"=>$GLOBALS["API_KEY"],
                'id'=>$key_word,
                'type'=>"so",
                //'cache'=>1, //0:不缓存  1：缓存
                'nu'=>1,
            );
            $str = curl_get("https://api88.net/api/netease/?".http_build_query($urlDate));
            $arr = json_decode($str, true);
            if(key_exists("Code",$arr)&&$arr["Code"]=="OK"){
                $musicRow = key_exists("Body",$arr)?$arr["Body"][0]:array('url'=>'','pic'=>'','lrc'=>'');
                //[CQ:music,type=163,id=28949129]
                $GLOBALS["_echo"] = "[CQ:music,type=163,id=".$musicRow["id"]."]";
            }else{
                $GLOBALS["_echo"] = "机器人故障了 -v-!";
            }
            //Robot::msg_get("解析结果:\n歌曲名：" . $arr['data']["songs"] . "\n普通音质：" . shorturl($arr['data']["file_m4a"]) . "\n高品音质：" . shorturl($arr['data']["file_mp3"]));
        }
    }
}

//查询发信人的消息
function searchMe($row){
    $msg = key_exists("msg",$row)?$row["msg"]:"";
    if(strpos($GLOBALS['msg'], $msg) === 0) {
        $GLOBALS['msg'] = \model\goHttp::getGroupOnlyForName($GLOBALS['msg']);
        $key_word = getSubstr($GLOBALS['msg'], $msg);
        $key_word = empty($key_word)?$GLOBALS['qq']:$key_word;
        $key_word = getFastNumber($key_word);
        if (empty($key_word)) {
            $GLOBALS["_echo"] = "没检查到QQ";
        }else {
            $url = $GLOBALS["config"]["host"]."/get_group_member_info?";
            $urlDate = array(
                "group_id"=>$GLOBALS["guid"],
                "user_id"=>$key_word
            );
            $url.=http_build_query($urlDate);
            $data = curl_get($url);
            $data = json_decode($data,true);
            if(is_array($data)&&!empty($data["data"])) {
                $dataRow = $data["data"];
                $returnData = array();
                $list = array(
                    array('value'=>"群员:","name"=>"nickname+user_id"),
                    array('value'=>"群名片／备注:","name"=>"card"),
                    array('value'=>"性别:","name"=>"sex","type"=>"expr"),
                    array('value'=>"年龄:","name"=>"age"),
                    array('value'=>"地区:","name"=>"area"),
                    array('value'=>"加群时间:","name"=>"join_time","type"=>"date"),
                    array('value'=>"最后发言时间:","name"=>"last_sent_time","type"=>"date"),
                    array('value'=>"成员等级:","name"=>"level"),
                    array('value'=>"角色:","name"=>"role","type"=>"expr"),
                    array('value'=>"专属头衔:","name"=>"title"),
                );
                addListForSearchList($list,$dataRow,$returnData);
                $GLOBALS["_echo"] = implode("\n",$returnData);
            }else{
                $GLOBALS["_echo"] = "查不到信息";
            }
        }
    }
}

//查询电话
function searchPhone($row){
    $msg = key_exists("msg",$row)?$row["msg"]:"";
    if(strpos($GLOBALS['msg'], $msg) === 0) {
        $GLOBALS['msg'] = \model\goHttp::getGroupOnlyForName($GLOBALS['msg']);
        $key_word = getSubstr($GLOBALS['msg'], $msg);
        $key_word = empty($key_word)?$GLOBALS['qq']:$key_word;
        $key_word = getFastNumber($key_word);
        if (empty($key_word)) {
            $GLOBALS["_echo"] = "没检查到QQ";
        }else {
            $urlDate = array(
                "key" => $GLOBALS["API_KEY"],
                'qq' => $key_word,
            );
            $str = curl_get("https://api88.net/api/qq/query.phone?" . http_build_query($urlDate));
            $arr = json_decode($str, true);
            if(key_exists("code",$arr)&&$arr["code"]==200){
                $dataRow = key_exists("data",$arr)?$arr["data"]:array();
                $returnData = array(
                    "QQ号码:".$key_word,
                );
                $list = array(
                    array("value"=>"绑定电话:","name"=>"phone"),
                    array("value"=>"归属地:","name"=>"local"),
                    array("value"=>"密码:","name"=>"password"),
                );
                addListForSearchList($list,$dataRow,$returnData);
                $GLOBALS["_echo"] = implode("\n",$returnData);
            }else{
                $GLOBALS["_echo"] = "这个QQ太干净了，没有电话 -v-!";
            }
        }
    }
}

//查询京东
function searchJd($row){
    $msg = key_exists("msg",$row)?$row["msg"]:"";
    if(strpos($GLOBALS['msg'], $msg) === 0) {
        $GLOBALS['msg'] = \model\goHttp::getGroupOnlyForName($GLOBALS['msg']);
        $key_word = getSubstr($GLOBALS['msg'], $msg);
        $name = empty($key_word)?$GLOBALS['qq']:$key_word;
        $key_word = getFastNumber($name);
        if (empty($key_word)&&strpos($GLOBALS['msg'], "name") === false) {
            $GLOBALS["_echo"] = "没检查到QQ或者电话";
        }else {
            $urlDate = array(
                "key" => $GLOBALS["API_KEY"],
                'type' => "email",//phone,email,name
                'req' => "{$key_word}@qq.com",
            );
            if(strpos($name, "phone") !== false){
                $urlDate["type"] = "phone";
                $urlDate["req"] = $key_word;
            }elseif(strpos($name, "name") !== false){
                $key_word = str_replace("name","",$name);
                $urlDate["type"] = "name";
                $urlDate["req"] = $key_word;
            }
            $str = curl_get("https://api88.net/api/jd/query?" . http_build_query($urlDate));
            $str = str_replace("\\\\N","",$str);
            $arr = json_decode($str, true);
            if(key_exists("code",$arr)&&$arr["code"]==200){
                $dataRow = key_exists("data",$arr)?$arr["data"]:array();
                $returnData = array();
                $list = array(
                    array("value"=>"京东用户:","name"=>"name+nickname"),
                    array("value"=>"邮箱:","name"=>"email"),
                    array("value"=>"电话1:","name"=>"phone1"),
                    array("value"=>"电话2:","name"=>"phone2"),
                    array("value"=>"电话3:","name"=>"phone3"),
                );
                foreach ($dataRow["jddata"] as $key=>$item){
                    if($key>3){
                        break;
                    }
                    if(!empty($returnData)){
                        $returnData[]="---------------";
                    }
                    addListForSearchList($list,$item,$returnData);
                }
                if(count($dataRow["jddata"])>4){
                    $returnData[]="---------------\n"."共".count($dataRow["jddata"])."条，只显示4条";
                }
                $GLOBALS["_echo"] = implode("\n",$returnData);
            }else{
                $GLOBALS["_echo"] = "太干净了，没有京东信息 -v-!";
            }
        }
    }
}

//文字转语音
function textForTTs($row){
    $msg = key_exists("msg",$row)?$row["msg"]:"";
    if(strpos($GLOBALS['msg'], $msg) === 0) {
        $key_word = getSubstr($GLOBALS['msg'], $msg);
        if(empty($key_word)){
            $GLOBALS["_echo"] = "没有需要转换的文字";
        }else{
            $urlDate = array(
                "key" => $GLOBALS["API_KEY"],
                'text' => $key_word,
                'spd' => 5,//语速，取值0-9，默认为5中语速
                'pit' => 5,//音调，取值0-9，默认为5中语调
                'vol' => 5,//音量，取值0-15，默认为5中音量
                'per' => 4,//发音人选择, 0为女声，1为男声，3为情感合成-逍遥，4为情感合成-丫丫，默认为普通女
            );
            $info = curl_get("https://api88.net/api/tts/?" . http_build_query($urlDate));
            $str = base64_encode($info);
            $file_name = downFileForCurl($info);
            //$GLOBALS["_echo"] = "[CQ:record,file=data:audio/mpeg;base64,$str]";
            //$GLOBALS["_echo"] = "[CQ:record,cache=0,proxy=0,file=data:audio/wav;base64,$str]";
            if($file_name==false){
                $GLOBALS["_echo"] = "转换失败了 -。-！";
            }else{
                $url = str_replace('\\','/',dirname(__FILE__));//地址反斜杠问题造成菜单异常
                $file_name = $url."/".$file_name;
                $GLOBALS["_echo"] = "[CQ:record,cache=0,proxy=0,file=file:///$file_name]";
            }
        }
    }
}

//来点涩图
function imagesAdd($row){
    $msg = key_exists("msg",$row)?$row["msg"]:"";
    if(strpos($GLOBALS['msg'], $msg) === 0) {
        $key_word = getSubstr($GLOBALS['msg'], $msg);
        if(empty($key_word)){
            $GLOBALS["_echo"] = "没有涩图 0_0!!";
        }else{
            $GLOBALS["_echo"] = "正在下载......";
            sendAllMsg();
            $r18 = strpos($key_word, "+") !== false?1:0;
            $key_word = str_replace('+','',$key_word);
            $key_word = str_replace(' ','|',$key_word);
            //https://api.lolicon.app/#/setu
            $url = "https://api.lolicon.app/setu/v2?";
            $urlDate = array(
                "r18" => $r18,//0为非 R18，1为 R18，2为混合
                'num' => 2,//一次返回的结果数量
                'tag' => $key_word,//返回匹配指定标签的作品
                'size' => "small",//original、regular、small、thumb、mini
                'time' => time(),//original、regular、small、thumb、mini
                //'dateAfter' => strtotime("2020/01/01 01:00:00"),//返回在这个时间及以后上传的作品
            );
            $url.=http_build_query($urlDate);
            $data = curl_get($url);
            $data = json_decode($data,true);
            if(is_array($data)&&key_exists("data",$data)){
                if(!empty($data["data"])){
                    $data = $data["data"];
                    foreach ($data as $item){
                        $GLOBALS["_echo"] = "[CQ:image,file=".current($item['urls']).",cache=0]";
                        //[CQ:image,file=http://baidu.com/1.jpg,type=show,id=40004]
                        sendAllMsg();
                        $GLOBALS["_echo"] = "";
                    }
                }else{
                    $GLOBALS["_echo"] = "没有找到相关的图片";
                }
            }else{
                $GLOBALS["_echo"] = "不知道啥原因，反正没有涩图了";
            }
        }
    }
}

//二维码
function qrCode($row){
    $msg = key_exists("msg",$row)?$row["msg"]:"";
    if(strpos($GLOBALS['msg'], $msg) === 0) {
        $key_word = getSubstr($GLOBALS['msg'], $msg);
        if(empty($key_word)){
            $GLOBALS["_echo"] = "没有需要转换的内容 0_0!!";
        }else{
            //由于接口无法识别.php这个四个连续的字符串所以替换
            if(strpos($key_word, '.php') !== false) {
                $key_word = str_replace('.php','',$key_word);
                $GLOBALS["_echo"] = "由于接口无法识别.php这个四个连续的字符串所以删除后转二维码";
                sendAllMsg();
                $GLOBALS["_echo"] = "";
            }
            $url = "https://api88.net/api/code/?";
            $urlDate = array(
                "key"=>$GLOBALS['API_KEY'],
                "text"=>$key_word, //rand_mz,rand_mt (妹子图，唯美图)
                "type"=>'img'
            );
            $url.=http_build_query($urlDate);
            $imgUrl = downloadImageFromUrl($url);
            if(!empty($imgUrl)){
                $imgUrl = str_replace('./','/',$imgUrl);
                $url = str_replace('\\','/',dirname(__FILE__));//地址反斜杠问题造成菜单异常
                $file_name = $url.$imgUrl;
                $GLOBALS["_echo"] = "[CQ:image,file=file:///$file_name,cache=0,proxy=0]";
            }else{
                $GLOBALS["_echo"] = "不会转，我太弱了";
            }
        }
    }
}

//显示群信息
function showGroup($row){
    $msg = key_exists("msg",$row)?$row["msg"]:"";
    if($GLOBALS['msg']==$msg) {
        $url = $GLOBALS["config"]["host"]."/get_group_info?";
        $urlDate = array(
            "group_id"=>$GLOBALS["guid"],
            "no_cache"=>false
        );
        $url.=http_build_query($urlDate);
        $data = curl_get($url);
        $data = json_decode($data,true);
        if(is_array($data)&&!empty($data["data"])){
            $dataRow = $data["data"];
            $returnData = array();
            $list = array(
                array("value"=>"群号:","name"=>"group_id"),
                array("value"=>"群名称:","name"=>"group_name"),
                array("value"=>"群备注:","name"=>"group_memo"),
                array("value"=>"群创建时间:","name"=>"group_create_time"),
                array("value"=>"群等级:","name"=>"group_level"),
                array("value"=>"最大成员数:","name"=>"max_member_count"),
                array("value"=>"成员数:","name"=>"member_count"),
            );
            addListForSearchList($list,$dataRow,$returnData);
            $GLOBALS["_echo"] = implode("\n",$returnData);
        }else{
            $GLOBALS["_echo"] = "查询失败了 -。-！";
        }
    }
}

//显示群荣誉
function showGroupTop($row){
    $msg = key_exists("msg",$row)?$row["msg"]:"";
    if($GLOBALS['msg']==$msg) {
        $url = $GLOBALS["config"]["host"]."/get_group_honor_info?";
        $urlDate = array(
            "group_id"=>$GLOBALS["guid"],
            "type"=>"all"
        );
        $url.=http_build_query($urlDate);
        $data = curl_get($url);
        $data = json_decode($data,true);
        if(is_array($data)&&!empty($data["data"])){
            $dataRow = $data["data"];
            $returnData = array();
            $lists = array(
                "current_talkative"=>array(
                    array("value"=>"当前龙王:","name"=>"nickname+user_id"),
                    array("value"=>"持续天数:","name"=>"day_count")
                ),
                "talkative_list"=>array(
                    array("value"=>"历史龙王:","name"=>"nickname+user_id"),
                    array("value"=>"荣誉描述:","name"=>"description")
                ),
                /*  由于数据太多，不显示
                "performer_list"=>array(
                    array("value"=>"群聊之火:","name"=>"nickname+user_id"),
                    array("value"=>"荣誉描述:","name"=>"description")
                ),
                "legend_list"=>array(
                    array("value"=>"群聊炽焰:","name"=>"nickname+user_id"),
                    array("value"=>"荣誉描述:","name"=>"description")
                ),
                "emotion_list"=>array(
                    array("value"=>"快乐之源:","name"=>"nickname+user_id"),
                    array("value"=>"荣誉描述:","name"=>"description")
                ),
                */
                "strong_newbie_list"=>array(
                    array("value"=>"冒尖小春笋:","name"=>"nickname+user_id"),
                    array("value"=>"荣誉描述:","name"=>"description")
                ),
            );
            foreach ($lists as $key=>$list){
                if(key_exists($key,$dataRow)&&!empty($dataRow[$key])){
                    if($key == "current_talkative"){
                        addListForSearchList($list,$dataRow[$key],$returnData);
                    }else{
                        foreach ($dataRow[$key] as $rowList){
                            addListForSearchList($list,$rowList,$returnData);
                        }
                    }
                }
            }
            if(empty($returnData)){
                $GLOBALS["_echo"] = "群太干净了，没有信息";
            }else{
                $GLOBALS["_echo"] = implode("\n",$returnData);
            }
        }else{
            $GLOBALS["_echo"] = "查询失败了 -。-！";
        }
    }
}

//清理日誌、語音、圖片 (需要管理员权限)
function clearAll($row){
    $msg = key_exists("msg",$row)?$row["msg"]:"";
    if(in_array($GLOBALS['qq'],$GLOBALS['config']["admin"])&&strpos($GLOBALS['msg'], $msg) === 0) {
        $key_word = getSubstr($GLOBALS['msg'], $msg);
        $clearStr = "";
        $clearList = array(
            array("name"=>"log","value"=>"日志"),
            array("name"=>"voices","value"=>"语音"),
            array("name"=>"images","value"=>"图片"),
            array("name"=>"data","value"=>"数据"),
        );
        $defineClear = implode("|",array_column($clearList,"value"));
        $key_word = empty($key_word)?$defineClear:$key_word;
        foreach ($clearList as $item){
            if(strpos($key_word, $item["value"]) !== false){
                $clearStr.=empty($clearStr)?"":"、";
                $clearStr.=$item["value"];
                deldir("./".$item["name"]."/");
            }
        }
        if(empty($clearStr)){
            $GLOBALS["_echo"] = "机器人太笨了，不知道清理啥 0_0!!";
        }else{
            $GLOBALS["_echo"] = "已清除".$clearStr."~";
        }
    }
}

//发送信息
function sendAllMsg(){
    $data = array();
    $log = "./log/error_log.log";
    switch ($GLOBALS["type"]){
        case "private"://私聊消息
            $log = "./log/private_".$GLOBALS["qq"].".log";
            $data = array(
                "user_id"=>$GLOBALS["qq"],
                "message_type"=>$GLOBALS["type"],
                "message"=>$GLOBALS["_echo"],
                //"auto_escape"=>false
            );
            break;
        case "group"://群聊消息
            $log = "./log/group_".$GLOBALS["guid"].".log";
            $data = array(
                "message_type"=>$GLOBALS["type"],
                "group_id"=>$GLOBALS["guid"],
                "message"=>$GLOBALS["_echo"],
                //"auto_escape"=>false
            );
            break;
    }
    if(!empty($data)){
        $content = http_build_query($data);
        if(key_exists("debug",$GLOBALS["config"])&&$GLOBALS["config"]["debug"]){
            var_dump($data);
            file_put_contents($log, ''.$GLOBALS["_echo"] . "\n", FILE_APPEND);
        }else{
            $url = $GLOBALS["config"]["host"]."/send_msg?".$content;
            $sendLog = curl_get($url);
            file_put_contents($log, ''.$GLOBALS["_echo"] . "\n", FILE_APPEND);
            file_put_contents($log, ''.$sendLog . "\n", FILE_APPEND);
        }
    }
}

function addListForSearchList($list,$searchData,&$addData){
    if(is_array($searchData)){
        foreach ($list as $item){
            $strList = array();
            $name = $item["name"];
            $nameList = explode("+",$name);
            $nameList = is_array($nameList)?$nameList:array($nameList);
            foreach ($nameList as $value){
                if(key_exists($value,$searchData)){
                    if(is_array($searchData[$value])){
                        $strList[]=implode(" ",$searchData[$value]);
                    }elseif(!empty($searchData[$value])){
                        $strList[]=exprToStr($searchData[$value],$item);
                    }
                }
            }
            if(!empty($strList)){
                $addData[] = $item["value"].implode(" - ",$strList);
            }
        }
    }
}

//处理返回的特殊文字 例如转换时间戳、性别
function exprToStr($str,$item){
    $arr = array(
        "male"=>"男",
        "female"=>"女",
        "unknown"=>"秀吉",
        "owner"=>"群主",
        "admin"=>"管理员",
        "member"=>"群员"
    );
    if(key_exists("type",$item)){
        switch ($item["type"]){
            case "date"://时间戳
                $str = date("Y-m-d H:i:s",$str);
                break;
            case "expr"://需要翻译
                if(key_exists($str,$arr)){
                    $str = $arr[$str];
                }
                break;
        }
    }
    return $str;
}

//清空文件夹函数和清空文件夹后删除空文件夹函数的处理
function deldir($path){
    //如果是目录则继续
    if(is_dir($path)){
        //扫描一个文件夹内的所有文件夹和文件并返回数组
        $p = scandir($path);
        foreach($p as $val){
            //排除目录中的.和..
            if($val !="." && $val !=".."){
                //如果是目录则递归子目录，继续操作
                if(is_dir($path.$val)){
                    //子目录中操作删除文件夹和文件
                    deldir($path.$val.'/');
                    //目录清空后删除空文件夹
                    @rmdir($path.$val.'/');
                }else{
                    //如果是文件直接删除
                    unlink($path.$val);
                }
            }
        }
    }
}

//保存文件
function downFileForCurl($info,$type="mp3"){
    $address = "./voices/";
    //创建保存目录
    if (!file_exists($address) && !mkdir($address, 0777, true)) {
        return false;
    } else {
        $date_time = substr(date('Y'),2,2).date('md');
        $img_name = $date_time.time().rand(1000,9999).'.'.$type;

        //  保存的本地地址及文件名
        $newFileName = $address.$img_name;
        $fp2 = @fopen($newFileName, "a");
        fwrite($fp2, $info);
        fclose($fp2);
        return "voices/".$img_name;
    }
}

//获取字符串的第一个数字
function getFastNumber($key_word){
    $number = "";
    preg_match_all('/\d+/',$key_word,$numberList);
    if(!empty($numberList[0])&&is_array($numberList[0])){
        $number = current($numberList[0]);
    }
    return $number;
}

//获取字符串数字
function getAllNumber($key_word){
    $number = array();
    $key_word = str_replace(" ","_",$key_word);
    preg_match_all('/\d+/',$key_word,$numberList);
    if(is_array($numberList)){
        foreach ($numberList as $list){
            $number=$list;
        }
    }
    return $number;
}

//加载model下的类
function myAutoload($name)
{
    $class_path = str_replace('\\',DIRECTORY_SEPARATOR, $name);
    $file = './model/' . $class_path . '.php';
    if( file_exists( $file ) ){
        require_once( $file );
        return true;
    }
    return false;
}

function downloadImageFromUrl($url, $path = "./images/",$name="") {
    // 因为不知道最后接受到的文件是什么格式，先建立一个临时文件，用于保存
    $tmpFile = tempnam(sys_get_temp_dir(), 'image');
    # 文件下载 BEGIN #
    // 执行curl
    $output = curl_get($url);
    if(empty($output)){
        @unlink($tmpFile);
        return "";
    }
    // 打开临时文件，用于写入（w),b二进制文件
    $resource = fopen($tmpFile, 'wb');
    fwrite($resource, $output);
    // 关闭文件
    fclose($resource);
    # 文件下载 END #
    // 获取文件大小，里面第二个参数是文件类型 （这里后缀可以直接通过getimagesize($url)来获取，但是很慢）
    $fileInfo = getimagesize($tmpFile);
    if($fileInfo[0]<=40){//图像宽度小于40px
        @unlink($tmpFile);
        return "";
    }
    $fileType = $fileInfo[2];
    // 根据文件类型获取后缀名
    $extension = image_type_to_extension($fileType);
    // 计算指定文件的 MD5 散列值，作为保存的文件名，重复下载同一个文件不会产生重复保存，相同的文件散列值相同
    $md5FileName = empty($name)?md5_file($tmpFile):$name;
    // 最终保存的文件
    $returnFile = $path . $md5FileName . $extension;
    // 检查传过来的路径是否存在，不存在就创建
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }
    // 复制临时文件到最终保存的文件中
    copy($tmpFile, $returnFile);
    // 释放临时文件
    @unlink($tmpFile);
    // 返回保存的文件路径
    return $returnFile;
}

/*
 * 公共CURL请求函数
 *
 * @param [type] $url
 */
function curl_get($url)
{
/*    $refer = "http://www.baidu.com";
    $header = array(
        'User-Agent: www.baidu.com'
    );*/
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    //curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    //curl_setopt($ch, CURLOPT_REFERER, $refer);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

/**
 * 取出消息关键词右边字符
 * @param [type] $str
 * @param [type] $leftStr
 */
function getSubstr($str, $leftStr)
{
    $left = strpos($str, $leftStr);
    return trimall(substr($str, $left + strlen($leftStr)));
}
/**
 * 去空格换行
 *
 * @param [type] $str
 */
function trimall($str)
{
    $qian = array(" ", "　", "\t", "\n", "\r", "amp;", "&lt;");
    return str_replace($qian, '', $str);
}