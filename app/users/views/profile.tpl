{extends file="parent.tpl"}
{block name=content}
	<div class="profile-toolbar">
		<img src="{$userObj->profilePicture(90)}" class="img-circle user-profile-image" alt="{$user.username}" height="90" width="90" />
	</div>
	<h1 class="profile-title">
		{$user.username}
	</h1>

	<div class="row">
		<div class="col-md-8" id="profile-activity-letter">

			{if $user.fb_posts == 0 && $user.tweets == 0 && $user.volunteer_hours == 0}
				<p>
					<em>{$user.username} is not active on InspireVive yet.</em>
				</p>
			{else}
				<p>
					Since joining InspireVive {$user.username} has completed <strong>{$user.volunteer_hours} verified volunteer {$app.locale->p($user.volunteer_hours,'hour','hours')}</strong>.
				{if $user.fb_posts > 0 || $user.tweets > 0}
					In addition, {$user.username} has sent <strong>{$user.fb_posts} Facebook {$app.locale->p($user.fb_posts,'post','posts')}</strong> and <strong>{$user.tweets} {$app.locale->p($user.tweets,'tweet','tweets')}</strong>
					in order to raise awareness about pressing issues.
				{/if}
				</p>

				<p>
					We applaud {$user.username} for taking action to make a difference in the local community.
				</p>

				<p>
					Signed,<br/>
					InspireVive
				</p>
			{/if}
		</div>
		<div class="col-md-4" id="profile-about">	

			{if $user.about||$isMine}
				<h4>About {$user.username}</h4>

				{if $user.about}
					<p>
						{$user.about}
					</p>
				{/if}
				{if $isMine}
					<p>
						<a href="/account" class="edit-link"><i class="glyphicon glyphicon-pencil"></i> Edit bio</a>
					</p>
				{/if}
				<br/>
			{/if}

			<p>
				{if $facebookConnected}
					<a href="{$userObj->facebookProfile()->url()}" target="_blank">
						<i class="icon-profile-facebook"></i>
					</a>
				{/if}
				{if $twitterConnected}
					<a href="{$userObj->twitterProfile()->url()}" target="_blank">
						<i class="icon-profile-twitter"></i>
					</a>
				{/if}
				{if $instagramConnected}
					<a href="{$userObj->instagramProfile()->url()}" target="_blank">
						<i class="icon-profile-instagram"></i>
					</a>
				{/if}
			</p>
		</div>
	</div>
{/block}