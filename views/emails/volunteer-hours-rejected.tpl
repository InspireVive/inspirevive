{extends file="emails/parent.tpl"}
{block name=content}
<p>Dear {$username},</p>

<p>We are writing to inform you that the volunteer hours you reported for <strong>{$orgname}</strong> were <strong>not approved</strong> on InspireVive.</p>

<p>
Place: {$place_name}<br>
Day: {$day}<br>
Number of Hours: {$hours} {$app.locale->p($hours,'hour','hours')}
</p>

<p>The entry has been removed. If you made a mistake then you can submit a new entry with the corrected information.</p>

<p>- InspireVive</p>
{/block}