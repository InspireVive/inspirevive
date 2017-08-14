{extends file="emails/parent.tpl"}
{block name=content}
<p>Dear {$username},</p>

<p>Thank you for joining InspireVive. Before we can activate your account, you must complete one last step.</p>

<p>Please click this button just once to activate your account:</p>

<table>
  <tr>
    <td align="center" width="100%" style="padding: 0;">
      <a style="width: 240px; background: #ee5237; padding-top: 6px; padding-right: 10px; padding-bottom: 6px; padding-left: 10px; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; color: #fff; font-weight: bold; text-decoration: none; font-family: Helvetica, Arial, sans-serif; display: block;" href="{$verify_link}">Verify this email address</a>
    </td>
  </tr>
</table>

<p>- InspireVive</p>
{/block}