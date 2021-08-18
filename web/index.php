<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="content-type" content="text/html;charset=utf-8">
    <meta name="theme-color" content="#2932e1">
    <meta name="referrer" content="never">
    <title>web</title>
    <link rel="stylesheet" type="text/css" href="./web/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="./web/css/main.css">
    <script src="./web/js/jquery-3.2.1.min.js"></script>
    <script src="./web/js/bootstrap.min.js"></script>
    <script src="./web/js/main.js"></script>
</head>
<body>
    <div class="col-lg-10 col-lg-offset-1 mb-20 pt-20">
        <div class="box-border">
            <div class="">
                <div class="media">
                    <div class="media-left media-middle" style="border-right: 1px solid #ddd">
                        <div>
                            <dl class="dl-horizontal" style="margin-bottom: 0px;">
                                <dt>登录QQ:</dt>
                                <dd><?php echo $GLOBALS["Robot"]["user_id"]; ?></dd>
                                <dt>昵  称:</dt>
                                <dd><?php echo $GLOBALS["Robot"]["nickname"]; ?></dd>
                            </dl>
                        </div>
                    </div>
                    <div class="media-body ptb-15 media-middle" style="padding-left: 10px;">
                        <div class="div-tab">
                            <div style="width: 40%" class="media-middle">
                                <textarea rows="4" class="form-control" id="sendText"></textarea>
                            </div>
                            <div style="width: 8%" class="media-middle text-center">
                                <span> 或者 </span>
                            </div>
                            <div style="width: 40%" class="media-middle">
                                <input type="file" class="form-control" id="changeFile" accept="image/x-png,image/gif,image/jpeg">
                                <input type="hidden" id="sendFile">
                            </div>
                            <div style="width: 12%;padding-left: 10px;" class="media-middle text-left">
                                <button class="btn btn-primary" id="sendMsg">发送</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5 col-lg-offset-1 mb-20">
        <form class="" id="formAuth">
            <?php
            $groupList = \model\goHttp::get_group_list();
            if(is_array($groupList)&&!empty($groupList["data"])){
                $onlyGroup = $GLOBALS["config"]["group_guid"];
                echo "<ul class='list-group'>";
                echo "<li class='list-group-item title-li'><h4 class=''>权限管理：<small>（点击机器人可以开-关，点击每个菜单的空白可以设置权限）</small></h4></li>";
                foreach ($groupList["data"] as $groupRow){
                    if(in_array($groupRow["group_id"],$onlyGroup)){
                        echo "<li class='list-group-item changeGroup'>".$groupRow["group_name"]."<a href='javascript:void(0);' data-type='0' data-id='{$groupRow["group_id"]}' class='text-primary addGroupId'>（机器人 - 开）</a></li>";
                        echo "<li class='list-group-item menuGroup' style='display: none;'><ul class='list-unstyled auth-menu'>";
                        echo "<li class=''><div class='checkbox'><label><input type='checkbox' name='all' data-group='{$groupRow["group_id"]}'>所有</label></div></li>";

                        if(!empty($GLOBALS["menu"])&&is_array($GLOBALS["menu"])){
                            foreach ($GLOBALS["menu"] as $menu_key=>$menuRow){
                                $checkValue = empty($menuRow["msg"])?$menuRow["fun"]:$menuRow["msg"];
                                $groupMenu = key_exists("group",$menuRow)?$menuRow["group"]:$onlyGroup;
                                if(in_array($groupRow["group_id"],$groupMenu)){
                                    echo "<li class=''><div class='checkbox'><label><input type='checkbox' data-group='{$groupRow["group_id"]}' name='fun[{$groupRow["group_id"]}]' checked value='{$menu_key}'>{$checkValue}</label></div></li>";
                                }else{
                                    echo "<li class=''><div class='checkbox'><label><input type='checkbox' data-group='{$groupRow["group_id"]}' name='fun[{$groupRow["group_id"]}]' value='{$menu_key}'>{$checkValue}</label></div></li>";
                                }
                            }
                        }

                        echo "</ul></li>";
                    }else{
                        echo "<li class='list-group-item'>".$groupRow["group_name"]."<a href='javascript:void(0);' data-type='1' data-id='{$groupRow["group_id"]}' class='text-warning addGroupId'>（机器人 - 关）</a></li>";
                    }
                }
                echo "</ul>";
            }
            ?>
        </form>
    </div>
    <div class="col-lg-5 mb-20">
        <ul class="list-group" id="QQMsgUl">
            <li class="list-group-item title-li">
                <h4>最近的QQ消息：<small>（消息有5秒的延迟）</small></h4>
            </li>
        </ul>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">请选择需要发送到的群</h4>
                </div>
                <div class="modal-body">
                    <ul class="list-group">
                        <?php
                        if(is_array($groupList)&&!empty($groupList["data"])){
                            foreach ($groupList["data"] as $groupRow){
                                echo "<button class='list-group-item sendBtn' data-id='{$groupRow["group_id"]}'>".$groupRow["group_name"]."</button>";
                            }
                        }
                        ?>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
