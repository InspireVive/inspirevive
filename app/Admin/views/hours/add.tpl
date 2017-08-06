{extends file="$viewsDir//parent.tpl"}
{block name=content}

<h4>Add Volunteer Hours <small>step 1 of 3</small></h4>

<p class="lead">
	Which place did the volunteer hours happen?
</p>
<br/>

<div class="well clearfix">
	<form action="{$org->manageUrl()}/hours/add/2" method="get" role="form">
		<div class="form-group">
			<h4>Select Place</h4>
			{if count($places) == 0}
				<div class="alert alert-danger">
					First you must create a place. <a href="{$org->manageUrl()}/places/add">Add one</a>
				</div>
			{else}
				<div class="col-md-4">
					<select name="place" class="form-control">
					{foreach from=$places item=place}
						<option value="{$place->id()}">{$place->name}</option>
					{/foreach}
					</select>
				</div>
				<div class="col-md-2">
					<button type="submit" class="btn btn-success">Go</button>
				</div>
			{/if}
		</div>
	</form>
</div>

{/block}