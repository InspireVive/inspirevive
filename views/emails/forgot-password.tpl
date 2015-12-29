{extends file="emails/parent.tpl"}
{block name=content}
<p>Dear {$username},</p>

<p>A request was made to reset your password on InspireVive from {$ip}. If you did not make this request, please ignore this message and nothing will be changed.</p>

<p>If you do wish to reset your password please visit the following page:</p>

<table>
	<tr>
		<td align="center" width="240" bgcolor="#ee5237" style="background: #ee5237; padding-top: 6px; padding-right: 10px; padding-bottom: 6px; padding-left: 10px; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; color: #fff; font-weight: bold; text-decoration: none; font-family: Helvetica, Arial, sans-serif; display: block;"><a href="{$forgot_link}" style="color: #fff; text-decoration: none;">Reset your password</a></td>
	</tr>
</table>

<p>- InspireVive</p>
{/block}