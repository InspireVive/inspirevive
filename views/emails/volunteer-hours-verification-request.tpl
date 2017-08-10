{extends file="emails/parent.tpl"}
{block name=content}
<p>
Dear {$coordinator_name},
</p>

<p>
We are writing on behalf of {$orgname} to kindly request your help in verifying the volunteer hours {$volunteer_name} claimed occured at {$place_name}. Do you verify that this is correct?
</p>

<p>
Volunteer: <a href="{$volunteer_url}">{$volunteer_name}</a><br>
Place: {$place_name}<br>
Day: {$day}<br>
Number of Hours: {$hours} {$app.locale->p($hours,'hour','hours')}
</p>

<table>
  <tr>
      <td align="center" width="100%" style="padding: 0;"><a style="width: 240px; background: #5aba66; padding-top: 6px; padding-right: 10px; padding-bottom: 6px; padding-left: 10px; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; color: #fff; font-weight: bold; text-decoration: none; font-family: Helvetica, Arial, sans-serif; display: block;" href="{$approval_link}" style="color: #fff; text-decoration: none;">Yes, this is correct</a></td>
  </tr>
</table>

<table>
  <tr>
      <td align="center" width="100%" style="padding: 0;"><a style="width: 240px; background: #ee5237; padding-top: 6px; padding-right: 10px; padding-bottom: 6px; padding-left: 10px; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; color: #fff; font-weight: bold; text-decoration: none; font-family: Helvetica, Arial, sans-serif; display: block;" href="{$reject_link}" style="color: #fff; text-decoration: none;">No, this is not correct</a></td>
  </tr>
</table>

<p>
If the reported entry is not correct, once you click <strong>No</strong> we will remove the entry from our database and inform the volunteer that the hours were not verified. If they made a mistake when reporting the hours, they will have the opportunity to report the corrected hours and we will ask for your approval again.
</p>

<p>
Thank you,<br>
InspireVive
</p>

<h4>About InspireVive</h4>

<p>
It is InspireVive's mission to increase volunteerism. One way we accomplish our mission is by providing software to volunteer organizations, like {$orgname}, to effectively manage their volunteer base.
</p>

{/block}