{extends file="$viewsDir//parent.tpl"}
{block name=content}

<div class="top-nav">
	<div class="row">
		<div class="col-md-8">
			<h3>
				{$name}
			</h3>
		</div>
		<div class="col-md-4">
		{if $volunteer.uid != $app.user->id()}
			<form method="post" action="{$org->manageUrl()}/volunteers/{$volunteer.uid}">
				<input type="hidden" name="method" value="DELETE" />
				<button type="submit" class="btn btn-danger pull-right">
					Remove Volunteer
				</button>
			</form>
		{/if}
		</div>
	</div>
</div>

{if !$volunteer.application_shared}
		<p class="alert alert-warning">
			{$name} has not shared the volunteer application with you yet. They must agree to share the application before we show it here.
		</p>
{else}	
	<div class="row">
		<div class="col-md-3 text-right">
			<p class="text-right">
				<strong>Username</strong>
			</p>
		</div>
		<div class="col-md-6">
			<p>
				{if $user.username}
					<a href="/users/{$user.username}" target="_blank">
						{$name}
					</a>
				{else}
					{$name}
				{/if}
			</p>
		</div>
	</div>

	<div class="row">
		<div class="col-md-3">
			<p class="text-right">
				<strong>Email</strong>
			</p>
		</div>
		<div class="col-md-6">
			<p>
				<a href="mailto:{$user.email}">
					{$user.email}
				</a>
			</p>
		</div>
	</div>

	{if !$completed}
		<p class="alert alert-warning">
			{$name} has not filled out the volunteer application yet. Once they complete the application we will show it here.
		</p>
	{else}
		<div class="row">
			<div class="col-md-3 text-right">
				<p class="text-right">
					<strong>Address</strong>
				</p>
			</div>
			<div class="col-md-6">
				<p>
					{$application.address}<br/>
					{$application.city}, {$application.state} {$application.zip_code}
				</p>
			</div>
		</div>

		<div class="row">
			<div class="col-md-3 text-right">
				<p class="text-right">
					<strong>Phone #</strong>
				</p>
			</div>
			<div class="col-md-2">
				<p>
					{$application.phone}
				</p>
			</div>
		</div>

		{if $application.alternate_phone}
			<div class="row">
				<div class="col-md-3 text-right">
					<p class="text-right">
						<strong>Alternate Phone #</strong>
					</p>
				</div>
				<div class="col-md-6">
					<p>
						{$application.alternate_phone}
					</p>
				</div>
			</div>
		{/if}

		<div class="row">
			<div class="col-md-3 text-right">
				<p class="text-right">
					<strong>Age</strong>
				</p>
			</div>
			<div class="col-md-6">
				<p>
					{$application.age}
				</p>
			</div>
		</div>
	{/if}
{/if}

{if $volunteer.role == $smarty.const.ORGANIZATION_ROLE_AWAITING_APPROVAL}
	<div class="row">
		<div class="col-md-3 text-right">
			<p class="text-right">
				<strong>Role</strong>
			</p>
		</div>
		<div class="col-md-6">
			<p>
				<span class="label label-danger">Awaiting Approval</span>
			</p>
			<form method="post" action="{$org->manageUrl()}/volunteers/{$volunteer.uid}">
				<input type="hidden" name="role" value="{$smarty.const.ORGANIZATION_ROLE_VOLUNTEER}" />
				<button type="submit" class="btn btn-success">
					Approve
				</button>
			</form>
		</div>
	</div>
{else}
	<div class="row">
		<div class="col-md-3 text-right">
			<p class="text-right">
				<strong>Role</strong>
			</p>
		</div>
		<div class="col-md-6">
			<p>
				{if $volunteer.role == $smarty.const.ORGANIZATION_ROLE_ADMIN}
					<span class="label label-success">Volunteer Coordinator</span>
					{if $volunteer.uid != $app.user->id()}
						<form method="post" action="{$org->manageUrl()}/volunteers/{$volunteer.uid}">
							<input type="hidden" name="role" value="{$smarty.const.ORGANIZATION_ROLE_VOLUNTEER}" />
							<button type="submit" class="btn btn-danger">
								Demote to Volunteer
							</button>
						</form>
					{/if}
				{else}
					<span class="label label-primary">Volunteer</span>
					<form method="post" action="{$org->manageUrl()}/volunteers/{$volunteer.uid}">
						<input type="hidden" name="role" value="{$smarty.const.ORGANIZATION_ROLE_ADMIN}" />
						<button type="submit" class="btn btn-success">
							Promote to Volunteer Coordinator
						</button>
					</form>
				{/if}
			</p>
		</div>
	</div>

	<div class="row">
		<div class="col-md-3 text-right">
			<p class="text-right">
				<strong>Status</strong>
			</p>
		</div>
		<div class="col-md-6">
			<p>
				{if $volunteer.active}
					<span class="label label-success">Active</span>
				{else}
					<span class="label label-default">Inactive</span>
			{/if}
			</p>
			<form method="post" action="{$org->manageUrl()}/volunteers/{$volunteer.uid}">
				<input type="hidden" name="active" value="{if $volunteer.active}0{else}1{/if}" />
				<button type="submit" class="btn {if $volunteer.active}btn-danger{else}btn-success{/if}">
					{if $volunteer.active}Make Inactive{else}Make Active{/if}
				</button>
			</form>
		</div>
	</div>
{/if}

{if $volunteer.metadata}
	<div class="row">
		<div class="col-md-3 text-right">
			<p class="text-right">
				<strong>Metadata</strong>
			</p>
		</div>
		<div class="col-md-9">
			<p>
				{foreach from=$volunteer.metadata key=field item=value}
					<strong>{$field}</strong> = {$value}<br/>
				{/foreach}
			</p>
		</div>
	</div>
{/if}
<br/>

<h4>Volunteer Activity</h4>

{if count($hours) == 0}
	<p class="empty">
		<span class="glyphicon glyphicon-time"></span>
		No volunteer hours have been recorded yet for {$name}!
		<a href="{$org->manageUrl()}/hours/add">Record hours</a>
	</p>
{else}
	<br/>
	<div class="volunteer-activity">
		<table class="table table-striped">
			<thead>
				<tr>
					<th></th>
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
						<form method="post" action="{$org->manageUrl()}/hours/{$hour->id()}?redir=volunteers,{$volunteer.uid}">
							<input type="hidden" name="approved" value="1" />
							<button type="submit" class="btn btn-success">
								Approve
							</button>
						</form>
					{/if}
				</td>
			</tr>
		{foreachelse}
			<tr>
				<td colspan="3">
					<em>No volunteer activity yet.</em>
				</td>
			</tr>
		{/foreach}
		</table>
	</div>
{/if}

{/block}