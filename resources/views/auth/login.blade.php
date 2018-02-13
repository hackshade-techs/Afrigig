
@extends('layouts.master')

@section('content')
	<div class="main-container">
		<div class="container">
			<div class="row">

				@if (count($errors) > 0)
					<div class="col-lg-12">
						<div class="alert alert-danger">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
							<ul class="list list-check">
								@foreach ($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					</div>
				@endif

				@if (Session::has('flash_notification.message'))
					<div class="container" style="margin-bottom: -10px; margin-top: -10px;">
						<div class="row">
							<div class="col-lg-12">
								@include('flash::message')
							</div>
						</div>
					</div>
				@endif


				<!-- @if (config('settings.activation_social_login'))
					<div class="container text-center" style="margin-bottom: 30px;">
						<div class="row">
							<div class="btn btn-fb" style="width: 194px; margin-right: 1px;">
								<a href="{{ lurl('auth/facebook') }}" class="btn-fb"><i class="icon-facebook"></i> Facebook</a>
							</div>
							<div class="btn btn-danger" style="width: 194px; margin-left: 1px;">
								<a href="{{ lurl('auth/google') }}" class="btn-danger"><i class="icon-googleplus-rect"></i> Google+</a>
							</div>
						</div>
					</div>
				@endif -->
        <!-- new login credentials -->
        <div class="row">
          <div class="col-md-12">

            <div class="wrapper">
              <section class="login-screen-sec">
                <div class="container">
                  <div class="login-screen"><a href="index.html"><img src="img/logo.png" class="img-responsive" alt=""></a>
                    <form id="loginForm" role="form" method="POST" action="{{ lurl('login') }}">
                      {!! csrf_field() !!}

                      <!-- email address -->
                      <div class="form-group <?php echo ($errors->has('email')) ? 'has-error' : ''; ?>">
      									<div class="input-icon"><i class="icon-user fa"></i>
      										<input id="email" name="email" type="text" placeholder="{{ t('Email Address') }}" class="form-control email"
      											   value="{{ (session('email')) ? session('email') : old('email') }}">
      									</div>
      								</div>

                      <!-- password -->
                      <div class="form-group <?php echo ($errors->has('password')) ? 'has-error' : ''; ?>">
      									<div class="input-icon"></i>
      										<input id="password" name="password" type="password" class="form-control" placeholder="{{ t('Password') }}">
      									</div>
      								</div>

                      <!-- keep me logged in  -->
                      <div>
        								<label>
        									<input type="checkbox" value="1" name="remember" id="remember"> {{ t('Keep me logged in') }} </label>
        								<p class="text-center pull-right"><a href="<?php echo lurl('password/reset'); ?>"> {{ t('Lost your password?') }} </a>
        								</p>
        								<div style=" clear:both"></div>
        							</div>


                      <!-- <div class="form-group">
                        <button id="loginBtn" class="btn btn-primary btn-block"> {{ t('Login') }} </button>
                      </div> -->

                      <button class="btn btn-login" type="submit">{{ t('Login') }}</button>

                      <span>{{ t('Don\'t have an account?') }}  <a href="signup.html"><a href="<?php echo lurl(trans('routes.signup')); ?>"><strong>{{ t('Sign Up') }} !</strong> </a></a></span><span>
                  </div>
                </div>
              </section>
              <button class="w3-button w3-teal w3-xlarge w3-right" onclick="openRightMenu()"><i class="spin fa fa-cog" aria-hidden="true"></i></button>
            </div>


          </div>
        </div>

        <!-- end of new login credentials -->



				<?php /*@include('layouts.inc.social.horizontal')*/ ?>
			</div>
		</div>
	</div>
@endsection

@section('after_scripts')
	<script language="javascript">
		$(document).ready(function () {
			$("#loginBtn").click(function () {
				$("#loginForm").submit();
				return false;
			});
		});
	</script>
@endsection
