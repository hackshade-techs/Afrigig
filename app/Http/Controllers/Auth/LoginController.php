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
use Illuminate\Http\Request;

use App\Http\Controllers\FrontController;
use Illuminate\Support\Facades\Auth;
use Torann\LaravelMetaTags\Facades\MetaTag;

class LoginController extends FrontController
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    // if not logged in redirect to
    protected $loginPath = 'login';
    // after you've logged in redirect to
    protected $redirectTo = 'account';
    // after you've logged out redirect to
    protected $redirectAfterLogout = 'account';

    /**
     * LoginController constructor.
     */
    public function __construct()
    {
		parent::__construct();

		$this->middleware('guest')->except(['except' => 'logout']);

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
        $this->loginPath = $this->lang->get('abbr') . '/' . trans('routes.login');
        $this->redirectTo = $this->lang->get('abbr') . '/account';
        $this->redirectAfterLogout = $this->lang->get('abbr') . '/' . trans('routes.login');
    }

    // -------------------------------------------------------
    // Laravel overwrites for loading JobClass views
    // -------------------------------------------------------

    /**
     * Show the application login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        // Remembering Login
        if (Auth::viaRemember()) {
            return redirect()->intended($this->lang->get('abbr') . '/account');
        }

        // Meta Tags
        MetaTag::set('title', t('Login'));
        MetaTag::set('description', t('Log in to :app_name', ['app_name' => config('settings.app_name')]));

        return view('auth.login');
    }

	/**
	 * @param Request $request
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function logout(Request $request)
	{
		$this->guard()->logout();
		$request->session()->flush();
		$request->session()->regenerate();

		flash()->success(t('You have been logged out.'));

		return redirect(property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/');
	}
}
