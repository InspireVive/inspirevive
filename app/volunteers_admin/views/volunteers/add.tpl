{extends file="$viewsDir//parent.tpl"}
{block name=content}

{foreach from=$app.errors->messages() item=error}
	<p class="alert alert-danger">{$error}</p>
{/foreach}

{if $numAdded > 0}
	<p class="alert alert-success">
		<strong>{$numAdded} {$app.locale->p($numAdded, 'volunteer</strong> was', 'volunteers</strong> were')} added.
	</p>
{/if}

<h4>Add Volunteers</h4>
<div class="row">
	<div class="col-md-8">
		<p>
			In order to add volunteers to your organization on InspireVive just enter the email address or InspireVive username of one or more volunteers. If a volunteer has not joined InspireVive yet, we will send them a personalized invitation email and request they fill out the InspireVive volunteer application.
		</p>

		<form role="form" method="post" action="{$org->manageUrl()}/volunteers">
			<div class="form-group">
				<label class="control-label">Email addresses and/or usernames (max. 1 per line)</label>
				<textarea class="form-control" name="emails" placeholder="One email or username per line..." rows="8">{$emails}</textarea>
			</div>
			<div class="form-group">
				<input type="submit" class="btn btn-primary" value="Add Volunteers" />
				&nbsp;&nbsp;&nbsp;or&nbsp;&nbsp;&nbsp;
				<a href="{$org->manageUrl()}/volunteers/add/import" class="btn btn-link">Import via CSV</a>
			</div>
		</form>
	</div>
</div>

{/block}