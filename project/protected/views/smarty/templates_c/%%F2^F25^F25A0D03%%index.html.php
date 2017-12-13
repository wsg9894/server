<?php /* Smarty version 2.6.14, created on 2017-12-05 14:04:42
         compiled from test/index.html */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
<table>
    <?php $_from = ($this->_tpl_vars['DATA']); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['V']):
?>
        <tr>
            <td><?php echo $this->_tpl_vars['V']['sAdminUserName']; ?>
</td>
            <td><?php echo $this->_tpl_vars['V']['sCreateUser']; ?>
</td>
        </tr>
    <?php endforeach; endif; unset($_from); ?>
</table>
</body>
</html>