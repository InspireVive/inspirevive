{extends file="$viewsDir//parent.tpl"}
{block name=content}

<div class="row">
	<div class="col-lg-8">
		<p>
		Please verify that the hours below have been entered correctly.
		</p>
	</div>
</div>

<form action="{$org->manageUrl()}/hours/add/confirm?place={$place->id()}" method="post" class="form-horizontal">
	<input type="hidden" name="json" value='{$json}' />

	<div class="form-group">
		<label class="control-label col-md-2">
			Place
		</label>
		<div class="col-md-4 form-control-static">
			<strong>{$place->name}</strong>
		</div>
	</div>

	<div class="form-group">
		<label class="control-label col-md-2">Tags</label>
		<div class="col-md-4 form-control-static">
			{$tags}
		</div>
	</div>

	<div class="form-group">
		<label class="control-label col-md-2">
			<strong>Total Entries</strong>
		</label>
		<div class="col-md-4 form-control-static">
			{count($input.hours)}
		</div>
	</div>

	<div class="record-hours-holder">
		<table class="table table-striped record-hours">
			<thead>
				<tr>
					<th>Username</th>
					{foreach from=$days item=day}
						<th>
							{if $day.today}
								<em>Today</em><br/>
							{else if $day.yesterday}
								<em>Yesterday</em><br/>
							{/if}
							{$day.timestamp}<br/>
							{$day.date}
						</th>
					{/foreach}
					<th class="total">Total</th>
				</tr>
			</thead>
			<tbody>
			{foreach from=$input.hours key=k item=hours}
				<tr>
					<td>
						{$input.username[$k]}
					</td>
					{foreach from=$hours item=hour}
					<td class="day">
						{$hour}
					</td>
					{/foreach}
					<td class="total">
						{$totals[$k]}
					</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>

	<div class="form-group">
		<div class="col-md-4 col-md-offset-2">
			<button type="submit" class="btn btn-default btn-lg" name="edit" value="t">Edit</button>
			<button type="submit" class="btn btn-success btn-lg" name="confirm" value="t">Confirm</button>
		</div>
	</div>
</form>

<script type="text/javascript">
$(function() {
	$('.day .form-control').keyup(function() {

		var row = $(this).parents('tr');

		var total = 0;
		$('.form-control', row).each(function() {
			var num = Math.floor(parseInt($(this).val()));
			if (isNaN(num) || num < 0) num = 0;
			total += num;
		});

		$('.total', row).html(total);
	});
});
</script>

{/block}