<div class="row" style="margin-bottom: 30px;">
	<div class="col-lg-12 page-content">
		<div>
			<div class="col-sm-8 page-content no-margin no-padding">
				@if (isset($cities))
					<div class="relative" style="text-align: center;">
						<div class="row" style="padding-top: 30px; padding-bottom: 30px; text-align: left;">
							<div class="col-md-12">
								<div>
									<h2 class="title-3">
										<i class="icon-location-2"></i>&nbsp;
										{{ t('Choose a city') }}
									</h2>
									<div class="row" style="padding: 0 10px 0 20px;">
										@foreach ($cities as $key => $items)
											<ul class="cat-list col-xs-4 {{ (count($cities) == $key+1) ? 'cat-list-border' : '' }}">
												@foreach ($items as $k => $city)
													<li>
														@if ($city->id == 999999999)
															<a href="#selectRegion" id="dropdownMenu1" data-toggle="modal">{{ $city->name }}</a>
														@else
															<a href="{{ lurl(trans('routes.v-search-location',
															[
															'countryCode' => $country->get('icode'),
															'city' => slugify($city->name),
															'id' => $city->id
															])) }}">
																{{ $city->name }}
															</a>
														@endif
													</li>
												@endforeach
											</ul>
										@endforeach
									</div>
								</div>
							</div>
						</div>

						@if (!auth()->user())
							<a class="btn btn-lg btn-yellow" href="{{ lurl(trans('routes.signup')) . '?type=3' }}" style="padding-left: 30px; padding-right: 30px; text-transform: none;">
								Become a Freelancer Signup
							</a>
						@else
							@if (in_array($user->user_type_id, [1, 2]))
								<a class="btn btn-lg btn-yellow" href="{{ lurl(trans('routes.create')) }}" style="padding-left: 30px; padding-right: 30px; text-transform: none;">
									{{ t('Post a Job') }}
								</a>
							@endif
						@endif

					</div>
				@endif
			</div>

			<?php
			$theme = 'default';
			if (config('app.theme')) {
				if (file_exists(base_path() . '/resources/views/layouts/inc/tools/svgmap/' . config('app.theme') . '.blade.php')) {
					$theme = config('app.theme');
				}
			}
			?>
			@include('layouts.inc.tools.svgmap.' . $theme)
		</div>
	</div>
</div>
