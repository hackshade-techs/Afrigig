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

namespace App\Http\Controllers\Ad;

use App\Models\PaymentMethod;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\ReviewedScope;
use Illuminate\Http\Request;
use App\Models\Ad;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Route;

trait PaymentTrait
{
    /**
     * Send Payment
     *
     * @param Request $request
     * @param Ad $ad
     * @return bool
     */
    public function postPayment(Request $request, Ad $ad)
    {
        // Get Payment Method
        $paymentMethod = PaymentMethod::find($request->input('payment_method'));

        if (!empty($paymentMethod)) {
            // Load Payment Plugin
            $plugin = plugin_load(strtolower($paymentMethod->name));

            // Payment using the selected Payment Method
            if (!empty($plugin)) {
                return call_user_func($plugin->class . '::postPayment', $request, $ad);
            }
        }

        return true;
    }

    /**
     * - Success URL when Credit Card is used
     * - Payment Process URL when no Credit Card is used
     *
     * @return mixed
     */
    public function getSuccessPayment()
    {
        // Get session parameters
        $params = Session::get('params');
        if (empty($params)) {
            flash()->error($this->msg['checkout']['error']);
            return redirect('/?error=paymentSessionNotFound');
        }

        // Get the entry
        $ad = Ad::withoutGlobalScopes([ActiveScope::class, ReviewedScope::class])->find($params['ad_id']);
        if (empty($ad)) {
            flash()->error($this->msg['checkout']['error']);
            return redirect('/?error=paymentEntryNotFound');
        }

        // GO TO PAYMENT METHODS

        if (!isset($params['payment_method'])) {
            flash()->error($this->msg['checkout']['error']);
            return redirect('/?error=paymentMethodParameterNotFound');
        }

        // Get Payment Method
        $paymentMethod = PaymentMethod::find($params['payment_method']);
        if (empty($paymentMethod)) {
            flash()->error($this->msg['checkout']['error']);
            return redirect('/?error=paymentMethodEntryNotFound');
        }

        // Load Payment Plugin
        $plugin = plugin_load(strtolower($paymentMethod->name));

        // Check if the Payment Method exists
        if (empty($plugin)) {
            flash()->error($this->msg['checkout']['error']);
            return redirect('/?error=paymentMethodPluginNotFound');
        }

        // Payment using the selected Payment Method
        return call_user_func($plugin->class . '::getSuccessPayment', $params, $ad);
    }

    /**
     * URL - Cancel Payment
     *
     * @return mixed
     */
    public function cancelPayment()
    {
        // Set the error message
        flash()->error($this->msg['checkout']['cancel']);

        // Get session parameters
        $params = Session::get('params');
        if (empty($params)) {
            return redirect('/?error=paymentCancelled');
        }

        // Get ad details
        $ad = Ad::withoutGlobalScopes([ActiveScope::class, ReviewedScope::class])->find($params['ad_id']);
        if (empty($ad)) {
            return redirect('/?error=paymentCancelled');
        }

        // Delete the ad only if it's new entry
        if (str_contains(Route::currentRouteAction(), 'PostController')) {
            $ad->delete();
        }

        // Redirect to the form page
        $this->uri['form'] = str_replace('#entryId', $ad->id, $this->uri['form']);
        return redirect($this->uri['form'] . '?error=paymentCancelled')->withInput();
    }
}
