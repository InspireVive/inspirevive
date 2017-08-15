{extends file="parent.tpl"}
{block name=bodyClass}noBg{/block}
{block name=content}

<h2>
	Welcome<span class="hidden-xs"> to InspireVive</span>, {$app.user->name(true)}!
</h2>
<hr/>

{if !$app.user->isVerified(false)}
	<div class="alert alert-danger">
		Your account has not been verified yet. Please check your email for instructions to verify your account.
		<a href="/users/resendVerification">Resend verification email</a>
	</div>
{/if}

<div class="profile-accordion {if $completedApplication}green{else}dark{/if}">
	<h3>
		Volunteer Application
		<div class="pull-right">
			<a class="btn btn-success" href="/volunteers/application">
				{if $completedApplication}
					Edit
				{else}
					Complete
				{/if}
			</a>
		</div>
	</h3>
	{if !$completedApplication}
		<p class="description">
			Before you are able to volunteer you must complete the volunteer application.
		</p>
	{/if}
</div>

{foreach from=$volunteersAt item=volunteer}
	{assign var=appShared value=$volunteer->application_shared}
	{assign var=org value=$volunteer->relation('organization')}
	<div class="profile-accordion {if $appShared}green{else}red{/if}">
		<h3>
			<a href="{$org->url()}">
				{$org->name}
			</a>
			<div class="pull-right">
				{if !$appShared}
					<form method="post" action="{$org->url()}/volunteers?redir=profile">
			            {$app.csrf->render($req) nofilter}
						<input type="hidden" name="application_shared" value="1" />
						<button type="submit" class="btn btn-inverse">
							Grant Access
						</button>
					</form>
				{/if}
			</div>
		</h3>
		{if $appShared}
			<form method="post" action="{$org->url()}/volunteers?redir=profile">
                {$app.csrf->render($req) nofilter}
				<input type="hidden" name="application_shared" value="0" />
				<p class="description">
					{if $org->getRoleOfUser($app.user) == $smarty.const.ORGANIZATION_ROLE_ADMIN}
						<a class="btn btn-link" href="{$org->manageUrl()}">
							<span class="glyphicon glyphicon-cog"></span>
							Manage
						</a>
						&middot;
					{/if}
					<a class="btn btn-link" href="{$org->url()}">
						<span class="glyphicon glyphicon-home"></span>
						Volunteer Hub
					</a>
					&middot;
					<a href="{$org->url()}/hours/report" class="btn btn-link">
						<span class="glyphicon glyphicon-time"></span>
						Report volunteer hours
					</a>
				</p>
			</form>
		{else}
			<p class="description">
				Before you are able to join as a volunteer you must grant {$org->name} access to your volunteer application.<br/>
				<form method="post" action="{$org->url()}/volunteers?redir=profile">
                    {$app.csrf->render($req) nofilter}
					<input type="hidden" name="method" value="DELETE" />
					<p class="description">
						<button class="btn btn-link" type="submit">
							<span class="glyphicon glyphicon-remove"></span>
							I no longer volunteer here
						</button>
					</p>
				</form>
			</p>
		{/if}
	</div>
{/foreach}

<br/>

<h3>
	Volunteer Hours
</h3>
<hr/>
<div class="volunteer-hours clearfix">
	{foreach from=$recentVolunteerHours item=hour name=hours}
		{if $smarty.foreach.hours.index == 4 && !$smarty.foreach.hours.last}
			<div class="see-more-toggle">
				<button type="button" class="btn btn-more btn-block collapsed" data-toggle="collapse" href="#more-volunteer-hours">
					View More
				</button>
			</div>
			<div class="panel-collapse collapse" id="more-volunteer-hours">
		{/if}
		{if $smarty.foreach.hours.index % 4 == 0}
			<div class="row">
		{/if}
			<div class="col-sm-3">
				<div class="volunteer-hour {if $hour->approved}approved{/if}">
					<div class="status"></div>
					<div class="place">
						<div class="title">
							{assign var=place value=$hour->place()}
							{if $place}
								{$place->name}
                            {/if}
						</div>
					</div>
					<div class="details">
						<div class="detail highlight">
							<div class="main">
								{$hour->hours}
							</div>
							<div class="title">
								{$app.locale->p($hour->hours,'hour','hours')}
							</div>
						</div>
						<div class="detail">
							<div class="main">
								{$hour->timestamp|date_format:'M j'}
							</div>
							<div class="title">
								{$hour->timestamp|date_format:'Y'}
							</div>
						</div>
						<div class="tags">
							{foreach from=$hour->tags() item=tag}
								<span class="label label-primary">{$tag}</span>
							{/foreach}
						</div>
					</div>
					<div class="organization">
						For {$hour->relation('organization')->name}
					</div>
				</div>
			</div>
		{if $smarty.foreach.hours.index % 4 == 3 || $smarty.foreach.hours.last % 3}
			</div>
			{if $smarty.foreach.hours.index > 4 && $smarty.foreach.hours.last}
				</div>
			{/if}
		{/if}
	{foreachelse}
		<p class="empty">
			You do not have any recent reported volunteer hours
		</p>
	{/foreach}
</div>

<div class="row">
	<div class="col-sm-6">
		<p>
			<strong>{$app.user->volunteer_hours}</strong>
			volunteer
			{$app.locale->p($app.user->volunteer_hours,'hour','hours')} all time
		</p>
	</div>
	<div class="col-sm-6 text-right">
		<p>
			<em>showing last 180 days</em>
		</p>
		<p>
			<button type="button" class="btn btn-success"></button>
			Approved&nbsp;&nbsp;&nbsp;
			<button type="button" class="btn btn-danger"></button>
			Awaiting Approval
		</p>
	</div>
</div>
<br/>

{/block}