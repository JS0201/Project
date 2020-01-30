var gboy = (function () {

    return {

        //加载
        load: function () {
            var index = layer.load(1, {

                shade: [0.2, '#000'] //0.1透明度的白色背景
            });

        },
        close: function () {
            layer.closeAll();
        },

        msg: function (message, url, type) {
            if (type == '0') {
                type = ' fa-close (alias)';
            } else {
                type = ' fa-check';
            }
            layer.open({
                type: 1,
                skin: 'gboy_msg',
                closeBtn: 0,
                title: false,
                anim: false,
                shade: false,
                time: 2000,
                content: '<i class="fa  ' + type + ' fa-2x"></i><br />' + message,
                end: function (layero, index) {

                    if (url != '' && url != undefined) {

                        gboy.url(url);
                    }
                }
            });
        },

        _confirm: function (message, action, par, url) {

            layer.open({
                type: 1,
                title: false,
                skin: 'gboy_confirm', //样式类名
                closeBtn: 0, //不显示关闭按钮
                anim: 2,
                shadeClose: false, //开启遮罩关闭
                btn: ['确认', '取消'], //按钮
                content: message,
                yes: function (index, layero) {
					gboy.load();
                    $.ajax({
                        url: action,
                        data: par,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {

                            layer.close(index);
                            if (data.status == '0') {

                                gboy.msg(data.message, '', 0);
                            } else {
                                if (url == '' || url == undefined) {
                                    gboy.msg(data.message);
                                } else {
                                    gboy.url(url);
                                }

                            }
                        }
                    });

                    return false;


                },
                btn2: function (index, layero) {


                }
            });
        },

        url: function (url) {

            location.href = url;
        },

        send: function (obj) {

            var url = $(obj).attr('action');
            var par = $(obj).serialize();

            $.post(url, par, function (data) {

                if (data.status == '0') {
                    gboy.msg(data.message, '', 0);
                } else {
                    gboy.msg(data.message, data.referer);
                }
            }, 'json');



        }


    };
})();

$(function () {

    $("[data-event='submit']").on('tap', function () {
        var obj_form = $(this).attr('data-form');
        if (obj_form == '' || obj_form == undefined) {
            obj_form = 'myform';
        }
        gboy.send('.' + obj_form);
    });


    $("[data-event='confirm']").on('tap', function () {

        var message = $(this).attr('data-message');
        var action = $(this).attr('data-action');
        var url = $(this).attr('data-url');
        var params = $(this).attr('data-params');
        gboy._confirm(message, action, params, url);
    });


    $("[data-event='collect']").on('tap', function () {
		
        gboy.load();
        var _this = $(this);
        var url = "?m=member&c=favorite&a=add";
        var sku_id = _this.attr('data-skuid');
        //var sku_price = _this.attr('data-price');
        // var sku_name = _this.attr('data-name');

        $.post(url, {sku_id: sku_id}, function (data) {
             gboy.close();
            if (data.status == 1) {
                _this.addClass('collect_ok');
                gboy.msg(data.message);
            } else {
                gboy.msg(data.message, '', 0);
            }
        }, 'json');
    });





})