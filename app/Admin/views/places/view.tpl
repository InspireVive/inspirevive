{extends file="$viewsDir/parent.tpl"}
{block name=content}

<div class="object-view">
	<div class="object-title">
		<div class="actions">
			<a href="{$org->manageUrl()}/places/{$place.id}/edit" class="btn btn-default">
				Edit Place
			</a>

			<form method="post" action="{$org->manageUrl()}/places/{$place.id}">
                {$app.csrf->render($req) nofilter}
				<input type="hidden" name="method" value="DELETE" />
				<button type="submit" class="btn btn-danger">
					Delete Place
				</button>
			</form>
		</div>

		<h1>{$place.name}</h1>
	</div>

	<div class="two-column clearfix">
		<div class="left-col details-list">
			<div class="section">
				<label class="title">Name</label>
				<div class="value">
					{$place.name}
				</div>
			</div>

			<div class="section">
				<label class="title">Type</label>
				<div class="value">
					{if $place.place_type == $smarty.const.VOLUNTEER_PLACE_EXTERNAL}
						External
					{else}
						Internal
					{/if}
				</div>
			</div>

            {if $place.address}
				<div class="section">
					<label class="title">Address</label>
					<div class="value">
						{$place.address}
					</div>
				</div>
            {/if}

            {if $place.place_type == $smarty.const.VOLUNTEER_PLACE_EXTERNAL}
				<div class="section">
					<label class="title">Volunteer Coordinator Name</label>
					<div class="value">
						{$place.verify_name}
					</div>
				</div>

				<div class="section">
					<label class="title">Volunteer Coordinator Email</label>
					<div class="value">
						<a href="mailto:{$place.verify_email}">
							{$place.verify_email}
						</a>
					</div>
				</div>

				<div class="section">
					<label class="title">Status</label>
					<div class="value">
						{if $place.verify_approved}
							<label class="label label-success">Approved</label>
						{else}
							<label class="label label-warning">Pending Approval</label>
						{/if}
					</div>
				</div>
            {/if}
		</div>

		<div class="right-col">
			<!-- Action Items -->
			{if $place.place_type == $smarty.const.VOLUNTEER_PLACE_EXTERNAL && !$place.verify_approved}
				<div class="action-item">
					<div class="title">
						Can this volunteer coordinator verify hours?
					</div>
					<p>This volunteer place needs to be approved before <em>{$place.verify_name}</em> can approve hours reported by volunteers.</p>
					<div class="actions">
						<form method="post" action="{$org->manageUrl()}/places/{$place.id}" class="inline">
							{$app.csrf->render($req) nofilter}
							<input type="hidden" name="method" value="DELETE" />
							<button type="submit" class="btn btn-danger">
								Deny
							</button>
						</form>
						<form method="post" action="{$org->manageUrl()}/places/{$place.id}" class="inline">
							{$app.csrf->render($req) nofilter}
							<input type="hidden" name="verify_approved" value="1" />
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

{/block}