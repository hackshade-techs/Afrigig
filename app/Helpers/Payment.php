<?php
/**
 * LaraClassified - Geo Classified Ads CMS
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

namespace App\Helpers;

use App\Models\Ad;
use App\Models\Package;
use App\Models\Payment as PaymentModel;
use App\Mail\AdPosted;
use App\Mail\AdNotification;
use App\Mail\PaymentNotification;
use App\Mail\PaymentSent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Facades\Route;

class Payment
{
    public static $country;
    public static $lang;
    public static $msg = [];
    public static $uri = [];

    /**
     * Save the payment and Send payment confirmation email
     * @param $ad
     * @param $params
     * @return PaymentModel
     */
    public static function register(Ad $ad, $params)
    {
        if (empty($ad)) {
            return null;
        }

        // Update ad 'reviewed'
        $ad->reviewed = 1;
        $ad->featured = 1;
        $ad->save();

        // Save the payment
        $paymentInfo = [
            'ad_id'             => $ad->id,
            'package_id'        => $params['package_id'],
            'payment_method_id' => $params['payment_method'],
        ];
        $payment = new PaymentModel($paymentInfo);
        $payment->save();

        // SEND EMAILS

        // Get all admin users
        $admins = User::where('is_admin', 1)->get();

        if (str_contains(Route::currentRouteAction(), 'PostController')) {
            // Send Confirmation Email
            if (config('settings.require_ads_activation') == 1) {
                try {
                    Mail::send(new AdPosted($ad));
                } catch (\Exception $e) {
                    flash()->error($e->getMessage());
                }
            }

            // Send Admin Notification Email
            if (config('settings.admin_email_notification') == 1) {
                try {
                    if ($admins->count() > 0) {
                        foreach ($admins as $admin) {
                            Mail::send(new AdNotification($ad, $admin));
                        }
                    }
                } catch (\Exception $e) {
                    flash()->error($e->getMessage());
                }
            }
        }

        // Send Payment Email Notifications
        if (config('settings.payment_email_notification') == 1) {
            // Send Confirmation Email
            try {
                Mail::send(new PaymentSent($payment, $ad));
            } catch (\Exception $e) {
                flash()->error($e->getMessage());
            }

            // Send to Admin the Payment Notification Email
            try {
                if ($admins->count() > 0) {
                    foreach ($admins as $admin) {
                        Mail::send(new PaymentNotification($payment, $ad, $admin));
                    }
                }
            } catch (\Exception $e) {
                flash()->error($e->getMessage());
            }
        }

        return $payment;
    }

    /**
     * Remove the ad for public - If there are no free packages
     * @param Ad $ad
     * @return bool
     */
    public static function removeEntry(Ad $ad)
    {
        // Don't delete the ad when user try to UPGRADE her ads
        if (str_contains(Route::currentRouteAction(), 'UpdateController')) {
            return false;
        }

        if (Auth::check()) {
            // Delete the ad if user is logged in and there are no free package
            if (Package::where('price', 0)->count() == 0) {
                if (!empty($ad)) {
                    // But! User can access to the ad from her area to UPGRADE it!
                    // You can UNCOMMENT the line below if you don't want the feature above.
                    // $ad->delete();
                }
            }
        } else {
            // Delete the ad if user is a guest
            if (!empty($ad)) {
                $ad->delete();
            }
        }

        return true;
    }
}
