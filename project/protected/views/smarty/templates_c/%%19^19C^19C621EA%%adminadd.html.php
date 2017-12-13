<?php /* Smarty version 2.6.14, created on 2017-12-12 09:32:14
         compiled from market/adminadd.html */ ?>
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
    <div class="x-body">
        <form class="layui-form">
          <div class="layui-form-item">
              <label for="L_email" class="layui-form-label">
                  <span class="x-red">*</span>用户邮箱
              </label>
              <div class="layui-input-inline">
                  <input type="text" id="L_email" name="email" required="" lay-verify="email"
                  autocomplete="off" class="layui-input">
              </div>
          </div>
          <div class="layui-form-item">
              <label for="L_pass" class="layui-form-label">
                  <span class="x-red">*</span>密码
              </label>
              <div class="layui-input-inline">
                  <input type="password" id="L_pass" name="password" required="" lay-verify="password"
                  autocomplete="off" class="layui-input">
              </div>
          </div>
          <div class="layui-form-item">
              <label for="L_realname" class="layui-form-label">
                  <span class="x-red">*</span>真实姓名
              </label>
              <div class="layui-input-inline">
                  <input type="text" id="L_realname" name="realname" required="" lay-verify="realname"
                  autocomplete="off" class="layui-input">
              </div>
          </div>
          
          <div class="layui-form-item">
              <label for="L_role" class="layui-form-label">
                  <span class="x-red">*</span>角色
              </label>
              <div class="layui-input-inline">
                <select name="role" id="L_role">
                  <option>全部</option>
                  <?php $_from = ($this->_tpl_vars['ROLE']); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['RO']):
?>
                    <option value="<?php echo $this->_tpl_vars['RO']['role_id']; ?>
"><?php echo $this->_tpl_vars['RO']['role_name']; ?>
</option>
                  <?php endforeach; endif; unset($_from); ?>
                </select>
              </div>
          </div>
          <div class="layui-form-item">
              <label class="layui-form-label">
              </label>
              <button  class="layui-btn" lay-filter="add" lay-submit="">
                  增加
              </button>
          </div>
      </form>
    </div>
    <script>
        layui.use(['form','layer'], function(){
            $ = layui.jquery;
          var form = layui.form
          ,layer = layui.layer;


          //自定义验证规则
          form.verify({
            realname: function(value){
              if(value.length < 2){
                return '姓名至少得2个字符啊';
              }
            }
            ,password: [/(.+){6,12}$/, '密码必须6到12位']
          });

          //监听提交
          form.on('submit(add)', function(data){
                var email = $('#L_email').val()
                var pass = $('#L_pass').val()
                var realname = $('#L_realname').val()
                var role = $('#L_role option:selected').val()
              $.post('index.php?r=market/Admin/Adminadd',{email:email,password:pass,realname:realname,role:role},function(data,status){
                  console.log(data)
                  console.log(status)
                  if(data == 'ok' && status == 'success') {
                      layer.alert("增加成功", {icon: 6}, function () {
                          // 获得frame索引
                          var index = parent.layer.getFrameIndex(window.name);
                          //关闭当前frame
                          parent.layer.close(index);
                      })
                  }
              })

            return false;
          });
          
          
        });
    </script>
  </body>

</html>