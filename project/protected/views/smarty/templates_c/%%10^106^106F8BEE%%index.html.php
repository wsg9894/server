<?php /* Smarty version 2.6.14, created on 2017-11-30 09:22:56
         compiled from movieGame/index.html */ ?>
<!DOCTYPE html>
<html>
<head>
    <title>影游互动</title>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
    <link rel="stylesheet" href="css/movieGame/swiper-3.4.0.min.css">
    <link rel="stylesheet" href="css/movieGame/main.css">
    <script type="text/javascript" src="js/movieGame/jquery.min.js"></script>
    <script type="text/javascript" src="js/movieGame/swiper-3.4.0.min.js"></script>
    <script type="text/javascript" src="js/movieGame/layout.js"></script>
    <script type="text/javascript" src="js/movieGame/main.js"></script>
</head>
<body style="position: inherit">
    <?php if (! empty ( $this->_tpl_vars['BANNERLIST'] )): ?>
        <div class="index-head">
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
&from=25"><img src="<?php echo $this->_tpl_vars['BANNERLIST'][$this->_sections['BANNERLOOP']['index']]['pic']; ?>
"></a>
                    </div>
                    <?php endfor; endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="index-menu">
        <div class="index-menu-item">
            <a href="/project/index.php?r=partner/Game/Ranking&from=3" style="color:#FFB600"><img src="images/movieGame/ranking.png" alt="">
            <p>手游排行</p></a>
        </div>
        <div class="index-menu-item">
            <a href="/project/index.php?r=partner/Game/Gamepackage&from=3" style="color:#FF5D5D"><img src="images/movieGame/package.png" alt="">
            <p>游戏礼包</p></a>
        </div>
        <div class="index-menu-item">
            <a href="/project/index.php?r=partner/Game/NoInsert&from=3" style="color:#15EB3D"><img src="images/movieGame/noinstall.png" alt="">
                <p>免安装游戏</p></a>
        </div>
        <div class="index-menu-item">
            <a href="/project/index.php?r=partner/Game/MyGame&from=3" style="color:#A537E4"><img src="images/movieGame/my.png" alt="">
            <p>我的游戏</p></a>
        </div>
    </div>
    <div class="cHr"></div>
    <div class="index-hot-game">
        <div class="index-hot-title">
            <?php if ($this->_tpl_vars['FROM'] == 21): ?>
            <a href="/project/index.php?r=partner/Game/Index&from=3"><h3 style="color:#999">火热游戏</h3></a>
            <a href="/project/index.php?r=partner/Game/Index&from=21"><h3>下载榜</h3></a>
            <a href="/project/index.php?r=partner/Game/Index&from=22"><h3 style="color:#999">新品榜</h3></a>
            <a href="/project/index.php?r=partner/Game/Index&from=23"><h3 style="color:#999">影视同期榜</h3></a>
            <?php elseif ($this->_tpl_vars['FROM'] == 22): ?>
            <a href="/project/index.php?r=partner/Game/Index&from=3"><h3 style="color:#999">火热游戏</h3></a>
            <a href="/project/index.php?r=partner/Game/Index&from=21"><h3 style="color:#999">下载榜</h3></a>
            <a href="/project/index.php?r=partner/Game/Index&from=22"><h3>新品榜</h3></a>
            <a href="/project/index.php?r=partner/Game/Index&from=23"><h3 style="color:#999">影视同期榜</h3></a>
            <?php elseif ($this->_tpl_vars['FROM'] == 23): ?>
            <a href="/project/index.php?r=partner/Game/Index&from=3"><h3 style="color:#999">火热游戏</h3></a>
            <a href="/project/index.php?r=partner/Game/Index&from=21"><h3 style="color:#999">下载榜</h3></a>
            <a href="/project/index.php?r=partner/Game/Index&from=22"><h3 style="color:#999">新品榜</h3></a>
            <a href="/project/index.php?r=partner/Game/Index&from=23"><h3>影视同期榜</h3></a>
            <?php else: ?>
            <a href="/project/index.php?r=partner/Game/Index&from=3"><h3>火热游戏</h3></a>
            <a href="/project/index.php?r=partner/Game/Index&from=21"><h3 style="color:#999">下载榜</h3></a>
            <a href="/project/index.php?r=partner/Game/Index&from=22"><h3 style="color:#999">新品榜</h3></a>
            <a href="/project/index.php?r=partner/Game/Index&from=23"><h3 style="color:#999">影视同期榜</h3></a>
            <?php endif; ?>
        </div>
        <?php unset($this->_sections['HOTBANNERLOOP']);
$this->_sections['HOTBANNERLOOP']['name'] = 'HOTBANNERLOOP';
$this->_sections['HOTBANNERLOOP']['loop'] = is_array($_loop=$this->_tpl_vars['HOTBANNERLIST']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['HOTBANNERLOOP']['show'] = true;
$this->_sections['HOTBANNERLOOP']['max'] = $this->_sections['HOTBANNERLOOP']['loop'];
$this->_sections['HOTBANNERLOOP']['step'] = 1;
$this->_sections['HOTBANNERLOOP']['start'] = $this->_sections['HOTBANNERLOOP']['step'] > 0 ? 0 : $this->_sections['HOTBANNERLOOP']['loop']-1;
if ($this->_sections['HOTBANNERLOOP']['show']) {
    $this->_sections['HOTBANNERLOOP']['total'] = $this->_sections['HOTBANNERLOOP']['loop'];
    if ($this->_sections['HOTBANNERLOOP']['total'] == 0)
        $this->_sections['HOTBANNERLOOP']['show'] = false;
} else
    $this->_sections['HOTBANNERLOOP']['total'] = 0;
if ($this->_sections['HOTBANNERLOOP']['show']):

            for ($this->_sections['HOTBANNERLOOP']['index'] = $this->_sections['HOTBANNERLOOP']['start'], $this->_sections['HOTBANNERLOOP']['iteration'] = 1;
                 $this->_sections['HOTBANNERLOOP']['iteration'] <= $this->_sections['HOTBANNERLOOP']['total'];
                 $this->_sections['HOTBANNERLOOP']['index'] += $this->_sections['HOTBANNERLOOP']['step'], $this->_sections['HOTBANNERLOOP']['iteration']++):
$this->_sections['HOTBANNERLOOP']['rownum'] = $this->_sections['HOTBANNERLOOP']['iteration'];
$this->_sections['HOTBANNERLOOP']['index_prev'] = $this->_sections['HOTBANNERLOOP']['index'] - $this->_sections['HOTBANNERLOOP']['step'];
$this->_sections['HOTBANNERLOOP']['index_next'] = $this->_sections['HOTBANNERLOOP']['index'] + $this->_sections['HOTBANNERLOOP']['step'];
$this->_sections['HOTBANNERLOOP']['first']      = ($this->_sections['HOTBANNERLOOP']['iteration'] == 1);
$this->_sections['HOTBANNERLOOP']['last']       = ($this->_sections['HOTBANNERLOOP']['iteration'] == $this->_sections['HOTBANNERLOOP']['total']);
?>
        <div class="index-hot-item">
            <div class="index-hot-item-left">
                <img src="<?php echo $this->_tpl_vars['HOTBANNERLIST'][$this->_sections['HOTBANNERLOOP']['index']]['img']; ?>
" alt="">
            </div>
            <div class="index-hot-item-right">
                <?php if ($this->_tpl_vars['HOTBANNERLIST'][$this->_sections['HOTBANNERLOOP']['index']]['introduce'] != ""): ?>
                <a href="/project/index.php?r=partner/Game/Gamedetail&gid=<?php echo $this->_tpl_vars['HOTBANNERLIST'][$this->_sections['HOTBANNERLOOP']['index']]['gid']; ?>
&from=3">
                    <?php endif; ?>
                    <h3><?php echo $this->_tpl_vars['HOTBANNERLIST'][$this->_sections['HOTBANNERLOOP']['index']]['gname']; ?>
</h3>
                    <p class="notice"><?php echo $this->_tpl_vars['HOTBANNERLIST'][$this->_sections['HOTBANNERLOOP']['index']]['describe']; ?>
</p>
                    <p class="tip"><?php if ($this->_tpl_vars['HOTBANNERLIST'][$this->_sections['HOTBANNERLOOP']['index']]['cate'] != ""):  echo $this->_tpl_vars['HOTBANNERLIST'][$this->_sections['HOTBANNERLOOP']['index']]['cate']; ?>
 | <?php endif; ?>
                        <?php echo $this->_tpl_vars['HOTBANNERLIST'][$this->_sections['HOTBANNERLOOP']['index']]['num_people']; ?>
</p>
                    <?php if ($this->_tpl_vars['HOTBANNERLIST'][$this->_sections['HOTBANNERLOOP']['index']]['introduce'] != ""): ?>
                </a>
                <?php endif; ?>
                <button onclick="getAJAX('<?php echo $this->_tpl_vars['HOTBANNERLIST'][$this->_sections['HOTBANNERLOOP']['index']]['gid']; ?>
')"><?php echo $this->_tpl_vars['HOTBANNERLIST'][$this->_sections['HOTBANNERLOOP']['index']]['install']; ?>
</button>
            </div>
        </div>
        <?php endfor; endif; ?>

    </div>
    <div class="index-bottom-line" style="visibility:hidden">
        <p>人家是有底线的</p>
    </div>
    <div class="hide-tip" style="position:fixed;left: 0px;top: 0px;width: 100%;height: 100%;background-color: rgba(0,0,0,0.6);display: none;z-index: 5">
        <img src="images/movieGame/rightup.png" style="width: 60%;float: right;margin-right: 0.0853rem;margin-top: 0.0853rem;" >
    </div>
    <div style="width: 100%;height: 0.4181rem;"></div>
    <div class="index-foot">
        <div class="index-foot-item">
            <a href="/index.php">
            <img src="/public/front-2.0/images/index_1.png" alt="">
            <p>首页</p>
                </a>
        </div>
        <div class="index-foot-item">
            <a href="/cinema/movieselect.php">
                <img src="/public/front-2.0/images/ticket_0.png" alt="">
                <p>购票</p>
            </a>
        </div>
        <div class="index-foot-item active">
            <img src="/public/front-2.0/images/game_0.png" alt="">
            <p>游戏</p>
        </div>
        <div class="index-foot-item">
            <a href="/project/index.php?r=scoreStore/Site">
                <img src="/public/front-2.0/images/shop_0.png" alt="">
                <p>商城</p>
            </a>
        </div>
        <div class="index-foot-item">
            <a href="/usercenter/UserCenterIndex.html">
                <img src="/public/front-2.0/images/my_0.png" alt="">
                <p>我的</p>
            </a>
        </div>
    </div>
<script>
    var swiper = new Swiper('.swiper-container', {
        direction: 'horizontal',
        loop: true,
        autoplay : 3000
    });
    $(window).scroll(function() {
        if ($(document).scrollTop() >= $(document).height() - $(window).height()) {
            $('.index-bottom-line').css('visibility','visible');
            setTimeout("remainTime()",2000);
        }
    });

    function remainTime(){
        $('.index-bottom-line').css('visibility','hidden');
    }

    function getAJAX(gid){
        var jsonData = {gid:gid};
        $.ajax({
            url: "/project/index.php?r=partner/Game/Install",
            data: jsonData,
            type: "post",
            success: function (data) {
                var json_str = JSON.parse(data);
                if(json_str.ERRORGAME == 0){
                    alert("请选择正确的下载/打开列表");
                    location.href = json_str.HREF;
                }else if(json_str.ERRORGAME == "isWeixin"){
                    $('.hide-tip').css("display","block");
                }else{
                    location.href = json_str.HREF;
                }
            },error: function (error) {

            }
        });
    }

    $('.hide-tip').click(function(){
        $('.hide-tip').css("display","none");
    });

</script>
    <script type="text/javascript" src="js/movieGame/navbar.js"></script>
<div style="display:none">
    <script src="https://s11.cnzz.com/z_stat.php?id=1256966228&web_id=1256966228" language="JavaScript"></script>
</div>
</body>
</html>