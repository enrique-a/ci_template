<!DOCTYPE html>
<html lang="<?php echo $page->language; ?>">
<head>
	<meta charset="<?php echo $page->charset; ?>">
	<title><?php echo $page->title; ?></title>
<?php foreach($page->meta as $name => $content): ?>
	<meta name="<?php echo $name; ?>" content="<?php echo $content; ?>">
<?php endforeach; ?>
<?php foreach($page->css as $css_file): ?>
	<link rel="stylesheet" href="<?php echo $css_file; ?>" type="text/css">
<?php endforeach; ?>
</head>
<body>
<?php if($page->title_enabled): ?>
	<h1><?php echo $page->title; ?></h1>
<?php endif; ?>
<?php $page->display_content(); ?>
<?php foreach($page->js as $js_file): ?>
	<script src="<?php echo $js_file; ?>"></script>
<?php endforeach; ?>
<?php if($page->js_inline): ?>
	<script>
<?php 	echo $page->js_inline; ?> 
	</script>
<?php endif; ?>
</body>
</html>