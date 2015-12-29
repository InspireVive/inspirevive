{extends file="emails/parent.tpl"}
{block name=content}
<p>Hey {$username}!</p>

<p>We are writing to inform you that the volunteer hours you reported for <strong>{$orgname}</strong> have been approved on InspireVive. Way to go!</p>

<p>
Place: {$place_name}<br>
Day: {$day}<br>
Number of Hours: {$hours} {$app.locale->p($hours,'hour','hours')}
</p>

<p>- InspireVive</p>
{/block}