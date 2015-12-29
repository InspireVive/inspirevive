{extends file="parent-minimal.tpl"}
{block name=main}

<h1 class="logo">
	<a href="/">
		<img src="{$app.view_engine->asset_url('/img/logo.png')}" alt="InspireVive" class="img-responsive" />
	</a>
</h1>

<div class="body skinny minimal container">
	<h4 class="title">You are about to switch to another InspireVive account</h4>

	<div class="row">
		<div class="col-sm-5">
			<img class="img-circle" src="{$app.user->profilePicture(40)}" height="40" width="40" />
			<br/>
			{$app.user->name()}
		</div>
		<div class="col-sm-2">
			<span class="glyphicon glyphicon-arrow-right" style="margin: 10px 0px 0px;"></span>
		</div>
		<div class="col-sm-5">
			<img class="img-circle" src="{$otherProfile->profilePicture(40)}" height="40" width="40" />
			<br/>
			{$otherProfile->username}
		</div>
	</div>

	<p>
		You are currently logged in as <strong>{$app.user->name()}</strong>. The Twitter account
		you are trying to login with is connected to another InspireVive account, <strong>{$otherProfile->username}</strong>.
	</p>
	<p>
		Do you want to be signed in as <strong>{$otherProfile->username}</strong>?
	</p>

	<p>
		<a href="/twitter/connect?forceLogin=t" class="btn btn-lg btn-block btn-primary">
			Yes, switch accounts
		</a>
	</p>

	<p>
		<a href="/twitter/connect" class="btn btn-default btn-block">
			No, use another Twitter account
		</a>
	</p>
</div>

{/block}