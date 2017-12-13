<?php /* Smarty version 2.6.14, created on 2017-12-13 09:42:37
         compiled from market/index.html */ ?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>后台登录-X-admin2.0</title>
	<meta name="renderer" content="webkit|ie-comp|ie-stand">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="Cache-Control" content="no-siteapp" />

    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" href="css/market/font.css">
	<link rel="stylesheet" href="css/market/xadmin.css">
    <script type="text/javascript" src="https://cdn.bootcss.com/jquery/3.2.1/jquery.min.js"></script>
    <script src="lib/layui/layui.js" charset="utf-8"></script>
    <script type="text/javascript" src="js/market/xadmin.js"></script>

</head>
<body>
    <!-- 顶部开始 -->
    <div class="container">
        <div class="logo"><a href="index.php?r=market/Index/index">XXX运营系统</a></div>
        <div class="left_open">
            <i title="展开左侧栏" class="iconfont">&#xe699;</i>
        </div>
        <ul class="layui-nav right" lay-filter="">
          <li class="layui-nav-item">
            <a href="javascript:;"><?php echo $this->_tpl_vars['SESS']['adminUserName']; ?>
</a>
            <dl class="layui-nav-child"> <!-- 二级菜单 -->
              <dd><a onclick="x_admin_show('修改密码','http://www.baidu.com')">修改密码</a></dd>
              <dd><a href="index.php?r=market/Login/Logout">退出</a></dd>
            </dl>
          </li>
        </ul>
        
    </div>
    <!-- 顶部结束 -->
    <!-- 中部开始 -->
     <!-- 左侧菜单开始 -->
    <div class="left-nav">
      <div id="side-nav">
        <ul id="nav">
            <li>
                <a href="javascript:;">
                    <i class="iconfont">&#xe6b8;</i>
                    <cite>权限管理</cite>
                    <i class="iconfont nav_right">&#xe697;</i>
                </a>
                <ul class="sub-menu">
                    <li>
                        <a _href="index.php?r=market/Admin/Adminaccount">
                            <i class="iconfont">&#xe6a7;</i>
                            <cite>账户列表</cite>  
                        </a>
                    </li >
                    <li>
                        <a _href="index.php?r=market/Admin/Rolelist">
                            <i class="iconfont">&#xe6a7;</i>
                            <cite>角色管理</cite>
                        </a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="javascript:;">
                    <i class="iconfont">&#xe723;</i>
                    <cite>影院管理</cite>
                    <i class="iconfont nav_right">&#xe697;</i>
                </a>
                <ul class="sub-menu">
                    <li>
                        <a _href="index.php?r=market/Index/Cinemalist">
                            <i class="iconfont">&#xe6a7;</i>
                            <cite>影院资料</cite>
                        </a>
                    </li >
                    <li>
                        <a _href="index.php?r=market/Index/Cinemamatch">
                            <i class="iconfont">&#xe6a7;</i>
                            <cite>影院匹配</cite>
                        </a>
                    </li >
                    <li>
                        <a _href="index.php?r=market/Index/Orderlist">
                            <i class="iconfont">&#xe6a7;</i>
                            <cite>影片资料</cite>
                        </a>
                    </li >
                    <li>
                        <a _href="index.php?r=market/Index/Schedule">
                            <i class="iconfont">&#xe6a7;</i>
                            <cite>场次信息</cite>
                        </a>
                    </li >
                </ul>
            </li>
            <li>
                <a href="javascript:;">
                    <i class="iconfont">&#xe6b8;</i>
                    <cite>活动管理</cite>
                    <i class="iconfont nav_right">&#xe697;</i>
                </a>
                <ul class="sub-menu">
                    <li>
                        <a _href="index.php?r=market/Index/Minus">
                            <i class="iconfont">&#xe6a7;</i>
                            <cite>立减</cite>                   
                        </a>
                    </li >
                    <li>
                        <a _href="index.php?r=market/Index/Bereduced">
                            <i class="iconfont">&#xe6a7;</i>
                            <cite>减至</cite> 
                        </a>
                    </li>
                    <li>
                        <a _href="index.php?r=market/Index/Bereduced">
                            <i class="iconfont">&#xe6a7;</i>
                            <cite>广告位管理</cite> 
                        </a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="javascript:;">
                    <i class="iconfont">&#xe6ce;</i>
                    <cite>渠道管理</cite>
                    <i class="iconfont nav_right">&#xe697;</i>
                </a>
                <ul class="sub-menu">
                    <li>
                        <a _href="index.php?r=market/Index/Channel">
                            <i class="iconfont">&#xe6a7;</i>
                            <cite>渠道定价</cite>
                        </a>
                    </li >
                </ul>
            </li>
            <li>
                <a href="javascript:;">
                    <i class="iconfont">&#xe6ce;</i>
                    <cite>会员管理</cite>
                    <i class="iconfont nav_right">&#xe697;</i>
                </a>
                <ul class="sub-menu">
                    <li>
                        <a _href="index.php?r=market/Index/Admindata">
                            <i class="iconfont">&#xe6a7;</i>
                            <cite>会员资料</cite>
                        </a>
                    </li >
                </ul>
            </li>
            <li>
                <a href="javascript:;">
                    <i class="iconfont">&#xe6ce;</i>
                    <cite>订单操作</cite>
                    <i class="iconfont nav_right">&#xe697;</i>
                </a>
                <ul class="sub-menu">
                    <li>
                        <a _href="index.php?r=market/Index/Admindata">
                            <i class="iconfont">&#xe6a7;</i>
                            <cite>订单查询</cite>
                        </a>
                    </li >
                </ul>
            </li>
            <li>
                <a href="javascript:;">
                    <i class="iconfont">&#xe6ce;</i>
                    <cite>数据报表</cite>
                    <i class="iconfont nav_right">&#xe697;</i>
                </a>
                <ul class="sub-menu">
                    <li>
                        <a _href="index.php?r=market/Index/Admindata">
                            <i class="iconfont">&#xe6a7;</i>
                            <cite>电影订单</cite>
                        </a>
                    </li >
                </ul>
            </li>
        </ul>
      </div>
    </div>
    <!-- <div class="x-slide_left"></div> -->
    <!-- 左侧菜单结束 -->
    <!-- 右侧主体开始 -->
    <div class="page-content">
        <div class="layui-tab tab" lay-filter="xbs_tab" lay-allowclose="false">
          <ul class="layui-tab-title">
            <li>我的桌面</li>
          </ul>
          <div class="layui-tab-content">
            <div class="layui-tab-item layui-show">
                <iframe src="index.php?r=market/Index/Welcome" frameborder="0" scrolling="yes" class="x-iframe"></iframe>
            </div>
          </div>
        </div>
    </div>
    <div class="page-content-bg"></div>
    <!-- 右侧主体结束 -->
    <!-- 中部结束 -->
    <!-- 底部开始 -->
    <div class="footer">
        <div class="copyright">Copyright ©2017 x-admin v2.3 All Rights Reserved</div>  
    </div>
    <!-- 底部结束 -->
    <script>
    //百度统计可去掉
    var _hmt = _hmt || [];
    (function() {
      var hm = document.createElement("script");
      hm.src = "https://hm.baidu.com/hm.js?b393d153aeb26b46e9431fabaf0f6190";
      var s = document.getElementsByTagName("script")[0]; 
      s.parentNode.insertBefore(hm, s);
    })();
    </script>
</body>
</html>