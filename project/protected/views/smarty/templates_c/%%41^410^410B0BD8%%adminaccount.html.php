<?php /* Smarty version 2.6.14, created on 2017-12-13 09:42:40
         compiled from market/adminaccount.html */ ?>
<!DOCTYPE html>
<html>
  
  <head>
    <meta charset="UTF-8">
    <title>欢迎页面-X-admin2.0</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
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
        <a href="">权限管理</a>
        <a>
          <cite>账户列表</cite></a>
      </span>
      <a class="layui-btn layui-btn-small" style="line-height:1.6em;margin-top:3px;float:right" href="javascript:location.replace(location.href);" title="刷新">
        <i class="layui-icon" style="line-height:30px">ဂ</i></a>
    </div>
    <div class="x-body">
      <div class="layui-row">
        <form class="layui-form layui-col-md12 x-so">
          <input type="text" name="realname" id="realname"  placeholder="请输入真实姓名" autocomplete="off" class="layui-input">
          <input type="text" name="email" id="email"  placeholder="请输入用户邮箱" autocomplete="off" class="layui-input">
          <div class="layui-input-inline">
            <select name="status" id="status">
              <option>状态</option>
              <option value="1">已启用</option>
              <option value="0">已停用</option>
            </select>
          </div>
          <button class="layui-btn" id="search">搜索</button>
        </form>
      </div>
      <xblock>
        <button class="layui-btn" onclick="x_admin_show('添加用户','index.php?r=market/Admin/Adminadd',600,400)"><i class="layui-icon"></i>添加</button>
        <span class="x-right" style="line-height:40px">共有数据：<?php echo $this->_tpl_vars['COUNT']; ?>
 条</span>
      </xblock>
      <table class="layui-table">
        <thead>
          <tr>
            <th>真实姓名</th>
            <th>用户邮箱</th>
            <th>角色</th>
            <th>状态</th>
            <th>注册时间</th>
            <th>操作</th></tr>
        </thead>
        <tbody>
        <?php $_from = ($this->_tpl_vars['USER']); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['USER']):
?>
          <tr>
            <td><?php echo $this->_tpl_vars['USER']['admin_realname']; ?>
</td>
            <td><?php echo $this->_tpl_vars['USER']['admin_user_email']; ?>
</td>
            <td><?php echo $this->_tpl_vars['USER']['role_name']; ?>
</td>
            <td class="td-status">
              <?php if ($this->_tpl_vars['USER']['admin_status'] == 1): ?>
              <span class="layui-btn layui-btn-normal layui-btn-mini">已启用</span>
              <?php else: ?>
              <span class="layui-btn layui-btn-normal layui-btn-mini layui-btn-disabled">已停用</span>
              <?php endif; ?>
            </td>
            <td><?php echo $this->_tpl_vars['USER']['admin_createtime']; ?>
</td>
            <td class="td-manage">
              <?php if ($this->_tpl_vars['USER']['admin_status'] == 1): ?>
              <a onclick="member_stop(this,'<?php echo $this->_tpl_vars['USER']['admin_user_id']; ?>
')" href="javascript:;" status="<?php echo $this->_tpl_vars['USER']['admin_status']; ?>
" title="停用">
                <i class="layui-icon">&#xe601;</i>
              </a>
              <?php else: ?>
              <a onclick="member_stop(this,'<?php echo $this->_tpl_vars['USER']['admin_user_id']; ?>
')" href="javascript:;" status="<?php echo $this->_tpl_vars['USER']['admin_status']; ?>
" title="启用">
                <i class="layui-icon">&#xe62f;</i>
              </a>
              <?php endif; ?>

                <a title="修改"  onclick="x_admin_show('修改','index.php?r=market/Admin/Adminedit&admin_user_id=<?php echo $this->_tpl_vars['USER']['admin_user_id']; ?>
',600,400)" href="javascript:;">
                <i class="layui-icon">&#xe642;</i>
              </a>
            </td>
          </tr>
        <?php endforeach; endif; unset($_from); ?>
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
          if($(obj).attr('title')=='启用'){

            //发异步把用户状态进行更改
            $(obj).attr('title','停用')
            $(obj).find('i').html('&#xe601;');

            $(obj).parents("tr").find(".td-status").find('span').removeClass('layui-btn-disabled').html('已启用');
            layer.msg('已启用!',{icon: 5,time:1000});
              $.post("index.php?r=market/Admin/Savestatus",{adminID:id,adminStatus:adminStatus},function(data,status){
                  console.log(status)
              })
          }else{
            $(obj).attr('title','启用')
            $(obj).find('i').html('&#xe62f;');

            $(obj).parents("tr").find(".td-status").find('span').addClass('layui-btn-disabled').html('已停用');
            layer.msg('已停用!',{icon: 5,time:1000});
              var adminStatus = $(obj).attr('status')
              $.post("index.php?r=market/Admin/Savestatus",{adminID:id,adminStatus:adminStatus},function(data,status){
                  console.log(status)
              })
          }
      }

      $('#search').on('click',function(){
          var realname = $('#realname').val()
          var email = $('#email').val()
          var status = $('#status option:selected').val()
          alert(realname)
          alert(email)
          alert(status)
//          $.post('index.php?r=market/Admin/Search',{realname:realname,email:email,status:status},function(data,status){
//              console.log(data)
//          },'json')
      })

    </script>

  </body>

</html>