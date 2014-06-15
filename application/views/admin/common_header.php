<!DOCTYPE html>
<html>
<head>
	<title><?= $this->config->item('app_name') ?></title>

	<meta charset="utf-8">
	
	<link rel="stylesheet" href="<?= base_url() ?>assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="<?= base_url() ?>assets/css/bootstrap-theme.min.css">
	<link rel="stylesheet" href="<?= base_url() ?>assets/css/admin.css">
	
	<link rel="stylesheet" media="screen" href="<?= css_link('flash_msg.css') ?>">

	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
	<script src="<?= base_url() ?>assets/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="<?= js_link('transit.js') ?>"></script>
	<script type="text/javascript" src="<?= js_link('flash.handle.js') ?>"></script>
	<script type="text/javascript" src="<?= js_link('ckeditor/ckeditor.js') ?>"></script>


	
</head>
<body class="admin">
	<?= $this->flashmanager->print_msgs(); ?>
		
	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="container">
			<div class="navbar-header">
				<a href="#" class="navbar-brand"><?= $this->config->item('app_name') ?></a>
			</div>
			<div class="collapse navbar-collapse">
				<ul class="nav navbar-nav">
					<?php foreach ($this->admin_menu as $k => $item): ?>
						<li class="list"><?= a( 'admin/'.$item['method'], t($item['label'], 'admin'), array('class' => 'menu-item')   ) ?></li>
					<?php endforeach ?>
				</ul>
			</div><!--/.navbar-inner-->
		</div>
	</div>

	<div id="container" class="container">
		<div class="starter-template">
			<div id="header" class="">
				<h1></h1>
				
			</div><!--/#header-->