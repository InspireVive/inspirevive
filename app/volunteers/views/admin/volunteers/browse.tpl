{extends file="$viewsDir/admin/parent.tpl"}
{block name=content}

{if $numAdded > 0}
	<p class="alert alert-success">
		<strong>{$numAdded} {$app.locale->p($numAdded, 'volunteer</strong> was', 'volunteers</strong> were')} added.
	</p>
{/if}

{foreach from=$app.errors->messages() item=error}
	<p class="alert alert-danger">{$error}</p>
{/foreach}

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
<hr/>

{if $showApproved}
	<div class="row">
		<div class="col-sm-3">
			<label>Show Inactive Volunteers:</label>
		</div>
		<div class="col-sm-3">
			<div class="switch">
				<input id="switch-show-inactive" class="cmn-toggle cmn-toggle-round" type="checkbox" {if $showInactive}checked="checked"{/if} />
				<label for="switch-show-inactive"></label>
			</div>
		</div>
	</div>
{/if}

{if count($volunteers) == 0}
	<p class="empty">
		<span class="glyphicon glyphicon-user"></span>
		None found.
		<a href="{$org->url()}/admin/volunteers/add">Add one</a>
	</p>
{else}
	<table class="table table-striped">
		<thead>
			<tr>
				<th>
					<a href="{$org->url()}/admin/volunteers/add" class="btn btn-success">
						<span class="glyphicon glyphicon-plus"></span> Add Volunteers
					</a>
				</th>
				<th>Username</th>
				<th>Full Name</th>
				<th>Age</th>
				<th>Active</th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$volunteers item=volunteer}
			{assign var=user value=$volunteer->relation('uid')}
			<tr>
				<td>
					<a href="{$org->url()}/admin/volunteers/{$user->id()}" class="btn btn-default">
						Details
					</a>
				</td>
				<td>
					{$user->name(true)}
				</td>
				{if !$user->hasCompletedVolunteerApplication()}
					{if $user->isTemporary()}
						<td colspan="2">
							<span class="text-danger">
								<span class="glyphicon glyphicon-exclamation-sign"></span>
								Not registered on InspireVive
							</span>
						</td>
					{else}
						<td colspan="2">
							<span class="text-danger">
								<span class="glyphicon glyphicon-exclamation-sign"></span>
								Volunteer application not yet completed
							</span>
						</td>
					{/if}
				{elseif !$volunteer->application_shared}
					<td colspan="2">
						<span class="text-danger">
							<span class="glyphicon glyphicon-exclamation-sign"></span>
							Volunteer has not granted access to volunteer application
						</span>
					</td>
				{else}
					{assign var=application value=$user->volunteerApplication()}
					<td>
						{$application->fullName()}
					</td>
					<td>
						{$application->age()}
					</td>
				{/if}
				<td>
					{if $volunteer->role >= $smarty.const.VOLUNTEER_ROLE_VOLUNTEER}
						{if $volunteer->active}
							<span class="text-success">Active</span>
						{else}
							<span class="text-danger">Inactive</span>
						{/if}
					{else}
						<form method="post" action="{$org->url()}/admin/volunteers/{$volunteer->id()}?redir=browse">
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
		<div class="col-sm-3">
			{if $hasLess}
				<a href="{$org->url()}/admin/volunteers?inactive={$showInactive}&amp;approved={$showApproved}&amp;page={$page-1}" class="btn btn-default">
					<span class="glyphicon glyphicon-arrow-left"></span>
					Previous Page
				</a>
			{/if}
		</div>
		<div class="col-sm-6 text-center">
			Page: <em>{$page+1}</em>, Total: <em>{$count|number_format}</em>
		</div>
		<div class="col-sm-3 text-right">
			{if $hasMore}
				<a href="{$org->url()}/admin/volunteers?inactive={$showInactive}&amp;approved={$showApproved}&amp;page={$page+1}" class="btn btn-default">
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
 		window.location = '{$org->url()}/admin/volunteers?inactive='+(showingInactive+0)+'&approved='+(showingApproved+0);
	});
</script>
{/block}