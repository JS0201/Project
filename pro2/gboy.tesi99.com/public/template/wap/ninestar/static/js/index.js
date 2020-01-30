document.documentElement.style.fontSize = document.documentElement.clientWidth / 7.5 + 'px';

$(function() {
	$('.trade-heade >div').click(function() {
		$(this).addClass('active').siblings().removeClass('active');
		$('.trade-list .tarde-tab >div').eq($(this).index()).addClass('active').siblings().removeClass('active');
		
		$('.safe-center-from .safe-center-from-tab').eq($(this).index()).addClass('active').siblings().removeClass('active');
		
	});

})