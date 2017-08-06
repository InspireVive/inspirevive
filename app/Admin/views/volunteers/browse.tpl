{extends file="$viewsDir//parent.tpl"}
{block name=content}

{if $numAdded > 0}
	<p class="alert alert-success">
		{$numAdded} {$app.locale->p($numAdded, 'volunteer was', 'volunteers were')} added!
	</p>
{/if}

{foreach from=$app.errors->messages() item=error}
	<p class="alert alert-danger">{$error}</p>
{/foreach}

<div class="row browse-params">
	<div class="col-md-4">
		<ul class="nav nav-pills">
			<li class="{if $showApproved}active{/if}">
				<a href="?approved=1">
					Approved
				</a>
			</li>
			<li class="{if !$showApproved}active{/if}">
				<a href="?approved=0">
					Awaiting Approval
					{if $volunteersAwaitingApproval > 0}
						<span class="badge">
							{$volunteersAwaitingApproval}
						</span>
					{/if}
				</a>
			</li>
		</ul>
	</div>
	<div class="col-md-5">
	</div>
	<div class="col-md-3 new-btn">
		<a href="{$org->manageUrl()}/volunteers/add" class="btn btn-success">
			<span class="glyphicon glyphicon-user"></span>
			Add Volunteers
		</a>
	</div>
</div>

<div class="row">
	<div class="col-md-4">
		{if $usernameNotFound}
			<p class="alert alert-danger">
				Could not find a volunteer with the username <strong>{$username|htmlspecialchars}</strong>.
			</p>
		{/if}
		<form action="{$org->manageUrl()}/volunteers/lookupUsername">
			<label>
				Look up volunteer by username:
			</label>
			<div class="lookup-username">
				<div class="input-group">
					<input type="text" class="form-control" name="username" placeholder="Search for..." />
					<span class="input-group-btn">
						<button type="submit" class="btn btn-default">
							Go!
						</button>
					</span>
				</div>
			</div>
		</form>
	</div>
	<div class="col-md-4">
		{if $showApproved}
			<div class="show-inactive-toggle">
				<label>
					<div class="switch">
						<input id="switch-show-inactive" class="cmn-toggle cmn-toggle-round" type="checkbox" {if $showInactive}checked="checked"{/if} />
						<label for="switch-show-inactive"></label>
					</div>
					Show Inactive
				</label>
			</div>
		{/if}
	</div>
</div>

{if count($volunteers) == 0}
	<p class="empty">
		<span class="glyphicon glyphicon-user"></span>
		None found.
		<a href="{$org->manageUrl()}/volunteers/add">Add one</a>
	</p>
{else}
	<table class="table table-striped">
		<thead>
			<tr>
				<th></th>
				<th>Name</th>
				<th>Username</th>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$volunteers item=volunteer}
			{assign var=user value=$volunteer->relation('uid')}
			<tr>
				<td>
					<a href="{$org->manageUrl()}/volunteers/{$user->id()}" class="btn btn-default">
						Details
					</a>
				</td>
				<td>
					{if !$user->hasCompletedVolunteerApplication()}
						{if $user->isTemporary()}
							<span class="text-danger">
								<span class="glyphicon glyphicon-exclamation-sign"></span>
								Not registered on InspireVive
							</span>
						{else}
							<span class="text-danger">
								<span class="glyphicon glyphicon-exclamation-sign"></span>
								Missing volunteer application
							</span>
						{/if}
					{elseif !$volunteer->application_shared}
						<span class="text-danger">
							<span class="glyphicon glyphicon-exclamation-sign"></span>
							Volunteer application not shared
						</span>
					{else}
						{$user->volunteerApplication()->fullName()}
					{/if}
				</td>
				<td>
					{if $user->username}
						{$user->username}
					{else}
						{$user->email}
					{/if}
				</td>
				<td>
					{if $volunteer->role >= $smarty.const.VOLUNTEER_ROLE_VOLUNTEER}
						{if $volunteer->active}
							<span class="label label-success">Active</span>
						{else}
							<span class="label label-default">Inactive</span>
						{/if}
					{else}
						<form method="post" action="{$org->manageUrl()}/volunteers/{$volunteer->id()}?redir=browse">
							<input type="hidden" name="role" value="{$smarty.const.ORGANIZATION_ROLE_VOLUNTEER}" />
							<button type="submit" class="btn btn-success">
								Approve
							</button>
						</form>
					{/if}
				</td>
			</tr>
		{/foreach}
		</tbody>
	</table>

	<!-- Pagination -->
	<div class="row">
		<div class="col-md-3">
			{if $hasLess}
				<a href="{$org->manageUrl()}/volunteers?inactive={$showInactive}&amp;approved={$showApproved}&amp;page={$page-1}" class="btn btn-default">
					<span class="glyphicon glyphicon-arrow-left"></span>
					Previous Page
				</a>
			{/if}
		</div>
		<div class="col-md-6 text-center">
			Page: <em>{$page+1}</em>, Total Volunteers: <em>{$count|number_format}</em>
		</div>
		<div class="col-md-3 text-right">
			{if $hasMore}
				<a href="{$org->manageUrl()}/volunteers?inactive={$showInactive}&amp;approved={$showApproved}&amp;page={$page+1}" class="btn btn-default">
					Next Page
					<span class="glyphicon glyphicon-arrow-right"></span>
				</a>
			{/if}
		</div>
	</div>
{/if}

<script type="text/javascript">
	var showingInactive = {if $showInactive}true{else}false{/if};
	var showingApproved = {if $showApproved}true{else}false{/if};

	$('#switch-show-inactive').change(function() {
		showingInactive = !showingInactive;
 		window.location = '{$org->manageUrl()}/volunteers?inactive='+(showingInactive+0)+'&approved='+(showingApproved+0);
	});
</script>
{/block}