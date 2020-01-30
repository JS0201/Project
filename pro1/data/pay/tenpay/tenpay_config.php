<?php
$spname="财付通双接口测试";
$partner = "1281772101";                                  	//财付通商户号
$key = "d10940eadfb45a967f5adcf51c412a95";											//财付通密钥

$return_url = "http:www.fruition-sz.cn/index.php?m=pay&a=tshow";			//显示支付结果页面,*替换成payReturnUrl.php所在路径
$notify_url = "http:www.fruition-sz.cn/index.php?m=pay&a=tshow";			//支付完成后的回调处理页面,*替换成payNotifyUrl.php所在路径
?>