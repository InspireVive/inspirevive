{extends file="parent.tpl"}
{block name=header}
<link href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" rel="stylesheet"  type="text/css" />
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
{/block}
{block name=content}

<h1 class="profile-title">
	{$org->name}
	<small>
		Report Volunteer Hours
	</small>
</h1>

<div class="row">
	<div class="col-md-8">
		<p>
			Please enter the hours for the day which they were performed. Only whole numbers are allowed and anything else will be rounded down.
		</p>
	</div>
</div>
<br/>

{foreach from=$app.errors->messages() item=error}
	<p class="alert alert-danger">{$error}</p>
{/foreach}

<form action="{$org->url()}/hours/report?place={$place->id()}" method="post" class="form-horizontal">
	{$app.csrf->render($req) nofilter}
	<div class="form-group">
		<label class="control-label col-md-2">
			Volunteer
		</label>
		<div class="col-md-4 form-control-static">
			<strong>{$app.user->name(true)}
		</div>
	</div>

	<div class="form-group">
		<label class="control-label col-md-2">
			Place
		</label>
		<div class="col-md-4 form-control-static">
			<strong>{$place->name}</strong>
			(<a href="{$org->url()}/hours/report">change</a>)
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

	<div class="form-group">
		<label class="control-label col-md-2">Day</label>
		<div class="col-md-3">
			<div class="input-group">
				<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
				<input name="timestamp" class="form-control datepicker" value="{$timestamp}" />
			</div>
		</div>
		<div class="col-md-4 col-md-offset-1 form-control-static">
			Format: Feb 20, 2014
		</div>
	</div>

	<div class="form-group">
		<label class="control-label col-md-2">Total Hours</label>
		<div class="col-md-1">
			<input name="hours" class="form-control" value="{$hours}" />
		</div>
		<div class="col-md-2 form-control-static">
			hour(s)
		</div>
		<div class="col-md-4 col-md-offset-1 form-control-static">
			Maximum of 12 hours per day
		</div>
	</div>

	<div class="form-group">
		<div class="col-md-4 col-md-offset-2">
			<button type="submit" class="btn btn-primary btn-lg">Report</button>
		</div>
	</div>
</form>

<script type="text/javascript">
$(function() {
	var tagsAutocomplete = $('#tags').data('autocomplete').split(' '); // not used

	$('#tags').tagsInput({
		delimiter: ' ',
		height: 'auto',
		width: 'auto',
		inputPadding: 0
	});

	$('.datepicker').datepicker({
		dateFormat: 'M d, yy',
		maxDate: '+0 D'
	});
});
</script>
{/block}