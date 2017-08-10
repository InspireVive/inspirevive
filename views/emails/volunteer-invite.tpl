{extends file="emails/parent.tpl"}
{block name=content}
<p>Dear {$username}!</p>

<p>You have been invited to join <strong>{$orgname}</strong> on InspireVive.</p>

<p>On InspireVive volunteer organizations like {$orgname} use our platform to manage their volunteer base. We allow non-profits to track volunteer hours, coordinate events, and communicate with their volunteer base.</p>

<p>As a volunteer you can create a free account on InspireVive to share your contact information with {$orgname}, see your volunteer hours, and stay up to date with the latest volunteer events.</p>

<table>
  <tr>
      <td align="center" width="100%" style="padding: 0;"><a style="width: 240px; background: #ee5237; padding-top: 6px; padding-right: 10px; padding-bottom: 6px; padding-left: 10px; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; color: #fff; font-weight: bold; text-decoration: none; font-family: Helvetica, Arial, sans-serif; display: block;" href="{$cta_url}" style="color: #fff; text-decoration: none;">Join {$orgname} on InspireVive</a></td>
  </tr>
</table>

<p>- InspireVive</p>
{/block}