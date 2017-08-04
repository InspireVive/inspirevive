{extends file='parent-minimal.tpl'}
{block name=main}
	<div id="fb-root"></div>

	<div id="content" class="clearfix">
		<div id="left-navbar" class="drawers scrollable visible-xs">
			<div class="left-drawer">
				<div class="person visible-xs">
					{if $app.user->isLoggedIn()}
						<div class="name">
							<img src="{$app.user->profilePicture(30)}" width="30" height="30" class="img-circle" />
							{$app.user->name(true)}
						</div>
						<div class="controls">
							<a href="/account" class="btn btn-link btn-lg">
								<span class="ion-gear-b"></span>
							</a>
							<a href="/logout" class="btn btn-link btn-lg">
								<span class="ion-log-out"></span>
							</a>
						</div>
					{else}
						<div class="controls">
							<a href="/signup" class="btn btn-link btn-lg">
								Join
							</a>
							<a href="/login" class="btn btn-link btn-lg">
								Sign In
							</a>
						</div>
					{/if}
				</div>

				<div class="footer">
					<p class="links">
						<a href="https://github.com/inspirevive/inspirevive">Powered by InspireVive</a>
					</p>
				</div>
			</div>
		</div>

		<div id="page" class="scrollable">
			{if $app.user->isAdmin()}
				<div class="alert admin">
					<a href="/admin" class="btn btn-link" target="_blank">
						Administration Dashboard
					</a>
				</div>
			{/if}
			<nav class="navbar navbar-default" role="navigation">
				<div class="container">
					<div class="navbar-header">
						<button type="button" class="navbar-toggle">
							<span class="sr-only">Toggle navigation</span>
							<span class="ion-navicon"></span>
						</button>
						<a class="navbar-brand" href="/">
							<img src="{$app.view_engine->asset_url('/img/logo.png')}" alt="InspireVive" class="img-responsive" />
						</a>
					</div>

					<div class="collapse navbar-collapse">
						<ul class="nav navbar-nav navbar-right">
							{if $app.user->isLoggedIn()}
								<li class="{if isset($profileTab)}active{/if}">
									<a href="/profile" title="{$app.user->username}">
										<img src="{$app.user->profilePicture(30)}" width="30" height="30" class="img-circle" />
										{$app.user->name(true)}
									</a>
								</li>
								<li class="dropdown">
									<a href="#" data-toggle="dropdown">
										<span class="ion-chevron-down"></span>
									</a>
									<ul class="dropdown-menu">
										<li>
											<a href="{$app.user->url()}">
												Your Public Profile
											</a>
										</li>
										<li class="divider"></li>
										<li>
											<a href="/account/profile">
												Update Profile
											</a>
										</li>
										<li>
											<a href="/account/settings">
												Account Settings
											</a>
										</li>
										<li class="divider"></li>
										<li>
											<a href="/logout">
												Sign Out
											</a>
										</li>
									</ul>
								</li>
							{else}
								<li class="login {if isset($loginTab)}active{/if}">
									<a href="/login">
										Sign In
									</a>
								</li>
								<li class="signup {if isset($signupTab)}active{/if}">
									<a href="/signup" class="btn btn-warning">
										Sign Up
									</a>
								</li>
							{/if}
						</ul>
					</div>
				</div>
			</nav>

{block name=preBody}{/block}
			<div class="body {block name=bodyClass}{/block}">
				<div class="container main-container">
{block name=content}{/block}
				</div>
			</div>
{block name=postBody}{/block}
		</div>
	</div>

	<footer class="clearfix hidden-xs">
		<div class="dos clearfix">
			<div class="container">
				<div class="copyright">
					<a href="https://github.com/inspirevive/inspirevive">Powered by InspireVive</a>
				</div>
			</div>
		</div>
	</footer>
	{block name=footer}{/block}
{/block}