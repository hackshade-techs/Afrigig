<?php
/**
 * LaraClassified - Geo Classified Ads CMS
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

namespace Larapen\Admin\app\Http\Controllers;

use App\Http\Requests;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Prologue\Alerts\Facades\Alert;
use App\Models\Ad;
use App\Models\Country;
use App\Models\User;

class DashboardController extends PanelController
{
	public $data = []; // the information we send to the view

	/**
	 * Create a new controller instance.
	 */
	public function __construct()
	{
		$this->middleware('admin');
		parent::__construct();

		// Get site mini stats
		if (config('settings.ads_review_activation')) {
			$countUnactivatedAds = Ad::where('active', 0)->orWhere('reviewed', 0)->count();
			$countActivatedAds = Ad::where('active', 1)->where('reviewed', 1)->count();
		} else {
			$countUnactivatedAds = Ad::where('active', 0)->count();
			$countActivatedAds = Ad::where('active', 1)->count();
		}
		$countUnactivatedUsers = User::where('is_admin', 0)->where('active', 0)->count();
		$countActivatedUsers = User::where('is_admin', 0)->where('active', 1)->count();
		$countUsers = User::where('is_admin', 0)->count();
		$countCountries = Country::where('active', 1)->count();

		view()->share('countUnactivatedAds', $countUnactivatedAds);
		view()->share('countActivatedAds', $countActivatedAds);
		view()->share('countUnactivatedUsers', $countUnactivatedUsers);
		view()->share('countActivatedUsers', $countActivatedUsers);
		view()->share('countUsers', $countUsers);
		view()->share('countCountries', $countCountries);
	}

	/**
	 * Show the admin dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function dashboard()
	{
        // Limit latest entries
        $latestEntriesLimit = 5;

        // Get latest Ads
        $ads = Ad::take($latestEntriesLimit)->orderBy('id', 'DESC')->get();
        $this->data['ads'] = $ads;

        // Get latest Users
        $users = User::take($latestEntriesLimit)->orderBy('id', 'DESC')->get();
        $this->data['users'] = $users;

        // Get Stats
        $statDayNumber = 30;
        setlocale(LC_TIME, config('applang.locale'));
        $currentDate = Carbon::now();

        $stats = [];
        for ($i = 1; $i <= $statDayNumber; $i++) {
            $dateObj = ($i == 1) ? $currentDate : $currentDate->subDay();
            $date = $dateObj->toDateString();

            // Ads Stats
            if (config('settings.ads_review_activation')) {
                $countActivatedAds = Ad::where('created_at', '>=', $date)->where('created_at', '<=', $date . ' 23:59:59')->where('active', 1)->where('reviewed', 1)->count();
                $countUnactivatedAds = Ad::where('created_at', '>=', $date)->where('created_at', '<=', $date . ' 23:59:59')->where('active', 0)->orWhere('reviewed', 0)->count();
            } else {
                $countActivatedAds = Ad::where('created_at', '>=', $date)->where('created_at', '<=', $date . ' 23:59:59')->where('active', 1)->count();
                $countUnactivatedAds = Ad::where('created_at', '>=', $date)->where('created_at', '<=', $date . ' 23:59:59')->where('active', 0)->count();
            }
            $stats['ads'][$i]['y'] = ucfirst($dateObj->formatLocalized('%b %d'));
            $stats['ads'][$i]['activated'] = $countActivatedAds;
            $stats['ads'][$i]['unactivated'] = $countUnactivatedAds;

            // Users Stats
            $countActivatedUsers = User::where('created_at', '>=', $date)->where('created_at', '<=', $date . ' 23:59:59')->where('is_admin', 0)->where('active', 1)->count();
            $countUnactivatedUsers = User::where('created_at', '>=', $date)->where('created_at', '<=', $date . ' 23:59:59')->where('is_admin', 0)->where('active', 0)->count();
            $stats['users'][$i]['y'] = ucfirst($dateObj->formatLocalized('%b %d'));
            $stats['users'][$i]['activated'] = $countActivatedUsers;
            $stats['users'][$i]['unactivated'] = $countUnactivatedUsers;
        }

        $stats['ads'] = array_reverse($stats['ads'], true);
        $stats['users'] = array_reverse($stats['users'], true);

        $this->data['adsStats'] = json_encode(array_values($stats['ads']), JSON_NUMERIC_CHECK);
        $this->data['usersStats'] = json_encode(array_values($stats['users']), JSON_NUMERIC_CHECK);

		$this->data['title'] = trans('admin::messages.dashboard'); // set the page title

		return view('admin::dashboard', $this->data);
	}

	/**
	 * Redirect to the dashboard.
	 *
	 * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
	 */
	public function redirect()
	{
		// The '/admin' route is not to be used as a page, because it breaks the menu's active state.
		return redirect(config('larapen.admin.route_prefix', 'admin') . '/dashboard');
	}
}
