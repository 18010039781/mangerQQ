<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/8/2 0002
 * Time: 9:46
 */

namespace model;


class canvas
{
    private $img;
    private $width=1000;
    private $height=250;

    private $padding=24;
    private $imgType;

    private $row;
    private $roundNum;
    public function __construct($row=array()){
        $this->row = $row;
    }

    public function sayImages(){
        $msg = $GLOBALS["msg"];
        preg_match('/^我(.*?)朋友(.*?)说(.*)$/',$msg,$matches);
        if(count($matches)==4){
            $qqNumber = self::getQQNumber();
            $this->roundNum = $qqNumber;
            //$qqUrl = "http://q.qlogo.cn/headimg_dl?dst_uin={$qqNumber}&spec=100&img_type=jpg";
            $qqUrl = "http://q.qlogo.cn/headimg_dl?dst_uin={$qqNumber}&spec=640&img_type=jpg";
            if(!is_dir("./images/photoHead")){
                mkdir ("./images/photoHead",0777,true);
            }
            $qqUrl = downloadImageFromUrl($qqUrl,"./images/photoHead/",$qqNumber);
            if(empty($qqUrl)){ //没有高清图像
                $qqUrl = "http://q.qlogo.cn/headimg_dl?dst_uin={$qqNumber}&spec=100&img_type=jpg";
                $qqUrl = downloadImageFromUrl($qqUrl,"./images/photoHead/",$qqNumber);
            }
            //写入朋友头像
            self::createImgFoSay($qqUrl);
            //写入朋友说的话
            self::sayContract($matches[3]);
            self::printImg();
        }
    }

    private function sayContract($text){
        $text = self::resetStr($text);
        $ccc = imagecolorallocate($this->img,0,0,0);
        $color2 = imagecolorallocate($this->img,98,102,117);
        $font2=realpath('./web/fonts/HYQingYunW.ttf');
        $width = $this->width-$this->height-40;
        $top = $this->height*0.6-60;
        $temp = array("color" => array(98, 102, 117),"fontsize" =>40,"width" => $width,"left" =>$this->height+22,"top" => $top,"hang_size" => 60);
        //imagettftext($this->img,28,0,$this->height+10,$this->height*0.7,$color2,$font2,$text);
        $box = imagettfbbox(58, 0, $font2, "朋友");
        $pengHeight = $box[1]-$box[7];
        $textHeight = self::draw_txt_to($this->img,$temp,$font2, $text, false);
        $height = ($this->height-$textHeight-$pengHeight)/2;
        //goHttp::send_group_msg("pengHeight:{$pengHeight},textHeight:{$textHeight},top:{$height},oldTop:{$top}");
        imagettftext($this->img,58,0,$this->height+15,$height+$pengHeight,$ccc,$font2,"朋友");
        $temp["top"] = $height+$pengHeight;
        self::draw_txt_to($this->img,$temp,$font2, $text, true);
        //die();
        //imagettftext($this->img,$font1,$this->height+60,$this->padding+20,"朋友",$ccc);
        //die();
    }

    private function createImgFoSay($filename){
        $this->img = imagecreatetruecolor($this->width, $this->height);
        $background = imagecolorallocate($this->img,255,255,255);
        $ccc = imagecolorallocate($this->img,242,242,242);
        imagefill($this->img, 0, 0, $background);
        list($width,$height,$type) = getimagesize($filename);
        $typeList = array(1=>"gif",2=>"jpeg",3=>"png");
        if(key_exists($type,$typeList)){
            $leftX = 50;
            $fun = "imagecreatefrom".$typeList[$type];
            $source = $fun($filename);
            self::setImgToCircle($source,$width,$height);
            $newheight = $this->height-$this->padding*2;
            imagecopyresampled($this->img, $source, $leftX, $this->padding, 0, 0, $newheight, $newheight, $width, $height);
            imagedestroy($source);
        }else{
            die();
        }
    }

    //把图片转圆角
    private function setImgToCircle($img,$width,$height){
        $bar = imagecreatetruecolor($width,$height);
        $background = imagecolorallocate($bar,255,255,255);
        imagefill($bar, 0, 0, $background);
        $fgcolor = imagecolorallocate($bar, 0, 0, 0);

        imagefilledarc($bar,$width/2,$height/2,$width,$height, 0, 360, $fgcolor, IMG_ARC_PIE);
        // 将弧角图片的颜色设置为透明
        imagecolortransparent($bar, $fgcolor);
        imagecopymerge($img, $bar,0, 0, 0, 0,$width,$height,100);
        imagedestroy($bar);
    }

    //生成线条、雪花
    private function createLine(){
        //雪花
        for ($i = 0; $i < 100; $i++) {
            $color = imagecolorallocate($this->img, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255));
            imagestring($this->img, mt_rand(1, 16), mt_rand(0, $this->width), mt_rand(0, $this->height), '*', $color);
        }
    }

    private function printImg(){
        //header('Content-Type: image/jpeg');
        // 输出图像
        $fileUrl = "./images/{$this->roundNum}.jpeg";
        imagejpeg($this->img,$fileUrl,100);
        // 释放内存
        imagedestroy($this->img);
        $fileUrl = realpath($fileUrl);
        $GLOBALS["_echo"] = "[CQ:image,file=file:///$fileUrl,cache=0,proxy=0]";
    }

    private function getQQNumber(){
        preg_match('/\[CQ:at,qq=(.*?)\]/',$GLOBALS["msg"],$matches);
        if(count($matches)==2){
            //@群内某人
            $number = $matches[1];
        }else{
            preg_match('/@(\d+)/',$GLOBALS["msg"],$matches);
            if(count($matches)==2&&is_numeric($matches[1])){
                //@一串数字
                $number = $matches[1];
            }else{
                //没找到QQ
                $number = self::randQQForGroup();
            }
        }
        return $number;
    }

    //随机一个QQ号
    public static function randQQ(){
        $str = "";
        for ($i = 0;$i<mt_rand(9,11);$i++){
            if(!empty($str)){
                $str.=mt_rand(0,9);
            }else{
                $str.=mt_rand(1,9);
            }
        }
        return $str;
    }

    //处理CQ字符串
    public static function resetStr($text){
        $text = trim($text);
        if (!empty($text)){
            preg_match('/^(.*)\[CQ:(.*)\](.*)$/',$text,$matches);
            if(count($matches)==4){
                $text = $matches[1].$matches[3];
            }
            $text = str_replace(array("她","它","他"),array("我","我","我"),$text);
            return htmlspecialchars_decode($text);
        }else{
            return "不知道说些啥~~";
        }
    }

    //随机群友QQ
    public static function randQQForGroup(){
        $numberList = array();
        $userList = goHttp::get_group_member_list();
        if(is_array($userList)&&!empty($userList["data"])){
            foreach ($userList["data"] as $item){
                $numberList[] = $item["user_id"];
            }
        }
        if(!empty($numberList)){
            $key = array_rand($numberList);
            return $numberList[$key];
        }else{
            return self::randQQ();
        }
    }

    //调整图像大小
    private function resize_imagejpeg($file, $w, $h) {
        list($width, $height) = getimagesize($file);
        $src = imagecreatefromjpeg($file);
        $dst = imagecreatetruecolor($w, $h);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, $width, $height);
        imagedestroy($src);
        return $dst;
    }

    //文字换行
    /**
     * 文字自动换行算法
     * @param $card 画板
     * @param $pos 数组，top距离画板顶端的距离，fontsize文字的大小，width宽度，left距离左边的距离，hang_size行高
     * @param $font_file 字体文件
     * @param $str 要写的字符串
     * @param $iswrite  是否输出，ture，  花出文字，false只计算占用的高度
     * @return int 返回整个字符所占用的高度
     */
    private function draw_txt_to($card, $pos,$font_file, $str, $iswrite){
        $_str_h = $pos["top"];
        $fontsize = $pos["fontsize"];
        $width = $pos["width"];
        $margin_lift = $pos["left"];
        $hang_size = $pos["hang_size"];
        $temp_string = "";
        $tp = 0;

        $font_color = imagecolorallocate($card, $pos["color"][0], $pos["color"][1], $pos["color"][2]);
        for ($i = 0; $i < mb_strlen($str,"UTF8"); $i++) {
            if($tp>1){ //大于两行不显示
                break;
            }
            $box = imagettfbbox($fontsize, 0, $font_file, $temp_string);
            $_string_length = $box[2] - $box[0];
            $temptext = mb_substr($str, $i, 1,"UTF8");
            $temp = imagettfbbox($fontsize, 0, $font_file, $temptext);
            if ($_string_length + $temp[2] - $temp[0] < $width) {//长度不够，字数不够，需要
                //继续拼接字符串。
                $temp_string .= mb_substr($str, $i, 1,"UTF8");
                if ($i == mb_strlen($str,"UTF8") - 1) {//是不是最后半行。不满一行的情况
                    $_str_h += $hang_size;//计算整个文字换行后的高度。
                    $tp++;//行数
                    if ($iswrite) {//是否需要写入，核心绘制函数
                        imagettftext($card, $fontsize, 0, $margin_lift, $_str_h, $font_color, $font_file, $temp_string);
                    }
                }
            } else {//一行的字数够了，长度够了。
//            打印输出，对字符串零时字符串置null
                $texts = mb_substr($str, $i, 1,"UTF8");//零时行的开头第一个字。
//            判断默认第一个字符是不是符号；
                $isfuhao = preg_match("/[\\\\pP]/u", $texts) ? true : false;//一行的开头这个字符，是不是标点符号
                if ($isfuhao) {//如果是标点符号，则添加在第一行的结尾
                    $temp_string .= $texts;
//                判断如果是连续两个字符出现，并且两个丢失必须放在句末尾的，单独处理
                    $f = mb_substr($str, $i + 1, 1,"UTF8");
                    $fh = preg_match("/[\\\\pP]/u", $f) ? true : false;
                    if ($fh) {
                        $temp_string .= $f;
                        $i++;
                    }
                } else {
                    $i--;
                }
                $tmp_str_len = mb_strlen($temp_string,"UTF8");
                $s = mb_substr($temp_string, $tmp_str_len-1, 1,"UTF8");//取零时字符串最后一位字符

                if (in_array($s, array("\\", "“", "'", "<", "《",))) {//判断零时字符串的最后一个字符是不是可以放在见面
                    //讲最后一个字符用“_”代替。指针前移动一位。重新取被替换的字符。
                    $temp_string=rtrim($temp_string,$s);
                    $i--;
                }
//            }

//            计算行高，和行数。
                $_str_h += $hang_size;
                $tp++;
                if ($iswrite) {
                    imagettftext($card, $fontsize, 0, $margin_lift, $_str_h, $font_color, $font_file, $temp_string);
                }
//           写完了改行，置null该行的临时字符串。
                $temp_string = "";
            }
        }
        return $tp * $hang_size;

    }

}
