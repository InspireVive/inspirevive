{extends file="parent.tpl"}
{block name=content}

<h1 class="profile-title">
	{$org.name}
	<small>Thank you for your interest</small>
</h1>

<p>
	We have notified <strong>{$org.name}</strong> that you are interested in joining as a volunteer.
	Your contact information will be shared with them so they may get in touch with you if necessary. Once your application has been approved you will receive a confirmation e-mail from InspireVive.
</p>

<p>
	<a href="{$orgObj->url()}" class="btn btn-primary">
		Go to Volunteer Hub
	</a>
</p>

<br/>
<h4>What if it has been several days/weeks and I never heard back from {$org.name}?</h4>
<p>
	Sometimes e-mails get lost or people are busy.
	If this happens we recommend trying to contact the volunteer coordinator directly.
</p>

<p>
	You can reach the volunteer coordinator for {$org.name} at:<br/>
	<a href="mailto:{$org.volunteer_coordinator_email}">{$org.volunteer_coordinator_email}</a>
</p>

{/block}