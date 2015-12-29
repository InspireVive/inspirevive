{extends file="emails/parent.tpl"}
{block name=content}
<p>Hey {$username}!</p>

<p>We are writing to inform you that new volunteer hours have been added to your InspireVive profile and <strong>{$orgname}</strong> has confirmed your recent volunteer activity. Way to go!</p>

<p>
Place: {$place_name}<br>
Day: {$day}<br>
Number of Hours: {$hours} {$app.locale->p($hours,'hour','hours')}
</p>

<p>- InspireVive</p>
{/block}