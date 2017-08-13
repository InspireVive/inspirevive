{extends file="$viewsDir/parent.tpl"}
{block name=content}

<div class="object-view">
	<div class="object-title">
		<div class="actions">
			<div class="dropdown">
				<button type="button" class="btn btn-link btn-lg" data-toggle="dropdown">
					Options
					<span class="ion-chevron-down"></span>
				</button>
				<ul class="dropdown-menu dropdown-menu-right">
					<li>
						<a href="{$org->manageUrl()}/hours/add?user={$volunteer.uid}">
							Record Hours
						</a>
					</li>

                    {if $volunteer.role == $smarty.const.ORGANIZATION_ROLE_ADMIN && $volunteer.uid != $app.user->id()}
						<li>
							<a href="#" class="demote-to-volunteer">
								Demote to Volunteer
							</a>
						</li>
                    {/if}

                    {if $volunteer.role == $smarty.const.ORGANIZATION_ROLE_VOLUNTEER}
						<li>
							<a href="#" class="promote-to-admin">
								Promote to Volunteer Coordinator
							</a>
						</li>
                    {/if}

					<li>
						<a href="#" class="mark-active">
							Mark {if $volunteer.active}Inactive{else}Active{/if}
						</a>
					</li>

                    {if $volunteer.uid != $app.user->id()}
						<li class="divider"></li>
						<li class="danger">
							<a href="#" class="delete-volunteer">
								Remove Volunteer
							</a>
						</li>
                    {/if}
				</ul>
			</div>
		</div>

		<h1>
			{$name}
		</h1>
	</div>

	<div class="two-column clearfix">
		<div class="left-col details-list">
			<h3>Details</h3>
            {if $volunteer.application_shared}
				<div class="section">
					<label class="title">Name</label>
					<div class="value">
						{$name}
					</div>
				</div>

				<div class="section">
					<label class="title">Email</label>
					<div class="value">
						<a href="mailto:{$user.email}">
							{$user.email}
						</a>
					</div>
				</div>

                {if $completed}
					<div class="section">
						<label class="title">Address</label>
						<div class="value">
							{$application.address}<br/>
							{$application.city}, {$application.state} {$application.zip_code}
						</div>
					</div>

					<div class="section">
						<label class="title">Phone #</label>
						<div class="value">
							{$application.phone}
						</div>
					</div>

                    {if $application.alternate_phone}
						<div class="section">
							<label class="title">Alternate Phone #</label>
							<div class="value">
								{$application.alternate_phone}
							</div>
						</div>
                    {/if}

					<div class="section">
						<label class="title">Age</label>
						<div class="value">
							{$application.age}
						</div>
					</div>
                {/if}
            {/if}

			<div class="section">
				<label class="title">Role</label>
				<div class="value">
		            {if $volunteer.role == $smarty.const.ORGANIZATION_ROLE_AWAITING_APPROVAL}
						<span class="text-warning">Pending Approval</span>
		            {elseif $volunteer.role == $smarty.const.ORGANIZATION_ROLE_ADMIN}
						<span class="text-success">Volunteer Coordinator</span>
					{else}
						Volunteer
					{/if}
				</div>
			</div>

			<div class="section">
				<label class="title">Status</label>
				<div class="value">
					{if $volunteer.active}
						<span class="label label-success">Active</span>
					{else}
						<span class="label label-default">Inactive</span>
					{/if}
				</div>
			</div>

			{if $user.username}
				<div class="section">
					<label class="title">Username</label>
					<div class="value">
						<a href="/users/{$user.username}" target="_blank">
							{$name}
						</a>
					</div>
				</div>
            {/if}

            {if $metadata}
				<h3>Metadata</h3>
                {foreach from=$metadata item=row}
					<div class="section">
						<label class="title">{$row.title}</label>
						<div class="value">
							{$row.value}
						</div>
					</div>
				{/foreach}
            {/if}
		</div>
		<div class="right-col">
			<!-- Action Items -->
			{if $user->isTemporary()}
				<div class="action-item warning">
					<div class="title">Not registered</div>
					<p>
						<span class="glyphicon glyphicon-exclamation-sign"></span>
                        {$name} has not registered on InspireVive yet.
					</p>
				</div>
            {elseif !$volunteer.application_shared}
				<div class="action-item warning">
					<div class="title">Volunteer application not shared</div>
					<p>
						<span class="glyphicon glyphicon-exclamation-sign"></span>
                        {$name} has not shared the volunteer application with you yet. They must agree to share the application before we show it here.
					</p>
				</div>
            {elseif !$completed}
				<div class="action-item warning">
					<div class="title">Missing volunteer application</div>
					<p>
						<span class="glyphicon glyphicon-exclamation-sign"></span>
                        {$name} has not filled out the volunteer application yet. Once they complete the application we will show it here.
					</p>
				</div>
            {/if}

            {if $volunteer.role == $smarty.const.ORGANIZATION_ROLE_AWAITING_APPROVAL}
				<div class="action-item">
					<div class="title">Approve this volunteer?</div>
					<p>This volunteer has requested to join your organization.</p>
					<div class="actions">
						<form method="post" action="{$org->manageUrl()}/volunteers/{$volunteer.uid}">
							{$app.csrf->render($req) nofilter}
							<input type="hidden" name="role" value="{$smarty.const.ORGANIZATION_ROLE_VOLUNTEER}" />
							<button type="submit" class="btn btn-success">
								Approve
							</button>
						</form>
					</div>
				</div>
			{/if}

			<!-- Recent Volunteer Activity -->
			<h3>Recent Volunteer Activity</h3>
			{if count($hours) == 0}
				<p class="empty">
					<span class="glyphicon glyphicon-time"></span>
					No volunteer hours have been recorded yet for {$name}!
					<a href="{$org->manageUrl()}/hours/add?user={$volunteer.uid}">Record hours</a>
				</p>
            {else}
				<div class="volunteer-activity">
					<table class="table table-striped">
						<thead>
							<tr>
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
						<tbody>
                        {foreach from=$hours item=hour}
							<tr class="clickable" onclick="window.location='{$org->manageUrl()}/hours/{$hour->id()}'">
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
						{foreachelse}
							<tr>
								<td colspan="4">
									<em>No volunteer activity yet.</em>
								</td>
							</tr>
                        {/foreach}
						</tbody>
						<tfoot>
							<tr>
								<td colspan="4">
								</td>
							</tr>
						</tfoot>
					</table>
				</div>
            {/if}
		</div>
	</div>
</div>

<form id="deleteVolunteerForm" method="post" action="{$org->manageUrl()}/volunteers/{$volunteer.uid}">
	{$app.csrf->render($req) nofilter}
	<input type="hidden" name="method" value="DELETE" />
</form>
<form id="markActiveForm" method="post" action="{$org->manageUrl()}/volunteers/{$volunteer.uid}">
	{$app.csrf->render($req) nofilter}
	<input type="hidden" name="active" value="{if $volunteer.active}0{else}1{/if}" />
</form>
<form id="promoteForm" method="post" action="{$org->manageUrl()}/volunteers/{$volunteer.uid}">
	{$app.csrf->render($req) nofilter}
	<input type="hidden" name="role" value="{$smarty.const.ORGANIZATION_ROLE_ADMIN}" />
</form>
<form id="demoteForm" method="post" action="{$org->manageUrl()}/volunteers/{$volunteer.uid}">
	{$app.csrf->render($req) nofilter}
	<input type="hidden" name="role" value="{$smarty.const.ORGANIZATION_ROLE_VOLUNTEER}" />
</form>

<script type="text/javascript">
	$(function() {
		$('.delete-volunteer').click(function(e) {
			e.preventDefault();
			if (window.confirm('Are you sure you want to remove this volunteer?')) {
				$('#deleteVolunteerForm').submit();
			}
		});

        $('.promote-to-admin').click(function(e) {
            e.preventDefault();
			$('#promoteForm').submit();
        });

        $('.demote-to-volunteer').click(function(e) {
            e.preventDefault();
            $('#demoteForm').submit();
        });

        $('.mark-active').click(function(e) {
            e.preventDefault();
            $('#markActiveForm').submit();
        });
	});
</script>

{/block}