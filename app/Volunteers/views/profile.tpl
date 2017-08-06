{extends file="parent.tpl"}
{block name=meta}
    <meta name="description" content="Online hub for {$org.name} volunteers to coordinate efforts." />

    <meta property="og:title" content="{$org.name}" />
    <meta property="og:type" content="website" />
    <meta property="og:description" content="Online hub for {$org.name} volunteers to coordinate efforts." />
    <meta property="og:image" content="{$app.view_engine->asset_url('/img/inspirevive-icon-512.jpg')}" />
    <meta property="og:url" content="{$orgObj->url()}" />
    <meta property="og:site_name" content="InspireVive" />

    <meta name="twitter:card" content="summary" />
    <meta name="twitter:url" content="{$orgObj->url()}" />
    <meta name="twitter:title" content="{$org.name} on InspireVive" />
    <meta name="twitter:description" content="Online community for {$org.name} volunteers to coordinate their volunteer efforts." />
    <meta name="twitter:image" content="{$app.view_engine->asset_url('/img/inspirevive-icon-512.jpg')}" />
{/block}
{block name=content}

<div class="organization-profile">
	{if $isVolunteer}
		<div class="profile-toolbar">
			<div class="btn-group">
				<a href="{$orgObj->url()}/hours/report" class="btn btn-primary">
					<span class="glyphicon glyphicon-hour"></span>
					Report Hours
				</a>
				{if $isVolunteerCoordinator}
					<a href="{$orgObj->manageUrl()}" class="btn btn-default">
						<span class="glyphicon glyphicon-cog"></span>
						Manage
					</a>
				{/if}
			</div>
		</div>
		<h1 class="profile-title">
			{$org.name}
			<small class="hidden-xs">
				Volunteer Hub
			</small>
		</h1>

		<div class="kv-fields">
			<div class="field">
				<div class="title">
					Your Position
				</div>
				<div class="value">
					{if $isVolunteerCoordinator}
						<span class="label label-success">
							Volunteer Coordinator
						</span>
					{else}
						<span class="label label-primary">
							Volunteer
						</span>
					{/if}
				</div>
			</div>

			<div class="field">
				<div class="title">
					Volunteer Coordinator
				</div>
				<div class="value">
					<a href="mailto:{$org.email}">{$org.email}</a>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<h3>Most Active Volunteers</h3>
				<hr/>
				{if count($topVolunteers) > 0}
					<div class="clearfix volunteers">
					{foreach from=$topVolunteers item=volunteer}
						<div class="volunteer pull-left">
							<a href="{$volunteer.user->url()}">
								<img src="{$volunteer.user->profilePicture()}" alt="Profile Picture" class="img-circle" width="80" height="80" /><br/>
								{$volunteer.user->name(true)}
							</a>
							<div class="hours">
								{$volunteer.hours|number_format} {$app.locale->p($volunteer.hours,'hour','hours')}
							</div>
						</div>
					{/foreach}
					</div>
				{else}
					<p class="empty">
						<em>
							The most active volunteers have not emerged yet. This could be you!
						</em>
					</p>
				{/if}
				<br/>
			</div>
			<div class="col-md-6">
				<h3>Volunteer Statistics</h3>
				<hr/>
				<table class="table table-striped">
					<thead>
						<tr>
							<th>Time Period</th>
							<th>Volunteer Hours</th>
						</tr>
					</thead>
					<tbody>
					{foreach from=$periods item=period}
						<tr>
							<td>{$period.title}</td>
							<td>{$period.hoursVolunteered|number_format}</td>
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>
		</div>

		<hr/>
	    <div id="disqus_thread"></div>
	    <script type="text/javascript">
	        /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
	        var disqus_shortname = 'inspirevive'; // required: replace example with your forum shortname

	        /* * * DON'T EDIT BELOW THIS LINE * * */
	        (function() {
	            var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
	            dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
	            (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
	        })();
	    </script>
	    <noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
	    <a href="http://disqus.com" class="dsq-brlink">comments powered by <span class="logo-disqus">Disqus</span></a>
	{else}
		<h1 class="profile-title">
			{$org.name}
			<small class="hidden-xs">
				Volunteer Hub
			</small>
		</h1>

		{if $awaitingApproval}
			<p class="text-center alert alert-info">
				We are currently waiting for the volunteer coordinator at {$org.name} to approve your request. When they approve your request we will send you an email. If it is taking too long then we recommend reaching out to them.
		{else}
			<p class="text-center alert alert-info">
				<span>
					Please request to join in order to access the Volunteer Hub for {$org.name}. {if !$app.user->isSignedIn()}If you are already a member, then you must be signed in to InspireVive.{/if}
				</span>
			</p>

			<form method="post" action="{$orgObj->url()}/volunteers" class="form-horizontal">
			    {$app.csrf->render($req) nofilter}
				<input type="hidden" name="application_shared" value="1" />
				<div class="form-group">
					<div class="col-md-4 col-md-offset-{if $app.user->isSignedIn()}4{else}2{/if}">
						<button type="submit" class="btn btn-primary btn-lg btn-block">
							Request to Join
						</button>
					</div>
					{if !$app.user->isSignedIn()}
						<div class="col-md-4">
							<a href="/login?redir={$orgObj->url()}" class="btn btn-default btn-lg btn-block">
								Sign in to InspireVive
							</a>
						</div>
					{/if}
				</div>
			</form>
			<br/>
		{/if}
	{/if}

    <hr/>
	<h4>About this page</h4>
	<p>
		{$org.name} uses InspireVive to coordinate with their volunteers, plan events, and keep track of volunteer activity. This page is the online hub for volunteers to stay up to date on the latest volunteer activities and report volunteer hours.
	</p>

	<p>
		InspireVive was created in order to give people the opportunity to spread the love. We want to introduce people to the pleasure that come from helping others. At InspireVive we believe that the purpose of life is to love others. We accomplish our mission by empowering charities and volunteer organizations with software to help them meet their goals, motivating individuals to volunteer through our online contests, and connecting individuals to the best volunteer opportunities.
	</p>
</div>

{/block}