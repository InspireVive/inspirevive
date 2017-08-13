{extends file="$viewsDir/parent.tpl"}
{block name=content}

{if $numAdded > 0}
	<p class="alert alert-success">
		{$numAdded} {$app.locale->p($numAdded, 'volunteer was', 'volunteers were')} added!
	</p>
{/if}

{foreach from=$app.errors->messages() item=error}
	<p class="alert alert-danger">{$error}</p>
{/foreach}

<div class="browse-params">
	<ul class="nav nav-tabs browse-tabs">
		<li class="{if $tab=='approved'}active{/if}">
			<a href="?role={$smarty.const.ORGANIZATION_ROLE_VOLUNTEER}&amp;inactive=0">
				Approved
			</a>
		</li>
		<li class="{if $tab=='pending'}active{/if}">
			<a href="?role={$smarty.const.ORGANIZATION_ROLE_AWAITING_APPROVAL}&amp;inactive=0">
				Awaiting Approval
				{if $volunteersAwaitingApproval > 0}
					<span class="badge">
						{$volunteersAwaitingApproval}
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
			<a href="{$org->manageUrl()}/volunteers/add" class="btn btn-success">
				<span class="ion-plus-round"></span>
				Add Volunteers
			</a>
		</li>
	</ul>
</div>

{if count($volunteers) > 0 || $search}
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

{if count($volunteers) == 0}
	<p class="empty">
		<span class="glyphicon glyphicon-user"></span>
		No matching volunteers were found.
		<a href="{$org->manageUrl()}/volunteers/add">Add one</a>
	</p>
{else}
	<div class="browse-table-holder">
		<table class="table table-striped">
			<thead>
				<tr>
					<th>
						<a href="?{$queryStrNoSort}&amp;sort={if $sort=='Users.full_name asc'}Users.full_name+desc{else}Users.full_name+asc{/if}">
							Name
                            {if $sort=='Users.full_name desc'}
								<span class="ion-arrow-down-b sort-arrow"></span>
                            {elseif $sort=='Users.full_name asc'}
								<span class="ion-arrow-up-b sort-arrow"></span>
                            {/if}
						</a>
					</th>
					<th>
						<a href="?{$queryStrNoSort}&amp;sort={if $sort=='Users.username asc'}Users.username+desc{else}Users.username+asc{/if}">
							Username
                            {if $sort=='Users.username desc'}
								<span class="ion-arrow-down-b sort-arrow"></span>
                            {elseif $sort=='Users.username asc'}
								<span class="ion-arrow-up-b sort-arrow"></span>
                            {/if}
						</a>
					</th>
					<th>
						<a href="?{$queryStrNoSort}&amp;sort={if $sort=='Users.email asc'}Users.email+desc{else}Users.email+asc{/if}">
							Email
                            {if $sort=='Users.email desc'}
								<span class="ion-arrow-down-b sort-arrow"></span>
                            {elseif $sort=='Users.email asc'}
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
			{foreach from=$volunteers item=volunteer}
				{assign var=user value=$volunteer->relation('uid')}
				<tr class="clickable" onclick="window.location='{$org->manageUrl()}/volunteers/{$user->id()}'">
					<td>
						{$user->full_name}
					</td>
					<td>
                        {$user->username}
					</td>
					<td>
						{$user->email}
					</td>
					<td>
                        {if $volunteer->role == $smarty.const.ORGANIZATION_ROLE_ADMIN}
							<span class="label label-success">Admin</span>
						{elseif $volunteer->role == $smarty.const.ORGANIZATION_ROLE_AWAITING_APPROVAL}
							<span class="label label-warning">Pending Approval</span>
                        {elseif !$volunteer->active}
							<span class="label label-default">Inactive Volunteer</span>
						{elseif !$user->hasCompletedVolunteerApplication()}
							{if $user->isTemporary()}
								<span class="label label-danger">
									Not registered
								</span>
							{else}
								<span class="label label-danger">
									Missing volunteer application
								</span>
							{/if}
						{elseif !$volunteer->application_shared}
							<span class="label label-danger">
								Volunteer application not shared
							</span>
						{else}
							<span class="label label-success">Volunteer</span>
                        {/if}
					</td>
				</tr>
			{/foreach}
			</tbody>
			<tfoot>
				<tr>
					<td colspan="4">
						<!-- Pagination -->
						<div class="row browse-pagination">
							<div class="col-md-3">
								{if $hasLess}
									<a href="{$org->manageUrl()}/volunteers?{$queryStrNoPage}&amp;page={$page-1}" class="btn btn-link">
										<span class="ion-arrow-left-c"></span>
										Previous Page
									</a>
								{/if}
							</div>
							<div class="col-md-6 totals">
								<div>
									Total Volunteers: <strong>{$count|number_format}</strong>
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
									<a href="{$org->manageUrl()}/volunteers?{$queryStrNoPage}&amp;page={$page+1}" class="btn btn-link">
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
	    var url = "{$org->manageUrl()}/volunteers";
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
            window.location = url + '?' + $.param(queryParams);
        }
    });
</script>
{/block}