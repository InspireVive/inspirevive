{extends file="emails/parent.tpl"}
{block name=content}
<p>Dear {$username},</p>

<p>We are writing to inform you that your password was successfully changed on {$smarty.const.SITE_TITLE} from {$ip}.</p>

<p>- InspireVive</p>
{/block}