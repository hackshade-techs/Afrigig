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
use App\Models\Message;

class EmployerContacted extends Mailable
{
    use Queueable, SerializesModels;

    public $ad;
    public $msg;
    public $pathToFile;

    /**
     * Create a new message instance.
     *
     * @param Ad $ad
     * @param Message $msg
     * @param $pathToFile
     */
    public function __construct(Ad $ad, Message $msg, $pathToFile)
    {
        $this->ad = $ad;
        $this->msg = $msg;
        $this->pathToFile = $pathToFile;

        $this->to($ad->contact_email, $ad->contact_name);
        $this->replyTo($msg->email, $msg->name);
        $this->subject(trans('mail.ad_employer_contacted_title', [
            'title' => $ad->title,
            'app_name' => config('settings.app_name')
        ]));
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Attachments
        if (file_exists($this->pathToFile)) {
            return $this->view('emails.ad.employer-contacted')->attach($this->pathToFile);
        } else {
            return $this->view('emails.ad.employer-contacted');
        }
    }
}
