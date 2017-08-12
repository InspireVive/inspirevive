{extends file="$viewsDir/parent.tpl"}
{block name=content}

{if $success}
	<p class="alert alert-success">
		A new volunteer place was added!
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
				{if $placesAwaitingApproval > 0}
					<span class="badge">
						{$placesAwaitingApproval}
					</span>
				{/if}
			</a>
		</li>
		<li class="{if $tab=='all'}active{/if}">
			<a href="?tab=all">
				All
			</a>
		</li>
		<li class="action">
			<a href="{$org->manageUrl()}/places/add" class="btn btn-success">
				<span class="ion-plus-round"></span>
				New Place
			</a>
		</li>
	</ul>
</div>

{if count($places) == 0}
	<p class="empty">
		<span class="glyphicon glyphicon-map-marker"></span>
		No matching volunteer places were found.
		<a href="{$org->manageUrl()}/places/add">Add one</a>
	</p>
{else}
	<div class="browse-table-holder">
		<table class="table table-striped">
			<thead>
				<tr>
					<th>
						Name
					</th>
					<th>
						Volunteer Coordinator
					</th>
					<th>
						Status
					</th>
				</tr>
			</thead>
			<tbody>
			{foreach from=$places item=place}
				<tr class="clickable" onclick="window.location='{$org->manageUrl()}/places/{$place->id()}'">
					<td>
						{$place->name}
					</td>
					<td>
						{if $place->place_type == $smarty.const.VOLUNTEER_PLACE_EXTERNAL}
							{$place->verify_name}
						{/if}
					</td>
					{if $place->place_type == $smarty.const.VOLUNTEER_PLACE_EXTERNAL}
						<td>
							{if $place->verify_approved}
								<span class="label label-success">Approved</span>
							{else}
								<span class="label label-warning">Pending Approval</span>
							{/if}
						</td>
					{else}
						<td><span class="label label-success">Approved</span></td>
					{/if}
				</tr>
			{/foreach}
			</tbody>
			<tfoot>
				<tr>
					<td colspan="3">
						<!-- Pagination -->
						<div class="row browse-pagination">
							<div class="col-md-3">
								{if $hasLess}
									<a href="{$org->manageUrl()}/places?{$queryStr}&amp;page={$page-1}" class="btn btn-link">
										<span class="ion-arrow-left-c"></span>
										Previous Page
									</a>
								{/if}
							</div>
							<div class="col-md-6 totals">
								Total Places: <strong>{$count|number_format}</strong> &middot; Page: {$page+1}
							</div>
							<div class="col-md-3 text-right">
								{if $hasMore}
									<a href="{$org->manageUrl()}/places?{$queryStr}&amp;page={$page+1}" class="btn btn-link">
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