{extends file="$viewsDir//parent.tpl"}
{block name=content}

<div class="top-nav">
	<div class="row">
		<div class="col-md-8">
			<h3>
				Hour Details
			</h3>
		</div>
		<div class="col-md-4">
			<form method="post" action="{$org->url()}/admin/hours/{$hour.id}">
				<input type="hidden" name="method" value="DELETE" />
				<button type="submit" class="btn btn-danger pull-right">
					Delete Hour Entry
				</button>
			</form>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-2 text-right">
		<p class="text-right">
			<strong>Volunteer</strong>
		</p>
	</div>
	<div class="col-md-6">
		<p>
			<a href="{$org->url()}/admin/volunteers/{$volunteer->id()}">
				{$volunteer->name(true)}
			</a>
		</p>
	</div>
</div>

<div class="row">
	<div class="col-md-2 text-right">
		<p class="text-right">
			<strong>Date</strong>
		</p>
	</div>
	<div class="col-md-6">
		<p>
			{$hour.timestamp|date_format:'F j, Y'}
		</p>
	</div>
</div>

<div class="row">
	<div class="col-md-2 text-right">
		<p class="text-right">
			<strong>Place</strong>
		</p>
	</div>
	<div class="col-md-6">
		<p>
			<a href="{$org->url()}/admin/places/{$place->id()}">
				{$place->name}
			</a>
		</p>
	</div>
</div>

<div class="row">
	<div class="col-md-2 text-right">
		<p class="text-right">
			<strong>Hours</strong>
		</p>
	</div>
	<div class="col-md-6">
		<p>
			{$hour.hours}
		</p>
	</div>
</div>

<div class="row">
	<div class="col-md-2 text-right">
		<p class="text-right">
			<strong>Approved?</strong>
		</p>
	</div>
	<div class="col-md-6">
		<p>
			{if $hour.approved}
				<label class="label label-success">Approved</label>
			{else}
				{if $hour.verification_requested}
					<label class="label label-primary">Verification Requested from {$place->name}</label>
				{else}
					<label class="label label-danger">Not Approved</label>
				{/if}
			{/if}
		</p>
	</div>
</div>

<div class="row">
	<div class="col-md-2 text-right">
		<p class="text-right">
			<strong>Tags</strong>
		</p>
	</div>
	<div class="col-md-6">
		<p>
			{foreach from=$tags item=tag}
				<span class="label label-default">{$tag}</span>
			{/foreach}
		</p>
	</div>
</div>

{if !$hour.approved}
<div class="row">
	<div class="col-md-2 text-right">
		<p class="text-right">
			<strong>Approve</strong>
		</p>
	</div>
	<div class="col-md-6">
		<div class="btn-group-form btn-group">
			<form method="post" action="{$org->url()}/admin/hours/{$hour.id}" class="inline">
				<input type="hidden" name="approved" value="1" />
				<button type="submit" class="btn btn-success">
					Approve
				</button>
			</form>
			<form method="post" action="{$org->url()}/admin/hours/{$hour.id}" class="inline">
				<input type="hidden" name="method" value="DELETE" />
				<button type="submit" class="btn btn-danger pull-right">
					Deny
				</button>
			</form>
		</div>
	</div>
</div>
{/if}

{/block}