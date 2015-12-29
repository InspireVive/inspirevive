{extends file="parent.tpl"}
{block name=header}
<script type="text/javascript">
	mixpanel.track("Report Hours - 1", {
		organization: '{$org->id()}'
	});
</script>
{/block}
{block name=content}

<h1 class="profile-title">
	{$org->name()}
	<small>
		Report Volunteer Hours
	</small>
</h1>

<p class="lead">
	Where did you perform the volunteer hours?
</p>
<br/>

{if count($places) > 0}
	<div class="well clearfix">
		<form action="{$org->url()}/hours/report/2" method="get" role="form">
			<div class="form-group">
				<h4>Volunteer Place</h4>
				{if count($places) == 0}
					<p class="lead">
						No places have been created yet.
					</p>
				{else}
					<div class="col-md-4">
						<select name="place" class="form-control">
						{foreach from=$places item=place}
							<option value="{$place->id()}">{$place->name}</option>
						{/foreach}
							<option value="-1">+ Add a new volunteer place</option>
						</select>
					</div>
					<div class="col-md-2">
						<button type="submit" class="btn btn-success">Go</button>
					</div>
				{/if}
			</div>
		</form>
	</div>

	<div class="or">
		<div class="line"></div>
		<div class="word">or</div>
	</div>
{/if}

<div class="well clearfix">
	<h4>Add a new volunteer place</h4>
	<p class="lead">
		This form allows you to report hours that happened at a volunteer place not listed above.
	</p>
	<p>
		<em>All fields are required.</em>
	</p>

	<form action="{$org->url()}/places/add" method="post" role="form" class="form-horizontal">
		<div class="form-group">
			<label class="control-label col-md-4">
				Location Name
			</label>
			<div class="col-md-4">
				<input type="text" name="name" class="form-control" />
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-md-4">
				Address
			</label>
			<div class="col-md-4">
				<textarea name="address" class="form-control"></textarea>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-md-4">
				Volunteer Coordinator's Name
			</label>
			<div class="col-md-4">
				<input type="text" name="verify_name" class="form-control" />
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-md-4">
				Volunteer Coordinator's E-mail Address
			</label>
			<div class="col-md-4">
				<input type="text" name="verify_email" class="form-control" />
			</div>
		</div>
		<div class="form-group">
			<div class="col-md-2 col-md-offset-4">
				<button type="submit" class="btn btn-success">Add</button>
			</div>
		</div>
	</form>
</div>

{/block}