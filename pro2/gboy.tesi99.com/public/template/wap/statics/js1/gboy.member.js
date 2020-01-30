var member = (function () {

    return {

        /* 加入购物车 */
        address: function () {

            var obj_form = $('.myform');
            var action = obj_form.attr('action');
            var params = obj_form.serialize();

            $.post(action, params, function (ret) {

                if (ret.status == 1) {
                    gboy.msg(ret.message, ret.referer);
                } else {
                    gboy.msg(ret.message, '', 0);
                }
            },'json');

        },

    };
})();


$(function () {



    $("[data-event='address']").on('tap', function () {
        member.address();
    });


})

