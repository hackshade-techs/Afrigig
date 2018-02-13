{{--
 * JobClass - Geolocalized Job Board Script
 * Copyright (c) BedigitCom. All Rights Reserved
 *
 * Website: http://www.bedigit.com
 *
 * LICENSE
 * -------
 * This software is furnished under a license and may be used and copied
 * only in accordance with the terms of such license and with the inclusion
 * of the above copyright notice. If you Purchased from Codecanyon,
 * Please read the full License from here - http://codecanyon.net/licenses/standard
--}}
@extends('layouts.master')

@section('content')
	<div class="main-container">
		<div class="container">
			<div class="row">

				@if (count($errors) > 0)
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

				<div class="col-md-9 page-content">
					<div class="inner-box category-content">
						<h2 class="title-2"><strong> <i class="icon-docs"></i> {{ t('Post a Job') }}</strong></h2>
						<div class="row">
							<div class="col-sm-12">


								<form class="form-horizontal" id="adForm" method="POST" action="{{ lurl('create/submit') }}"
									  enctype="multipart/form-data">
									{!! csrf_field() !!}
									<fieldset>

										<!-- Category -->
										<div class="form-group required <?php echo ($errors->has('category')) ? 'has-error' : ''; ?>">
											<label class="col-md-3 control-label">{{ t('Category') }} <sup>*</sup></label>
											<div class="col-md-8">
												<select name="category" id="category" class="form-control selecter">
													<option value="0"
															@if(old('category')=='' or old('category')==0)selected="selected"@endif> {{ t('Select a category') }} </option>
													@foreach ($categories as $cat)
														<option value="{{ $cat->tid }}"
																@if(old('category')==$cat->tid)selected="selected"@endif> {{ $cat->name }} </option>
													@endforeach
												</select>
											</div>
										</div>

										<!-- Job title -->
										<div class="form-group required <?php echo ($errors->has('title')) ? 'has-error' : ''; ?>">
											<label class="col-md-3 control-label" for="title">{{ t('Title') }} <sup>*</sup></label>
											<div class="col-md-8">
												<input id="title" name="title" placeholder="{{ t('Job title') }}" class="form-control input-md"
													   type="text" value="{{ old('title') }}">
												<span class="help-block">{{ t('A great title needs at least 60 characters.') }} </span>
											</div>
										</div>

										<!-- Describe ad -->
										<div class="form-group required <?php echo ($errors->has('description')) ? 'has-error' : ''; ?>">
											<label class="col-md-3 control-label" for="description">{{ t('Describe ad') }} <sup>*</sup></label>
                                            <div class="col-md-11" style="position: relative; float: right; padding-top: 10px;">
                                                <?php $ckeditorClass = (config('settings.ckeditor_wysiwyg')) ? 'ckeditor' : ''; ?>
												<textarea class="form-control {{ $ckeditorClass }}" id="description" name="description" rows="10">{{ old('description') }}</textarea>
												<p class="help-block">{{ t('Describe what makes your ad unique') }}</p>
											</div>
										</div>

										<!-- Ad Type -->
										<div id="adTypeBloc" class="form-group required <?php echo ($errors->has('ad_type')) ? 'has-error' : ''; ?>">
											<label class="col-md-3 control-label">{{ t('Job Type') }} <sup>*</sup></label>
											<div class="col-md-8">
												<select name="ad_type" id="ad_type" class="form-control selecter">
													@foreach ($ad_types as $type)
														<option value="{{ $type->tid }}" @if(old('ad_type')==$type->tid)selected="selected"@endif> {{ $type->name }} </option>
													@endforeach
												</select>
											</div>
										</div>

										<!-- Salary -->
										<div id="salaryBloc" class="form-group <?php echo ($errors->has('salary_min')) ? 'has-error' : ''; ?>">
											<label class="col-md-3 control-label" for="price">{{ t('Salary') }}</label>
											<div class="col-md-4">
												<div class="input-group">
													@if ($country->get('currency')->in_left == 1)
														<span class="input-group-addon">{{ $country->get('currency')->symbol }}</span>
													@endif
													<input id="salary_min" name="salary_min" class="form-control" placeholder="{{ t('Salary (min)') }}" type="text" value="{{ old('salary_min') }}">
													<input id="salary_max" name="salary_max" class="form-control" placeholder="{{ t('Salary (max)') }}" type="text" value="{{ old('salary_max') }}">
													@if ($country->get('currency')->in_left == 0)
														<span class="input-group-addon">{{ $country->get('currency')->symbol }}</span>
													@endif
												</div>
											</div>

											<div class="col-md-4">
												<select name="salary_type" id="salary_type" class="form-control selecter">
													@foreach ($salary_type as $type)
														<option value="{{ $type->tid }}" @if(old('ad_type')==$type->tid)selected="selected"@endif> {{ 'per'.' '.$type->name }} </option>
													@endforeach
												</select>
												<div class="checkbox">
													<label>
														<input id="negotiable" name="negotiable" type="checkbox" value="1" {{ (old('negotiable')=='1') ? 'checked="checked"' : '' }}>
														{{ t('Negotiable') }}
													</label>
												</div>
											</div>
										</div>

										<!-- Start Date -->
										<div class="form-group <?php echo ($errors->has('start_date')) ? 'has-error' : ''; ?>">
											<label class="col-md-3 control-label" for="start_date">{{ t('Start Date') }} </label>
											<div class="col-md-8">
												<input id="start_date" name="start_date" placeholder="{{ t('Start Date') }}" class="form-control input-md"
													   type="text" value="{{ old('start_date') }}">
											</div>
										</div>



										<div class="content-subheading"><i class="icon-user fa"></i> <strong>Company information</strong></div>



										<!-- Company Name -->
										<div class="form-group required <?php echo ($errors->has('company_name')) ? 'has-error' : ''; ?>">
											<label class="col-md-3 control-label" for="company_name">{{ t('Company Name') }} <sup>*</sup></label>
											<div class="col-md-8">
												<input id="company_name" name="company_name" placeholder="{{ t('Company Name') }}" class="form-control input-md" type="text" value="{{ old('company_name') }}">
											</div>
										</div>

										<!-- Company Description -->
										<div class="form-group required <?php echo ($errors->has('company_description')) ? 'has-error' : ''; ?>">
											<label class="col-md-3 control-label" for="company_description">{{ t('Company Description') }} <sup>*</sup></label>
											<div class="col-md-8">
												<textarea class="form-control" id="company_description" name="company_description" rows="5">{{ old('company_description') }}</textarea>
												<p class="help-block">{{ t('Describe the company') }}</p>
											</div>
										</div>

										<!-- Logo -->
										<div id="logoBloc" class="form-group <?php echo ($errors->has('logo')) ? 'has-error' : ''; ?>">
											<label class="col-md-3 control-label" for="logo"> {{ t('Logo') }} </label>
											<div class="col-md-8">
												<div class="mb10 <?php echo ($errors->has('logo')) ? 'has-error' : ''; ?>">
													<input id="logo" name="logo" type="file" class="file picimg">
												</div>
												<p class="help-block">{{ t('File types: :file_types', ['file_types' => showValidFileTypes('image')]) }}</p>
											</div>
										</div>

										<!-- Company Website -->
										<div class="form-group <?php echo ($errors->has('company_website')) ? 'has-error' : ''; ?>">
											<label class="col-md-3 control-label" for="company_website">{{ t('Company Website') }} </label>
											<div class="col-md-8">
												<input id="company_website" name="company_website" placeholder="{{ t('Company Website') }}" class="form-control input-md" type="text" value="{{ old('company_website') }}">
											</div>
										</div>

										<!-- Country -->
										@if (!$ip_country)
											<div class="form-group required <?php echo ($errors->has('country')) ? 'has-error' : ''; ?>">
												<label class="col-md-3 control-label" for="country">{{ t('Your Country') }} <sup>*</sup></label>
												<div class="col-md-8">
													<select id="country" name="country" class="form-control sselecter">
														<option value="0" {{ (!old('country') or old('country')==0) ? 'selected="selected"' : '' }}> {{ t('Select your Country') }} </option>
														@foreach ($countries as $item)
															<option value="{{ $item->get('code') }}" {{ (old('country', ($country) ? $country->get('code') : 0)==$item->get('code')) ? 'selected="selected"' : '' }}>{{ $item->get('name') }}</option>
														@endforeach
													</select>
												</div>
											</div>
										@else
											<input id="country" name="country" type="hidden" value="{{ $country->get('code') }}">
										@endif

										<?php
										/*
										@if (\Illuminate\Support\Facades\Schema::hasColumn('ads', 'address'))
										<!-- Address -->
										<div class="form-group required <?php echo ($errors->has('address')) ? 'has-error' : ''; ?>">
											<label class="col-md-3 control-label" for="title">{{ t('Address') }} </label>
											<div class="col-md-8">
												<input id="address" name="address" placeholder="{{ t('Address') }}" class="form-control input-md"
													   type="text" value="{{ old('address') }}">
												<span class="help-block">{{ t('Fill an address to display on Google Maps.') }} </span>
											</div>
										</div>
										@endif
										*/
										?>

										<!-- Location -->
										<div id="locationBox"
											 class="form-group required <?php echo ($errors->has('location')) ? 'has-error' : ''; ?>">
											<label class="col-md-3 control-label" for="location">{{ t('Location') }} <sup>*</sup></label>
											<div class="col-md-8">
												<select id="location" name="location" class="form-control sselecter">
													<option value="0" {{ (!old('location') or old('location')==0) ? 'selected="selected"' : '' }}> {{ t('Select your Location') }} </option>
												</select>
											</div>
										</div>

										<!-- Sub-Location -->
										<div id="sub_location_box"
											 class="form-group <?php echo ($errors->has('sub_location')) ? 'has-error' : ''; ?>">
											<label class="col-md-3 control-label" for="sub_location">{{ t('Sub-location') }}</label>
											<div class="col-md-8">
												<select id="sub_location" name="sub_location" class="form-control sselecter">
													<option value="0" {{ (!old('sub_location') or old('sub_location')==0) ? 'selected="selected"' : '' }}> {{ t('Please select your location before') }} </option>
												</select>
											</div>
										</div>
										<input type="hidden" id="has_children" name="has_children" value="{{ old('has_children') }}">

										<!-- City -->
										<div id="city_box"
											 class="form-group required <?php echo ($errors->has('city')) ? 'has-error' : ''; ?>">
											<label class="col-md-3 control-label" for="city">{{ t('City') }} <sup>*</sup></label>
											<div class="col-md-8">
												<select id="city" name="city" class="form-control sselecter">
													<option value="0" {{ (!old('city') or old('city')==0) ? 'selected="selected"' : '' }}> {{ t('Please select your location before') }} </option>
												</select>
											</div>
										</div>

										@if (Auth::check())
											<input id="contact_name" name="contact_name" type="hidden" value="{{ $user->name }}">
										@else
											<div class="form-group required <?php echo ($errors->has('contact_name')) ? 'has-error' : ''; ?>">
												<label class="col-md-3 control-label" for="contact_name">{{ t('Contact Name') }} <sup>*</sup></label>
												<div class="col-md-8">
													<div class="input-group">
														<span class="input-group-addon"><i class="icon-user"></i></span>
														<input id="contact_name" name="contact_name" placeholder="{{ t('Contact Name') }}"
														   class="form-control input-md" type="text" value="{{ old('contact_name') }}">
													</div>
												</div>
											</div>
										@endif

										@if (Auth::check())
											<input id="contact_email" name="contact_email" type="hidden" value="{{ $user->email }}">
										<!--<input id="contact_phone" name="contact_phone" type="hidden" value="{{-- $user->phone --}}">-->
										@else
										<!-- Contact Email -->
											<div class="form-group required <?php echo ($errors->has('contact_email')) ? 'has-error' : ''; ?>">
												<label class="col-md-3 control-label" for="contact_email"> {{ t('Contact Email') }} <sup>*</sup></label>
												<div class="col-md-8">
													<div class="input-group">
														<span class="input-group-addon"><i class="icon-mail"></i></span>
														<input id="contact_email" name="contact_email" class="form-control"
															   placeholder="{{ t('Contact Email') }}" type="text" value="{{ old('contact_email') }}">
													</div>
												</div>
											</div>
										@endif

										<!-- Phone Number -->
										<div class="form-group required <?php echo ($errors->has('contact_phone')) ? 'has-error' : ''; ?>">
											<label class="col-md-3 control-label" for="contact_phone">{{ t('Phone Number') }}</label>
											<div class="col-md-8">
												<div class="input-group">
													<span id="phone_country" class="input-group-addon">
														<i class="icon-phone-1"></i>
													</span>
													<input id="contact_phone" name="contact_phone"
														   placeholder="{{ t('Phone Number (in local format)') }}"
														   class="form-control input-md" type="text"
														   value="{{ old('contact_phone', ((Auth::check() and isset($user->phone)) ? $user->phone : '')) }}">
												</div>
												<div class="checkbox">
													<label>
														<input id="contact_phone_hidden" name="contact_phone_hidden" type="checkbox"
															   value="1" {{ (old('contact_phone_hidden')=='1') ? 'checked="checked"' : '' }}>
														<small> {{ t('Hide the phone number on this ads.') }}</small>
													</label>
												</div>
											</div>
										</div>



										@if (isset($packages) and isset($payment_methods) and $packages->count() > 0 and $payment_methods->count() > 0)
											<div style="margin-top: 30px;"></div>
											<div class="well" style="padding-bottom: 0;">
												<h3><i class="icon-certificate icon-color-1"></i> {{ t('Premium Ad') }} </h3>
												<p>
													{{ t('The premium package help sellers promote their products or services by giving more visibility to their ads to attract more buyers and sell what they want faster.') }}
												</p>
												<div class="form-group <?php echo ($errors->has('package' )) ? 'has-error' : ''; ?>"
													 style="margin-bottom: 0;">
													<table id="packagesTable" class="table table-hover checkboxtable" style="margin-bottom: 0;">
														@foreach ($packages as $package)
															<tr>
																<td>
																	<div class="radio" style="padding-left: 15px;">
																		<label>
																			<input class="package-selection" type="radio" name="package"
																				   id="package-{{ $package->tid }}"
																				   value="{{ $package->tid }}" {{ (old('package' )==$package->tid) ? 'checked' : (($package->price==0) ? 'checked' : '') }}>
																			<strong>{{ $package->name }} </strong>
																		</label>
																	</div>
																</td>
																<td style="text-align: right; padding-right: 25px;">
																	<p id="price-{{ $package->tid }}">
																		@if ($package->currency->in_left == 1) <span
																				class="priceCurr">{{ $package->currency->symbol }}</span> @endif
																		<span class="priceInt">{{ $package->price }}</span>
																		@if ($package->currency->in_left == 0) <span
																				class="priceCurr">{{ $package->currency->symbol }}</span> @endif
																	</p>
																</td>
															</tr>
														@endforeach

														<tr>
															<td>
																<div class="form-group <?php echo ($errors->has('payment_method')) ? 'has-error' : ''; ?>"
																	 style="margin-bottom: 0;">
																	<div class="col-md-8">
																		<select class="form-control selecter" name="payment_method" id="payment_method">
																			@foreach ($payment_methods as $payment_method)
                                                                                @if (view()->exists('payment::' . $payment_method->name))
																				<option value="{{ $payment_method->id }}" data-name="{{ $payment_method->name }}" {{ (old('payment_method')==$payment_method->id) ? 'selected="selected"' : '' }}>
																					{{ $payment_method->display_name }}
																				</option>
																				@endif
																			@endforeach
																		</select>
																	</div>
																</div>
															</td>
															<td style="text-align: right; padding-right: 25px;">
																<p style="margin-top: 7px;">
																	<strong>{{ t('Payable Amount') }} :
																		@if ($packages->get(0)->currency->in_left == 1) <span
																				class="priceCurr">{{ $packages->get(0)->currency->symbol }}</span> @endif
																		<span id="payableAmount">0</span>
																		@if ($packages->get(0)->currency->in_left == 0) <span
																				class="priceCurr">{{ $packages->get(0)->currency->symbol }}</span> @endif
																	</strong>
																</p>
															</td>
														</tr>

													</table>
												</div>
											</div>
										
											@if (isset($payment_methods) and $payment_methods->count() > 0)
												<!-- Payment Plugins -->
												<?php $hasCcBox = 0; ?>
												@foreach($payment_methods as $payment_method)
													@if (view()->exists('payment::' . $payment_method->name))
														@include('payment::' . $payment_method->name)
													@endif
													<?php if ($payment_method->has_ccbox == 1 && $hasCcBox == 0) $hasCcBox = 1; ?>
												@endforeach
											@endif
										@endif

                                        
										@if (config('settings.activation_recaptcha'))
                                            <!-- Captcha -->
											<div class="form-group required <?php echo ($errors->has('g-recaptcha-response')) ? 'has-error' : ''; ?>">
												<label class="col-md-3 control-label" for="g-recaptcha-response"></label>
												<div class="col-md-8">
													{!! Recaptcha::render(['lang' => $lang->get('abbr')]) !!}
												</div>
											</div>
										@endif

										<!-- Terms -->
										<div class="form-group required <?php echo ($errors->has('term')) ? 'has-error' : ''; ?>">
											<label class="col-md-3 control-label"></label>
											<div class="col-md-8">
												<label class="checkbox-inline" for="term-0" style="margin-left: -20px;">
													{!! t('By continuing on this website, you accept our <a href=":url">Terms of Use</a>', ['url' => getUrlPageByType('terms')]) !!}
												</label>
											</div>
										</div>

										<!-- Button  -->
										<div class="form-group">
											<label class="col-md-3 control-label"></label>
											<div class="col-md-8">
												<button id="submitAdForm" class="btn btn-success btn-lg submitAdForm"> {{ t('Submit') }} </button>
											</div>
										</div>

										<div style="margin-bottom: 30px;"></div>

									</fieldset>
								</form>


							</div>
						</div>
					</div>
				</div>
				<!-- /.page-content -->

				<div class="col-md-3 reg-sidebar">
					<div class="reg-sidebar-inner text-center">
						<div class="promo-text-box"><i class=" icon-picture fa fa-4x icon-color-1"></i>
							<h3><strong>{{ t('Post a Job') }}</strong></h3>
							<p>
								{{ t('Do you have a post to be filled within your company? Find the right candidate in a few clicks at :app_name', ['app_name' => getDomain()]) }}
							</p>
						</div>

						<div class="panel sidebar-panel">
							<div class="panel-heading uppercase">
								<small><strong>{{ t('How to sell quickly?') }}</strong></small>
							</div>
							<div class="panel-content">
								<div class="panel-body text-left">
									<ul class="list-check">
										<li> {{ t('Use a brief title and description of the item') }} </li>
										<li> {{ t('Make sure you post in the correct category') }}</li>
										<li> {{ t('Add a logo to your ad') }}</li>
										<li> {{ t('Put a min and max salary') }}</li>
										<li> {{ t('Check the item before publish') }}</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('after_styles')
    @include('layouts.inc.tools.wysiwyg.css')
@endsection

@section('after_scripts')
    @include('layouts.inc.tools.wysiwyg.js')
    
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.13.1/jquery.validate.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery.payment/1.2.3/jquery.payment.min.js"></script>
	@if (file_exists(public_path() . '/assets/plugins/forms/validation/localization/messages_'.$lang->get('abbr').'.min.js'))
		<script src="{{ url('/assets/plugins/forms/validation/localization/messages_'.$lang->get('abbr').'.min.js') }}" type="text/javascript"></script>
	@endif
    
	<script src="{{ url('/assets/js/fileinput.min.js') }}" type="text/javascript"></script>
	@if (file_exists(public_path() . '/assets/js/fileinput_locale_'.$lang->get('abbr').'.js'))
		<script src="{{ url('/assets/js/fileinput_locale_'.$lang->get('abbr').'.js') }}" type="text/javascript"></script>
	@endif
	<script language="javascript">
		/* initialize with defaults (logo) */
		$('#logo').fileinput(
		{
			'showPreview': true,
			'allowedFileExtensions': {!! getUploadFileTypes('image', true) !!},
			'browseLabel': '{!! t("Browse") !!}',
			'showUpload': false,
			'showRemove': false,
			'maxFileSize': {{ (int)config('settings.upload_max_file_size', 1000) }}
		});
	</script>
	<script language="javascript">
		var countries = <?php echo (isset($countries)) ? $countries->toJson() : '{}'; ?>;
		var countryCode = '<?php echo old('country', ($country) ? $country->get('code') : 0); ?>';
		var lang = {
			'select': {
				'category': "{{ t('Select a category') }}",
				'country': "{{ t('Select a country') }}",
				'loc': "{{ t('Select a location') }}",
				'subLocation': "{{ t('Select a sub-location') }}",
				'city': "{{ t('Select a city') }}"
			},
			'price': "{{ t('Price') }}",
			'salary': "{{ t('Salary') }}"
		};
		var loc = '<?php echo old('location', 0); ?>';
		var subLocation = '<?php echo old('sub_location', 0); ?>';
		var city = '<?php echo old('city', 0); ?>';
		var hasChildren = '<?php echo old('has_children'); ?>';

		$(document).ready(function ()
        {
			/* Set Country Phone Code */
			setCountryPhoneCode(countryCode, countries);
			$('#country').change(function () {
				setCountryPhoneCode($(this).val(), countries);
			});
    
            @if (isset($packages) and isset($payment_methods) and $packages->count() > 0 and $payment_methods->count() > 0)
                /* Show price & Payment Methods */
                var selectedPackage = $('input[name=package]:checked').val();
                var packagePrice = getPackagePrice(selectedPackage);
                showAmount(packagePrice);
                $('.package-selection').click(function () {
                    selectedPackage = $(this).val();
                    packagePrice = getPackagePrice(selectedPackage);
                    showAmount(packagePrice);
                });
                
                /* Form Default Submission */
                $('#submitAdForm').on('click', function (e) {
                    e.preventDefault();
            
                    if (packagePrice <= 0) {
                        $('#adForm').submit();
                    }
                    
                    return false;
                });
            @endif
		});

	</script>
	<script src="{{ url('/assets/js/app/d.select.location.js?time=' . time()) }}"></script>
@endsection
