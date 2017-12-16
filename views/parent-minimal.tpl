<!DOCTYPE html>
<html class="{block name=htmlClass}{/block}">
	<head>
		<title>{if isset($title)}{$title} - InspireVive{else}InspireVive{/if}</title>

		<meta charset="utf-8"> 
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
		{block name=meta}
	    <meta name="description" content="InspireVive helps organizations volunteer more effectively." />

	    <meta property="og:title" content="InspireVive" />
	    <meta property="og:type" content="website" />
	    <meta property="og:description" content="InspireVive helps organizations volunteer more effectively." />
	    <meta property="og:image" content="http:{$app.view_engine->asset_url('/img/icon.jpg')}" />
	    <meta property="og:site_name" content="InspireVive" />

	    <meta name="twitter:card" content="summary" />
	    <meta name="twitter:title" content="InspireVive" />
	    <meta name="twitter:description" content="InspireVive helps organizations volunteer more effectively." />
	    <meta name="twitter:image" content="http:{$app.view_engine->asset_url('/img/icon.jpg')}" />
		{/block}
		
		<link href="{$app.view_engine->asset_url('/css/bootstrap.min.css')}" rel="stylesheet" type="text/css" />
		<link href="{$app.view_engine->asset_url('/css/jquery-ui.css')}" rel="stylesheet"  type="text/css" />
		<link href="{$app.view_engine->asset_url('/css/styles.css')}" rel="stylesheet" type="text/css" />
		<link rel="shortcut icon" href="{$app.view_engine->asset_url('/favicon.ico')}" type="image/x-icon" />

		<script type="text/javascript" src="{$app.view_engine->asset_url('/js/jquery.min.js')}"></script>
		<script src="{$app.view_engine->asset_url('/js/bootstrap.min.js')}"></script>
		<script type="text/javascript" src="{$app.view_engine->asset_url('/js/jquery-ui.min.js')}"></script>
		<script type="text/javascript" src="{$app.view_engine->asset_url('/js/header.js')}"></script>
		
		{block name=header}{/block}
</head>
<body class="{if isset($tabClass)}tab-active-{$tabClass}{/if}">
	{block name=main}{/block}
</body>
</html>