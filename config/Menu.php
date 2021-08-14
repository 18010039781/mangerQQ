<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/8/2 0002
 * Time: 9:32
 * @groupBool: true：群组消息才会执行
 */
return array(
    //检查是否需要撤回消息(检查需要放在上面)
    //检查是否需要撤回消息
    array('msg'=>'定时撤回消息-不推荐关闭','groupBool'=>true,'fun'=>'deleteNextMsg','className'=>'deleteMsg'),
    //检查新群员
    array('msg'=>'新群员提示','groupBool'=>false,'fun'=>'newUser','className'=>'timerMsg'),
    //检查撤回消息
    array('msg'=>'撤回消息提示','groupBool'=>false,'fun'=>'recallMsg','className'=>'timerMsg'),
    //检查禁言消息
    array('msg'=>'禁言消息提示','groupBool'=>false,'fun'=>'stopSpeak','className'=>'timerMsg'),
    //检查荣誉消息（龙王、群聊之火、快乐源泉）
    //array('msg'=>'荣誉消息提示','groupBool'=>false,'fun'=>'honorChange','className'=>'timerMsg'),
    //检查荣誉消息（龙王、群聊之火、快乐源泉）
    array('msg'=>'配套服务-不推荐关闭','groupBool'=>true,'fun'=>'relevance','className'=>'timerMsg'),
    array('msg'=>'多条查询','groupBool'=>true,'fun'=>'moreSearch','className'=>'timerMsg'),

    //正常的菜单
    //array('msg'=>'查询京东','groupBool'=>true,'fun'=>'searchJd','className'=>''),
    array('msg'=>'查询电话','groupBool'=>true,'fun'=>'searchPhone','className'=>''),
    array('msg'=>'查询信息','groupBool'=>true,'fun'=>'searchMe','className'=>''),
    array('msg'=>'点歌','groupBool'=>true,'fun'=>'musicWYU','className'=>''),
    array('msg'=>'文字转语音','groupBool'=>true,'fun'=>'textForTTs','className'=>''),
    array('msg'=>'来点','groupBool'=>true,'fun'=>'','className'=>'setu'),
    array('msg'=>'清理','groupBool'=>true,'fun'=>'clearAll','className'=>''),
    array('msg'=>'二维码','groupBool'=>true,'fun'=>'qrCode','className'=>''),
    array('msg'=>'群信息','groupBool'=>true,'fun'=>'showGroup','className'=>''),
    array('msg'=>'群荣誉','groupBool'=>true,'fun'=>'showGroupTop','className'=>''),
    array('msg'=>'设置管理员','groupBool'=>true,'fun'=>'setGroupAdmin','className'=>''),
    array('msg'=>'撤回','groupBool'=>true,'fun'=>'','className'=>'deleteMsg'),
    array('msg'=>'设置','groupBool'=>true,'fun'=>'settingArr','className'=>'setu'),
    array('msg'=>'我有个朋友..说..','groupBool'=>true,'fun'=>'sayImages','className'=>'canvas'),
);
