<?php
if($url == -1) {
	$url = 'javascript:history.back()';
}
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>温馨提示</title>
		<link type="text/css" rel="stylesheet" href="__ROOT__/static/css/bootstrap.min.css" />
		<script type="text/javascript" src="__ROOT__/static/js/bootstrap.min.js"></script>
	</head>
	<body>
	<div class="panel panel-default" style="margin: 20px;">
	  <div class="panel-heading">
		<h3 class="panel-title text-center">温馨提示</h3>
	  </div>
	  <div class="panel-body  text-center" >
			<h4><?php echo $message; ?></h4>
			<?php if($url != 'null') : ?>
			<p ></p>
			<h5 ><a href="<?php echo $url ?>"><small>如果您的浏览器没有自动跳转，请点击这里</small></a></h5>
			<?php endif;?>
	  </div>
	</div>

<script type="text/javascript">
<?php if($url != 'null') : ?>
setTimeout(function() {
	location.href = '<?php echo strip_tags($url) ?>';
}, 3000);
<?php endif;?>
</script>
	</body>
</html>

