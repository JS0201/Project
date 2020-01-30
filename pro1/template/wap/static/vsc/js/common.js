$(function () {
	//取消移动端默认300ms延迟
//	FastClick.attach(document.body);
	
	$(".tab-hd-item").on("click",function () {
		$(this).addClass("cur").siblings().removeClass("cur");
		$(this).parents(".tab-bar").find(".tab-item").eq($(this).index()).show().siblings().hide()
	})
	$("[model-data]").on('click',function () {
		var target = $(this).attr('model-data');
		$('.container'+target).show();
	})
	$(".dialog .close").on('click',function () {
		$('.container').hide();
	})	
	
	$('header .left-back').off().on('click',function(){
		window.history.back(-1)
	})
	
})
		
