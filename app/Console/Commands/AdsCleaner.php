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

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Package;
use App\Models\Payment;
use App\Models\User;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\ReviewedScope;
use App\Mail\AdArchived;
use App\Mail\AdDeleted;
use Carbon\Carbon;
use App\Models\Ad;
use App\Models\Country;
use App\Models\TimeZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class AdsCleaner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ads:clean';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all old ads.';

    /**
     * Default Ads Expiration Duration
     *
     * @var int
     */
    private $unactivated_ads_expiration = 30; // Delete the unactivated ads after this expiration
    private $activated_ads_expiration = 150; // Archive the activated ads after this expiration
    private $archived_ads_expiration = 7; // Delete the archived ads after this expiration

	/**
	 * AdsCleaner constructor.
	 */
    public function __construct()
    {
        parent::__construct();

        $this->unactivated_ads_expiration = (int)config('settings.unactivated_ads_expiration', $this->unactivated_ads_expiration);
        $this->activated_ads_expiration = (int)config('settings.activated_ads_expiration', $this->activated_ads_expiration);
        $this->archived_ads_expiration = (int)config('settings.archived_ads_expiration', $this->archived_ads_expiration);

        // App name
        config(['app.name' => config('settings.app_name')]);
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
    }
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get all Countries
        $countries = Country::withoutGlobalScope(ActiveScope::class)->get();
		if ($countries->count() <= 0) {
			dd('No country found.');
		}

		foreach ($countries as $country)
		{
			// Ads query
            $ads = Ad::withoutGlobalScopes([ActiveScope::class, ReviewedScope::class])->where('country_code', $country->code);
            
            if ($ads->count() <= 0) {
                $this->info('No ads in "' . $country->name . '" (' . strtolower($country->code) . ') website.');
                continue;
            }
            
            // Get all Ads
            $ads = $ads->get();

			foreach ($ads as $ad)
			{
				// Get country time zone by city
				$city = City::find($ad->city_id);
				$timeZoneId = (!empty($city)) ? $city->time_zone : 'Europe/London';

				// Set today with time zone
				$today = Carbon::now($timeZoneId);
                
                // Debug
                // dd($today->diffInDays($ad->created_at));

                /* Non-activated Ads */
                if ($ad->active != 1) {
                    // Delete non-active Ads after '$this->unactivated_ads_expiration' days
                    if ($today->diffInDays($ad->created_at) >= $this->unactivated_ads_expiration) {
                        $ad->delete();
                        continue;
                    }
                }
                /* Activated Ads */
                else
                {
                    /* Admin's Ads */
                    if (isset($ad->user_id)) {
                        $possibleAdminUser = User::find($ad->user_id);
                        if (!empty($possibleAdminUser)) {
                            if ($possibleAdminUser->is_admin == 1) {
                                // Delete all Admin Ads after '$this->activated_ads_expiration' days
                                if ($today->diffInDays($ad->created_at) >= $this->activated_ads_expiration) {
                                    $ad->delete();
                                    continue;
                                }
                            }
                        }
                    }
    
                    /* Users's Ads */

                    /* Check if ad is featured */
                    if ($ad->featured == 1)
                    {
                        // Get all Packs
                        $packages = Package::where('translation_lang', config('applang.abbr', config('app.locale')))->get();

                        /* It is a website with Premium Ads */
                        if ($packages->count() > 0) {
                            // Check the ad's transaction
                            $payment = Payment::where('ad_id', $ad->id)->orderBy('id', 'DESC')->first();
                            if (!empty($payment)) {
                                // Get Package info
                                $package = Package::find($payment->package_id);
                                if (!empty($package)) {
                                    // Un-featured the ad after {$package->duration} days
                                    if ($today->diffInDays($ad->created_at) >= $package->duration) {

                                        // Un-featured
                                        $ad->featured = 0;
                                        $ad->save();

                                        continue;
                                    }
                                }
                            }
                        }
                    }
                    /* It is a free website */
                    else
                    {
                        // Auto-archive
                        if ($ad->archived != 1) {
                            // Archive all activated ads after '$this->activated_ads_expiration' days
                            if ($today->diffInDays($ad->created_at) >= $this->activated_ads_expiration) {
                                // Archive
                                $ad->archived = 1;
                                $ad->save();
                
                                if ($country->active == 1) {
                                    // Send an Email confirmation
                                    Mail::send(new AdArchived($ad));
                                }
                
                                continue;
                            }
                        }
    
                        // Auto-delete
                        if ($ad->archived == 1) {
                            // Send an email alert to a week of the definitive deletion (using 'updated_at')
                            if ($today->diffInWeeks($ad->updated_at->subWeek()) >= 1) {
                                // @todo: Alert user 1 week later
                            }
        
                            // Send an email alert the day before the final deletion (using 'updated_at')
                            if ($today->diffInDays($ad->updated_at->subDay()) >= 1) {
                                // @todo: Alert user 1 day later
                            }
        
                            // Delete all archived ads '$this->archived_ads_expiration' days later (using 'updated_at')
                            if ($today->diffInDays($ad->updated_at) >= $this->archived_ads_expiration) {
                                if ($country->active == 1) {
                                    // Send an Email confirmation
                                    Mail::send(new AdDeleted($ad));
                                }
            
                                // Delete
                                $ad->delete();
            
                                continue;
                            }
                        }
                    }
                }
            }
        }
    }
}
