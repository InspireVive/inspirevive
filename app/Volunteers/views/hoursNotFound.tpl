{extends file="parent.tpl"}
{block name=content}

<h1 class="profile-title">
	{$org->name}
	<small>
		Verify Volunteer Hours
	</small>
</h1>

<h4>Not Found</h4>

<p class="alert alert-info">
	Sorry, we could not locate the hour entry you were trying to verify. If you landed here from an InspireVive email then the hour entry has probably been verified already.
</p>

<hr/>
<p>
	{$org->name} thanks you for your participation.
</p>

{/block}