<footer class="footer">
	<div class="row lg-menu">
		<div class="container">
			<div class="col-md-4 col-sm-4"><img src="img/footer-logo.png" class="img-responsive" alt="" /> </div>
			<div class="col-md-8 co-sm-8 pull-right">
				<ul>
					@if ($pages->count() > 0)
	            @foreach($pages as $page)
	               <li> <a href="{{ lurl(trans('routes.v-page', ['slug' => $page->slug])) }}"> {{ $page->name }} </a></li>
	            @endforeach
	        @endif
					<li><a href="{{ lurl(trans('routes.contact')) }}"> {{ t('Contact') }} </a></li>
					<li><a href="{{ lurl(trans('routes.v-sitemap', ['countryCode' => $country->get('icode')])) }}"> {{ t('Sitemap') }} </a></li>
					@if (\App\Models\Country::where('active', 1)->count() > 1)
						<li><a href="{{ lurl(trans('routes.countries')) }}"> {{ t('Countries') }} </a></li>
					@endif
				</ul>
			</div>
		</div>
	</div>
	<div class="row no-padding">
		<div class="container">
			<div class="col-md-3 col-sm-12">
				<div class="footer-widget">
					<h3 class="widgettitle widget-title">About Job Stock</h3>
					<div class="textwidget">
						<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem.</p>
						<p>7860 North Park Place <br>San Francisco, CA 94120</p>
						<p><strong>Email:</strong> Support@careerdesk</p>
						<p><strong>Call:</strong> <a href="tel:+15555555555">555-555-1234</a></p>
						<ul class="footer-social">
							<li><a href="{{ config('settings.facebook_page_url') }}"><i class="fa fa-facebook"></i></a></li>
							<li><a href="#"><i class="fa fa-google-plus"></i></a></li>
							<li><a href="{{ config('settings.twitter_url') }}"><i class="fa fa-twitter"></i></a></li>
							<li><a href="#"><i class="fa fa-instagram"></i></a></li>
							<li><a href="#"><i class="fa fa-linkedin"></i></a></li>
						</ul>
					</div>
				</div>
			</div>
			<div class="col-md-3 col-sm-4">
				<div class="footer-widget">
					<h3 class="widgettitle widget-title">All Navigation</h3>
					<div class="textwidget">
						<div class="textwidget">
							<ul class="footer-navigation">
								<li><a href="manage-company.html" title="">Front-end Design</a></li>
								<li><a href="manage-company.html" title="">Android Developer</a></li>
								<li><a href="manage-company.html" title="">CMS Development</a></li>
								<li><a href="manage-company.html" title="">PHP Development</a></li>
								<li><a href="manage-company.html" title="">IOS Developer</a></li>
								<li><a href="manage-company.html" title="">Iphone Developer</a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-3 col-sm-4">
				<div class="footer-widget">
					<h3 class="widgettitle widget-title">All Categories</h3>
					<div class="textwidget">
						<ul class="footer-navigation">
							<li><a href="manage-company.html" title="">Front-end Design</a></li>
							<li><a href="manage-company.html" title="">Android Developer</a></li>
							<li><a href="manage-company.html" title="">CMS Development</a></li>
							<li><a href="manage-company.html" title="">PHP Development</a></li>
							<li><a href="manage-company.html" title="">IOS Developer</a></li>
							<li><a href="manage-company.html" title="">Iphone Developer</a></li>
						</ul>
					</div>
				</div>
			</div>
			<div class="col-md-3 col-sm-4">
				<div class="footer-widget">
					<h3 class="widgettitle widget-title">Connect Us</h3>
					<div class="textwidget">
						<form class="footer-form"> <input type="text" class="form-control" placeholder="Your Name"> <input type="text" class="form-control" placeholder="Email"> <textarea class="form-control" placeholder="Message"></textarea> <button type="submit" class="btn btn-primary">Login</button>                  </form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row copyright">
		<div class="container">
			<p>&copy; {{ date('Y') }} <a href="{{ url('/') }}" style="padding: 0;">{{ config('settings.app_name') }}</a>. All Rights Reserved </p>
		</div>
	</div>
</footer>
<div class="clearfix"></div>
<div class="modal fade" id="signup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				<div class="tab" role="tabpanel">
					<ul class="nav nav-tabs" role="tablist">
						<li role="presentation" class="active"><a href="#login" role="tab" data-toggle="tab">Sign In</a></li>
						<li role="presentation"><a href="#register" role="tab" data-toggle="tab">Sign Up</a></li>
					</ul>
					<div class="tab-content" id="myModalLabel2">
						<div role="tabpanel" class="tab-pane fade in active" id="login"><img src="img/logo.png" class="img-responsive" alt="" />
							<div class="subscribe wow fadeInUp">
								<form class="form-inline" method="post">
									<div class="col-sm-12">
										<div class="form-group"> <input type="email" name="email" class="form-control" placeholder="Username" required=""> <input type="password" name="password" class="form-control" placeholder="Password" required="">
											<div class="center"> <button type="submit" id="login-btn" class="submit-btn"> Login </button> </div>
										</div>
									</div>
								</form>
							</div>
						</div>
						<div role="tabpanel" class="tab-pane fade" id="register"><img src="img/logo.png" class="img-responsive" alt="" />
							<form class="form-inline" method="post">
								<div class="col-sm-12">
									<div class="form-group"> <input type="text" name="email" class="form-control" placeholder="Your Name" required=""> <input type="email" name="email" class="form-control" placeholder="Your Email" required=""> <input type="email" name="email" class="form-control"
											placeholder="Username" required=""> <input type="password" name="password" class="form-control" placeholder="Password" required="">
										<div class="center"> <button type="submit" id="subscribe" class="submit-btn"> Create Account </button> </div>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
