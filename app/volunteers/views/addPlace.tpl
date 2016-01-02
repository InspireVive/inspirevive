{extends file="parent.tpl"}
{block name=content}

<h1 class="profile-title">
	{$org->name}
	<small>
		Add Volunteer Place
	</small>
</h1>

<p class="lead">
	What is the volunteer place you like to add and who is the volunteer coordinator?
</p>
<p>
	<em>All fields are required.</em>
</p>

{foreach from=$app.errors->messages() item=message}
	<div class="alert alert-danger">
		{$message}
	</div>
{/foreach}

<form action="{$org->url()}/places/add" method="post" role="form" class="form-horizontal">
	<div class="form-group">
		<label class="control-label col-md-4">
			Location Name
		</label>
		<div class="col-md-3">
			<input type="text" name="name" class="form-control" value="{if isset($place.name)}{$place.name}{/if}">
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-md-4">
			Address
		</label>
		<div class="col-md-3">
			<textarea name="address" class="form-control">{if isset($place.address)}{$place.address}{/if}</textarea>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-md-4">
			Volunteer Coordinator's Name
		</label>
		<div class="col-md-3">
			<input type="text" name="verify_name" class="form-control" value="{if isset($place.verify_name)}{$place.verify_name}{/if}">
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-md-4">
			Volunteer Coordinator's E-mail Address
		</label>
		<div class="col-md-4">
			<input type="text" name="verify_email" class="form-control" value="{if isset($place.verify_email)}{$place.verify_email}{/if}">
		</div>
	</div>
	<div class="form-group">
		<div class="col-md-2 col-md-offset-4">
			<button type="submit" class="btn btn-success">Add</button>
		</div>
	</div>
</form>

{/block}