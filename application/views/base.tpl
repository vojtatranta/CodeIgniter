<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title>{config('app_name')} | {$title|default:''}</title>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
	<script src="{'bootstrap.min.js'|js_link}"></script>
	<script src="//cdn.datatables.net/1.10.0/js/jquery.dataTables.js"></script>
	<script src="//cdn.datatables.net/plug-ins/28e7751dbec/integration/bootstrap/3/dataTables.bootstrap.js"></script>
	<script src="{'flash.handle.js'|js_link}"></script>
	<script src="{'chosen/chosen.jquery.min.js'|js_link}"></script>
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,600,700&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="//cdn.datatables.net/plug-ins/28e7751dbec/integration/bootstrap/3/dataTables.bootstrap.css">
	<link rel="stylesheet" href="{'bootstrap.min.css'|css_link}">
	<link rel="stylesheet" href="{'chosen/chosen.min.css'|js_link}">
	<link rel="stylesheet" href="{'flash_msg.css'|css_link}">
	<link rel="stylesheet" href="{'less.php?less=custom.less'|css_link}">
	{block name='css'}
	{/block}
	
</head>
<body class="{block name='body_css'}{/block}">
{$this->flashmanager->print_msgs()}
	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="container">
			<div class="navbar-header">
				<a href="/czlt-kurzy" class="navbar-brand">{config('app_name')}</a>
			</div>
			<div class="collapse navbar-collapse">
				<ul class="nav navbar-nav">
				{block name='header'}
					{foreach from=$models['main'] item=model_name}
						<li class="{if (  isset($model_listing) AND $model_name == $model_listing)}active{/if} list">
							<a href="{base_url()}home/listing/{$model_name}">{t($model_name, 'admin')}</a>
						</li>
					{/foreach}
						<li class="dropdown">
					    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
					      Taxonomies <span class="caret"></span>
					    </a>
					    <ul class="dropdown-menu">
				        {foreach from=$models['dropdown'] item=model_name}
					      <li class="{if (  isset($model_listing) AND $model_name == $model_listing)}active{/if} list">
							<a href="{base_url()}home/listing/{$model_name}">{t($model_name, 'admin')}</a>
						</li>
				        {/foreach}
					    </ul>
					  </li>				
				{/block}
				
				</ul>
				<ul class="nav navbar-nav pull-right">
					<li class="pull-right">
						<a href="/">Přihlášky</a>
					</li>
				</ul>
				
			</div><!--/.navbar-inner-->
			
			<div class="collapse navbar-collapse">
				<ul class="nav navbar-nav">
				</ul>
			</div><!--/.navbar-inner-->
		</div>
	</div>
	<div class="container">
		{block name='content'}
			
		{/block}
	</div>
</body>

<script type="text/javascript" src="{'custom.js'|js_link}" ></script>
{block name='scripts'}
{/block}
</html>