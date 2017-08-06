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
			<div class="alert alert-danger">
				{$error}
			</div>
		{/foreach}

		{if $success}
			<div class="alert alert-success">Thank you for updating your account.</div>
		{/if}

		<div class="tab-content">
			<div class="tab-pane fade {if $section=='profile'||!$section}in active{/if}" id="profile">
				<form action="/account" method="post" class="form-horizontal" role="form">
					<input type="hidden" name="profile" value="settings" />
					<h3>Edit Your Profile</h3>
					<br/>

					<div class="form-group">
						<label class="control-label col-md-3">Bio</label>
						<div class="col-md-7">
							<textarea name="about" class="form-control">{$app.user->about}</textarea>
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
					<input type="hidden" name="section" value="settings" />
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
						<label class="control-label col-md-3">Email Address</label>
						<div class="col-md-5">
							<input type="email" name="email" class="form-control" />
						</div>
						<div class="col-md-4 form-control-static">
							<strong>Current: </strong> {$app.user->email}
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-md-3">New Password</label>
						<div class="col-md-5">
							<input type="password" name="password[]" class="form-control" />
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-md-3">Confirm New Password</label>
						<div class="col-md-5">
							<input type="password" name="password[]" class="form-control" />
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