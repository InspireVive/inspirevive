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

	{if $error}
		<div class="alert alert-danger">
            {$error}
		</div>
	{/if}

	<form action="/login" method="post">
		<input type="hidden" name="redir" value="{$redir|htmlspecialchars}" />
		<div class="form-group">
			<label class="placeholder">Email address</label>
			<input type="email" name="username" id="login_username" value="{$loginUsername}" class="form-control input-lg" placeholder="Email address" autofocus />
		</div>
		<div class="form-group">
			<label class="placeholder">Password</label>
			<input type="password" name="password" class="form-control input-lg" placeholder="Password" />
		</div>
		<div class="form-group">
			<button class="btn btn-block btn-success btn-lg">Sign In</button>
		</div>
	</form>

	<p>
		<a href="/forgot">Forgot password?</a>
	</p>
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