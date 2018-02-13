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

use App\Models\Category;
use App\Models\City;
use Torann\LaravelMetaTags\Facades\MetaTag;

class SitemapController extends FrontController
{
	/**
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
    public function index()
    {
        $data = array();
        
        // Get Categories
        $cats = Category::where('translation_lang', $this->lang->get('abbr'))->orderBy('lft')->get();
        $cats = collect($cats)->keyBy('translation_of');
        $cats = $sub_cats = $cats->groupBy('parent_id');

        if ($cats->has(0)) {
            $col = round($cats->get(0)->count() / 3, 0, PHP_ROUND_HALF_EVEN);
            $col = ($col > 0) ? $col : 1;
            $data['cats'] = $cats->get(0)->chunk($col);
            $data['sub_cats'] = $sub_cats->forget(0);
        } else {
            $data['cats'] = collect([]);
            $data['sub_cats'] = collect([]);
        }
        
        // Location sitemap
        $limit = 100;
        $cities = City::where('country_code', $this->country->get('code'))->take($limit)->orderBy('population', 'DESC')->get();
        
        $col = round($cities->count() / 4, 0, PHP_ROUND_HALF_EVEN);
        $col = ($col > 0) ? $col : 1;
        $data['cities'] = $cities->chunk($col);
        
        // Meta Tags
        MetaTag::set('title', t('Sitemap :country', ['country' => $this->country->get('name')]));
        MetaTag::set('description', t('Sitemap :domain - :country. 100% Free Job Board', ['domain' => getDomain(), 'country' => $this->country->get('name')]));
        
        return view('sitemap.index', $data);
    }
}
