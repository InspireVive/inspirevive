{extends file="$viewsDir/admin/parent.tpl"}
{block name=content}

<h2>Welcome, {$org->name}!</h2>
<hr/>
<div class="stat-group-title">Shortcuts</div>

<div class="row">
	<div class="col-md-3">
		<p>
			<a href="{$org->url()}/admin/volunteers/add" class="btn btn-primary btn-block">
				<span class="glyphicon glyphicon-user"></span>
				Add Volunteers
			</a>
		</p>
	</div>
	<div class="col-md-3">
		<p>
			<a href="{$org->url()}/admin/hours/add" class="btn btn-danger btn-block">
				<span class="glyphicon glyphicon-time"></span>
				Input Volunteer Hours
			</a>
		</p>
	</div>
	<div class="col-md-3">
		<p>
			<a href="{$org->url()}/admin/places/add" class="btn btn-info btn-block">
				<span class="glyphicon glyphicon-map-marker"></span>
				Add Volunteer Place
			</a>
		</p>
	</div>
</div>
<br/>

{foreach from=$periods item=period}
	<div class="stat-group-title">{$period.title}</div>
	<div class="row well">
		<div class="col-md-4 stat">
			<div class="number">{$period.hoursVolunteered|number_format}</div>
			<div class="title">
				{$app.locale->p($period.hoursVolunteered,'Hour','Hours')} Volunteered
			</div>
		</div>
		<div class="col-md-4 stat">
			<div class="number">{$period.volunteers}</div>
			<div class="title">
				{if $period.title != 'All Time'}New {/if}{$app.locale->p($period.volunteers,'Volunteer','Volunteers')}
			</div>
		</div>
		<div class="col-md-4 stat">
			{if $period.topVolunteer}
				<div class="name">
					<a href="{$org->url()}/admin/volunteers/{$period.topVolunteer.user->id()}">
						{$period.topVolunteer.user->name()}</strong>
					</a>:
					{$period.topVolunteer.hours|number_format} {$app.locale->p($period.topVolunteer.hours,'hour','hours')}
				</div>
			{else}
				<div class="name">
					N/A
				</div>
			{/if}
			<div class="title">
				Top Volunteer
			</div>
		</div>
	</div>
{/foreach}

{/block}