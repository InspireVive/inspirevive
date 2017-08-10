{extends file="$viewsDir/parent.tpl"}
{block name=content}

{if $success}
	<p class="alert alert-success">
		A new volunteer place was added!
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
				{if $placesAwaitingApproval > 0}
					<span class="badge">
						{$placesAwaitingApproval}
					</span>
				{/if}
			</a>
		</li>
		<li class="action">
			<a href="{$org->manageUrl()}/places/add" class="btn btn-success">
				<span class="glyphicon glyphicon-plus"></span>
				New Place
			</a>
		</li>
	</ul>
</div>

{if count($places) == 0}
	<p class="empty">
		<span class="glyphicon glyphicon-map-marker"></span>
		None found.
		<a href="{$org->manageUrl()}/places/add">Add one</a>
	</p>
{else}
	<table class="table table-striped">
		<thead>
			<tr>
				<th></th>
				<th>
					Name
				</th>
				<th>
					Type
				</th>
				<th>
					Status
				</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$places item=place}
			<tr>
				<td>
					<a href="{$org->manageUrl()}/places/{$place->id()}" class="btn btn-default">
						Details
					</a>
				</td>
				<td>
					{$place->name}
				</td>
				<td>
					{if $place->place_type == $smarty.const.VOLUNTEER_PLACE_EXTERNAL}
						External
					{else}
						Internal
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
					<td>
						{if !$place->verify_approved}
							<form method="post" action="{$org->manageUrl()}/places/{$place->id()}?redir=browse">
			                    {$app.csrf->render($req) nofilter}
								<input type="hidden" name="verify_approved" value="1" />
								<button type="submit" class="btn btn-success">
									Approve
								</button>
							</form>
						{/if}
					</td>
				{else}
					<td><span class="label label-success">Approved</span></td>
					<td></td>
				{/if}
			</tr>
		{/foreach}
		</tbody>
	</table>

	<!-- Pagination -->
	<div class="row">
		<div class="col-md-3">
			{if $hasLess}
				<a href="{$org->manageUrl()}/places?approved={$showApproved}&amp;page={$page-1}" class="btn btn-default">
					<span class="glyphicon glyphicon-arrow-left"></span>
					Previous Page
				</a>
			{/if}
		</div>
		<div class="col-md-6 text-center">
			Page: <em>{$page+1}</em>, Total Places: <em>{$count|number_format}</em>
		</div>
		<div class="col-md-3 text-right">
			{if $hasMore}
				<a href="{$org->manageUrl()}/places?approved={$showApproved}&amp;page={$page+1}" class="btn btn-default">
					Next Page
					<span class="glyphicon glyphicon-arrow-right"></span>
				</a>
			{/if}
		</div>
	</div>
{/if}

{/block}