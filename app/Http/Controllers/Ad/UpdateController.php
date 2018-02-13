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
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\ReviewedScope;
use Illuminate\Http\Request;
use App\Http\Controllers\FrontController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Torann\LaravelMetaTags\Facades\MetaTag;
use App\Helpers\Localization\Helpers\Country as CountryLocalizationHelper;
use App\Helpers\Localization\Country as CountryLocalization;
use App\Helpers\Payment as PaymentHelper;

class UpdateController extends FrontController
{
    use PaymentTrait;

    public $request;
    public $data;
    public $msg = [];
    public $uri = [];
    public $packages;
    public $payment_methods;

    /**
     * UpdateController constructor.
     */
    public function __construct()
    {
        parent::__construct();

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
        $this->msg['post']['success'] = t("Your Ad has been updated.");
        $this->msg['checkout']['success'] = t("We have received your payment.");
        $this->msg['checkout']['cancel'] = t("We have not received your payment. Payment cancelled.");
        $this->msg['checkout']['error'] = t("We have not received your payment. An error occurred.");

        // URL Paths
        $this->uri['form'] = $this->lang->get('abbr') . '/update/#entryId';
        $this->uri['success'] = $this->lang->get('abbr') . '/update/#entryId/success';

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
            }
        ])->orderBy('lft')->get();
        $data['ad_types'] = AdType::where('translation_lang', $this->lang->get('abbr'))->get();
        $data['salary_type'] = SalaryType::where('translation_lang', $this->lang->get('abbr'))->get();
        //$data['states'] = City::where('country_code', $this->country->get('code'))->where('feature_code', 'ADM1')->get()->all();
        $data['packages'] = Package::where('translation_lang', $this->lang->get('abbr'))->with('currency')->orderBy('lft')->get();
        $data['payment_methods'] = PaymentMethod::orderBy('lft')->get();

        $this->countries = $data['countries'];
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
     * @param $adId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getForm($adId)
    {
        $data = array();
        
        // Get Ad
        // GET ADS INFO
        $ad = Ad::withoutGlobalScopes([ActiveScope::class, ReviewedScope::class])->where('user_id', $this->user->id)->where('id', $adId)->with([
            'user',
            'country',
            'category',
            'adType' => function ($query) {
                $query->where('translation_lang', $this->lang->get('abbr'));
            },
            'city',
            'pictures'
        ])->first();
        
        if (is_null($ad)) {
            abort(404);
        }
        view()->share('ad', $ad);

        // Get current Payment
        $current_payment = null;
        if ($ad->featured == 1) {
            $current_payment = Payment::where('ad_id', $ad->id)->orderBy('created_at', 'DESC')->first();
        }
        view()->share('current_payment', $current_payment);

        // Get the package of the current payment (if exists)
        if (isset($current_payment) && !empty($current_payment)) {
            $current_payment_package = Package::transById($current_payment->package_id);
            view()->share('current_payment_package', $current_payment_package);
        }
        
        // Meta Tags
        MetaTag::set('title', t('Update My Ad'));
        MetaTag::set('description', t('Update My Ad'));
        
        return view('ad.edit', $data);
    }
    
    /**
     * Store a new ad post.
     *
     * @param $adId
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function postForm($adId, Request $request)
    {
        // Form validation
        $validator = Validator::make($request->all(), Rules::Ad($request, 'PUT'));
        if ($validator->fails()) {
            // BugFix with : $request->except('logo')
            return back()->withErrors($validator)->withInput($request->except('logo'));
        }
        
        // Get Ad
        $ad = Ad::withoutGlobalScopes([ActiveScope::class, ReviewedScope::class])->where('user_id', $this->user->id)->where('id', $adId)->first();
        if (empty($ad)) {
            abort(404);
        }
        
        
        // Update Ad
        // @todo: In this version user can't change the country her Ad! Please add a SELECT BOX in the post view to activate this functionality.
        // $ad->country_code = $request->input('country_code');
        $ad->category_id = $request->input('category');
        $ad->ad_type_id = $request->input('ad_type');
        $ad->company_name = $request->input('company_name');
        $ad->company_description = $request->input('company_description');
		$ad->company_website = $request->input('company_website');
		$ad->title = $request->input('title');
		$ad->description = $request->input('description');
        $ad->salary_min = $request->input('salary_min');
		$ad->salary_max = $request->input('salary_max');
		$ad->salary_type_id = $request->input('salary_type');
        $ad->negotiable = $request->input('negotiable');
		$ad->start_date = $request->input('start_date');
        $ad->contact_name = $request->input('contact_name');
        $ad->contact_email = $request->input('contact_email');
        $ad->contact_phone = $request->input('contact_phone');
        $ad->contact_phone_hidden = $request->input('contact_phone_hidden');
        $ad->ip_addr = Ip::get();
        $ad->save();

        // Save logo
        if ($request->hasFile('logo')) {
            $ad->logo = $request->file('logo');
            $ad->save();
        }

        // CHECK PAYMENT !!!

        // Check if selected pack has been already paid for this ad
        $alreadyPaidPackage = false;
        $current_payment = Payment::where('ad_id', $ad->id)->orderBy('created_at', 'DESC')->first();
        if (!empty($current_payment)) {
            if ($current_payment->package_id == $request->input('package')) {
                $alreadyPaidPackage = true;
            }
        }

        // Check if payment process is required
        $package = Package::find($request->input('package'));
        if (!empty($package) && $package->price > 0 && $request->has('payment_method') && !$alreadyPaidPackage) {
            $paymentResult = $this->postPayment($request, $ad);

            if (is_a($paymentResult, 'Illuminate\Http\RedirectResponse')) {
                return $paymentResult;
            }
            if (is_bool($paymentResult) === true && !$paymentResult) {
                $this->uri['form'] = str_replace('#entryId', $ad->id, $this->uri['form']);
                return redirect($this->uri['form'] . '?error=payment');
            }
        }

        // FOR NOT PAID ENTRIES

        $country_code = strtoupper($this->country->get('code'));
        if ($this->countries->has($country_code)) {
            $urlPath = $this->lang->get('abbr') . '/' . slugify($ad->title) . '/' . $ad->id . '.html';
        } else {
            $urlPath = '/';
        }

        $message = t("Your Ad has been updated.");
        flash()->success($message);

        return redirect($urlPath);
    }

    /**
     * @param $adId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function success($adId)
    {
        if (!session('success')) {
            return redirect($this->lang->get('abbr') . '/account/myads');
        }

        // Meta Tags
        MetaTag::set('title', session('message'));
        MetaTag::set('description', session('message'));
        
        return view('ad.success');
    }
}
