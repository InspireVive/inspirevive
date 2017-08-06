<!DOCTYPE html>
<html {if isset($ngAppModule)}ng-app="{$ngAppModule}"{/if}>
	<head>
		<title>{if isset($title)}{$title} :: {$org->name} - InspireVive{else}InspireVive{/if}</title>

		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		
		<link href="{$app.view_engine->asset_url('/css/bootstrap.min.css')}" rel="stylesheet" type="text/css" />
		<link href="{$app.view_engine->asset_url('/css/jquery-ui.css')}" rel="stylesheet"  type="text/css" />
		<link href="{$app.view_engine->asset_url('/css/org-management-styles.css')}" rel="stylesheet" type="text/css" />
		<link rel="shortcut icon" href="{$app.view_engine->asset_url('/favicon.ico')}" type="image/x-icon" />
			
		<script type="text/javascript">
			var currentUser = {$app.user->toArray()|json_encode nofilter};
		</script>
		<script type="text/javascript" src="{$app.view_engine->asset_url('/js/jquery.min.js')}"></script>
		<script src="{$app.view_engine->asset_url('/js/bootstrap.min.js')}"></script>
		<script type="text/javascript" src="{$app.view_engine->asset_url('/js/jquery-ui.min.js')}"></script>
		<script type="text/javascript" src="{$app.view_engine->asset_url('/js/org-management-header.js')}"></script>
		
		{block name=header}{/block}
</head>
<body class="admin-section organization-admin">
	<div class="container main-container">
		<nav class="navbar navbar-default" role="navigation">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="{$org->manageUrl()}">
					{$org->name}
					<small>Management</small>
				</a>
			</div>

			<div class="collapse navbar-collapse navbar-ex1-collapse">
				<ul class="nav navbar-nav navbar-right">
					<li>
						<a href="{$org->url()}">
							<span class="glyphicon glyphicon-home"></span>
							Volunteer Hub
						</a>
					</li>
					<li>
						<a href="/profile">
							<img src="{$app.user->profilePicture()}" height="21" width="21" class="img-circle" />
							{$app.user->username}
						</a>
					</li>
				</ul>
			</div>
		</nav>
		
		<div class="body">
			<div id="thebar"></div>
			<ul class="nav nav-tabs">
				<li class="{if isset($dashboardPage)&&$dashboardPage}active{/if}">
					<a href="{$org->manageUrl()}">
						<span class="glyphicon glyphicon-heart"></span>
						<span class="title">Pulse</span>
					</a>
				</li>
				<li class="{if isset($volunteersPage)&&$volunteersPage}active{/if}">
					<a href="{$org->manageUrl()}/volunteers{if $volunteersAwaitingApproval > 0}?approved=0{/if}">
						<span class="glyphicon glyphicon-user"></span>
						<span class="title">Volunteers</span>
						{if $volunteersAwaitingApproval > 0}
							<span class="badge alert-danger">
								{$volunteersAwaitingApproval}
							</span>
						{/if}
					</a>
				</li>
				<li class="{if isset($hoursPage)&&$hoursPage}active{/if}">
					<a href="{$org->manageUrl()}/hours{if $hoursAwaitingApproval > 0}?approved=0{/if}">
						<span class="glyphicon glyphicon-time"></span>
						<span class="title">Hours</span>
						{if $hoursAwaitingApproval > 0}
							<span class="badge alert-danger">
								{$hoursAwaitingApproval}
							</span>
						{/if}
					</a>
				</li>
				<li class="{if isset($placesPage)&&$placesPage}active{/if}">
					<a href="{$org->manageUrl()}/places{if $placesAwaitingApproval > 0}?approved=0{/if}">
						<span class="glyphicon glyphicon-map-marker"></span>
						<span class="title">Places</span>
						{if $placesAwaitingApproval > 0}
							<span class="badge alert-danger">
								{$placesAwaitingApproval}
							</span>
						{/if}
					</a>
				</li>
				<li class="{if isset($reportsPage)&&$reportsPage}active{/if}">
					<a href="{$org->manageUrl()}/reports">
						<span class="glyphicon glyphicon-file"></span>
						<span class="title">Reports</span>
					</a>
				</li>
			</ul>

			{block name=content}{/block}
		</div>

		<footer>
			<a href="https://github.com/inspirevive/inspirevive">Powered by InspireVive</a>
		</footer>		
	</div>
</body>
</html>
<link href='//fonts.googleapis.com/css?family=Titillium+Web:100,200,400,700' rel='stylesheet' type='text/css'>