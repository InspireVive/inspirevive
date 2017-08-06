{extends file='parent-minimal.tpl'}
{block name=htmlClass}lightbg{/block}
{block name=main}

<h1 class="logo">
	<a href="/">
		<img src="{$app.view_engine->asset_url('/img/logo.png')}" alt="InspireVive" class="img-responsive" />
	</a>
</h1>

<div class="body minimal container">
	<h4 class="title">Complete your InspireVive Volunteer Application</h4>

	{if $app.user->invited_by}
		<p>
			<strong>{$app.user->relation('invited_by')->name}</strong> has invited you to join InspireVive and would like for you to fill out your volunteer application.
		</p>
	{/if}

	{foreach from=$app.errors->messages() item=message}
		<div class="alert alert-danger">
			{$message}
		</div>
	{/foreach}

	{if $accept_error}
		<div class="alert alert-danger">
			You must agree to the terms listed at the bottom of this page before we can accept your application.
		</div>
	{/if}

	<form method="post" action="/volunteers/application" role="form" class="form-horizontal" id="charity-application">
		{$app.csrf->render($req) nofilter}
		<div class="form-group">
			<div class="col-md-4">
				<label>First Name</label>
				<input type="text" name="first_name" class="form-control" value="{if isset($application.first_name)}{$application.first_name}{/if}" />
			</div>
			<div class="col-md-4">
				<label><em>Middle Name (optional)</em></label>
				<input type="text" name="middle_name" class="form-control" value="{if isset($application.middle_name)}{$application.middle_name}{/if}" />
			</div>
			<div class="col-md-4">
				<label>Last Name</label>
				<input type="text" name="last_name" class="form-control" value="{if isset($application.last_name)}{$application.last_name}{/if}" />
			</div>
		</div>
		<hr/>

		<h4>Contact Information</h4>
		<br/>
		<div class="form-group">
			<div class="col-md-6">
				<label>Address</label>
				<input type="text" name="address" class="form-control" value="{if isset($application.address)}{$application.address}{/if}" />
			</div>
			<div class="col-md-2">
				<label>City</label>
				<input type="text" name="city" class="form-control" value="{if isset($application.city)}{$application.city}{/if}" />
			</div>
			<div class="col-md-2">
				<label>State</label>
				<select name="state" class="form-control">
				{foreach from=$states key=s item=state}
					<option value="{$s}" {if isset($application.state)&&$application.state==$s}selected="selected"{/if}>
						{$state}
					</option>
				{/foreach}
				</select>
			</div>
			<div class="col-md-2">
				<label>Zip Code</label>
				<input type="text" name="zip_code" class="form-control" value="{if isset($application.zip_code)}{$application.zip_code}{/if}" />
			</div>
		</div>

		<div class="form-group">
			<div class="col-md-4">
				<label>Phone Number</label>
				<input type="text" name="phone" class="form-control" value="{if isset($application.phone)}{$application.phone}{/if}" />
			</div>
			<div class="col-md-4">
			</div>
			<div class="col-md-4">
				<label><em>Alternate Phone Number (optional)</em></label>
				<input type="text" name="alternate_phone" class="form-control" value="{if isset($application.alternate_phone)}{$application.alternate_phone}{/if}" />
			</div>
		</div>

		<div class="form-group">
			<div class="col-md-12">
				<label>Date of Birth</label>
			</div>
		</div>

		<div class="form-group">
			<div class="col-md-2">
				<label>Month</label>
				<select name="month" class="form-control">
				{foreach from=$months key=m item=month}
					<option value="{$m}" {if isset($application.month)&&$application.month==$m}selected="selected"{/if}>
						{$month}
					</option>
				{/foreach}
				</select>
			</div>
			<div class="col-md-2">
				<label>Day</label>
				<select name="day" class="form-control">
				{foreach from=$days item=day}
					<option value="{$day}" {if isset($application.day)&&$application.day==$day}selected="selected"{/if}>
						{$day}
					</option>
				{/foreach}
				</select>
			</div>
			<div class="col-md-2">
				<label>Year</label>
				<select name="year" class="form-control">
				{foreach from=$years item=year}
					<option value="{$year}" {if isset($application.year)&&$application.year==$year}selected="selected"{/if}>
						{$year}
					</option>
				{/foreach}
				</select>
			</div>
		</div>
		<br/>

		<div class="form-group">
			<div class="col-md-4">
				<p>
					<button type="submit" class="btn btn-primary btn-lg btn-block">Save</button>
				</p>
			</div>
			<div class="col-md-8">
				<p>
					<label>
						<input type="checkbox" name="accept" />
						I assert that I accurately completed this form and pledge to uphold InspireVive's Code of Conduct as a volunteer
					</label>
				</p>
			</div>
		</div>
	</form>
</div>

{/block}