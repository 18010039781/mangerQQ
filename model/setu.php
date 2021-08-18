<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/8/2 0002
 * Time: 9:46
 */

namespace model;


class setu{

    private $row;
    private $keyWord="";
    private $code="+";

    private $setuLink=array(
        array("file"=>"zhen","url"=>"https://cdn.seovx.com/?mom=302","expr"=>"美图"),
        array("file"=>"yuan","url"=>"https://cdn.seovx.com/d/?mom=302","expr"=>"二刺螈|二次元"),
        array("file"=>"zhen","url"=>"http://api.nmb.show/xiaojiejie1.php","expr"=>"真人|妹子|女人"),
        array("file"=>"zhen","url"=>"http://api.nmb.show/xiaojiejie2.php","expr"=>"美女|富婆"),
        //array("fuc"=>"zhen","url"=>"https://cdn.seovx.com/?mom=302","expr"=>"真人|美女"),
    );

    public function __construct($row=array()){
        $this->row = $row;
        $msg = key_exists("msg",$row)?$row["msg"]:"";
        if(!empty($msg)&&strpos($GLOBALS['msg'], $msg) === 0){
            $this->keyWord = getSubstr($GLOBALS['msg'], $msg);
            //添加接口
            $url = "https://api88.net/api/img/rand/?";
            $urlDate = array(
                "key"=>$GLOBALS['API_KEY'],
                "rand_type"=>"rand_mt" //rand_mz,rand_mt (妹子图，唯美图)
            );
            $url.=http_build_query($urlDate);
            $this->setuLink[]=array("file"=>"zhen","url"=>$url,"expr"=>"小清新|清新|女神");
        }
    }

    public function init(){
        if(!empty($this->keyWord)){
            $this->keyWord = $this->resetStr($this->keyWord);
            //$GLOBALS["_echo"] = "正在下载......";
            //sendAllMsg();
            //$GLOBALS["_echo"] = "";
            $this->getUrlAndData();
        }
    }

    private function getUrlAndData(){
        foreach ($this->setuLink as $row){
            $key_word = str_replace($this->code,'',$this->keyWord);
            if(!empty($key_word)&&strstr($row["expr"],$key_word)!==false){
                //有地址
                $this->foreachImages($row);
                return;
            }
        }
        $this->imagesForV2();
    }

    private function foreachImages($row,$num=2){
        $fileUrl = downloadImageFromUrl($row["url"],"./images/{$row['file']}/");
        if(!empty($fileUrl)){
            $fileUrl = realpath($fileUrl);
            $echo = "[CQ:image,file=file:///$fileUrl,cache=0,proxy=0]";
            if($num==1){
                $GLOBALS["_echo"] = $echo;
            }else{
                goHttp::send_group_msg($echo);
            }
        }
        $num--;
        if($num>0){
            $this->foreachImages($row,$num);
        }
    }

    //
    private function imagesForV2(){
        $r18 = strpos($this->keyWord,$this->code) !== false?1:0;
        $key_word = str_replace($this->code,'',$this->keyWord);
        $key_word = str_replace(' ','|',$key_word);
        $url = "https://api.lolicon.app/setu/v2?";
        $urlDate = array(
            "r18" => $r18,//0为非 R18，1为 R18，2为混合
            'num' => 2,//一次返回的结果数量
            //'tag' => $key_word,//返回匹配指定标签的作品
            'size' => "small",//original、regular、small、thumb、mini
            'dateAfter' => strtotime("2020/01/01 01:00:00"),//返回在这个时间及以后上传的作品
        );
        if(!in_array($key_word,array("色图"))){
            $urlDate["tag"] = $key_word;
        }
        $url.=http_build_query($urlDate);
        $data = curl_get($url);
        $data = json_decode($data,true);
        $this->echoData($data,"v2");
    }

    //
    private function imagesForV1(){
        $r18 = strpos($this->keyWord,$this->code) !== false?1:0;
        $key_word = str_replace($this->code,'',$this->keyWord);
        $url = "https://api.lolicon.app/setu?";
        $urlDate = array(
            "r18" => $r18,//0为非 R18，1为 R18，2为混合
            'num' => 2,//一次返回的结果数量
            //'keyword' => $key_word,//返回匹配指定标签的作品
            'size1200' => true,//
        );
        if(!empty($key_word)&&!in_array($key_word,array("色图"))){
            $urlDate["keyword"] = $key_word;
        }
        $url.=http_build_query($urlDate);
        $data = curl_get($url);

        $data = json_decode($data,true);
        $this->echoData($data,"v2");
    }
    //
    private function imagesForApi88(){
        if(strpos($this->keyWord, "真人")){//妹子图
            $rand_type = "rand_mz";
        }else{//唯美图
            $rand_type = "rand_mt";
        }
        $url = "https://api88.net/api/img/rand/?";
        $urlDate = array(
            "key"=>$GLOBALS['API_KEY'],
            "rand_type"=>$rand_type, //rand_mz,rand_mt (妹子图，唯美图)
            "type"=>'json'
        );
        $url.=http_build_query($urlDate);
        $data = curl_get($url);
        $data = json_decode($data,true);
        $this->echoData($data,"api88");
    }

    private function echoData($data,$str){
        if(is_array($data)){
            switch ($str){
                case "v1":
                case "v2":
                    if(!empty($data["data"])){
                        $data = $data["data"];
                        foreach ($data as $item){
                            $imageUrl = key_exists("url",$item)?$item['url']:current($item['urls']);
                            $imageUrl = downloadImageFromUrl($imageUrl,"./images/setu/");
                            $fileUrl = realpath($imageUrl);
                            if(!empty($fileUrl)){
                                $GLOBALS["_echo"] = "[CQ:image,file=file:///$fileUrl,cache=0,proxy=0]";
                            }
                            //[CQ:image,file=http://baidu.com/1.jpg,type=show,id=40004]
                            sendAllMsg();
                            $GLOBALS["_echo"] = "";
                        }
                    }else{
                        $GLOBALS["_echo"] = "没有找到{$this->keyWord}相关的图片";
                    }
                    break;
                default://v1、v2
                    if(!empty($data["data"])&&!empty($data["data"]["url"])){
                        $imageUrl = $data["data"]["url"];
                        $GLOBALS["_echo"] = "[CQ:image,file=$imageUrl,cache=0,proxy=0]";
                    }else{
                        $GLOBALS["_echo"] = "没有找到相关的图片";
                    }
            }
        }else{
            $GLOBALS["_echo"] = "不知道啥原因，反正没有涩图了";
        }
    }

    public function echoTest(){
        echo "test";
    }

    //中文转日文
    public function zhTojp($text){
        $str = "";
        $url = "https://api88.net/api/fanyi/?";
        $urlDate = array(
            "key"=>$GLOBALS['API_KEY'],
            "text"=>$text,
            "from"=>'zh',
            "to"=>'jp'
        );
        $url.=http_build_query($urlDate);
        $data = curl_get($url);
        $data = json_decode($data,true);
        if(is_array($data)&&$data["code"]==200){
            foreach ($data["data"]["trans_result"] as $row){
                $str.=empty($str)?"":"|";
                $str.="|".$row["dst"];
            }
        }
        if(empty($str)){
            return $text;
        }else{
            return $str;
        }
    }

    //中文转日文(含有分割符合)
    public function zhTojpEx($text){
        if(strstr($text,"|")!==false){
            $str = "";
            $textList = explode("|",$text);
            foreach ($textList as $item){
                if(!empty($item)){
                    $str.=self::zhTojp($item);
                }
            }
            return $str;
        }else{
            return self::zhTojp($text);
        }
    }

    private function resetStr($str){
        $arr = self::getResetArr();
        $keyList = array_keys($arr);
        $valueList = array_values($arr);
        $output = str_replace($keyList, $valueList, $str);

        return $output;
    }

    public function settingArr(){
        if(!empty($this->keyWord)){
            $textList = explode("=",$this->keyWord);
            if(count($textList)==2){
                $file="./data/{$GLOBALS['guid']}_group_data.php";
                $list = self::getResetArr();
                $list[$textList[0]] = $textList[1];
                $text='<?php return '.var_export($list,true).';';
                file_put_contents($file,$text);
                $GLOBALS["_echo"] = "设置成功：{$textList[0]}=$textList[1]";
            }else{
                $GLOBALS["_echo"] = "参数非法，格式：设置 大哥=色图";
            }
        }
    }

    private function getResetArr(){
        $file="./data/{$GLOBALS['guid']}_group_data.php";
        $list = array();
        if(file_exists($file)){
            $list = include($file);
        }
        $list["的"]="";
        $list["色图"]="";
        $list["涩图"]="";
        return $list;
    }
}