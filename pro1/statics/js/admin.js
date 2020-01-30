$(function() {
	var sideTop;
	var height;
	var maxNum;
	var num = 0;
	var sh;

	//显示与隐藏侧边栏
	$(".ico-left").click(function() {
		if ($(this).attr('class') == "ico-left") {
			$(this).addClass("close-left");
			$(".side").animate({
				left: "-200px"
			}, 400);
			$("#main").animate({
				left: "0"
			}, 400);
			$(".welcome ul").animate({
				marginLeft: "18px"
			}, 400);
		} else {
			$(this).removeClass("close-left");
			$(".side").animate({
				left: "0"
			}, 400);
			$("#main").animate({
				left: "200px"
			}, 400);
			$(".welcome ul").animate({
				marginLeft: "88px"
			}, 400);
		}
	});

	//上滑
	$(".show-side .top").click(function() {
		num++;
		if (num > maxNum) {
			num = maxNum;
		}
		$(".side-menu-height").animate({
			marginTop: "-" + num * sh + "px"
		}, 100);
	});
	//下滑
	$(".show-side .bottom").click(function() {
		num--;
		if (num <= 0) {
			num = 0;
		}
		$(".side-menu-height").animate({
			marginTop: "-" + num * sh + "px"
		}, 100);
	});

	$("#side-scroll").find("a").click(function() {
		$("#side-scroll").find("a").each(function() {
			$(this).removeClass("focus");
		});
		$(this).addClass("focus");
		$("#main_frame").attr("src", $(this).attr("href"));
		return false;
	})

	$('.form-select-edit').parents(".form-group").each(function(i) {
		$(this).css({
			zIndex: i + 2
		})
	})

	//侧边菜单滑动
	$(window).resize(function() {
			if ($(".copy").length > 0) {
				sideTop = $(".copy").offset().top - 131; //窗口高度
				height = $(".side-menu-height").height(); //菜单高度
				sh = height / $(".side-menu-height").find("li").length; //滚动高度
				//菜单的高度减去向上滚动的距离，未触动滚动效果时为0
				var str = $(".side-menu-height").css("marginTop");
				var numerial = parseFloat(str.substring(0, str.length - 2));
				var no_s_height = height + numerial; //菜单未滚动的区域
				//滚动后放大窗口时触发
				if (sideTop > no_s_height && numerial != 0 && sideTop < height) {
					$(".side-menu-height").css({
						marginTop: numerial + (sideTop - no_s_height) + "px"
					});
					maxNum = Math.ceil((numerial + 14) / sh);
				} else if (sideTop > height) {
					$(".side-menu-height").css({
						marginTop: "0px"
					});
					num = 0;
				} else {
					maxNum = Math.ceil((height - sideTop + 14) / sh);
				}

				if (num > maxNum) {
					num = maxNum;
				}
				//显示隐藏上下箭头
				if (sideTop < height) {
					$(".show-side").css({
						bottom: "144px"
					});
				} else {
					$(".show-side").css({
						bottom: "130px"
					});
				}
			}
		})
		//侧边滑动
	$(window).trigger('resize');
})

$(function() {
	$("input[name=limit]").bind('keyup', function(e) {
		if (e.keyCode == 13) {
			var url = replaceParamVal('limit', $(this).val());
			console.log(url);
			window.location.href = url
			return false;
		}
	});


})

/**
 * 替换URL目标参数值
 * @param  {string} arg 参数名
 * @param  {string} val 参数值
 * @param  {string} url 目标地址
 * @return {string}
 */
function replaceParamVal(arg, val, url) {
	url = url || this.location.href.toString();
	var pattern = arg + '=([^&]*)';
	var replaceText = arg + '=' + val;
	if (url.match(pattern)) {
		var tmp = '/(' + arg + '=)([^&]*)/gi';
		tmp = url.replace(eval(tmp), replaceText);
		return tmp;
	} else {
		if (url.match('[\?]')) {
			return url + '&' + replaceText;
		} else {
			return url + '?' + replaceText;
		}
	}
	return url + '\n' + arg + '\n' + val;
}


$(function() {
		//加入自定义快捷菜单
		$('.fixed-nav .first a').on('click', function() {
		
			$.post(menuaddurl, {
				'_m': SYS_MODULE_NAME,
				'_c': SYS_CONTROL_NAME,
				'_a': SYS_METHOD_NAME
			}, function(data) {
						
				_progress(data.message);
			},'json');
			refresh_diymenu();
		})
	})
	//刷新自定义菜单
function refresh_diymenu() {
	$.post(menurefreshurl, '', function(data) {
	
		$('#diy_menu', window.parent.document).html(data);
	})
}

function _progress(content) {
	var d = dialog({
		id: 'trip',
		padding: 30,
		align: 'bottom left',
		content: '' + content + '',
		quickClose: true
	}).show();
	setTimeout(function() {
		d.close().remove();
	}, 1000);
}

(function($){
    $.fn.FontScroll = function(options){
        var d = {time: 3000,s: 'fontColor',num: 1}
        var o = $.extend(d,options);


        this.children('ul').addClass('line');
        var _con = $('.line').eq(0);
        var _conH = _con.height(); //滚动总高度
        var _conChildH = _con.children().eq(0).height();//一次滚动高度
        var _temp = _conChildH;  //临时变量
        var _time = d.time;  //滚动间隔
        var _s = d.s;  //滚动间隔


        _con.clone().insertAfter(_con);//初始化克隆

        //样式控制
        var num = d.num;
        var _p = this.find('li');
        var allNum = _p.length;
        if(allNum == num + 1) return false;

        _p.eq(num).addClass(_s);


        var timeID = setInterval(Up,_time);
		this.hover(function(){clearInterval(timeID)},function(){timeID = setInterval(Up,_time);});

        function Up(){
            _con.animate({marginTop: '-'+_conChildH});
            //样式控制
            _p.removeClass(_s);
            num += 1;
            _p.eq(num).addClass(_s);

            if(_conH == _conChildH){
                _con.animate({marginTop: '-'+_conChildH},"normal",over);
            } else {
                _conChildH += _temp;
            }
        }
        function over(){
            _con.attr("style",'margin-top:0');
            _conChildH = _temp;
            num = 1;
            _p.removeClass(_s);
            _p.eq(num).addClass(_s);
        }
    }
    
    $.hdLoad = {
    	start: function(){
    		if($("#hd-load-tips").length > 0){
    			this.loading();
    			return;
    		}
			if(typeof this.elems == "undefined") this.elems = [];
    		var name = 'hd-load-' + Date.now();
    		this.elems.push(name);
    		$('body').append('<div class="hd-load-tips '+ name +'"><div class="load-tips-text">开始加载！</div><div class="load-tips-bg"></div></div>');
    		this.loading();
    	},
    	loading: function(){
    		$('.'+this.elems[0]).children(".load-tips-text").html('加载中，请稍后...');
    	},
    	end: function(){
    		var that = this;
    		$('.'+this.elems[0]).children(".load-tips-text").html('加载完成!');
			setTimeout(function(){
				$('.'+that.elems[0]).animate({opacity: 0},300,function(){
					$('.'+that.elems[0]).remove();
					that.elems.splice(0,1);
				});
			},300);
		},
		error: function(){
			var that = this;
			$('.'+this.elems[0]).children(".load-tips-text").html('加载失败!').css({color: red});
			setTimeout(function(){
				$('.'+that.elems[0]).animate({opacity: 0},300,function(){
					$('.'+that.elems[0]).remove();
					that.elems.splice(0,1);
				});
			},300)
		}
    }
    
})(jQuery);

function message(t){
	var d = dialog({
	    content: '<div class="padding-large-left padding-large-right padding-top padding-bottom bg-white text-small">'+t+'</div>'
	});
	d.show();
	setTimeout(function () {
	    d.close().remove();
	}, 1000);
}

/**
 *显示分类 @lcl 2014-11-11 11:47:09
 */

//显示分类
function nb_category(pid, e){
    $(e).parent().nextAll('.child').empty();
    if($(e).hasClass('focus')){
        if($(e).parent().next('.child').children('a').length<=0){
            $(this).removeClass('focus');
        }else{
            var flog = true;
            $(e).parent().nextAll('div a').each(function(){
            if($(this).hasClass('focus')){
                flog = false;
            }
            });
            if(flog){
                _this.removeClass('focus');
            }
        }
    }else{
        $(e).addClass('focus').siblings().removeClass('focus');
    }   
    var strHTML = "";
    $.each(jsoncategory, function(InfoIndex, Info){
    if (pid == Info.parent_id)
        strHTML += " <a href = 'javascript:void(0)' onclick = 'nb_category(" + Info.id + ",this)' id = 'cat" + Info.id + "' data-id = " + Info.id + " > " + Info.name + " </a>";
    });
    if (pid == 0){
    	$(".root").html(strHTML);
    } else{
    	$(e).parent().next('div').css('background', '#FFF');
        $(e).parent().next('div').html(strHTML);
    }
}

//显示分类
function nb_p_category(pid, e){
    if($(e).hasClass('disable')){
        return false;
    }
    $(e).parent().nextAll('.child').empty();
    if($(e).hasClass('focus')){
        if($(e).parent().next('.child').children('a').length<=0){
            $(this).removeClass('focus');
        }else{
            var flog = true;
            $(e).parent().nextAll('div a').each(function(){
            if($(this).hasClass('focus')){
                flog = false;
            }
            });
            if(flog){
                _this.removeClass('focus');
            }
        }
    }else{
        $(e).addClass('focus').siblings().removeClass('focus');
    }   
   var strHTML = "";
    $.each(jsoncategory, function(InfoIndex, Info){
    if (pid == Info.parent_id)
        strHTML += " <a href = 'javascript:void(0)' onclick = 'nb_p_category(" + Info.id + ",this)' id = 'cat" + Info.id + "' data-id = " + Info.id + " > " + Info.name + " </a>";
    });
    if (pid == -1){
        $(".root").html(strHTML);
    } else{
        $(e).parent().next('div').css('background', '#FFF');
        $(e).parent().next('div').html(strHTML);
    }
}
