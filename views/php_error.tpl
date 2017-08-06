{extends file="parent.tpl"}
{block name=content}

{if $app.environment == 'development'}
	<section class="exception">
		<div class="container">
			<h1>PHP Error</h1>
			<pre>{$error}</pre>
		</div>
	</section>
{else}
	<h1>Internal Server Error</h1>
{/if}

{/block}