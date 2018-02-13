<!-- this (.mobile-filter-sidebar) part will be position fixed in mobile version -->
<?php
	$fullUrl = url(\Illuminate\Support\Facades\Request::getRequestUri());
    $tmpExplode = explode('?', $fullUrl);
    $fullUrlNoParams = current($tmpExplode);

	$inputAdType = [];
	if (\Illuminate\Support\Facades\Input::has('type')) {
		foreach (\Illuminate\Support\Facades\Input::get('type') as $type) {
			$inputAdType[] = $type;
		}
	}
?>
<div class="col-sm-3 page-sidebar mobile-filter-sidebar">
	<aside>
		<div class="inner-box">
			<div class="list-filter">
				<h5 class="list-title"><strong><a href="#"> {{ t('Date Posted') }} </a></strong></h5>
				<div class="filter-date filter-content">
					<ul>
						@if (isset($dates) and !empty($dates))
							@foreach($dates as $key => $value)
							<li>
								<input type="radio" name="postedDate" value="{{ $key }}" id="postedDate_{{ $key }}" {{ (\Illuminate\Support\Facades\Input::get('postedDate')==$key) ? 'checked="checked"' : '' }}>
								<label for="postedDate_{{ $key }}">{{ $value }}</label>
							</li>
							@endforeach
						@endif
						<?php
						$postedQueryString = '';
						foreach(Request::except(['postedDate']) as $key => $value) {
							if ($postedQueryString != '') {
								$postedQueryString .= '&';
							}
							if (is_array($value)) {
								$tmpQString = '';
								foreach ($value as $k => $v) {
									if ($tmpQString != '') {
										$tmpQString .= '&';
									}
									$tmpQString .= $key . '[' . $k . ']=' . $v;
								}
								$postedQueryString .= $tmpQString;
							} else {
								$postedQueryString .= $key . '=' . $value;
							}
						}
						?>
						<input type="hidden" id="postedQueryString" value="{{ $postedQueryString }}">
					</ul>
				</div>
			</div>
			<!--/.date-posted-->

			<div class="list-filter">
				<h5 class="list-title"><strong><a href="#">{{ t('Job Type') }}</a></strong></h5>
				<div class="filter-content filter-employment-type">
					<ul id="blocAdType" class="browse-list list-unstyled">
						@if (isset($ad_types) and !empty($ad_types))
							@foreach($ad_types as $key => $ad_type)
								<li>
									<input type="checkbox" name="type[{{ $key }}]" id="employment_{{ $ad_type->tid }}" value="{{ $ad_type->tid }}" class="emp emp-type" {{ (in_array($ad_type->tid,  $inputAdType)) ? 'checked="checked"' : '' }}>
									<label for="employment_{{ $ad_type->tid }}">{{ $ad_type->name }}</label>
								</li>
							@endforeach
						@endif
						<?php
						$adTypeQueryString = '';
						foreach(Request::except(['type']) as $key => $value) {
							if ($adTypeQueryString != '') {
								$adTypeQueryString .= '&';
							}
							if (is_array($value)) {
								$tmpQString = '';
								foreach ($value as $k => $v) {
									if ($tmpQString != '') {
										$tmpQString .= '&';
									}
									$tmpQString .= $key . '[' . $k . ']=' . $v;
								}
								$adTypeQueryString .= $tmpQString;
							} else {
								$adTypeQueryString .= $key . '=' . $value;
							}
						}
						?>
						<input type="hidden" id="adTypeQueryString" value="{{ $adTypeQueryString }}">
					</ul>
				</div>
			</div>
			<!--/.ad-type-->

			<div class="list-filter">
				<h5 class="list-title"><strong><a href="#">{{ t('Salary Pay Range') }}</a></strong></h5>
				<div class="filter-salary filter-content ">
					<form role="form" class="form-inline" action="{{ $fullUrlNoParams }}" method="GET">
						<?php $i = 0; ?>
						@foreach(Request::except(['minSalary', 'maxSalary', '_token']) as $key => $value)
							@if (is_array($value))
								@foreach($value as $k => $v)
									<input type="hidden" name="{{ $key.'['.$k.']' }}" value="{{ $v }}">
								@endforeach
							@else
								<input type="hidden" name="{{ $key }}" value="{{ $value }}">
							@endif
						@endforeach
						{!! csrf_field() !!}
						<div class="form-group col-sm-4 no-padding">
							<input type="text" id="maxSalary" name="maxSalary" value="{{ Request::get('maxSalary') }}" placeholder="2000" class="form-control">
						</div>
						<div class="form-group col-sm-1 no-padding text-center hidden-xs"> -</div>
						<div class="form-group col-sm-4 no-padding">
							<input type="text" id="maxSalary" name="maxSalary" value="{{ Request::get('maxSalary') }}" placeholder="4000" class="form-control">
						</div>
						<div class="form-group col-sm-3 no-padding">
							<button class="btn btn-default pull-right btn-block-xs" type="submit">{{ t('GO') }}</button>
						</div>
					</form>

					<div class="clearfix"></div>
				</div>
				<div style="clear:both"></div>
			</div>
			<!--/.list-filter-->


			<div class="locations-list list-filter">
				<h5 class="list-title"><strong><a href="#">{{ t('Specialisms') }}</a></strong></h5>
				<ul class="browse-list list-unstyled long-list">
					@if (!$cats->isEmpty())
					@foreach ($cats->groupBy('parent_id')->get(0) as $cat)
					<li>
						@if (isset($uriPathCatSlug) and $uriPathCatSlug == $cat->slug)
							<strong>
								<a href="{{ lurl(trans('routes.v-search-cat', ['countryCode' => $country->get('icode'), 'catSlug' => $cat->slug])) }}" title="{{ $cat->name }}">
									{{ $cat->name }} <span class="count">{{ $count_cat_ads->get($cat->tid)->total or 0 }}</span>
								</a>
							</strong>
						@else
							<a href="{{ lurl(trans('routes.v-search-cat', ['countryCode' => $country->get('icode'), 'catSlug' => $cat->slug])) }}" title="{{ $cat->name }}">
								{{ $cat->name }} <span class="count">{{ $count_cat_ads->get($cat->tid)->total or 0 }}</span>
							</a>
						@endif
					</li>
					@endforeach
					@endif
				</ul>
			</div>
			<!--/.list-filter-->

			<div class="locations-list list-filter">
				<h5 class="list-title"><strong><a href="#">{{ t('Location') }}</a></strong></h5>
				<ul class="browse-list list-unstyled long-list">
					@if (isset($cities) and $cities->count() > 0)
					@foreach ($cities as $city)
					<li>
						@if (isset($uriPathCityId) and $uriPathCityId == $city->id)
							<strong>
								<a href="{{ lurl(trans('routes.v-search-location', ['countryCode' => $country->get('icode'), 'city' => slugify($city->name), 'id' => $city->id])) }}" title="{{ $city->name }}">
									{{ $city->name }}
								</a>
							</strong>
						@else
							<a href="{{ lurl(trans('routes.v-search-location', ['countryCode' => $country->get('icode'), 'city' => slugify($city->name), 'id' => $city->id])) }}" title="{{ $city->name }}">
								{{ $city->name }}
							</a>
						@endif
					</li>
					@endforeach
					@endif
				</ul>
			</div>
			<!--/.locations-list-->

			<div style="clear:both"></div>
		</div>

		<!--/.categories-list-->
	</aside>
</div>
<!--/.page-side-bar-->

@section('after_scripts')
	@parent
	<script>
		var baseUrl = '<?php echo $fullUrlNoParams; ?>';

		$(document).ready(function ()
		{
			$('input[type=radio][name=postedDate]').click(function() {
				var postedQueryString = $('#postedQueryString').val();
				if (postedQueryString != '') {
					postedQueryString = postedQueryString + '&';
				}
				postedQueryString = postedQueryString + 'postedDate=' + $(this).val();

				var searchUrl = baseUrl + '?' + postedQueryString;
				window.location.replace(searchUrl);
				window.location.href = searchUrl;
			});

			$('#blocAdType input[type=checkbox]').click(function() {
				var adTypeQueryString = $('#adTypeQueryString').val();

				if (adTypeQueryString != '') {
					adTypeQueryString = adTypeQueryString + '&';
				}
				var tmpQString = '';
				$('#blocAdType input[type=checkbox]:checked').each(function(){
					if (tmpQString != '') {
						tmpQString = tmpQString + '&';
					}
					tmpQString = tmpQString + 'type[]=' + $(this).val();
				});
				adTypeQueryString = adTypeQueryString + tmpQString;

				var searchUrl = baseUrl + '?' + adTypeQueryString;
				window.location.replace(searchUrl);
				window.location.href = searchUrl;
			});
		});

		function rawurlencode(str) {
			str = (str + '').toString();
			return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').replace(/\)/g, '%29').replace(/\*/g, '%2A');
		}
	</script>
@endsection