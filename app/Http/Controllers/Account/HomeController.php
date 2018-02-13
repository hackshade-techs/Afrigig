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

namespace App\Http\Controllers\Account;

use App\Models\Resume;
use App\Models\UserType;
use Creativeorange\Gravatar\Facades\Gravatar;
use App\Models\Ad;
use App\Models\SavedAd;
use App\Models\Gender;
use Illuminate\Support\Facades\DB;
use Torann\LaravelMetaTags\Facades\MetaTag;
use App\Helpers\Localization\Helpers\Country as CountryLocalizationHelper;
use App\Helpers\Localization\Country as CountryLocalization;

class HomeController extends AccountBaseController
{
	/**
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
    public function index()
    {
        $data = [];

        $data['countries'] = CountryLocalizationHelper::transAll(CountryLocalization::getCountries(), $this->lang->get('abbr'));
        $data['genders'] = Gender::where('translation_lang', $this->lang->get('abbr'))->get();
        $data['userTypes'] = UserType::all();
        $data['gravatar'] = Gravatar::fallback(url('images/user.jpg'))->get($this->user->email);

        // Mini Stats
        $data['ad_counter'] = DB::table('ads')
            ->select('user_id', DB::raw('SUM(visits) as total_visits'))
            ->where('country_code', $this->country->get('code'))
            ->where('user_id', $this->user->id)
            ->groupBy('user_id')
            ->first();
        $data['countAds'] = Ad::where('country_code', $this->country->get('code'))
            ->where('user_id', $this->user->id)
            ->count();
        $data['countFavoriteAds'] = SavedAd::whereHas('ad', function($query) {
                $query->where('country_code', $this->country->get('code'));
            })
            ->where('user_id', $this->user->id)
            ->count();
        
        $data['resume'] = Resume::where('user_id', $this->user->id)->first();

        // Meta Tags
        MetaTag::set('title', t('My account'));
        MetaTag::set('description', t('My account on :app_name', ['app_name' => config('settings.app_name')]));

        return view('account.home', $data);
    }
}
