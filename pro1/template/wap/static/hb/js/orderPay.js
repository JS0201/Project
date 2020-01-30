// 点击结算进行相关的弹框操作
$('.pay .btn').on('click', function () {
  mui.confirm("订单金额:5356.00", "确认订单", ["取消", "立即支付"], function (e) {
    console.log(e);
    if (e.index == 1) {
      mui.prompt("您已经开启资金密码保护", "请输入资金密码", "本次消费5366.00(ACNY)", ["取消", "确定"], function (e) {
        console.log(e);
        if (e.index == 1) {
          mui.toast('当前ACNY可用余额不足，请前往充值', {
            duration: 'long',
            type: 'div'
          });
        }
      });
    }
  });
});






