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

return [

    /*
     |--------------------------------------------------------------------------
     | The item's ID on CodeCanyon
     |--------------------------------------------------------------------------
     |
     */

    'item_id' => '18776089',

	/*
	 |--------------------------------------------------------------------------
	 | Purchase code checker URL
	 |--------------------------------------------------------------------------
	 |
	 */

    'purchase_code_checker_url' => 'http://api.bedigit.com/envato.php?purchase_code=',

    /*
     |--------------------------------------------------------------------------
     | Demo domain
     |--------------------------------------------------------------------------
     |
     */

    'demo_domain' => 'bedigit.com',

    /*
     |--------------------------------------------------------------------------
     | Default Logo
     |--------------------------------------------------------------------------
     |
     */

    'logo' => 'app/default/logo.png',

    /*
     |--------------------------------------------------------------------------
     | Default Favicon
     |--------------------------------------------------------------------------
     |
     */

    'favicon' => 'app/default/ico/favicon.png',

    /*
     |--------------------------------------------------------------------------
     | Default ads picture & Default ads pictures sizes
     |--------------------------------------------------------------------------
     |
     */

    'picture' => [
        'default' => 'app/default/picture.jpg',
        'size' => [
            'width'  => 1000,
            'height' => 1000,
        ],
        'quality' => 100,
        'resize' => [
            'small'     => '120x90',
            'medium'    => '320x240',
            'big'       => '816x460',
            'large'     => '1000x1000'
        ],
    ],

    /*
     |--------------------------------------------------------------------------
     | Default user profile picture
     |--------------------------------------------------------------------------
     |
     */

    'photo' => '',

    /*
     |--------------------------------------------------------------------------
     | Set as default language the browser language
     |--------------------------------------------------------------------------
     |
     */

    'detect_browser_language' => false,

    /*
     |--------------------------------------------------------------------------
     | Optimize your links for SEO (for International website)
     |--------------------------------------------------------------------------
     |
     */

    'multi_countries_website' => false,

	/*
     |--------------------------------------------------------------------------
     | Force links to use the HTTPS protocol
     |--------------------------------------------------------------------------
     |
     */

	'force_https' => false,

    /*
     |--------------------------------------------------------------------------
     | Plugins Path & Namespace
     |--------------------------------------------------------------------------
     |
     */

    'plugin' => [
        'path'      => app_path('Plugins') . '/',
        'namespace' => '\\App\Plugins\\',
    ],

];
