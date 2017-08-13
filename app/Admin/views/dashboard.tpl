{extends file="$viewsDir/parent.tpl"}
{block name=content}

<h1>Welcome, {$org->name}!</h1>

<div class="btn-toolbar dashboard hidden-xs">
	<a href="{$org->manageUrl()}/volunteers/add" class="btn btn-link">
		<span class="glyphicon glyphicon-user"></span>
		Add Volunteers
	</a>
	<a href="{$org->manageUrl()}/places/add" class="btn btn-link">
		<span class="glyphicon glyphicon-map-marker"></span>
		New Volunteer Place
	</a>
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
			<div class="number">{$period.volunteers|number_format}</div>
			<div class="title">
				{if $period.title != 'All Time'}New {/if}{$app.locale->p($period.volunteers,'Volunteer','Volunteers')}
			</div>
		</div>
		<div class="col-md-4 stat">
			{if $period.topVolunteer}
				<div class="name">
					<a href="{$org->manageUrl()}/volunteers/{$period.topVolunteer.user->id()}">
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