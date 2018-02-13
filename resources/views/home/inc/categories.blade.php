@if (isset($categories) and $categories->count() > 0)
<section class="first-feature">
	<div class="container">
		<div class="all-features">
			{{-- {{ dd($categories) }} --}}
			@foreach ($categories as $key => $items)
			<div class="col-md-3 col-sm-6 small-padding">
				<a href="{{ lurl(trans('routes.v-search-cat', ['countryCode' => $country->get('icode'), 'catSlug' => $items->slug])) }}">
					<div class="job-feature">
						<div class="feature-icon"><i class="fa fa-desktop"></i></div>
						<div class="feature-caption">

								<h5>{{ $items->name }}</h5>

							<p>{{ $items->description }}</p>
						</div>
					</div>
				</a>
			</div>
		  @endforeach
			{{-- <div class="col-md-3 col-sm-6 small-padding">
				<div class="job-feature">
					<div class="feature-icon"><i class="fa fa-mobile"></i></div>
					<div class="feature-caption">
						<h5>Mobile Developer</h5>
						<p>At vero eos et accusamus et iusto odio dignissimos ducimus.</p>
					</div>
				</div>
			</div>
			<div class="col-md-3 col-sm-6 small-padding">
				<div class="job-feature">
					<div class="feature-icon"><i class="fa fa-lightbulb-o"></i></div>
					<div class="feature-caption">
						<h5>Creative Designer</h5>
						<p>At vero eos et accusamus et iusto odio dignissimos ducimus.</p>
					</div>
				</div>
			</div>
			<div class="col-md-3 col-sm-6 small-padding">
				<div class="job-feature">
					<div class="feature-icon"><i class="fa fa-file-text"></i></div>
					<div class="feature-caption">
						<h5>Content Writer</h5>
						<p>At vero eos et accusamus et iusto odio dignissimos ducimus.</p>
					</div>
				</div>
			</div>
			<div class="col-md-3 col-sm-6 small-padding">
				<div class="job-feature">
					<div class="feature-icon"><i class="fa fa-hdd-o"></i></div>
					<div class="feature-caption">
						<h5>Manager</h5>
						<p>At vero eos et accusamus et iusto odio dignissimos ducimus.</p>
					</div>
				</div>
			</div>
			<div class="col-md-3 col-sm-6 small-padding">
				<div class="job-feature">
					<div class="feature-icon"><i class="fa fa-bullhorn"></i></div>
					<div class="feature-caption">
						<h5>Sales & Marketing</h5>
						<p>At vero eos et accusamus et iusto odio dignissimos ducimus.</p>
					</div>
				</div>
			</div>
			<div class="col-md-3 col-sm-6 small-padding">
				<div class="job-feature">
					<div class="feature-icon"><i class="fa fa-credit-card"></i></div>
					<div class="feature-caption">
						<h5>Accountant</h5>
						<p>At vero eos et accusamus et iusto odio dignissimos ducimus.</p>
					</div>
				</div>
			</div>
			<div class="col-md-3 col-sm-6 small-padding">
				<div class="job-feature">
					<div class="feature-icon"><i class="fa fa-user"></i></div>
					<div class="feature-caption">
						<h5>Management / HR</h5>
						<p>At vero eos et accusamus et iusto odio dignissimos ducimus.</p>
					</div>
				</div>
			</div> --}}
		</div>
	</div>
</section>
@endif


{{-- <div class="col-lg-12 content-box">
	<div class="row row-featured row-featured-category">
		<div class="col-lg-12 box-title no-border">
			<div class="inner">
				<h2>
					<span class="title-3">{{ t('Browse by') }} <span style="font-weight: bold;">{{ t('Category') }}</span></span>
					<a href="{{ lurl(trans('routes.v-sitemap', ['countryCode' => $country->get('icode')])) }}"
					   class="sell-your-item">
						{{ t('View more') }} <i class="icon-th-list"></i>
					</a>
				</h2>
			</div>
		</div>

		@if (isset($categories) and $categories->count() > 0)
		<div style="padding: 0 10px 0 20px;">
			@foreach ($categories as $key => $items)
				<ul class="cat-list list list-check col-xs-4 {{ (count($categories) == $key+1) ? 'cat-list-border' : '' }}" style="padding: 25px;">
					@foreach ($items as $k => $cat)
						<li>
							<a href="{{ lurl(trans('routes.v-search-cat', ['countryCode' => $country->get('icode'), 'catSlug' => $cat->slug])) }}">
								{{ $cat->name }}
							</a>
						</li>
					@endforeach
				</ul>
			@endforeach
		</div>
		@endif

	</div>
</div>

<div style="clear: both"></div> --}}
