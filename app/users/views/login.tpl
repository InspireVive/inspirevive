{extends file='parent-minimal.tpl'}
{block name=htmlClass}lightbg smallform{/block}
{block name=main}

<h1 class="logo">
	<a href="/">
		<img src="{$app.view_engine->asset_url('/img/logo.png')}" alt="InspireVive" class="img-responsive" />
	</a>
</h1>

<div class="body skinny minimal container">
	<h4 class="title">Welcome back!</h4>

	{foreach from=$app.errors->errors() item=error}
		<div class="alert alert-danger">
			{if $error.error == 'user_login_no_match'}
				We do not have a match for that email address and password.<br/>
				<a href="/forgot">Did you forget your password?</a>
			{elseif $error.error == 'user_login_unverified'}
				You must verify your email address before you can log in.<br/>
				<a href="/users/verify/{$error.params.uid}">Resend verification email</a>
			{else}
				{$error.message}
			{/if}
		</div>
	{/foreach}

	<form action="/login" method="post">
		<input type="hidden" name="redir" value="{$redir|htmlspecialchars}" />
		<div class="form-group">
			<label class="placeholder">Email address</label>
			<input type="text" name="user_email" id="login_username" value="{$loginUsername}" class="form-control input-lg" placeholder="Email address" />
		</div>
		<div class="form-group">
			<label class="placeholder">Password</label>
			<input type="password" name="password" class="form-control input-lg" placeholder="Password" />
		</div>
		<div class="form-group">
			<button class="btn btn-block btn-success btn-lg">Sign In</button>
		</div>
	</form>
</div>

<div class="body skinny minimal container secondary">
	Don't have an account? <a href="/signup">Sign Up</a>
</div>

<script type="text/javascript">
$(function() {
	$('#login_username').focus();
});
</script>

{/block}