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
<?php
	$fullUrl = url(\Illuminate\Support\Facades\Request::getRequestUri());
    $tmpExplode = explode('?', $fullUrl);
    $fullUrlNoParams = current($tmpExplode);
?>
@extends('layouts.master')

@section('search')
	@parent
	@include('search.inc.form')
	@include('search.inc.breadcrumbs', [
		'city' 		=> (isset($city) and !is_null($city)) ? $city : null,
		'cat' 		=> (isset($cat) and !is_null($cat)) ? $cat : null,
		'sub_cat' 	=> (isset($sub_cat) and !is_null($sub_cat)) ? $sub_cat : null
	])
	@include('layouts.inc.advertising.top')
@endsection

@section('content')
	<div class="main-container">
		<div class="container">

			<div class="row">
				@if (Session::has('flash_notification.message'))
					<div class="container" style="margin-bottom: -10px; margin-top: -10px;">
						<div class="row">
							<div class="col-lg-12">
								@include('flash::message')
							</div>
						</div>
					</div>
				@endif
			</div>

			<div class="row">

				@include('search.inc.ads-sidebar')

				<div class="col-sm-9 page-content col-thin-left">

					<div class="category-list">
						<div class="tab-box clearfix ">

							<!-- Nav tabs -->
							<div class="col-lg-12  box-title no-border">
								<div class="inner">
									<h2>
										<small>{{ $count->get('all') }} {{ t('Jobs Found') }}</small>
									</h2>
								</div>
							</div>

							<!-- Mobile Filter bar -->
							<div class="mobile-filter-bar col-lg-12  ">
								<ul class="list-unstyled list-inline no-margin no-padding">
									<li class="filter-toggle">
										<a class="">
											<i class="icon-th-list"></i>
											Filters
										</a>
									</li>
									<li>
										<div class="dropdown">
											<a data-toggle="dropdown" class="dropdown-toggle"><i class="caret "></i>{{ t('Sort by') }}</a>
											<ul class="dropdown-menu">
												<li><a href="{!! qsurl($fullUrlNoParams, Request::except(['orderBy', 'distance'])) !!}" rel="nofollow">{{ t('Sort by') }}</a></li>
												<li><a href="{!! qsurl($fullUrlNoParams, array_merge(Request::except('orderBy'), ['orderBy'=>'relevance'])) !!}" rel="nofollow">{{ t('Relevance') }}</a></li>
												<li><a href="{!! qsurl($fullUrlNoParams, array_merge(Request::except('orderBy'), ['orderBy'=>'date'])) !!}" rel="nofollow">{{ t('Date') }}</a></li>
												@if ($isLocSearch or Request::has('l') or Request::has('r'))
													<li><a href="{!! qsurl($fullUrlNoParams, array_merge(Request::except('distance'), ['distance'=>100])) !!}" rel="nofollow">{{ t('Around') . ' 100 km' }}</a></li>
													<li><a href="{!! qsurl($fullUrlNoParams, array_merge(Request::except('distance'), ['distance'=>300])) !!}" rel="nofollow">{{ t('Around') . ' 300 km' }}</a></li>
													<li><a href="{!! qsurl($fullUrlNoParams, array_merge(Request::except('distance'), ['distance'=>500])) !!}" rel="nofollow">{{ t('Around') . ' 500 km' }}</a></li>
												@endif
											</ul>

										</div>
									</li>
								</ul>
							</div>
							<div class="menu-overly-mask"></div>
							<!-- Mobile Filter bar End-->


							<div class="tab-filter hide-xs" style="padding-top: 6px; padding-right: 6px;">
								<select id="order_by" class="selecter" data-style="btn-select" data-width="auto">
									<option value="{!! qsurl($fullUrlNoParams, Request::except(['orderBy', 'distance'])) !!}">{{ t('Sort by') }}</option>
									<option{{ (Request::get('orderBy')=='relevance') ? ' selected="selected"' : '' }} value="{!! qsurl($fullUrlNoParams, array_merge(Request::except('orderBy'), ['orderBy'=>'relevance'])) !!}">{{ t('Relevance') }}</option>
									<option{{ (Request::get('orderBy')=='date') ? ' selected="selected"' : '' }} value="{!! qsurl($fullUrlNoParams, array_merge(Request::except('orderBy'), ['orderBy'=>'date'])) !!}">{{ t('Date') }}</option>
									@if ($isLocSearch or Request::has('l') or Request::has('r'))
										<option{{ (Request::get('distance')==100) ? ' selected="selected"' : '' }} value="{!! qsurl($fullUrlNoParams, array_merge(Request::except('distance'), ['distance'=>100])) !!}">{{ t('Around') . ' 100 km' }}</option>
										<option{{ (Request::get('distance')==300) ? ' selected="selected"' : '' }} value="{!! qsurl($fullUrlNoParams, array_merge(Request::except('distance'), ['distance'=>300])) !!}">{{ t('Around') . ' 300 km' }}</option>
										<option{{ (Request::get('distance')==500) ? ' selected="selected"' : '' }} value="{!! qsurl($fullUrlNoParams, array_merge(Request::except('distance'), ['distance'=>500])) !!}">{{ t('Around') . ' 500 km' }}</option>
									@endif
								</select>
							</div>
							<!--/.tab-filter-->

						</div>
						<!--/.tab-box-->

						<div class="listing-filter hidden-xs">
							<div class="pull-left col-sm-6 col-xs-12">
								<div class="breadcrumb-list text-center-xs">
									{{ t('All jobs') }}
									@if ($isLocSearch or Request::has('l') or Request::has('r'))
										{{ t('in') }}
										<?php
										if (Request::has('l') or Request::has('r')) {
											$searchUrl = qsurl($fullUrlNoParams, Request::except(['l', 'r', 'location']));
										} else {
											$searchUrl = lurl(trans('routes.v-search', ['countryCode' => $country->get('icode')]));
										}
										?>
										<a rel="nofollow" class="jobs-s-tag" href="{!! $searchUrl !!}">
											{!! t(':distance km around :city', ['distance' => \App\Helpers\Search::$distance, 'city' => $city->name]) !!}
										</a>
									@endif
									@if ($isCatSearch or Request::has('c'))
										@if (isset($cat))
											{{ t('in') }}
											<?php
											if (Request::has('c')) {
												$searchUrl = qsurl($fullUrlNoParams, Request::except(['c']));
											} else {
												$searchUrl = lurl(trans('routes.v-search', ['countryCode' => $country->get('icode')]));
											}
											?>
											<a rel="nofollow" class="jobs-s-tag" href="{!! $searchUrl !!}">
												{!! $cat->name !!}
											</a>
										@endif
									@endif
									@if ($isCompanySearch)
										{{ t('at') }}
										<a rel="nofollow" class="jobs-s-tag" href="{!! lurl(trans('routes.v-search', ['countryCode' => $country->get('icode')])) !!}">
											{!! $uriPathCompanyName !!}
										</a>
									@endif
									@if (Request::has('postedDate') and isset($dates->{Request::get('postedDate')}))
										<a rel="nofollow" class="jobs-s-tag" href="{!! qsurl($fullUrlNoParams, Request::except(['postedDate'])) !!}">
											{!! $dates->{Request::get('postedDate')} !!}
										</a>
									@endif
									@if (Request::has('type'))
										@if (is_array(Request::get('type')))
											@foreach(Request::get('type') as $key => $value)
												<?php
												$jobType = \App\Models\AdType::transById($value);
												?>
												@if (!empty($jobType))
													<a rel="nofollow" class="jobs-s-tag" href="{!! qsurl($fullUrlNoParams, Request::except(['type.'.$key])) !!}">
														{!! $jobType->name !!}
													</a>
												@endif
											@endforeach

										@else
											<?php
											$jobType = \App\Models\AdType::transById(Request::get('type'));
											?>
											@if (!empty($jobType))
												<a rel="nofollow" class="jobs-s-tag" href="{!! qsurl($fullUrlNoParams, Request::except(['type'])) !!}">
													{!! $jobType->name !!}
												</a>
											@endif
										@endif
									@endif
								</div>
							</div>
							<div class="pull-right col-sm-6 col-xs-12 text-right text-center-xs listing-view-action">
								@if (!empty(\Illuminate\Support\Facades\Input::all()))
									<a class="clear-all-button text-muted" href="{!! lurl(trans('routes.v-search', ['countryCode' => $country->get('icode')])) !!}">{{ t('Clear all') }}</a>
								@endif
							</div>
							<div style="clear:both;"></div>
						</div>
						<!--/.listing-filter-->

						<div class="adds-wrapper jobs-list">
							@include('search.inc.ads')
						</div>
						<!--/.adds-wrapper-->

						<div class="tab-box save-search-bar text-center">
							@if (Request::has('q') and Request::get('q') != '' and $count->get('all') > 0)
								<a name="{!! qsurl($fullUrlNoParams, Request::except(['_token', 'location'])) !!}" id="save_search" count="{{ $count->get('all') }}">
									<i class=" icon-star-empty"></i> {{ t('Save Search') }}
								</a>
							@else
								<a href="#"> &nbsp; </a>
							@endif
						</div>
					</div>

					<div class="pagination-bar text-center">
						{!! $ads->appends(Request::except('page'))->render() !!}
					</div>
					<!--/.pagination-bar -->

					@if (!\Auth::check())
						<div class="post-promo text-center">
							<h2> {{ t('Looking for a job?') }} </h2>
							<h5> {{ t('Upload your CV and easily apply to jobs from any device!') }} </h5>
							<a href="{{ lurl(trans('routes.signup')) . '?type=3' }}" class="btn btn-lg btn-border btn-post btn-danger">
								{{ t('Upload your CV') }}
							</a>
						</div>
						<!--/.post-promo-->
					@endif

				</div>
				<!--/.page-content-->

				<!-- Advertising -->
				@include('layouts.inc.advertising.bottom')

			</div>

		</div>
	</div>
@endsection

@section('modal_location')
	@parent
	@include('layouts.inc.modal.location')
@endsection

@section('after_scripts')
	<script>
		var stateId = '<?php echo (isset($city)) ? $country->get('code').'.'.$city->subadmin1_code : '0' ?>';
		$(document).ready(function () {
			$('#ad_type a').click(function (e) {
				e.preventDefault();
				var goToUrl = $(this).attr('href');
				window.location.replace(goToUrl);
				window.location.href = goToUrl;
			});
			$('#order_by').change(function () {
				var goToUrl = $(this).val();
				window.location.replace(goToUrl);
				window.location.href = goToUrl;
			});
		});
	</script>
	<script type="text/javascript" src="{{ url('assets/js/app/load.cities.js') }}"></script>
@endsection
