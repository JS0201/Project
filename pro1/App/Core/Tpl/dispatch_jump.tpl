<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>提示</title>
                <link type="text/css" rel="stylesheet" href="<?php echo $root?>/statics/css/gboy.css" />
		<link type="text/css" rel="stylesheet" href="<?php echo $root?>/statics/css/admin.css" />
		<script type="text/javascript" src="<?php echo $root?>/statics/js/jquery-1.7.2.min.js"></script>
                <script type="text/javascript" src="<?php echo $root?>/statics/js/gboy.plug.js"></script>
	</head>
	
	<body>
 
	<?php 
    

		if($jumpUrl!='javascript:history.back(-1);' && $jumpUrl!='null' &&  $jumpUrl!='-1' ){
			$jumpUrl=$jumpUrl.'&formhash='.$_REQUEST['formhash'];
		}else{
			$jumpUrl = 'javascript:history.back()';
		}
	?>
		<div class="padding-big layout">
			<div class="success-info layout border radius bg-white">
				<div class="title border-bottom text-white">
					<h6>温馨提示</h6>
				</div>
				<div class="text border-bottom">
					<?php if(isset($message)) {?>
					<span><?php echo $message; ?></span>
					<?php }else{?>
				
					<span><?php echo $error; ?></span>
					<?php }?>
					
					<?php if($jumpUrl != 'null') : ?>
					<p><a href="<?php echo $jumpUrl; ?>">如果您的浏览器没有自动跳转，请点击这里</a></p>
					<?php endif;?>
				</div>
			</div>
		</div>
<script type="text/javascript">
<?php if($jumpUrl != 'null') : ?>
setTimeout(function() {
	location.href = '<?php echo strip_tags($jumpUrl); ?>';
}, 2000);
<?php endif;?>
</script>
	</body>
</html>
