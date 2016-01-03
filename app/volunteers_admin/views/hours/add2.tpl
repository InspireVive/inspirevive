{extends file="$viewsDir//parent.tpl"}
{block name=content}

<div class="top-nav">
	<div class="row">
		<div class="col-sm-3">
			<a href="{$org->url()}/admin/hours" class="btn btn-link btn-block">
				&larr; Cancel
			</a>
		</div>
	</div>
</div>

<h4>Add Volunteer Hours <small>step 2 of 3</small></h4>

<div class="row">
	<div class="col-md-8">
		<p>
			Please enter the hours for each volunteer according to the day they were performed. Only whole numbers are allowed. Anything else will be rounded down.
		</p>
	</div>
</div>
<br/>

{foreach from=$app.errors->messages() item=error}
	<p class="alert alert-danger">{$error}</p>
{/foreach}

{if count($volunteers) == 0}
	<p class="empty">
		No volunteers found. <a href="{$org->url()}/admin/volunteers/add">Add one</a>
	</p>
{else}
	<form action="{$org->url()}/admin/hours/add?place={$place->id()}" method="post" class="form-horizontal">
		<div class="form-group">
			<label class="control-label col-md-2">
				Place
			</label>
			<div class="col-md-4 form-control-static">
				<strong>{$place->name}</strong>
				(<a href="{$org->url()}/admin/hours/add">change</a>)
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-md-2">Tags</label>
			<div class="col-md-4">
				<input name="tags" id="tags" class="form-control" value="{$tags}" data-autocomplete="{$availableTags|implode:' '}" /><br/>
				Recent Tags: {$availableTags|implode:' '}
			</div>
			<div class="col-md-4">
				Tags are delimited by spaces. Letters, numbers, and dashes (-) only are allowed.
			</div>
		</div>

		<div class="record-hours-holder">
			<table class="table table-striped record-hours">
				<thead>
					<tr>
						<th>Name</th>
					{foreach from=$days item=day}
						<th>
							{if $day.today}
								<em>Today</em><br/>
							{else if $day.yesterday}
								<em>Yesterday</em><br/>
							{/if}
							{$day.timestamp}<br/>
							{$day.date}
							<input type="hidden" name="days[]" value="{$day.date}" />
						</th>
					{/foreach}
						<th>
							<a href="#" class="add-day btn btn-default btn-block">
								<span class="glyphicon glyphicon-plus"></span>
								Day
							</a>
						</th>
						<th class="total">Total</th>
					</tr>
				</thead>
				<tbody>
				{if count($volunteers) > 0}
					{foreach from=$volunteers item=volunteer}
						{assign var=user value=$volunteer->relation('uid')}
						<tr data-uid="{$user->id()}">
							<td>
								{$user->name(true)}<br/>
								<em>{$user->volunteerApplication()->fullName()}</em>
								<input type="hidden" name="username[{$user->id()}]" value="{$user->name(true)}" />
							</td>
						{foreach from=$days key=k item=day}
							<td class="day">
								<input tpye="text" name="hours[{$user->id()}][]" class="form-control" value="{if isset($input.hours[$user->id()][$k])}{$input.hours[$user->id()][$k]}{/if}" />
							</td>
						{/foreach}
							<td class="add-day-holder"></td>
							<td class="total">
								0
							</td>
						</tr>
					{/foreach}
				{/if}
				</tbody>
			</table>
		</div>

		<div class="form-group">
			<div class="col-md-4 col-md-offset-2">
				<button type="submit" class="btn btn-primary btn-lg">Next &rarr;</button>
			</div>
		</div>
	</form>
{/if}

<script type="text/javascript">
$(function() {
	function calcTotal (row) {
		var total = 0;
		$('.form-control', row).each(function() {
			var num = Math.floor(parseInt($(this).val()));
			if (isNaN(num) || num < 0) num = 0;
			total += num;
		});

		$('.total', row).html(total);
	}

	function recalcEverything()
	{
		$('.record-hours-holder tbody tr').each(function() {
			calcTotal(this);
		});
	}

	$('.record-hours-holder').delegate('.day .form-control','keyup',function() {
		calcTotal($(this).parents('tr'));
	});

	$('.record-hours-holder').delegate('.remove-day','click',function(e) {
		e.preventDefault();

		var index = $('th', $(this).parents('tr')).index($(this).parent()) + 1;

		$('td:nth-child(' + index + ')', $(this).parents('table')).remove();
		$(this).parent().remove();

		recalcEverything();

		return false;
	});

	$('.add-day').click(function() {
		$(this).parent().before('<th><input type="text" class="form-control datepicker" name="days[]" placeholder="Click..." /></th>');

		$('.record-hours-holder tbody tr').each(function() {
			var uid = $(this).data('uid');
			$('.add-day-holder', this).before('<td class="day"><input tpye="text" name="hours[' + uid + '][]" class="form-control" /></td>');
		});

		$('.datepicker').datepicker({
			dateFormat: 'M d, yy',
			maxDate: '+0 D'
		});

		recalcEverything();

		return false;
	});

	// initialization
	var tagsAutocomplete = $('#tags').data('autocomplete').split(' '); // not used

	$('#tags').tagsInput({
		delimiter: ' ',
		height: 'auto',
		width: 'auto',
		inputPadding: 0
	});

	recalcEverything();
});
</script>

{/block}