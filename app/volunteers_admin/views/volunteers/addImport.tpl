{extends file="$viewsDir//parent.tpl"}
{block name=content}

<div class="top-nav">
	<div class="row">
		<div class="col-sm-3">
			<a href="{$org->manageUrl()}/volunteers/add" class="btn btn-link btn-block">
				&larr; Cancel
			</a>
		</div>
	</div>
</div>

<h4>Import Volunteers via CSV</h4>
<p>
	Volunteers can be imported in bulk from a .CSV (comma-separated values) file. The first line denotes the name of the field so columns may be in any order.
</p>

<p>
	The <strong>e-mail</strong> column is the only column required and it can be an e-mail address or username of existing user.
	Any columns in additonal to <strong>email</strong> will be added as meta-data to the volunteer's profile.
</p>

<p>
	<a href="{$app.view_engine->asset_url('/volunteer-import-template.csv')}" class="btn btn-link">
		<span class="glyphicon glyphicon-download"></span> Download Import Template
	</a>
</p>

{if $numAdded > 0}
	<p class="alert alert-success">
		<strong>{$numAdded} {$app.locale->p($numAdded, 'volunteer</strong> was', 'volunteers</strong> were')} added.
	</p>
{/if}

{foreach from=$app.errors->messages() item=error}
	<div class="alert alert-danger">{$error}</div>
{/foreach}

<form class="form-horizontal" method="post" action="{$org->manageUrl()}/volunteers" enctype="multipart/form-data">
	<div class="form-group">
		<label class="control-label col-md-2">CSV File to Import</label>
		<div class="col-md-3 form-control-static">
			<input type="file" name="import" />
		</div>
	</div>
	<div class="form-group">
		<div class="col-md-4 col-md-offset-2">
			<button type="submit" class="btn btn-primary btn-lg">
				Import
			</button>
		</div>
	</div>
</form>

{/block}
