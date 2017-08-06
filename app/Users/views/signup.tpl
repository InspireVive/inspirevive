{extends file='parent-minimal.tpl'}
{block name=htmlClass}lightbg smallform{/block}
{block name=main}

<h1 class="logo">
	<a href="/">
		<img src="{$app.view_engine->asset_url('/img/logo.png')}" alt="InspireVive" class="img-responsive" />
	</a>
</h1>

<div class="body skinny minimal container">
	<h4 class="title">Create your InspireVive account</h4>

	{foreach from=$app.errors->messages() item=message}
		<div class="alert alert-danger">
			{$message}
		</div>
	{/foreach}

	<form action="/signup" method="post" role="form">
		<input type="hidden" name="redir" value="{$redir|htmlspecialchars}" />
		<div class="form-group">
			<label class="placeholder">Username (<em>letters and numbers</em> only)</label>
			<input type="text" name="username" id="register_username" value="{$signupUsername}" class="form-control input-lg" placeholder="Username (letters + numbers)" autofocus />
		</div>
		<div class="form-group">
			<label class="placeholder">Email</label>
			<input type="email" name="email" value="{$signupEmail}" class="form-control input-lg" placeholder="Email address" />
		</div>
		<div class="form-group">
			<label class="placeholder">Password (at least <em>8 characters</em>)</label>
			<input type="password" name="password[]" class="form-control input-lg" placeholder="Password (min. 8 characters)" />
		</div>
		<div class="form-group">
			<label class="placeholder">Confirm Password</label>
			<input type="password" name="password[]" class="form-control input-lg" placeholder="Confirm Password" />
		</div>
		<div class="form-group">
			<button class="btn btn-block btn-success btn-lg">Join InspireVive</button>
		</div>
	</form>
</div>

<div class="body skinny minimal container secondary">
	Already have an account? <a href="/login">Sign In</a>
</div>

<script type="text/javascript">
$(function() {
	$('#register_username').focus();
});
</script>

{/block}