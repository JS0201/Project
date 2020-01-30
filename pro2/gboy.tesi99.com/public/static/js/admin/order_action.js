/**
 * 		后台订单JS操作类
 */
var order_action = (function() {

	// 参数(tpl_url:必填)、宽度(可选) 
	function alert_tpl(data , width) {
		width = parseInt(width) > 0 ? width : 280;

		 parent.layer.open({
            type: 2,
            title: data.title,
            shadeClose: false,
            resize:false,
            scrollbar:false,
            move:false,
            shade: 0.3,
            btn: ['确认','取消'],
            area: [width, '580px'],
            content: data.tpl_url,
            yes:function( index,layero){
				//window.location.href= this.returnValue.referer;
				
				 var data=$(layero).find('iframe').contents().find('form').serialize();
				 var url=$(layero).find('iframe').contents().find('form').attr('action');
                $.post(url,data,function(data){
                    alert(data.message);
                    if(data.status=='1'){
                        parent.layer.closeAll();
                        location.reload();
                    }

                },'json');
				 
			}
		 })
	};
	
	return {

		/* 确认付款 */
		pay:function(tpl_url) {
		
			var param = {
				tpl_url : tpl_url,
				sn : order.sn,
				title : '确认付款',
				action:'pay'
			};
			alert_tpl(param);
		},

		/* 订单操作 */
		order : function(status , tpl_url) {
			var param = {
				
				tpl_url : tpl_url,
				
				sub_sn : order.sub_sn,
				title : '确认订单',
				status:status,
				action:'order'
			};
			if (status==4) {	// 删除订单
				$.post(tpl_url ,param ,function(ret) {
					alert(ret.message);
					if (ret.status != 1) return false;
					window.location.href = ret.referer;
				},'json');
				return false;
			} else {
				alert_tpl(param);
			}
		},

		/* 确认订单 */
		confirm: function(tpl_url) {
			var param = {
				tpl_url : tpl_url,
				sub_sn : order.sub_sn,
				title : '确认订单',
				action:'confirm'
			};
			alert_tpl(param);
		},

		/* 确认发货 */
		delivery:function(tpl_url){
			var param = {
				tpl_url : tpl_url,
				sub_sn : order.sub_sn,
				title : '确认发货',
				action:'delivery' 
			};
			alert_tpl(param);
		},

		/* 确认完成 */
		finish:function(tpl_url){
			var param = {
				tpl_url : tpl_url,
				sub_sn : order.sub_sn,
				title : '确认完成',
				action:'finish'
			};
			alert_tpl(param);
		},
		
		/* 确认配送 */
		complete_parcel: function(tpl_url) {
			var param = {
				tpl_url : tpl_url,
				title : '确认配送',
				action:'complete_parcel'
			};
			alert_tpl(param);
		},

		/* 修改应付总额 */
		update_real_price: function(tpl_url) {
			var param = {
				tpl_url : tpl_url,
				title : '修改应付总额',
				sub_sn : order.sub_sn
			};
			alert_tpl(param,480);
		},

		// 初始化
		init:function() {
			if (typeof(order) != 'object') {
				alert('无法读取此订单信息！');
				location.href = '/';
				return;
			}
		}
	};
})();