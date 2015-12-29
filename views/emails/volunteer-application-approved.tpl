{extends file="emails/parent.tpl"}
{block name=content}
<p>Hey {$username}!</p>

<p>We are writing to inform you that your request to join <strong>{$orgname}</strong> on InspireVive has been approved!</p>

<p>Now that you are confirmed as a volunteer of {$orgname} you have access to the Volunteer Hub where you can keep up to date with the latest volunteer opportunities and stay in touch with your fellow {$orgname} volunteers.</p>

<table>
  <tr>
      <td align="center" width="240" bgcolor="#ee5237" style="background: #ee5237; padding-top: 6px; padding-right: 10px; padding-bottom: 6px; padding-left: 10px; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; color: #fff; font-weight: bold; text-decoration: none; font-family: Helvetica, Arial, sans-serif; display: block;"><a href="{$orgurl}" style="color: #fff; text-decoration: none;">View Volunteer Hub</a></td>
  </tr>
</table>

<p>- InspireVive</p>
{/block}