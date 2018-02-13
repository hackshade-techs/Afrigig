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

namespace App\Plugins\paypal;

use App\Models\Ad;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use App\Helpers\Payment;
use App\Models\Package;
use Illuminate\Support\Facades\Session;
use Omnipay\Omnipay;
use Illuminate\Support\Facades\Route;

class Paypal extends Payment
{
    /**
     * @param Request $request
     * @param Ad $ad
     * @return bool
     */
    public static function postPayment(Request $request, Ad $ad)
    {
        // Get Pack infos
        $package = Package::find($request->input('package'));

        // Don't make a payment if 'price' = 0 or null
        if (empty($package) or $package->price <= 0) {
            return false;
        }

        $params = [
            'payment_method' => $request->get('payment_method'),
            'cancelUrl'      => url(parent::$lang->get('abbr') . '/create/cancel-payment'),
            'returnUrl'      => url(parent::$lang->get('abbr') . '/create/success-payment'),
            'name'           => $package->name,
            'description'    => $package->name,
            'amount'         => (!is_float($package->price)) ? floatval($package->price) : $package->price,
            'currency'       => $package->currency_code,
        ];

        // Set the API return URLs for update form
        if (str_contains(Route::currentRouteAction(), 'UpdateController')) {
            $params['cancelUrl'] = url(parent::$lang->get('abbr') . '/update/' . $ad->id . '/cancel-payment');
            $params['returnUrl'] = url(parent::$lang->get('abbr') . '/update/' . $ad->id . '/success-payment');
        }

        Session::put('params', array_merge($params, ['ad_id' => $ad->id, 'package_id' => $package->id]));
        Session::save();

        try {
            $gateway = Omnipay::create('PayPal_Express');
            $gateway->setUsername(config('payment.paypal.username'));
            $gateway->setPassword(config('payment.paypal.password'));
            $gateway->setSignature(config('payment.paypal.signature'));
            $gateway->setTestMode((config('payment.paypal.mode') == 'sandbox') ? true : false);

            // Card data
            // $params['card'] = [];

            $response = $gateway->purchase($params)->send();

            // Payment by Credit Card when Card info are provide from the form.
            if ($response->isSuccessful()) {
                // Payment was successful: update database
                // print_r($response); // debug
            } elseif ($response->isRedirect()) {
                // Redirect to offsite payment gateway
                // Redirect to success URL to make the payment on the Paypal website
                $response->redirect();
            } else {
                // Payment failed

                // Remove the entry
                parent::removeEntry($ad);

                // Return to form
                $msg = '';
                $msg .= parent::$msg['checkout']['error'];
                $msg .= '<br>' . $response->getMessage();
                flash()->error($msg);

                return false;
            }
        } catch (\Exception $e) {
            // Payment API error

            // Remove the entry
            parent::removeEntry($ad);

            Session::forget('params');

            // Return to Form
            flash()->error($e->getMessage());

            return false;
        }

        // If no errors found ...
        return true;
    }

    /**
     * @param $params
     * @param $ad
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public static function getSuccessPayment($params, $ad)
    {
        // Set form page URL
        parent::$uri['form'] = str_replace('#entryId', $ad->id, parent::$uri['form']);
        parent::$uri['success'] = str_replace('#entryId', $ad->id, parent::$uri['success']);

        // Try to make the payment
        try {
            $gateway = Omnipay::create('PayPal_Express');
            $gateway->setUsername(config('payment.paypal.username'));
            $gateway->setPassword(config('payment.paypal.password'));
            $gateway->setSignature(config('payment.paypal.signature'));
            $gateway->setTestMode((config('payment.paypal.mode') == 'live') ? false : true);

            // Make the payment
            $response = $gateway->completePurchase($params)->send();
            $paypal_response = $response->getData(); // this is the raw response object

            if (isset($paypal_response['PAYMENTINFO_0_ACK']) && $paypal_response['PAYMENTINFO_0_ACK'] === 'Success') {
                // Payment was successful: update database
                // Save the Payment in database
                $payment = parent::register($ad, $params);

                // Successful transaction
                flash()->success(parent::$msg['checkout']['success']);

                // Redirect
                return redirect(parent::$uri['success'])->with(['success' => 1, 'message' => parent::$msg['post']['success']]);
            } else {
                // Payment failed

                // Remove the entry
                parent::removeEntry($ad);

                // Return to Form
                flash()->error(parent::$msg['checkout']['error']);

                // Redirect
                return redirect(parent::$uri['form'] . '?error=payment')->withInput();
            }
        } catch (\Exception $e) {
            // Payment API error

            // Remove the entry
            parent::removeEntry($ad);

            // Return to Form
            flash()->error($e->getMessage());

            // Redirect
            return redirect(parent::$uri['form'] . '?error=paymentApi')->withInput();
        }
    }

    /**
     * @return bool
     */
    public static function installed()
    {
        $paymentMethod = PaymentMethod::active()->where('name', 'LIKE', 'paypal')->first();
        if (empty($paymentMethod)) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public static function install()
    {
        // Remove the plugin entry
        self::uninstall();

        // Plugin data
        $data = [
            'id'           => 1,
            'name'         => 'paypal',
            'display_name' => 'Paypal',
            'description'  => 'Payment with Paypal',
            'has_ccbox'    => 0,
            'lft'          => 0,
            'rgt'          => 0,
            'depth'        => 1,
            'active'       => 1,
        ];

        try {
            // Create plugin data
            $paymentMethod = PaymentMethod::create($data);
            if (empty($paymentMethod)) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public static function uninstall()
    {
        $deletedRows = PaymentMethod::where('name', 'LIKE', 'paypal')->delete();
        if ($deletedRows <= 0) {
            return false;
        }

        return true;
    }
}
