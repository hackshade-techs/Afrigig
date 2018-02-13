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

namespace App\Http\Controllers\Ad;

use App\Events\AdWasVisited;
use App\Helpers\Arr;
use App\Helpers\Rules;
use App\Models\Ad;
use App\Models\AdType;
use App\Models\Category;
use App\Models\City;
use App\Models\Message;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Picture;
use App\Models\ReportType;
use App\Http\Controllers\FrontController;
use App\Models\Resume;
use App\Models\User;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\ReviewedScope;
use App\Mail\EmployerContacted;
use App\Mail\ReportSent;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Larapen\TextToImage\Facades\TextToImage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request as Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Torann\LaravelMetaTags\Facades\MetaTag;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Localization\Helpers\Country as CountryLocalizationHelper;
use App\Helpers\Localization\Country as CountryLocalization;

class DetailsController extends FrontController
{
    public $msg = [];

    /**
     * Ad expire time (in months)
     * @var int
     */
    public $expire_time = 24;

    /**
     * DetailsController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // From Laravel 5.3.4 or above
        $this->middleware(function ($request, $next) {
            $this->commonQueries();

            return $next($request);
        });

        // Messages
        $this->msg['message']['success'] = "Your message has sent successfully to :contact_name.";
        $this->msg['report']['success'] = "Your report has sent successfully to us. Thank you!";
        $this->msg['mail']['error'] = "The sending messages is not enabled. Please check the SMTP settings in the admin.";
        $this->msg['notification']['expiration'] = "Warning! This ad has expired. The product or service is not more available (may be)";
    }

    /**
     * Common Queries
     */
    public function commonQueries()
    {
        // Check Country URL for SEO
        $countries = CountryLocalizationHelper::transAll(CountryLocalization::getCountries(), $this->lang->get('abbr'));
        view()->share('countries', $countries);
    }

    /**
     * Show ad's details.
     *
     * @param $title
     * @param $adId
     * @return View
     */
    public function index($title, $adId)
    {
        $data = [];

        if (!is_numeric($adId)) {
            abort(404);
        }

        // GET ADS DETAILS
        if (Auth::check()) {
            // Get ad details even if it's not activated and reviewed
            $ad = Ad::withoutGlobalScopes([ActiveScope::class, ReviewedScope::class])->where('id', $adId)->with(['user', 'city', 'pictures'])->first();

            // If the logged user is not an admin user...
            if (Auth::user()->is_admin != 1) {
                // Then don't get ad that are not from the user
                if (isset($ad) && Auth::user()->id != $ad->user_id) {
                    // Get ad details
                    $ad = Ad::where('id', $adId)->with(['user', 'city', 'pictures'])->first();
                }
            }

            // Get resume details
            $resume = Resume::where('user_id', Auth::user()->id)->first();
            view()->share('resume', $resume);
        } else {
            // Get ad details
            $ad = Ad::where('id', $adId)->with(['user', 'city', 'pictures'])->first();
        }

        // Preview Ad after activation
        if (Input::has('preview') and Input::get('preview') == 1) {
            $ad = Ad::withoutGlobalScopes([ActiveScope::class, ReviewedScope::class])->where('id', $adId)->with(['user', 'city', 'pictures'])->first();
        }

        // dd($ad); // debug

        // Ad not found
        if (empty($ad) or empty($ad->city)) {
            abort(404);
        }

        // Share ad details
        view()->share('ad', $ad);


        // Get category details
        $cat = Category::transById($ad->category_id);
        view()->share('cat', $cat);

        // Get ad type details
        $adType = AdType::transById($ad->ad_type_id);
        view()->share('adType', $adType);


        // Require info
        if (empty($cat) or empty($adType)) {
            abort(404);
        }


        // Get package details
        $package = null;
        if ($ad->featured == 1) {
            $payment = Payment::where('ad_id', $ad->id)->orderBy('id', 'DESC')->first();
            if (!empty($payment)) {
                $package = Package::transById($payment->package_id);
            }
        }
        view()->share('package', $package);
    
    
        // Get ad's user decision about comments activation
        $commentsAreDisabledByAdUser = false;
        // Get possible ad's user
        if (isset($ad->user_id) && !empty($ad->user_id)) {
            $possibleAdUser = User::find($ad->user_id);
            if (!empty($possibleAdUser)) {
                if ($possibleAdUser->disable_comments == 1) {
                    $commentsAreDisabledByAdUser = true;
                }
            }
        }
        view()->share('commentsAreDisabledByAdUser', $commentsAreDisabledByAdUser);


        // GET PARENT CATEGORY
        if ($cat->parent_id == 0) {
            $parent_cat = $cat;
        } else {
            $parent_cat = Category::transById($cat->parent_id);
        }
        view()->share('parent_cat', $parent_cat);

        // REPORT ABUSE TYPE COLLECTION
        $report_types = ReportType::where('translation_lang', $this->lang->get('abbr'))->get();
        view()->share('report_types', $report_types);

        // Increment Ad visits counter
        Event::fire(new AdWasVisited($ad));

        // GET SIMILAR ADS
        $carousel = $this->getSimilarAds($ad);
        $data['carousel'] = $carousel;

        // SEO
        $title = $ad->title . ', ' . $ad->city->name;
        $description = str_limit(str_strip($ad->description), 200);

        // Meta Tags
        MetaTag::set('title', $title);
        MetaTag::set('description', $description);

        // Open Graph
        $this->og->title($title)->description($description)->type('article')->article(['author' => config('settings.facebook_page_url')])->article(['publisher' => config('settings.facebook_page_url')]);
        if (!$ad->pictures->isEmpty()) {
            if ($this->og->has('image')) {
                $this->og->forget('image')->forget('image:width')->forget('image:height');
            }
            foreach ($ad->pictures as $picture) {
                $this->og->image(resize($picture->filename, 'large'), [
                    'width'  => 600,
                    'height' => 600,
                ]);
            }
        }
        view()->share('og', $this->og);

        // Expiration Info
        $today_dt = Carbon::now(config('timezone.id'));
        if ($today_dt->gt($ad->created_at->addMonths($this->expire_time))) {
            flash()->error(t($this->msg['notification']['expiration']));
        }

        // View
        return view('ad.details', $data);
    }

    /**
     * @param $adId
     * @param HttpRequest $request
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function sendMessage($adId, HttpRequest $request)
    {
        $this->middleware('auth', ['only' => ['sendMessage']]);

        // Form validation
        $validator = Validator::make($request->all(), Rules::Message($request));
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Get Ad
        $ad = Ad::find($adId);
        if (empty($ad)) {
            abort(404);
        }

        // Store Message
        $message = new Message([
            'ad_id'   => $adId,
            'name'    => $request->input('sender_name'),
            'email'   => $request->input('sender_email'),
            'phone'   => $request->input('sender_phone'),
            'message' => $request->input('message'),
        ]);
        $message->save();

        // Save and Send user's resume
        $pathToFile = null;
        if ($request->hasFile('filename')) {
            $message->filename = $request->file('filename');
            $message->save();

            // Get path of uploaded file
            $pathToFile = public_path($message->filename);
        } else {
            if (Auth::check()) {
                $resume = Resume::where('user_id', Auth::user()->id)->first();
                if (!empty($resume)) {
                    $pathToFile = public_path($resume->filename);
                }
            }
        }

        // Send a message to publisher
        try {
            Mail::send(new EmployerContacted($ad, $message, $pathToFile));
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }

        // Success message
        if (!session('flash_notification')) {
            flash()->success(t($this->msg['message']['success'], ['contact_name' => $ad->contact_name]));
        }

        return redirect($this->lang->get('abbr') . '/' . slugify($ad->title) . '/' . $ad->id . '.html');
    }

    /**
     * @param $adId
     * @param HttpRequest $request
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function sendReport($adId, HttpRequest $request)
    {
        // Form validation
        $validator = Validator::make($request->all(), Rules::Report($request));
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Get Ad
        $ad = Ad::find($adId);
        if (is_null($ad)) {
            abort(404);
        }

        // Store Report
        $report = [
            'ad_id'          => $adId,
            'report_type_id' => $request->input('report_type'),
            'email'          => $request->input('report_sender_email'),
            'message'        => $request->input('report_message'),
        ];

        // Send Abus Report to admin
        try {
            if (config('settings.app_email')) {
                $recipient = [
                    'email' => config('settings.app_email'),
                    'name'  => config('settings.app_name'),
                ];
                $recipient = Arr::toObject($recipient);
                Mail::send(new ReportSent($ad, $report, $recipient));
            } else {
                $admins = User::where('is_admin', 1)->get();
                if ($admins->count() > 0) {
                    foreach ($admins as $admin) {
                        Mail::send(new ReportSent($ad, $report, $admin));
                    }
                }
            }
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }

        // Success message
        if (!session('flash_notification')) {
            flash()->success(t($this->msg['report']['success']));
        }

        return redirect($this->lang->get('abbr') . '/' . slugify($ad->title) . '/' . $ad->id . '.html');
    }

    /**
     * Get similar ads
     *
     * @param $ad
     * @param string $type
     * @return array|null|\stdClass
     */
    private function getSimilarAds($ad, $type = 'location')
    {
        switch ($type) {
            case 'category':
                return $this->getCategorySimilarAds($ad->category_id, $ad->id);
                break;
            case 'location':
                return $this->getLocationSimilarAds($ad->city_id, $ad->id);
                break;
            default:
                return $this->getLocationSimilarAds($ad->city_id, $ad->id);
        }
    }

    /**
     * Get similar ads (Ads in the same Category)
     *
     * @param $categoryId
     * @param int $currentAdId
     * @return array|null|\stdClass
     */
    private function getCategorySimilarAds($categoryId, $currentAdId = 0)
    {
        $limit = 20;
        $carousel = null;

        // Get ads from same category
        $reviewedAdSql = '';
        if (config('settings.ads_review_activation')) {
            $reviewedAdSql = ' AND a.reviewed = 1';
        }
        $sql = 'SELECT DISTINCT a.* ' . '
				FROM ' . table('ads') . ' as a
				INNER JOIN ' . table('categories') . ' as c ON c.id=a.category_id AND c.active=1
				INNER JOIN ' . table('categories') . ' as cp ON cp.id=c.parent_id AND cp.active=1
				WHERE a.country_code = :country_code 
					AND :category_id  IN (c.id, cp.id) 
					AND a.active=1 
					AND a.archived!=1 
					AND a.deleted_at IS NULL ' . $reviewedAdSql . '
					AND a.id != :current_ad_id
				ORDER BY a.created_at DESC
				LIMIT 0,' . (int)$limit;
        $bindings = [
            'country_code'  => $this->country->get('code'),
            'category_id'   => $categoryId,
            'current_ad_id' => $currentAdId,
        ];
        $ads = DB::select(DB::raw($sql), $bindings);

        if (!empty($ads)) {
            shuffle($ads);
            $carousel = [
                'title' => t('Similar Ads'),
                'link'  => qsurl($this->lang->get('abbr') . '/' . trans('routes.v-search', ['countryCode' => $this->country->get('icode')]), array_merge(Request::except('c'), ['c' => $categoryId])),
                'ads'   => $ads,
            ];
            $carousel = Arr::toObject($carousel);
        }

        return $carousel;
    }

    /**
     * Get ads in the same Location
     *
     * @param $cityId
     * @param int $currentAdId
     * @return array|null|\stdClass
     */
    private function getLocationSimilarAds($cityId, $currentAdId = 0)
    {
        $distance = 500; // km
        $limit = 20;
        $carousel = null;

        $city = City::find($cityId);

        if (!empty($city)) {
            // Get ads from same location (with radius)
            $reviewedAdSql = '';
            if (config('settings.ads_review_activation')) {
                $reviewedAdSql = ' AND a.reviewed = 1';
            }
            $sql = 'SELECT a.*, 3959 * acos(cos(radians(' . $city->latitude . ')) * cos(radians(a.lat))'
                . '* cos(radians(a.lon) - radians(' . $city->longitude . '))'
                . '+ sin(radians(' . $city->latitude . ')) * sin(radians(a.lat))) as distance
				FROM ' . table('ads') . ' as a
				INNER JOIN ' . table('categories') . ' as c ON c.id=a.category_id AND c.active=1
				WHERE a.country_code = :country_code 
					AND a.active=1 
					AND a.archived!=1 
					AND a.deleted_at IS NULL ' . $reviewedAdSql . '
					AND a.id != :current_ad_id
				HAVING distance <= ' . $distance . ' 
				ORDER BY distance ASC, a.created_at DESC 
				LIMIT 0,' . (int)$limit;
            $bindings = [
                'country_code'  => $this->country->get('code'),
                'current_ad_id' => $currentAdId,
            ];
            $ads = DB::select(DB::raw($sql), $bindings);

            if (!empty($ads)) {
                shuffle($ads);
                $carousel = [
                    'title' => t('More jobs at :distance km around :city', ['distance' => $distance, 'city' => $city->name]),
                    'link'  => qsurl($this->lang->get('abbr') . '/' . trans('routes.v-search', ['countryCode' => $this->country->get('icode')]), array_merge(Request::except(['l', 'location']), ['l' => $city->id])),
                    'ads'   => $ads,
                ];
                $carousel = Arr::toObject($carousel);
            }
        }

        return $carousel;
    }
}
