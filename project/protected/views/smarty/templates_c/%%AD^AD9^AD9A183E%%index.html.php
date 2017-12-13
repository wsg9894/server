<?php /* Smarty version 2.6.14, created on 2017-11-30 09:22:43
         compiled from scoreStore/index.html */ ?>
<!DOCTYPE html>
<html>
<head>
    <title>E豆商城</title>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
    <link rel="stylesheet" href="css/scoreStore/swiper-3.4.0.min.css">
    <link rel="stylesheet" href="css/scoreStore/main.css">
    <script type="text/javascript" src="js/scoreStore/layout.js"></script>
    <script type="text/javascript" src="js/scoreStore/swiper-3.4.0.min.js"></script>
</head>
<body class="index">
<div class="swiper-container">
    <div class="swiper-wrapper">
        <?php unset($this->_sections['BANNERLOOP']);
$this->_sections['BANNERLOOP']['name'] = 'BANNERLOOP';
$this->_sections['BANNERLOOP']['loop'] = is_array($_loop=$this->_tpl_vars['BANNERLIST']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['BANNERLOOP']['show'] = true;
$this->_sections['BANNERLOOP']['max'] = $this->_sections['BANNERLOOP']['loop'];
$this->_sections['BANNERLOOP']['step'] = 1;
$this->_sections['BANNERLOOP']['start'] = $this->_sections['BANNERLOOP']['step'] > 0 ? 0 : $this->_sections['BANNERLOOP']['loop']-1;
if ($this->_sections['BANNERLOOP']['show']) {
    $this->_sections['BANNERLOOP']['total'] = $this->_sections['BANNERLOOP']['loop'];
    if ($this->_sections['BANNERLOOP']['total'] == 0)
        $this->_sections['BANNERLOOP']['show'] = false;
} else
    $this->_sections['BANNERLOOP']['total'] = 0;
if ($this->_sections['BANNERLOOP']['show']):

            for ($this->_sections['BANNERLOOP']['index'] = $this->_sections['BANNERLOOP']['start'], $this->_sections['BANNERLOOP']['iteration'] = 1;
                 $this->_sections['BANNERLOOP']['iteration'] <= $this->_sections['BANNERLOOP']['total'];
                 $this->_sections['BANNERLOOP']['index'] += $this->_sections['BANNERLOOP']['step'], $this->_sections['BANNERLOOP']['iteration']++):
$this->_sections['BANNERLOOP']['rownum'] = $this->_sections['BANNERLOOP']['iteration'];
$this->_sections['BANNERLOOP']['index_prev'] = $this->_sections['BANNERLOOP']['index'] - $this->_sections['BANNERLOOP']['step'];
$this->_sections['BANNERLOOP']['index_next'] = $this->_sections['BANNERLOOP']['index'] + $this->_sections['BANNERLOOP']['step'];
$this->_sections['BANNERLOOP']['first']      = ($this->_sections['BANNERLOOP']['iteration'] == 1);
$this->_sections['BANNERLOOP']['last']       = ($this->_sections['BANNERLOOP']['iteration'] == $this->_sections['BANNERLOOP']['total']);
?>
        <div class="swiper-slide">
            <a href="<?php echo $this->_tpl_vars['BANNERLIST'][$this->_sections['BANNERLOOP']['index']]['url']; ?>
"><img src="<?php echo $this->_tpl_vars['BANNERLIST'][$this->_sections['BANNERLOOP']['index']]['pic']; ?>
"></a>
        </div>
        <?php endfor; endif; ?>
    </div>
    <div class="swiper-pagination"></div>
</div>
<div id="menu-group">
    <a href="/project/index.php?r=scoreStore/Site/Tasks">
        <div class="menu-item">
            <img src="images/scoreStore/index_02.png">
            <img class="red" src="images/scoreStore/..png">
            <p>任务专栏</p>
        </div>
    </a>
    <a href="/project/index.php?r=scoreStore/Site/myScore">
        <div class="menu-item">
            <img src="images/scoreStore/index_03.png">
            <p>我的E豆</p>
            <span><?php if ($this->_tpl_vars['UINFO']['iCurScore'] > 0):  echo $this->_tpl_vars['UINFO']['iCurScore'];  else: ?>0<?php endif; ?></span>
        </div>
    </a>
    <div class="hr-v" style="left:50%;"></div>
    <?php if (empty ( $this->_tpl_vars['TIPINFO']['tipLink'] )): ?>
    <p id="Esaid">小E说：<?php echo $this->_tpl_vars['TIPINFO']['tipCont']; ?>
</p>
    <?php else: ?>
    <a href="<?php echo $this->_tpl_vars['TIPINFO']['tipLink']; ?>
"><p id="Esaid">小E说：<?php echo $this->_tpl_vars['TIPINFO']['tipCont']; ?>
</p></a>
    <?php endif; ?>
</div>
<div id="banner-group">
    <!--首推荐位-->
	<?php if (! empty ( $this->_tpl_vars['RECOMMENDINFO']['firstPicUrl'] )): ?>
    <div class="banner-item banner1">
        <?php if ($this->_tpl_vars['RECOMMENDINFO']['firstGoodsID'] != 0): ?>
        <a href="/project/index.php?r=scoreStore/Site/GoodsDetail&gid=<?php echo $this->_tpl_vars['RECOMMENDINFO']['firstGoodsID']; ?>
"><img src="<?php echo $this->_tpl_vars['RECOMMENDINFO']['firstPicUrl']; ?>
"></a>
        <?php else: ?>
        <a href="<?php echo $this->_tpl_vars['RECOMMENDINFO']['firstLink']; ?>
"><img src="<?php echo $this->_tpl_vars['RECOMMENDINFO']['firstPicUrl']; ?>
"></a>
        <?php endif; ?>
    </div>
	<?php endif; ?>

    <!--次推荐位-->
	<?php if (! empty ( $this->_tpl_vars['RECOMMENDINFO']['secondPicUrl'] )): ?>
    <div class="banner-item banner1">
        <?php if ($this->_tpl_vars['RECOMMENDINFO']['secondGoodsID'] != 0): ?>
        <a href="/project/index.php?r=scoreStore/Site/GoodsDetail&gid=<?php echo $this->_tpl_vars['RECOMMENDINFO']['secondGoodsID']; ?>
"><img src="<?php echo $this->_tpl_vars['RECOMMENDINFO']['secondPicUrl']; ?>
"></a>
        <?php else: ?>
        <a href="<?php echo $this->_tpl_vars['RECOMMENDINFO']['secondLink']; ?>
"><img src="<?php echo $this->_tpl_vars['RECOMMENDINFO']['secondPicUrl']; ?>
"></a>
        <?php endif; ?>
    </div>
	<?php endif; ?>
</div>
<!--商品class偶数+right-->

<div id="good-group">
    <?php unset($this->_sections['GOODSLOOP']);
$this->_sections['GOODSLOOP']['name'] = 'GOODSLOOP';
$this->_sections['GOODSLOOP']['loop'] = is_array($_loop=$this->_tpl_vars['GOODSLIST']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['GOODSLOOP']['show'] = true;
$this->_sections['GOODSLOOP']['max'] = $this->_sections['GOODSLOOP']['loop'];
$this->_sections['GOODSLOOP']['step'] = 1;
$this->_sections['GOODSLOOP']['start'] = $this->_sections['GOODSLOOP']['step'] > 0 ? 0 : $this->_sections['GOODSLOOP']['loop']-1;
if ($this->_sections['GOODSLOOP']['show']) {
    $this->_sections['GOODSLOOP']['total'] = $this->_sections['GOODSLOOP']['loop'];
    if ($this->_sections['GOODSLOOP']['total'] == 0)
        $this->_sections['GOODSLOOP']['show'] = false;
} else
    $this->_sections['GOODSLOOP']['total'] = 0;
if ($this->_sections['GOODSLOOP']['show']):

            for ($this->_sections['GOODSLOOP']['index'] = $this->_sections['GOODSLOOP']['start'], $this->_sections['GOODSLOOP']['iteration'] = 1;
                 $this->_sections['GOODSLOOP']['iteration'] <= $this->_sections['GOODSLOOP']['total'];
                 $this->_sections['GOODSLOOP']['index'] += $this->_sections['GOODSLOOP']['step'], $this->_sections['GOODSLOOP']['iteration']++):
$this->_sections['GOODSLOOP']['rownum'] = $this->_sections['GOODSLOOP']['iteration'];
$this->_sections['GOODSLOOP']['index_prev'] = $this->_sections['GOODSLOOP']['index'] - $this->_sections['GOODSLOOP']['step'];
$this->_sections['GOODSLOOP']['index_next'] = $this->_sections['GOODSLOOP']['index'] + $this->_sections['GOODSLOOP']['step'];
$this->_sections['GOODSLOOP']['first']      = ($this->_sections['GOODSLOOP']['iteration'] == 1);
$this->_sections['GOODSLOOP']['last']       = ($this->_sections['GOODSLOOP']['iteration'] == $this->_sections['GOODSLOOP']['total']);
?>
    <?php if ($this->_sections['GOODSLOOP']['index']%2 == 0): ?>
    <div class="good-box">
        <?php endif; ?>
        <a href="/project/index.php?r=scoreStore/Site/GoodsDetail&gid=<?php echo $this->_tpl_vars['GOODSLIST'][$this->_sections['GOODSLOOP']['index']]['gid']; ?>
">
            <div class="good">
                <div class="photo">
                    <img src="<?php echo $this->_tpl_vars['GOODSLIST'][$this->_sections['GOODSLOOP']['index']]['list_pic']; ?>
">
					<p class="sub"><?php echo $this->_tpl_vars['GOODSLIST'][$this->_sections['GOODSLOOP']['index']]['list_name']; ?>
</p>
					<?php if ($this->_tpl_vars['GOODSLIST'][$this->_sections['GOODSLOOP']['index']]['allow_day'] <= 0): ?>
					<p class="price"><?php if ($this->_tpl_vars['GOODSLIST'][$this->_sections['GOODSLOOP']['index']]['extra_price'] > 0): ?>￥<?php echo $this->_tpl_vars['GOODSLIST'][$this->_sections['GOODSLOOP']['index']]['extra_price']; ?>
+<?php endif;  echo $this->_tpl_vars['GOODSLIST'][$this->_sections['GOODSLOOP']['index']]['price']; ?>
E豆<span style="float:left;position:relative;top:0.01rem"><img src="images/over.png" style="width:0.3rem;"></span></p>
					<?php else: ?>
					<p class="price"><?php if ($this->_tpl_vars['GOODSLIST'][$this->_sections['GOODSLOOP']['index']]['extra_price'] > 0): ?>￥<?php echo $this->_tpl_vars['GOODSLIST'][$this->_sections['GOODSLOOP']['index']]['extra_price']; ?>
+<?php endif;  echo $this->_tpl_vars['GOODSLIST'][$this->_sections['GOODSLOOP']['index']]['price']; ?>
E豆</p>
					<?php endif; ?>
                </div>
            </div>
        </a>
        <?php if ($this->_sections['GOODSLOOP']['index']%2 == 1): ?>
    </div>
    <?php endif; ?>
    <?php endfor; endif; ?>
</div>
<div id="footer">
    <img src="images/scoreStore/logo.png">
</div>
<div id="index-foot">
    <div class="index-foot-item">
        <a href="/index.php">
            <img src="/public/front-2.0/images/index_0.png" alt="">
            <p>首页</p>
        </a>
    </div>
    <div class="index-foot-item">
        <a href="/cinema/movieselect.php">
            <img src="/public/front-2.0/images/ticket_0.png" alt="">
            <p>购票</p>
        </a>
    </div>
	<div class="index-foot-item">
        <a href="/project/index.php?r=partner/Game/Index&from=15">
            <img src="/public/front-2.0/images/game_0.png" alt="">
            <p>游戏</p>
        </a>
    </div>
    <div class="index-foot-item active">
        <img src="/public/front-2.0/images/shop_1.png" alt="">
        <p>商城</p>
    </div>
    <div class="index-foot-item">
        <a href="/usercenter/UserCenterIndex.html">
            <img src="/public/front-2.0/images/my_0.png" alt="">
            <p>我的</p>
        </a>
    </div>
</div>
<style>
    #index-foot{width: 3.2rem;height: 0.4179rem;background-color: #fff;border-top: 1px solid #ccc;position: fixed;bottom: 0px;}
    #index-foot .index-foot-item.active p{color:#1dd087;}
    #index-foot .index-foot-item{width: 20%;float: left;height: 100%;}
    #index-foot .index-foot-item img{width: 32%;margin: 0.0213rem auto;margin-bottom:0px;display: block;}
    #index-foot .index-foot-item p{text-align:center;font-size: 0.0853rem;color:#999;}
    #index-foot a{ color:#333;
        -webkit-tap-highlight-color: rgba(0,0,0,0);
        display: block;
        text-decoration: none;
    }
</style>
<script>
    var mySwiper = new Swiper ('.swiper-container', {
        direction: 'horizontal',
        loop: true,
        autoplay:3000,
        pagination: '.swiper-pagination',
    })
</script>

<div style="display:none">
    <script src="https://s11.cnzz.com/z_stat.php?id=1256966228&web_id=1256966228" language="JavaScript"></script>
</div>
</body>
</html>