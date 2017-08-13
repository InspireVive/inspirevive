{extends file="$viewsDir/parent.tpl"}
{block name=content}

{if $numAdded > 0 && $numVolunteers > 0}
	<p class="alert alert-success">
		Added a total of <strong>{$numAdded} {$app.locale->p($numAdded,'hour','hours')}</strong> for <strong>{$numVolunteers} {$app.locale->p($numVolunteers,'volunteer','volunteers')}</strong>.
	</p>
{/if}

<div class="browse-params">
	<ul class="nav nav-tabs browse-tabs">
		<li class="{if $tab=='approved'}active{/if}">
			<a href="?approved=1">
				Approved
			</a>
		</li>
		<li class="{if $tab=='pending'}active{/if}">
			<a href="?approved=0">
				Awaiting Approval
				{if $hoursAwaitingApproval > 0}
					<span class="badge">
						{$hoursAwaitingApproval}
					</span>
				{/if}
			</a>
		</li>
		<li class="{if $tab=='all'}active{/if}">
			<a href="?tab=all">
				All
			</a>
		</li>
	</ul>
</div>

{if count($hours) == 0}
	<p class="empty">
		<span class="glyphicon glyphicon-time"></span>
		No matching hour entries were found.
	</p>
{else}
	<div class="browse-table-holder">
		<table class="table table-striped">
			<thead>
				<tr>
					<th>
						<a href="?{$queryStrNoSort}&amp;sort={if $sort=='Users.full_name desc'}Users.full_name+asc{else}Users.full_name+desc{/if}">
							Volunteer
                            {if $sort=='Users.full_name desc'}
								<span class="ion-arrow-down-b sort-arrow"></span>
                            {elseif $sort=='Users.full_name asc'}
								<span class="ion-arrow-up-b sort-arrow"></span>
                            {/if}
						</a>
					</th>
					<th>
						<a href="?{$queryStrNoSort}&amp;sort={if $sort=='timestamp desc'}timestamp+asc{else}timestamp+desc{/if}">
							Date
                            {if $sort=='timestamp desc'}
								<span class="ion-arrow-down-b sort-arrow"></span>
                            {elseif $sort=='timestamp asc'}
								<span class="ion-arrow-up-b sort-arrow"></span>
                            {/if}
						</a>
					</th>
					<th>
						<a href="?{$queryStrNoSort}&amp;sort={if $sort=='VolunteerPlaces.name desc'}VolunteerPlaces.name+asc{else}VolunteerPlaces.name+desc{/if}">
							Place
                            {if $sort=='VolunteerPlaces.name desc'}
								<span class="ion-arrow-down-b sort-arrow"></span>
                            {elseif $sort=='VolunteerPlaces.name asc'}
								<span class="ion-arrow-up-b sort-arrow"></span>
                            {/if}
						</a>
					</th>
					<th>
						<a href="?{$queryStrNoSort}&amp;sort={if $sort=='hours desc'}hours+asc{else}hours+desc{/if}">
							# Hours
                            {if $sort=='hours desc'}
								<span class="ion-arrow-down-b sort-arrow"></span>
                            {elseif $sort=='hours asc'}
								<span class="ion-arrow-up-b sort-arrow"></span>
                            {/if}
						</a>
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
									<a href="{$org->manageUrl()}/hours?{$queryStrNoPage}&amp;page={$page-1}" class="btn btn-link">
										<span class="ion-arrow-left-c"></span>
										Previous Page
									</a>
								{/if}
							</div>
							<div class="col-md-6 totals">
								<div>
									Total Hour Entries: <strong>{$count|number_format}</strong>
								</div>
                                {if $numPages > 1}
									<div>
										Page:
										<select class="page-selector">
											{for $i=1 to $numPages}
												<option value="{$i}" {if $i == $page}selected="selected"{/if}>{$i}</option>
											{/for}
										</select>
									</div>
								{/if}
							</div>
							<div class="col-md-3 text-right">
								{if $hasMore}
									<a href="{$org->manageUrl()}/hours?{$queryStrNoPage}&amp;page={$page+1}" class="btn btn-link">
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

<script type="text/javascript">
	$(function() {
		var url = "{$org->manageUrl()}/volunteers?" + {$queryStrNoPage|json_encode nofilter};
		$('.page-selector').change(function() {
			window.location = url + '&page=' + $(this).val();
		});
	});
</script>

{/block}