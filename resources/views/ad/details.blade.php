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

<?php
// Phone
$contact_phone = TextToImage::make($ad->contact_phone, IMAGETYPE_PNG, ['backgroundColor' => '#2ECC71', 'color' => '#FFFFFF']);
?>

@section('content')
	{!! csrf_field() !!}
	<input type="hidden" id="ad_id" value="{{ $ad->id }}">
	<div class="main-container">

		@if (Session::has('flash_notification.message'))
			<div class="container" style="margin-bottom: -10px; margin-top: -10px;">
				<div class="row">
					<div class="col-lg-12">
						@include('flash::message')
					</div>
				</div>
			</div>
			<?php Session::forget('flash_notification.message'); ?>
		@endif

		@include('layouts.inc.advertising.top')

		<div class="container">
			<ol class="breadcrumb pull-left">
				<li><a href="{{ lurl('/') }}"><i class="icon-home fa"></i></a></li>
				<li><a href="{{ lurl('/') }}">{{ $country->get('name') }}</a></li>
				<li>
					<a href="{{ lurl(trans('routes.v-search-cat', ['countryCode' => $country->get('icode'), 'catSlug' => $parent_cat->slug])) }}">
						{{ $parent_cat->name }}
					</a>
				</li>
				@if ($parent_cat->id != $cat->id)
				<li>
					<a href="{{ lurl(trans('routes.v-search-subCat',
					[
					'countryCode' => $country->get('icode'),
					'catSlug' => $parent_cat->slug,
					'subCatSlug' => $cat->slug
					])) }}">
						{{ $cat->name }}
					</a>
				</li>
				@endif
				<li class="active">{{ str_limit($ad->title, 70) }}</li>
			</ol>
			<div class="pull-right backtolist">
				<a href="{{ URL::previous() }}">
					<i class="fa fa-angle-double-left"></i> {{ t('Back to Results') }}
				</a>
			</div>
		</div>
		<div class="container">
			<div class="row">
				<div class="col-sm-9 page-content col-thin-right">
					<div class="inner inner-box ads-details-wrapper">
						<h2 class="enable-long-words">
							<strong>
                                <a href="{{ lurl(slugify($ad->title).'/'.$ad->id.'.html') }}" title="{{ mb_ucfirst($ad->title) }}">
                                    {{ mb_ucfirst($ad->title) }}
                                </a>
                            </strong>
							<small class="label label-default adlistingtype">{{ t(':type Job', ['type' => $adType->name]) }}</small>
                            @if ($ad->featured==1 and isset($package) and !empty($package))
                                <small class="label label-primary adlistingtype"><i class="icon-star-circled"></i> {{ $package->short_name }}</small>
                            @endif
						</h2>
						<span class="info-row">
							<span class="date"><i class=" icon-clock"> </i> {{ $ad->created_at_ta }} </span> -&nbsp;
							<span class="category">{{ $parent_cat->name }}</span> -&nbsp;
							<span class="item-location"><i class="fa fa-map-marker"></i> {{ $ad->city->name }} </span> -&nbsp;
							<span class="category"><i class="icon-eye-3"></i> {{ $ad->visits }} {{ trans_choice('global.count_views', getPlural($ad->visits)) }}</span>
						</span>

						<div class="Ads-Details">
							<div class="row" style="padding-bottom: 20px;">
								<div class="ads-details-info jobs-details-info col-md-8 enable-long-words from-wysiwyg">
									<h5 class="list-title"><strong>{{ t('Job Details') }}</strong></h5>
                                    <div>
                                        @if (config('settings.simditor_wysiwyg') || config('settings.ckeditor_wysiwyg'))
                                            {!! auto_link(\Mews\Purifier\Facades\Purifier::clean($ad->description)) !!}
                                        @else
                                            {!! nl2br(auto_link(str_clean($ad->description))) !!}
                                        @endif
                                    </div>
									@if (!empty($ad->company_description))
										<div style="margin-bottom: 50px;"></div>
										<h5 class="list-title"><strong>{{ t('Company Description') }}</strong></h5>
                                        <div>
										    {!! nl2br(auto_link(str_clean($ad->company_description))) !!}
                                        </div>
									@endif
								</div>
								<div class="col-md-4">
									<aside class="panel panel-body panel-details job-summery">
										<ul>
											@if (!empty($ad->start_date))
											<li>
												<p class="no-margin">
													<strong>{{ t('Start Date') }}:</strong>&nbsp;
													{{ $ad->start_date }}
												</p>
											</li>
											@endif
											<li>
												<p class="no-margin">
													<strong>{{ t('Company') }}:</strong>&nbsp;
													<a href="{!! lurl(trans('routes.v-search-company', ['companyName' => $ad->company_name])) !!}">
														{{ $ad->company_name }}
													</a>
												</p>
											</li>
											<li>
												<p class=" no-margin">
													<strong>{{ t('Salary') }}:</strong>&nbsp;
													@if ($ad->salary_max > 0)
														{!! \App\Helpers\Number::money($ad->salary_max) !!}
													@else
														{!! \App\Helpers\Number::money('--') !!}
													@endif
													@if ($ad->negotiable == 1)
														<small class="label label-success"> {{ t('Negotiable') }}</small>
													@endif
												</p>
											</li>
											<li>
												<p class="no-margin">
													<strong>{{ t('Job Type') }}:</strong>&nbsp;
													<a href="{{ lurl(trans('routes.t-search')) . '?type[]=' . $ad->ad_type_id }}">
														{{ \App\Models\AdType::find($ad->ad_type_id)->name }}
													</a>
												</p>
											</li>
											<li>
												<p class="no-margin">
													<strong>{{ t('Location') }}:</strong>&nbsp;
													<a href="{!! lurl(trans('routes.v-search-location', ['countryCode' => $country->get('icode'), 'city' => slugify($ad->city->name), 'id' => $ad->city->id])) !!}">
														{{ $ad->city->name }}
													</a>
												</p>
											</li>
										</ul>
									</aside>
									<div class="ads-action">
										<ul class="list-border">
											@if (isset($ad->user) and $ad->user->id != 1)
												<li>
													<a href="{{ lurl(trans('routes.v-search-user', ['countryCode' => $country->get('icode'), 'id' => $ad->user->id])) }}">
														<i class="fa fa-user"></i> {{ t('More jobs by Company') }}
													</a>
												</li>
											@endif
											<li id="{{ $ad->id }}">
												<a class="make-favorite">
												@if (Auth::check())
													@if (\App\Models\SavedAd::where('user_id', $user->id)->where('ad_id', $ad->id)->count() > 0)
														<i class="fa fa-heart"></i> {{ t('Remove favorite') }}
													@else
														<i class="fa fa-heart"></i> {{ t('Save ad') }}
													@endif
												@else
													<i class="fa fa-heart"></i> {{ t('Save ad') }}
												@endif
                                                </a>
											</li>
											<!--<li><a href="#"> <i class="fa fa-share-alt"></i> {{ t('Share ad') }} </a> </li>-->
											<li>
												<a href="#report_abuse" data-toggle="modal">
													<i class="fa icon-info-circled-alt"></i> {{ t('Report abuse') }}
												</a>
											</li>
										</ul>
									</div>
								</div>

								<br>&nbsp;<br>
							</div>
							<div class="content-footer text-left">
								@if (Auth::check())
									@if ($user->id == $ad->user_id)
										<a class="btn btn-default" href="{{ lurl('update/'.$ad->id) }}">
											<i class="icon-pencil-2"></i> {{ t('Update') }}
										</a>
									@else
										@if ($ad->contact_email != '')
											<a class="btn btn-default" data-toggle="modal" href="#applyJob">
												<i class=" icon-mail-2"></i> {{ t('Apply Online') }}
											</a>
										@endif
									@endif
								@else
									@if ($ad->contact_email != '')
										<a class="btn btn-default" data-toggle="modal" href="#applyJob">
											<i class=" icon-mail-2"></i> {{ t('Apply Online') }}
										</a>
									@endif
								@endif
								@if ($ad->contact_phone_hidden != 1 and !empty($ad->contact_phone))
									<a href="tel:{{ $ad->contact_phone }}" class="btn btn-success showphone">
										<i class="icon-phone-1"></i>
										{!! $contact_phone !!}{{-- t('View phone') --}}
									</a>
								@endif
							</div>
						</div>

						@if (config('settings.show_ad_on_googlemap'))
							<div class="Ads-OnGoogleMaps" style="margin-bottom: 10px;">
								<h5 class="list-title"><strong>{{ t('Location') }}</strong></h5>
								<iframe id="googleMaps" width="100%" height="170" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src=""></iframe>
							</div>
						@endif
					</div>
					<!--/.ads-details-wrapper-->
				</div>
				<!--/.page-content-->

				<div class="col-sm-3 page-sidebar-right">
					<aside>
						<div class="panel sidebar-panel panel-contact-seller">
							<div class="panel-heading">{{ t('Company Information') }}</div>
							<div class="panel-content user-info">
								<div class="panel-body text-center">
									<div class="seller-info">
										<div class="company-logo-thumb">
											<a>
												<img alt="Logo {{ $ad->company_name }}" class="img-responsive" src="{{ resize($ad->logo, 'medium') }}">
											</a>
										</div>

										@if (isset($ad->company_name) and $ad->company_name != '')
											@if (isset($ad->user) and $ad->user->id != 1)
												<h3 class="no-margin">
													<a href="{{ lurl(trans('routes.v-search-user', ['countryCode' => $country->get('icode'), 'id' => $ad->user->id])) }}">
														{{ $ad->company_name }}
													</a>
												</h3>
											@else
												<h3 class="no-margin">{{ $ad->company_name }}</h3>
											@endif
										@endif
										<p>
											{{ t('Location') }}:&nbsp;
											<strong>
												<a href="{!! lurl(trans('routes.v-search-location', ['countryCode' => $country->get('icode'), 'city' => slugify($ad->city->name), 'id' => $ad->city->id])) !!}">
													{{ $ad->city->name }}
												</a>
											</strong>
										</p>
										@if ($ad->user and !empty($ad->user->created_at_ta))
											<p> {{ t('Joined') }}: <strong>{{ $ad->user->created_at_ta }}</strong></p>
										@endif
										@if (!empty($ad->company_website))
											<p>
												{{ t('Web') }}:
												<strong>
													<a href="{{ $ad->company_website }}" target="_blank" rel="nofollow">
														{{ getHostByUrl($ad->company_website) }}
													</a>
												</strong>
											</p>
										@endif
									</div>
									<div class="user-ads-action">
										@if (Auth::check())
											@if ($user->id == $ad->user_id)
												<a href="{{ lurl('update/'.$ad->id) }}" data-toggle="modal" class="btn btn-default btn-block">
													<i class=" icon-pencil-2"></i> {{ t('Update') }}
												</a>
											@else
												@if ($ad->contact_email != '')
													<a href="#applyJob" data-toggle="modal" class="btn btn-default btn-block">
														<i class=" icon-mail-2"></i> {{ t('Apply Online') }}
													</a>
												@endif
											@endif
										@else
											@if ($ad->contact_email != '')
												<a href="#applyJob" data-toggle="modal" class="btn btn-default btn-block">
													<i class=" icon-mail-2"></i> {{ t('Apply Online') }}
												</a>
											@endif
										@endif
										@if ($ad->contact_phone_hidden != 1 and !empty($ad->contact_phone))
											<a href="tel:{{ $ad->contact_phone }}" class="btn btn-success btn-block showphone">
												<i class=" icon-phone-1"></i>
                                                {!! $contact_phone !!}{{-- t('View phone') --}}
											</a>
										@endif
									</div>
								</div>
							</div>
						</div>

						@include('layouts.inc.social.horizontal')

						<div class="panel sidebar-panel">
							<div class="panel-heading">{{ t('Tips for candidates') }}</div>
							<div class="panel-content">
								<div class="panel-body text-left">
									<ul class="list-check">
										<li> {{ t('Check if the offer matches your profile') }} </li>
                                        <li> {{ t('Check the start date') }} </li>
										<li> {{ t('Meet the employer in a professional location') }} </li>
									</ul>
                                    <?php $tipsUrl = getUrlPageByType('tips'); ?>
                                    @if ($tipsUrl != '#' && $tipsUrl != '')
									<p>
										<a class="pull-right" href="{{ $tipsUrl }}">
											{{ t('Know more') }}
											<i class="fa fa-angle-double-right"></i>
										</a>
									</p>
                                    @endif
								</div>
							</div>
						</div>
					</aside>
				</div>
			</div>

			<div style="margin-top: 30px;"></div>
			@include('layouts.inc.carousel')
			@include('layouts.inc.advertising.bottom')
			@include('layouts.inc.tools.facebook-comments')

		</div>
	</div>
@endsection

@section('modal_abuse')
	@include('ad.inc.modal-abuse')
@endsection

@section('modal_message')
	@include('ad.inc.modal-message')
@endsection

@section('after_scripts')
    @if (config('services.googlemaps.key'))
        <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.googlemaps.key') }}" type="text/javascript"></script>
    @endif
    
	<script src="{{ url('assets/plugins/bxslider/jquery.bxslider.min.js') }}"></script>
	<script>
		var stateId = '<?php echo (isset($city)) ? $country->get('code') . '.' . $city->subadmin1_code : '0' ?>';

		/* JS translation */
		var lang = {
			loginToSaveAd: "@lang('global.Please log in to save the Ads.')",
			loginToSaveSearch: "@lang('global.Please log in to save your search.')",
			confirmationSaveSearch: "@lang('global.Search saved successfully !')",
			confirmationRemoveSaveSearch: "@lang('global.Search deleted successfully !')"
		};

		$('.bxslider').bxSlider({
			pagerCustom: '#bx-pager',
			controls: true,
			nextText: " @lang('global.Next') &raquo;",
			prevText: "&laquo; @lang('global.Previous') "
		});

		$(document).ready(function () {
			@if (count($errors) > 0)
				@if (count($errors) > 0 and old('msg_form')=='1')
					$('#applyJob').modal();
				@endif
				@if (count($errors) > 0 and old('abuse_form')=='1')
					$('#report_abuse').modal();
				@endif
			@endif
			@if (config('settings.show_ad_on_googlemap'))
				genGoogleMaps(
				'<?php echo config('services.googlemaps.key'); ?>',
				'<?php echo (isset($ad->city) and !is_null($ad->city)) ? addslashes($ad->city->name) . ',' . $country->get('name') : $country->get('name') ?>',
				'<?php echo $lang->get('abbr'); ?>'
				);
			@endif
		})
	</script>
	<script src="{{ url('assets/js/form-validation.js') }}"></script>
	<script src="{{ url('assets/js/app/show.phone.js') }}"></script>
	<script type="text/javascript" src="{{ url('assets/js/app/make.favorite.js') }}"></script>
@endsection