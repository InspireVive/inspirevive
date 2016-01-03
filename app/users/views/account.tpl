{extends file="parent.tpl"}
{block name=content}
<br/>
<div class="row">
	<div class="col-sm-3">
		<ul class="nav nav-pills nav-stacked" role="tablist">
			<li class="{if $section=='profile'||!$section}active{/if}">
				<a href="#profile" role="tab" data-toggle="tab">Your Profile</a>
			</li>
			<li class="{if $section=='settings'}active{/if}">
				<a href="#account" role="tab" data-toggle="tab">Account Settings</a>
			</li>
		</ul>
		<br/>
	</div>
	<div class="col-sm-9">
		{foreach from=$app.errors->messages() item=error}
			<div class="alert alert-error">
				{$error}
			</div>
		{/foreach}

		{if $success}
			<div class="alert alert-success">Thank you for updating your account.</div>
		{/if}

		<div class="tab-content">
			<div class="tab-pane fade {if $section=='profile'||!$section}in active{/if}" id="profile">
				<h3>Social Media Integrations</h3>
				<br/>

				<div class="btn-toolbar">
					{if $facebookConnected}
						<form action="/facebook/disconnect?r=/account" method="post" class="inline">
							<button class="btn btn-success" type="submit">
								<span class="ion-social-facebook"></span>
								Connected
							</button>
						</form>
					{else}
						<a href="/facebook/connect" class="btn btn-default">
							<span class="ion-social-facebook"></span>
							Connect
						</a>
					{/if}
					{if $twitterConnected}
						<form action="/twitter/disconnect?r=/account" method="post" class="inline">
							<button class="btn btn-success" type="submit">
								<span class="ion-social-twitter"></span>
								Connected
							</button>
						</form>
					{else}
						<a href="/twitter/connect" class="btn btn-default">
							<span class="ion-social-twitter"></span>
							Connect
						</a>
					{/if}
					{if $instagramConnected}
						<form action="/instagram/disconnect?r=/account" method="post" class="inline">
							<button class="btn btn-success" type="submit">
								<span class="ion-social-instagram"></span>
								Connected
							</button>
						</form>
					{else}
						<a href="/instagram/connect" class="btn btn-default">
							<span class="ion-social-instagram"></span>
							Connect
						</a>
					{/if}
				</div>
				<br/>

				<form action="/account" method="post" class="form-horizontal" role="form">
					<h3>Edit Your Profile</h3>
					<br/>

					<div class="form-group">
						<label class="control-label col-md-3">Bio</label>
						<div class="col-md-7">
							<textarea name="about" class="form-control">{$app.user->about}</textarea>
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-md-3">Profile Picture</label>
						<div class="col-md-9">
							<div class="row">
							{foreach from=['Facebook','Twitter','Instagram','Gravatar'] item=source}
								{assign var=picture value=$app.user->profilePicture(80, strtolower($source))}
								{if $picture}
									<div class="col-sm-3">
										<p class="text-center">
											<img src="{$picture}" class="img-circle" width="80" height="80" />
											<br/>
											<input type="radio" name="profile_picture_preference" value="{$source|strtolower}" id="preference_{$source}" {if $app.user->profile_picture_preference==strtolower($source)}checked="checked"{/if} />
											<label for="preference_{$source}">{$source}</label>
										</p>
									</div>
								{/if}
							{/foreach}
							</div>
						</div>
					</div>
					
					<div class="form-group">
						<div class="col-md-10 col-md-offset-3">
							<input type="submit" value="Update" class="btn btn-primary btn-lg" />
						</div>
					</div>
				</form>
			</div>

			<div class="tab-pane fade {if $section=='settings'}in active{/if}" id="account">
				<form action="/account" method="post" class="form-horizontal" rol="form">
					<h3>Edit Your Account</h3>
					<br/>

					<div class="form-group">
						<label class="control-label col-md-3">Username</label>
						<div class="col-md-5 form-control-static">
							{$app.user->username}
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-md-3">Current Password</label>
						<div class="col-md-5">
							<input type="password" name="current_password" class="form-control" />
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-md-3">E-mail Address</label>
						<div class="col-md-5">
							<input type="text" name="user_email" class="form-control" />
						</div>
						<div class="col-md-4 form-control-static">
							<strong>Current: </strong> {$app.user->user_email}
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-md-3">New Password</label>
						<div class="col-md-5">
							<input type="password" name="user_password[]" class="form-control" />
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-md-3">Confirm New Password</label>
						<div class="col-md-5">
							<input type="password" name="user_password[]" class="form-control" />
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-9 col-md-offset-3">
							<input type="submit" value="Update" class="btn btn-primary btn-lg" />
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
{/block}