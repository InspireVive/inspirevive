<!DOCTYPE html>
<html>
<head>
	<title>{$header.Title}</title>
	<link rel="stylesheet" type="text/css" href="{$css}" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body>
	<div id="title">{$header.Title}</div>

	<div class="section header">
		<div class="key-value">
		{foreach from=$header key=title item=value}
			{if !in_array($title, array('Title'))}
			<div class="row clearfix">
				<div class="key">
					{$title}
				</div>
				<div class="value">
					{$value}
				</div>
			</div>
			{/if}
		{/foreach}
		</div>
	</div>

	{foreach from=$sections item=section}
		<div class="section">
			{if $section.title}
				<h3>{$section.title}</h3>
			{/if}
			{if isset($section.keyvalue)}
				<div class="key-value">
				{foreach from=$section.keyvalue key=title item=value}
					<div class="row clearfix">
						<div class="key">
							{$title}
						</div>
						<div class="value">
							{$value}
						</div>
					</div>
				{/foreach}
				</div>
			{/if}
			{if isset($section.rows)}
			<table>
				{if $section.header}
				<thead>
					<tr>
					{foreach from=$section.header item=val}
						<th>{$val}</th>
					{/foreach}
					</tr>
				</thead>
				{/if}
				<tbody>
				{foreach from=$section.rows item=row}
					<tr>
					{foreach from=$row item=val}
						<td>{$val}</td>
					{/foreach}
					</tr>
				{/foreach}
				</tbody>
				{if $section.footer}
				<tfoot>
					<tr>
					{foreach from=$section.footer item=val}
						<td>{$val}</td>
					{/foreach}
					</tr>
				</tfoot>
				{/if}
			</table>
			{/if}
		</div>
	{/foreach}
</body>
</html>