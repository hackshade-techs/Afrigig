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

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $ad;

    /**
     * AdNotification constructor.
     * @param $ad
     * @param $adminUser
     */
    public function __construct($ad, $adminUser)
    {
        $this->ad = $ad;

        $this->to($adminUser->email, $adminUser->name);
        $this->subject(trans('mail.ad_notification_title'));
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.ad.notification');
    }
}
