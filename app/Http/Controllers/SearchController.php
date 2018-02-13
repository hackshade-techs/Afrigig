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
use App\Helpers\Search;
use App\Models\Ad;
use App\Models\SubAdmin1;
use App\Models\AdType;
use App\Models\Category;
use App\Models\City;
use App\Models\User;
use App\Mail\AdSentByEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use App\Helpers\Localization\Helpers\Country as CountryLocalizationHelper;
use App\Helpers\Localization\Country as CountryLocalization;
use Torann\LaravelMetaTags\Facades\MetaTag;
use App\Helpers\Rules;
use Illuminate\Support\Facades\Validator;

class SearchController extends FrontController
{
    public $request;
	public $countries;
	public $isIndexSearch = false;
	public $isCatSearch = false;
	public $isLocSearch = false;
	public $isUserSearch = false;
	public $isCompanySearch = false;

	protected $city = null;
	private $cats;

	/**
	 * SearchController constructor.
	 * @param Request $request
	 */
    public function __construct(Request $request)
    {
        parent::__construct();

        // From Laravel 5.3.4 or above
        $this->middleware(function ($request, $next) {
            $this->commonQueries();
            return $next($request);
        });

        $this->request = $request;
    }

    /**
     * Common Queries
     */
    public function commonQueries()
    {
        $countries = CountryLocalizationHelper::transAll(CountryLocalization::getCountries(), $this->lang->get('abbr'));
        $this->countries = $countries;
        view()->share('countries', $countries);

        // CATEGORIES COLLECTION
        $cats = Category::where('translation_lang', $this->lang->get('abbr'))->orderBy('lft')->get();
        if ($cats->count() > 0) {
            $cats = collect($cats)->keyBy('translation_of');
        }
        view()->share('cats', $cats);
        $this->cats = $cats;


        // COUNT CATEGORIES ADS COLLECTION
        $sql = 'SELECT c.id, count(*) as total
				FROM ' . table('ads') . ' as a
				INNER JOIN ' . table('categories') . ' as c ON c.id=a.category_id AND c.active=1
				WHERE a.country_code = :country_code AND a.active=1 AND a.archived!=1 AND a.deleted_at IS NULL
				GROUP BY c.id';
        $bindings = [
            'country_code' => $this->country->get('code')
        ];
        $count_cat_ads = DB::select(DB::raw($sql), $bindings);
        $count_cat_ads = collect($count_cat_ads)->keyBy('id');
        view()->share('count_cat_ads', $count_cat_ads);


        // CITIES COLLECTION
        $cities = City::where('country_code', '=', $this->country->get('code'))
            ->take(100)
            ->orderBy('population', 'DESC')
            ->orderBy('name')
            ->get();
        view()->share('cities', $cities);


        // ADTYPE COLLECTION
        $ad_types = AdType::where('translation_lang', $this->lang->get('abbr'))->orderBy('lft')->get();
        view()->share('ad_types', $ad_types);


        // DATE RANGE COLLECTION
        $dates = Arr::toObject([
            '2' 	=> '24 ' . t('hours'),
            '4' 	=> '3 ' . t('days'),
            '8' 	=> '7 ' . t('days'),
            '31' 	=> '30 ' . t('days'),
        ]);
        view()->share('dates', $dates);

        // STATES COLLECTION => MODAL
        $states = SubAdmin1::where('code', 'LIKE', $this->country->get('code') . '.%')->orderBy('name')->get(['code', 'name'])->keyBy('code');
        view()->share('states', $states);
    }


    /**
     * @return View
     */
    public function index()
    {
		$this->isIndexSearch = true;

		$cat = $this->getSelectedCategory();
		$location = $this->getSelectedLocation();

        $search = new Search($this->request, $this->country, $this->lang);
        $data = $search->fechAll();
        view()->share('count', $data['count']);
        view()->share('ads', $data['ads']);

		$this->exportRequiredVars();
        
        // HEAD: BUILD TITLE & DESCRIPTION
        if ($this->isIndexSearch) {
            $title = t('Search for') . ' ';
            if (Input::has('q') and Input::has('c') and Input::has('l')) {
                $title .= Input::get('q') . ' ' . $this->cat->name . ' - ' . $this->city->name;
            } else {
                if (Input::has('q') and Input::has('c') and !Input::has('l')) {
                    $title .= Input::get('q') . ' ' . $this->cat->name;
                } else {
                    if (Input::has('q') and !Input::has('c') and Input::has('l')) {
                        $title .= Input::get('q') . ' - ' . $this->city->name;
                    } else {
                        if (!Input::has('q') and Input::has('c') and Input::has('l')) {
                            $title .= $this->cat->name . ' - ' . $this->city->name;
                        } else {
                            if (Input::has('q') and !Input::has('c') and !Input::has('l')) {
                                $title .= Input::get('q');
                            } else {
                                if (!Input::has('q') and Input::has('c') and !Input::has('l')) {
                                    $title .= t('jobs') . ' ' . $this->cat->name;
                                } else {
                                    if (!Input::has('q') and !Input::has('c') and Input::has('l')) {
                                        $title .= t('jobs in') . ' - ' . $this->city->name;
                                    } else {
                                        if (Input::has('r')) {
                                            $title .= t('jobs in') . ' ' . $this->city->name;
                                        } else {
                                            if (!Input::has('q') and !Input::has('c') and !Input::has('l') and !Input::has('r')) {
                                                $title = t('Latest jobs');
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            $title = t('Jobs in');
            if ($this->isCatSearch) {
                $title .= ' ' . $this->cat->name;
            } else {
                if ($this->isLocSearch) {
                    $title .= ' ' . $this->city->name;
                }
            }
        }
        // Meta Tags
        MetaTag::set('title', $title . ', ' . $this->country->get('name'));
        MetaTag::set('description', $title);
        
        return view('search.serp');
    }

    /**
     * @param $countryCode
     * @param null $catSlug
     * @return View
     */
    public function category($countryCode, $catSlug = null)
    {
        // Check multi-countries site parameters
        if (!config('larapen.core.multi_countries_website')) {
            $catSlug = $countryCode;
        }

		$this->isCatSearch = true;

		// Get category ID
		$cat = $this->getSelectedCategory($catSlug);
		if (empty($cat)) {
			$cat = Category::where('translation_lang', $this->lang->get('abbr'))->where('slug', 'LIKE', $catSlug)->first();
		}

        $cat_id = ($cat) ? $cat->tid : 0;
        
        $search = new Search($this->request, $this->country, $this->lang);
        $data = $search->setCategory($cat_id)->setRequestFilters()->fetch();
		$data['uriPathCatSlug'] = $catSlug;

        view()->share('uriPathCatSlug', $catSlug);
		$this->exportRequiredVars();
        
        // SEO
        $title = $cat->name . ' - ' . t('Jobs :category in :location', ['category' => $cat->name, 'location' => $this->country->get('name')]);
        $description = str_limit(t('Jobs :category in :location', [
                'category' => $cat->name,
                'location' => $this->country->get('name')
            ]) . '. ' . t('Looking for a job') . ' - ' . $this->country->get('name'), 200);
        
        // Meta Tags
        MetaTag::set('title', $title);
        MetaTag::set('description', $description);
        
        // Open Graph
        $this->og->title($title)->description($description)->type('website');
        if ($data['count']->get('all') > 0) {
            $filtered = $data['ads']->getCollection();
            if ($this->og->has('image')) {
                $this->og->forget('image')->forget('image:width')->forget('image:height');
            }
            /*
            foreach($pictures->get() as $picture) {
                $this->og->image(resize($picture->filename, 'large'),
                    [
                        'width'     => 600,
                        'height'    => 600
                    ]);
            }
            */
        }
        view()->share('og', $this->og);
        
        return view('search.serp', $data);
    }

    /**
     * @param $countryCode
     * @param $catSlug
     * @param null $subCatSlug
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|View
     */
    public function subCategory($countryCode, $catSlug, $subCatSlug = null)
    {
        // Check multi-countries site parameters
        if (!config('larapen.core.multi_countries_website')) {
            $subCatSlug = $catSlug;
            $catSlug = $countryCode;
        }

        $this->isCatSearch = true;

        // Get sub-category ID
        $cat = $this->getSelectedCategory($catSlug, $subCatSlug);
        if (empty($cat)) {
            $cat = Category::where('translation_lang', $this->lang->get('abbr'))->where('slug', 'LIKE', $catSlug)->first();
            if (empty($cat)) {
                abort(404);
            }
        }
        $sub_cat = Category::where('translation_lang', $this->lang->get('abbr'))->where('parent_id', '=', $cat->tid)->where('slug', 'LIKE', $subCatSlug)->first();
        $sub_cat_id = ($sub_cat) ? $sub_cat->tid : 0;

        // Redirect to parent category if sub-category not found
        if (!isset($sub_cat_id) or $sub_cat_id <= 0 or !is_numeric($sub_cat_id)) {
            if (!is_null($cat)) {
                return redirect($this->lang->get('abbr') . '/' . trans('routes.v-search-cat', ['catSlug' => $cat->slug]));
            } else {
                abort(404);
            }
        }
        
        $search = new Search($this->request, $this->country, $this->lang);
        $data = $search->setSubCategory($sub_cat_id)->setRequestFilters()->fetch();

        view()->share('uriPathCatSlug', $catSlug);
        view()->share('uriPathSubCatSlug', $subCatSlug);
		$this->exportRequiredVars();
        
        // Meta Tags
        MetaTag::set('title', $sub_cat->name . ' - ' . t('Jobs :category in :location',
                ['category' => $cat->name, 'location' => $this->country->get('name')]));
        MetaTag::set('description', t('Jobs :category in :location', [
                'category' => $sub_cat->name,
                'location' => $this->country->get('name')
            ]) . '. ' . t('Looking for a job') . ' - ' . $this->country->get('name'));
        
        return view('search.serp', $data);
    }

    /**
     * @param $countryCode
     * @param $cityName
     * @param null $cityId
     * @return View
     */
    public function location($countryCode, $cityName, $cityId = null)
    {
        // Check multi-countries site parameters
        if (!config('larapen.core.multi_countries_website')) {
            $cityId = $cityName;
            $cityName = $countryCode;
        }

        $this->isLocSearch = true;

        $location = $this->getSelectedLocation($cityName, $cityId);
        if (empty($location)) {
            abort(404);
        }
        
        $search = new Search($this->request, $this->country, $this->lang);
        $data = $search->setLocation($location->latitude, $location->longitude)->setRequestFilters()->fetch();

        view()->share('uriPathCityName', $cityName);
        view()->share('uriPathCityId', $cityId);
		$this->exportRequiredVars();
        
        // Meta Tags
        MetaTag::set('title',
            $location->name . ' - ' . t('Jobs in :location', ['location' => $location->name]) . ', ' . $this->country->get('name'));
        MetaTag::set('description', t('Jobs in :location',
                ['location' => $location->name]) . ', ' . $this->country->get('name') . '. ' . t('Looking for a job') . ' - ' . $location->name . ', ' . $this->country->get('name'));
        
        return view('search.serp', $data);
    }

    /**
     * @param $countryCode
     * @param null $userId
     * @return View
     */
    public function user($countryCode, $userId = null)
    {
        // Check multi-countries site parameters
        if (!config('larapen.core.multi_countries_website')) {
            $userId = $countryCode;
        }

        $this->isUserSearch = true;

        $search = new Search($this->request, $this->country, $this->lang);
        $data = $search->setUser($userId)->setRequestFilters()->fetch();
		$this->exportRequiredVars();

		// Meta Tags
		$user = User::find($userId);
		if (!empty($user)) {
			$title = t('All jobs') . ' ' . t('of') . ' ' . $user->name;
			MetaTag::set('title', $title);
			MetaTag::set('description', $title . ' - ' . $this->country->get('name'));
		}
        
        return view('search.serp', $data);
    }

    /**
     * @param $countryCode
     * @param null $companyName
     * @return View
     */
	public function company($countryCode, $companyName = null)
	{
        // Check multi-countries site parameters
        if (!config('larapen.core.multi_countries_website')) {
            $companyName = $countryCode;
        }

		$this->isCompanySearch = true;

		$search = new Search($this->request, $this->country, $this->lang);
		$data = $search->setCompany($companyName)->setRequestFilters()->fetch();

        view()->share('uriPathCompanyName', $companyName);
		$this->exportRequiredVars();

		// Meta Tags
		$title = t('All jobs') . ' ' . t('at') . ' ' . $companyName;
		MetaTag::set('title', $title);
		MetaTag::set('description', $title . ' - ' . $this->country->get('name'));

		return view('search.serp', $data);
	}

	/**
	 * @param Request $request
	 * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function sendByEmail(Request $request)
	{
		// Form validation
		$validator = Validator::make($request->all(), Rules::SendAdByEmail($request));
		if ($validator->fails()) {
			return back()->withErrors($validator)->withInput();
		}

		// Get Ad
		$ad = Ad::find($request->input('ad'));
		if (!empty($ad))
		{
			// Store data
			$mailData = [
				'ad_id' 			=> $ad->id,
				'sender_email' 		=> $request->input('sender_email'),
				'recipient_email' 	=> $request->input('recipient_email'),
				'message' 			=> $request->input('message'),
			];

			// Send ad by email
            try {
                Mail::send(new AdSentByEmail($ad, $mailData));
            } catch (\Exception $e) {
                flash()->error($e->getMessage());
            }

			// Success message
			if (!session('flash_notification')) {
				flash()->success(t("Your message has sent successfully"));
			}
		}

		return redirect(URL::previous());
	}


    /**
     * CATEGORY SELECTED
     *
     * @param null $catSlug
     * @param null $subCatSlug
     * @return mixed|null
     */
    private function getSelectedCategory($catSlug = null, $subCatSlug = null)
    {
        if (!$this->isCatSearch and !Input::has('c')) {
            return null;
        }

        $cat = null;

        if (Input::has('c')) {
            $cat = $this->cats->get((int)Input::get('c'));
        } else {
            if (is_null($subCatSlug)) {
                $cat = $this->cats->whereStrict('slug', $catSlug)->flatten()->get(0);
            } else {
                $cat = $this->cats->whereStrict('slug', $catSlug)->flatten()->get(0);
                $sub_cat = $this->cats->whereStrict('slug', $subCatSlug)->flatten()->get(0);
                view()->share('sub_cat', $sub_cat);
            }
        }

        if (empty($cat)) {
            abort(404);
        }

        $this->cat = $cat;
        view()->share('cat', $cat);

        return $cat;
    }

    /**
     * CITY SELECTED
     *
     * @param null $cityName
     * @param null $cityId
     * @return array|null|\stdClass
     */
    private function getSelectedLocation($cityName = null, $cityId = null)
    {
        if (is_null($cityId) and !Input::has('r') and !Input::has('l') and !Input::has('location')) {
            return null;
        }

        if (Input::has('r') and !Input::has('l')) {
            // If REGION
            // NOTE: city = SubAdmin1 (Just for Search result page title)
            $region = Input::get('r');
            $city = Search::searchCountryPopularCityByRegion($this->country->get('code'), $region);

            // If empty... then return collection of URL parameters
            if (empty($city)) {
                $city = Arr::toObject(['name' => $region . ' (-)', 'subadmin1_code' => 0]);
            }
        }
        else
        {
            // If NOT REGION
            if (Input::has('l'))
            {
                $city = City::find(Input::get('l'));
            }
            else if (Input::has('location'))
            {
                $cityName = rawurldecode(Input::get('location'));
                $city = City::where('country_code', $this->country->get('code'))->where('name', 'LIKE', $cityName)->first();
                if (empty($city)) {
                    $city = City::where('country_code', $this->country->get('code'))->where('name', 'LIKE', '% ' . $cityName)->first();
                    if (empty($city)) {
                        $city = City::where('country_code', $this->country->get('code'))->where('name', 'LIKE', $cityName . ' %')->first();
                    }
                }
            }
            else
            {
                // Get City by Id
                $city = City::find((int)$cityId);

                // Get City by (raw) Name - @todo: delete this in the next releases
                if (empty($city)) {
                    $cityName = rawurldecode($cityName);
                    $city = City::where('country_code', $this->country->get('code'))->where('name', 'LIKE', $cityName)->first();
                    if (empty($city)) {
                        $city = City::where('country_code', $this->country->get('code'))->where('name', 'LIKE', '% ' . $cityName)->first();
                        if (empty($city)) {
                            $city = City::where('country_code', $this->country->get('code'))->where('name', 'LIKE', $cityName . ' %')->first();
                        }
                    }
                }
            }
        }

        if (empty($city)) {
            abort(404);
        }

        $this->city = $city;
        view()->share('city', $city);

        return $city;
    }

	private function exportRequiredVars()
	{
		view()->share('isIndexSearch', $this->isIndexSearch);
		view()->share('isCatSearch', $this->isCatSearch);
		view()->share('isLocSearch', $this->isLocSearch);
		view()->share('isUserSearch', $this->isUserSearch);
		view()->share('isCompanySearch', $this->isCompanySearch);
	}
}
