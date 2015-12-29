{extends file="parent-minimal.tpl"}
{block name=htmlClass}lightbg smallform{/block}
{block name=header}
<script type="text/javascript">
	mixpanel.track("Finish Signup Page");
</script>
{/block}
{block name=main}

<h1 class="logo">
	<a href="/">
		<img src="{$app.view_engine->asset_url('/img/logo.png')}" alt="InspireVive" class="img-responsive" />
	</a>
</h1>

<div class="body skinny minimal container">
	<h4 class="title">Finish Joining InspireVive</h4>

	<p class="text-center">
		<a href="{$profileUrl}" target="_blank">
			<img src="{$profilePic}" alt="{$username}" class="img-circle" /><br/>
			<strong>{$username}</strong>
		</a>
	</p>

	{foreach from=$app.errors->messages() item=message}
		<div class="alert alert-danger">
			{$message}
		</div>
	{/foreach}
		
	<form action="/signup/finish" method="post">
		<div class="form-group">
			<label class="placeholder">Username (<em>letters and numbers</em> only)</label>
			<input type="text" class="form-control input-lg" name="username" value="{$username_post|default:$username}" placeholder="Username (letters + numbers)" />
		</div>
		<div class="form-group">
			<label class="placeholder">Email Address</label>
			<input type="text" class="form-control input-lg" name="user_email" value="{$userEmail}" placeholder="E-mail Address" />
		</div>
		<div class="form-group">
			<label class="placeholder">Password (at least <em>8 characters</em>)</label>
			<input type="password" class="form-control input-lg" name="user_password[]" placeholder="Password (min. 8 chars.)" />
		</div>
		<div class="form-group">
			<label class="placeholder">Confirm Password</label>
			<input type="password" class="form-control input-lg" name="user_password[]" placeholder="Confirm Password" />
		</div>
		<div class="form-group">
			<input type="submit" value="Finish" class="btn btn-lg btn-block btn-primary" />
		</div>
	</form>
</div>

{/block}