<?php
/**
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
 */

namespace App\Http\Controllers;

/*
 * Increase PHP page execution time for this controller.
 * NOTE: This function has no effect when PHP is running in safe mode (http://php.net/manual/en/ini.sect.safe-mode.php#ini.safe-mode).
 * There is no workaround other than turning off safe mode or changing the time limit (max_execution_time) in the php.ini.
 */
set_time_limit(0);

use App\Helpers\Localization\Helpers\Country as CountryLocalizationHelper;
use App\Helpers\Localization\Country as CountryLocalization;
use App\Models\Category;
use App\Models\Page;
use Carbon\Carbon;
use App\Models\Ad;
use App\Models\City;
use Illuminate\Support\Facades\App;
use Watson\Sitemap\Facades\Sitemap;

class SitemapsController extends FrontController
{
    protected $defaultDate = '2015-10-30T20:10:00+02:00';

    /**
     * SitemapsController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        
        // Get Countries
        $this->countries = CountryLocalizationHelper::transAll(CountryLocalization::getCountries(), $this->lang->get('abbr'));

		// From Laravel 5.3.4 or above
		$this->middleware(function ($request, $next) {
			$this->commonQueries();
			return $next($request);
		});
    }

	/**
	 * Common Queries
	 */
	public function commonQueries()
	{
		// Get country language
		$this->lang = $this->country->get('lang');
		App::setLocale($this->lang->get('abbr'));

		// Date : Carbon object
		$this->defaultDate = Carbon::parse(date('Y-m-d H:i'));
		if (config('timezone.id')) {
			$this->defaultDate->timezone(config('timezone.id'));
		}
	}
    
    /**
     * Index Sitemap
     * @return mixed
     */
    public function index()
    {
        foreach ($this->countries as $item) {
            $country = CountryLocalization::getCountryInfo($item->get('code'));
            Sitemap::addSitemap(url($country->get('lang')->get('abbr') . '/' . $country->get('icode') . '/sitemaps.xml'));
        }
        
        return Sitemap::index();
    }
    
    /**
     * Index Sitemap
     * @return mixed
     */
    public function site()
    {
        Sitemap::addSitemap(url($this->lang->get('abbr') . '/' . $this->country->get('icode') . '/sitemaps/pages.xml'));
        Sitemap::addSitemap(url($this->lang->get('abbr') . '/' . $this->country->get('icode') . '/sitemaps/categories.xml'));
        Sitemap::addSitemap(url($this->lang->get('abbr') . '/' . $this->country->get('icode') . '/sitemaps/cities.xml'));

        $countAds = Ad::active()->where('country_code', $this->country->get('code'))->count();
        if ($countAds > 0) {
            Sitemap::addSitemap(url($this->lang->get('abbr') . '/' . $this->country->get('icode') . '/sitemaps/ads.xml'));
        }
        
        return Sitemap::index();
    }
    
    /**
     * @return mixed
     */
    public function pages()
    {
        Sitemap::addTag(lurl('/?d=' . $this->country->get('code')), $this->defaultDate, 'daily', '1.0');
        Sitemap::addTag(lurl(trans('routes.v-sitemap') . '?d=' . $this->country->get('icode')), $this->defaultDate, 'daily', '0.5');
        Sitemap::addTag(lurl(trans('routes.v-search') . '?d=' . $this->country->get('icode')), $this->defaultDate, 'daily', '0.6');

        $pages = Page::where('translation_lang', $this->lang->get('abbr'))->orderBy('lft', 'ASC')->get();
        if ($pages->count() > 0) {
            foreach($pages as $page) {
                Sitemap::addTag(lurl(trans('routes.v-page', ['slug' => $page->slug])), $this->defaultDate, 'daily', '0.7');
            }
        }

        Sitemap::addTag(lurl(trans('routes.contact') . '?d=' . $this->country->get('icode')), $this->defaultDate, 'daily', '0.7');
        
        return Sitemap::render();
    }

    /**
     * @return mixed
     */
    public function categories()
    {
        // Categories
        $cats = Category::where('translation_lang', $this->lang->get('abbr'))->orderBy('lft')->get();

        if ($cats->count() > 0) {
            $cats = collect($cats)->keyBy('translation_of');
            $cats = $sub_cats = $cats->groupBy('parent_id');
            $cats = $cats->get(0);
            $sub_cats = $sub_cats->forget(0);

            foreach ($cats as $cat) {
                Sitemap::addTag(lurl(trans('routes.v-search-cat', ['countryCode' => $this->country->get('icode'), 'catSlug' => $cat->slug])),
                    $this->defaultDate, 'daily', '0.8');
                if ($sub_cats->get($cat->id)) {
                    foreach ($sub_cats->get($cat->id) as $sub_cat) {
                        Sitemap::addTag(lurl(trans('routes.v-search-subCat',
                            [
                                'countryCode' => $this->country->get('icode'),
                                'catSlug'     => $cat->slug,
                                'subCatSlug'  => $sub_cat->slug
                            ])),
                            $this->defaultDate, 'weekly', '0.5');
                    }
                }
            }
        }

        return Sitemap::render();
    }
    
    /**
     * @return mixed
     */
    public function cities()
    {
        $taked = 1000;
        $cities = City::where('country_code', $this->country->get('code'))->take($taked)->orderBy('population', 'DESC')->orderBy('name')->get();

        if ($cities->count() > 0) {
            foreach($cities as $city) {
                $city->name = trim(head(explode('/', $city->name)));
                Sitemap::addTag(url(trans('routes.v-search-location',
                    [
                        'countryCode' => $this->country->get('icode'),
                        'city'        => slugify($city->name),
                        'id'          => $city->id
                    ])),
                    $this->defaultDate, 'weekly', '0.7');
            }
        }
        
        return Sitemap::render();
    }
    
    /**
     * @return mixed
     */
    public function ads()
    {
        $taked = 50000;
        $ads = Ad::active()->where('country_code', $this->country->get('code'))->take($taked)->orderBy('created_at', 'DESC')->get();
        
        if ($ads->count() > 0) {
            foreach ($ads as $ad) {
                Sitemap::addTag(lurl(slugify($ad->title) . '/' . $ad->id . '.html'), $ad->created_at, 'daily', '0.6');
            }
        }
        
        return Sitemap::render();
    }
}
