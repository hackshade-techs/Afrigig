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

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Ad;

class PaymentSent extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;
    public $ad;

    /**
     * PaymentSent constructor.
     * @param Payment $payment
     * @param Ad $ad
     */
    public function __construct(Payment $payment, Ad $ad)
    {
        $this->payment = $payment;
        $this->ad = $ad;

        $this->to($ad->seller_email, $ad->seller_name);
        $this->subject(trans('mail.payment_sent_title'));
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.payment.sent');
    }
}
