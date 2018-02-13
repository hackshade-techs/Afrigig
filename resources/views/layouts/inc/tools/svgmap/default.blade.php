@if (config('settings.show_country_svgmap'))
	@if (file_exists(public_path('images/maps/' . strtolower($country->get('code')) . '.svg')))

		<div id="countrymap" class="col-sm-3 page-sidebar col-thin-left">&nbsp;</div>

		@section('after_scripts')
			@parent
			<script src="{{ url('assets/plugins/twism/jquery.twism.js') }}"></script>
			<script>
				$(document).ready(function () {
					/* SVG Maps */
					@if(config('settings.show_country_svgmap'))
						$('#countrymap').twism("create",
						{
							map: "custom",
							customMap: '<?php echo 'images/maps/' . strtolower($country->get('code')) . '.svg'; ?>',
							backgroundColor: 'transparent',
							border: '#7324bc',
							hoverBorder: "#7324bc",
							borderWidth: 4,
							color: '#e3d7ef',
							width: '300px',
							height: '300px',
							click: function(region) {
								if (typeof region == "undefined") {
									return false;
								}
								region = rawurlencode(region);
								var searchPage = '<?php echo lurl(trans('routes.t-search')); ?>';
								window.location.replace(searchPage + '?r=' + region);
								window.location.href = searchPage + '?r=' + region;
							},
							hover: function(region_id) {
								if (typeof region_id == "undefined") {
									return false;
								}
								var selectedIdObj = document.getElementById(region_id);
								if (typeof selectedIdObj == "undefined") {
									return false;
								}
								selectedIdObj.style.fill = '#7324bc';
								return;
							},
							unhover: function(region_id) {
								if (typeof region_id == "undefined") {
									return false;
								}
								var selectedIdObj = document.getElementById(region_id);
								if (typeof selectedIdObj == "undefined") {
									return false;
								}
								selectedIdObj.style.fill = '#e3d7ef';
								return;
							}
						});
					@endif
				});

				function rawurlencode(str) {
					str = (str + '').toString();
					return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').replace(/\)/g, '%29').replace(/\*/g, '%2A');
				}
			</script>
		@endsection

	@endif
@endif