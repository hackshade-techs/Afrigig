<!-- =========================================================== -->

<!-- Small boxes (Stat box) -->
<div class="row">
	<div class="col-lg-3 col-xs-6">
		<!-- small box -->
		<div class="small-box bg-yellow">
			<div class="inner">
				<h3>{{ $countUnactivatedAds }}</h3>

				<p>{{ __t('Unactivated ads') }}</p>
			</div>
			<div class="icon">
				<i class="fa fa-edit"></i>
			</div>
			<a href="{{ url(config('larapen.admin.route_prefix', 'admin') . '/ad?active=0') }}" class="small-box-footer">
				{{ __t('View more') }} <i class="fa fa-arrow-circle-right"></i>
			</a>
		</div>
	</div>
	<!-- ./col -->

	<div class="col-lg-3 col-xs-6">
		<!-- small box -->
		<div class="small-box bg-green">
			<div class="inner">
				<h3>{{ $countActivatedAds }}</h3>

				<p>{{ __t('Activated ads') }}</p>
			</div>
			<div class="icon">
				<i class="fa fa-check-circle-o"></i>
			</div>
			<a href="{{ url(config('larapen.admin.route_prefix', 'admin') . '/ad?active=1') }}" class="small-box-footer">
				{{ __t('View more') }} <i class="fa fa-arrow-circle-right"></i>
			</a>
		</div>
	</div>
	<!-- ./col -->

	<div class="col-lg-3 col-xs-6">
		<!-- small box -->
		<div class="small-box bg-aqua">
			<div class="inner">
				<h3>{{ $countUsers }}</h3>

				<p>{{ __t('User Registrations') }}</p>
			</div>
			<div class="icon">
				<i class="ion ion-person-add"></i>
			</div>
			<a href="{{ url(config('larapen.admin.route_prefix', 'admin') . '/user') }}" class="small-box-footer">
				{{ __t('View more') }} <i class="fa fa-arrow-circle-right"></i>
			</a>
		</div>
	</div>
	<!-- ./col -->

	<div class="col-lg-3 col-xs-6">
		<!-- small box -->
		<div class="small-box bg-red">
			<div class="inner">
				<h3>{{ $countCountries }}</h3>

				<p>
					{{ __t('Activated countries') }}
					<span class="label label-default tooltipHere"
						  title="" data-placement="bottom" data-toggle="tooltip" type="button"
						  data-original-title="{!! __t('To launch your website for several countries you need to activate these countries.') . ' ' . __t('By disabling or removing a country the ads of this country (also) will be deleted.') !!}">
                        {{ __t('Help') }} <i class="fa fa-support"></i>
                    </span>
				</p>
			</div>
			<div class="icon">
				<i class="fa fa-globe"></i>
			</div>
			<a href="{{ url(config('larapen.admin.route_prefix', 'admin') . '/country') }}" class="small-box-footer">
				{{ __t('View more') }} <i class="fa fa-arrow-circle-right"></i>
			</a>
		</div>
	</div>
	<!-- ./col -->
</div>
<!-- /.row -->

<!-- =========================================================== -->

<div class="row">
	<!-- Left col -->
	<section class="col-lg-6 connectedSortable">
		<!-- ADS STATS -->
		<div class="box box-primary">
			<div class="box-header with-border">
				<h3 class="box-title">{{ __t('Ads Stats') }}</h3>
				
				<div class="box-tools pull-right">
					<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
					<button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
				</div>
			</div>
			<div class="box-body chart-responsive">
				<div class="chart" id="bar-chart" style="height: 300px;"></div>
			</div>
			<!-- /.box-body -->
		</div>
		<!-- /.box -->
		
		<!-- TABLE: LATEST ADS -->
		<div class="box box-primary">
			<div class="box-header with-border">
				<h3 class="box-title">{{ __t('Latest Ads') }}</h3>
				
				<div class="box-tools pull-right">
					<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
					</button>
					<button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
				</div>
			</div>
			<!-- /.box-header -->
			<div class="box-body">
				<div class="table-responsive">
					<table class="table no-margin">
						<thead>
						<tr>
							<th>ID</th>
							<th>{{ __t('Title') }}</th>
							<th>{{ __t('Status') }}</th>
							<th>{{ __t('Date') }}</th>
						</tr>
						</thead>
						<tbody>
						@if ($ads->count() > 0)
							@foreach($ads as $ad)
								<tr>
									<td>{{ $ad->id }}</td>
									<td>{!! getAdLink($ad) !!}</td>
									<td>
										@if (config('settings.ads_review_activation'))
											@if ($ad->active == 0 or $ad->reviewed == 0)
												<span class="label label-warning">{{ __t('Unactivated') }}</span>
											@else
												<span class="label label-success">{{ __t('Activated') }}</span>
											@endif
										@else
											@if ($ad->active == 0)
												<span class="label label-warning">{{ __t('Unactivated') }}</span>
											@else
												<span class="label label-success">{{ __t('Activated') }}</span>
											@endif
										@endif
									</td>
									<td>
										<div class="sparkbar" data-color="#00a65a" data-height="20">{{ $ad->created_at }}</div>
									</td>
								</tr>
							@endforeach
						@else
							<tr>
								<td>
									{{ __t('No ads found') }}
								</td>
							</tr>
						@endif
						</tbody>
					</table>
				</div>
				<!-- /.table-responsive -->
			</div>
			<!-- /.box-body -->
			<div class="box-footer clearfix">
				<a href="{{ url(config('app.locale') . '/' . trans('routes.create')) }}" target="_blank" class="btn btn-sm btn-primary btn-flat pull-left">{{ __t('Post New Ad') }}</a>
				<a href="{{ url(config('larapen.admin.route_prefix', 'admin') . '/ad') }}" class="btn btn-sm btn-default btn-flat pull-right">{{ __t('View All Ads') }}</a>
			</div>
			<!-- /.box-footer -->
		</div>
		<!-- /.box -->
	</section>
	<!-- /.Left col -->
	
	<!-- right col (We are only adding the ID to make the widgets sortable)-->
	<section class="col-lg-6 connectedSortable">
		<!-- USERS STATS -->
		<div class="box box-primary">
			<div class="box-header with-border">
				<h3 class="box-title">{{ __t('Users Stats') }}</h3>
				
				<div class="box-tools pull-right">
					<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
					</button>
					<button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
				</div>
			</div>
			<div class="box-body chart-responsive">
				<div class="chart" id="line-chart" style="height: 300px;"></div>
			</div>
			<!-- /.box-body -->
		</div>
		<!-- /.box -->
		
		<!-- TABLE: LATEST USERS -->
		<div class="box box-primary">
			<div class="box-header with-border">
				<h3 class="box-title">{{ __t('Latest Users') }}</h3>
				
				<div class="box-tools pull-right">
					<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
					</button>
					<button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
				</div>
			</div>
			<!-- /.box-header -->
			<div class="box-body">
				<div class="table-responsive">
					<table class="table no-margin">
						<thead>
						<tr>
							<th>ID</th>
							<th>{{ __t('Name') }}</th>
							<th>{{ __t('Status') }}</th>
							<th>{{ __t('Date') }}</th>
						</tr>
						</thead>
						<tbody>
						@if ($users->count() > 0)
							@foreach($users as $user)
								<tr>
									<td>
										<a href="{{ url(config('larapen.admin.route_prefix', 'admin') . '/user/'.$user->id.'/edit') }}">
											{{ $user->id }}
										</a>
									</td>
									<td>
										<a href="{{ url(config('larapen.admin.route_prefix', 'admin') . '/user/'.$user->id.'/edit') }}">
											{{ str_limit($user->name, 70) }}
										</a>
									</td>
									<td>
										@if ($user->active == 0)
											<span class="label label-warning">{{ __t('Unactivated') }}</span>
										@else
											<span class="label label-success">{{ __t('Activated') }}</span>
										@endif
									</td>
									<td>
										<div class="sparkbar" data-color="#00a65a" data-height="20">{{ $user->created_at }}</div>
									</td>
								</tr>
							@endforeach
						@else
							<tr>
								<td>
									{{ __t('No users found') }}
								</td>
							</tr>
						@endif
						</tbody>
					</table>
				</div>
				<!-- /.table-responsive -->
			</div>
			<!-- /.box-body -->
			<div class="box-footer clearfix">
				<a href="{{ url(config('larapen.admin.route_prefix', 'admin') . '/user') }}" class="btn btn-sm btn-default btn-flat pull-right">{{ __t('View All Users') }}</a>
			</div>
			<!-- /.box-footer -->
		</div>
		<!-- /.box -->
	</section>
	<!-- /.right col -->
</div>

<!-- =========================================================== -->

{{-- Define blade stacks so css and js can be pushed from the fields to these sections. --}}
@section('after_styles')
    @stack('after_styles')
    <?php /*<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">*/ ?>
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/') }}/plugins/morris/0.5.1/morris.css">
@endsection

@section('after_scripts')
    <script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
    <script src="{{ asset('vendor/adminlte') }}/plugins/morris/morris.min.js"></script>
    
    <script>
        $(function () {
            "use strict";
    
            // ADS STATS
            var area = new Morris.Bar({
                element: 'bar-chart',
                resize: true,
                data: <?php echo $adsStats; ?>,
                xkey: 'y',
                ykeys: ['activated', 'unactivated'],
                labels: ['{{ __t('Activated') }}', '{{ __t('Unactivated') }}'],
                lineColors: ['#3c8dbc', '#a0d0e0'],
                hideHover: 'auto',
                parseTime: false
            });
    
            // USERS STATS
            var line = new Morris.Line({
                element: 'line-chart',
                resize: true,
                data: <?php echo $usersStats; ?>,
                xkey: 'y',
                ykeys: ['activated', 'unactivated'],
                labels: ['{{ __t('Activated') }}', '{{ __t('Unactivated') }}'],
                lineColors: ['#3c8dbc', '#a0d0e0'],
                hideHover: 'auto',
                parseTime: false
            });
        });
    </script>

@endsection
