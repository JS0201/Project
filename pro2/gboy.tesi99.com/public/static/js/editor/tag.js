UE.registerUI('商城名称 用户名 用户手机 用户邮箱 商品名称 商品规格 主订单号 订单金额 商品金额 付款金额 支付方式 充值金额 邮件验证链接 验证码 配送方式 运单号 用户可用余额 变动金额', function( editor, uiName ) {
	//注册按钮执行时的command命令，使用命令默认就会带有回退操作
    editor.registerCommand(uiName, {
		text: ''+uiName+'',
        execCommand: function() {
           
        }
		
    });
    //创建一个button
    var btn = new UE.ui.Button({
        //按钮的名字
        name: 'gboytag',
        //提示
        title: uiName,
        //添加额外样式，指定icon图标，这里默认使用一个重复的icon
        cssRules: '',
		label:uiName,
        //点击时执行的命令
        onclick: function() {
            //这里可以不用执行命令,做你自己的操作也可
            editor.execCommand('insertHtml', '{'+uiName+'}');
        }
    });
    //当点到编辑内容上时，按钮要做的状态反射
    editor.addListener('selectionchange', function() {
        var state = editor.queryCommandState(uiName);
        if (state == -1) {
            btn.setDisabled(true);
            btn.setChecked(false);
        } else {
            btn.setDisabled(false);
            btn.setChecked(state);
        }
    });
	//alert(JSON.stringify(btn))
	//btn.setAttribute('class','edui-haidaotag');
	
    //因为你是添加button,所以需要返回这个button
    return btn;
});
