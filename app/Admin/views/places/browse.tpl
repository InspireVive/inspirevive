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

{if count($places) > 0 || $search}
	<div class="browse-search-holder">
		<div class="input-group">
			<span class="input-group-addon">
				<span class="ion-search"></span>
			</span>
			<input type="text" class="form-control browse-search input-sm" placeholder="Search..." value="{$search}" />
            {if $search}
				<span class="input-group-btn">
					<button type="button" class="btn btn-default btn-sm reset-search">
						Reset
					</button>
				</span>
            {/if}
		</div>
	</div>
{/if}

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
						<a href="?{$queryStrNoSort}&amp;sort={if $sort=='name asc'}name+desc{else}name+asc{/if}">
							Name
							{if $sort=='name desc'}
								<span class="ion-arrow-down-b sort-arrow"></span>
							{elseif $sort=='name asc'}
								<span class="ion-arrow-up-b sort-arrow"></span>
							{/if}
						</a>
					</th>
					<th>
						<a href="?{$queryStrNoSort}&amp;sort={if $sort=='verify_name asc'}verify_name+desc{else}verify_name+asc{/if}">
							Volunteer Coordinator
                            {if $sort=='verify_name desc'}
								<span class="ion-arrow-down-b sort-arrow"></span>
                            {elseif $sort=='verify_name asc'}
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
									<a href="{$org->manageUrl()}/places?{$queryStrNoPage}&amp;page={$page-1}" class="btn btn-link">
										<span class="ion-arrow-left-c"></span>
										Previous Page
									</a>
								{/if}
							</div>
							<div class="col-md-6 totals">
								<div>
									Total Places: <strong>{$count|number_format}</strong>
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
									<a href="{$org->manageUrl()}/places?{$queryStrNoPage}&amp;page={$page+1}" class="btn btn-link">
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
		var url = "{$org->manageUrl()}/places";
        var queryParams = {$req->query()|json_encode nofilter};

        $('.browse-search').keypress(function(e) {
            if (e.keyCode === 13) {
                search($(this).val());
            }
        });

        $('.reset-search').click(function() {
            search('');
        });

        $('.page-selector').change(function() {
            queryParams.page = $(this).val();
            window.location = url + '?' + $.param(queryParams);
        });

        function search(query) {
            queryParams.search = query;
            delete queryParams.page;
            window.location = url + '?' + $.param(queryParams);
        }
	});
</script>

{/block}