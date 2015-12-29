{extends file="parent.tpl"}
{block name=header}
<script type="text/javascript">
	mixpanel.track("Approve Volunteer - Thanks", {
		organization: '{$org->id()}'
	});
</script>
{/block}
{block name=content}

<h1 class="profile-title">
	{$org->name()}
	<small>
		Approve Volunteer
	</small>
</h1>

{if $success}
	<h4>Thank you!</h4>
	<p>
		We have {if $approved}approved{else}denied{/if} <strong>{$name}'s</strong> request to join {$org->name()} and notified them over e-mail of your decision.
	</p>

	{if $approved}
		<h4>What's next?</h4>
		<p>
			We recommend getting in touch with the volunteer and giving them a warm welcome. Also explain what the next step to volunteering at your organization is, whether that is additional documentation or finding events in the Volunteer Hub.
		</p>
	{/if}

	<p>
		{if $approved}
			<a href="{$org->manageUrl()}/volunteers/{$user.uid}" class="btn btn-primary">
				View {$name}'s' Volunteer Application
			</a>
		{else}
			<a href="{$org->manageUrl()}" class="btn btn-primary">
				Go to {$org->name()} Management
			</a>
		{/if}
	</p>
{else}
	{foreach from=$app.errors->messages() item=error}
		<p class="alert alert-danger">
			{$error}
		</p>
	{/foreach}
{/if}
{/block}