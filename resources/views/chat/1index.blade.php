<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>聊天室</title>
    <link href="static/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="static/css/font-awesome.min.css?v=4.4.0" rel="stylesheet">
    <link href="static/css/animate.min.css" rel="stylesheet">
    <link href="static/css/style.min.css?v=4.1.0" rel="stylesheet">
    <link href="static/layer/skin/default/layer.css" rel="stylesheet">
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content  animated fadeInRight">
    <div class="row">
        <div class="col-sm-3"></div>
        <div class="col-sm-6">
            <div class="ibox chat-view">
                <div class="ibox-title">
                    <a href="javascript:loginOut();" class="roll-nav roll-right J_tabExit" style="float: right;height: 20px"><i class="fa fa fa-sign-out"></i> 退出</a>
                    <span id="tips">聊天窗口</span><marquee width="60%"></marquee>在线人数:<span id="onlinePeople"></span>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-md-9 ">
                            <div class="chat-discussion" id="chatbox" style="height: 650px">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="chat-users">
                                <div class="users-list">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="chat-message-form">
                                <div class="form-group">
                                    <textarea class="form-control message-input" name="message" placeholder="输入消息内容，按回车键发送" id="message"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- login box -->
<div class="ibox-content" style="display: none;width:350px;height:150px" id="loginBox">
    <form class="form-horizontal">
        <div class="form-group">
            <label class="col-sm-3 control-label">用户名：</label>

            <div class="col-sm-8">
                <input type="text" placeholder="用户名" class="form-control" id="uname">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-8">
                <button class="btn btn-sm btn-primary m-t-n-xs" type="button" id="lgbtn">登 录</button>
            </div>
        </div>
    </form>
</div>

<script src="static/js/jquery.min.js?v=2.1.4"></script>
<script src="static/js/bootstrap.min.js?v=3.3.6"></script>
<script src="static/layer/layer.js"></script>
<script type="text/javascript">

    var logbox;
    $(function(){
        //check login
        checkLogin();
        //send message
        document.getElementById('message').addEventListener('keydown',function(e){
            if(e.keyCode!=13) return;
            e.preventDefault();  //取消事件的默认动作
            sendMessage();
            this.value = '';
        });

        //login
        $("#lgbtn").click(function(){
            if( $("#uname").val() == '' ){
                layer.alert('用户名不能为空', {"title":"友情提示", "icon":2});
                return false;
            }
            doLogin( $("#uname").val() );
            layer.close( logbox );
        });

    });

    //登录操作
    function showLogin(){

        logbox = layer.open({
            type: 1,
            title:'欢迎加入聊天室',
            skin: 'layui-layer-demo', //加上边框
            closeBtn: 0, //不显示关闭按钮
            area: ['380px', '200px'], //宽高
            content: $("#loginBox")
        });

    }
    //检查登录
    function checkLogin(){
        var user_info = getLocalStorage();
        if( !user_info ){
            showLogin();
        }else{
            doLogin( user_info.name );
        }

        return user_info;
    }

    function doLogin( name ){
        /**
         * 与GatewayWorker建立websocket连接，域名和端口改为你实际的域名端口，
         * 其中端口为Gateway端口，即start_gateway.php指定的端口。
         * start_gateway.php 中需要指定websocket协议，像这样
         * $gateway = new Gateway(websocket://0.0.0.0:7272);重点
         */
        ws = new WebSocket('ws://127.0.0.1:8282');
        //获取头像
        var user_info = getLocalStorage();
        if(!user_info){
            var avar = parseInt(Math.random() * 10);
            if( avar == 0 ) avar = 1;
            avar = 'a' + avar + '.jpg';
        }else{
            var avar = user_info.avar;
        }
        /* 0代表加载的风格，支持0-2 */
        var loading = layer.load(0, {shade: false});
        ws.onopen = function(){
            layer.close(loading);
            $("#tips").text('您好：' + name);
            localStorage.setItem('userInfo', '{"name" : "' + name + '", "avar" : "' + avar + '"}');
        };

        /* 服务端主动推送消息时会触发这里的onmessage */
        ws.onmessage = function(e){
            // json数据转换成js对象
            var data = eval("("+e.data+")");
            showOnlineUser(data);
            var type = data.type || '';
            switch(type){
                /* 将client_id发给后台进行uid绑定 */
                case 1:
                    if(data.msg=='上线'){
                        tellOnline( data.user );
                        updateOnlinePeople(data.count);
                    }else{
                        $(function(){
                            var url = '/bind';
                            $.ajax({
                                type:'get',
                                url:url,
                                data:{ client_id:data.client_id, user:name, avar:avar },
                                dataType:'json',
                                success:function(result){
                                }
                            });
                        });
                    }
                    break;
                case 2:
                    var msg = JSON.parse(data.message);
                    var message = parseMessage( msg.user, msg.stime, msg.avar, msg.msg );
                    $("#chatbox").append( message );
                    var ex = document.getElementById("chatbox");
                    ex.scrollTop = ex.scrollHeight;
                    break;
                case 3:
                    updateOnlinePeople(data.count);
                    tellOutline( data.user );
                    $(".chat-user").each(function(){
                        var uid = $(this).attr('data-uid');
                        console.log(uid);
                        console.log(data.uid);
                        if(uid==data.uid){
                            $(this).html();
                        }
                    })
                    break;
                case 4:
                    tellNotice(data.message);
                    break;
                default :
                    console.log(data);
                    break;
            }
        };
    }

    /* 获取localStorage */
    function getLocalStorage(){
        if(localStorage.getItem("userInfo")){
            return $.parseJSON( localStorage.getItem("userInfo") );
        }else{
            return false;
        }
    }
    /* 提示上线 */
    function tellOnline( name ){
        layer.msg( name + '上线了', {time : 3000});
    }
    //提示下线
    function tellOutline( name ){
        layer.msg( name + '下线了', {time : 1000});
    }
    //定时通知
    function tellNotice( msg ){
        $('marquee').text(msg);
    }
    //更新在线人数
    function updateOnlinePeople( count ){
        $('#onlinePeople').text(count);
    }

    //发送消息
    function sendMessage(){

        //format date
        Date.prototype.format =function(format)
        {
            var o = {
                "M+" : this.getMonth()+1, //month
                "d+" : this.getDate(), //day
                "h+" : this.getHours(), //hour
                "m+" : this.getMinutes(), //minute
                "s+" : this.getSeconds(), //second
                "q+" : Math.floor((this.getMonth()+3)/3), //quarter
                "S" : this.getMilliseconds() //millisecond
            }
            if(/(y+)/.test(format)) format=format.replace(RegExp.$1,
                (this.getFullYear()+"").substr(4- RegExp.$1.length));
            for(var k in o)if(new RegExp("("+ k +")").test(format))
                format = format.replace(RegExp.$1,
                    RegExp.$1.length==1? o[k] :
                        ("00"+ o[k]).substr((""+ o[k]).length));
            return format;
        }

        var times = new Date().format("yyyy-MM-dd hh:mm:ss");
        var userinfo = localStorage.getItem("userInfo")?true:false;//是否登陆
        if( userinfo ){
            userinfo = $.parseJSON(localStorage.getItem("userInfo"));
            //socket send
            var msg = '{"user" : "' + userinfo.name + '", "avar" : "' + userinfo.avar + '", "stime" : "'
                + times + '", "msg": "' + $("#message").val() + '"}';
            ws.send( msg );
        }
    }
    //解析消息发送样式
    function parseMessage( user, time, avar, message ){

        var _html = '<div class="chat-message"><img class="message-avatar" src="static/img/' + avar + '" alt="">';
        _html += '<div class="message"><a class="message-author" href="#"> ' + user + '</a>';
        _html += '<span class="message-date"> ' + time + ' </span>';
        _html += '<span class="message-content">' + message + '</span></div></div>';

        return _html;
    }

    //退出登录
    function loginOut(){
        localStorage.setItem('userInfo', '');
        layer.load(0, {shade: false}); //0代表加载的风格，支持0-2
        setTimeout( function(){
            window.location.reload();
        }, 2);
    }

    //展示在线人员
    function showOnlineUser( info ){
        if(info.all_client_info){
            console.log(info);
            var _html = '';
            $.each( info.all_client_info, function(k, v){
                _html += '<div class="chat-user" data-uid="'+v.uid+'"><span class="pull-right label label-primary">在线</span>';
                _html += '<img class="chat-avatar" src="static/img/' + v.avar + '" alt=""><div class="chat-user-name">';
                _html += '<a href="#">' + v.user + '</a></div></div>';
            });
            console.log(_html);
            $(".users-list").html( _html );
        }
    }
</script>
</body>
</html>