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

				@if (Session::has('flash_notification.message'))
					<div class="container" style="margin-bottom: -10px; margin-top: -10px;">
						<div class="row">
							<div class="col-lg-12">
								@include('flash::message')
							</div>
						</div>
					</div>
				@endif

				<div class="col-sm-3 page-sidebar">
					@include('account/inc/sidebar-left')
				</div>
				<!--/.page-sidebar-->

				<div class="col-sm-9 page-content">
					<div class="inner-box">
						@if ($pagePath=='myads')
							<h2 class="title-2"><i class="icon-docs"></i> {{ t('My Ads') }} </h2>
						@elseif ($pagePath=='archived')
							<h2 class="title-2"><i class="icon-folder-close"></i> {{ t('Archived ads') }} </h2>
						@elseif ($pagePath=='favourite')
							<h2 class="title-2"><i class="icon-heart-1"></i> {{ t('Favourite ads') }} </h2>
						@elseif ($pagePath=='pending-approval')
							<h2 class="title-2"><i class="icon-hourglass"></i> {{ t('Pending approval') }} </h2>
						@else
							<h2 class="title-2"><i class="icon-docs"></i> {{ t('Ads') }} </h2>
						@endif

						<div class="table-responsive">
							<form method="POST" action="{{ lurl('account/'.$pagePath.'/delete') }}">
								{!! csrf_field() !!}
								<div class="table-action">
									<label for="checkAll">
										<input type="checkbox" id="checkAll">
										{{ t('Select') }}: {{ t('All') }} |
										<button type="submit" class="btn btn-xs btn-danger">
                                            {{ t('Delete') }} <i class="glyphicon glyphicon-remove"></i>
                                        </button>
									</label>
									<div class="table-search pull-right col-xs-7">
										<div class="form-group">
											<label class="col-xs-5 control-label text-right">{{ t('Search') }} <br>
												<a title="clear filter" class="clear-filter" href="#clear">[{{ t('clear') }}]</a> </label>
											<div class="col-xs-7 searchpan">
												<input type="text" class="form-control" id="filter">
											</div>
										</div>
									</div>
								</div>
								<table id="addManageTable" class="table table-striped table-bordered add-manage-table table demo"
									   data-filter="#filter" data-filter-text-only="true">
									<thead>
									<tr>
										<th data-type="numeric" data-sort-initial="true"></th>
										<th> Photo</th>
										<th data-sort-ignore="true"> {{ t('Adds Details') }} </th>
										<th data-type="numeric"> --</th>
										<th> Option</th>
									</tr>
									</thead>
									<tbody>

									<?php
                                    if (isset($ads) && $ads->count() > 0):
									foreach($ads as $key => $ad):
										// Fixed 1
										if ($pagePath == 'favourite') {
											if (isset($ad->ad)) {
												if (!empty($ad->ad)) {
													$ad = $ad->ad;
												} else {
													continue;
												}
											} else {
												continue;
											}
										}

										// Fixed 2
										if (!$countries->has($ad->country_code)) continue;

										// Ad URL setting
										$adUrl = lurl(slugify($ad->title) . '/' . $ad->id . '.html');

										// Ad City
										if ($ad->city) {
											$city = $ad->city->name;
										} else {
											$city = '-';
										}

                                        // Get Payment Info
                                        $payment = \App\Models\Payment::where('ad_id', $ad->id)->orderBy('id', 'DESC')->first();

                                        // Get Package Info
                                        $package = null;
                                        if (!empty($payment)) {
                                            $package = \App\Models\Package::transById($payment->package_id);
                                        }

										// Get country flag
										$iconPath = 'images/flags/16/' . strtolower($ad->country_code) . '.png';
									?>
									<tr>
										<td style="width:2%" class="add-img-selector">
											<div class="checkbox">
												<label><input type="checkbox" name="ad[]" value="{{ $ad->id }}"></label>
											</div>
										</td>
										<td style="width:14%" class="add-img-td">
											<a href="{{ $adUrl }}">
												<img class="thumbnail img-responsive" src="{{ resize(\App\Models\Ad::getLogo($ad->logo), 'medium') }}" alt="img">
											</a>
										</td>
										<td style="width:58%" class="ads-details-td">
											<div>
												<p>
                                                    <strong>
                                                        <a href="{{ $adUrl }}" title="{{ $ad->title }}">{{ str_limit($ad->title, 40) }}</a>
                                                    </strong>
                                                    @if (isset($package) and !empty($package))
                                                        <?php
                                                        if ($ad->featured == 1) {
                                                            $class = 'text-success';
                                                            $packageInfo = '';
                                                        } else {
                                                            $class = 'text-danger';
                                                            $packageInfo = ' (' . t('Expired') . ')';
                                                        }
                                                        ?>
                                                        <i class="fa fa-check-circle {{ $class }} tooltipHere" title="" data-placement="bottom"
                                                           data-toggle="tooltip" type="button"
                                                           data-original-title="{{ $package->short_name . $packageInfo }}"></i>
                                                    @endif
                                                </p>
												<p>
													<strong> {{ t('Posted On') }} </strong>: {{ $ad->created_at->formatLocalized('%d %B %Y %H:%M') }}
												</p>
												<p>
													<strong> {{ t('Visitors') }} </strong>: {{ $ad->visits or 0 }}
													<strong>{{ t('Located In') }}:</strong> {{ $city }}
													@if (file_exists(public_path($iconPath)))
														<img src="{{ url($iconPath) }}" data-toggle="tooltip" title="{{ $ad->country_code }}">
													@endif
												</p>
											</div>
										</td>
										<td style="width:16%" class="price-td">
											<div>
												<strong>
													{!! \App\Helpers\Number::money($ad->salary_min) !!}
												</strong>
											</div>
										</td>
										<td style="width:10%" class="action-td">
											<div>
												@if ($ad->user_id==$user->id and in_array($ad->active, array(0, 1)) and $ad->archived==0)
													<p>
                                                        <a class="btn btn-primary btn-xs" href="{{ lurl('update/' . $ad->id) }}">
                                                            <i class="fa fa-edit"></i> {{ t('Edit') }}
                                                        </a>
                                                    </p>
												@endif
												@if ($ad->active==1 and $ad->archived==0)
													<!--<p>
														<a class="btn btn-info btn-xs"> <i class="fa fa-mail-forward"></i> {{ t('Share') }} </a>
													</p>-->
												@endif
												@if ($ad->archived==1)
													<p>
                                                        <a class="btn btn-info btn-xs" href="{{ lurl('account/'.$pagePath.'/repost/'.$ad->id) }}">
                                                            <i class="fa fa-recycle"></i> {{ t('Repost') }}
                                                        </a>
                                                    </p>
												@endif
												<p>
                                                    <a class="btn btn-danger btn-xs" href="{{ lurl('account/'.$pagePath.'/delete/'.$ad->id) }}">
                                                        <i class="fa fa-trash"></i> {{ t('Delete') }}
                                                    </a>
                                                </p>
											</div>
										</td>
									</tr>
									<?php endforeach; ?>
                                    <?php endif; ?>
									</tbody>
								</table>
							</form>
						</div>
							
                        <div class="pagination-bar text-center">
                            {{ (isset($ads)) ? $ads->links() : '' }}
                        </div>

					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('after_scripts')
	<script src="{{ url('assets/js/footable.js?v=2-0-1') }}" type="text/javascript"></script>
	<script src="{{ url('assets/js/footable.filter.js?v=2-0-1') }}" type="text/javascript"></script>
	<script type="text/javascript">
		$(function () {
			$('#addManageTable').footable().bind('footable_filtering', function (e) {
				var selected = $('.filter-status').find(':selected').text();
				if (selected && selected.length > 0) {
					e.filter += (e.filter && e.filter.length > 0) ? ' ' + selected : selected;
					e.clear = !e.filter;
				}
			});

			$('.clear-filter').click(function (e) {
				e.preventDefault();
				$('.filter-status').val('');
				$('table.demo').trigger('footable_clear_filter');
			});

			$('#checkAll').click(function () {
				checkAll(this);
			});
		});
	</script>
	<!-- include custom script for ads table [select all checkbox]  -->
	<script>
		function checkAll(bx) {
			var chkinput = document.getElementsByTagName('input');
			for (var i = 0; i < chkinput.length; i++) {
				if (chkinput[i].type == 'checkbox') {
					chkinput[i].checked = bx.checked;
				}
			}
		}
	</script>
@endsection
