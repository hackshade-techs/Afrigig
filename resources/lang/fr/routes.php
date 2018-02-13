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
    
    'countries' => 'pays',
    
    'login' => 'connexion',
    'logout' => 'deconnexion',
    'signup' => 'inscription',
    'create' => 'creer-offre',

    'page' => 'page/{slug}.html',
    't-page' => 'page',
    'v-page' => 'page/:slug.html',

    'contact' => 'contact.html',

];

if (config('larapen.core.multi_countries_website'))
{
    // Sitemap
    $lcRoutes['sitemap'] = '{countryCode}/plan-du-site.html';
    $lcRoutes['v-sitemap'] = ':countryCode/plan-du-site.html';

    // Latest Ads
    $lcRoutes['search'] = '{countryCode}/dernieres-offres';
    $lcRoutes['t-search'] = 'dernieres-offres';
    $lcRoutes['v-search'] = ':countryCode/dernieres-offres';

    // Search by Sub-Category
    $lcRoutes['search-subCat'] = '{countryCode}/categorie-emploi/{catSlug}/{subCatSlug}';
    $lcRoutes['t-search-subCat'] = 'categorie-emploi';
    $lcRoutes['v-search-subCat'] = ':countryCode/categorie-emploi/:catSlug/:subCatSlug';

    // Search by Category
    $lcRoutes['search-cat'] = '{countryCode}/categorie-emploi/{catSlug}';
    $lcRoutes['t-search-cat'] = 'categorie-emploi';
    $lcRoutes['v-search-cat'] = ':countryCode/categorie-emploi/:catSlug';

    // Search by Location
    $lcRoutes['search-location'] = '{countryCode}/offres-emploi/{city}/{id}';
    $lcRoutes['t-search-location'] = 'offres-emploi';
    $lcRoutes['v-search-location'] = ':countryCode/offres-emploi/:city/:id';

    // Search by User
    $lcRoutes['search-user'] = '{countryCode}/search/user/{id}';
    $lcRoutes['t-search-user'] = 'search/user';
    $lcRoutes['v-search-user'] = ':countryCode/search/user/:id';

    // Search by Company name
    $lcRoutes['search-company'] = '{countryCode}/offres-emploi-chez/{companyName}';
    $lcRoutes['t-search-company'] = 'offres-emploi-chez';
    $lcRoutes['v-search-company'] = ':countryCode/offres-emploi-chez/:companyName';
}
else
{
    // Sitemap
    $lcRoutes['sitemap'] = 'plan-du-site.html';
    $lcRoutes['v-sitemap'] = 'plan-du-site.html';

    // Latest Ads
    $lcRoutes['search'] = 'dernieres-offres';
    $lcRoutes['t-search'] = 'dernieres-offres';
    $lcRoutes['v-search'] = 'dernieres-offres';

    // Search by Sub-Category
    $lcRoutes['search-subCat'] = 'categorie-emploi/{catSlug}/{subCatSlug}';
    $lcRoutes['t-search-subCat'] = 'categorie-emploi';
    $lcRoutes['v-search-subCat'] = 'categorie-emploi/:catSlug/:subCatSlug';

    // Search by Category
    $lcRoutes['search-cat'] = 'categorie-emploi/{catSlug}';
    $lcRoutes['t-search-cat'] = 'categorie-emploi';
    $lcRoutes['v-search-cat'] = 'categorie-emploi/:catSlug';

    // Search by Location
    $lcRoutes['search-location'] = 'offres-emploi/{city}/{id}';
    $lcRoutes['t-search-location'] = 'offres-emploi';
    $lcRoutes['v-search-location'] = 'offres-emploi/:city/:id';

    // Search by User
    $lcRoutes['search-user'] = 'search/user/{id}';
    $lcRoutes['t-search-user'] = 'search/user';
    $lcRoutes['v-search-user'] = 'search/user/:id';

    // Search by Company name
    $lcRoutes['search-company'] = 'offres-emploi-chez/{companyName}';
    $lcRoutes['t-search-company'] = 'offres-emploi-chez';
    $lcRoutes['v-search-company'] = 'offres-emploi-chez/:companyName';
}

return $lcRoutes;