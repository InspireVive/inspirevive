{extends file="$viewsDir//parent.tpl"}
{block name=content}

{if $numAdded > 0 && $numVolunteers > 0}
	<p class="alert alert-success">
		Added a total of <strong>{$numAdded} {$app.locale->p($numAdded,'hour','hours')}</strong> for <strong>{$numVolunteers} {$app.locale->p($numVolunteers,'volunteer','volunteers')}</strong>.
	</p>
{/if}

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
					{if $hoursAwaitingApproval > 0}
						<span class="badge">
							{$hoursAwaitingApproval}
						</span>
					{/if}
				</a>
			</li>
		</ul>
	</div>
	<div class="col-md-5">

	</div>
	<div class="col-md-3 new-btn">
		<a href="{$org->manageUrl()}/hours/add" class="btn btn-success">
			<span class="glyphicon glyphicon-time"></span>
			Record Hours
		</a>
	</div>
</div>

{if count($hours) == 0}
	<p class="empty">
		<span class="glyphicon glyphicon-time"></span>
		None found.
		<a href="{$org->manageUrl()}/hours/add">Record hours</a>
	</p>
{else}
	<table class="table table-striped">
		<thead>
			<tr>
				<th></th>
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
	{foreach from=$hours item=hour}
		<tr>
			<td>
				<a href="{$org->manageUrl()}/hours/{$hour->id()}" class="btn btn-default">
					Details
				</a>
			</td>
			<td>
				{assign var=user value=$hour->relation('uid')}
				<a href="{$org->manageUrl()}/volunteers/{$user->id()}">
					{$user->name(true)}
				</a>
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
					<form method="post" action="{$org->manageUrl()}/hours/{$hour->id()}?redir=browse">
						<input type="hidden" name="approved" value="1" />
						<button type="submit" class="btn btn-success">
							Approve
						</button>
					</form>
				{/if}
			</td>
		</tr>
	{/foreach}
	</table>

	<!-- Pagination -->
	<div class="row">
		<div class="col-md-3">
			{if $hasLess}
				<a href="{$org->manageUrl()}/hours?approved={$showApproved}&amp;page={$page-1}" class="btn btn-default">
					<span class="glyphicon glyphicon-arrow-left"></span>
					Previous Page
				</a>
			{/if}
		</div>
		<div class="col-md-6 text-center">
			Page: <em>{$page+1}</em>, Total Hours: <em>{$count|number_format}</em>
		</div>
		<div class="col-md-3 text-right">
			{if $hasMore}
				<a href="{$org->manageUrl()}/hours?approved={$showApproved}&amp;page={$page+1}" class="btn btn-default">
					Next Page
					<span class="glyphicon glyphicon-arrow-right"></span>
				</a>
			{/if}
		</div>
	</div>
{/if}

{/block}