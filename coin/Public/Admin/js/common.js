//dom加载完成后执行的js
;$(function(){

	//全选的实现
	$(".check-all").click(function(){
		$(".ids").prop("checked", this.checked);
	});
	$(".ids").click(function(){
		var option = $(".ids");
		option.each(function(i){
			if(!this.checked){
				$(".check-all").prop("checked", false);
				return false;
			}else{
				$(".check-all").prop("checked", true);
			}
		});
	});

	//ajax get请求
	$('.ajax-get').click(function(){
		var confirm_msg = $(this).attr('confirm-msg') ? $(this).attr('confirm-msg') : '确认要执行该操作吗';
		var target;
		var that = this;
		if ( $(this).hasClass('confirm') ) {
			if(!confirm(confirm_msg)){
				return false;
			}
		}
		if ( (target = $(this).attr('href')) || (target = $(this).attr('url')) ) {
			$.get(target).success(function(data){
				if (data.status==1) {
					if (data.url) {
						updateAlert(data.info + ' 页面即将自动跳转~','alert-success');
					}else{
						updateAlert(data.info,'alert-success');
					}
					setTimeout(function(){
						if (data.url) {
							location.href=data.url;
						}else if( $(that).hasClass('no-refresh')){
							$('#top-alert').find('button').click();
						}else{
							location.reload();
						}
					},1500);
				}else{
					updateAlert(data.info);
					setTimeout(function(){
						if (data.url) {
							location.href=data.url;
						}else{
							$('#top-alert').find('button').click();
						}
					},1500);
				}
			});

		}
		return false;
	});

	//ajax post submit请求
	$('.ajax-post').click(function(){
		var confirm_msg = $(this).attr('confirm-msg') ? $(this).attr('confirm-msg') : '确认要执行该操作吗';

		var target,query,form;
		var target_form = $(this).attr('target-form');
		var that = this;
		var nead_confirm=false;
		if( ($(this).attr('type')=='submit') || (target = $(this).attr('href')) || (target = $(this).attr('url')) ){

			form = $('.'+target_form);

			if ($(this).attr('hide-data') === 'true'){//无数据时也可以使用的功能

				form = $('.hide-data');
				query = form.serialize();
			}else if (form.get(0)==undefined){
				console.log('c1');
				confirm(confirm_msg);
				return false;
			}else if ( form.get(0).nodeName=='FORM' ){
				if ( $(this).hasClass('confirm') ) {
					console.log('c2');
					if(!confirm(confirm_msg)){
						return false;
					}
				}
				if($(this).attr('url') !== undefined){
					target = $(this).attr('url');
				}else{
					target = form.get(0).action;
				}
				query = form.serialize();
			}else if( form.get(0).nodeName=='INPUT' || form.get(0).nodeName=='SELECT' || form.get(0).nodeName=='TEXTAREA') {
				form.each(function(k,v){
					if(v.type=='checkbox' && v.checked==true){
						nead_confirm = true;
					}
				})
				if ( nead_confirm && $(this).hasClass('confirm') ) {
					console.log('c2');
					if(!confirm(confirm_msg)){
						return false;
					}
				}
				query = form.serialize();
			}else{
				if ( $(this).hasClass('confirm') ) {
					console.log('c3');
					if(!confirm(confirm_msg)){
						return false;
					}
				}
				query = form.find('input,select,textarea').serialize();
			}
			$(that).addClass('disabled').attr('autocomplete','off').prop('disabled',true);
			$.post(target,query).success(function(data){
				if (data.status==1) {
					if (data.url) {
						updateAlert(data.info + ' 页面即将自动跳转~','alert-success');
					}else{
						updateAlert(data.info ,'alert-success');
					}
					setTimeout(function(){
						if (data.url) {
							location.href=data.url;
						}else if( $(that).hasClass('no-refresh')){
							$('#top-alert').find('button').click();
							$(that).removeClass('disabled').prop('disabled',false);
						}else{
							location.reload();
						}
					},1500);
				}else{
					updateAlert(data.info);
					setTimeout(function(){
						if (data.url) {
							location.href=data.url;
						}else{
							$('#top-alert').find('button').click();
							$(that).removeClass('disabled').prop('disabled',false);
						}
					},1500);
				}
			});
		}
		return false;
	});

	/**顶部警告栏*/
	var content = $('#main');
	var top_alert = $('#top-alert');
	top_alert.find('.close').on('click', function () {
		top_alert.removeClass('block').slideUp(200);
		// content.animate({paddingTop:'-=55'},200);
	});

	window.updateAlert = function (text,c) {
		text = text||'default';
		c = c||false;
		if ( text!='default' ) {
			top_alert.find('.alert-content').text(text);
			if (top_alert.hasClass('block')) {
			} else {
				top_alert.addClass('block').slideDown(200);
				// content.animate({paddingTop:'+=55'},200);
			}
		} else {
			if (top_alert.hasClass('block')) {
				top_alert.removeClass('block').slideUp(200);
				// content.animate({paddingTop:'-=55'},200);
			}
		}
		if ( c!=false ) {
			top_alert.removeClass('alert-error alert-warn alert-info alert-success').addClass(c);
		}
	};
	/*
        //按钮组
        (function(){
            //按钮组(鼠标悬浮显示)
            $(".btn-group").mouseenter(function(){
                var userMenu = $(this).children(".dropdown ");
                var icon = $(this).find(".btn i");
                icon.addClass("btn-arrowup").removeClass("btn-arrowdown");
                userMenu.show();
                clearTimeout(userMenu.data("timeout"));
            }).mouseleave(function(){
                var userMenu = $(this).children(".dropdown");
                var icon = $(this).find(".btn i");
                icon.removeClass("btn-arrowup").addClass("btn-arrowdown");
                userMenu.data("timeout") && clearTimeout(userMenu.data("timeout"));
                userMenu.data("timeout", setTimeout(function(){userMenu.hide()}, 100));
            });

            //按钮组(鼠标点击显示)
            // $(".btn-group-click .btn").click(function(){
            //     var userMenu = $(this).next(".dropdown ");
            //     var icon = $(this).find("i");
            //     icon.toggleClass("btn-arrowup");
            //     userMenu.toggleClass("block");
            // });
            $(".btn-group-click .btn").click(function(e){
                if ($(this).next(".dropdown").is(":hidden")) {
                    $(this).next(".dropdown").show();
                    $(this).find("i").addClass("btn-arrowup");
                    e.stopPropagation();
                }else{
                    $(this).find("i").removeClass("btn-arrowup");
                }
            })
            $(".dropdown").click(function(e) {
                e.stopPropagation();
            });
            $(document).click(function() {
                $(".dropdown").hide();
                $(".btn-group-click .btn").find("i").removeClass("btn-arrowup");
            });
        })();
    */


	// 独立域表单获取焦点样式
	$(".text").focus(function(){
		$(this).addClass("focus");
	}).blur(function(){
		$(this).removeClass('focus');
	});
	$("textarea").focus(function(){
		$(this).closest(".textarea").addClass("focus");
	}).blur(function(){
		$(this).closest(".textarea").removeClass("focus");
	});
});

/* 上传图片预览弹出层 */
$(function(){
	$(window).resize(function(){
		var winW = $(window).width();
		var winH = $(window).height();
		$(".upload-img-box").click(function(){
			//如果没有图片则不显示
			if($(this).find('img').attr('src') === undefined){
				return false;
			}
			// 创建弹出框以及获取弹出图片
			var imgPopup = "<div id=\"uploadPop\" class=\"upload-img-popup\"></div>"
			var imgItem = $(this).find(".upload-pre-item").html();

			//如果弹出层存在，则不能再弹出
			var popupLen = $(".upload-img-popup").length;
			if( popupLen < 1 ) {
				$(imgPopup).appendTo("body");
				$(".upload-img-popup").html(
					imgItem + "<a class=\"close-pop\" href=\"javascript:;\" title=\"关闭\"></a>"
				);
			}

			// 弹出层定位
			var uploadImg = $("#uploadPop").find("img");
			var popW = uploadImg.width();
			var popH = uploadImg.height();
			var left = (winW -popW)/2;
			var top = (winH - popH)/2 + 50;
			$(".upload-img-popup").css({
				"max-width" : winW * 0.9,
				"left": left,
				"top": top
			});
		});

		// 关闭弹出层
		$("body").on("click", "#uploadPop .close-pop", function(){
			$(this).parent().remove();
		});
	}).resize();

	// 缩放图片
	function resizeImg(node,isSmall){
		if(!isSmall){
			$(node).height($(node).height()*1.2);
		} else {
			$(node).height($(node).height()*0.8);
		}
	}
})

//标签页切换(无下一步)
function showTab() {
	$(".tab-nav li").click(function(){
		var self = $(this), target = self.data("tab");
		self.addClass("current").siblings(".current").removeClass("current");
		window.location.hash = "#" + target.substr(3);
		$(".tab-pane.in").removeClass("in");
		$("." + target).addClass("in");
	}).filter("[data-tab=tab" + window.location.hash.substr(1) + "]").click();
}

//标签页切换(有下一步)
function nextTab() {
	$(".tab-nav li").click(function(){
		var self = $(this), target = self.data("tab");
		self.addClass("current").siblings(".current").removeClass("current");
		window.location.hash = "#" + target.substr(3);
		$(".tab-pane.in").removeClass("in");
		$("." + target).addClass("in");
		showBtn();
	}).filter("[data-tab=tab" + window.location.hash.substr(1) + "]").click();

	$("#submit-next").click(function(){
		$(".tab-nav li.current").next().click();
		showBtn();
	});
}

// 下一步按钮切换
function showBtn() {
	var lastTabItem = $(".tab-nav li:last");
	if( lastTabItem.hasClass("current") ) {
		$("#submit").removeClass("hidden");
		$("#submit-next").addClass("hidden");
	} else {
		$("#submit").addClass("hidden");
		$("#submit-next").removeClass("hidden");
	}
}

//导航高亮
function highlight_subnav(url){
	$('.side-sub-menu').find('a[href="'+url+'"]').closest('li').addClass('current');
}


//自动计算到帐金额
function Gtnum(S){
	var V = parseFloat($('#money').val());
	if(isNaN(V)) V = 0;
	V = V.toFixed(2);
	var P = 0;
	if(V > 0){
		if(S.indexOf('%') > 0){
			S = parseFloat(S.replace('%',''));
			P = (V * S / 100).toFixed(2);
		} else {
			P = parseFloat(S).toFixed(2);
		}
	}
	var H = (V - P).toFixed(2);
	$('#Gtnum').text(H);
	$('#Gsnum').text(P);
}

//货币格式化
function Gsnum(s){
	var v = parseFloat(s);
	if(isNaN(v)) v = 0;
	var n = parseFloat(v.toFixed(2));
	return n;
}

//秒格式化
function getTime(nS) {
	var time  =  (Math.floor(nS/3600) ? Math.floor(nS/3600)+':'+Math.floor(nS%3600/60) : Math.floor(nS/60))+':'+(nS%60/100).toFixed(2).slice(-2);
	return time;
}
//时间格式化
function getLocalTime1(nS) {
	return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/,'');
}



function getGameTime(nS) {
	var time  =  (Math.floor(nS/3600) ? Math.floor(nS/3600)+':'+Math.floor(nS%3600/60) : Math.floor(nS/60))+':'+(nS%60/100).toFixed(2).slice(-2);
	strs=time.split(":"); 
	if (strs[0] < 10) {
		strs[0] = '0' + strs[0];
	}
	var timeHtml = '<span class="time_number">' + strs[0] + '</span>:<span class="time_number">' +  strs[1] +'</span>';
	return timeHtml;
}
//时间格式化
function getLocalTime(nS, type) {
	var newDate = new Date();
	newDate.setTime(nS * 1000);
	Date.prototype.format = function(format) {
		var date = {
			"M+": this.getMonth() + 1,
			"d+": this.getDate(),
			"h+": this.getHours(),
			"m+": this.getMinutes(),
			"s+": this.getSeconds(),
			"q+": Math.floor((this.getMonth() + 3) / 3),
			"S+": this.getMilliseconds()
		};
		if (/(y+)/i.test(format)) {
			format = format.replace(RegExp.$1, (this.getFullYear() + '').substr(4 - RegExp.$1.length));
		}
		for (var k in date) {
			if (new RegExp("(" + k + ")").test(format)) {
				format = format.replace(RegExp.$1, RegExp.$1.length == 1 ? date[k] : ("00" + date[k]).substr(("" + date[k]).length));
			}
		}
		return format;
	}
	type = type ? type : 'yyyy-MM-dd hh:mm:ss';
	return newDate.format(type);
}

//播放开奖音乐
function playSound() {
	var a = $('#SOUND');
	if (a.length == 0) {
		a = $('<audio id="SOUND"><source src="statics/images/kaijiang.mp3" type="audio/mpeg"/></audio>').appendTo($('body'))[0];
		if (a.load) {
			a.load()
		}
	} else {
		a = a[0]
	}
	if (a.play) {
		a.play()
	}
}

//播放倒计时音乐
function playSound_Djs() {
	var a = $('#DAOJISHI');
	if (a.length == 0) {
		a = $('<audio id="DAOJISHI"><source src="statics/images/daojishi.mp3" type="audio/mpeg"/></audio>').appendTo($('body'))[0];
		if (a.load) {
			a.load()
		}
	} else {
		a = a[0]
	}
	if (a.play) {
		a.play()
	}
}

//隐藏部分字符串
//frontLen: 前面需要保留几位    endLen: 后面需要保留几位
function hiddenStr(str, frontLen, endLen) {
	var len = str.length - frontLen - endLen;
	if (len <= 0) {
		return str.substring(0, 1) + '***';
	} else if (len <= 2) {
		frontLen = 1;
		endLen = 1;
	}
	var xing = '';
	for(var i = 0; i < len; i++) {
		xing += '*';
	}
	return str.substring(0, frontLen) + xing + str.substring(str.length - endLen);
}

//生成随机字符串
function randomStr(len) {
	len = len || 6;
	var chars = 'abcdefhijkmnprstwxyz123456789';
	var maxPos = chars.length;
	var Str = '';
	for(i = 0; i < len; i++) {
		Str += chars.charAt(Math.floor(Math.random() * maxPos));
	}
	return Str;
}

$(function(){
	//TOP效果
	$(window).scroll(function(){
		if ($(window).scrollTop()>200){
			$('#s_top').fadeIn(500);
		}else{
			$('#s_top').fadeOut(500);
		}
	});
	$('#s_top').click(function(){
		$('body,html').animate({scrollTop:0},500);
		return false;
	});

	//客服
	var button_toggle = true;
	$(".show_box").on("mouseover", function(){
		var tip_top;
		var show= $(this).attr('show');
		var hide= $(this).attr('hide');
		tip_top = show == 'tel' ?  42 :  -18;
		button_toggle = false;
		$("#show_box").css("top" , tip_top).show().find(".flag_"+show).show();
		$(".flag_"+hide).hide();
	}).on("mouseout", function(){
		button_toggle = true;
		hideRightTip();
	});
	$("#show_box").on("mouseover", function(){
		button_toggle = false;
		$(this).show();
	}).on("mouseout", function(){
		button_toggle = true;
		hideRightTip();
	});
	function hideRightTip(){
		setTimeout(function(){
			if( button_toggle ) $("#show_box").hide();
		}, 500);
	}

});
