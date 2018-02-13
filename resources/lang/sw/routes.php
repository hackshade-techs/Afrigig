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

$lcRoutes = [
    /*
    |--------------------------------------------------------------------------
    | Routes Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the global website.
    |
    */
    
    'countries' => 'countries',
    
    'login' => 'login',
    'logout' => 'logout',
    'signup' => 'signup',
    'create' => 'create',

    'page' => 'page/{slug}.html',
    't-page' => 'page',
    'v-page' => 'page/:slug.html',

    'contact' => 'contact.html',

];

if (config('larapen.core.multi_countries_website'))
{
    // Sitemap
    $lcRoutes['sitemap'] = '{countryCode}/sitemap.html';
    $lcRoutes['v-sitemap'] = ':countryCode/sitemap.html';

    // Latest Ads
    $lcRoutes['search'] = '{countryCode}/latest-jobs';
    $lcRoutes['t-search'] = 'latest-jobs';
    $lcRoutes['v-search'] = ':countryCode/latest-jobs';

    // Search by Sub-Category
    $lcRoutes['search-subCat'] = '{countryCode}/job-category/{catSlug}/{subCatSlug}';
    $lcRoutes['t-search-subCat'] = 'job-category';
    $lcRoutes['v-search-subCat'] = ':countryCode/job-category/:catSlug/:subCatSlug';

    // Search by Category
    $lcRoutes['search-cat'] = '{countryCode}/job-category/{catSlug}';
    $lcRoutes['t-search-cat'] = 'job-category';
    $lcRoutes['v-search-cat'] = ':countryCode/job-category/:catSlug';

    // Search by Location
    $lcRoutes['search-location'] = '{countryCode}/jobs/{city}/{id}';
    $lcRoutes['t-search-location'] = 'jobs';
    $lcRoutes['v-search-location'] = ':countryCode/jobs/:city/:id';

    // Search by User
    $lcRoutes['search-user'] = '{countryCode}/search/user/{id}';
    $lcRoutes['t-search-user'] = 'search/user';
    $lcRoutes['v-search-user'] = ':countryCode/search/user/:id';

    // Search by Company name
    $lcRoutes['search-company'] = '{countryCode}/jobs-at/{companyName}';
    $lcRoutes['t-search-company'] = 'jobs-at';
    $lcRoutes['v-search-company'] = ':countryCode/jobs-at/:companyName';
}
else
{
    // Sitemap
    $lcRoutes['sitemap'] = 'sitemap.html';
    $lcRoutes['v-sitemap'] = 'sitemap.html';

    // Latest Ads
    $lcRoutes['search'] = 'latest-jobs';
    $lcRoutes['t-search'] = 'latest-jobs';
    $lcRoutes['v-search'] = 'latest-jobs';

    // Search by Sub-Category
    $lcRoutes['search-subCat'] = 'job-category/{catSlug}/{subCatSlug}';
    $lcRoutes['t-search-subCat'] = 'job-category';
    $lcRoutes['v-search-subCat'] = 'job-category/:catSlug/:subCatSlug';

    // Search by Category
    $lcRoutes['search-cat'] = 'job-category/{catSlug}';
    $lcRoutes['t-search-cat'] = 'job-category';
    $lcRoutes['v-search-cat'] = 'job-category/:catSlug';

    // Search by Location
    $lcRoutes['search-location'] = 'jobs/{city}/{id}';
    $lcRoutes['t-search-location'] = 'jobs';
    $lcRoutes['v-search-location'] = 'jobs/:city/:id';

    // Search by User
    $lcRoutes['search-user'] = 'search/user/{id}';
    $lcRoutes['t-search-user'] = 'search/user';
    $lcRoutes['v-search-user'] = 'search/user/:id';

    // Search by Company name
    $lcRoutes['search-company'] = 'jobs-at/{companyName}';
    $lcRoutes['t-search-company'] = 'jobs-at';
    $lcRoutes['v-search-company'] = 'jobs-at/:companyName';
}

return $lcRoutes;