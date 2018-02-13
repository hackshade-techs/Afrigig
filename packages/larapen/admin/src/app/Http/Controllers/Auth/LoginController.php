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

namespace Larapen\Admin\app\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Larapen\Admin\app\Http\Controllers\Controller;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $loginPath;
    protected $redirectTo;
    protected $redirectAfterLogout;
    protected $data;

	/**
	 * AuthController constructor.
	 */
    public function __construct()
    {
		parent::__construct();

        $this->middleware('guest')->except(['except' => 'logout']);

		$this->loginPath = config('larapen.admin.route_prefix', 'admin') . '/login';
		$this->redirectTo = config('larapen.admin.route_prefix', 'admin');
		$this->redirectAfterLogout = config('larapen.admin.route_prefix', 'admin') . '/login';
    }

    // -------------------------------------------------------
    // Laravel overwrites for loading admin views
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
            return redirect()->intended($this->redirectTo);
        }

        $this->data['title'] = trans('admin::messages.login'); // set the page title

        return view('admin::auth.login', $this->data);
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

		return redirect(property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/');
	}
}
