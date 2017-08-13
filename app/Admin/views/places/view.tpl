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
					<li>
						<a href="{$org->manageUrl()}/places/{$place.id}/edit">
							Edit Place
						</a>
					</li>
					<li class="divider"></li>
					<li class="danger">
						<a href="#" class="delete-place">
							Delete Place
						</a>
					</li>
				</ul>
			</div>
		</div>

		<h1>{$place.name}</h1>
	</div>

	<div class="two-column clearfix">
		<div class="left-col details-list">
			<h3>Details</h3>
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
						{$place.address|nl2br nofilter}
					</div>
				</div>
            {/if}

            {if $place.place_type == $smarty.const.VOLUNTEER_PLACE_EXTERNAL}
				<div class="section">
					<label class="title">Volunteer Coordinator</label>
					<div class="value">
						{$place.verify_name}<br/>
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

			<!-- Recent Volunteer Activity -->
			<h3>Recent Volunteer Activity</h3>
            {if count($hours) == 0}
				<p class="empty">
					<span class="glyphicon glyphicon-time"></span>
					No volunteer hours have been recorded at this place yet.
				</p>
            {else}
				<div class="volunteer-activity">
					<table class="table table-striped">
						<thead>
						<tr>
							<th>
								Date
							</th>
							<th>
								Place
							</th>
							<th>
								# Hours
							</th>
							<th>
								Status
							</th>
						</tr>
						</thead>
						<tbody>
                        {foreach from=$hours item=hour}
							<tr class="clickable" onclick="window.location='{$org->manageUrl()}/hours/{$hour->id()}'">
								<td>
                                    {$hour->timestamp|date_format:'M d, Y'}
								</td>
								<td>
                                    {$hour->relation('place')->name}
								</td>
								<td>
                                    {$hour->hours}
								</td>
								<td>
                                    {if $hour->approved}
										<label class="label label-success">Approved</label>
                                    {else}
										<span class="label label-warning">Pending Approval</span>
                                    {/if}
								</td>
							</tr>
                            {foreachelse}
							<tr>
								<td colspan="4">
									<em>No volunteer activity yet.</em>
								</td>
							</tr>
                        {/foreach}
						</tbody>
						<tfoot>
						<tr>
							<td colspan="4">
							</td>
						</tr>
						</tfoot>
					</table>
				</div>
            {/if}
		</div>
	</div>
</div>

<form id="deletePlaceForm" method="post" action="{$org->manageUrl()}/places/{$place.id}">
	{$app.csrf->render($req) nofilter}
	<input type="hidden" name="method" value="DELETE" />
</form>

<script type="text/javascript">
	$(function() {
		$('.delete-place').click(function(e) {
		    e.preventDefault();
		    if (window.confirm('Are you sure you want to delete this place?')) {
		        $('#deletePlaceForm').submit();
            }
        });
    });
</script>

{/block}