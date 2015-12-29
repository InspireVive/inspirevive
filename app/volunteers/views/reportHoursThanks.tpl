{extends file="parent.tpl"}
{block name=content}

<h1 class="profile-title">
	{$org->name()}
	<small>
		Report Volunteer Hours
	</small>
</h1>

<h4>Thank you!</h4>
<p>
	Your volunteer hours have been reported. We will e-mail you once they have been approved by a volunteer coordinator.
</p>
<br/>
<p>
	<a href="{$org->url()}" class="btn btn-primary">
		Volunteer Hub
	</a>
	<a href="/profile" class="btn btn-default">
		Your Profile
	</a>
</p>

{/block}