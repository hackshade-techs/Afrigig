<div class="container-fluid home-background">
	<div class="row">
		<div class="col-xs-12 col-sm-8 col-md-6 col-lg-5">
			<h1 class="m-t-80 m-b-40" style="margin-top: 100px; margin-left: 30px; font-weight: 700; line-height:60px; font-size: 50px;">Get it done with a freelancer</h1>
			<p style="margin-left: 25px; font-weight: 400; font-size: 20px;">Let your work be done faster, Afrigig Uganda</p>
			<a style="margin-left: 25px;" class="btn btn-lg btn-yellow" href="{{ lurl(trans('routes.signup')) . '?type=3' }}" style="padding-left: 30px; padding-right: 30px; text-transform: none;">
				Get Started
			</a>
		</div>
	</div>
	{{-- <div class="intro search-width ">
		<div class="dtable hw100">
			<div class="dtable-cell hw100">
				<div class="container text-center">

					<div class="row search-row">
						<form id="seach" name="search" action="{{ lurl(trans('routes.v-search', ['countryCode' => $country->get('icode')])) }}"
							  method="GET">
							<div class="col-lg-5 col-sm-5 search-col relative">
								<i class="icon-docs icon-append"></i>
								<input type="text" name="q" class="form-control keyword has-icon" placeholder="{{ t('What?') }}" value="">
							</div>
							<div class="col-lg-5 col-sm-5 search-col relative locationicon">
								<i class="icon-location-2 icon-append"></i>
								<input type="hidden" id="l_search" name="l" value="">
								<input type="text" id="loc_search" name="location" class="form-control locinput input-rel searchtag-input has-icon"
									   placeholder="{{ t('Where?') }}" value="">
							</div>
							<div class="col-lg-2 col-sm-2 search-col">
								<button class="btn btn-primary btn-search btn-block"><i class="icon-search"></i> <strong>{{ t('Search') }}</strong>
								</button>
							</div>
							{!! csrf_field() !!}
						</form>
					</div>

				</div>
			</div>
		</div>
	</div> --}}
</div>
