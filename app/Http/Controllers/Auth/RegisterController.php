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

namespace App\Http\Controllers\Auth;

use App\Mail\UserNotification;
use App\Mail\UserRegistered;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Helpers\Ip;
use App\Helpers\Rules;
use App\Models\Resume;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\ReviewedScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use App\Models\Ad;
use App\Models\Gender;
use App\Models\UserType;
use App\Models\User;
use App\Http\Controllers\FrontController;
use Illuminate\Http\Request;
use Torann\LaravelMetaTags\Facades\MetaTag;
use App\Helpers\Localization\Helpers\Country as CountryLocalizationHelper;
use App\Helpers\Localization\Country as CountryLocalization;
use Illuminate\Support\Facades\File;

class RegisterController extends FrontController
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/account';

    /**
     * @var array
     */
    public $msg = [];

    /**
     * SignupController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // From Laravel 5.3.4 or above
        $this->middleware(function ($request, $next) {
            $this->commonQueries();

            return $next($request);
        });

        /*
         * Messages
         */
        if (config('settings.require_users_activation') == 1) {
            $this->msg['signup']['success'] = "Your account has been created. Activation link sent by email, Please Check and Activate.";
        } else {
            $this->msg['signup']['success'] = "Your account has been created.";
        }
        $this->msg['activation']['success'] = "Congratulation :first_name ! Your account has been activated.";
        $this->msg['activation']['multiple'] = "Your account is already activated.";
        $this->msg['activation']['error'] = "Your account's activation has failed.";
    }

    /**
     * Common Queries
     */
    public function commonQueries()
    {
        $this->redirectTo = $this->lang->get('abbr') . '/account';
    }

    /**
     * Show the form the create a new user account.
     *
     * @return View
     */
    public function showRegistrationForm()
    {
        $data = [];

        // References
        $data['countries'] = CountryLocalizationHelper::transAll(CountryLocalization::getCountries(), $this->lang->get('abbr'));
        $data['genders'] = Gender::where('translation_lang', $this->lang->get('abbr'))->get();
        $data['userTypes'] = UserType::all();

        // Meta Tags
        MetaTag::set('title', t('Sign Up'));
        MetaTag::set('description', t('Sign Up on :app_name !', ['app_name' => mb_ucfirst(config('settings.app_name'))]));

        return view('auth.signup.index', $data);
    }

    /**
     * Store a new ad post.
     *
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        // Form validation
        $validator = Validator::make($request->all(), Rules::Signup($request));
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }


        // Store User
        $userInfo = [
            'country_code'     => $this->country->get('code'),
            'gender_id'        => $request->input('gender'),
            'name'             => $request->input('name'),
            'user_type_id'     => $request->input('user_type'),
            'phone'            => $request->input('phone'),
            'email'            => $request->input('email'),
            'password'         => bcrypt($request->input('password')),
            'phone_hidden'     => $request->input('phone_hidden'),
            'ip_addr'          => Ip::get(),
            'activation_token' => md5(uniqid()),
            'active'           => (config('settings.require_users_activation') == 1) ? 0 : 1,
        ];
        $user = new User($userInfo);
        $user->save();


        // Add Job seekers resume
        if ($request->input('user_type') == 3) {
            if ($request->hasFile('filename')) {
                // Save user's resume
                $resumeInfo = [
                    'country_code' => $this->country->get('code'),
                    'user_id'      => $user->id,
                    'active'       => 1,
                ];
                $resume = new Resume($resumeInfo);
                $resume->save();

                // Upload user's resume
                $resume->filename = $request->file('filename');
                $resume->save();
            }
        }

        // Update Ads created by this email
        if (isset($user->id) and $user->id > 0) {
            Ad::withoutGlobalScopes([ActiveScope::class, ReviewedScope::class])->where('contact_email', $request->input('email'))->update(['user_id' => $user->id]);
        }

        // Send Welcome Email
        if (config('settings.require_users_activation') == 1) {
            try {
                Mail::send(new UserRegistered($user));
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
                        Mail::send(new UserNotification($user, $admin));
                    }
                }
            } catch (\Exception $e) {
                flash()->error($e->getMessage());
            }
        }

        // Redirect new users to the user area if Users activation is not required
        if ((int)config('settings.require_users_activation') != 1) {
            if (Auth::loginUsingId($user->id)) {
                return redirect()->intended($this->lang->get('abbr') . '/account');
            }
        }

        return redirect($this->lang->get('abbr') . '/signup/success')->with(['success' => 1, 'message' => t($this->msg['signup']['success'])]);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|View
     */
    public function success()
    {
        if (!session('success')) {
            return redirect($this->lang->get('abbr') . '/');
        }

        // Meta Tags
        MetaTag::set('title', session('message'));
        MetaTag::set('description', session('message'));

        return view('auth.signup.success');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|View
     */
    public function activation($token)
    {
        $user = User::withoutGlobalScope(ActiveScope::class)->where('activation_token', $token)->first();

        if ($user) {
            if ($user->active != 1) {
                // Activate
                $user->active = 1;
                $user->save();
                flash()->success(t($this->msg['activation']['success'], ['first_name' => $user->name]));
            } else {
                flash()->error(t($this->msg['activation']['multiple']));
            }
            // Connect the User
            if (Auth::loginUsingId($user->id)) {
                //$this->user = Auth::user();
                //view()->share('user', $this->user);
                return redirect($this->lang->get('abbr') . '/account');
            } else {
                return redirect($this->lang->get('abbr') . '/' . trans('routes.login'));
            }
        } else {
            $data = ['error' => 1, 'message' => t($this->msg['activation']['error'])];
        }

        // Meta Tags
        MetaTag::set('title', $data['message']);
        MetaTag::set('description', $data['message']);

        return view('auth.signup.activation', $data);
    }
}
