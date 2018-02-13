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
use App\Helpers\Rules;
use App\Models\Page;
use App\Mail\FormSent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Torann\LaravelMetaTags\Facades\MetaTag;
use Larapen\TextToImage\Facades\TextToImage;

class PageController extends FrontController
{
    /**
     * @param $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index($slug)
    {
        $page = Page::where('slug', $slug)->where('translation_lang', $this->lang->get('abbr'))->first();
        if (empty($page)) {
            abort(404);
        }
        view()->share('page', $page);
        view()->share('uriPathPageSlug', $slug);

        // SEO
        $title = $page->title;
        $description = str_limit(str_strip($page->content), 200);

        // Meta Tags
        MetaTag::set('title', $title);
        MetaTag::set('description', $description);

        // Open Graph
        $this->og->title($title)->description($description);
        if (!empty($page->picture)) {
            if ($this->og->has('image')) {
                $this->og->forget('image')->forget('image:width')->forget('image:height');
            }
            $this->og->image(url($page->picture), [
                'width'  => 600,
                'height' => 600,
            ]);
        }
        view()->share('og', $this->og);

        return view('pages.index');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function contact()
    {
        // SEO
        $title = t('Contact Us');
        $description = str_limit(str_strip(t('Contact Us')), 200);

        // Meta Tags
        MetaTag::set('title', $title);
        MetaTag::set('description', $description);

        // Open Graph
        $this->og->title($title)->description($description);
        view()->share('og', $this->og);

        return view('pages.contact');
    }

    /**
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function contactPost(Request $request)
    {
        // Form validation
        $validator = Validator::make($request->all(), Rules::ContactUs($request, 'POST'));
        if ($validator->fails()) {
            // BugFix with : $request->except('pictures')
            return back()->withErrors($validator)->withInput();
        }

        // Store Contact Info
        $contact_form = [
            'country_code' => $this->country->get('code'),
            'country'      => $this->country->get('name'),
            'first_name'   => $request->input('first_name'),
            'last_name'    => $request->input('last_name'),
            'company_name' => $request->input('company_name'),
            'email'        => $request->input('email'),
            'message'      => $request->input('message'),
        ];
        $contact_form = Arr::toObject($contact_form);

        // Send Contact Email
        try {
            if (config('settings.app_email')) {
                $recipient = [
                    'email' => config('settings.app_email'),
                    'name'  => config('settings.app_name'),
                ];
                $recipient = Arr::toObject($recipient);
                Mail::send(new FormSent($contact_form, $recipient));
            } else {
                $admins = User::where('is_admin', 1)->get();
                if ($admins->count() > 0) {
                    foreach ($admins as $admin) {
                        Mail::send(new FormSent($contact_form, $admin));
                    }
                }
            }
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }

        if (!session('flash_notification')) {
            flash()->success(t("Your message has been sent to our moderators. Thank you"));
        }

        return redirect($this->lang->get('abbr') . '/' . trans('routes.contact'));
    }
}
