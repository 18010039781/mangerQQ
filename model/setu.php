<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/8/2 0002
 * Time: 9:46
 */

namespace model;


class setu{

    private $keyWord="";
    private $code="+";

    private $notList=array("色图");

    public function __construct($row=array()){
        $msg = key_exists("msg",$row)?$row["msg"]:"";
        if(!empty($msg)&&strpos($GLOBALS['msg'], $msg) === 0){
            $this->keyWord = getSubstr($GLOBALS['msg'], $msg);
        }
    }

    public function init(){
        if(!empty($this->keyWord)){
            $GLOBALS["_echo"] = "正在下载......";
            sendAllMsg();
            $GLOBALS["_echo"] = "";
            $this->getUrlAndData();
        }
    }

    private function validateStr(){
        $str = "v1";
        if(strpos($this->keyWord, "真人") === 0||strpos($this->keyWord, "唯美") === 0){
            $str="api88";
        }
        return $str;
    }

    private function getUrlAndData($str=""){
        if(empty($str)){
            $str = $this->validateStr();
        }
        switch ($str){
            case "v2":
                $this->imagesForV2();
                break;
            case "v1":
                $this->imagesForV1();
                break;
            default:
                $this->imagesForApi88();
        }
    }

    //
    private function imagesForV2(){
        $r18 = strpos($this->keyWord,$this->code) !== false?1:0;
        $key_word = str_replace('+','',$this->keyWord);
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
        $key_word = str_replace('+','',$this->keyWord);
        $url = "https://api.lolicon.app/setu?";
        $urlDate = array(
            "r18" => $r18,//0为非 R18，1为 R18，2为混合
            'num' => 2,//一次返回的结果数量
            //'keyword' => $key_word,//返回匹配指定标签的作品
            'size1200' => true,//
        );
        if(!in_array($key_word,array("色图"))){
            $urlDate["keyword"] = $key_word;
        }
        $url.=http_build_query($urlDate);
        $data = curl_get($url);

        $data = json_decode($data,true);
        $this->echoData($data,"v1");
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
                            $GLOBALS["_echo"] = "[CQ:image,file=$imageUrl,cache=0,proxy=0]";
                            //[CQ:image,file=http://baidu.com/1.jpg,type=show,id=40004]
                            sendAllMsg();
                            $GLOBALS["_echo"] = "";
                        }
                    }else{
                        $GLOBALS["_echo"] = "没有找到相关的图片";
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
}