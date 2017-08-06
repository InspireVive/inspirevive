{extends file="$viewsDir//parent.tpl"}
{block name=content}

{foreach from=$app.errors->messages() item=error}
	<p class="alert alert-danger">{$error}</p>
{/foreach}

<form method="post" action="{$org->manageUrl()}/places{if isset($place.id)}/{$place.id}{/if}" role="form" class="form-horizontal">
    {$app.csrf->render($req) nofilter}
	<div class="form-group">
		<div class="col-md-8 col-md-offset-4">
			{if isset($place.id)}
				<h4>Edit Place</h4>
			{else}
				<h4>New Place</h4>
			{/if}
		</div>
	</div>

	<div class="form-group">
		<label class="control-label col-md-4">
			Name
		</label>
		<div class="controls col-md-4">
			<input class="form-control" name="name" value="{$place.name}" />
		</div>
	</div>

	<div class="form-group">
		<label class="control-label col-md-4">
			Address
		</label>
		<div class="controls col-md-4">
			<textarea class="form-control" name="address">{$place.address}</textarea>
		</div>
	</div>

	<div class="form-group">
		<label class="control-label col-md-4">
			Delegate the approval of hours self-reported here to another volunteer coordinator?
		</label>
		<div class="controls col-md-2 form-control-static">
			<input type="hidden" name="place_type" id="external-place-input" value="{$place.place_type}" />
			<input type="checkbox" id="external-place-toggle" {if $place.place_type == $smarty.const.VOLUNTEER_PLACE_EXTERNAL}checked="checked"{/if} value="{$smarty.const.VOLUNTEER_PLACE_EXTERNAL}" />
			<label class="inline" for="external-place-toggle">Yes</label>
		</div>
	</div>

	<div class="place-external {if $place.place_type != $smarty.const.VOLUNTEER_PLACE_EXTERNAL}hidden{/if}">
		<div class="form-group">
			<label class="control-label col-md-4">
				Volunteer Coordinator's Name
			</label>
			<div class="col-md-3">
				<input type="text" name="verify_name" class="form-control" value="{if isset($place.verify_name)}{$place.verify_name}{/if}">
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-md-4">
				Volunteer Coordinator's Email Address
			</label>
			<div class="col-md-4">
				<input type="text" name="verify_email" class="form-control" value="{if isset($place.verify_email)}{$place.verify_email}{/if}">
			</div>
		</div>
	</div>

	<div class="form-group">
		<div class="controls col-md-4 col-md-offset-4">
			<input type="submit" class="btn btn-primary" value="{if isset($place.id)}Save Place{else}Add Place{/if}" />
		</div>
	</div>
</form>

<script type="text/javascript">
	$(function() {
		$('#external-place-toggle').change(function() {
			var external = $(this).is(':checked');
			if (external)
				$('.place-external').removeClass('hidden');
			else
				$('.place-external').addClass('hidden');

			$('#external-place-input').val((external)?{$smarty.const.VOLUNTEER_PLACE_EXTERNAL}:{$smarty.const.VOLUNTEER_PLACE_INTERNAL});
		});
	});
</script>
{/block}