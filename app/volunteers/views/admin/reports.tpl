{extends file="$viewsDir/admin/parent.tpl"}
{block name=content}

<p class="lead">
	Generate reports to effectively analyze the activity and effectiveness of your volunteer base.
</p>

<form method="get" action="/api/reports/{$org->organization}" target="_blank" class="form-horizontal">
	<div class="form-group">
		<div class="col-sm-10 col-sm-offset-2">
			<h4>Report Type</h4>
		</div>
	</div>

	<div class="form-group">
		<label class="control-label col-sm-2">Pick a report</label>
		<div class="col-sm-3">
			<select class="form-control" name="type">
			{foreach from=$reports key=k item=report}
				<option value="{$report.id}">{$report.name}</option>
			{/foreach}
			</select>
		</div>
	</div>

	<div class="dates">
		<div class="form-group">
			<div class="col-sm-10 col-sm-offset-2">
				<h4>Date Range</h4>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-2">Start Date</label>
			<div class="col-sm-3">
				<div class="input-group">
					<span class="input-group-addon">
						<span class="glyphicon glyphicon-calendar"></span>
					</span>
					<input id="start" name="start" class="form-control datepicker" type="text" />
				</div>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-2">End Date</label>
			<div class="col-sm-3">
				<div class="input-group">
					<span class="input-group-addon">
						<span class="glyphicon glyphicon-calendar"></span>
					</span>
					<input id="end" name="end" class="form-control datepicker" type="text" />
				</div>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-2">Shortcuts</label>
			<div class="col-sm-10">
				<button type="button" class="btn btn-link shortcut" data-period="this_month">This month</button>
				<button type="button" class="btn btn-link shortcut" data-period="last_month">Last month</button>
				<button type="button" class="btn btn-link shortcut" data-period="this_year">This year</button>
				<button type="button" class="btn btn-link shortcut" data-period="ytd">YTD</button>
				<button type="button" class="btn btn-link shortcut" data-period="all_time">All Time</button>
			</div>
		</div>
	</div>

	<div class="form-group">
		<div class="col-sm-10 col-sm-offset-2">
			<h4>Output</h4>
		</div>
	</div>

	<div class="form-group">
		<label class="control-label col-sm-2">Output Type</label>
		<div class="col-sm-3">
			<select class="form-control" name="output">
				<option value="csv">CSV</option>
				<option value="pdf">PDF</option>
			</select>
		</div>
	</div>

	<div class="form-group">
		<div class="col-sm-3 col-sm-offset-2">
			<button type="submit" class="btn btn-lg btn-primary">
				Generate
			</button>
		</div>
	</div>
</form>

<script type="text/javascript">
	var availableReports = {$reports|json_encode};

	$('.datepicker').datepicker({
		dateFormat: 'M d, yy'
	});
	shortcut('this_month');

	function shortcut(type) {
		var start = moment().toDate();
		var end = moment().toDate();

		switch (type) {
		case 'this_month':
			start = moment().startOf('month').toDate();
			end = moment().endOf('month').toDate();
		break;
		case 'last_month':
			start = moment().subtract('months', 1).startOf('month').toDate();
			end = moment().subtract('months', 1).endOf('month').toDate();
		break;
		case 'this_year':
			start = moment().startOf('year').toDate();
			end = moment().endOf('year').toDate();
		break;
		case 'ytd':
			start = moment().subtract('year', 1).toDate();
			end = moment().endOf('day').toDate();
		break;
		case 'all_time':
			start = moment.unix({$firstHourTs}).toDate();
			end = moment().endOf('day').toDate();
		break;
		}

		$('#start').datepicker('setDate', start);
		$('#end').datepicker('setDate', end);
	}

	$('.shortcut').click(function() {
		shortcut($(this).data('period'));
		return false;
	})
</script>
{/block}