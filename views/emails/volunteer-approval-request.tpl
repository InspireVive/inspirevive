{extends file="emails/parent.tpl"}
{block name=content}
<p>Hi {$orgname}!</p>

<p>A new volunteer has expressed interest in volunteering at your organization on InspireVive.</p>

<p>
{if isset($full_name)}Name: <strong>{$name}</strong><br>{/if}
	Username: <strong>{$username}</strong><br>
	Email: <a href="mailto:{$volunteer_email}" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;color: #606060;font-weight: normal;text-decoration: underline;"><strong>{$volunteer_email}</strong></a><br>
{if isset($full_name)}
	Address: <strong>{$address}</strong><br>
	Phone #: <strong>{$phone}</strong><br>
	Alternate Phone #: <strong>{$alternate_phone}</strong><br>
	Age: <strong>{$age}</strong>
{/if}
</p>

<p>
	<strong>Do you approve this volunteer's request to join {$orgname}?</strong>
</p>

<table>
  <tr>
      <td align="center" width="240" bgcolor="#5aba66" style="background: #5aba66; padding-top: 6px; padding-right: 10px; padding-bottom: 6px; padding-left: 10px; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; color: #fff; font-weight: bold; text-decoration: none; font-family: Helvetica, Arial, sans-serif; display: block;"><a href="{$approval_link}" style="color: #fff; text-decoration: none;">Yes, I approve</a></td>
  </tr>
</table>

<table>
  <tr>
      <td align="center" width="240" bgcolor="#ee5237" style="background: #ee5237; padding-top: 6px; padding-right: 10px; padding-bottom: 6px; padding-left: 10px; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; color: #fff; font-weight: bold; text-decoration: none; font-family: Helvetica, Arial, sans-serif; display: block;"><a href="{$reject_link}" style="color: #fff; text-decoration: none;">No, I do not approve</a></td>
  </tr>
</table>

<p>
	If you need additional information then please contact this volunteer using the information supplied above.
</p>

<p>- InspireVive</p>
{/block}