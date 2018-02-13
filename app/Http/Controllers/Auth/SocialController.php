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

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Helpers\Ip;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\FrontController;
use App\Models\User;
use Illuminate\Support\Facades\Request as Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\Ad;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\ReviewedScope;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserNotification;

class SocialController extends FrontController
{
    use AuthenticatesUsers;

    protected $redirectTo = '/account';
    protected $redirectPath = '/account';
    private $network = ['facebook', 'google', 'twitter'];

    /**
     * SocialController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Redirect the user to the Provider authentication page.
     *
	 * @return mixed
	 */
    public function redirectToProvider()
    {
        $provider = Request::segment(2);
        if (!in_array($provider, $this->network)) {
            $provider = Request::segment(3);
        }
        if (!in_array($provider, $this->network)) {
            abort(404);
        }
        
        return Socialite::driver($provider)->redirect();
    }
    
    /**
     * Obtain the user information from Provider.
     *
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
    public function handleProviderCallback()
    {
        $provider = Request::segment(2);
        if (!in_array($provider, $this->network)) {
            $provider = Request::segment(3);
        }
        if (!in_array($provider, $this->network)) {
            abort(404);
        }
        
        // Country Code
        if (isset($this->country) and $this->country) {
            $country_code = $this->country->get('code');
        } else {
            $country_code = (isset($this->ip_country) and $this->ip_country) ? $this->ip_country->get('code') : null;
        }
        
        // API CALL - GET USER FROM PROVIDER
        try {
            $user_data = Socialite::driver($provider)->user();

            // Data not found
            if (!$user_data) {
                flash()->error(t("Unknown error. Please try again in a few minutes."));
                return redirect($this->lang->get('abbr') . '/' . trans('routes.login'));
            }

            // Email not found
            if (!$user_data OR !filter_var($user_data->getEmail(), FILTER_VALIDATE_EMAIL)) {
                flash()->error(t("Email address not found. You can't use your :provider account on our website.", ['provider' => ucfirst($provider)]));
                return redirect($this->lang->get('abbr') . '/' . trans('routes.login'));
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if (is_string($msg) and !empty($msg)) {
                flash()->error($msg);
            } else {
                flash()->error("Unknown error. The social network API doesn't work.");
            }
            
            return redirect($this->lang->get('abbr') . '/' . trans('routes.login'));
        }
        
        // Debug
        // dd($user_data);
        
        // DATA MAPPING
        try {
            $map_user = [];
            if ($provider == 'facebook') {
                $map_user['name'] = (isset($user_data->user['name'])) ? $user_data->user['name'] : '';
                if ($map_user['name'] == '') {
                    if (isset($user_data->user['first_name']) and isset($user_data->user['last_name'])) {
                        $map_user['name'] = $user_data->user['first_name'] . ' ' . $user_data->user['last_name'];
                    }
                }
            } else {
                if ($provider == 'google') {
                    $map_user = [
                        'name' => (isset($user_data->name)) ? $user_data->name : '',
                    ];
                }
            }

            // GET LOCAL USER
            $user = User::where('provider', $provider)->where('provider_id', $user_data->getId())->first();

            // CREATE LOCAL USER IF DON'T EXISTS
            if (empty($user)) {
                // Before... Check if user has not signup with an email
                $user = User::where('email', $user_data->getEmail())->first();
                if (empty($user)) {
                    $user_info = [
                        'country_code' => $country_code,
                        'name'         => $map_user['name'],
                        'email'        => $user_data->getEmail(),
                        'ip_addr'      => Ip::get(),
                        'active'       => 1,
                        'provider'     => $provider,
                        'provider_id'  => $user_data->getId(),
                        'created_at'   => date('Y-m-d H:i:s'),
                    ];
                    $user = new User($user_info);
                    $user->save();

                    // Update Ads created by this email
                    if (isset($user->id) and $user->id > 0) {
                        Ad::withoutGlobalScopes([ActiveScope::class, ReviewedScope::class])->where('contact_email', $user_info['email'])->update(['user_id' => $user->id]);
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
                    
                } else {
                    // Update 'created_at' if empty (for time ago module)
                    if (empty($user->created_at)) {
                        $user->created_at = date('Y-m-d H:i:s');
                        $user->save();
                    }
                }
            }

            // GET A SESSION FOR USER
            if (Auth::loginUsingId($user->id)) {
                return redirect()->intended($this->lang->get('abbr') . '/account');
            } else {
                flash()->error("The Email Address or Password don't match.");

                return redirect($this->lang->get('abbr') . '/' . trans('routes.login'));
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if (is_string($msg) and !empty($msg)) {
                flash()->error($msg);
            } else {
                flash()->error("Unknown error. The service does not work.");
            }

            return redirect($this->lang->get('abbr') . '/' . trans('routes.login'));
        }
    }
}
