{extends file="parent.tpl"}
{block name=header}
<script type="text/javascript">
	mixpanel.track("Approve Hours - Thanks", {
		organization: '{$org->id()}'
	});
</script>
{/block}
{block name=content}

<h1 class="profile-title">
	{$org->name()}
	<small>
		Verify Volunteer Hours
	</small>
</h1>

{if $success}
	<h4>Thank you!</h4>
	<p>
		The <strong>{$hour.hours} {$app.locale->p($hour.hours,'volunteer hour','volunteer hours')}</strong> for <strong>{$user->name(true)}</strong> on <strong>{$hour.timestamp|date_format:'l, F j, Y'}</strong> at <strong>{$place.name}</strong> have been {if $approved}approved{else}denied{/if}.
	</p>
{else}
	{foreach from=$app.errors->messages() item=error}
		<p class="alert alert-danger">
			{$error}
		</p>
	{/foreach}
{/if}

<hr/>
<p>
	{$org->name()} thanks you for your participation.
</p>

{/block}