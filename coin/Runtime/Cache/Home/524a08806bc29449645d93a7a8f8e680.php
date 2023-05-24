<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html   >
<head>
    <meta charset="UTF-8">
    <title><?php echo ($webname); ?></title>
    <link rel="stylesheet" href="/Public/Home/login/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="/Public/Home/static/css/base.css" />

    <link rel="stylesheet" href="/Public/Dela/demo/demo.css">
    <link rel="stylesheet" href="/Public/Dela/template/dela-template.css">
    <link rel="stylesheet" href="/Public/build/css/countrySelect.css">

    <link rel="stylesheet" href="/Public/Home/static/css/style.css">

    <style>
        .main_demo {
            width: 28% !important;
            float: left;
        }

        .container-input {
            padding-left: 0px;
            width: 60%;
            margin: 0px;
        }

        button {
            appearance: auto;
            writing-mode: horizontal-tb !important;
            font-style: ;
            font-variant-ligatures: ;
            font-variant-caps: ;
            font-variant-numeric: ;
            font-variant-east-asian: ;
            font-weight: ;
            font-stretch: ;
            font-size: ;
            font-family: ;
            text-rendering: auto;
            color: buttontext;
            letter-spacing: normal;
            word-spacing: normal;
            line-height: normal;
            text-transform: none;
            text-indent: 0px;
            text-shadow: none;
            display: inline-block;
            text-align: center;
            align-items: flex-start;
            cursor: default;
            box-sizing: border-box;
            background-color: buttonface;
            margin: 0em;
            padding: 1px 6px;
            border-width: 2px;
            border-style: outset;
            border-color: buttonborder;
            border-image: initial;
        }
        .btn-hover {
            width: 7em;
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            cursor: pointer;
            height: 30px;
            text-align: center;
            border: none;
            background-size: 300% 100%;
            border-radius: 50px;
            moz-transition: all .4s ease-in-out;
            -o-transition: all .4s ease-in-out;
            -webkit-transition: all .4s ease-in-out;
            transition: all .4s ease-in-out;
        }
        .btn-hover.color-1 {
            /*color: #0052fe;*/
            color: #6d7c82;
            border: none !important;
            /*background-image: linear-gradient(to right, #25aae1, #40e495, #30dd8a, #2bb673);*/
            /*box-shadow: 0 4px 15px 0 rgb(49 196 190 / 75%);*/
        }
        .btn-hover.color-6 {
            /*color: #0052fe;*/
            color: #6d7c82;
            border: none !important;
            /*background-image: linear-gradient(to right, #009245, #FCEE21, #00A8C5, #D9E021);*/
            /*box-shadow: 0 4px 15px 0 rgb(83 176 57 / 75%);*/
        }
        .btnl {
            float: left;
            margin-bottom:1.1em;

        }
        .btnr {
            float: right;
            margin-bottom:1.1em;
            position: absolute;
            left: 40%;
        }
        .code {
            width: 100% !important;
        }
        .send-btn {
            width: 20%;
            height: 1.8em;
            position: absolute;
            top: 415px;
            left: 220px;

        }
        .mobile_input {
            width: 70% !important;
            float: right;
        }

        .btn-hover.color-4 {
            background-image: linear-gradient(to right, #fc6076, #ff9a44, #ef9d43, #e75516);
            box-shadow: 0 4px 15px 0 rgb(252 104 110 / 75%);
        }

        .css-jmskxt {
            background: #000000;
        }

    </style>
</head>
<body>
<header class="css-jmskxt">
	 <a href="<?php echo U('Index/index');?>" clss="css-1mvf8us">
	     <img src="/Upload/public/<?php echo get_config('waplogo');?>" class="css-1jgk2rg" style="height:50px;width:50px;border-radius:5px;margin-left: 30px;" />
	 </a>
	<div class="css-1tp5kus header-title">
		<div class="css-vurnku  f22 fw header-title"><?php echo L('PNSCX');?></div>
	</div>
	 <div class="css-1tp5kus">
	     <a href="<?php echo U('Index/index');?>" class="css-1smf7ma">
	         <div class="css-vurnku"><?php echo L('首页');?></div>
	     </a>
	 </div>


	<div class="css-1tp5kus">
		<a href="<?php echo U('Finance/index');?>" class="css-1smf7ma">
			<div class="css-vurnku"><?php echo L('充币');?> </div>
		</a>
	</div>
	<div class="css-1tp5kus">
		<a href="<?php echo U('Contract/index');?>" class="css-1smf7ma">
			<div class="css-vurnku"><?php echo L('秒合约');?> </div>
		</a>
	</div>
	<div class="css-1tp5kus">
		<a href="<?php echo U('Trade/index');?>" class="css-1smf7ma">
			<div class="css-vurnku"><?php echo L('币币交易');?> </div>
		</a>
	</div>


	 <div class="css-1tp5kus">
	     <a href="<?php echo U('Issue/index');?>" class="css-1smf7ma">
	         <div class="css-vurnku"><img src="/Public/Home/static/imgs/hot-2.svg" class="hot-2">IEO</div>
	     </a>
	 </div>
	 <div class="css-1tp5kus">
	     <a href="<?php echo U('Orepool/index');?>" class="css-1smf7ma">
	         <div class="css-vurnku"> <img src="/Public/Home/static/imgs/hot-2.svg" class="hot-2">DeFi</div>
	     </a>
	 </div>
	 <div class="css-1tp5kus">
	     <a href="<?php echo U('Index/gglist');?>" class="css-1smf7ma">
	         <div class="css-vurnku"><?php echo L('公告中心');?></div>
	     </a>
	 </div>
	 <div class="css-11y6cix"></div>
	 
	 <ul id="nav" class="nav">
	 <div class="css-wu6zme">
	     
	     <?php if($uid != 0): ?><div class="css-mu7imd nav">
	         <div class="css-1smf7ma">
	             <a href="<?php echo U('Finance/index');?>">
	             <div class="css-15owl46"  style="padding: 0px 5px;">
	                 <div class="css-vurnku" style="color:#fff;"><?php echo L('钱包');?></div>
	             </div>
	             </a>
	         </div>
	         <div class="css-1smf7ma">
	             <li class="nLi">
	                 <div class="css-15owl46"  style="padding: 0px 5px;">
	                     <div class="css-vurnku"><?php echo L('订单');?>
	                         <i class="bi bi-caret-down-fill css-1x1srvk" style="font-size:12px;"></i>
	                     </div>
	                 </div>
	                 <ul class="sub" style="padding:0px;display:none;z-index: 999999;">
	                 <!--<div class="order_navlist" style="transform: translate(972px, 64px);">-->
	                 <div class="order_navlist" style="top: 50px;right:180px;">
	                     
	                         <li style="list-style:none;">
	                             <a href="<?php echo U('Trade/bborder');?>">
	                             <div class="optionli">
	                                 <i class="bi bi-ui-checks" style="color:#00b897;font-size:18px;"></i>
	                                 <span style="color:#EAECEF;"><?php echo L('币币交易订单');?></span>
	                             </div>
	                             </a>
	                         </li>
	                         <li  style="list-style:none;">
	                             <a href="<?php echo U('Contract/contractjc');?>">
	                             <div class="optionli">
	                                 <i class="bi bi-sliders" style="color:#00b897;font-size:18px;"></i>
	                                 <span  style="color:#EAECEF;"><?php echo L('合约交易订单');?></span>
	                             </div>
	                             </a>
	                         </li>
	                         <li  style="list-style:none;">
	                             <a href="<?php echo U('Issue/normalissue');?>">
	                             <div class="optionli">
	                                 <i class="bi bi-ui-radios" style="color:#00b897;font-size:18px;"></i>
	                                 <span  style="color:#EAECEF;"><?php echo L('认购订单');?></span>
	                             </div>
	                             </a>
	                         </li>
	                         <li  style="list-style:none;">
	                             <a href="<?php echo U('Orepool/normalmin');?>">
	                             <div class="optionli">
	                                 <i class="bi bi-hammer" style="color:#00b897;font-size:18px;"></i>
	                                 <span  style="color:#EAECEF;"><?php echo L('矿机订单');?></span>
	                             </div>
	                             </a>
	                         </li>
	                     
	                 </div>
	                 </ul>
	             </li>
	         </div>
	         
	         
	         <div class="css-1smf7ma">
	             <li class="nLi">
	                 <div class="css-15owl46" style="padding: 0px 5px;">
	                     <div class="css-vurnku">
	                         <a href="<?php echo U('User/index');?>" style="color: #fff;">
	                         <i class="bi bi-person-circle" style="font-size:20px;"></i>
	                         </a>
	                     </div>
	                 </div>
	                 <ul class="sub" style="padding:0px;display:none;z-index: 999999;">
	                 <div class="order_navlist" style="min-width:220px;top: 60px;right:60px;">
	                     
	                         <li style="list-style:none;">
	                             <div style="width:100%;height:50px;line-height:50px;text-align:left;padding:0px 15px;">
	                                 <span style="font-size:16px;font-weight:bold;color:#EAECEF;"><?php echo ($username); ?></span>
	                                 <?php if($rzstatus == 2): ?><span class="f12 fgreen"><?php echo L('已认证');?></span>
	                                 <?php else: ?>
	                                 <span class="f12 fred"><?php echo L('未认证');?></span><?php endif; ?>
	                             </div>
	                         </li>

	                         <li  style="list-style:none;">
	                             <a href="<?php echo U('User/index');?>">
	                             <div class="optionli">
	                                 <i class="bi bi-person-fill" style="color:#00b897;font-size:18px;"></i>
	                                 <span  style="color:#EAECEF;"><?php echo L('账户总览');?></span>
	                             </div>
	                             </a>
	                         </li>
	                         <li  style="list-style:none;">
	                             <a href="<?php echo U('User/respwd');?>">
	                             <div class="optionli">
	                                 <i class="bi bi-gear" style="color:#00b897;font-size:18px;"></i>
	                                 <span  style="color:#EAECEF;"><?php echo L('安全设置');?></span>
	                             </div>
	                             </a>
	                         </li>
	                         <li  style="list-style:none;">
	                             <a href="<?php echo U('User/authrz');?>">
	                             <div class="optionli">
	                                 <i class="bi bi-shield-fill" style="color:#00b897;font-size:18px;"></i>
	                                 <span  style="color:#EAECEF;"><?php echo L('身份认证');?></span>
	                             </div>
	                             </a>
	                         </li>
	                         <li  style="list-style:none;">
	                             <a href="<?php echo U('User/tgcode');?>">
	                             <div class="optionli">
	                                 <i class="bi bi-person-plus-fill" style="color:#00b897;font-size:18px;"></i>
	                                 <span  style="color:#EAECEF;"><?php echo L('推荐返佣');?></span>
	                             </div>
	                             </a>
	                         </li>
	                         
	                         <li  style="list-style:none;">
	                             <a href="<?php echo U('Login/loginout');?>">
	                             <div class="optionli" style="border-top:1px solid #f5f5f5;">
	                                 <i class="bi bi-box-arrow-right" style="color:#00b897;font-size:18px;"></i>
	                                 <span  style="color:#EAECEF;"><?php echo L('退出账号');?></span>
	                             </div>
	                             </a>
	                         </li>
	                     
	                 </div>
	                 </ul>
	                 
	             </li>
	             
	         </div>
	         <div class="css-1smf7ma">
	             
	             <div class="css-15owl46" style="padding: 0px 5px;"> 
	                 <a href="<?php echo U('User/notice');?>" style="color: #fff;">
	                 <div class="css-vurnku">
	                     <i class="bi bi-bell css-6px2js" style="font-size:20px;"></i>
	                     <?php if($sum >= 1): ?><div class="css-1rch7es"><?php echo ($sum); ?></div><?php endif; ?>
	                 </div>
	                 </a>
	             </div>
	         </div>
	     </div>
	     <?php else: ?>
	     <div class="css-mu7imd">
	         <a href="<?php echo U('Login/index');?>">
	         <div class="css-1smf7ma">
	             <div class="css-15owl46"  style="padding: 0px 5px;">
	                 <div class="css-vurnku"><?php echo L('登陆');?></div>
	                 
	             </div>
	         </div>
	         </a>
	     </div>
	     <div class="css-mu7imd" style="border: 2px solid #00b897;border-radius: 5px;">
	         <a href="<?php echo U('Login/register');?>">
	         <div class="css-1smf7ma">
	             <div class="css-15owl46"  style="padding: 0px 5px;">
	                 <div class="css-vurnku" style="color: #00b897"><?php echo L('注册');?></div>
	             </div>
	         </div>
	         </a>
	     </div><?php endif; ?>
	     
	 </div>

	 <div class="css-wu6zme">
	     <li class="nLi">
	     <div class="css-1ql2hru" style="padding: 0px 5px;">
	         <div class="css-1smf7ma"><?php echo L('下载');?></div>
	         <ul class="sub" style="padding:0px;display:none;z-index: 999999;">
	             <div class="order_navlist" style="min-width:220px;top: 50px;right: 60px;">
	                 <div style="width:140px;height:140px;margin:10px auto;">
	                     <img src="/Public/Static/qrcode/FanamLa.png" style="width:100%;border-radius:10px;" />
	                 </div>
	                 <div style="width:100%;height:30px;line-height:30px;text-align:center;">
	                     <span style="color:#fff;font-size:12px;"><?php echo L('自动识别设备');?></span>
	                 </div>
	             </div>
	         </ul>
	     </div>
	     </li>
	     <li class="nLi">
	     <div class="css-1ql2hru" style="padding: 0px 5px;">
	         
	        <?php if(LANG_SET=='zh-cn'){?>
			<div class="css-1smf7ma">中文简体&nbsp;&nbsp;</div>
			<?php }elseif(LANG_SET=='en-us'){?>
			<div class="css-1smf7ma">English&nbsp;&nbsp;</div>	
			<?php }elseif(LANG_SET=='fr-fr'){?>
			<div class="css-1smf7ma">Français&nbsp;&nbsp;</div>	
			<?php }elseif(LANG_SET=='de-de'){?>
			<div class="css-1smf7ma">Deutsch&nbsp;&nbsp;</div>	
			<?php }elseif(LANG_SET=='it-it'){?>
			<div class="css-1smf7ma">Italiano&nbsp;&nbsp;</div>	
			<?php }elseif(LANG_SET=='ja-jp'){?>
            <div class="css-1smf7ma">日本語&nbsp;&nbsp;</div>
            <?php }elseif(LANG_SET=='ko-kr'){?>
            <div class="css-1smf7ma">한국어&nbsp;&nbsp;</div>
            <?php }elseif(LANG_SET=='tr-tr'){?>
            <div class="css-1smf7ma">Türk&nbsp;&nbsp;</div>	
			<?php }?>
			
	     </div>
	     <ul class="sub" style="padding:0px;display:none;">
	        <!--<div class="order_navlist" style="min-width:160px;transform: translate(1161px, 64px);">-->
	        <div class="order_navlist" style="min-width:160px;top:50px;right: 10px;">
	            <li  style="list-style:none;">
	                <a href="<?php echo U('?Lang=zh-cn');?>">
	                <div class="optionli">
	                    <span  style="color:#EAECEF;font-size:14px;">简体中文</span>
	                </div>
	                </a>
	            </li>
	            <li  style="list-style:none;">
	                <a href="<?php echo U('?Lang=en-us');?>">
	                <div class="optionli">
	                    <span  style="color:#EAECEF;font-size:14px;">English</span>
	                </div>
	                </a>
	            </li>
	            <li  style="list-style:none;">
	                <a href="<?php echo U('?Lang=fr-fr');?>">
	                <div class="optionli">
	                    <span  style="color:#EAECEF;font-size:14px;">Français</span>
	                </div>
	                </a>
	            </li>
	            <li  style="list-style:none;">
	                <a href="<?php echo U('?Lang=de-de');?>">
	                <div class="optionli">
	                    <span  style="color:#EAECEF;font-size:14px;">Deutsch</span>
	                </div>
	                </a>
	            </li>
	            <li  style="list-style:none;">
	                <a href="<?php echo U('?Lang=ja-jp');?>">
	                <div class="optionli">
	                    <span  style="color:#EAECEF;font-size:14px;">日本語</span>
	                </div>
	                </a>
	            </li>
	            <li  style="list-style:none;">
	                <a href="<?php echo U('?Lang=it-it');?>">
	                <div class="optionli">
	                    <span  style="color:#EAECEF;font-size:14px;">Italiano</span>
	                </div>
	                </a>
	            </li>
	            <li  style="list-style:none;">
	                <a href="<?php echo U('?Lang=ko-kr');?>">
	                <div class="optionli">
	                    <span  style="color:#EAECEF;font-size:14px;">한국어</span>
	                </div>
	                </a>
	            </li>
	            <li  style="list-style:none;">
	                <a href="<?php echo U('?Lang=tr-tr');?>">
	                <div class="optionli">
	                    <span  style="color:#EAECEF;font-size:14px;">Türk</span>
	                </div>
	                </a>
	            </li>
	       </div>
	     </li>
	 </div>
	 </ul>
</header> 



<!-- partial:index.partial.html -->
<div class="starfield">
    <div class="static"></div>
    <div class="moving-1"></div>
    <div class="moving-2"></div>
    <div class="moving-3"></div>
</div>

<!-- partial -->





<!-- partial:index.partial.html -->
<div class="container">
    <!-- code here -->
    <div class="card">
        <form class="card-form" onclick="return false">
            <div class="margin-topbox-px-10">
                <div data-bn-type="text" class="css-1g5tc38  tcl fch f36 fw"><?php echo L('立即注册');?><span class="fbaseblue"> <?php echo L('PNSCX');?></span></div>
                <div data-bn-type="text" class="css-152kxht tcl f16"></div>
            </div>

            <!--<div class="input">-->
            <!--    <button class="btn-hover color-6 btnr" onclick="stype(2)">E-mail</button>-->
            <!--    <button class="btn-hover color-1 btnl" onclick="stype(1)">Mobile</button>-->
            <!--</div>-->

            <input type="hidden" name="type" id="type" value="2">
            <input type="hidden" name="account" id="account" >
            <div class="input"  id="e-box">
                <input type="text" class="input-field" name="email" id="email" value=""  required/>
                <label class="input-label"><?php echo L('邮箱');?></label>
            </div>

            <!--<div id="main_demo" style="margin-bottom:1.1em;display: block" >-->
            <!--    <div class="container input container-input">-->
            <!--        <div class="form-item">-->
            <!--            <input id="country_selector" style="width: 367px;border-bottom: 1px solid #eee;" class="input-field"  type="text">-->
            <!--            <label for="country_selector" style="display:none;">Select a country here...</label>-->
            <!--        </div>-->
            <!--        <button type="submit" style="display:none;">Submit</button>-->
            <!--    </div>-->
            <!--</div>-->


            <div class="input" style="margin-top: 1.5rem;">
                <input type="password" class="input-field"  name="lpwd" id="lpwd" value="" required/>
                <label class="input-label"><?php echo L('密码');?></label>
            </div>

            <div class="input">
                <input type="text" class="input-field"  name="invit" id="invit" value="<?php echo ($qrcode); ?>" required/>
                <label class="input-label"><?php echo L('邀请码');?></label>
            </div>

            <div class="input">
                <input type="text" class="input-field"  name="ecode" id="ecode" value="" style="width: 70%" required />
                <label class="input-label"><?php echo L('验证码');?></label>
                <div class="action-button login-send-button tcr" style="background-color: rgba(255,255,255,0.5);width: 30%">
                    <a><label class="" onclick="getCode()"><?php echo L('获取验证码');?></label></a>
                </div>
            </div>
            <div class="action">
                <button class="action-button " onclick="upreg()"><?php echo L('立即注册');?></button>
            </div>
        </form>
        <div class="login-reg tcr">
            <!--<a href="<?php echo U('Login/findpwd');?>"  class="css-s84t59 floginbr" ><?php echo L('忘记密码');?>？</a>-->
            <a href="<?php echo U('Login/index');?>"  class="css-utqtyo floginbr" ><?php echo L('登录');?></a>
        </div>
    </div>
</div>
<!-- partial -->

</body>


<script src="https://cdn.staticfile.org/jquery/2.1.1/jquery.min.js"></script>
<script type="text/javascript" src="/Public/Home/static/js/layer/layer.js" ></script>
<script type="text/javascript" src="/Public/Home/static/js/jquery.SuperSlide.2.1.1.js" ></script>

<script type="text/javascript">
    function upreg(){
        var  type = $('#type').val();
        if (type != 1 && type != 2) {
            layer.msg("<?php echo L('请选择注册类型');?>");return false;
        }
        if (type == 1) {
            var account = $("#email").val();
        }

        if (type == 2) {
            var account = $(".selected-flag");
        }
 
        var email = $("#email").val();
        var reg = /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/;
        if(email=='' || email == null){
            layer.msg("<?php echo L('请输入邮箱');?>");return false;
        }
        if(!reg.test(email)){
            layer.msg("<?php echo L('邮箱格式不正确');?>");return false;
        }
        var ecode = $("#ecode").val();
        if(ecode == ''){
            layer.msg("<?php echo L('请输入邮箱验证码');?>");return false;
        }
        var lpwd = $("#lpwd").val();
        if(lpwd == ''){
            layer.msg("<?php echo L('请输入密码');?>");return false;
        }
        var invit = $("#invit").val();
        if( invit == ''){
            layer.msg("<?php echo L('请输入邀请码');?>");return false;
        }
        $.post("<?php echo U('Login/upregister');?>",
            {'email' : email, 'ecode' : ecode, 'lpwd' : lpwd, 'invit' : invit},
            function(data){
                if(data.code == 1){
                    layer.msg(data.info);
                    setTimeout(function(){
                        window.location.href="<?php echo U('Login/index');?>";
                    },2000);
                }else{
                    layer.msg(data.info);return false;
                }
            }
        );


    }
    
    
    
            //获取验证码
    function getCode(){
        var  type = $('#type').val();
        if (type != 1 && type != 2) {
            layer.msg("<?php echo L('请选择注册类型');?>");return false;
        }
        
        
        if(type == 1){
            //获取手机验证码
            var account = $("#country_selector").val();
            var phone = account.split(' ')[1]
            console.log(phone)
            if(phone==''){
                layer.msg("<?php echo L('请输入手机号');?>");return false;
            }
            $.post("<?php echo U('Login/sendpcode');?>",
                    {'email' : account},
                    function(data){
                        if(data.code == 1){
                            layer.msg(data.info);
                            
                        }else{
                            layer.msg(data.info);return false;
                        }
                    }
                );
            
        }
        
        
        if(type == 2){
            //获取邮箱验证码
            var account = $("#email").val();
            // console.log(account)
            var email = $("#email").val();
            var reg = /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/;
            if(email=='' || email == null){
                layer.msg("<?php echo L('请输入邮箱');?>");return false;
            }
            if(!reg.test(email)){
                layer.msg("<?php echo L('邮箱格式不正确');?>");return false;
            }
            
             $.post("<?php echo U('Login/sendcode');?>",
                {'email' : email},
                function(data){
                    if(data.code == 1){
                        layer.msg(data.info);
                        
                    }else{
                        layer.msg(data.info);return false;
                    }
                }
            );
        }
        
    }
</script>


<script type="text/javascript">

    function stype(type) {
       $('.btn-hover').css('color', '#6d7c82')
        if (type == 2) {
            $('#e-box').show();
            $('#main_demo').hide();
            $('.btnr').css('color','#0052fe')

        }
        if (type == 1) {
            $('#e-box').hide();
            $('#main_demo').show();
            $('.btnl').css('color','#0052fe')

        }
        $('#type').val(type);

    }

    function goindex(){
        window.location.href="<?php echo U('Index/index');?>";
    }

    function uplogin(){
        window.location.href="<?php echo U('Login/index');?>";
    }

    function forgot_password() {
        window.location.href="<?php echo U('Login/findpwd');?>";
    }


</script>

<script type="text/javascript">
    $("#nav").slide({
        type:"menu",// 效果类型，针对菜单/导航而引入的参数（默认slide）
        titCell:".nLi", //鼠标触发对象
        targetCell:".sub", //titCell里面包含的要显示/消失的对象
        effect:"slideDown", //targetCell下拉效果
        delayTime:300 , //效果时间
        triggerTime:0, //鼠标延迟触发时间（默认150）
        returnDefault:true //鼠标移走后返回默认状态，例如默认频道是“预告片”，鼠标移走后会返回“预告片”（默认false）
    });
</script>
<script src="/Public/build/js/jquery-1.9.1.min.js"></script>
<script src="/Public/build/js/countrySelect.js"></script>
<script>
    $("#country_selector").countrySelect({
        preferredCountries: ["jp","us"]
    });
</script>

</html>