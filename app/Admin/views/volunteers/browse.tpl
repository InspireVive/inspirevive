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
		<li class="{if $showApproved}active{/if}">
			<a href="?approved=1">
				Approved
			</a>
		</li>
		<li class="{if $showPending}active{/if}">
			<a href="?approved=0">
				Awaiting Approval
				{if $volunteersAwaitingApproval > 0}
					<span class="badge">
						{$volunteersAwaitingApproval}
					</span>
				{/if}
			</a>
		</li>
		<li class="{if $showInactive}active{/if}">
			<a href="?inactive=1">
				Inactive
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
					<th>Name</th>
					<th>Email</th>
					<th>Status</th>
				</tr>
			</thead>
			<tbody>
			{foreach from=$volunteers item=volunteer}
				{assign var=user value=$volunteer->relation('uid')}
				<tr class="clickable" onclick="window.location='{$org->manageUrl()}/volunteers/{$user->id()}'">
					<td>
						{if $user->hasCompletedVolunteerApplication()}
							{$user->volunteerApplication()->fullName()}
						{/if}
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
					<td colspan="3">
						<!-- Pagination -->
						<div class="row browse-pagination">
							<div class="col-md-3">
								{if $hasLess}
									<a href="{$org->manageUrl()}/volunteers?inactive={$showInactive}&amp;approved={$showApproved}&amp;page={$page-1}" class="btn btn-link">
										<span class="ion-arrow-left-c"></span>
										Previous Page
									</a>
								{/if}
							</div>
							<div class="col-md-6 totals">
								Total Volunteers: <strong>{$count|number_format}</strong> &middot; Page: {$page+1}
							</div>
							<div class="col-md-3 text-right">
								{if $hasMore}
									<a href="{$org->manageUrl()}/volunteers?inactive={$showInactive}&amp;approved={$showApproved}&amp;page={$page+1}" class="btn btn-link">
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