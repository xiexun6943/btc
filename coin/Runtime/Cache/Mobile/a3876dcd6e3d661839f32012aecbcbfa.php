<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/css/bootstrap.min.css"
          integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="/Public/Static/css/base2.css"/>
    <link rel="stylesheet" type="text/css" href="/Public/Static/css/nologed.css"/>
    <link rel="stylesheet" href="/Public/Static/Icoinfont/iconfont.css">
    <script src="/Public/Static/Icoinfont/iconfont.js"></script>
    <title><?php echo ($webname); ?></title>
    <style>
        ::-webkit-input-placeholder {
            color: #b5b5b5;
            font-size: 12px;
        }

        ::-moz-placeholder {
            color: #b5b5b5;
            font-size: 12px;
        }

        input:focus {
            background: #ebecf0;
            outline: 1px solid #ebecf0;
        }

        a:hover, a:link, a:visited, a:active {
            color: #000000;
            text-decoration: none;
        }

        .no_header {
            position: fixed;
            z-index: 9999;
            background: #fff;
            padding: 0px 10px;
            top: 0px;
        }

        .txtl {
            line-height: 50px;
            width: 10%;
        }

        .oreimgbox {
            width: 100%;
            height: 150px;
            margin-top: 50px;
        }

        .btmbox {
            width: 100%;
            height: 60px;
            background: #f5f5f5;
        }

        .orebox {
            width: 100%;
            margin: 0px auto;
            background: #fff;
            margin-bottom: 10px;
            box-shadow: 0 2px 10px 0 rgb(0 0 0 / 10%);
            padding: 10px;
        }

        .progress-bar {
            color: #000;
            background-color: #f3c420;
        }

        .progress {
            height: 0.9rem;
            background-color: #f5f5f5;
            border-radius: .5rem;
        }

        .obbox {
            width: 33.33%;
            height: 60px;
            float: left;
        }

        .obbox_h {
            width: 100%;
            height: 30px;
            line-height: 20px;
        }

        .issuebox {
            width: 100%;
            height: 500px;
            background: #f5f5f5;
            padding: 10px 0px;
        }

    </style>
</head>
<body>
<div class="container-fluid " style="padding:0px;width:100vw;">
    <div class="no_header">
        <a href="<?php echo U('Trade/tradelist');?>">
            <div class="fl allhg txtl">
                <i class="bi bi-arrow-left fcc fw" style="font-size: 24px;"></i>
            </div>
        </a>

        <div class="fl allhg" id="centerbox" style="width:80%;text-align:center;line-height:50px;">
            <span class="fcc fzmmm"><?php echo L('在线客服');?></span>
        </div>


    </div>

    <!-- BEGIN ProvideSupport.com Graphics Chat Button Code -->
    <script>(function (D) {
        function f() {
            function n(n, e) {
                e = D.createElement("script");
                e.src = "https://image.providesupport.com/" + n, D.body.appendChild(e)
            }

            n("js/0kxudqxs3jj5n1r63sc2a6io2u/safe-standard-sync.js?ps_h=FgqY&ps_t=" + Date.now()), n("sjs/static.js")
        }

        D.readyState == "complete" ? f() : window.addEventListener("load", f)
    })(document)</script>
    <noscript>
        <div style="display:inline"><a href="https://vm.papepritz.com/0kxudqxs3jj5n1r63sc2a6io2u">即时支持聊天</a></div>
    </noscript>
    <!-- END ProvideSupport.com Graphics Chat Button Code -->
    <a href="https://vm.papepritz.com/090k66n7qhygy0uublxva3tb0h">
    <div class="header" style="position: relative; margin-top:55px; padding-left:30px; background: #007bff">
        <i class="iconfont" style="color: #FFFFFF;font-size:22px;">
			<img style="width:auto;height: 2rem"  src="/Public/Static/img/contact_us.png"/>
		</i>
        <h4  style="margin-top: -1.5rem;font-weight: bold; font-size: 18px; padding-left:50px ;padding-bottom: 10px "><?php echo L('在线客服');?></h4>
    </div>
    </a>

</div>
<style>
    .psmtc_FgqY{
        display: none;
    }
</style>
<!--底部-->
<div class="footer">

    <a href="<?php echo U('Index/index');?>">
        <div class="footer_op">
            <div class="f_op_t" style="line-height: 35px;">
                <?php
 if($select == 'index') { echo '<img src="/Public/Static/Icoinfont/icon/nav-1-active.png" style="width: 25px;" >'; }else{ echo '<img src="/Public/Static/Icoinfont/icon/nav-1.png" style="width: 25px;">'; } ?>
            </div>
            <div class="f_op_b">
                <span class="fzm fcy"><?php echo L('首页');?></span>
            </div>
        </div>
    </a>

    <a href="<?php echo U('Trade/index');?>">
        <div class="footer_op">
            <div class="f_op_t" style="line-height:35px;">
                <?php
 if($select == 'trade') { echo '<img src="/Public/Static/Icoinfont/icon/nav-2-active.png" style="width: 25px;" >'; }else{ echo '<img src="/Public/Static/Icoinfont/icon/nav-2.png" style="width: 25px;">'; } ?>
            </div>
            <div class="f_op_b">
                <span class="fzm fcch"><?php echo L('行情');?></span>
            </div>
        </div>
    </a>

    <a href="/Trade/trans?sytx=BTC/USDT">
        <div class="footer_op">
            <div class="f_op_t" style="line-height:35px;">
                <?php
 if($select == 'trans') { echo '<img src="/Public/Static/Icoinfont/icon/nav-3-active.png" style="width: 25px;" >'; }else{ echo '<img src="/Public/Static/Icoinfont/icon/nav-3.png" style="width: 25px;">'; } ?>
            </div>
            <div class="f_op_b">
                <span class="fzm fcch"><?php echo L('交易');?></span>
            </div>
        </div>
    </a>

    <a href="<?php echo U('Contract/index');?>">
        <div class="footer_op">
            <div class="f_op_t" style="line-height:35px;">
                <?php
 if($select == 'contract') { echo '<img src="/Public/Static/Icoinfont/icon/nav-4-active.png" style="width: 25px;" >'; }else{ echo '<img src="/Public/Static/Icoinfont/icon/nav-4.png" style="width: 25px;">'; } ?>
            </div>
            <div class="f_op_b">
                <span class="fzm fcch"><?php echo L('合约');?></span>
            </div>
        </div>
    </a>

    <a href="<?php echo U('User/index');?>">
        <div class="footer_op">
            <div class="f_op_t" style="line-height:35px;">
                <?php
 if($select == 'user') { echo '<img src="/Public/Static/Icoinfont/icon/nav-5-active.png" style="width: 25px;" >'; }else{ echo '<img src="/Public/Static/Icoinfont/icon/nav-5.png" style="width: 25px;">'; } ?>
            </div>
            <div class="f_op_b">
                <span class="fzm fcch"><?php echo L('资产');?></span>
            </div>
        </div>
    </a>


</div>

</body>

<script src="https://cdn.staticfile.org/jquery/2.1.1/jquery.min.js"></script>
<script type="text/javascript" src="/Public/Static/js/layer/layer.js"></script>

</html>