{extends file="emails/parent.tpl"}
{block name=content}
<p>
Hi {$orgname}!
</p>

<p>
A volunteer from {$orgname} has requested that a new place be added to the list of places your volunteers can report hours at. By approving this volunteer place we can verify future volunteer activity at {$place_name} with the volunteer coordinator listed below over email.
</p>

<p>
Place: <strong>{$place_name}</strong><br>
Address: {$address|nl2br}<br>
Volunteer Coordinator Name: {$coordinator_name}<br>
Volunteer Coordinator Email: <a href="mailto:{$coordinator_email}">{$coordinator_email}</a>
</p>

<p>
Do you approve adding this place to the list of volunteer places for {$orgname}?
</p>

<table>
  <tr>
      <td align="center" width="240" bgcolor="#ee5237" style="background: #ee5237; padding-top: 6px; padding-right: 10px; padding-bottom: 6px; padding-left: 10px; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; color: #fff; font-weight: bold; text-decoration: none; font-family: Helvetica, Arial, sans-serif; display: block;"><a href="{$place_admin_url}" style="color: #fff; text-decoration: none;">Approve on InspireVive</a></td>
  </tr>
</table>

<p>
- InspireVive
</p>
{/block}