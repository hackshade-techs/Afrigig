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
				<div class="col-sm-3 page-sidebar">
					@include('account/inc/sidebar-left')
				</div>
				<!--/.page-sidebar-->

				<div class="col-sm-9 page-content">
					<div class="inner-box">
						<h2 class="title-2"><i class="icon-cancel-circled "></i> @lang('global.Close account') </h2>
						<p>@lang('global.You are sure you want to close your account?')</p>

						@if ($user->is_admin)
							<span style="color: red; font-weight: bold;">Admin users can't be deleted by this way.</span>
						@else
						<form role="form" method="POST" action="{{ lurl('account/close') }}">
							{!! csrf_field() !!}
							<div>
								<label class="radio-inline">
									<input type="radio" name="close_account_confirmation" id="closeAccountConfirmation1"
										   value="1"> @lang('global.Yes')
								</label>
								<label class="radio-inline">
									<input type="radio" name="close_account_confirmation" id="closeAccountConfirmation0" value="0"
										   checked> @lang('global.No')
								</label>
							</div>
							<br>
							<button type="submit" class="btn btn-primary">@lang('global.Submit')</button>
						</form>
						@endif

					</div><!--/.inner-box-->
				</div>
				<!--/.page-content-->

			</div>
			<!--/.row-->
		</div>
		<!--/.container-->
	</div>
	<!-- /.main-container -->
@endsection
