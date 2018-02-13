<?php
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/


/*
|--------------------------------------------------------------------------
| Installation
|--------------------------------------------------------------------------
|
| The install process routes
|
*/
Route::group([
    'middleware' => ['web'],
    'namespace'  => 'App\Http\Controllers',
], function() {
    // Route::get('install', 'InstallController@starting');
    Route::get('install/site_info', 'InstallController@siteInfo');
    Route::post('install/site_info', 'InstallController@siteInfo');
    Route::get('install/system_compatibility', 'InstallController@systemCompatibility');
    Route::get('install/database', 'InstallController@database');
    Route::post('install/database', 'InstallController@database');
    Route::get('install/database_import', 'InstallController@databaseImport');
    Route::get('install/cron_jobs', 'InstallController@cronJobs');
    Route::get('install/finish', 'InstallController@finish');
});


/*
|--------------------------------------------------------------------------
| Back-end
|--------------------------------------------------------------------------
|
| The admin panel routes
|
*/
Route::group([
    'middleware' => ['admin', 'bannedUser'],
    'prefix'     => config('larapen.admin.route_prefix', 'admin'),
    'namespace'  => 'App\Http\Controllers\Admin',
], function() {
    CRUD::resource('ad', 'AdController');
    CRUD::resource('skill', 'SkillController');
    CRUD::resource('category', 'CategoryController');
    CRUD::resource('picture', 'PictureController');
    CRUD::resource('item_type', 'AdTypeController');
    CRUD::resource('salary_type', 'SalaryTypeController');
    CRUD::resource('user', 'UserController');
    CRUD::resource('gender', 'GenderController');
    CRUD::resource('advertising', 'AdvertisingController');
    CRUD::resource('package', 'PackageController');
    CRUD::resource('page', 'PageController');
    CRUD::resource('payment', 'PaymentController');
    CRUD::resource('payment_method', 'PaymentMethodController');
    CRUD::resource('report_type', 'ReportTypeController');
    CRUD::resource('blacklist', 'BlacklistController');
    CRUD::resource('loc_admin1', 'SubAdmin1Controller');
    CRUD::resource('loc_admin2', 'SubAdmin2Controller');
    CRUD::resource('city', 'CityController');
    CRUD::resource('country', 'CountryController');
    CRUD::resource('currency', 'CurrencyController');
    CRUD::resource('time_zone', 'TimeZoneController');
    Route::get('account', 'UserController@account');
    Route::post('ajax/{table}/{field}', 'AjaxController@saveAjaxRequest');

    // Plugins
    Route::get('plugin', 'PluginController@index');
    Route::get('plugin/{plugin}/install', 'PluginController@install');
    Route::get('plugin/{plugin}/uninstall', 'PluginController@uninstall');
    Route::get('plugin/{plugin}/delete', 'PluginController@delete');
});


/*
|--------------------------------------------------------------------------
| Purchase Code Checker
|--------------------------------------------------------------------------
|
| Checking your purchase code. If you do not have one, please follow this link:
| https://codecanyon.net/item/jobclass-geolocalized-job-portal-script/18776089
| to acquire a valid code.
|
| IMPORTANT: Do not change this part of the code.
|
*/
$tab = [
    'install',
    config('larapen.admin.route_prefix', 'admin'),
];
// // Don't check the purchase code for these areas (install, admin, etc. )
// if (!in_array(\Illuminate\Support\Facades\Request::segment(1), $tab)) {
//     // Make the purchase code verification only if 'installed' file exists
//     if (file_exists(storage_path('installed')) && !config('settings.error')) {
//         // Get purchase code from 'installed' file
//         $purchase_code = file_get_contents(storage_path('installed'));

//         // Send the purchase code checking
//         if (
//             $purchase_code == '' or
//             config('settings.purchase_code') == '' or
//             $purchase_code != config('settings.purchase_code')
//         ) {
//             $apiUrl = config('larapen.core.purchase_code_checker_url') . config('settings.purchase_code') . '&item_id=' . config('larapen.core.item_id');
//             $data = \App\Helpers\Curl::fetch($apiUrl);

//             // Check & Get cURL error by checking if 'data' is a valid json
//             if (!isJson($data)) {
//                 $data = json_encode(['valid' => false, 'message' => 'Invalid purchase code. ' . strip_tags($data)]);
//             }

//             // Format object data
//             $data = json_decode($data);

//             // Check if 'data' has the valid json attributes
//             if (!isset($data->valid) || !isset($data->message)) {
//                 $data = json_encode(['valid' => false, 'message' => 'Invalid purchase code. Incorrect data format.']);
//                 $data = json_decode($data);
//             }

//             // Checking
//             if ($data->valid == true) {
//                 file_put_contents(storage_path('installed'), $data->license_code);
//             } else {
//                 // Invalid purchase code
//                 dd($data->message);
//             }
//         }
//     }
// }


/*
|--------------------------------------------------------------------------
| Front-end
|--------------------------------------------------------------------------
|
| The not translated front-end routes
|
*/
Route::group([
    'middleware' => ['web'],
    'namespace'  => 'App\Http\Controllers',
], function($router) {
    // AJAX
    Route::group(['prefix' => 'ajax'], function($router) {
        Route::get('places/countries/{code}/locations', 'Ajax\PlacesController@getLocations');
        Route::get('places/locations/{code}/sub-locations', 'Ajax\PlacesController@getSubLocations');
        Route::get('places/sub-locations/{code}/cities', 'Ajax\PlacesController@getCities');
        Route::post('autocomplete/city', 'Ajax\AutocompleteController@getCities');
        Route::post('category/sub-categories', 'Ajax\CategoryController@getSubCategories');
        Route::post('state/cities', 'Ajax\StateCitiesController@getCities');
        Route::post('save/ad', 'Ajax\AdController@saveAd');
        Route::post('save/search', 'Ajax\AdController@saveSearch');
        Route::get('json/countries.js', 'Ajax\JsonController@getCountries');
        Route::post('ad/phone', 'Ajax\AdController@getPhone');
    });

    // SEO
    Route::get('robots.txt', 'RobotsController@index');
    Route::get('sitemaps.xml', 'SitemapsController@index');

});


/*
|--------------------------------------------------------------------------
| Front-end
|--------------------------------------------------------------------------
|
| The translated front-end routes
|
*/
Route::group([
    'prefix'     => LaravelLocalization::setLocale(),
    'middleware' => ['local'],
    'namespace'  => 'App\Http\Controllers',
], function($router) {
    Route::group(['middleware' => ['web']], function($router) {
        // HOMEPAGE
        Route::group(['middleware' => 'httpCache:yes'], function($router) {
            Route::get('/', 'HomeController@index');
            Route::get(LaravelLocalization::transRoute('routes.countries'), 'CountriesController@index');
        });


        // AUTH
        Route::auth();
        Route::group(['middleware' => ['guest']], function() {
            // Registration Routes
            Route::get(LaravelLocalization::transRoute('routes.signup'), ['as' => 'register', 'uses' => 'Auth\RegisterController@showRegistrationForm']);
            Route::post('signup/submit', ['as' => 'register.post', 'uses' => 'Auth\RegisterController@register']);
            Route::get('signup/success', 'Auth\RegisterController@success');

            // Login Routes
            Route::get(LaravelLocalization::transRoute('routes.login'), ['as' => 'login', 'uses' => 'Auth\LoginController@showLoginForm']);
            Route::post(LaravelLocalization::transRoute('routes.login'), ['as' => 'login.post', 'uses' => 'Auth\LoginController@login']);

            // Activation
            Route::get('user/activation/{token}', 'Auth\RegisterController@activation');

            // Social Authentication
            Route::get('auth/facebook', 'Auth\SocialController@redirectToProvider');
            Route::get('auth/facebook/callback', 'Auth\SocialController@handleProviderCallback');
            Route::get('auth/google', 'Auth\SocialController@redirectToProvider');
            Route::get('auth/google/callback', 'Auth\SocialController@handleProviderCallback');
            Route::get('auth/twitter', 'Auth\SocialController@redirectToProvider');
            Route::get('auth/twitter/callback', 'Auth\SocialController@handleProviderCallback');
        });
        Route::get(LaravelLocalization::transRoute('routes.logout'), 'Auth\LoginController@logout');


        // ADS
        $router->pattern('id', '[0-9]+');
        Route::get(LaravelLocalization::transRoute('routes.create'), 'Ad\PostController@getForm');
        Route::post('create/submit', 'Ad\PostController@postForm');
        Route::get('create/success', 'Ad\PostController@success');
        Route::get('create/success-payment', 'Ad\PostController@getSuccessPayment');
        Route::get('create/cancel-payment', 'Ad\PostController@cancelPayment');
        Route::get('create/activation/{token}', 'Ad\PostController@activation');
        Route::group(['middleware' => 'auth'], function($router) {
            $router->pattern('id', '[0-9]+');
            Route::get('update/{id}', ['as' => 'adUpdateHelper', 'uses' => 'Ad\UpdateController@getForm']);
            Route::post('update/{id}', ['as' => 'adUpdateSubmitHelper', 'uses' => 'Ad\UpdateController@postForm']);
            Route::get('update/{id}/success', ['as' => 'adUpdateSuccessHelper', 'uses' => 'Ad\UpdateController@success']);
            Route::get('update/{id}/success-payment', 'Ad\UpdateController@getSuccessPayment');
            Route::get('update/{id}/cancel-payment', 'Ad\UpdateController@cancelPayment');
        });
        Route::get('{title}/{id}.html', ['as' => 'adHelper', 'uses' => 'Ad\DetailsController@index']);
        Route::post('{id}/contact', ['as' => 'adContactHelper', 'uses' => 'Ad\DetailsController@sendMessage']);
        Route::post('{id}/report', ['as' => 'adReportHelper', 'uses' => 'Ad\DetailsController@sendReport']);
        Route::post('send-by-email', ['as' => 'adSendByEmailHelper', 'uses' => 'SearchController@sendByEmail']);


        // ACCOUNT
        Route::group(['middleware' => ['auth', 'bannedUser'], 'namespace' => 'Account'], function($router) {
            $router->pattern('id', '[0-9]+');

            Route::get('account', 'HomeController@index');
            Route::post('account/details', 'EditController@details');
            Route::put('account/resume/update', 'EditController@resume');
            Route::put('account/settings/update', 'EditController@settings');
            Route::post('account/preferences', 'EditController@preferences');

            Route::get('account/home', 'HomeController@index');
            Route::get('account/saved-search', 'AdsController@getSavedSearch');

            $router->pattern('pagePath', '(myads|archived|favourite|pending-approval|saved-search)+');
            Route::get('account/{pagePath}', ['as' => 'adListHelper', 'uses' => 'AdsController@getPage']);
            // archived only
            Route::get('account/{pagePath}/repost/{id}', ['as' => 'adArchivedRepostHelper', 'uses' => 'AdsController@getArchivedAds']);

            // delete
            Route::get('account/{pagePath}/delete/{id}', ['as' => 'adGroupDeleteHelper', 'uses' => 'AdsController@delete']);
            Route::post('account/{pagePath}/delete', ['as' => 'adGroupDeleteSubmitHelper', 'uses' => 'AdsController@delete']);

            // close an user's account
            Route::get('account/close', 'CloseController@index');
            Route::post('account/close', 'CloseController@submit');
        });


        // Country Code Pattern
        $countries = new \App\Helpers\Localization\Helpers\Country();
        $country_code_pattern = implode('|', array_map('strtolower', array_keys($countries->all())));
        $router->pattern('countryCode', $country_code_pattern);


        // XML SITEMAPS
        Route::get('{countryCode}/sitemaps.xml', 'SitemapsController@site');
        Route::get('{countryCode}/sitemaps/pages.xml', 'SitemapsController@pages');
        Route::get('{countryCode}/sitemaps/categories.xml', 'SitemapsController@categories');
        Route::get('{countryCode}/sitemaps/cities.xml', 'SitemapsController@cities');
        Route::get('{countryCode}/sitemaps/ads.xml', 'SitemapsController@ads');


        // STATICS PAGES
        Route::group(['middleware' => 'httpCache:yes'], function($router) {
            Route::get(LaravelLocalization::transRoute('routes.page'), ['as' => 'pageHelper', 'uses' => 'PageController@index']);
            Route::get(LaravelLocalization::transRoute('routes.contact'), 'PageController@contact');
            Route::post(LaravelLocalization::transRoute('routes.contact'), 'PageController@contactPost');
            Route::get(LaravelLocalization::transRoute('routes.sitemap'), 'SitemapController@index');
        });


        // DYNAMIC URL PAGES
        $router->pattern('id', '[0-9]+');
        Route::get(LaravelLocalization::transRoute('routes.search'), ['as' => 'searchHelper', 'uses' => 'SearchController@index']);
        Route::get(LaravelLocalization::transRoute('routes.search-user'), ['as' => 'searchUserHelper', 'uses' => 'SearchController@user']);
        Route::get(LaravelLocalization::transRoute('routes.search-company'), ['as' => 'searchCompanyHelper', 'uses' => 'SearchController@company']);
        Route::get(LaravelLocalization::transRoute('routes.search-location'), ['as' => 'searchLocationHelper', 'uses' => 'SearchController@location']);
        Route::get(LaravelLocalization::transRoute('routes.search-subCat'), ['as' => 'searchSubCatHelper', 'uses' => 'SearchController@subCategory']);
        Route::get(LaravelLocalization::transRoute('routes.search-cat'), ['as' => 'searchCatHelper', 'uses' => 'SearchController@category']);
    });
});
