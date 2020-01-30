var cart = (function () {

    return {

        /* 加入购物车 */
        cart_add: function (goods_id, num) {
            gboy.load();
            var bool = false;
            if (num < 1)
                return;
            var params = {};
            params[goods_id] = num;
            $.ajaxSettings.async = false;
            $.getJSON("index.php?m=order&c=cart&a=cart_add", {params: params}, function (ret) {

                if (ret.status == 1) {

                    bool = true;

                } else {

                    bool = false;
                }
            });
            $.ajaxSettings.async = true;
            return bool;
        },

        /* 改变数量 加减按钮 */
        number: function (goods_id, buy_nums) {

            gboy.load();
            $.getJSON('?m=order&c=cart&a=set_nums', {sku_id: goods_id, nums: buy_nums}, function (ret) {
                gboy.close();
                if (ret.status == 1) {
                    update_total();
                } else {
                    gboy.msg(ret.message);
                }

            });
        },

        /* 立即购买 */
        buy_now: function (goods_id, buy_nums){
            gboy.load();
            // 组装数据格式
            var params = {};
            params[goods_id] = buy_nums;
            $.ajax({
                url: 'index.php?m=order&c=cart&a=cart_add',
                data: {params: params, buynow: true},
                type: 'GET',
                dataType: 'json',
                success: function (ret) {
                    gboy.close();
                    if (ret.status == 0) {

                        gboy.msg(ret.message, '', 0);
                        return false;
                    }
                    // 直接跳转到结算页面
                     window.location.href = 'index.php?m=order&c=cart&a=settlement';
                }
            });
        },

        /* 联盟商家立即购买 */
        buy_now1: function (goods_id, buy_nums){
            gboy.load();
            // 组装数据格式
            var params = {};
            params[goods_id] = buy_nums;
            $.ajax({
                url: 'index.php?m=order&c=cart&a=cart_add',
                data: {params: params, buynow: true},
                type: 'GET',
                dataType: 'json',
                success: function (ret) {
                    gboy.close();
                    if (ret.status == 0) {

                        gboy.msg(ret.message, '', 0);
                        return false;
                    }
                    // 直接跳转到结算页面
                     window.location.href = 'index.php?m=order&c=cart&a=settlement1';
                }
            });
        },

        /* 删除购物车商品 */
        delpro: function (goods_id) {
            gboy.load();
            $.ajax({
                url: 'index.php?m=order&c=cart&a=delpro',
                data: {'sku_id': goods_id},
                type: 'GET',
                dataType: 'json',
                success: function (ret) {
                    gboy.close();
                    if (ret.status == 0) {
                        gboy.msg(ret.message, '', 0);

                    } else {
                        gboy.msg(ret.message, 'index.php?m=order&c=cart&a=index');
                    }

                }
            });
        },

        /* 清空购物车 */
        clear: function () {
            gboy.load();
            $.ajax({
                url: 'index.php?m=order&c=cart&a=clear',
                type: 'GET',
                dataType: 'json',
                success: function (ret) {
                    gboy.close();
                    gboy.url('index.php?m=order&c=cart&a=index');
                }
            });
        },

        /* 创建订单 */
        create_order: function (order_params) {
            gboy.load();

            $.ajax({
                url: 'index.php?m=order&c=cart&a=create',
                data: order_params,
                type: 'POST',
                dataType: 'json',
                success: function (ret) {
					
                    gboy.close();
                    if (ret.status == 0) {

                        gboy.msg(ret.message, '', 0);

                    } else {

                        gboy.msg(ret.message, ret.referer);
                    }

                }
            });
        },

    };
})();


$(function () {



    /* 添加购物车 */
    $("[data-event='cart_add']").on('click', function () {

        var goods_id = $(this).attr('data-skuids');
        var cart_type = $(this).attr('data-type');
        var num = $.trim($('input[name="number"]').val());
        if (num == '' || num == undefined || num <= 1) {
            num = 1;
        }

        var r = cart.cart_add(goods_id, num);

        var cart_url = '';

        if (cart_type == '1') {

            cart_url = 'index.php?m=order&c=cart&a=index';
        } else {
            cart_url = 'index.php?m=order&c=cart&a=cart';
        }


        if (r) {
            gboy.msg('购物车加入成功', cart_url);
        } else {
            gboy.msg('购物车加入失败', '', 0);
        }

    });

    /* 立即购买 */
    $("[data-event='buy_now']").on('click', function () {
        var goods_id = $(this).attr('data-skuids');
        var buy_nums = $('input[name="number"]').val() ? $('input[name="number"]').val() : 1;
        cart.buy_now(goods_id, buy_nums);
    });

    //土地认购
    $("[data-event='buy_now1']").on('click', function () {
        var goods_id = $(this).attr('data-skuids');
        var buy_nums = $('input[name="number"]').val() ? $('input[name="number"]').val() : 1;
        cart.buy_now1(goods_id, buy_nums);
    });

    /* 删除购物车商品 */
    $("[data-event='delpro']").on('click', function () {
        var goods_id = $(this).attr('data-skuids');
        cart.delpro(goods_id);
    });

    /* 清空购物车 */
    $("[data-event='clear']").on('click', function () {
        cart.clear();
    });


   
    /* 创建订单 */
    $("[data-event='create_order']").on('click', function () {

        var order_params = {};
       // var pay_method = $('#pay_method').val();

        var pay=$('.pay01');
        var pay_method="";
        pay.each(function(key,val){

            if($(val).find('.merchant-icon').hasClass('active')){
                pay_method=$(val).attr('data-pay')
            }

        })
        console.log(pay_method)
        var address_id = $('#address_id').val();
        var remarks = $.trim($('#introducebb').val());


        order_params.pay_method = pay_method;
        order_params.remarks = remarks;
        order_params.address_id = address_id;
		
	
		if(pay_method=='1'){
			if(confirm('是否确认使用ACNY支付？')){

			}else{
				return false;
			}
		}else if(pay_method=='2'){
            if(confirm('是否确认使用USDT支付？')){

            }else{
                return false;
            }
        }
		
		
         cart.create_order(order_params);
    });

    /* 改变数量 加减按钮 */
    $("[data-event=number]").on('click', function () {

        var obj = $(this);
        var goods_id = obj.attr('data-skuids');
        // 当前购买数
        var now_nums = parseInt(obj.parent().find("[data-id='buy-num']").val());
        // 最大购买数
        var max_nums = parseInt(obj.parent().find("[data-id='buy-num']").attr("data-max"));

        var adjust = obj.attr('data-adjust');
        if (adjust == 'add') {
            now_nums++;
        } else {

            if (now_nums <= 1)
                now_nums = 1;
            now_nums--;
        }
        now_nums = (now_nums < 1 || !now_nums) ? 1 : ((now_nums > max_nums) ? max_nums : now_nums);

        obj.parent().find("[data-id='buy-num']").val(now_nums)
        cart.number(goods_id, now_nums);

    });

    $("input[name='number']").on('keyup', function () {
        var val = $(this).val();
        var val2 = val.replace(/[^\d]/g, '');
        var min = parseInt($(this).attr("data-min"));
        var max = parseInt($(this).attr("data-max"));
        if ($(this).attr("data-min") == undefined) {
            min = 0
        } else if ($(this).attr("data-max") == undefined) {
            min = 999
        }
        if (val2 <= min) {
            val2 = min;
        } else if (val2 >= max) {
            val2 = max;
        }
        $(this).val(val2);

    });

    /* 改变数量 input文本 */
    $(".adjust-input").on('keyup', function () {
        var min = parseInt($(this).data("min"));	// 最小购买数
        var max = parseInt($(this).data("max"));	// 最大购买数
        var now_nums = parseInt($(this).val());			// 当前购买数
        now_nums = (now_nums < min || isNaN(now_nums)) ? 1 : now_nums;
        now_nums = (now_nums > max) ? max : now_nums;
        var sku_id = $(this).data("skuids");


        cart.number(sku_id, now_nums);
    });


})


// 更新当前页面总价
function update_total() {
    var total = 0, numbers = 0, checked_inputs = 0;
    var checked_inputs = $(".goods ul li");
    $(checked_inputs).each(function (k, v) {

        var price = 0, num = 0;
        price = parseFloat($(v).find("#num").attr('data-price'));
        console.log(price)
        num = parseInt($(v).find("input[data-id='buy-num']").val());
        max_nums = parseInt($(v).find("input[data-id='buy-num']").attr("data-max"));
        num = (num < 1 || !num) ? 1 : ((num > max_nums) ? max_nums : num);
        total += price * num;

        numbers += num;
        $('#total-price').text(total.toFixed(2))
    });
    // $("[data-id='totle']").text('￥' + total.toFixed(2));
    // $("[data-id='sku-numbers']").text(parseInt(numbers));
    // 结算按钮
    /*
     if (checked_inputs.length > 0) {
     $("[data-id=sub-btn]").removeClass('disabled');
     } else {
     $("[data-id='chekced-all']").prop("checked", false);
     $("[data-id=sub-btn]").addClass('disabled');
     }
     */



    // 更新当前页面总价
    function update_total(){
        var total = 0, numbers = 0, checked_inputs = 0;
        var checked_inputs = $(".cart-list .cart-case");

        $(checked_inputs).each(function (k, v) {

            var price = 0, num = 0;
            price = parseFloat($(v).find("#num").attr('data-price'));
            // console.log(price)
            num = parseInt($(v).find("input[data-id='buy-num']").val());
            // console.log(num)
            max_nums = parseInt($(v).find("input[data-id='buy-num']").attr("data-max"));
            num = (num < 1 || !num) ? 1 : ((num > max_nums) ? max_nums : num);
            total += price * num;
            // console.log(total)
            $('#total-price').text(total.toFixed(2))

            numbers += num;
            console.log(numbers)

        });
    }
}