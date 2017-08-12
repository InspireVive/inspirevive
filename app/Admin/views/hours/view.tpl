{extends file="$viewsDir/parent.tpl"}
{block name=content}

<div class="object-view">
	<div class="object-title">
		<div class="actions">
			<div class="dropdown">
				<button type="button" class="btn btn-link btn-lg" data-toggle="dropdown">
					Options
					<span class="ion-chevron-down"></span>
				</button>
				<ul class="dropdown-menu dropdown-menu-right">
					<li class="danger">
						<a href="#" class="delete-hour">
							Delete Hour Entry
						</a>
					</li>
				</ul>
			</div>
		</div>

		<h1>
			Volunteer Hours
		</h1>
		<h2>
            {$volunteer->name(true)}
			@
            {$place->name}
		</h2>
	</div>

	<div class="two-column clearfix">
		<div class="left-col details-list">
			<h3>Details</h3>
			<div class="section">
				<label class="title">Volunteer</label>
				<div class="value">
					<a href="{$org->manageUrl()}/volunteers/{$volunteer->id()}">
						{$volunteer->name(true)}
					</a>
				</div>
			</div>

			<div class="section">
				<label class="title">Date</label>
				<div class="value">
					{$hour.timestamp|date_format:'F j, Y'}
				</div>
			</div>

			<div class="section">
				<label class="title">Place</label>
				<div class="value">
					<a href="{$org->manageUrl()}/places/{$place->id()}">
						{$place->name}
					</a>
				</div>
			</div>

			<div class="section">
				<label class="title"># Hours</label>
				<div class="value">
					{$hour.hours}
				</div>
			</div>

			<div class="section">
				<label class="title">Status</label>
				<div class="value">
					{if $hour.approved}
						<label class="label label-success">Approved</label>
					{else}
						{if $hour.verification_requested}
							<label class="label label-warning">Verification Requested from {$place->verify_email}</label>
						{else}
							<label class="label label-warning">Pending Approval</label>
						{/if}
					{/if}
				</div>
			</div>

            {if count($tags) > 0}
				<div class="section">
					<label class="title">Tags</label>
					<div class="value">
						{foreach from=$tags item=tag}
							<span class="label label-default">{$tag}</span>
						{/foreach}
					</div>
				</div>
            {/if}
		</div>
		<div class="right-col">
            {if !$hour.approved}
				<div class="action-item">
					<div class="title">Can you verify these volunteer hours?</div>
					<p>This volunteer activity has not been approved yet. Do you approve this hour entry?</p>
					<div class="actions">
						<form method="post" action="{$org->manageUrl()}/hours/{$hour.id}" class="inline">
                            {$app.csrf->render($req) nofilter}
							<input type="hidden" name="method" value="DELETE" />
							<button type="submit" class="btn btn-danger">
								Deny
							</button>
						</form>
						<form method="post" action="{$org->manageUrl()}/hours/{$hour.id}" class="inline">
                            {$app.csrf->render($req) nofilter}
							<input type="hidden" name="approved" value="1" />
							<button type="submit" class="btn btn-success">
								Approve
							</button>
						</form>
					</div>
				</div>
            {/if}
		</div>
	</div>
</div>

<form id="deleteHourForm" method="post" action="{$org->manageUrl()}/hours/{$hour.id}">
	{$app.csrf->render($req) nofilter}
	<input type="hidden" name="method" value="DELETE" />
</form>

<script type="text/javascript">
	$(function() {
		$('.delete-hour').click(function(e) {
			e.preventDefault();
			if (window.confirm('Are you sure you want to delete this hour entry?')) {
				$('#deleteHourForm').submit();
			}
		});
	});
</script>

{/block}