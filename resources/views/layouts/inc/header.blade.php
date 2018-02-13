<<<<<<< HEAD
<nav class="navbar navbar-default navbar-fixed navbar-transparent white bootsnav">
	<div class="container"> <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-menu"><i class="fa fa-bars"></i></button>
		<div class="navbar-header"> <a class="navbar-brand" href="index.html"><img src="img/logo.png" class="logo logo-display" alt=""><img src="img/logo-white.png" class="logo logo-scrolled" alt=""></a> </div>
		<div class="collapse navbar-collapse" id="navbar-menu">
			<ul class="nav navbar-nav navbar-left" data-in="fadeInDown" data-out="fadeOutUp">
				<li class="active"><input type="text" class="form-control" placeholder="Find Freelancer"></li>
				<li class="dropdown megamenu-fw"><a href="#" class="dropdown-toggle" data-toggle="dropdown">Browse</a>
					<ul class="dropdown-menu megamenu-content" role="menu">
						<li>
							<div class="row">
								<div class="col-menu col-md-3">
									<h6 class="title">Main Pages</h6>
									<div class="content">
										<ul class="menu-col">
											<li><a href="index.html">Home Page 1</a></li>
											<li><a href="index-2.html">Home Page 2</a></li>
											<li><a href="index-3.html">Home Page 3</a></li>
											<li><a href="index-4.html">Home Page 4</a></li>
											<li><a href="index-5.html">Home Page 5</a></li>
											<li><a href="signin-signup.html">Sign In / Sign Up</a></li>
											<li><a href="search-job.html">Search Job</a></li>
										</ul>
									</div>
								</div>
								<div class="col-menu col-md-3">
									<h6 class="title">For Candidate</h6>
									<div class="content">
										<ul class="menu-col">
											<li><a href="browse-jobs.html">Browse Jobs</a></li>
											<li><a href="browse-company.html">Browse Companies</a></li>
											<li><a href="create-resume.html">Create Resume</a></li>
											<li><a href="resume-detail.html">Resume Detail</a></li>
											<li><a href="http://themezhub.com/">Manage Jobs</a></li>
											<li><a href="job-detail.html">Job Detail</a></li>
											<li><a href="browse-jobs-grid.html">Job In Grid</a></li>
											<li><a href="candidate-profile.html">Candidate Profile</a></li>
										</ul>
									</div>
								</div>
								<div class="col-menu col-md-3">
									<h6 class="title">For Employee</h6>
									<div class="content">
										<ul class="menu-col">
											<li><a href="create-job.html">Create Job</a></li>
											<li><a href="create-company.html">Create Company</a></li>
											<li><a href="manage-company.html">Manage Company</a></li>
											<li><a href="manage-candidate.html">Manage Candidate</a></li>
											<li><a href="manage-employee.html">Manage Employee</a></li>
											<li><a href="browse-resume.html">Browse Resume</a></li>
											<li><a href="search-new.html">New Search Job</a></li>
											<li><a href="employer-profile.html">Employer Profile</a></li>
										</ul>
									</div>
								</div>
								<div class="col-menu col-md-3">
									<h6 class="title">Extra Pages</h6>
									<div class="content">
										<ul class="menu-col">
											<li><a href="manage-resume-2.html">Manage Resume 2</a></li>
											<li><a href="manage-resume.html">Manage Resume</a></li>
											<li><a href="company-detail.html">Company Detail</a></li>
											<li><a href="blog-detail.html">Blog detail</a></li>
											<li><a href="accordion.html">Accordion</a></li>
											<li><a href="tab.html">Tab Style</a></li>
											<li><a href="new-job-detail.html">New Job Detail</a></li>
										</ul>
									</div>
								</div>
							</div>
=======
<?php
use Illuminate\Support\Facades\Request;

// Search parameters
$queryString = (Request::getQueryString() ? ('?' . Request::getQueryString()) : '');

// Get default language
$defaultLang = \App\Models\Language::where('default', 1)->first();
?>
<div class="header">
	<nav class="navbar navbar-site navbar-default" role="navigation">
		<div class="container">
			<div class="navbar-header">
				<button data-target=".navbar-collapse" data-toggle="collapse" class="navbar-toggle" type="button">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a href="{{ lurl('/') }}" class="navbar-brand logo logo-title">
					<img src="{{ \Storage::url(config('settings.app_logo')) }}" style="width:auto; height:40px; float:left; margin:0 5px 0 0;"
						 alt="{{ strtolower(config('settings.app_name')) }}" class="tooltipHere" title="" data-placement="bottom"
						 data-toggle="tooltip" type="button"
						 data-original-title="{{ config('settings.app_name') . ((isset($country) and $country->has('name')) ? ' ' . $country->get('name') : '') }}"/>
				</a>
				@if (config('settings.activation_country_flag'))
					@if (isset($country) and !$country->isEmpty())
						@if (file_exists(public_path() . '/images/flags/32/'.strtolower($country->get('code')).'.png'))
							<span class="navbar-brand logo logo-title hidden-sm hidden-xs">
								@if (\App\Models\Country::where('active', 1)->count() > 1)
									<a href="{{ lurl(trans('routes.countries')) }}" title="{{ t('Countries') }}">
										<img src="{{ url('images/flags/32/'.strtolower($country->get('code')).'.png') }}" style="float: left; margin: 6px 0 0 5px;">
									</a>
								@else
									<img src="{{ url('images/flags/32/'.strtolower($country->get('code')).'.png') }}" style="float: left; margin: 6px 0 0 5px;">
								@endif
							</span>
						@endif
					@endif
				@endif
			</div>
			<div class="navbar-collapse collapse">
				<ul class="nav navbar-nav navbar-left">
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">
							<span>Hire Freelancers Now!</span>
							<i class=" icon-down-open-big fa"></i>
						</a>
						<ul class="dropdown-menu user-menu">
							<li><a href="{{ lurl(trans('routes.create')) }}">{{ t('Create a Job ad') }}</a></li>
							{{-- <li class="active"><a href="#">Post a Local Job</a></li> --}}

						</ul>
					</li>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">
							<span>Find Work</span>
							<i class=" icon-down-open-big fa"></i>
						</a>
						<ul class="dropdown-menu user-menu">
							<li><a href="{{ lurl('/' .  trans('routes.t-search')) }}">{{ t('Browse Jobs') }}</a></li>
							<li class=""><a href="#">Browse Categories</a></li>

						</ul>
					</li>
					<li class="{{ Request::path()=='page/how-it-works.html'}}"><a href="{{ url('page/how-it-works.html') }}">How it Works</a></li>

					{{-- @if (!auth()->user())
						<li><a href="{{ lurl(trans('routes.signup')) . '?type=3' }}">{{ t('Add Resume') }}</a></li>
					@endif --}}
				</ul>

				<ul class="nav navbar-nav navbar-right">
					@if (!auth()->user())
						<li><a href="{{ lurl(trans('routes.login')) }}">{{ t('Login') }}</a></li>
						<li><a href="{{ lurl(trans('routes.signup')) }}">{{ t('Signup') }}</a></li>
						<li class="postadd">
							<a class="btn btn-block btn-post btn-yellow"
							   href="{{ lurl(trans('routes.create')) }}"> {{ t('Create a Job ad') }}</a>
>>>>>>> 8e04d4a49228797c1ee8087edd3a284f61136490
						</li>
					</ul>
				</li>
				<li><a href="blog.html">How it Works</a></li>
			</ul>
			<ul class="nav navbar-nav navbar-right" data-in="fadeInDown" data-out="fadeOutUp">
				<li><a href="login.html"><i class="fa fa-pencil" aria-hidden="true"></i>Login</a></li>
				<li><a href="pricing.html"><i class="fa fa-sign-in" aria-hidden="true"></i>SignUp</a></li>
				<li class="left-br"><a href="javascript:void(0)" data-toggle="modal" data-target="#signup" class="signin">Post Project</a></li>
			</ul>
		</div>
<<<<<<< HEAD
	</div>
</nav>
=======
	</nav>
</div>
>>>>>>> 8e04d4a49228797c1ee8087edd3a284f61136490
