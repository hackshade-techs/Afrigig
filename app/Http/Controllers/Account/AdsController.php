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

use App\Helpers\Arr;
use App\Helpers\Search;
use App\Models\Ad;
use App\Models\Category;
use App\Models\SavedAd;
use App\Models\SavedSearch;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\ReviewedScope;
use App\Mail\AdDeleted;
use Carbon\Carbon;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Torann\LaravelMetaTags\Facades\MetaTag;

class AdsController extends AccountBaseController
{
    private $per_page = 12;

    public function __construct()
    {
        parent::__construct();

        $this->per_page = (is_numeric(config('settings.ads_per_page'))) ? config('settings.ads_per_page') : $this->per_page;
    }

    /**
     * @param $pagePath
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|View
     */
    public function getPage($pagePath)
    {
        view()->share('pagePath', $pagePath);

        switch($pagePath) {
            case 'myads':
                return $this->getMyAds();
                break;
            case 'archived':
                return $this->getArchivedAds($pagePath);
                break;
            case 'favourite':
                return $this->getFavouriteAds();
                break;
            case 'pending-approval':
                return $this->getPendingApprovalAds();
                break;
            default:
                abort(404);
        }
    }

    /**
     * @return View
     */
    public function getMyAds()
    {
        $data = [];
        $data['ads'] = $this->my_ads->paginate($this->per_page);
        $data['type'] = 'myads';

        // Meta Tags
        MetaTag::set('title', t('My ads'));
        MetaTag::set('description', t('My ads on :app_name', ['app_name' => config('settings.app_name')]));

        return view('account.ads', $data);
    }

    /**
     * @param $pagePath
     * @param null $adId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|View
     */
    public function getArchivedAds($pagePath, $adId = null)
    {
        // If repost
        if (str_contains(URL::current(), $pagePath . '/repost')) {
            $res = false;
            if (is_numeric($adId) and $adId > 0) {
                $res = Ad::find($adId)->update([
                    'archived' => 0,
                    'created_at' => Carbon::now(),
                ]);
            }
            if (!$res) {
                flash()->success(t("The repost has done successfully."));
            } else {
                flash()->error(t("The repost has failed. Please try again."));
            }

            return redirect($this->lang->get('abbr') . '/account/' . $pagePath);
        }

        $data = [];
        $data['ads'] = $this->archived_ads->paginate($this->per_page);

        // Meta Tags
        MetaTag::set('title', t('My archived ads'));
        MetaTag::set('description', t('My archived ads on :app_name', ['app_name' => config('settings.app_name')]));

        view()->share('pagePath', $pagePath);

        return view('account.ads', $data);
    }

    /**
     * @return View
     */
    public function getFavouriteAds()
    {
        $data = [];
        $data['ads'] = $this->favourite_ads->paginate($this->per_page);

        // Meta Tags
        MetaTag::set('title', t('My favourite ads'));
        MetaTag::set('description', t('My favourite ads on :app_name', ['app_name' => config('settings.app_name')]));

        return view('account.ads', $data);
    }

    /**
     * @return View
     */
    public function getPendingApprovalAds()
    {
        $data = [];
        $data['ads'] = $this->pending_ads->paginate($this->per_page);

        // Meta Tags
        MetaTag::set('title', t('My pending approval ads'));
        MetaTag::set('description', t('My pending approval ads on :app_name', ['app_name' => config('settings.app_name')]));

        return view('account.ads', $data);
    }

    /**
     * @param HttpRequest $request
     * @return View
     */
    public function getSavedSearch(HttpRequest $request)
    {
        $data = [];

        // Get QueryString
        $tmp = parse_url(url(Request::getRequestUri()));
        $query_string = (isset($tmp['query']) ? $tmp['query'] : 'false');
        $query_string = preg_replace('|\&pag[^=]*=[0-9]*|i', '', $query_string);

        // CATEGORIES COLLECTION
        $cats = Category::where('translation_lang', $this->lang->get('abbr'))->orderBy('lft')->get();
        $cats = collect($cats)->keyBy('translation_of');
        view()->share('cats', $cats);

        // Search
        $saved_search = SavedSearch::where('country_code', $this->country->get('code'))
            ->where('user_id', $this->user->id)
            ->orderBy('created_at', 'DESC')
            ->simplePaginate($this->per_page, ['*'], 'pag');

        if (collect($saved_search->getCollection())->keyBy('query')->keys()->contains($query_string)) {
            if ($saved_search->getCollection()->count() > 0) {
                $search = new Search($request, $this->country, $this->lang);
                $data = $search->fechAll();
            }
        }
        $data['saved_search'] = $saved_search;

        // Meta Tags
        MetaTag::set('title', t('My saved search'));
        MetaTag::set('description', t('My saved search on :app_name', ['app_name' => config('settings.app_name')]));

        view()->share('pagePath', 'saved-search');

        return view('account.saved-search', $data);
    }

    /**
     * @param $pagePath
     * @param null $adId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function delete($pagePath, $adId = null)
    {
        // Get Entries ID
        $ids = [];
        if (Input::has('ad')) {
            $ids = Input::get('ad');
        } else {
            $id = $adId;
            if (!is_numeric($id) and $id <= 0) {
                $ids = [];
            } else {
                $ids[] = $id;
            }
        }

        // Delete
        $nb = 0;
        if ($pagePath == 'favourite') {
            $saved_ads = SavedAd::where('user_id', $this->user->id)->whereIn('ad_id', $ids);
            if ($saved_ads->count() > 0) {
                $nb = $saved_ads->delete();
            }
        } elseif ($pagePath == 'saved-search') {
            $nb = SavedSearch::destroy($ids);
        } else {
            foreach($ids as $id) {
                $ad = Ad::withoutGlobalScopes([ActiveScope::class, ReviewedScope::class])->find($id);
                if (!empty($ad)) {
                    $tmp_ad = Arr::toObject($ad->toArray());

                    // Delete Ad
                    $nb = $ad->delete();

                    // Send an Email confirmation
                    try {
                        Mail::send(new AdDeleted($tmp_ad));
                    } catch (\Exception $e) {
                        flash()->error($e->getMessage());
                    }
                }
            }
        }

        // Confirmation
        if ($nb == 0) {
            flash()->error(t("No deletion is done. Please try again."));
        } else {
            $count = count($ids);
            if ($count > 1) {
                flash()->success(t("x ads has been deleted successfully.", ['count' => $count]));
            } else {
                flash()->success(t("1 ad has been deleted successfully."));
            }
        }

        return redirect($this->lang->get('abbr') . '/account/' . $pagePath);
    }
}
