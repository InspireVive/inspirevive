{extends file="emails/parent.tpl"}
{block name=content}
<p>Hi {$orgname}!</p>

<p>There {$app.locale->p($num_unapproved,'is','are')} {$num_unapproved} new volunteer {$app.locale->p($num_unapproved,'hour entry','hour entries')} reported by volunteers for {$orgname} awaiting your approval on InspireVive.</p>

<table>
	<tr>
		<td align="center" width="240" bgcolor="#ee5237" style="background: #ee5237; padding-top: 6px; padding-right: 10px; padding-bottom: 6px; padding-left: 10px; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; color: #fff; font-weight: bold; text-decoration: none; font-family: Helvetica, Arial, sans-serif; display: block;"><a href="{$orgurl}/admin/hours?unapproved=1" style="color: #fff; text-decoration: none;">View hours waiting for approval</a></td>
	</tr>
</table>

<p>Thank you for your help in making a difference.</p>

<p>- InspireVive</p>
{/block}