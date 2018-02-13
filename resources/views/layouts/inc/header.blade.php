<?php
use Illuminate\Support\Facades\Request;

// Search parameters
$queryString = (Request::getQueryString() ? ('?' . Request::getQueryString()) : '');

// Get default language
$defaultLang = \App\Models\Language::where('default', 1)->first();
?>
<nav class="navbar navbar-default navbar-fixed navbar-transparent white bootsnav">
	<div class="container"> <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-menu"><i class="fa fa-bars"></i></button>
		<div class="navbar-header">
				<a class="navbar-brand" href="{{ lurl('/') }}">
					<img src="img/logo.png" class="logo logo-display" alt="">
					<img src="img/logo-white.png" class="logo logo-scrolled" alt="">
				</a>
		</div>
		<div class="collapse navbar-collapse" id="navbar-menu">
			<ul class="nav navbar-nav navbar-left" data-in="fadeInDown" data-out="fadeOutUp">
				<li class="active"><input type="text" class="form-control" placeholder="Find Freelancer"></li>
				<li class="dropdown"> <a href="#" class="dropdown-toggle" data-toggle="dropdown">Find Work</a>
					<ul class="dropdown-menu" role="menu">
						<div class="row">
							<div class="col-menu">
								<h6 class="title">Find Work</h6>
								<div class="content">
									<ul class="menu-col">
										<li> <a href="{{ lurl('/' .  trans('routes.t-search')) }}">{{ t('Browse Jobs') }}</a> </li>
										<li> <a href="#">Browse Categories</a> </li>
									</ul>
								</div>
							</div>
					</ul>
				</li>
				<li><a href="page/how-it-works.html">How it Works</a></li>
			</ul>
			<ul class="nav navbar-nav navbar-right" data-in="fadeInDown" data-out="fadeOutUp">
				<li><a href="{{ lurl(trans('routes.login')) }}"><i class="fa fa-sign-in" aria-hidden="true"></i>{{ t('Login') }}</a></li>
				<li><a href="{{ lurl(trans('routes.signup')) }}"><i class="fa fa-user" aria-hidden="true"></i>{{ t('Signup') }}</a></li>
				<li class="left-br"><a href="{{ lurl(trans('routes.create')) }}" class="signin">{{ t('Create a Job ad') }}</a></li>
			</ul>
		</div>
	</div>
</nav>
