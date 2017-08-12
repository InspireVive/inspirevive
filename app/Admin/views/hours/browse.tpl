{extends file="$viewsDir/parent.tpl"}
{block name=content}

{if $numAdded > 0 && $numVolunteers > 0}
	<p class="alert alert-success">
		Added a total of <strong>{$numAdded} {$app.locale->p($numAdded,'hour','hours')}</strong> for <strong>{$numVolunteers} {$app.locale->p($numVolunteers,'volunteer','volunteers')}</strong>.
	</p>
{/if}

<div class="browse-params">
	<ul class="nav nav-tabs browse-tabs">
		<li class="{if $showApproved}active{/if}">
			<a href="?approved=1">
				Approved
			</a>
		</li>
		<li class="{if !$showApproved}active{/if}">
			<a href="?approved=0">
				Awaiting Approval
				{if $hoursAwaitingApproval > 0}
					<span class="badge">
						{$hoursAwaitingApproval}
					</span>
				{/if}
			</a>
		</li>
	</ul>
</div>

{if count($hours) == 0}
	<p class="empty">
		<span class="glyphicon glyphicon-time"></span>
		No matching volunteer places were found.
	</p>
{else}
	<div class="browse-table-holder">
		<table class="table table-striped">
			<thead>
				<tr>
					<th>
						Volunteer
					</th>
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
						{$hour->relation('uid')->name(true)}
					</td>
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
			{/foreach}
			</tbody>
			<tfoot>
				<tr>
					<td colspan="5">
						<!-- Pagination -->
						<div class="row browse-pagination">
							<div class="col-md-3">
								{if $hasLess}
									<a href="{$org->manageUrl()}/hours?approved={$showApproved}&amp;page={$page-1}" class="btn btn-link">
										<span class="ion-arrow-left-c"></span>
										Previous Page
									</a>
								{/if}
							</div>
							<div class="col-md-6 totals">
								Total Hour Entries: <strong>{$count|number_format}</strong> &middot; Page: {$page+1}
							</div>
							<div class="col-md-3 text-right">
								{if $hasMore}
									<a href="{$org->manageUrl()}/hours?approved={$showApproved}&amp;page={$page+1}" class="btn btn-link">
										Next Page
										<span class="ion-arrow-right-c"></span>
									</a>
								{/if}
							</div>
						</div>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>
{/if}

{/block}