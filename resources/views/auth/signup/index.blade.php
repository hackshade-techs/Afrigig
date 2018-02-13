
@extends('layouts.master')

@section('content')

	<div class="main-container">


		<div class="container">


      </div>
			<div class="row">

				@if (isset($errors) and count($errors) > 0)
					<div class="col-lg-12">
						<div class="alert alert-danger">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
							<h5><strong>{{ t('Oops ! An error has occurred. Please correct the red fields in the form') }}</strong></h5>
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

				<!-- <div class="col-md-12 page-content">
					<div class="inner-box category-content"> -->
						<!-- <h2 class="title-2"><strong> <i class="icon-user-add"></i> {{ t('Create your account, Its free') }}</strong></h2> -->
						<!-- <div class="row"> -->
							<!-- @if (config('settings.activation_social_login'))
								<div class="container text-center" style="margin-bottom: 30px;">
									<div class="row row-centered">
										<div class="btn btn-lg btn-fb col-md-5 col-centered" style="margin-right: 4px;">
											<a href="{{ lurl('auth/facebook') }}" class="btn-fb"><i class="icon-facebook"></i> {!! t('Connect with Facebook') !!}</a>
										</div>
										<div class="btn btn-lg btn-danger col-md-5 col-centered" style="margin-left: 4px;">
											<a href="{{ lurl('auth/google') }}" class="btn-danger"><i class="icon-googleplus-rect"></i> {!! t('Connect with Google+') !!}</a>
										</div>
									</div>

									<div class="row row-centered loginOr">
										<div class="col-xs-12 col-sm-12">
											<hr class="hrOr">
											<span class="spanOr rounded">{{ t('or') }}</span>
										</div>
									</div>
								</div>
							@endif -->

              <!-- new registration theming-->
              <div class="row">
                <div class="col-md-12">

                  <div class="wrapper">
                    <section class="signup-screen-sec">
                      <div class="container">
                        <div class="signup-screen">
                          <a href="index.html"><img src="img/logo.png" class="img-responsive" alt=""></a>

                          <form method="POST" action="{{ lurl('signup/submit') }}" enctype="multipart/form-data">
                            {!! csrf_field() !!}
                            <!-- user name -->
                            <div class="form-group required <?php echo (isset($errors) and $errors->has('name')) ? 'has-error' : ''; ?>">


        											<div class="col-md-12">
        												<input name="name" placeholder="{{ t('Name') }}" class="form-control input-md" type="text"
        													   value="{{ old('name') }}">
        											</div>
        										</div>


          										<!-- User Type -->
          										<div class="form-group required <?php echo (isset($errors) and $errors->has('user_type')) ? 'has-error' : ''; ?>">
          											<label class="col-md-4 control-label">{{ t('You are a') }} <sup>*</sup></label>
          											<div class="col-md-6">
          												@foreach ($userTypes as $type)
          													<label class="radio-inline" for="user_type-{{ $type->id }}">
          														<input type="radio" name="user_type" id="user_type-{{ $type->id }}" class="user_type"
          															   value="{{ $type->id }}" {{ (old('user_type', \Illuminate\Support\Facades\Input::get('type'))==$type->id) ? 'checked="checked"' : '' }}>
          														{{ t('' . $type->name) }}
          													</label>
          												@endforeach
          											</div>
          										</div>

                              <!-- Country -->
          										@if (!$ip_country)
          											<div class="form-group required <?php echo (isset($errors) and $errors->has('country')) ? 'has-error' : ''; ?>">
          												<!-- <label class="col-md-4 control-label" for="country">{{ t('Your Country') }} <sup>*</sup></label> -->
          												<div class="col-md-12">
          													<select id="country" name="country" class="form-control sselecter">
          														<option value="0" {{ (!old('country') or old('country')==0) ? 'selected="selected"' : '' }}>{{ t('Select') }}</option>
          														@foreach ($countries as $code => $item)
          															<option value="{{ $code }}" {{ (old('country', (!$country->isEmpty()) ? $country->get('code') : 0)==$code) ? 'selected="selected"' : '' }}>
          																{{ $item->get('name') }}
          															</option>
          														@endforeach
          													</select>
          												</div>
          											</div>
          										@else
          											<input id="country" name="country" type="hidden" value="{{ $country->get('code') }}">
          										@endif

          										<!-- Phone Number -->
          										<div class="form-group required <?php echo (isset($errors) and $errors->has('phone')) ? 'has-error' : ''; ?>">
          											<!-- <label class="col-md-4 control-label">{{ t('Phone') }} <sup>*</sup></label> -->
          											<div class="col-md-12">

                                    <!-- <span id="phone_country" class="input-group-addon"><i class="icon-mail"></i></span> -->
          													<input name="phone" placeholder="{{ t('Phone Number') }}" class="form-control input-md"
          														   type="text" value="{{ old('phone') }}">

          											</div>
          										</div>


                              <!-- email -->
                              <div class="form-group required <?php echo (isset($errors) and $errors->has('email')) ? 'has-error' : ''; ?>">
          											<!-- <label class="col-md-4 control-label" for="email">{{ t('Email') }} <sup>*</sup></label> -->


                                <div class="col-md-12">
                               <input id="email" name="email" type="email" class="form-control" placeholder="{{ t('Email') }}"
          														   value="{{ old('email') }}">

          											</div>
                                </div>

                                <!-- password fields -->
                                <div class="form-group required <?php echo (isset($errors) and $errors->has('password')) ? 'has-error' : ''; ?>">
            											<!-- <label class="col-md-4 control-label" for="password">{{ t('Password') }} <sup>*</sup></label> -->
            											<div class="col-md-12">
            												<input id="password" name="password" type="password" class="form-control"
            													   placeholder="{{ t('Password') }}">

            												<input id="password_confirmation" name="password_confirmation" type="password" class="form-control"
            													   placeholder="{{ t('Password Confirmation') }}">
            												<p class="help-block">{{ t('At least 5 characters') }}</p>
            											</div>
            										</div>

                                <!-- terms and conditions -->

                                <div class="form-group required <?php echo (isset($errors) and $errors->has('term')) ? 'has-error' : ''; ?>"
            											 style="margin-top: -10px;">

            											<div class="col-md-12">
            												<div class="termbox mb10">
            													<label class="checkbox-inline" for="term">
            														<input name="term" id="term" value="1" type="checkbox" {{ (old('term')=='1') ? 'checked="checked"' : '' }}>
            														{!! t('I have read and agree to the <a href=":url">Terms & Conditions</a>', ['url' => getUrlPageByType('terms')]) !!}
            													</label>
            												</div>
            												<div style="clear:both"></div>
            											</div>
            										</div>




                            <button class="btn btn-login" type="submit">{{ t('Register') }}</button>
                            <!-- <div class="form-group">
        											<label class="col-md-4 control-label"></label>
        											<div class="col-md-8">
        												<button id="signup_btn" class="btn btn-success btn-lg"> {{ t('Register') }} </button>
        											</div>
        										</div> -->
                              <span>Have You Account? <a href="login.html"> Login</a></span>





                            </form>
                        </div>
                      </div>
                    </section><button class="w3-button w3-teal w3-xlarge w3-right" onclick="openRightMenu()"><i class="spin fa fa-cog" aria-hidden="true"></i></button>
                    <div class="w3-sidebar w3-bar-block w3-card-2 w3-animate-right" style="display:none;right:0;" id="rightMenu">
                    <button onclick="closeRightMenu()" class="w3-bar-item w3-button w3-large">Close &times;</button>
                      <ul id="styleOptions" title="switch styling">
                        <li><a href="javascript: void(0)" class="cl-box blue" data-theme="colors/blue-style"></a></li>
                        <li><a href="javascript: void(0)" class="cl-box red" data-theme="colors/red-style"></a></li>
                        <li><a href="javascript: void(0)" class="cl-box purple" data-theme="colors/purple-style"></a></li>
                        <li><a href="javascript: void(0)" class="cl-box green" data-theme="colors/green-style"></a></li>
                        <li><a href="javascript: void(0)" class="cl-box dark-red" data-theme="colors/dark-red-style"></a></li>
                        <li><a href="javascript: void(0)" class="cl-box orange" data-theme="colors/orange-style"></a></li>
                        <li><a href="javascript: void(0)" class="cl-box sea-blue" data-theme="colors/sea-blue-style "></a></li>
                        <li><a href="javascript: void(0)" class="cl-box pink" data-theme="colors/pink-style"></a></li>
                      </ul>
                    </div>
                  </div>

                </div>
              </div>
              <!-- end of new registration themeing -->

						</div>
					</div>
				</div>


			</div>
		</div>
	</div>
@endsection

@section('after_scripts')
	<script src="{{ url('/assets/js/fileinput.min.js') }}" type="text/javascript"></script>
	@if (file_exists(public_path() . '/assets/js/fileinput_locale_'.$lang->get('abbr').'.js'))
		<script src="{{ url('/assets/js/fileinput_locale_'.$lang->get('abbr').'.js') }}" type="text/javascript"></script>
	@endif
	<script language="javascript">
		/* initialize with defaults (resume) */
		$('#filename').fileinput(
		{
			'showPreview': false,
			'allowedFileExtensions': {!! getUploadFileTypes('file', true) !!},
			'browseLabel': '{!! t("Browse") !!}',
			'showUpload': false,
			'showRemove': false,
			'maxFileSize': {{ (int)config('settings.upload_max_file_size', 1000) }}
		});
	</script>
	<script language="javascript">
		var user_type = '<?php echo old('user_type', \Illuminate\Support\Facades\Input::get('type')); ?>';

		$(document).ready(function ()
		{
			/* Set user type */
			setUserType(user_type);
			$('.user_type').click(function () {
				setUserType($(this).val());
			});

			var countries = <?php echo (isset($countries)) ? $countries->toJson() : '{}'; ?>;
			var countryCode = '<?php echo old('country', ($country) ? $country->get('code') : 0); ?>';

			/* Set Country Phone Code */
			setCountryPhoneCode(countryCode, countries);
			$('#country').change(function () {
				setCountryPhoneCode($(this).val(), countries);
			});

			/* Submit Form */
			$("#signup_btn").click(function () {
				$("#signup_form").submit();
				return false;
			});
		});
	</script>
@endsection
