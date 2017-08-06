{extends file='parent-minimal.tpl'}
{block name=htmlClass}lightbg smallform{/block}
{block name=main}

<h1 class="logo">
	<a href="/">
		<img src="{$app.view_engine->asset_url('/img/logo.png')}" alt="InspireVive" class="img-responsive" />
	</a>
</h1>

<div class="body skinny minimal container">
	<h4 class="title">Thank you and welcome to InspireVive!</h4>

	<p>
		Your volunteer application has been received. Congratulations on taking your first step towards volunteering!
	</p>

	<h4>What's next?</h4>

	<p>
		<a href="/profile" class="btn btn-default btn-block btn-lg">Go to your profile</a>
	</p>
</div>

{/block}