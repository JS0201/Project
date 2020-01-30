$(function () {
	$(".lp-back").on("click",function () {	history.back(-1);});
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
	
})
		
