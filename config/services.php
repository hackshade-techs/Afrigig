<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => null,
        'secret' => null,
        'guzzle' => [
            'verify' => false,
        ],
    ],

    'mandrill' => [
        'secret' => null,
        'guzzle' => [
            'verify' => false,
        ],
    ],

    'ses' => [
        'key'    => null,
        'secret' => null,
        'region' => null,
    ],

    'sparkpost' => [
        'secret' => null,
        'guzzle' => [
            'verify' => false,
        ],
    ],

    'facebook' => [
        'client_id'     => null,
        'client_secret' => null,
        'redirect'      => env('APP_URL') . '/auth/facebook/callback',
    ],

    'google' => [
        'client_id'     => null,
        'client_secret' => null,
        'redirect'      => env('APP_URL') . '/auth/google/callback',
    ],

    'twitter' => [
        'client_id'     => null,
        'client_secret' => null,
        'redirect'      => env('APP_URL') . '/auth/twitter/callback',
    ],

    'googlemaps' => [
        'key' => null, //-> for Google Map JavaScript & Embeded
    ],

    'paypal' => [
        'mode'      => env('PAYPAL_MODE', 'sandbox'),
        'username'  => env('PAYPAL_USERNAME', ''),
        'password'  => env('PAYPAL_PASSWORD', ''),
        'signature' => env('PAYPAL_SIGNATURE', ''),
    ],

    'stripe' => [
        'model'  => App\Models\User::class,
        'key'    => env('STRIPE_KEY', ''),
        'secret' => env('STRIPE_SECRET', ''),
    ],

    'checkout' => [
        'advanced'        => env('TWOCHECKOUT_ADVANCED', true),
        'mode'            => env('TWOCHECKOUT_MODE', ''),
        'publishable_key' => env('TWOCHECKOUT_PUBLISHABLE_KEY', ''),
        'private_key'     => env('TWOCHECKOUT_PRIVATE_KEY', ''),
        'seller_id'       => env('TWOCHECKOUT_SELLER_ID', ''),
        'secret_word'     => env('TWOCHECKOUT_SECRET_WORD', ''),
    ],

];
