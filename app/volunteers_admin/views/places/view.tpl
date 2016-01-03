{extends file="$viewsDir//parent.tpl"}
{block name=content}

<div class="top-nav">
	<div class="row">
		<div class="col-md-8">
			<h3>
				{$place.name}
			</h3>
		</div>
		<div class="col-md-4 text-right">
			<a href="{$org->url()}/admin/places/{$place.id}/edit" class="btn btn-default">
				Edit Place
			</a>
			<form method="post" action="{$org->url()}/admin/places/{$place.id}" class="inline">
				<input type="hidden" name="method" value="DELETE" />
				<button type="submit" class="btn btn-danger">
					Delete Place
				</button>
			</form>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-3 text-right">
		<p class="text-right">
			<strong>Name</strong>
		</p>
	</div>
	<div class="col-md-6">
		<p>
			{$place.name}
		</p>
	</div>
</div>

<div class="row">
	<div class="col-md-3 text-right">
		<p class="text-right">
			<strong>Type</strong>
		</p>
	</div>
	<div class="col-md-6">
		<p>
			{if $place.place_type == $smarty.const.VOLUNTEER_PLACE_EXTERNAL}
				External
			{else}
				Internal
			{/if}
		</p>
	</div>
</div>

<div class="row">
	<div class="col-md-3 text-right">
		<p class="text-right">
			<strong>Address</strong>
		</p>
	</div>
	<div class="col-md-6">
		<p>
			{$place.address}
		</p>
	</div>
</div>

{if $place.place_type == $smarty.const.VOLUNTEER_PLACE_EXTERNAL}
	<div class="row">
		<div class="col-md-3 text-right">
			<p class="text-right">
				<strong>Volunteer Coordinator Name</strong>
			</p>
		</div>
		<div class="col-md-6">
			<p>
				{$place.verify_name}
			</p>
		</div>
	</div>

	<div class="row">
		<div class="col-md-3 text-right">
			<p class="text-right">
				<strong>Volunteer Coordinator E-mail</strong>
			</p>
		</div>
		<div class="col-md-6">
			<p>
				<a href="mailto:{$place.verify_email}">
					{$place.verify_email}
				</a>
			</p>
		</div>
	</div>

	<div class="row">
		<div class="col-md-3 text-right">
			<p class="text-right">
				<strong>Approved?</strong>
			</p>
		</div>
		<div class="col-md-6">
			{if $place.verify_approved}
				<p>
					<label class="label label-success">Approved</label>
				</p>
			{else}
				<div class="btn-group-form btn-group">
					<form method="post" action="{$org->url()}/admin/places/{$place.id}" class="inline">
						<input type="hidden" name="verify_approved" value="1" />
						<button type="submit" class="btn btn-success">
							Approve
						</button>
					</form>
					<form method="post" action="{$org->url()}/admin/places/{$place.id}" class="inline">
						<input type="hidden" name="method" value="DELETE" />
						<button type="submit" class="btn btn-danger">
							Deny
						</button>
					</form>
				</div>
			{/if}
		</div>
	</div>
{/if}

{/block}