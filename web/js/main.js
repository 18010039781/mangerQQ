
$(function ($) {
    //添加、删除管理的群组
    $(".addGroupId").on("click",function (even) {
        var group_id = $(this).data("id");
        var type = $(this).data("type");
        var data = {"group_id":group_id,"type":type,"fun":"addGroupId"};
        $.getJSON("./web/admin.php",data, function(json){
            if(json['status']==200){
                window.location.reload();
                even.stopPropagation();
            }
        });
    });

    $(".changeGroup").on("click",function () {
        var nextLi = $(this).next("li.menuGroup");
        if(nextLi.css("display")=="none"){
            $(".menuGroup").not(nextLi).slideUp("fast");
            nextLi.slideDown("fast");
        }else{
            nextLi.slideUp("fast");
        }
    });
    
    $(".auth-menu input[type='checkbox']").on("click",function () {
        var $authMenu = $(this).parents(".auth-menu:first");
        var groupId = $(this).data("group");
        if($(this).attr("name")=="all"){//所有
            var $checkList = $authMenu.find("input[type='checkbox']").not(this);
            if($(this).is(":checked")){
                $checkList.prop("checked",true);
            }else{
                $checkList.prop("checked",false);
            }
        }
        ajaxAutoGroup(groupId);
    });

    setInterval(ajaxMsgTimer,1000*10);
    ajaxMsgTimer();


    //发送消息
    $("#sendMsg").on("click",function () {
        var text = $("#sendText").val();
        var file = $("#sendFile").val();
        if(text==""&&file=="") {
            hindDiv("请输入文字或者选择文件");
        }else{
            $("#myModal").modal("show");
        }
    });
    $(".sendBtn").on("click",function () {
        var group_id = $(this).data("id");
        sendMsg(group_id);
    });
});

function sendMsg(groupId) {
    var text = $("#sendText").val();
    var file = $("#sendFile").val();
    var group_id = groupId;
    if(text==""&&file==""){
        hindDiv("请输入文字或者选择文件");
    }else{
        var fileList = $("#sendFile").get(0).files[0];
        $("#sendText").val("");
        $("#sendFile").val("");
        $("#myModal").modal("hide");
        if(fileList["type"] == "image/gif"||fileList["type"] == "image/png"||fileList["type"] == "image/jpg"||fileList["type"] == "image/jpeg"){
            var reader = new FileReader();
            reader.readAsDataURL(fileList);
            reader.onload = function(e){
                var data = {
                    "group_id":group_id,
                    "text":text,
                    "file":e.target.result
                }
                $.post("./web/admin.php?fun=sendMsg",data, function(json){
                    if(json['status']==200){
                        hindDiv("消息已发送");
                    }else{
                        hindDiv(json['error']);
                    }
                },"json");
            };
        }else{
            hindDiv("文件类型必须是png、jpg、jpeg格式");
        }
    }
}

function ajaxMsgTimer() {
    $.getJSON("./web/admin.php",{"fun":"ajaxMsg"}, function(json){
        if(json['status']==200){
            $("#QQMsgUl>.title-li").after(json["html"]);
        }else{
            hindDiv(json['error']);
        }
    });
}

function ajaxAutoGroup(groupId) {
    var form = $('#formAuth').serializeArray();
    var data = {
        "fun":"addAuth",
        'group_id':groupId,
        'auth_list':form
    };
    $.getJSON("./web/admin.php",data, function(json){
        if(json['status']==200){
            hindDiv("设置成功！");
        }else{
            hindDiv(json['error']);
        }
    });
}
Timer = "";
function hindDiv(str) {
    clearTimeout(Timer);
    var html = '<div class="hind-top" id="hindTitle">';
    html+='<div class="text-center text-success">'+str+'</div>';
    html+='</div>';
    if($("#hindTitle").length==1){
        $("#hindTitle>div").text(str);
        $("#hindTitle").stop().slideDown("fast");
    }else{
        $("body").append(html);
        $("#hindTitle").slideDown("fast");
    }
    Timer = setTimeout(function () {
        $("#hindTitle").stop().slideUp("fast");
    },2000);
}