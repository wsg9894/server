<?php /* Smarty version 2.6.14, created on 2017-12-06 16:41:52
         compiled from market/minus-add.html */ ?>
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
    <div class="x-body">
        <form class="layui-form">
          <div class="layui-form-item">
              <label for="username" class="layui-form-label">
                  <span class="x-red">*</span>下发总额
              </label>
              <div class="layui-input-inline">
                  <input type="text" id="username" name="username" required="" lay-verify="required"
                  autocomplete="off" class="layui-input">
              </div>
              <div class="layui-form-mid layui-word-aux">
                  <span class="x-red">*此活动优惠的总金额</span>
              </div>
          </div>
          <div class="layui-form-item">
              <label for="username" class="layui-form-label">
                  <span class="x-red">*</span>活动名称
              </label>
              <div class="layui-input-inline">
                  <input type="text" id="username" name="username" required="" lay-verify="required"
                  autocomplete="off" class="layui-input">
              </div>
              <div class="layui-form-mid layui-word-aux">
                  <span class="x-red">*最多输入30字</span>
              </div>
          </div>
          <div class="layui-form-item">
              <label for="phone" class="layui-form-label">
                  <span class="x-red">*</span>活动日期
              </label>
              <div class="layui-input-inline">
                  <input class="layui-input" placeholder="开始时间" name="start" id="start">
              </div>
              <div class="layui-input-inline">
                  <input class="layui-input" placeholder="结束时间" name="end" id="end">
              </div>
          </div>
          <div class="layui-form-item">
              <label for="username" class="layui-form-label">
                  <span class="x-red">*</span>活动时段
              </label>
              <div class="layui-input-block">
                <input type="radio" name="like1" title="不限">
                <span class="x-red">*不限表示0:00-23:59都生效</span> 
              </div>
              <div class="layui-input-block">
                <input type="radio" name="like1" title="范围">
                <span class="x-red">*对应场次的放映是时间，前后均包含，注意不要出现重复的时间段</span>
              </div>          
          </div>
          <div class="layui-form-item">
              <label for="username" class="layui-form-label">
                  活动星期
              </label>
              <div class="layui-input-block">
                <input type="checkbox" name="like1[]" lay-skin="primary" title="不限">
                <input type="checkbox" name="like1[]" lay-skin="primary" title="星期一">
                <input type="checkbox" name="like1[]" lay-skin="primary" title="星期二">
                <input type="checkbox" name="like1[]" lay-skin="primary" title="星期三">
                <input type="checkbox" name="like1[]" lay-skin="primary" title="星期四">
                <input type="checkbox" name="like1[]" lay-skin="primary" title="星期五">
                <input type="checkbox" name="like1[]" lay-skin="primary" title="星期六">
                <input type="checkbox" name="like1[]" lay-skin="primary" title="星期日">
              </div>
          </div>
          <div class="layui-form-item">
              <label for="username" class="layui-form-label">
                  <span class="x-red">*</span>适用用户
              </label>
              <div class="layui-input-block">
                <input type="radio" name="like1" title="全部用户">
                <input type="radio" name="like1" title="仅新用户">
                <input type="radio" name="like1" title="仅老用户">
                <span class="x-red">*新用户：注册后没有买过票的用户 老用户：注册后买过票的用户</span>
              </div>
          </div>
          <div class="layui-form-item">
              <label for="L_email" class="layui-form-label">
                  <span class="x-red">*</span>单张立减金额
              </label>
              <!-- <div class="layui-input-block">
                票价 > =
                  <input type="text" id="L_email" name="email" class="layui-input">时，单张立减
                  <input type="text" id="L_email" name="email" class="layui-input">元
              </div> -->
              <div class="layui-form-mid layui-word-aux">
                  <span class="x-red">*规则说明<br>1、阶梯票价设置：最多5个阶梯;<br>2、不在区间内的价格则不参加活动<br>3、当立减价格大于等于票价时做出确认提示：当前立减金额过大会导致0元出票，是否确认！</span>
              </div>
          </div>
        <div><hr>
          <b>限制条件：</b>
        </div>
          <div class="layui-form-item layui-form-text">
              <label for="desc" class="layui-form-label">
                  不能同时享受的活动ID
              </label>
              <div class="layui-input-inline">
                <input type="text" id="username" name="username" required="" lay-verify="required"
                  autocomplete="off" class="layui-input">
              </div>
              <div class="layui-form-mid layui-word-aux">
                  <span class="x-red">*非必填项，最多10个ID，多个之间用英文逗号隔开，用户享受当前活动后就不能再享受配置ID的活动了</span>
              </div>
          </div>
          <div class="layui-form-item layui-form-text">
              <label for="desc" class="layui-form-label">
                  <span class="x-red">*</span>每影院上限
              </label>
              <div class="layui-input-inline">
                <input type="text" id="username" name="username" required="" lay-verify="required" autocomplete="off" class="layui-input">
              </div><div class="layui-input-inline">张</div>
              <div class="layui-form-mid layui-word-aux">
                  <span class="x-red">*每个影院享受优惠资格的次数，0为不限</span>
              </div>
          </div>
          <div class="layui-form-item layui-form-text">
              <label for="desc" class="layui-form-label">
                  <span class="x-red">*</span>每场次上限
              </label>
              <div class="layui-input-inline">
                <input type="text" id="username" name="username" required="" lay-verify="required" autocomplete="off" class="layui-input">
              </div><div class="layui-input-inline">张</div>
              <div class="layui-form-mid layui-word-aux">
                  <span class="x-red">*每个场次享受优惠资格的次数，0为不限，最大为200</span>
              </div>
          </div>
          <div class="layui-form-item layui-form-text">
              <label for="desc" class="layui-form-label">
                  <span class="x-red">*</span>每人上限
              </label>
              <div class="layui-input-inline">
                <input type="text" id="username" name="username" required="" lay-verify="required" autocomplete="off" class="layui-input">
              </div><div class="layui-input-inline">张</div>              
              <div class="layui-form-mid layui-word-aux">
                  <span class="x-red">*每个人享受优惠资格的次数，0为不限</span>
              </div>
          </div>
          <div class="layui-form-item">
              <label for="username" class="layui-form-label">
                  <span class="x-red">*</span>适用影院
              </label>
              <div class="layui-input-block">
                <input type="radio" name="like1" title="不限">
                <input type="radio" name="like1" title="选择影院">
                <a href="" style="color:blue">导入影院ID</a>
                <span class="x-red">*使用导入功能时，需要导入excel格式，影院名称+专资编码</span>
              </div>
          </div>
          <div class="layui-form-item">
              <label for="username" class="layui-form-label">
                 <span class="x-red">*</span>适用影片
              </label>
              <div class="layui-input-block">
                <input type="checkbox" name="like1[]" lay-skin="primary" title="不限">
                <input type="checkbox" name="like1[]" lay-skin="primary" title="指定影片">
                <span class="x-red">*模糊匹配，输入名称后给出推荐的影片名称供选择</span>
              </div>
          </div>
          <div class="layui-form-item">
              <label for="username" class="layui-form-label">
                 <span class="x-red">*</span>适用版本
              </label>
              <div class="layui-input-block">
                <input type="checkbox" name="like1[]" lay-skin="primary" title="不限">
                <input type="checkbox" name="like1[]" lay-skin="primary" title="胶片">
                <input type="checkbox" name="like1[]" lay-skin="primary" title="2D">
                <input type="checkbox" name="like1[]" lay-skin="primary" title="3D">
                <input type="checkbox" name="like1[]" lay-skin="primary" title="IMAX">
                <input type="checkbox" name="like1[]" lay-skin="primary" title="IMAX3D">
              </div>
          </div><hr>
          <div><b>优惠标签设置：</b></div>
          <div class="layui-form-item">
              <label for="username" class="layui-form-label">
                 <span class="x-red">*</span>优惠标签
              </label>
              <div class="layui-input-block">
                <input type="checkbox" name="like1[]" lay-skin="primary" title="显示">
                <input type="checkbox" name="like1[]" lay-skin="primary" title="不显示">
                <span class="x-red">*影院列表页、影院详情页</span>
              </div>
          </div>
          <div class="layui-form-item">
              <label for="phone" class="layui-form-label">
                  <span class="x-red">*</span>优惠标题
              </label>
              <div class="layui-input-inline">
                  <input type="text" id="title" name="title" required="" lay-verify="phone"
                  autocomplete="off" class="layui-input">
              </div>
              <div class="layui-form-mid layui-word-aux">
                  <span class="x-red">*</span>应用在影院详情页、提交订单页，最多25个字，用于录入活动限制条件
              </div>
          </div>
          <div class="layui-form-item layui-form-text">
              <label for="desc" class="layui-form-label">
                  <span class="x-red">*</span>优惠详情
              </label>
              <div class="layui-input-block">
                  <textarea placeholder="最多500字，用户详细描述活动规则" id="desc" name="desc" class="layui-textarea"></textarea>
              </div>
          </div><hr>
          <div class="layui-form-item">
              <label for="username" class="layui-form-label">
                 <span class="x-red">*</span>适用渠道
              </label>
              <div class="layui-input-block">
                <input type="checkbox" name="like1[]" lay-skin="primary" title="不限">
                <input type="checkbox" name="like1[]" lay-skin="primary" title="所有外部渠道">
                <input type="checkbox" name="like1[]" lay-skin="primary" title="渠道名称">
                <input type="checkbox" name="like1[]" lay-skin="primary" title="渠道名称">
                <input type="checkbox" name="like1[]" lay-skin="primary" title="渠道名称">
                <input type="checkbox" name="like1[]" lay-skin="primary" title="渠道名称">
              </div>
          </div><hr>
          <div><b>库存预警：</b><span class="x-red">默认预警阈值设定在活动总金额的5%，当小于5%时进行预警</span></div>
          <div class="layui-form-item">
              <label for="phone" class="layui-form-label">
                  <span class="x-red">*</span>提醒邮箱
              </label>
              <div class="layui-input-inline">
                  <input type="text" id="email" name="email" required="" lay-verify="email"
                  autocomplete="off" class="layui-input">
              </div>
              <div class="layui-form-mid layui-word-aux">
                  <span class="x-red">*</span>填写接收预警的邮箱
              </div>
          </div>
          <div class="layui-form-item">
              <label for="phone" class="layui-form-label">
                  <span class="x-red">*</span>提醒手机
              </label>
              <div class="layui-input-inline">
                  <input type="text" id="phone" name="phone" required="" lay-verify="phone"
                  autocomplete="off" class="layui-input">
              </div>
              <div class="layui-form-mid layui-word-aux">
                  <span class="x-red">*</span>填写接收预警的手机号码
              </div>
          </div>
          <div class="layui-form-item">
              <label for="L_repass" class="layui-form-label">
              </label>
              <button  class="layui-btn" lay-filter="add" lay-submit="">
                  增加
              </button>
          </div>
      </form>
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
        layui.use(['form','layer'], function(){
            $ = layui.jquery;
          var form = layui.form
          ,layer = layui.layer;
        
          //自定义验证规则
          form.verify({
            nikename: function(value){
              if(value.length < 5){
                return '昵称至少得5个字符啊';
              }
            }
            ,pass: [/(.+){6,12}$/, '密码必须6到12位']
            ,repass: function(value){
                if($('#L_pass').val()!=$('#L_repass').val()){
                    return '两次密码不一致';
                }
            }
          });

          //监听提交
          form.on('submit(add)', function(data){
            console.log(data);
            //发异步，把数据提交给php
            layer.alert("增加成功", {icon: 6},function () {
                // 获得frame索引
                var index = parent.layer.getFrameIndex(window.name);
                //关闭当前frame
                parent.layer.close(index);
            });
            return false;
          });
          
          
        });
    </script>
    <script>var _hmt = _hmt || []; (function() {
        var hm = document.createElement("script");
        hm.src = "https://hm.baidu.com/hm.js?b393d153aeb26b46e9431fabaf0f6190";
        var s = document.getElementsByTagName("script")[0];
        s.parentNode.insertBefore(hm, s);
      })();</script>
  </body>

</html>