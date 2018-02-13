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

use App\Helpers\Ip;
use App\Helpers\Rules;
use App\Models\Ad;
use App\Models\AdType;
use App\Models\Category;
use App\Models\Package;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\City;
use App\Models\SalaryType;
use App\Models\User;
use App\Http\Controllers\FrontController;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\ReviewedScope;
use App\Mail\AdNotification;
use App\Mail\AdPosted;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Torann\LaravelMetaTags\Facades\MetaTag;
use App\Helpers\Localization\Helpers\Country as CountryLocalizationHelper;
use App\Helpers\Localization\Country as CountryLocalization;
use App\Helpers\Payment as PaymentHelper;

class PostController extends FrontController
{
    use PaymentTrait;

    public $data;
    public $msg = [];
    public $uri = [];
    public $packages;
    public $payment_methods;

    /**
     * PostController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // Check if guests can post Ads
        if (config('settings.activation_guests_can_post') != '1') {
            $this->middleware('auth')->only(['getForm', 'postForm']);
        }

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
        // Messages
        $this->msg['post']['success'] = t("Your Ad has been created.");
        $this->msg['checkout']['success'] = t("We have received your payment. Please check your inbox to activate your ad.");
        $this->msg['checkout']['cancel'] = t("We have not received your payment. Payment cancelled.");
        $this->msg['checkout']['error'] = t("We have not received your payment. An error occurred.");
        $this->msg['activation']['success'] = "Congratulation ! Your ad \":title\" has been activated.";
        $this->msg['activation']['multiple'] = "Your ad is already activated.";
        $this->msg['activation']['error'] = "Your ad's activation has failed.";

        // URL Paths
        $this->uri['form'] = $this->lang->get('abbr') . '/' . trans('routes.create');
        $this->uri['success'] = $this->lang->get('abbr') . '/create/success';

        // Payment Helper vars
        PaymentHelper::$country = $this->country;
        PaymentHelper::$lang = $this->lang;
        PaymentHelper::$msg = $this->msg;
        PaymentHelper::$uri = $this->uri;

        // References
        $data = [];
        $data['countries'] = CountryLocalizationHelper::transAll(CountryLocalization::getCountries(), $this->lang->get('abbr'));
        $data['categories'] = Category::where('parent_id', 0)->where('translation_lang', $this->lang->get('abbr'))->with([
            'children' => function ($query) {
                $query->where('translation_lang', $this->lang->get('abbr'));
            },
        ])->orderBy('lft')->get();
        $data['ad_types'] = AdType::where('translation_lang', $this->lang->get('abbr'))->get();
        $data['salary_type'] = SalaryType::where('translation_lang', $this->lang->get('abbr'))->get();
        $data['packages'] = Package::where('translation_lang', $this->lang->get('abbr'))->with('currency')->orderBy('lft')->get();
        $data['payment_methods'] = PaymentMethod::orderBy('lft')->get();

        $this->packages = $data['packages'];
        $this->payment_methods = $data['payment_methods'];
        Rules::$packages = $this->packages;
        Rules::$payment_methods = $this->payment_methods;

        view()->share('countries', $data['countries']);
        view()->share('categories', $data['categories']);
        view()->share('ad_types', $data['ad_types']);
        view()->share('salary_type', $data['salary_type']);
        view()->share('packages', $data['packages']);
        view()->share('payment_methods', $data['payment_methods']);
    }

    /**
     * Show the form the create a new ad post.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getForm()
    {
        // Only Admin users and Employers/Companies can post ads
        if (Auth::check()) {
            if (!in_array($this->user->user_type_id, [1, 2])) {
                return redirect()->intended($this->lang->get('abbr') . '/account');
            }
        }

        // Meta Tags
        MetaTag::set('title', t('Post a Job'));
        MetaTag::set('description', t('Post a Job') . ' - ' . $this->country->get('name') . '.');

        return view('ad.create');
    }

    /**
     * Store a new ad post.
     *
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function postForm(Request $request)
    {
        // Form validation
        $validator = Validator::make($request->all(), Rules::Ad($request, 'POST'));
        if ($validator->fails()) {
            // BugFix with : $request->except('pictures')
            return back()->withErrors($validator)->withInput($request->except('pictures'));
        }


        // Get User if exists
        if (Auth::check()) {
            $user = $this->user;
        } else {
            if ($request->has('contact_email')) {
                $user = User::where('email', $request->input('contact_email'))->first();
            }
        }

        // Get city infos
        if ($request->has('city')) {
            $city = City::find($request->input('city'));
            if (empty($city)) {
                flash()->error(t("Post Ads was disabled for this time. Please try later. Thank you."));

                return back();
            }
        }

        // Ad data
        $adInfo = [
            'country_code'         => $this->country->get('code'),
            'user_id'              => (isset($user) and !is_null($user)) ? $user->id : 0,
            'category_id'          => $request->input('category'),
            'ad_type_id'           => $request->input('ad_type'),
            'company_name'         => $request->input('company_name'),
            'company_description'  => $request->input('company_description'),
            'company_website'      => $request->input('company_website'),
            'title'                => $request->input('title'),
            'description'          => $request->input('description'),
            'salary_min'           => $request->input('salary_min'),
            'salary_max'           => $request->input('salary_max'),
            'salary_type_id'       => $request->input('salary_type'),
            'negotiable'           => $request->input('negotiable'),
            'start_date'           => $request->input('start_date'),
            'contact_name'         => $request->input('contact_name'),
            'contact_email'        => $request->input('contact_email'),
            'contact_phone'        => $request->input('contact_phone'),
            'contact_phone_hidden' => $request->input('contact_phone_hidden'),
            'city_id'              => $request->input('city'),
            'lat'                  => $city->latitude,
            'lon'                  => $city->longitude,
            'package_id'              => $request->input('package'),
            'ip_addr'              => Ip::get(),
            'activation_token'     => md5(uniqid()),
            'active'               => (config('settings.require_ads_activation') == 1) ? 0 : 1,
        ];

        // Save Ad to database
        $ad = new Ad($adInfo);
        $ad->save();

        // Save resume
        if ($request->hasFile('logo')) {
            $ad->logo = $request->file('logo');
            $ad->save();
        }


        // User country unknown (Update It!)
        if (isset($user) and isset($user->country_code) and $user->country_code == '') {
            if (is_numeric($user->id)) {
                $user = User::find($user->id);
                if (!empty($user)) {
                    $user->country_code = $this->country->get('code');
                    $user->save();
                }
            }
        }

        // CHECK PAYMENT !!!

        // Check if payment process is required
        $package = Package::find($request->input('package'));
        if (!empty($package) && $package->price > 0 && $request->has('payment_method')) {
            $paymentResult = $this->postPayment($request, $ad);

            if (is_a($paymentResult, 'Illuminate\Http\RedirectResponse')) {
                return $paymentResult;
            }
            if (is_bool($paymentResult) === true && !$paymentResult) {
                if (!Auth::check()) {
                    return redirect($this->uri['form'] . '?error=payment');
                }
            }
        }

        // FOR NOT PAID ENTRIES

        // Send Confirmation Email
        if (config('settings.require_ads_activation') == 1) {
            try {
                Mail::send(new AdPosted($ad));
            } catch (\Exception $e) {
                flash()->error($e->getMessage());
            }
        }

        // Send Admin Notification Email
        if (config('settings.admin_email_notification') == 1) {
            try {
                // Get all admin users
                $admins = User::where('is_admin', 1)->get();
                if ($admins->count() > 0) {
                    foreach ($admins as $admin) {
                        Mail::send(new AdNotification($ad, $admin));
                    }
                }
            } catch (\Exception $e) {
                flash()->error($e->getMessage());
            }
        }

        return redirect($this->uri['success'])->with(['success' => 1, 'message' => $this->msg['post']['success']]);
    }

    /**
     * Success post
     *
     * @return mixed
     */
    public function success()
    {
        if (!session('success')) {
            return redirect($this->lang->get('abbr') . '/');
        }

        // Meta Tags
        MetaTag::set('title', session('message'));
        MetaTag::set('description', session('message'));

        return view('ad.success');
    }

    /**
     * Activation
     *
     * @param $token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function activation($token)
    {
        $ad = Ad::withoutGlobalScopes([ActiveScope::class, ReviewedScope::class])->where('activation_token', $token)->first();

        if ($ad) {
            if ($ad->active != 1) {
                // Activate
                $ad->active = 1;
                $ad->save();
                flash()->success(t($this->msg['activation']['success'], ['title' => $ad->title]));
            } else {
                flash()->error(t($this->msg['activation']['multiple']));
            }

            return redirect($this->lang->get('abbr') . '/' . slugify($ad->title) . '/' . $ad->id . '.html?preview=1');
        } else {
            $data = ['error' => 1, 'message' => t($this->msg['activation']['error'])];
        }

        // Meta Tags
        MetaTag::set('title', $data['message']);
        MetaTag::set('description', $data['message']);

        return view('ad.activation', $data);
    }
}
