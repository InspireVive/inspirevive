{extends file='parent-minimal.tpl'}
{block name=htmlClass}lightbg smallform{/block}
{block name=main}

<h1 class="logo">
	<a href="/">
		<img src="{$app.view_engine->asset_url('/img/logo.png')}" alt="InspireVive" class="img-responsive" />
	</a>
</h1>

<div class="body skinny minimal container">
	{if $success}
		<h4 class="title">Thank you</h4>

		<div class="alert alert-success">
			Thank you for verifying your email!
		</div>
	{else}
		<h4 class="title">Uh oh!</h4>

		<div class="alert alert-danger">
			We were unable to verify your email address. Have you already done this?
		</div>
	{/if}

	<p>
		<a href="/profile" class="btn btn-block btn-primary btn-lg">Return to InspireVive</a>
	</p>
</div>

{/block}