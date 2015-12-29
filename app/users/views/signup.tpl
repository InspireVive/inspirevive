{extends file='parent-minimal.tpl'}
{block name=htmlClass}lightbg smallform{/block}
{block name=header}
<script type="text/javascript">
	mixpanel.track("Signup Page");
</script>
{/block}
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
			<input type="text" name="username" id="register_username" value="{$signupUsername}" class="form-control input-lg" placeholder="Username (letters + numbers)" />
		</div>
		<div class="form-group">
			<label class="placeholder">Email</label>
			<input type="text" name="user_email" value="{$signupEmail}" class="form-control input-lg" placeholder="E-mail address" />
		</div>
		<div class="form-group">
			<label class="placeholder">Password (at least <em>8 characters</em>)</label>
			<input type="password" name="user_password[]" class="form-control input-lg" placeholder="Password (min. 8 characters)" />
		</div>
		<div class="form-group">
			<label class="placeholder">Confirm Password</label>
			<input type="password" name="user_password[]" class="form-control input-lg" placeholder="Confirm Password" />
		</div>
		<div class="form-group">
			<button class="btn btn-block btn-success btn-lg">Join InspireVive</button>
		</div>
	</form>

	<div class="actions bottom">
		<label>
			Sign up with:
		</label>
		<a class="btn-action" title="Sign In with Facebook" href="/facebook/connect">
			<span class="ion-social-facebook"></span>
		</a>
		<a class="btn-action" title="Sign In with Twitter" href="/twitter/connect">
			<span class="ion-social-twitter"></span>
		</a>
		<a class="btn-action" title="Sign In with Instagram" href="/instagram/connect">
			<span class="ion-social-instagram"></span>
		</a>
	</div>
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