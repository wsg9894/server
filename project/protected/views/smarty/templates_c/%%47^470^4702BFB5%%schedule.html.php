<?php /* Smarty version 2.6.14, created on 2017-12-06 16:48:26
         compiled from market/schedule.html */ ?>
<!DOCTYPE html>
<html>
  
  <head>
    <meta charset="UTF-8">
    <title>欢迎页面-X-admin2.0</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width,user-scalable=yes, minimum-scale=0.4, initial-scale=0.8,target-densitydpi=low-dpi" />
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" href="css/market/font.css">
    <link rel="stylesheet" href="css/market/xadmin.css">
    <script type="text/javascript" src="https://cdn.bootcss.com/jquery/3.2.1/jquery.min.js"></script>
    <script type="text/javascript" src="lib/layui/layui.js" charset="utf-8"></script>
    <script type="text/javascript" src="js/market/xadmin.js"></script>
    <!-- 让IE8/9支持媒体查询，从而兼容栅格 -->
    <!--[if lt IE 9]>
      <script src="https://cdn.staticfile.org/html5shiv/r29/html5.min.js"></script>
      <script src="https://cdn.staticfile.org/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  
  <body>
    <div class="x-nav">
      <span class="layui-breadcrumb">
        <a href="">首页</a>
        <a href="">影院管理</a>
        <a>
          <cite>场次信息</cite></a>
      </span>
      <a class="layui-btn layui-btn-small" style="line-height:1.6em;margin-top:3px;float:right" href="javascript:location.replace(location.href);" title="刷新">
        <i class="layui-icon" style="line-height:30px">ဂ</i></a>
    </div>
    <div class="x-body">
      <div class="layui-row">
        <form class="layui-form layui-col-md12 x-so">
          <input class="layui-input" placeholder="放映日期" name="start" id="start">
          <input type="text" name="username"  placeholder="请输入影院ID" autocomplete="off" class="layui-input">
          <input type="text" name="username"  placeholder="请输入影片名称" autocomplete="off" class="layui-input">
          <input type="text" name="username"  placeholder="请输入所属热线" autocomplete="off" class="layui-input">
          <input type="text" name="username"  placeholder="请输入所在省市" autocomplete="off" class="layui-input">
          <button class="layui-btn"  lay-submit="" lay-filter="sreach"><i class="layui-icon">&#xe615;</i></button>
        </form>
      </div>
      <xblock>
        <span class="x-right" style="line-height:40px">共有数据：88 条</span>
      </xblock>
      <table class="layui-table">
        <thead>
          <tr>
            <th>供应商</th>
            <th>排期ID</th>
            <th>影院ID</th>
            <th>影院名称</th>
            <th>放映日期</th>
            <th>影厅</th>
            <th>影片</th>
            <th>语言/版本</th>
            <th>挂牌价</th>
            <th>保护价</th>
            <th>结算价</th>
            <th>状态</th>
            <th>操作</th>
            </tr>
        </thead>
        <tbody>
          <tr>
            <td>2017009171822298053</td>
            <td>老王:18925139194</td>
            <td>7829.10</td>
            <td>7854.10</td>
            <td>待确认</td>
            <td>未支付</td>
            <td>未发货</td>
            <td>其他方式</td>
            <td>申通物流</td>
            <td>2017-08-17 18:22</td>
            <td>申通物流</td>
            <td class="td-status">
              <span class="layui-btn layui-btn-normal layui-btn-mini">停售</span>
            </td>
            <td class="td-manage">
              <a title="查看"  onclick="x_admin_show('日志','order-view.html')" href="javascript:;">
                <i class="layui-icon">&#xe63c;</i>
              </a>
              <a onclick="member_stop(this,'10001')" href="javascript:;"  title="开售">
                <i class="layui-icon">&#xe601;</i>
              </a>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="page">
        <div>
          <a class="prev" href="">&lt;&lt;</a>
          <a class="num" href="">1</a>
          <span class="current">2</span>
          <a class="num" href="">3</a>
          <a class="num" href="">489</a>
          <a class="next" href="">&gt;&gt;</a>
        </div>
      </div>

    </div>
    <script>
      layui.use('laydate', function(){
        var laydate = layui.laydate;
        
        //执行一个laydate实例
        laydate.render({
          elem: '#start' //指定元素
        });

        //执行一个laydate实例
        laydate.render({
          elem: '#end' //指定元素
        });
      });

       /*用户-停用*/
      function member_stop(obj,id){

              if($(obj).attr('title')=='开售'){

                //发异步把用户状态进行更改
                $(obj).attr('title','停售')
                $(obj).find('i').html('&#xe62f;');

                $(obj).parents("tr").find(".td-status").find('span').addClass('layui-btn-disabled').html('停售');
                layer.msg('停售',{icon: 5,time:1000});

              }else{
                $(obj).attr('title','开售')
                $(obj).find('i').html('&#xe601;');

                $(obj).parents("tr").find(".td-status").find('span').removeClass('layui-btn-disabled').html('开售');
                layer.msg('开售!',{icon: 5,time:1000});
              }
      }

    </script>
    <script>var _hmt = _hmt || []; (function() {
        var hm = document.createElement("script");
        hm.src = "https://hm.baidu.com/hm.js?b393d153aeb26b46e9431fabaf0f6190";
        var s = document.getElementsByTagName("script")[0];
        s.parentNode.insertBefore(hm, s);
      })();</script>
  </body>

</html>