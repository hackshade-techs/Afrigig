<?php
	$fullUrl = url(\Illuminate\Support\Facades\Request::getRequestUri());
?>
<!DOCTYPE html>
<html lang="{{ ($lang->has('abbr')) ? $lang->get('abbr') : 'en' }}">
<head>
	<meta charset="utf-8">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	@if (isset($lang) and isset($country) and $country->has('lang'))
		@if ($lang->get('abbr') != $country->get('lang')->get('abbr'))
			<meta name="robots" content="noindex">
			<meta name="googlebot" content="noindex">
		@endif
	@endif
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="apple-mobile-web-app-title" content="{{ config('settings.app_name') }}">
	<link rel="apple-touch-icon-precomposed" sizes="144x144" href="{{ \Storage::url('app/default/ico/apple-touch-icon-144-precomposed.png') }}">
	<link rel="apple-touch-icon-precomposed" sizes="114x114" href="{{ \Storage::url('app/default/ico/apple-touch-icon-114-precomposed.png') }}">
	<link rel="apple-touch-icon-precomposed" sizes="72x72" href="{{ \Storage::url('app/default/ico/apple-touch-icon-72-precomposed.png') }}">
	<link rel="apple-touch-icon-precomposed" href="{{ \Storage::url('app/default/ico/apple-touch-icon-57-precomposed.png') }}">
	<link rel="shortcut icon" href="{{ \Storage::url(config('settings.app_favicon')) }}">
	<title>{{ MetaTag::get('title') }}</title>
	{!! MetaTag::tag('description') !!}
	<link rel="canonical" href="{{ $fullUrl }}"/>
	@foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
		@if (strtolower($localeCode) != strtolower($lang->get('abbr')))
			<link rel="alternate" href="{{ LaravelLocalization::getLocalizedURL($localeCode) }}" hreflang="{{ strtolower($localeCode) }}"/>
		@endif
	@endforeach
	@if (count($dns_prefetch) > 0)
		@foreach($dns_prefetch as $dns)
			<link rel="dns-prefetch" href="{{ $dns }}">
		@endforeach
	@endif
	@if (config('services.facebook.client_id'))
		<meta property="fb:app_id" content="{{ config('services.facebook.client_id') }}" />
	@endif
	{!! $og->renderTags() !!}
	{!! MetaTag::twitterCard() !!}
	@if (config('settings.google_site_verification'))
		<meta name="google-site-verification" content="{{ config('settings.google_site_verification') }}" />
	@endif
	@if (config('settings.msvalidate'))
		<meta name="msvalidate.01" content="{{ config('settings.msvalidate') }}" />
	@endif
	@if (config('settings.alexa_verify_id'))
		<meta name="alexaVerifyID" content="{{ config('settings.alexa_verify_id') }}" />
	@endif

  @yield('before_styles')

  <link rel="stylesheet" href="{{ url('css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ url('css/bootstrap-theme.min.css') }}">
  <link rel="stylesheet" href="{{ url('css/bootstrap-select.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ url('css/bootstrap-wysihtml5.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ url('css/prettify.css') }}">
  <link rel="stylesheet" href="{{ url('css/owl.carousel.css') }}">
  <link rel="stylesheet" href="{{ url('css/owl.theme.css') }}">
  <link href="{{ url('css/font-awesome.css') }}" rel="stylesheet">
  <link href="{{ url('css/line-font.css') }}" rel="stylesheet">
  <link href="{{ url('css/animate.css') }}" rel="stylesheet">
  <link href="{{ url('css/bootsnav.css') }}" rel="stylesheet">
  <link href="{{ url('css/style.css') }}" rel="stylesheet">
  <link type="text/css" rel="stylesheet" id="jssDefault" href="{{ url('css/colors/green-style.css') }}">
  <link href="{{ url('css/responsiveness.css') }}" rel="stylesheet">

  @yield('after_styles')

</head>

<body>
  <div class="wrapper">
		@section('header')
			@if (Auth::check())
				@include('layouts.inc.header', ['user' => $user])
			@else
				@include('layouts.inc.header')
			@endif
		@show

    @section('search')
    @show
{{--
    @if (isset($site_country_info))
      <div class="container" style="margin-bottom: -30px; margin-top: 20px;">
        <div class="row">
          <div class="col-lg-12">
            <div class="alert alert-warning">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              {!! $site_country_info !!}
            </div>
          </div>
        </div>
      </div>
    @endif --}}

    @yield('content')

    @section('info')
    @show

    <div class="clearfix"></div>

    @section('footer')
      @include('layouts.inc.footer')
    @show

    @section('modal_location')
    @show
    @section('modal_abuse')
    @show
    @section('modal_message')
    @show

    @yield('before_scripts')

    <script type="text/javascript" src="{{ url('js/jquery.min.js') }}"></script>
    <script src="{{ url('js/bootstrap.min.js') }}"></script>
    <script src="{{ url('js/bootsnav.js') }}"></script>
    <script src="{{ url('js/viewportchecker.js') }}"></script>
    <script src="{{ url('js/bootstrap-select.min.js') }}"></script>
    <script src="{{ url('js/wysihtml5-0.3.0.js') }}"></script>
    <script src="{{ url('js/bootstrap-wysihtml5.js') }}"></script>
    <script type="text/javascript" src="{{ url('js/owl.carousel.min.js') }}"></script>
    <script src="{{ url('js/custom.js') }}"></script>
    <script src="{{ url('/assets/plugins/SocialShare/SocialShare.min.js') }}"></script>

    @yield('after_scripts')

    <script>
        <?php
        $tracking_code = config('settings.tracking_code');
        $tracking_code = preg_replace('#<script(.*?)>(.*?)</script>#is', '$2', $tracking_code);
        echo $tracking_code . "\n";
        ?>
    </script>

    <script language="javascript">
    	var siteUrl = '<?php echo url('/'); ?>';
    	var languageCode = '<?php echo $lang->get('abbr'); ?>';
    	var langLayout = {
    		'hideMaxListItems': {
    			'moreText': "{{ t('View More') }}",
    			'lessText': "{{ t('View Less') }}"
    		}
    	};
    	$(document).ready(function () {
    		/* Select Boxes */
    		$(".selecter").select2({
    			language: '<?php echo $lang->get('abbr'); ?>',
    			dropdownAutoWidth: 'true',
    			minimumResultsForSearch: Infinity
    		});
    		/* Searchable Select Boxes */
    		$(".sselecter").select2({
    			language: '<?php echo $lang->get('abbr'); ?>',
    			dropdownAutoWidth: 'true',
    		});

    		/* Social Share */
    		$('.share').ShareLink({
    			title: '<?php echo addslashes(MetaTag::get('title')); ?>',
    			text: '<?php echo addslashes(MetaTag::get('title')); ?>',
    			url: '<?php echo $fullUrl; ?>'
    		});
    	});
    </script>

  </div>
</body>
</html>
