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

@section('search')
	@parent
	<div class="clearfix"></div>
	@include('home.inc.search')
@endsection

@section('content')

	<div class="clearfix"></div>
	@include('home.inc.categories')
	<div class="clearfix"></div>
	<section class="video-sec dark" id="video">
		<div class="container">
			<div class="row">
				<div class="main-heading">
					<p>Best For Your Projects</p>
					<h2>Watch Our <span>video</span></h2></div>
			</div>
			<div class="video-part"><a href="#" data-toggle="modal" data-target="#my-video" class="video-btn"><i class="fa fa-play"></i></a></div>
		</div>
	</section>
	<div class="clearfix"></div>
	<section class="wp-process">
		<div class="container">
			<div class="row">
				<div class="main-heading">
					<p>How We Work</p>
					<h2>Our Work <span>Process</span></h2></div>
			</div>
			<div class="col-md-4 col-sm-6">
				<div class="work-process">
					<div class="work-process-icon"><span class="icon-search"></span></div>
					<div class="work-process-caption">
						<h4>Search Job</h4>
						<p>Aliquam vestibulum cursus felis. In iaculis iaculis sapien ac condimentum. Vestibulum congue posuere lacus</p>
					</div>
				</div>
			</div>
			<div class="col-md-4 col-sm-6">
				<div class="work-process">
					<div class="work-process-icon"><span class="icon-mobile"></span></div>
					<div class="work-process-caption">
						<h4>Find Job</h4>
						<p>Aliquam vestibulum cursus felis. In iaculis iaculis sapien ac condimentum. Vestibulum congue posuere lacus</p>
					</div>
				</div>
			</div>
			<div class="col-md-4 col-sm-6">
				<div class="work-process">
					<div class="work-process-icon"><span class="icon-profile-male"></span></div>
					<div class="work-process-caption">
						<h4>Hire Employee</h4>
						<p>Aliquam vestibulum cursus felis. In iaculis iaculis sapien ac condimentum. Vestibulum congue posuere lacus</p>
					</div>
				</div>
			</div>
			<div class="col-md-4 col-sm-6">
				<div class="work-process">
					<div class="work-process-icon"><span class="icon-layers"></span></div>
					<div class="work-process-caption">
						<h4>Start Work</h4>
						<p>Aliquam vestibulum cursus felis. In iaculis iaculis sapien ac condimentum. Vestibulum congue posuere lacus</p>
					</div>
				</div>
			</div>
			<div class="col-md-4 col-sm-6">
				<div class="work-process">
					<div class="work-process-icon"><span class="icon-wallet"></span></div>
					<div class="work-process-caption">
						<h4>Pay Money</h4>
						<p>Aliquam vestibulum cursus felis. In iaculis iaculis sapien ac condimentum. Vestibulum congue posuere lacus</p>
					</div>
				</div>
			</div>
			<div class="col-md-4 col-sm-6">
				<div class="work-process">
					<div class="work-process-icon"><span class="icon-happy"></span></div>
					<div class="work-process-caption">
						<h4>Happy</h4>
						<p>Aliquam vestibulum cursus felis. In iaculis iaculis sapien ac condimentum. Vestibulum congue posuere lacus</p>
					</div>
				</div>
			</div>
		</div>
	</section>
	<div class="clearfix"></div>
	<section class="testimonial" id="testimonial">
		<div class="container">
			<div class="row">
				<div class="main-heading">
					<p>What Say Our Client</p>
					<h2>Our Success <span>Stories</span></h2></div>
			</div>
			<div class="row">
				<div id="client-testimonial-slider" class="owl-carousel">
					<div class="client-testimonial">
						<div class="pic"><img src="img/img-01.jpg" alt=""></div>
						<p class="client-description">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor et dolore magna aliqua.</p>
						<h3 class="client-testimonial-title">Lacky Mole</h3>
						<ul class="client-testimonial-rating">
							<li class="fa fa-star-o"></li>
							<li class="fa fa-star-o"></li>
							<li class="fa fa-star"></li>
						</ul>
					</div>
					<div class="client-testimonial">
						<div class="pic"><img src="img/img-02.jpg" alt=""></div>
						<p class="client-description">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor et dolore magna aliqua.</p>
						<h3 class="client-testimonial-title">Karan Wessi</h3>
						<ul class="client-testimonial-rating">
							<li class="fa fa-star-o"></li>
							<li class="fa fa-star"></li>
							<li class="fa fa-star"></li>
						</ul>
					</div>
					<div class="client-testimonial">
						<div class="pic"><img src="img/img-03.jpg" alt=""></div>
						<p class="client-description">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor et dolore magna aliqua.</p>
						<h3 class="client-testimonial-title">Roul Pinchai</h3>
						<ul class="client-testimonial-rating">
							<li class="fa fa-star-o"></li>
							<li class="fa fa-star-o"></li>
							<li class="fa fa-star"></li>
						</ul>
					</div>
					<div class="client-testimonial">
						<div class="pic"><img src="img/img-04.jpg" alt=""></div>
						<p class="client-description">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor et dolore magna aliqua.</p>
						<h3 class="client-testimonial-title">Adam Jinna</h3>
						<ul class="client-testimonial-rating">
							<li class="fa fa-star-o"></li>
							<li class="fa fa-star-o"></li>
							<li class="fa fa-star"></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</section>
	<section class="pricing">
		<div class="container">
			<div class="row">
				<div class="main-heading">
					<p>Check Our Packages</p>
					<h2>Our Best <span>Offers</span></h2></div>
			</div>
			<div class="col-md-4 col-sm-4">
				<div class="pr-table">
					<div class="pr-plan">
						<h4>Basic</h4></div>
					<div class="pr-price">
						<h3><sup>$</sup>29<sub>/Mon</sub></h3></div>
					<div class="pr-features">
						<ul>
							<li>1 GB Ram</li>
							<li>2 GB Memory</li>
							<li>1 Core Processor</li>
							<li>32 GB SSD Disk</li>
							<li>1 TB Transfer</li>
						</ul>
					</div>
					<div class="pr-buy-button"><a href="#" class="pr-btn" title="Price Button">Get Started</a></div>
				</div>
			</div>
			<div class="col-md-4 col-sm-4">
				<div class="pr-table">
					<div class="pr-plan">
						<h4>Premium</h4></div>
					<div class="pr-price">
						<h3><sup>$</sup>40<sub>/Mon</sub></h3></div>
					<div class="pr-features">
						<ul>
							<li>1 GB Ram</li>
							<li>2 GB Memory</li>
							<li>1 Core Processor</li>
							<li>32 GB SSD Disk</li>
							<li>1 TB Transfer</li>
						</ul>
					</div>
					<div class="pr-buy-button"><a href="#" class="pr-btn active" title="Price Button">Get Started</a></div>
				</div>
			</div>
			<div class="col-md-4 col-sm-4">
				<div class="pr-table">
					<div class="pr-plan">
						<h4>Ultimate</h4></div>
					<div class="pr-price">
						<h3><sup>$</sup>120<sub>/Mon</sub></h3></div>
					<div class="pr-features">
						<ul>
							<li>1 GB Ram</li>
							<li>2 GB Memory</li>
							<li>1 Core Processor</li>
							<li>32 GB SSD Disk</li>
							<li>1 TB Transfer</li>
						</ul>
					</div>
					<div class="pr-buy-button"><a href="#" class="pr-btn" title="Price Button">Get Started</a></div>
				</div>
			</div>
		</div>
	</section>
@endsection

@section('modal_location')
	@include('layouts.inc.modal.location')
@endsection

@section('after_scripts')
	<script>
		var stateId = '<?php echo (isset($city)) ? $country->get('code').'.'.$city->subadmin1_code : '0' ?>';
	</script>
	<script type="text/javascript" src="{{ url('assets/js/app/load.cities.js') }}"></script>
@endsection
