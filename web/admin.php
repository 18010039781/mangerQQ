<?php
$fun = key_exists("fun",$_GET)?$_GET["fun"]:"";
$config = include ("../config/Config.php");
switch ($fun){
    case "addGroupId"://添加群组权限
        $type=key_exists("type",$_GET)?$_GET["type"]:0;// 0：取消
        $group_id=key_exists("group_id",$_GET)?$_GET["group_id"]:0;//群组id
        if(in_array($group_id,$config["group_guid"])&&$type==0){
            $config["group_guid"] = removeStrToArr($group_id,$config["group_guid"]);
        }elseif($type!=0){
            $config["group_guid"][] = $group_id;
        }
        $text='<?php return '.var_export($config,true).';';
        //$text='<?php return '.var_export($config,true).';';
        file_put_contents("../config/Config.php",$text);
        echo json_encode(array('status'=>200));
        break;
    case "addAuth"://管理菜单权限
        $auth_list=key_exists("auth_list",$_GET)?$_GET["auth_list"]:0;// 0：取消
        $group_id=key_exists("group_id",$_GET)?$_GET["group_id"]:0;//群组id
        $menu = include ("../config/Menu.php");
        if(in_array($group_id,$config["group_guid"])){
            //$auth_list=$auth_list["fun"];
            $nowList = array();
            if(is_array($auth_list)){
                foreach ($auth_list as $item){
                    if($item["name"] == "fun[{$group_id}]"){
                        $nowList[] = $item["value"];
                    }
                }
            }
            if(is_array($menu)){
                foreach ($menu as $menuKey => $menuRow){
                    $groupList = key_exists("group",$menuRow)?$menuRow["group"]:$config["group_guid"];
                    $groupList = removeStrToArr($group_id,$groupList);
                    if(in_array($menuKey,$nowList)){
                        $groupList[] = $group_id;
                    }
                    $menu[$menuKey]["group"] = $groupList;
                }
                $text='<?php return '.var_export($menu,true).';';
                file_put_contents("../config/Menu.php",$text);
            }
            echo json_encode(array('status'=>200));
        }else{
            echo json_encode(array('status'=>100,'error'=>"系统异常"));
        }
        break;
    case "ajaxMsg"://获取最近的qq消息
        $fileUrl ="../log/all_qq.log";
        if(file_exists($fileUrl)){
            $time=key_exists("time",$_GET)?$_GET["time"]:time();// 0：取消
            $con = getLatestLines($fileUrl,10);
            $data = explode("\r\n",$con);
            $html = "";
            for ($i=count($data)-1;$i>=0;$i--){ //倒着显示
                $itemStr = $data[$i];
                if(!empty($itemStr)){
                    $itemRow = json_decode($itemStr,true);
                    if(!empty($itemRow["time"])&&$itemRow["time"]>$time){
                        $html.="<li class='list-group-item' data-time='{$itemRow['time']}'>";
                        //$itemRow["message"].=" <br/>startTime:".date("Y-m-d H:i:s",$statTime);
                        $html.=getMsgForJson($itemRow);
                        $html.="</li>";
                    }
                }
            }
            foreach ($data as $itemStr){
            }
            echo json_encode(array("status"=>200,"html"=>$html));
        }else{
            echo json_encode(array("status"=>100,"error"=>"没有任何消息~~~"));
        }
        break;
    case "sendMsg":
        require "../function.php";
        require "../model/goHttp.php";
        $text = key_exists("text",$_POST)?$_POST["text"]:"";
        $file = key_exists("file",$_POST)?$_POST["file"]:"";
        $group_id=key_exists("group_id",$_POST)?$_POST["group_id"]:0;//群组id
        $GLOBALS["config"] = $config;
        $GLOBALS["guid"] = $group_id;
        if(empty($text)&&empty($file)){
            echo json_encode(array("status"=>100,"error"=>"不知道咋错的"));
        }else{
            if(!empty($file)){
                $file=end(explode("base64,",$file));
                $text.="[CQ:image,file=base64://{$file}]";
            }
            \model\goHttp::send_group_msg($text);
            echo json_encode(array("status"=>200));
        }
        break;
    default:
        echo json_encode(array());
}

function removeStrToArr($str,$arr){
    $list = array();
    if(is_array($arr)){
        foreach ($arr as $value){
            if($str!=$value){
                $list[] = $value;
            }
        }
    }
    return $list;
}

function getLatestLines($filename,$count = 20,$sep = "\r\n"){ //  注意这里必须用双引号
    $content = ''; // 最终内容
    $_current = '';// 当前读取内容寄存
    $seper = '';// 分隔符判断
    $seperLen = strlen($sep); // 分隔符长度
    $step= 1; // 每次走多少字符
    $pos = 0;// 起始位置 -1 就是从最后一个开始
    $i = 0;
    $count--;
    $handle = fopen($filename,'a+'); //读写模式，指针会被放在最后  当然也可以探测出 filesize然后从最后往前读。

    while($i < $count && fseek($handle,$pos,SEEK_END) ===0){
        // SEEK_END - 设定位置为文件尾加上 offset 。（要移动到文件尾之前的位置，需要给 offset 传递一个负值。）
        $_current = fread($handle,$step);
        $seper = $_current.$seper;
        if (strlen($seper)>$seperLen){
            $seper = substr($seper, 0, -$seperLen); // 截取当前字符最后几位。与分隔符匹配，所以长度和分隔符要一样。
        }
        $content = $_current.$content;
        $pos -= $step; // 向后退
        if ($sep === $seper ){ // 判断是否是换行或其他分隔符
            $i++;
        }
    }
    fclose($handle);
    return $content;

}

function getMsgForJson($dataJson){
    $html = "";
    if(key_exists("group_id",$dataJson)){
        $html.="<div class='div-tab' style='margin-bottom: 10px;'>";
        $html.="<p><d>消息类型</d>：群消息</p>";
        $html.="<p class='text-center'><d>群QQ</d>：{$dataJson['group_id']}</p>";
        $html.="<p class='text-right'><d>发送时间</d>：".date("Y-m-d H:i:s",$dataJson['time'])."</p>";
        $html.="</div>";
        if(key_exists("sender",$dataJson)){
            $html.="<div class='div-tab' style='margin-bottom: 10px;'>";
            $html.="<p><d>发信人昵称</d>：{$dataJson['sender']['nickname']}</p>";
            $html.="<p class='text-center'><d>群名片</d>：{$dataJson['sender']['card']}</p>";
            $html.="<p class='text-right'><d>发信人QQ</d>：{$dataJson['sender']['user_id']}</p>";
            $html.="</div>";
            $html.="<p><d>消息</d>：".translationMessage($dataJson['message'])."</p>";
        }else{
            $html.="<p class='text-allwork'><d>消息json</d>：".json_encode($dataJson)."</p>";
        }
    }
    return $html;
}

function translationMessage($str){
    $message = "";
    preg_match('/^(.*)\[CQ:image,file=(.*?),url=(.*?)\](.*)$/',$str,$matches);
    if(is_array($matches)&&count($matches)==5){
        $message.=$matches[1]."<img src='{$matches[3]}'/>".$matches[4];
    }
    return empty($message)?$str:$message;
}
