<?php

namespace Larapen\Admin\app\Http\Controllers;


class Controller extends \App\Http\Controllers\Controller
{
    public $request;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        // App name
        config(['app.name' => config('settings.app_name')]);
        // Admin panel theme
        config(['larapen.admin.skin' => config('settings.admin_theme')]);
        // Mail
        config(['mail.driver' => env('MAIL_DRIVER', config('settings.mail_driver'))]);
        config(['mail.host' => env('MAIL_HOST', config('settings.mail_host'))]);
        config(['mail.port' => env('MAIL_PORT', config('settings.mail_port'))]);
        config(['mail.encryption' => env('MAIL_ENCRYPTION', config('settings.mail_encryption'))]);
        config(['mail.username' => env('MAIL_USERNAME', config('settings.mail_username'))]);
        config(['mail.password' => env('MAIL_PASSWORD', config('settings.mail_password'))]);
        config(['mail.from.address' => env('MAIL_FROM_ADDRESS', config('settings.app_email_sender'))]);
        config(['mail.from.name' => env('MAIL_FROM_NAME', config('settings.app_name'))]);
        // Mailgun
        config(['services.mailgun.domain' => env('MAILGUN_DOMAIN', config('settings.mailgun_domain'))]);
        config(['services.mailgun.secret' => env('MAILGUN_SECRET', config('settings.mailgun_secret'))]);
        // Mandrill
        config(['services.mandrill.secret' => env('MANDRILL_SECRET', config('settings.mandrill_secret'))]);
        // Amazon SES
        config(['services.ses.key' => env('SES_KEY', config('settings.ses_key'))]);
        config(['services.ses.secret' => env('SES_SECRET', config('settings.ses_secret'))]);
        config(['services.ses.region' => env('SES_REGION', config('settings.ses_region'))]);

        // Use DB checkbox field Settings (for Admin panel)
        if (str_contains(config('settings.show_powered_by'), 'fa')) {
            config(['larapen.admin.show_powered_by' => str_contains(config('settings.show_powered_by'), 'fa-check-square-o') ? 1 : 0]);
        } else {
            config(['larapen.admin.show_powered_by' => config('settings.show_powered_by')]);
        }
    }
}
