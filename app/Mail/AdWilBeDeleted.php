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
use App\Models\Ad;

class AdWilBeDeleted extends Mailable
{
    use Queueable, SerializesModels;

    public $ad;
    public $days;

    /**
     * Create a new message instance.
     *
     * @param Ad $ad
     * @param $days
     */
    public function __construct(Ad $ad, $days)
    {
        $this->ad = $ad;
        $this->days = $days;

        $this->to($ad->contact_email, $ad->contact_name);
        $this->subject(trans('mail.ad_will_be_deleted_title', [
            'title' => $ad->title,
            'days' => $days
        ]));
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.ad.will-be-deleted');
    }
}
