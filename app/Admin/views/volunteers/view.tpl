{extends file="$viewsDir/parent.tpl"}
{block name=content}

<div class="object-view">
	<div class="object-title">
		<div class="actions">
			<a href="{$org->manageUrl()}/hours/add?user={$volunteer.uid}" class="btn btn-default">
				<span class="glyphicon glyphicon-time"></span>
				Record Hours
			</a>

            {if $volunteer.role == $smarty.const.ORGANIZATION_ROLE_ADMIN && $volunteer.uid != $app.user->id()}
				<form method="post" action="{$org->manageUrl()}/volunteers/{$volunteer.uid}">
                    {$app.csrf->render($req) nofilter}
					<input type="hidden" name="role" value="{$smarty.const.ORGANIZATION_ROLE_VOLUNTEER}" />
					<button type="submit" class="btn btn-danger">
						Demote to Volunteer
					</button>
				</form>
            {/if}

            {if $volunteer.role == $smarty.const.ORGANIZATION_ROLE_VOLUNTEER}
				<form method="post" action="{$org->manageUrl()}/volunteers/{$volunteer.uid}">
                    {$app.csrf->render($req) nofilter}
					<input type="hidden" name="role" value="{$smarty.const.ORGANIZATION_ROLE_ADMIN}" />
					<button type="submit" class="btn btn-success">
						Promote to Volunteer Coordinator
					</button>
				</form>
            {/if}

			<form method="post" action="{$org->manageUrl()}/volunteers/{$volunteer.uid}">
				{$app.csrf->render($req) nofilter}
				<input type="hidden" name="active" value="{if $volunteer.active}0{else}1{/if}" />
				<button type="submit" class="btn {if $volunteer.active}btn-danger{else}btn-success{/if}">
					{if $volunteer.active}Make Inactive{else}Make Active{/if}
				</button>
			</form>

            {if $volunteer.uid != $app.user->id()}
				<form method="post" action="{$org->manageUrl()}/volunteers/{$volunteer.uid}">
                    {$app.csrf->render($req) nofilter}
					<input type="hidden" name="method" value="DELETE" />
					<button type="submit" class="btn btn-danger">
						Remove Volunteer
					</button>
				</form>
            {/if}
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
					<label class="title">Username</label>
					<div class="value">
						{if $user.username}
							<a href="/users/{$user.username}" target="_blank">
								{$name}
							</a>
						{else}
							{$name}
						{/if}
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

            {if $volunteer.metadata}
				<h3>Metadata</h3>
                {foreach from=$volunteer.metadata key=field item=value}
					<div class="section">
						<label class="title">{$field}</label>
						<div class="value">
							{$value}
						</div>
					</div>
				{/foreach}
            {/if}
		</div>
		<div class="right-col">
			<!-- Action Items -->
            {if !$volunteer.application_shared}
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

			<h4>Volunteer Activity</h4>

			<!-- Volunteer Activity -->
            {if count($hours) == 0}
				<p class="empty">
					<span class="glyphicon glyphicon-time"></span>
					No volunteer hours have been recorded yet for {$name}!
					<a href="{$org->manageUrl()}/hours/add?user={$volunteer.uid}">Record hours</a>
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
                                            {$app.csrf->render($req) nofilter}
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
		</div>
	</div>
</div>

{/block}