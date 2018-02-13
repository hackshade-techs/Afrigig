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

use App\Helpers\Arr;
use App\Models\Ad;
use App\Models\Category;
use App\Models\Package;
use App\Models\SubAdmin1;
use App\Models\City;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Torann\LaravelMetaTags\Facades\MetaTag;
use App\Helpers\Localization\Helpers\Country as CountryLocalizationHelper;
use App\Helpers\Localization\Country as CountryLocalization;

class HomeController extends FrontController
{
    /**
     * HomeController constructor.
     */
	public function __construct()
	{
		parent::__construct();

		// Check Country URL for SEO
        $countries = CountryLocalizationHelper::transAll(CountryLocalization::getCountries(), $this->lang->get('abbr'));
        view()->share('countries', $countries);
	}

	/**
	 * @return View
	 */
    public function index()
    {
        $data = array();

		// Companies
		$data['featuredCompanies'] = $this->getFeaturedCompanies();

		// Featured Ads
		$data['latestAds'] = $this->getLatestAds();
        
        // Get Categories
        $data['categories'] = $this->getCategories();

        // Get Cities
        $data['cities'] = $this->getLocations();
        
        
        // Get Bottom Infos
        if (config('settings.activation_home_stats'))
        {
			// Count ads
			$data['count_ads'] = Ad::where('country_code', $this->country->get('code'))->count();
			$data['count_cities'] = $this->countLocations();
            // Count users
            $data['count_users'] = User::where('active', 1)->count();
            // Count Facebook fans
            $data['count_facebook_fans'] = countFacebookFans(config('settings.facebook_page_id'));
        }
        
        
        // Modal - States Collection
        $states = SubAdmin1::where('code', 'LIKE', $this->country->get('code') . '.%')->orderBy('name')->get(['code', 'name'])->keyBy('code');
        view()->share('states', $states);
        
        
        // SEO
        if (config('settings.app_name') and config('settings.app_slogan')) {
            $title = config('settings.app_name') . ' - ' . config('settings.app_slogan');
        } else {
            $title = t('Jobs ads in :location', ['location' => $this->country->get('name')]);
        }
        $description = str_limit(str_strip(t('Sell and Buy products and services on :app_name in Minutes',
                ['app_name' => mb_ucfirst(config('settings.app_name'))]) . ' ' . $this->country->get('name') . '. ' . t('Jobs in :location',
                ['location' => $this->country->get('name')]) . '. ' . t('Looking for a job') . ' - ' . $this->country->get('name')),
            200);
        
        // Meta Tags
        MetaTag::set('title', $title);
        MetaTag::set('description', strip_tags($description));
        
        // Open Graph
        $this->og->title($title)->description($description);
        view()->share('og', $this->og);
        
        return view('home.index', $data);
    }

    private function getCategories()
	{
		$cats = Category::where('parent_id', 0)->where('translation_lang', $this->lang->get('abbr'))->orderBy('lft')->get();

		$cols = round($cats->count() / 3, 0); // PHP_ROUND_HALF_EVEN
		$cols = ($cols > 0) ? $cols : 1; // Fix array_chunk with 0
		$cats = $cats->chunk($cols);

		return $cats;
	}

    private function getLocations($citiesTaken = 14)
	{
		$cities = City::where('country_code', $this->country->get('code'))->take($citiesTaken)->orderBy('population', 'DESC')->orderBy('name')->get();
		$cities = collect($cities)->push(Arr::toObject([
			'id' => 999999999,
			'name' => t('More cities') . ' &raquo;',
			'subadmin1_code' => 0
		]));

		$cols = round($cities->count() / 3, 0); // PHP_ROUND_HALF_EVEN
		$cols = ($cols > 0) ? $cols : 1; // Fix array_chunk with 0
		$cities = $cities->chunk($cols);

		return $cities;
	}

	private function countLocations()
	{
		$cities = City::where('country_code', $this->country->get('code'))->orderBy('population', 'DESC')->orderBy('name');
		return $cities->count();
	}

	/**
	 * Get Latest ads
	 *
	 * @return array|null|\stdClass
	 */
    private function getLatestAds()
	{
		$limit = 5;
		$ads = null;

        // Get featured ads
        $reviewedAdSql = '';
        if (config('settings.ads_review_activation')) {
            $reviewedAdSql = ' AND a.reviewed = 1';
        }
        $sql = 'SELECT DISTINCT a.*, p.package_id as p_package_id' . '
                FROM ' . table('ads') . ' as a 
                INNER JOIN ' . table('categories') . ' as c ON c.id=a.category_id AND c.active=1
                LEFT JOIN ' . table('payments') . ' as p ON p.ad_id=a.id
                WHERE a.country_code = :country_code AND a.active=1 AND a.archived!=1 AND a.deleted_at IS NULL ' . $reviewedAdSql . '
                ORDER BY p.package_id DESC, a.created_at DESC
                LIMIT 0,' . (int)$limit;
        $bindings = [
            'country_code' 	=> $this->country->get('code')
        ];
        $ads = DB::select(DB::raw($sql), $bindings);

        if (!empty($ads)) {
            shuffle($ads);
        }

		$latestAds = [
			'title' => t('Home - Latest Jobs'),
			'link' 	=> lurl(trans('routes.v-search', ['countryCode' => $this->country->get('icode')])),
			'rows' 	=> $ads,
		];
		$latestAds = Arr::toObject($latestAds);

		return $latestAds;
	}

	/**
	 * Get Companies
	 *
	 * @return array|null|\stdClass
	 */
	private function getFeaturedCompanies()
	{
		$limit = 12;
		$featuredCompanies = null;

		// Get Categories
        $reviewedAdSql = '';
        if (config('settings.ads_review_activation')) {
            $reviewedAdSql = ' AND a.reviewed = 1';
        }
		$sql = 'SELECT DISTINCT a.*, COUNT(a.id) as count_ads
				FROM ' . table('ads') . ' as a
				INNER JOIN ' . table('categories') . ' as c ON c.id=a.category_id AND c.active=1
				LEFT JOIN ' . table('payments') . ' as py ON py.ad_id=a.id
                LEFT JOIN ' . table('packages') . ' as p ON p.id=py.package_id
				WHERE a.country_code = :country_code AND a.active=1 AND a.archived!=1 AND a.deleted_at IS NULL ' . $reviewedAdSql . '
				GROUP BY a.company_name
				ORDER BY p.lft DESC, a.logo DESC, a.created_at DESC
				LIMIT 0,' . (int)$limit;
		$bindings = [
			'country_code' 	=> $this->country->get('code')
		];
		$ads = DB::select(DB::raw($sql), $bindings);

		if (!empty($ads)) {
			shuffle($ads);
		}
		$featuredCompanies = [
			'title' => t('Home - Featured Company'),
			'link' 	=> lurl(trans('routes.v-search', ['countryCode' => $this->country->get('icode')])),
			'rows' 	=> $ads,
		];
		$featuredCompanies = Arr::toObject($featuredCompanies);

		return $featuredCompanies;
	}
}
